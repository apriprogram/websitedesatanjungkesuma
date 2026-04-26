<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BudgetItem extends Model
{
    use HasFactory;

    public const CATEGORY_PELAKSANAAN = 'pelaksanaan';
    public const CATEGORY_PENDAPATAN = 'pendapatan';
    public const CATEGORY_PEMBELANJAAN = 'pembelanjaan';

    protected $fillable = [
        'year',
        'category',
        'subcategory',
        'description',
        'anggaran',
        'realisasi',
        'icon',
        'order_no',
        'is_published',
        'is_active_year',
    ];

    protected $casts = [
        'year' => 'integer',
        'anggaran' => 'float',
        'realisasi' => 'float',
        'order_no' => 'integer',
        'is_published' => 'boolean',
        'is_active_year' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(category, 'pelaksanaan', 'pendapatan', 'pembelanjaan')")
            ->orderBy('order_no')
            ->orderBy('id');
    }

    public function scopeActiveYear(Builder $query): Builder
    {
        if (!Schema::hasColumn($this->getTable(), 'is_active_year')) {
            return $query;
        }

        return $query->where('is_active_year', true);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->anggaran <= 0) {
            return 0;
        }

        $percent = round(($this->realisasi / $this->anggaran) * 100);

        return (int) max(0, $percent);
    }
}
