<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'excerpt',
        'category',
        'status',
        'published_at',
        'created_by',
        'views',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public const CATEGORY_DESA = 'desa';
    public const CATEGORY_DAERAH = 'daerah';
    public const CATEGORY_PUSAT = 'pusat';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public static function categories(): array
    {
        return [
            self::CATEGORY_DESA => 'Pengumuman Desa',
            self::CATEGORY_DAERAH => 'Pengumuman Pemerintah Daerah',
            self::CATEGORY_PUSAT => 'Pengumuman Pemerintah Pusat',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function markPublished(): void
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = $this->published_at ?? now();
        $this->save();
    }

    public function getExcerptAttribute($value)
    {
        return $value ?: Str::limit(strip_tags($this->body), 180);
    }

    public function attachments()
    {
        return $this->hasMany(AnnouncementAttachment::class)->orderBy('sort_order')->orderBy('id');
    }

    public function fileAttachments()
    {
        return $this->attachments()->where('type', 'file');
    }

    public function imageAttachments()
    {
        return $this->attachments()->where('type', 'image');
    }
}
