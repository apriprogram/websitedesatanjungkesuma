<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class ReloadSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sample-data:reload {--down : Jalankan rollback sample data sebelum memuat ulang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memuat ulang data contoh referensi, wilayah, dan penduduk untuk dashboard admin.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $migrationPath = database_path('migrations/2025_10_31_020000_seed_sample_data.php');

        if (! file_exists($migrationPath)) {
            $this->error('File migrasi sample data tidak ditemukan: ' . $migrationPath);
            return self::FAILURE;
        }

        try {
            /** @var object $migration */
            $migration = require $migrationPath;

            if ($this->option('down')) {
                $this->info('Menghapus data sample sebelumnya...');
                $migration->down();
            }

            $this->info('Memuat data sample terbaru...');
            $migration->up();

            $this->info('Data sample berhasil dimuat ulang.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Gagal memuat ulang data sample: ' . $exception->getMessage());
            return self::FAILURE;
        }
    }
}
