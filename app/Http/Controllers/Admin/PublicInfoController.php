<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicInfoHour;
use App\Models\PublicInfoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicInfoController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('public_info_settings')) {
            return back()->with('error', 'Tabel pengaturan informasi publik belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $setting = PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting([
            'section_title' => 'Informasi Publik',
            'section_subtitle' => 'Akses informasi, jam layanan, pengumuman banner, dan lokasi kantor desa',
            'request_title' => 'Permohonan Informasi Publik',
            'request_description' => 'Ajukan permohonan informasi publik desa secara mudah dan transparan.',
            'request_button_label' => 'Ajukan Permohonan',
            'hours_title' => 'Jam Kerja',
            'hours_description' => 'Jam pelayanan kantor Desa Tanjung Kesuma.',
            'hours_note' => 'Buka Senin-Jumat, 08.00-16.00 WIB',
            'map_title' => 'Maps Lokasi Kantor Desa',
            'map_description' => 'Temukan lokasi kantor desa pada peta berikut.',
            'footer_address' => 'Desa Tanjung Kesuma, Kecamatan Purbolinggo, Kabupaten Lampung Timur, Provinsi Lampung, Indonesia',
            'whatsapp_number' => '6281234567890',
        ]);

        $hours = $this->ensureHours($setting);
        $defaultFooterLinks = [
            ['label' => 'Profil', 'url' => '#profil'],
            ['label' => 'Pemerintahan', 'url' => '#pemerintahan'],
            ['label' => 'Program & Layanan', 'url' => '#layanan'],
            ['label' => 'Berita & Pengumuman', 'url' => '#berita'],
            ['label' => 'Statistik', 'url' => '#statistik'],
            ['label' => 'Kontak', 'url' => '#kontak'],
        ];
        $footerLinks = $setting->footer_links ?? $defaultFooterLinks;

        return view('admin.public-info.index', compact('setting', 'hours', 'footerLinks'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'update_section' => ['required', 'string', 'in:main,request,hours,map,footer_links'],
            'section_title' => ['sometimes', 'string', 'max:150'],
            'section_subtitle' => ['sometimes', 'nullable', 'string', 'max:200'],
            'request_title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'request_description' => ['sometimes', 'nullable', 'string'],
            'request_button_label' => ['sometimes', 'nullable', 'string', 'max:80'],
            'request_button_url' => ['sometimes', 'nullable', 'url'],
            'request_image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'hours_title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'hours_description' => ['sometimes', 'nullable', 'string', 'max:200'],
            'hours_note' => ['sometimes', 'nullable', 'string', 'max:200'],
            'map_title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'map_description' => ['sometimes', 'nullable', 'string', 'max:200'],
            'map_embed_url' => ['sometimes', 'nullable', 'string'],
            'footer_address' => ['sometimes', 'nullable', 'string', 'max:350'],
            'whatsapp_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'footer_links' => ['sometimes', 'array'],
            'footer_links.*.label' => ['nullable', 'string', 'max:120'],
            'footer_links.*.url' => ['nullable', 'string', 'max:250'],
            'is_published' => ['sometimes', 'nullable'],
            'hours' => ['sometimes', 'array'],
            'hours.*.open_time' => ['nullable', 'string', 'max:20'],
            'hours.*.close_time' => ['nullable', 'string', 'max:20'],
            'hours.*.is_closed' => ['nullable'],
            'hours.*.note' => ['nullable', 'string', 'max:100'],
        ]);

        $setting = PublicInfoSetting::first() ?? new PublicInfoSetting();
        $section = $data['update_section'];

        // Core fields update
        $setting->fill(collect($data)->except(['update_section', 'request_image', 'hours', 'footer_links', 'is_published'])->toArray());

        if ($section === 'map') {
            $setting->is_published = $request->boolean('is_published', false);
        }

        if ($section === 'request' && $request->hasFile('request_image')) {
            if ($setting->request_image && Storage::disk('public')->exists($setting->request_image)) {
                Storage::disk('public')->delete($setting->request_image);
            }
            $path = $request->file('request_image')->store('public-info', 'public');
            $setting->request_image = $path;
        }

        if ($section === 'footer_links') {
            $footerLinks = collect($request->input('footer_links', []))
                ->filter(function ($item) {
                    $label = trim($item['label'] ?? '');
                    $url = trim($item['url'] ?? '');
                    return $label !== '' || $url !== '';
                })
                ->map(function ($item) {
                    return [
                        'label' => trim($item['label'] ?? ''),
                        'url' => trim($item['url'] ?? ''),
                    ];
                })
                ->values()
                ->toArray();
            $setting->footer_links = $footerLinks;
            // Only clear socials if we are explicitly updating the links/socials part
            $setting->footer_socials = [];
        }

        $setting->save();

        if ($section === 'hours') {
            $hoursInput = $request->input('hours');
            if (is_array($hoursInput)) {
                foreach ($hoursInput as $day => $payload) {
                    $dayInt = (int) $day;
                    PublicInfoHour::updateOrCreate(
                        ['public_info_setting_id' => $setting->id, 'day_of_week' => $dayInt],
                        [
                            'open_time' => $payload['open_time'] ?? null,
                            'close_time' => $payload['close_time'] ?? null,
                            'is_closed' => isset($payload['is_closed']),
                            'note' => $payload['note'] ?? null,
                            'sort_order' => $dayInt,
                        ]
                    );
                }
            }
        }

        return back()->with('status', 'Pengaturan Informasi Publik berhasil diperbarui.');
    }

    private function ensureHours(PublicInfoSetting $setting)
    {
        $days = [
            0 => 'Senin',
            1 => 'Selasa',
            2 => 'Rabu',
            3 => 'Kamis',
            4 => 'Jumat',
            5 => 'Sabtu',
            6 => 'Minggu',
        ];

        if (!$setting->exists) {
            $setting->save();
        }

        $hours = collect();
        foreach ($days as $day => $label) {
            $record = $setting->hours()->where('day_of_week', $day)->first();
            if (!$record) {
                $record = $setting->hours()->create([
                    'day_of_week' => $day,
                    'open_time' => in_array($day, [5, 6]) ? null : '08.00',
                    'close_time' => in_array($day, [5, 6]) ? null : '16.00',
                    'is_closed' => in_array($day, [5, 6]),
                    'sort_order' => $day,
                ]);
            }
            $record->day_label = $label;
            $hours->push($record);
        }

        return $hours;
    }
}
