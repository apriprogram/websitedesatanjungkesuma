<?php

namespace Database\Seeders;

use App\Models\BudgetItem;
use Illuminate\Database\Seeder;

class BudgetItemSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        $data = [
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PELAKSANAAN,
                'subcategory' => 'Pendapatan Desa',
                'anggaran' => 1244664000000,
                'realisasi' => 734804400000,
                'order_no' => 1,
                'icon' => 'fa-regular fa-money-bill-1',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PELAKSANAAN,
                'subcategory' => 'Belanja Desa',
                'anggaran' => 922299687000,
                'realisasi' => 254277477000,
                'order_no' => 2,
                'icon' => 'fa-regular fa-money-bill-1',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PENDAPATAN,
                'subcategory' => 'Dana Desa',
                'anggaran' => 1244664000000,
                'realisasi' => 734804400000,
                'order_no' => 1,
                'icon' => 'fa-regular fa-money-bill-1',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PENDAPATAN,
                'subcategory' => 'Bagi Hasil Pajak & Retribusi',
                'anggaran' => 155000000000,
                'realisasi' => 84000000000,
                'order_no' => 2,
                'icon' => 'fa-regular fa-money-bill-1',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PENDAPATAN,
                'subcategory' => 'Pendapatan Asli Desa (PADes)',
                'anggaran' => 32000000000,
                'realisasi' => 12500000000,
                'order_no' => 3,
                'icon' => 'fa-regular fa-money-bill-1',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PEMBELANJAAN,
                'subcategory' => 'Bidang Penyelenggaraan Pemerintah Desa',
                'anggaran' => 322076947000,
                'realisasi' => 53015428000,
                'order_no' => 1,
                'icon' => 'fa-regular fa-file-lines',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PEMBELANJAAN,
                'subcategory' => 'Bidang Pelaksanaan Pembangunan Desa',
                'anggaran' => 322076947000,
                'realisasi' => 53015428000,
                'order_no' => 2,
                'icon' => 'fa-regular fa-file-lines',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PEMBELANJAAN,
                'subcategory' => 'Bidang Pemberdayaan Masyarakat Desa',
                'anggaran' => 67782000000,
                'realisasi' => 600000000,
                'order_no' => 3,
                'icon' => 'fa-regular fa-file-lines',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PEMBELANJAAN,
                'subcategory' => 'Bidang Penanggulangan Bencana, Darurat & Mendesak',
                'anggaran' => 22500000000,
                'realisasi' => 4500000000,
                'order_no' => 4,
                'icon' => 'fa-regular fa-file-lines',
            ],
            [
                'year' => $year,
                'category' => BudgetItem::CATEGORY_PEMBELANJAAN,
                'subcategory' => 'Belanja Modal Aset Desa',
                'anggaran' => 88000000000,
                'realisasi' => 41000000000,
                'order_no' => 5,
                'icon' => 'fa-regular fa-file-lines',
            ],
        ];

        foreach ($data as $item) {
            BudgetItem::updateOrCreate(
                [
                    'year' => $item['year'],
                    'category' => $item['category'],
                    'subcategory' => $item['subcategory'],
                ],
                $item
            );
        }
    }
}
