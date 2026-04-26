<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'sort_order',
    ];

    public function scopeActive($query)
    {
        return $query->whereNotNull('url')->where('url', '!=', '');
    }
}
