<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PageAttachment;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'feature_image',
        'meta_title',
        'meta_description',
        'content',
        'published_at',
        'views',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(PageAttachment::class)->orderBy('sort_order')->orderBy('id');
    }
}
