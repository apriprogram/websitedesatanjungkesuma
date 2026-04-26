<?php

namespace Database\Seeders;

use App\Models\SekilasInfo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SekilasInfoSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('sekilas_infos')) {
            return;
        }

        $seed = [
            [
                'title' => 'Layanan administrasi buka tiap hari kerja',
                'content' => 'Pelayanan surat menyurat dibuka Senin–Jumat pukul 08.00–15.00 WIB. Bawa berkas asli dan fotokopi.',
                'sort_order' => 1,
                'is_active' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Posyandu balita dan lansia',
                'content' => 'Jadwal Posyandu: minggu ke-2 setiap bulan di balai desa. Mohon hadir tepat waktu.',
                'sort_order' => 2,
                'is_active' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Pengajuan bantuan UMKM',
                'content' => 'Pendaftaran bantuan peralatan usaha dibuka sampai tanggal 20 bulan ini. Isi formulir di kantor desa.',
                'sort_order' => 3,
                'is_active' => true,
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($seed as $item) {
            SekilasInfo::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
