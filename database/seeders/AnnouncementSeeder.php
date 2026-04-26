<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entries = [
            // Desa
            [
                'title' => 'Pelayanan Imunisasi Balita di Balai Desa',
                'body' => 'Pelayanan imunisasi gratis untuk balita Desa Tanjung Kesuma dilaksanakan setiap Selasa pagi di Balai Desa.',
                'category' => Announcement::CATEGORY_DESA,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Forum Warga & Rencana Penanaman Pohon',
                'body' => 'Tingkatkan penghijauan dengan ikut serta dalam forum warga untuk menentukan titik penanaman pohon di wilayah barat.',
                'category' => Announcement::CATEGORY_DESA,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Pendaftaran Kartu Identitas di Kantor Desa',
                'body' => 'Sampaikan dokumen lengkap ke kantor desa jika warga belum memiliki Kartu Identitas Desa.',
                'category' => Announcement::CATEGORY_DESA,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(9),
            ],
            [
                'title' => 'Kegiatan Sosialisasi PKK & Kesehatan Ibu',
                'body' => 'Bidang PKK dan Puskesmas Desa mengundang ibu hamil untuk sosialisasi program kesehatan terbaru.',
                'category' => Announcement::CATEGORY_DESA,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'Lomba Kebersihan RT/RW 2025',
                'body' => 'Mendorong partisipasi masyarakat dalam lomba kebersihan tingkat RT/RW dengan hadiah menarik.',
                'category' => Announcement::CATEGORY_DESA,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(15),
            ],
            // Daerah
            [
                'title' => 'Pembukaan Program Dana Infrastruktur Lampung Timur',
                'body' => 'Kabupaten mengucurkan dana infrastruktur untuk memperbaiki 3 jalan utama yang menghubungkan Dusun Tanjung Kesuma.',
                'category' => Announcement::CATEGORY_DAERAH,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Bimbingan Teknis Layanan Administrasi Publik',
                'body' => 'Dinas Kependudukan menggelar bimbingan teknis untuk operator desa pada 25 November 2025.',
                'category' => Announcement::CATEGORY_DAERAH,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Agrofest Kabupaten Lampung Timur',
                'body' => 'Event Agrofest menghadirkan pasar tani untuk mempromosikan produk petani desa dan UMKM lokal.',
                'category' => Announcement::CATEGORY_DAERAH,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Program Peningkatan Literasi Digital Kecamatan',
                'body' => 'Kecamatan menyediakan pelatihan literasi digital selama 3 hari untuk perangkat desa dan pelajar.',
                'category' => Announcement::CATEGORY_DAERAH,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(13),
            ],
            [
                'title' => 'Servis Kesehatan Keliling untuk Lansia',
                'body' => 'Dinas Kesehatan Lampung Timur menyediakan layanan kesehatan keliling yang akan singgah di Desa Tanjung Kesuma pada akhir bulan.',
                'category' => Announcement::CATEGORY_DAERAH,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(18),
            ],
            // Pusat
            [
                'title' => 'Pengumuman Subsidi Program BBM Penugasan',
                'body' => 'Pemerintah pusat menyampaikan kebijakan terbaru mengenai subsidi BBM untuk kendaraan dinas desa.',
                'category' => Announcement::CATEGORY_PUSAT,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Gerakan Nasional Tanam 10 Juta Pohon',
                'body' => 'Instruksi Presiden mengajak semua desa untuk ikut mendukung Gerakan Nasional Tanam 10 Juta Pohon tahun ini.',
                'category' => Announcement::CATEGORY_PUSAT,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Kebijakan Digitalisasi Arsip Pemerintahan',
                'body' => 'Kementerian Dalam Negeri mengeluarkan panduan digitalisasi arsip untuk mempercepat pelayanan publik.',
                'category' => Announcement::CATEGORY_PUSAT,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Pemberdayaan UMKM Nasional',
                'body' => 'Program UMKM Nasional mendorong pelatihan pemasaran digital bagi pelaku usaha desa.',
                'category' => Announcement::CATEGORY_PUSAT,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(11),
            ],
            [
                'title' => 'Penyuluhan Keluarga Produktif',
                'body' => 'Kementerian PPPA menyelenggarakan penyuluhan keluarga produktif untuk meningkatkan ekonomi rumah tangga di desa.',
                'category' => Announcement::CATEGORY_PUSAT,
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now()->subDays(14),
            ],
        ];

        foreach ($entries as $index => $entry) {
            $base = Str::slug($entry['title']);
            $entry['slug'] = $base . '-' . ($index + 1);
            if (!isset($entry['excerpt'])) {
                $entry['excerpt'] = Str::limit(strip_tags($entry['body']), 160);
            }
            Announcement::create($entry);
        }
    }
}
