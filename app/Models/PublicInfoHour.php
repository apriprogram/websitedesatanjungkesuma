<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicInfoHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_info_setting_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_closed',
        'note',
        'sort_order',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(PublicInfoSetting::class, 'public_info_setting_id');
    }
}
