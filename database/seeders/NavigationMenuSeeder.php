<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NavigationMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menuTree = [
            [
                'title' => 'Beranda',
                'type' => 'custom',
                'url' => '/',
                'icon' => 'fa-solid fa-house',
                'children' => [],
            ],
            [
                'title' => 'Profil Desa',
                'type' => 'custom',
                'url' => '#profil',
                'icon' => 'fa-solid fa-landmark',
                'children' => [
                    ['title' => 'Sejarah', 'url' => '#sejarah'],
                    ['title' => 'Visi & Misi', 'url' => '#visi-misi'],
                    ['title' => 'Pemerintahan', 'url' => '#pemerintahan'],
                    ['title' => 'Geografis', 'url' => '#geografis'],
                ],
            ],
            [
                'title' => 'Layanan',
                'type' => 'custom',
                'url' => '#layanan',
                'icon' => 'fa-solid fa-headset',
                'children' => [
                    ['title' => 'Layanan Administrasi', 'url' => '#layanan-publik'],
                    ['title' => 'Surat Menyurat', 'url' => '#layanan-surat'],
                    ['title' => 'Pengajuan Bantuan', 'url' => '#bantuan'],
                    ['title' => 'Pelaporan', 'url' => '#pelaporan'],
                ],
            ],
            [
                'title' => 'Data Desa',
                'type' => 'custom',
                'url' => '#statistik',
                'icon' => 'fa-solid fa-chart-pie',
                'children' => [
                    ['title' => 'Statistik Penduduk', 'url' => '#statistik'],
                    ['title' => 'Data Wilayah', 'url' => '#demo-wilayah'],
                    ['title' => 'Pendidikan', 'url' => '#demo-pendidikan-ditempuh'],
                    ['title' => 'Pekerjaan', 'url' => '#demo-pekerjaan'],
                    ['title' => 'Agama', 'url' => '#demo-agama'],
                ],
            ],
            [
                'title' => 'Informasi',
                'type' => 'custom',
                'url' => '#berita',
                'icon' => 'fa-solid fa-newspaper',
                'children' => [
                    ['title' => 'Berita Desa', 'url' => route('news.index')],
                    ['title' => 'Pengumuman', 'url' => route('announcements.index')],
                    ['title' => 'Agenda Desa', 'url' => '#agenda'],
                    ['title' => 'Galeri Video', 'url' => route('videos.index')],
                ],
            ],
            [
                'title' => 'Anggaran',
                'type' => 'custom',
                'url' => '#transparansi',
                'icon' => 'fa-solid fa-coins',
                'children' => [
                    ['title' => 'Transparansi Anggaran', 'url' => '#transparansi'],
                    ['title' => 'Program Pembangunan', 'url' => '#pembangunan'],
                ],
            ],
            [
                'title' => 'Kontak',
                'type' => 'custom',
                'url' => '#kontak',
                'icon' => 'fa-solid fa-envelope',
                'children' => [
                    ['title' => 'Hubungi Kami', 'url' => '#kontak'],
                    ['title' => 'Lokasi Kantor', 'url' => '#lokasi'],
                    ['title' => 'Jam Layanan', 'url' => '#jam-layanan'],
                ],
            ],
        ];

        NavigationMenu::query()->delete();

        $position = 1;
        foreach ($menuTree as $parent) {
            $parentItem = NavigationMenu::create([
                'parent_id' => null,
                'title' => $parent['title'],
                'icon' => $parent['icon'] ?? null,
                'type' => $parent['type'] ?? 'custom',
                'page_slug' => $parent['type'] === 'page' ? ($parent['page_slug'] ?? Str::slug($parent['title'])) : null,
                'url' => $parent['type'] === 'custom' ? ($parent['url'] ?? '#') : null,
                'position' => $position++,
                'is_active' => true,
                'target_blank' => false,
            ]);

            $childPos = 1;
            foreach ($parent['children'] as $child) {
                NavigationMenu::create([
                    'parent_id' => $parentItem->id,
                    'title' => $child['title'],
                    'icon' => $child['icon'] ?? null,
                    'type' => $child['type'] ?? 'custom',
                    'page_slug' => ($child['type'] ?? 'custom') === 'page' ? ($child['page_slug'] ?? Str::slug($child['title'])) : null,
                    'url' => ($child['type'] ?? 'custom') === 'custom' ? ($child['url'] ?? '#') : null,
                    'position' => $childPos++,
                    'is_active' => true,
                    'target_blank' => false,
                ]);
            }
        }
    }
}
