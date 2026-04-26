<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Kepala Desa Tinjau Proyek Pembangunan Jalan Usaha Tani',
            'Meriahnya Perayaan HUT RI ke-79 di Desa Tanjung Kesuma',
            'Penyaluran Bibit Tanaman untuk Kelompok Tani',
            'Sosialisasi Pencegahan Stunting bagi Ibu Hamil',
            'Karang Taruna Gelar Turnamen Voli Antar Dusun',
            'Pelatihan Digital Marketing untuk UMKM Desa',
            'Musrenbangdes Tetapkan Prioritas Pembangunan 2026',
            'Gotong Royong Bersihkan Saluran Irigasi Desa',
            'Mahasiswa KKN Gelar Bimbel Gratis untuk Anak SD',
            'Kunjungan Camat dalam Rangka Monitoring Dana Desa',
            'Desa Tanjung Kesuma Raih Juara Lomba Desa Tingkat Kecamatan',
            'Penyuluhan Kesehatan Lingkungan oleh Puskesmas',
            'Pembagian Sembako bagi Warga Kurang Mampu',
            'Rapat Evaluasi Kinerja Perangkat Desa Semester I',
            'Panen Raya Padi Organik Kelompok Tani Subur Makmur',
            'Peresmian Gedung Serbaguna Desa Tanjung Kesuma',
            'Safari Ramadhan Pemerintah Desa Keliling Masjid',
            'Pelayanan Administrasi Kependudukan Keliling (Jemput Bola)',
            'Pelatihan Pengolahan Sampah Menjadi Pupuk Organik',
            'Seni Budaya Kuda Lumping Hibur Warga Tanjung Kesuma',
        ];

        $title = $this->faker->randomElement($titles) . ' - ' . $this->faker->numberBetween(1, 1000); // Add suffix to ensure uniqueness if looping
        $slug = Str::slug($title);

        // Ensure at least one category exists or create a default one
        $category = Category::inRandomOrder()->first();
        if (!$category) {
            $category = Category::create(['name' => 'Berita Desa', 'slug' => 'berita-desa']);
        }

        // Ensure at least one user exists or use the first one
        $user = User::inRandomOrder()->first();
        if (!$user) {
            $user = User::factory()->create();
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'summary' => "Berita terbaru seputar $title yang berlangsung di Desa Tanjung Kesuma.",
            'content' => $this->generateIndonesianContent($title),
            'thumbnail' => null,
            'category_id' => $category->id,
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'is_featured' => $this->faker->boolean(15),
            'views' => $this->faker->numberBetween(50, 1500),
            'seo_title' => $title,
            'seo_description' => "Baca berita selengkapnya tentang $title hanya di Website Resmi Desa Tanjung Kesuma.",
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    private function generateIndonesianContent($title)
    {
        return "
            <p><strong>Tanjung Kesuma</strong> - Pemerintah Desa Tanjung Kesuma terus berkomitmen meningkatkan pelayanan dan pembangunan desa. Salah satu kegiatan terbaru adalah <strong>$title</strong> yang dilaksanakan pada pekan ini.</p>
            <p>Kegiatan ini melibatkan berbagai elemen masyarakat, mulai dari perangkat desa, BPD, tokoh masyarakat, hingga pemuda. Antusiasme warga terlihat sangat tinggi dalam mengikuti rangkaian acara tersebut.</p>
            <p>Kepala Desa Tanjung Kesuma menyampaikan apresiasi setinggi-tingginya kepada seluruh pihak yang telah mendukung. 'Kami berharap kegiatan seperti $title ini dapat memberikan manfaat nyata bagi kesejahteraan warga desa,' ujar Beliau.</p>
            <p>Ke depan, program serupa akan terus digalakkan demi terwujudnya Desa Tanjung Kesuma yang lebih maju, mandiri, dan sejahtera.</p>
        ";
    }
}
