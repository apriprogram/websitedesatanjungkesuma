<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Peraturan Desa (Perdes)',
                'description' => 'Produk hukum tingkat desa yang ditetapkan oleh Kepala Desa bersama BPD.',
            ],
            [
                'name' => 'Peraturan Kepala Desa (Perkades)',
                'description' => 'Peraturan pelaksanaan dari Perdes atau kewenangan Kepala Desa.',
            ],
            [
                'name' => 'Keputusan Kepala Desa (SK Kades)',
                'description' => 'Keputusan penetapan yang bersifat konkret, individual, dan final.',
            ],
            [
                'name' => 'Dokumen Perencanaan',
                'description' => 'Dokumen perencanaan pembangunan (RPJMDes, RKPDes).',
            ],
            [
                'name' => 'Laporan Keuangan',
                'description' => 'Laporan APBDes, realisasi anggaran, dan pertanggungjawaban.',
            ],
            [
                'name' => 'Laporan Kinerja',
                'description' => 'Laporan Penyelenggaraan Pemerintahan Desa (LPPD, LKPPD).',
            ],
            [
                'name' => 'Informasi Publik',
                'description' => 'Dokumen yang wajib disediakan dan diumumkan secara berkala.',
            ],
            [
                'name' => 'Dokumen Umum', // Keeping existing one if needed
                'description' => 'Dokumen umum lainnya yang tidak masuk kategori khusus.',
            ],
        ];

        foreach ($categories as $cat) {
            DocumentCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])], // Match by slug to avoid dupes
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }
    }
}
