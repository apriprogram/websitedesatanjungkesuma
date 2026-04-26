<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data to prevent accumulation on re-seed
        DB::table('budget_items')->delete();

        // Data for Year 2026
        $data2026 = [
            // 1. Pelaksanaan
            [
                'year' => 2026,
                'category' => 'pelaksanaan',
                'subcategory' => 'Pendapatan Desa',
                'description' => 'Total Pendapatan Desa Tahun 2026',
                'anggaran' => 1244664000000,
                'realisasi' => 734804400000,
                'icon' => 'fas fa-coins',
                'order_no' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2026,
                'category' => 'pelaksanaan',
                'subcategory' => 'Belanja Desa',
                'description' => 'Total Belanja Desa Tahun 2026',
                'anggaran' => 922299687000,
                'realisasi' => 254277477000,
                'icon' => 'fas fa-shopping-cart',
                'order_no' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. Pendapatan
            [
                'year' => 2026,
                'category' => 'pendapatan',
                'subcategory' => 'Dana Desa',
                'description' => 'Penerimaan Dana Desa dari Pemerintah Pusat',
                'anggaran' => 1244664000000,
                'realisasi' => 734804400000,
                'icon' => 'fas fa-hand-holding-usd',
                'order_no' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2026,
                'category' => 'pendapatan',
                'subcategory' => 'Bagi Hasil Pajak & Retribusi',
                'description' => 'Penerimaan Bagi Hasil Pajak Daerah dan Retribusi Daerah',
                'anggaran' => 155000000000,
                'realisasi' => 84000000000,
                'icon' => 'fas fa-percent',
                'order_no' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2026,
                'category' => 'pendapatan',
                'subcategory' => 'Pendapatan Asli Desa (PADes)',
                'description' => 'Hasil Usaha Desa, Hasil Aset, dan Lain-lain Pendapatan Asli Desa',
                'anggaran' => 32000000000,
                'realisasi' => 12500000000,
                'icon' => 'fas fa-money-bill-wave',
                'order_no' => 3,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. Pembelanjaan
            [
                'year' => 2026,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Penyelenggaraan Pemerintah Desa',
                'description' => 'Belanja untuk operasional dan gaji perangkat desa',
                'anggaran' => 322076947000,
                'realisasi' => 53015428000,
                'icon' => 'fas fa-landmark',
                'order_no' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2026,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Pelaksanaan Pembangunan Desa',
                'description' => 'Pembangunan infrastruktur jalan, jembatan, dan fasilitas umum',
                'anggaran' => 322076947000,
                'realisasi' => 53015428000,
                'icon' => 'fas fa-hard-hat',
                'order_no' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2026,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Pemberdayaan Masyarakat Desa',
                'description' => 'Pelatihan dan pemberdayaan ekonomi masyarakat',
                'anggaran' => 67782000000,
                'realisasi' => 600000000,
                'icon' => 'fas fa-users',
                'order_no' => 3,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2026,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Penanggulangan Bencana, Darurat dan Mendesak Desa',
                'description' => 'Dana siap pakai untuk kebencanaan',
                'anggaran' => 22500000000,
                'realisasi' => 4500000000,
                'icon' => 'fas fa-first-aid',
                'order_no' => 4,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2026,
                'category' => 'pembelanjaan',
                'subcategory' => 'Belanja Modal Aset Desa',
                'description' => 'Pengadaan aset tetap desa',
                'anggaran' => 88000000000,
                'realisasi' => 41000000000,
                'icon' => 'fas fa-cubes',
                'order_no' => 5,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Data for Year 2025 (Distinct from 2026)
        $data2025 = [
            // 1. Pelaksanaan
            [
                'year' => 2025,
                'category' => 'pelaksanaan',
                'subcategory' => 'Pendapatan Desa',
                'description' => 'Total Pendapatan Desa Tahun 2025',
                'anggaran' => 1150000000000,
                'realisasi' => 1100000000000,
                'icon' => 'fas fa-coins',
                'order_no' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2025,
                'category' => 'pelaksanaan',
                'subcategory' => 'Belanja Desa',
                'description' => 'Total Belanja Desa Tahun 2025',
                'anggaran' => 1050000000000,
                'realisasi' => 980000000000,
                'icon' => 'fas fa-shopping-cart',
                'order_no' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. Pendapatan
            [
                'year' => 2025,
                'category' => 'pendapatan',
                'subcategory' => 'Dana Desa',
                'description' => 'Penerimaan Dana Desa dari Pemerintah Pusat',
                'anggaran' => 1150000000000,
                'realisasi' => 1100000000000,
                'icon' => 'fas fa-hand-holding-usd',
                'order_no' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2025,
                'category' => 'pendapatan',
                'subcategory' => 'Bagi Hasil Pajak & Retribusi',
                'description' => 'Penerimaan Bagi Hasil Pajak Daerah dan Retribusi Daerah',
                'anggaran' => 140000000000,
                'realisasi' => 135000000000,
                'icon' => 'fas fa-percent',
                'order_no' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2025,
                'category' => 'pendapatan',
                'subcategory' => 'Pendapatan Asli Desa (PADes)',
                'description' => 'Hasil Usaha Desa, Hasil Aset, dan Lain-lain Pendapatan Asli Desa',
                'anggaran' => 25000000000,
                'realisasi' => 22000000000,
                'icon' => 'fas fa-money-bill-wave',
                'order_no' => 3,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. Pembelanjaan
            [
                'year' => 2025,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Penyelenggaraan Pemerintah Desa',
                'description' => 'Belanja untuk operasional dan gaji perangkat desa',
                'anggaran' => 300000000000,
                'realisasi' => 290000000000,
                'icon' => 'fas fa-landmark',
                'order_no' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2025,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Pelaksanaan Pembangunan Desa',
                'description' => 'Pembangunan infrastruktur jalan, jembatan, dan fasilitas umum',
                'anggaran' => 500000000000,
                'realisasi' => 480000000000,
                'icon' => 'fas fa-hard-hat',
                'order_no' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2025,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Pemberdayaan Masyarakat Desa',
                'description' => 'Pelatihan dan pemberdayaan ekonomi masyarakat',
                'anggaran' => 50000000000,
                'realisasi' => 45000000000,
                'icon' => 'fas fa-users',
                'order_no' => 3,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2025,
                'category' => 'pembelanjaan',
                'subcategory' => 'Bidang Penanggulangan Bencana, Darurat dan Mendesak Desa',
                'description' => 'Dana siap pakai untuk kebencanaan',
                'anggaran' => 20000000000,
                'realisasi' => 10000000000,
                'icon' => 'fas fa-first-aid',
                'order_no' => 4,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => 2025,
                'category' => 'pembelanjaan',
                'subcategory' => 'Belanja Modal Aset Desa',
                'description' => 'Pengadaan aset tetap desa',
                'anggaran' => 70000000000,
                'realisasi' => 65000000000,
                'icon' => 'fas fa-cubes',
                'order_no' => 5,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert Data
        DB::table('budget_items')->insert(array_merge($data2025, $data2026));
    }
}
