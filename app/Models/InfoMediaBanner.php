<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoMediaBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image_url',
        'status',
        'sort_order',
    ];

    /**
     * Pastikan path gambar banner info selalu mengarah ke folder banner-info.
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'banner-info/') ? $value : "banner-info/{$value}";
    }
}
