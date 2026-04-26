<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $categories = Announcement::categories();
        $query = Announcement::with('attachments')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        $totalAnnouncements = Announcement::count();
        $publishedCount = Announcement::where('status', Announcement::STATUS_PUBLISHED)->count();
        $draftCount = Announcement::where('status', Announcement::STATUS_DRAFT)->count();
        $categoryCount = count($categories);
        $publishedThisMonth = Announcement::where('status', Announcement::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->whereBetween('published_at', [now()->startOfMonth(), now()])
            ->count();

        if ($request->filled('category') && array_key_exists($request->category, $categories)) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where('title', 'like', $q);
        }

        $announcements = $query->paginate(12)->withQueryString();

        return view('admin.announcements.index', compact(
            'announcements',
            'categories',
            'totalAnnouncements',
            'publishedCount',
            'draftCount',
            'categoryCount',
            'publishedThisMonth'
        ));
    }

    public function create()
    {
        $categories = Announcement::categories();
        $statuses = [
            Announcement::STATUS_DRAFT => 'Draft',
            Announcement::STATUS_PUBLISHED => 'Terbit',
        ];

        return view('admin.announcements.create', compact('categories', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data['slug'] = $this->generateSlug($data['title']);
        $data['created_by'] = auth()->id();

        if ($data['status'] === Announcement::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        DB::transaction(function () use ($request, $data) {
            $announcement = Announcement::create($data);
            $this->handleAttachments($request, $announcement);
        });

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Announcement $announcement)
    {
        $announcement->load('attachments');
        $categories = Announcement::categories();
        $statuses = [
            Announcement::STATUS_DRAFT => 'Draft',
            Announcement::STATUS_PUBLISHED => 'Terbit',
        ];

        return view('admin.announcements.edit', compact('announcement', 'categories', 'statuses'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $this->validateRequest($request);
        $data['slug'] = $this->generateSlug($data['title'], $announcement->id);

        if ($data['status'] === Announcement::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        DB::transaction(function () use ($request, $announcement, $data) {
            $announcement->update($data);
            $this->handleAttachments($request, $announcement);
        });

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Pengumuman berhasil dihapus.');
    }

    public function destroyAttachment(Announcement $announcement, AnnouncementAttachment $attachment)
    {
        if ($attachment->announcement_id !== $announcement->id) {
            abort(404);
        }

        if ($attachment->path) {
            try {
                if (file_exists(public_path('storage/' . $attachment->path))) {
                    Storage::disk('public')->delete($attachment->path);
                }
            } catch (\Exception $e) {
                // Abaikan error
            }
        }

        $attachment->delete();

        return back()->with('status', 'Lampiran dihapus.');
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:' . implode(',', array_keys(Announcement::categories()))],
            'status' => ['required', 'in:' . implode(',', [Announcement::STATUS_DRAFT, Announcement::STATUS_PUBLISHED])],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'files' => ['nullable', 'array'],
            'files.*' => ['nullable', 'file', 'max:10240'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    protected function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (
            Announcement::where('slug', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '<>', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function handleAttachments(Request $request, Announcement $announcement): void
    {
        // Documents
        $files = collect(Arr::wrap($request->file('files')))
            ->flatten()
            ->filter(fn($file) => $file instanceof \Illuminate\Http\UploadedFile);
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            $path = safe_store($file, 'announcements/files');
            $announcement->attachments()->create([
                'type' => 'file',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        // Images
        $images = collect(Arr::wrap($request->file('images')))
            ->flatten()
            ->filter(fn($file) => $file instanceof \Illuminate\Http\UploadedFile);
        foreach ($images as $image) {
            if (!$image) {
                continue;
            }
            $path = safe_store($image, 'announcements/images');
            $announcement->attachments()->create([
                'type' => 'image',
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime' => $image->getClientMimeType(),
                'size' => $image->getSize(),
            ]);
        }
    }
}
