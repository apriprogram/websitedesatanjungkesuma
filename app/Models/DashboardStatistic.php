<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'label',
        'value',
        'order_column',
        'meta',
        'updated_by',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
