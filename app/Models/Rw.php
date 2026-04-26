<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rw extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function rts(): HasMany
    {
        return $this->hasMany(Rt::class);
    }

    public function penduduks(): HasMany
    {
        return $this->hasMany(Penduduk::class);
    }
}
