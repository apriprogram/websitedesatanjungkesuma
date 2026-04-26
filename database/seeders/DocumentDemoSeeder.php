<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\VillageDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = [
            'Peraturan Desa (Perdes)',
            'Rencana Kerja Pemerintah (RKP Desa)',
            'APBDes',
            'Laporan Kegiatan',
            'Laporan Pertanggungjawaban (LPJ)',
            'Laporan Realisasi Anggaran',
            'Dokumen Profil Desa',
            'Layanan Administrasi',
            'Arsip Surat Masuk',
            'Arsip Surat Keluar',
        ];

        $categories = collect($categoryNames)->mapWithKeys(function ($name) {
            $slug = Str::slug($name);
            $cat = DocumentCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => null]
            );
            return [$name => $cat->id];
        });

        $samples = [
            ['title' => 'Perdes No 1 Tahun 2023 tentang APBDes', 'cat' => 'Peraturan Desa (Perdes)', 'year' => 2023, 'type' => 'pdf'],
            ['title' => 'RKP Desa 2024', 'cat' => 'Rencana Kerja Pemerintah (RKP Desa)', 'year' => 2024, 'type' => 'docx'],
            ['title' => 'APBDes 2024', 'cat' => 'APBDes', 'year' => 2024, 'type' => 'xlsx'],
            ['title' => 'Laporan Kegiatan Posyandu Triwulan I 2024', 'cat' => 'Laporan Kegiatan', 'year' => 2024, 'type' => 'pdf'],
            ['title' => 'LPJ Dana Desa 2023', 'cat' => 'Laporan Pertanggungjawaban (LPJ)', 'year' => 2023, 'type' => 'pdf'],
            ['title' => 'Laporan Realisasi Anggaran 2023', 'cat' => 'Laporan Realisasi Anggaran', 'year' => 2023, 'type' => 'pdf'],
            ['title' => 'Profil Desa 2024', 'cat' => 'Dokumen Profil Desa', 'year' => 2024, 'type' => 'pptx'],
            ['title' => 'Form Layanan Administrasi KTP', 'cat' => 'Layanan Administrasi', 'year' => 2024, 'type' => 'doc'],
            ['title' => 'Arsip Surat Masuk Jan 2024', 'cat' => 'Arsip Surat Masuk', 'year' => 2024, 'type' => 'zip'],
            ['title' => 'Arsip Surat Keluar Feb 2024', 'cat' => 'Arsip Surat Keluar', 'year' => 2024, 'type' => 'zip'],
        ];

        foreach ($samples as $sample) {
            VillageDocument::updateOrCreate(
                ['title' => $sample['title']],
                [
                    'document_category_id' => $categories[$sample['cat']] ?? $categories->first(),
                    'description' => null,
                    'file_path' => 'documents/sample-' . Str::slug($sample['title']) . '.' . $sample['type'],
                    'file_type' => $sample['type'],
                    'file_size' => 1024, // 1MB placeholder
                    'year' => $sample['year'],
                    'is_public' => true,
                    'uploaded_by' => null,
                    'uploaded_at' => now()->subDays(rand(1, 90)),
                ]
            );
        }
    }
}
