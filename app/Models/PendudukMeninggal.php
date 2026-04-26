<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendudukMeninggal extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tanggal_meninggal' => 'date',
    ];

    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class);
    }
}
