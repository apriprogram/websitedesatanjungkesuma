<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dusun extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function rws(): HasMany
    {
        return $this->hasMany(Rw::class);
    }

    public function rts(): HasManyThrough
    {
        return $this->hasManyThrough(Rt::class, Rw::class);
    }

    public function penduduks(): HasMany
    {
        return $this->hasMany(Penduduk::class);
    }
}
