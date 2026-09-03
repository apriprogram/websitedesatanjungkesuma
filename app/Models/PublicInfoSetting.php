<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicInfoSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_title',
        'section_subtitle',
        'request_title',
        'request_description',
        'request_button_label',
        'request_button_url',
        'request_image',
        'hours_title',
        'hours_description',
        'hours_note',
        'map_title',
        'map_description',
        'map_embed_url',
        'footer_address',
        'footer_links',
        'footer_socials',
        'whatsapp_number',
        'is_published',
        'show_budget_section',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_budget_section' => 'boolean',
        'footer_links' => 'array',
        'footer_socials' => 'array',
    ];

    public function hours(): HasMany
    {
        return $this->hasMany(PublicInfoHour::class)->orderBy('sort_order')->orderBy('day_of_week');
    }
}
