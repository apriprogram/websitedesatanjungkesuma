<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rt extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class);
    }

    public function penduduks(): HasMany
    {
        return $this->hasMany(Penduduk::class);
    }
}
