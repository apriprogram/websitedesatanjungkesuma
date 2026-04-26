<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PagesAndNavSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Profil Desa',
                'slug' => 'profil-desa',
                'meta_title' => 'Profil Desa Tanjung Kesuma',
                'meta_description' => 'Gambaran umum Desa Tanjung Kesuma, sejarah singkat, dan informasi pemerintah desa.',
                'content' => '<p>Profil singkat Desa Tanjung Kesuma, meliputi sejarah, kondisi geografis, demografi, serta struktur pemerintahan desa.</p>',
            ],
            [
                'title' => 'Sejarah Desa',
                'slug' => 'sejarah-desa',
                'meta_title' => 'Sejarah Desa Tanjung Kesuma',
                'meta_description' => 'Perjalanan sejarah Desa Tanjung Kesuma dari masa ke masa.',
                'content' => '<p>Jejak perkembangan Desa Tanjung Kesuma, tokoh pendiri, dan peristiwa penting yang membentuk desa hingga saat ini.</p>',
            ],
            [
                'title' => 'Visi dan Misi',
                'slug' => 'visi-misi',
                'meta_title' => 'Visi dan Misi Desa',
                'meta_description' => 'Arah pembangunan Desa Tanjung Kesuma melalui visi dan misi yang disepakati.',
                'content' => '<p><strong>Visi:</strong> Mewujudkan desa maju, mandiri, dan sejahtera.<br><strong>Misi:</strong> Peningkatan pelayanan publik, penguatan ekonomi warga, dan pengelolaan sumber daya berkelanjutan.</p>',
            ],
            [
                'title' => 'Pemerintahan Desa',
                'slug' => 'pemerintahan-desa',
                'meta_title' => 'Struktur Pemerintahan Desa',
                'meta_description' => 'Struktur organisasi, perangkat desa, dan layanan pemerintahan Desa Tanjung Kesuma.',
                'content' => '<p>Struktur organisasi pemerintahan desa, tugas dan fungsi perangkat, serta layanan administrasi bagi masyarakat.</p>',
            ],
            [
                'title' => 'Wilayah & Demografi',
                'slug' => 'wilayah-desa',
                'meta_title' => 'Wilayah dan Demografi Desa',
                'meta_description' => 'Peta wilayah, batas desa, dan komposisi demografi warga Tanjung Kesuma.',
                'content' => '<p>Informasi batas wilayah, luas area, pembagian dusun/RT/RW, serta data demografi penduduk.</p>',
            ],
            [
                'title' => 'Pelayanan Administrasi',
                'slug' => 'layanan-administrasi',
                'meta_title' => 'Pelayanan Administrasi Desa',
                'meta_description' => 'Panduan layanan administrasi kependudukan dan surat-menyurat.',
                'content' => '<p>Jenis layanan administrasi yang tersedia di kantor desa, persyaratan dokumen, dan alur pelayanan.</p>',
            ],
            [
                'title' => 'Surat Menyurat',
                'slug' => 'surat-menyurat',
                'meta_title' => 'Surat Menyurat Desa',
                'meta_description' => 'Informasi pengajuan surat keterangan, domisili, dan dokumen lain.',
                'content' => '<p>Panduan pengajuan surat keterangan, domisili, usaha, dan formulir yang dapat diunduh.</p>',
            ],
            [
                'title' => 'Pengaduan Warga',
                'slug' => 'pengaduan-warga',
                'meta_title' => 'Pengaduan Warga',
                'meta_description' => 'Saluran resmi untuk menyampaikan aspirasi dan keluhan.',
                'content' => '<p>Saluran pengaduan, etika penyampaian keluhan, dan mekanisme tindak lanjut oleh pemerintah desa.</p>',
            ],
            [
                'title' => 'Statistik Penduduk',
                'slug' => 'statistik-penduduk',
                'meta_title' => 'Statistik Penduduk Desa',
                'meta_description' => 'Ringkasan statistik kependudukan Desa Tanjung Kesuma.',
                'content' => '<p>Data ringkas komposisi penduduk berdasarkan jenis kelamin, usia, pendidikan, pekerjaan, dan agama.</p>',
            ],
            [
                'title' => 'Data Wilayah',
                'slug' => 'data-wilayah',
                'meta_title' => 'Data Wilayah Desa',
                'meta_description' => 'Informasi pembagian wilayah, dusun, RT/RW, dan fasilitas umum.',
                'content' => '<p>Peta dan daftar dusun/RT/RW beserta fasilitas umum seperti sekolah, puskesmas, dan tempat ibadah.</p>',
            ],
            [
                'title' => 'Pendidikan',
                'slug' => 'data-pendidikan',
                'meta_title' => 'Data Pendidikan Desa',
                'meta_description' => 'Data capaian pendidikan warga Desa Tanjung Kesuma.',
                'content' => '<p>Rekapitulasi pendidikan terakhir warga dan program peningkatan kapasitas di bidang pendidikan.</p>',
            ],
            [
                'title' => 'Pekerjaan',
                'slug' => 'data-pekerjaan',
                'meta_title' => 'Data Pekerjaan Desa',
                'meta_description' => 'Data profesi dan mata pencaharian utama warga desa.',
                'content' => '<p>Distribusi pekerjaan warga dan potensi ekonomi lokal yang dikembangkan.</p>',
            ],
            [
                'title' => 'Agama',
                'slug' => 'data-agama',
                'meta_title' => 'Data Agama Desa',
                'meta_description' => 'Komposisi pemeluk agama di Desa Tanjung Kesuma.',
                'content' => '<p>Statistik keberagaman agama di desa dan kegiatan keagamaan yang difasilitasi.</p>',
            ],
            [
                'title' => 'Transparansi Anggaran',
                'slug' => 'transparansi-anggaran',
                'meta_title' => 'Transparansi Anggaran Desa',
                'meta_description' => 'Informasi pengelolaan keuangan dan APBDes.',
                'content' => '<p>Rangkuman APBDes, realisasi anggaran, dan laporan keuangan desa.</p>',
            ],
            [
                'title' => 'Program Pembangunan',
                'slug' => 'program-pembangunan',
                'meta_title' => 'Program Pembangunan Desa',
                'meta_description' => 'Rencana dan progres program pembangunan desa.',
                'content' => '<p>Daftar program pembangunan fisik dan non-fisik beserta status pelaksanaannya.</p>',
            ],
            [
                'title' => 'Kontak Desa',
                'slug' => 'kontak',
                'meta_title' => 'Kontak Desa Tanjung Kesuma',
                'meta_description' => 'Informasi kontak dan lokasi kantor desa.',
                'content' => '<p>Alamat kantor desa, nomor telepon, email resmi, dan jam layanan.</p>',
            ],
            [
                'title' => 'Lokasi Kantor',
                'slug' => 'lokasi-kantor',
                'meta_title' => 'Lokasi Kantor Desa',
                'meta_description' => 'Peta lokasi kantor Desa Tanjung Kesuma.',
                'content' => '<p>Peta lokasi kantor desa dan panduan rute menuju kantor layanan.</p>',
            ],
            [
                'title' => 'Jam Layanan',
                'slug' => 'jam-layanan',
                'meta_title' => 'Jam Layanan Desa',
                'meta_description' => 'Waktu operasional layanan administrasi desa.',
                'content' => '<p>Jam operasional pelayanan administrasi serta informasi hari libur layanan.</p>',
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                array_merge($pageData, [
                    'status' => 'published',
                    'published_at' => now(),
                ])
            );
        }

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
                'url' => '#',
                'icon' => 'fa-solid fa-landmark',
                'children' => [
                    ['title' => 'Profil Desa', 'type' => 'page', 'page_slug' => 'profil-desa'],
                    ['title' => 'Sejarah', 'type' => 'page', 'page_slug' => 'sejarah-desa'],
                    ['title' => 'Visi & Misi', 'type' => 'page', 'page_slug' => 'visi-misi'],
                    ['title' => 'Pemerintahan', 'type' => 'page', 'page_slug' => 'pemerintahan-desa'],
                    ['title' => 'Wilayah & Demografi', 'type' => 'page', 'page_slug' => 'wilayah-desa'],
                ],
            ],
            [
                'title' => 'Layanan',
                'type' => 'custom',
                'url' => '#',
                'icon' => 'fa-solid fa-headset',
                'children' => [
                    ['title' => 'Pelayanan Administrasi', 'type' => 'page', 'page_slug' => 'layanan-administrasi'],
                    ['title' => 'Surat Menyurat', 'type' => 'page', 'page_slug' => 'surat-menyurat'],
                    ['title' => 'Pengaduan Warga', 'type' => 'page', 'page_slug' => 'pengaduan-warga'],
                ],
            ],
            [
                'title' => 'Data Desa',
                'type' => 'custom',
                'url' => '#',
                'icon' => 'fa-solid fa-chart-pie',
                'children' => [
                    ['title' => 'Statistik Penduduk', 'type' => 'page', 'page_slug' => 'statistik-penduduk'],
                    ['title' => 'Data Wilayah', 'type' => 'page', 'page_slug' => 'data-wilayah'],
                    ['title' => 'Pendidikan', 'type' => 'page', 'page_slug' => 'data-pendidikan'],
                    ['title' => 'Pekerjaan', 'type' => 'page', 'page_slug' => 'data-pekerjaan'],
                    ['title' => 'Agama', 'type' => 'page', 'page_slug' => 'data-agama'],
                ],
            ],
            [
                'title' => 'Informasi',
                'type' => 'custom',
                'url' => '#',
                'icon' => 'fa-solid fa-newspaper',
                'children' => [
                    ['title' => 'Berita Desa', 'type' => 'custom', 'url' => route('news.index')],
                    ['title' => 'Pengumuman', 'type' => 'custom', 'url' => route('announcements.index')],
                ],
            ],
            [
                'title' => 'Anggaran',
                'type' => 'custom',
                'url' => '#',
                'icon' => 'fa-solid fa-coins',
                'children' => [
                    ['title' => 'Transparansi Anggaran', 'type' => 'page', 'page_slug' => 'transparansi-anggaran'],
                    ['title' => 'Program Pembangunan', 'type' => 'page', 'page_slug' => 'program-pembangunan'],
                ],
            ],
            [
                'title' => 'Kontak',
                'type' => 'custom',
                'url' => '#',
                'icon' => 'fa-solid fa-envelope',
                'children' => [
                    ['title' => 'Kontak Desa', 'type' => 'page', 'page_slug' => 'kontak'],
                    ['title' => 'Lokasi Kantor', 'type' => 'page', 'page_slug' => 'lokasi-kantor'],
                    ['title' => 'Jam Layanan', 'type' => 'page', 'page_slug' => 'jam-layanan'],
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
                'page_slug' => ($parent['type'] ?? 'custom') === 'page' ? ($parent['page_slug'] ?? Str::slug($parent['title'])) : null,
                'url' => ($parent['type'] ?? 'custom') === 'custom' ? ($parent['url'] ?? '#') : null,
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
