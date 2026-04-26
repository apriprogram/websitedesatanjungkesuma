<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Banner Utama (Hero Slides)
        DB::table('hero_slides')->insert([
            [
                'title' => 'Selamat Datang di Desa Tanjung Kesuma',
                'subtitle' => 'Mewujudkan Desa Mandiri dan Sejahtera',
                'description' => 'Website Resmi Pemerintah Desa Tanjung Kesuma, Kecamatan Purbolinggo, Kabupaten Lampung Timur.',
                'background_url' => '/assets/default/bg-login.png',
                'button_label' => 'Jelajahi Desa',
                'button_url' => '/profil/tentang-kami',
                'status' => 'active',
                'is_profile_slide' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Potensi Desa',
                'subtitle' => 'Unggulan Pertanian dan Perkebunan',
                'description' => 'Membangun ekonomi desa melalui pengembangan potensi lokal yang berkelanjutan.',
                'background_url' => '/assets/default/bg-login.png',
                'button_label' => 'Lihat Potensi',
                'button_url' => '/potensi',
                'status' => 'active',
                'is_profile_slide' => false,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 2. Banner Info (Info Media Banners)
        DB::table('info_media_banners')->insert([
            [
                'title' => 'Layanan Kependudukan',
                'subtitle' => 'Mudah dan Cepat',
                'description' => 'Kami siap melayani pembuatan KTP, KK, dan Akta Kelahiran dengan proses yang transparan.',
                'image_url' => '/assets/default/bg-login.png',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Program Bantuan',
                'subtitle' => 'Tepat Sasaran',
                'description' => 'Informasi penyaluran bantuan sosial untuk warga yang membutuhkan.',
                'image_url' => '/assets/default/bg-login.png',
                'status' => 'active',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 3. Infografis (Infographic Banners)
        DB::table('infographic_banners')->insert([
            [
                'title' => 'Transparansi APBDesa',
                'subtitle' => 'Tahun Anggaran 2025',
                'description' => 'Laporan realisasi anggaran desa yang akuntabel dan transparan.',
                'image_url' => '/assets/default/bg-login.png',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Statistik Penduduk',
                'subtitle' => 'Data Terkini',
                'description' => 'Grafik pertumbuhan dan komposisi penduduk Desa Tanjung Kesuma.',
                'image_url' => '/assets/default/bg-login.png',
                'status' => 'active',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
