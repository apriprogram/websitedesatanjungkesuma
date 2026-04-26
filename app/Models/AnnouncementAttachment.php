<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'type',
        'path',
        'original_name',
        'mime',
        'size',
        'sort_order',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function getUrlAttribute(): string
    {
        $path = $this->path;
        if (is_array($path)) {
            $path = reset($path) ?: '';
        }
        $path = (string) $path;

        if ($path === '') {
            return '';
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }
        return asset($path);
    }
}
