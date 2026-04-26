<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $status = $request->input('status');
        $perPage = (int) $request->input('per_page', 6);
        $perPage = in_array($perPage, [6, 12, 18, 24, 36]) ? $perPage : 6;
        $publishedFrom = $request->input('published_from');
        $publishedTo = $request->input('published_to');

        // Tampilkan yang terbaru diunggah lebih dulu (pakai published_at jika ada, jika tidak gunakan created_at)
        $query = News::with(['category', 'author'])
            ->orderByDesc(DB::raw('COALESCE(published_at, created_at)'))
            ->orderByDesc('created_at');

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($publishedFrom) {
            $query->whereDate('published_at', '>=', $publishedFrom);
        }

        if ($publishedTo) {
            $query->whereDate('published_at', '<=', $publishedTo);
        }

        $news = $query->paginate($perPage)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $statuses = $this->statusOptions();
        $statusCounts = News::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $totalViews = News::sum('views');

        return view('admin.news.index', compact(
            'news',
            'categories',
            'statuses',
            'search',
            'category',
            'status',
            'perPage',
            'publishedFrom',
            'publishedTo',
            'statusCounts',
            'totalViews'
        ));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $news = new News();

        $statuses = $this->statusOptions();

        return view('admin.news.create', compact('categories', 'news', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validateNews($request);
        $payload['slug'] = Str::slug($payload['title']) ?: Str::random(6);
        $payload['author_id'] = $request->user()->id;
        $payload['views'] = 0;

        if ($request->hasFile('thumbnail')) {
            $payload['thumbnail'] = $request->file('thumbnail')->store('news', 'public');
        }

        News::create($payload);

        return redirect()->route('admin.news.index')->with('status', 'Berita berhasil dibuat.');
    }

    public function edit(News $news)
    {
        $categories = Category::orderBy('name')->get();

        $statuses = $this->statusOptions();

        return view('admin.news.edit', compact('news', 'categories', 'statuses'));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $payload = $this->validateNews($request, $news);
        $payload['slug'] = Str::slug($payload['title']) ?: $news->slug;

        if ($request->hasFile('thumbnail')) {
            if (!empty($news->thumbnail)) {
                try {
                    if (file_exists(public_path('storage/' . $news->thumbnail))) {
                        Storage::disk('public')->delete($news->thumbnail);
                    }
                } catch (\Exception $e) {
                    // Abaikan error
                }
            }
            $payload['thumbnail'] = $request->file('thumbnail')->store('news', 'public');
        }

        $news->update($payload);

        return redirect()->route('admin.news.index')->with('status', 'Berita berhasil diperbarui.');
    }

    protected function validateNews(Request $request, ?News $news = null): array
    {
        $methods = [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'thumbnail' => ['nullable', 'image'],
        ];

        $payload = $request->validate($methods);

        return $payload;
    }

    protected function statusOptions(): array
    {
        return ['draft', 'published', 'archived'];
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return back()->with('status', 'Berita berhasil dihapus.');
    }
}
