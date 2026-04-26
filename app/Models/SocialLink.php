<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'icon',
        'url',
        'sort_order',
    ];

    public function scopeActive($query)
    {
        return $query->whereNotNull('url')->where('url', '!=', '');
    }

    public function scopeIcons($query)
    {
        return $query->where('type', 'icon');
    }

    public function scopeTextLinks($query)
    {
        return $query->where('type', 'text');
    }
}
