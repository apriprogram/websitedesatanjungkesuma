<?php

namespace Database\Seeders;

use App\Models\DashboardStatistic;
use Illuminate\Database\Seeder;

class DashboardStatisticSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['type' => 'penduduk', 'label' => 'Penduduk Hidup', 'order_column' => 1],
            ['type' => 'penduduk', 'label' => 'Penduduk Pindah', 'order_column' => 2],
            ['type' => 'penduduk', 'label' => 'Penduduk Meninggal', 'order_column' => 3],
            ['type' => 'penduduk', 'label' => 'Jumlah KK', 'order_column' => 4],
            ['type' => 'penduduk', 'label' => 'Total Penduduk', 'order_column' => 5],
            ['type' => 'wilayah', 'label' => 'Jumlah Dusun', 'order_column' => 1],
            ['type' => 'wilayah', 'label' => 'Jumlah RW', 'order_column' => 2],
            ['type' => 'wilayah', 'label' => 'Jumlah RT', 'order_column' => 3],
            ['type' => 'pekerjaan', 'label' => 'Petani', 'order_column' => 1],
            ['type' => 'pekerjaan', 'label' => 'Wirausaha', 'order_column' => 2],
            ['type' => 'pendidikan', 'label' => 'SMA Sederajat', 'order_column' => 1],
            ['type' => 'pendidikan', 'label' => 'Sarjana', 'order_column' => 2],
            ['type' => 'agama', 'label' => 'Islam', 'order_column' => 1],
            ['type' => 'agama', 'label' => 'Kristen', 'order_column' => 2],
            ['type' => 'status_perkawinan', 'label' => 'Menikah', 'order_column' => 1],
            ['type' => 'status_perkawinan', 'label' => 'Belum Menikah', 'order_column' => 2],
            ['type' => 'usia', 'label' => 'Anak (0-12)', 'order_column' => 1],
            ['type' => 'usia', 'label' => 'Remaja (13-17)', 'order_column' => 2],
            ['type' => 'usia', 'label' => 'Dewasa (18-59)', 'order_column' => 3],
            ['type' => 'usia', 'label' => 'Lansia (60+)', 'order_column' => 4],
            ['type' => 'golongan_darah', 'label' => 'Golongan A', 'order_column' => 1],
            ['type' => 'golongan_darah', 'label' => 'Golongan B', 'order_column' => 2],
            ['type' => 'golongan_darah', 'label' => 'Golongan AB', 'order_column' => 3],
            ['type' => 'golongan_darah', 'label' => 'Golongan O', 'order_column' => 4],
        ];

        foreach ($defaults as $stat) {
            DashboardStatistic::firstOrCreate(
                ['type' => $stat['type'], 'label' => $stat['label']],
                ['order_column' => $stat['order_column'], 'value' => 0]
            );
        }
    }
}
