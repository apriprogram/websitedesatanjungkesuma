<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Realistic page titles for a village website
        $titles = [
            'Potensi Pertanian Desa',
            'Produk Unggulan UMKM',
            'Lembaga Pemberdayaan Masyarakat',
            'Karang Taruna Tunas Harapan',
            'Badan Permusyawaratan Desa (BPD)',
            'Pemberdayaan Kesejahteraan Keluarga (PKK)',
            'Posyandu Balita dan Lansia',
            'Gapoktan Tani Makmur',
            'BUMDes Maju Bersama',
            'Destinasi Wisata Alam',
            'Sarana dan Prasarana Desa',
            'Prosedur Pelayanan Surat',
            'Laporan Realisasi APBDes',
            'Kegiatan Keagamaan Desa',
            'Profil Kepala Desa dan Perangkat',
        ];

        $title = $this->faker->unique()->randomElement($titles) . ' ' . $this->faker->numberBetween(2024, 2026);
        // Fallback if unique runs out, though 10 pages is small enough
        if (!$title) {
            $title = "Halaman Informasi " . $this->faker->numberBetween(1, 100);
        }

        $slug = Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'status' => 'published',
            'feature_image' => null,
            'meta_title' => $title . ' - Desa Tanjung Kesuma',
            'meta_description' => 'Informasi lengkap mengenai ' . $title . ' di Desa Tanjung Kesuma.',
            'content' => $this->generateIndonesianContent($title),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    private function generateIndonesianContent($topic)
    {
        return "
            <p>Ini adalah halaman resmi yang memuat informasi mengenai <strong>$topic</strong> di Desa Tanjung Kesuma. Kami berkomitmen untuk memberikan transparansi dan layanan terbaik bagi seluruh warga desa.</p>
            <p>Desa Tanjung Kesuma terus berupaya mengembangkan potensi yang ada, baik dari sektor sumber daya manusia maupun sumber daya alam. Dukungan dari seluruh elemen masyarakat sangat diharapkan demi kemajuan bersama.</p>
            <p>Untuk informasi lebih lanjut mengenai $topic, silakan hubungi kantor desa atau perangkat desa terkait pada jam kerja.</p>
        ";
    }
}
