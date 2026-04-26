<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'background_url',
        'button_label',
        'button_url',
        'status',
        'is_profile_slide',
        'sort_order',
    ];

    /**
     * Pastikan path background hero selalu mengarah ke folder hero-banners.
     */
    public function getBackgroundUrlAttribute($value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'hero-banners/') ? $value : "hero-banners/{$value}";
    }
}
