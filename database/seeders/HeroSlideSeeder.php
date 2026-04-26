<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $slides = [
            [
                'title' => 'Selamat Datang di Desa Tanjung Kesuma',
                'subtitle' => 'Desa berbudaya, mandiri, dan ramah digital',
                'description' => 'Kelola layanan publik dan informasi desa dalam satu platform modern.',
                'background_url' => 'img/Banner/gambar_desa_1.png',
                'button_label' => 'Layanan Publik',
                'button_url' => '#layanan',
                'status' => 'active',
                'is_profile_slide' => false,
                'sort_order' => 1,
            ],
            [
                'title' => 'Profil Desa Tanjung Kesuma',
                'subtitle' => 'Potensi pertanian, budaya, dan pariwisata',
                'description' => 'Menampilkan visi desa, data kependudukan, dan agenda pembangunan.',
                'background_url' => 'img/Banner/gambar_desa_2.jpg',
                'button_label' => 'Profil Desa',
                'button_url' => '#profil',
                'status' => 'active',
                'is_profile_slide' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Berita & Agenda Desa',
                'subtitle' => 'Konten terbaru dari pemerintahan desa',
                'description' => 'Pantau agenda, pengumuman, dan berita penting desa dari dashboard ini.',
                'background_url' => 'img/Banner/gambar_desa_3.jpg',
                'button_label' => 'Informasi',
                'button_url' => '#berita-terkini',
                'status' => 'draft',
                'is_profile_slide' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Layanan Digital Desa',
                'subtitle' => 'Inovasi untuk warga mandiri',
                'description' => 'Akses database warga, pengaduan, dan pelaporan dalam satu klik.',
                'background_url' => 'img/Banner/gambar_desa_4.jpg',
                'button_label' => 'Mulai Sekarang',
                'button_url' => '#layanan-digital',
                'status' => 'active',
                'is_profile_slide' => false,
                'sort_order' => 4,
            ],
            [
                'title' => 'Pertanian Pintar',
                'subtitle' => 'Stok pangan dan panen terkini',
                'description' => 'Modul informasi pertanian berbasis data untuk petani desa.',
                'background_url' => 'img/Banner/gambar_desa_5.jpg',
                'button_label' => 'Kunjungi Unit',
                'button_url' => '#pertanian',
                'status' => 'draft',
                'is_profile_slide' => false,
                'sort_order' => 5,
            ],
            [
                'title' => 'Kegiatan Sosial',
                'subtitle' => 'Gotong royong & pelayanan masyarakat',
                'description' => 'Jadwal program mingguan bersama aparat desa dan masyarakat.',
                'background_url' => 'img/Banner/gambar_desa_6.jpg',
                'button_label' => 'Agenda',
                'button_url' => '#agenda',
                'status' => 'archived',
                'is_profile_slide' => false,
                'sort_order' => 6,
            ],
            [
                'title' => 'Pembangunan Infrastruktur',
                'subtitle' => 'Proyek publik dalam pelaksanaan',
                'description' => 'Pantau status jalan, irigasi, dan bangunan publik terbaru.',
                'background_url' => 'img/Banner/gambar_desa_7.jpg',
                'button_label' => 'Lihat Progress',
                'button_url' => '#infrastruktur',
                'status' => 'active',
                'is_profile_slide' => false,
                'sort_order' => 7,
            ],
            [
                'title' => 'Kampanye Lingkungan',
                'subtitle' => 'Kebersihan dan konservasi desa',
                'description' => 'Gerakan sadar lingkungan, bank sampah, dan edukasi warga.',
                'background_url' => 'img/Banner/gambar_desa_8.jpg',
                'button_label' => 'Gabung Gerakan',
                'button_url' => '#lingkungan',
                'status' => 'draft',
                'is_profile_slide' => false,
                'sort_order' => 8,
            ],
        ];

        foreach ($slides as $slide) {
            HeroSlide::updateOrCreate([
                'title' => $slide['title'],
            ], $slide);
        }
    }
}
