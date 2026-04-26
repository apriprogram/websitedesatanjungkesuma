<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Undangan Musyawarah Desa (Musdes)',
            'Jadwal Pelayanan Posyandu Balita',
            'Himbauan Kebersihan Lingkungan RT/RW',
            'Penyaluran Bantuan Langsung Tunai (BLT)',
            'Kerja Bakti Massal Jumat Bersih',
            'Pembayaran Pajak Bumi dan Bangunan (PBB)',
            'Pendaftaran Tanah Sistematis Lengkap (PTSL)',
            'Lomba Kebersihan Antar Dusun',
            'Pengumuman Pemadaman Listrik Bergilir',
            'Pelaksanaan Vaksinasi Pekan Imunisasi Nasional',
            'Rapat Koordinasi Ketua RT dan RW',
            'Laporan Keuangan Semester Desa',
            'Pemberitahuan Libur Pelayanan Kantor Desa',
            'Seleksi Perangkat Desa Baru',
            'Sosialisasi Ketahanan Pangan Desa',
        ];

        $title = $this->faker->randomElement($titles);
        $slug = Str::slug($title) . '-' . Str::random(6);

        // Ensure at least one user exists or use the first one
        $user = User::inRandomOrder()->first();

        $category = $this->faker->randomElement(['desa', 'daerah', 'pusat']);
        $catLabel = ucfirst($category);

        return [
            'title' => $title,
            'slug' => $slug,
            'body' => "
                <p><strong>PENGUMUMAN - $title</strong></p>
                <p>Diberitahukan kepada seluruh warga masyarakat Desa Tanjung Kesuma, bahwa akan dilaksanakan kegiatan terkait $title.</p>
                <p>Kami menghimbau partisipasi dan perhatian seluruh warga demi kelancaran kegiatan tersebut. Informasi lebih rinci dapat dilihat di papan pengumuman balai desa atau menghubungi ketua RT masing-masing.</p>
                <p>Demikian pengumuman dari $catLabel ini disampaikan untuk menjadi perhatian.</p>
            ",
            'excerpt' => "Pengumuman resmi terkait $title bagi warga Desa Tanjung Kesuma.",
            'category' => $category,
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'created_by' => $user ? $user->id : null,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
