<?php

namespace Database\Factories;

use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VillageDocument>
 */
class VillageDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Peraturan Desa Nomor 1 Tahun 2025 tentang APBDes',
            'Laporan Pertanggungjawaban Realisasi Anggaran 2024',
            'Rencana Pembangunan Jangka Menengah Desa (RPJMDes) 2020-2026',
            'Surat Keputusan Kepala Desa tentang Pengangkatan Perangkat',
            'Profil Potensi Desa Tanjung Kesuma',
            'Peta Batas Wilayah Desa',
            'Data Statistik Kependudukan Semester I 2025',
            'Rencana Kerja Pemerintah Desa (RKPDes) Tahun 2026',
            'Laporan Kinerja Kepala Desa Akhir Tahun 2024',
            'Dokumen Kajian Lingkungan Hidup Desa',
            'Tata Tertib Musyawarah Desa',
            'Peraturan Kepala Desa tentang Pungutan Desa',
            'Proposal Bantuan Pembangunan Jembatan Desa',
            'Daftar Inventaris Aset Desa',
            'Laporan Keuangan BUMDes Maju Bersama',
        ];

        $title = $this->faker->unique()->randomElement($titles);

        // Pick a random category or default to 'Dokumen Umum'
        $category = DocumentCategory::inRandomOrder()->first();
        if (!$category) {
            $category = DocumentCategory::create(['name' => 'Dokumen Umum', 'slug' => 'dokumen-umum']);
        }

        // Ensure user exists
        $user = User::inRandomOrder()->first();
        if (!$user) {
            $user = User::factory()->create();
        }

        return [
            'title' => $title,
            'document_category_id' => $category->id,
            'description' => $this->faker->sentence(10),
            'file_path' => 'documents/dummy_' . Str::slug($title) . '.pdf',
            'file_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(500, 5000), // KB
            'year' => $this->faker->numberBetween(2020, 2026),
            'is_public' => true,
            'uploaded_by' => $user->id,
            'uploaded_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
