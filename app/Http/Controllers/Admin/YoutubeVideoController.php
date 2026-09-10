<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YoutubeVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class YoutubeVideoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 12);
        $perPage = in_array($perPage, [5, 10, 12, 25, 50], true) ? $perPage : 12;
        $search = trim((string) $request->get('search', ''));

        $videos = YoutubeVideo::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('youtube_url', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => YoutubeVideo::count(),
            'published' => YoutubeVideo::where('is_published', true)->count(),
            'draft' => YoutubeVideo::where('is_published', false)->count(),
            'publishedThisMonth' => YoutubeVideo::where('is_published', true)
                ->whereMonth('published_at', now()->month)
                ->whereYear('published_at', now()->year)
                ->count(),
            'newThisMonth' => YoutubeVideo::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $editing = null;
        if ($request->filled('edit')) {
            $editing = YoutubeVideo::find($request->integer('edit'));
        }

        return view('admin.youtube-videos.index', compact('videos', 'stats', 'editing', 'perPage', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validateVideo($request);

        $youtubeId = YoutubeVideo::extractId($payload['youtube_url']);
        if (!$youtubeId) {
            return back()->withInput()->withErrors(['youtube_url' => 'Link YouTube tidak valid.']);
        }

        $payload['youtube_id'] = $youtubeId;
        $payload = YoutubeVideo::publishedPayload($payload);

        YoutubeVideo::create($payload);

        return redirect()->route('admin.youtube-videos.index')->with('status', 'Video berhasil ditambahkan.');
    }

    public function update(Request $request, YoutubeVideo $youtubeVideo): RedirectResponse
    {
        $payload = $this->validateVideo($request, $youtubeVideo);

        $youtubeId = YoutubeVideo::extractId($payload['youtube_url']);
        if (!$youtubeId) {
            return back()->withInput()->withErrors(['youtube_url' => 'Link YouTube tidak valid.']);
        }

        $payload['youtube_id'] = $youtubeId;
        $payload = YoutubeVideo::publishedPayload($payload);

        $youtubeVideo->update($payload);

        return redirect()->route('admin.youtube-videos.index')->with('status', 'Video berhasil diperbarui.');
    }

    public function toggle(YoutubeVideo $youtubeVideo): RedirectResponse
    {
        $newStatus = ! $youtubeVideo->is_published;
        $youtubeVideo->update([
            'is_published' => $newStatus,
            'published_at' => $newStatus
                ? ($youtubeVideo->published_at ?? now())
                : $youtubeVideo->published_at,
        ]);

        return back()->with('status', 'Status video diperbarui.');
    }

    public function destroy(YoutubeVideo $youtubeVideo): RedirectResponse
    {
        $youtubeVideo->delete();

        return back()->with('status', 'Video berhasil dihapus.');
    }

    protected function validateVideo(Request $request, ?YoutubeVideo $youtubeVideo = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'youtube_url' => ['required', 'string', 'max:255'],
            'thumbnail_url' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
