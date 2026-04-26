<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfographicBanner extends Model
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
     * Pastikan path gambar infografis selalu mengarah ke folder infographics.
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'infographics/') ? $value : "infographics/{$value}";
    }
}
