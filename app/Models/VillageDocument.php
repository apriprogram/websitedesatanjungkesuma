<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VillageDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'document_category_id',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'year',
        'is_public',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
