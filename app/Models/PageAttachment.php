<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'type',
        'path',
        'original_name',
        'mime',
        'size',
        'sort_order',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function getUrlAttribute(): string
    {
        $path = (string) $this->path;
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        if ($path && file_exists(public_path('storage/' . $path))) {
            return asset('storage/' . $path);
        }
        
        if ($path && file_exists(public_path($path))) {
            return asset($path);
        }

        return '';
    }
}
