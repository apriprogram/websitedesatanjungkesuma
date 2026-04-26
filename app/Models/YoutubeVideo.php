<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YoutubeVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'youtube_url',
        'youtube_id',
        'thumbnail_url',
        'description',
        'sort_order',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('created_at');
    }

    public function getEmbedUrlAttribute(): string
    {
        $id = $this->youtube_id ?: static::extractId((string) $this->youtube_url) ?? '';
        return $id ? "https://www.youtube.com/embed/{$id}" : '';
    }

    public function getThumbnailAttribute(): string
    {
        if (!empty($this->thumbnail_url)) {
            return $this->thumbnail_url;
        }

        $id = $this->youtube_id ?: static::extractId((string) $this->youtube_url) ?? null;
        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : '';
    }

    public static function extractId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Full URL patterns
        $patterns = [
            '/v=([a-zA-Z0-9_-]{6,})/i',
            '#youtu\.be/([a-zA-Z0-9_-]{6,})#i',
            '#youtube\.com/embed/([a-zA-Z0-9_-]{6,})#i',
            '#youtube\.com/shorts/([a-zA-Z0-9_-]{6,})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $match)) {
                return $match[1];
            }
        }

        // Already only ID
        if (preg_match('/^[a-zA-Z0-9_-]{6,}$/', $url)) {
            return $url;
        }

        return null;
    }

    public static function publishedPayload(array $payload): array
    {
        $payload['is_published'] = !empty($payload['is_published']);

        if ($payload['is_published'] && empty($payload['published_at'])) {
            $payload['published_at'] = Carbon::now();
        }

        return $payload;
    }
}
