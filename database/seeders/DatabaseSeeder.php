<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\DashboardStatisticSeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\PegawaiDetailBackfillSeeder;
use Database\Seeders\PegawaiSeeder;
use Database\Seeders\ReferenceSeeder;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\BudgetItemSeeder;
use Database\Seeders\SekilasInfoSeeder;
use Database\Seeders\DocumentDemoSeeder;
use Database\Seeders\RandomPendudukSeeder;
use Database\Seeders\RandomNewsAnnouncementSeeder;
use Database\Seeders\NavigationMenuSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = 'Apriyansah74!';

        $admins = [
            [
                'email' => 'admin@desatanjungkesuma.id',
                'nama' => 'Administrator Desa',
                'nomor_hp' => '081234567890',
            ],
            [
                'email' => 'sekretaris@desatanjungkesuma.id',
                'nama' => 'Sekretaris Desa',
                'nomor_hp' => '081298765432',
            ],
            [
                'email' => 'operator@desatanjungkesuma.id',
                'nama' => 'Operator Data Desa',
                'nomor_hp' => '082134567890',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'nama' => $admin['nama'],
                    'nomor_hp' => $admin['nomor_hp'] ?? null,
                    'password' => Hash::make($defaultPassword),
                    'is_admin' => true,
                    'is_active' => true,
                ]
            );
        }

        $this->call([
            ReferenceSeeder::class,
            PegawaiSeeder::class,
            PegawaiDetailBackfillSeeder::class,
            DashboardStatisticSeeder::class,
            NewsSeeder::class,
            AnnouncementSeeder::class,
            BannerSeeder::class,
            SocialLinkSeeder::class,
            BudgetSeeder::class,
            SekilasInfoSeeder::class,
            DocumentDemoSeeder::class,
            RandomPendudukSeeder::class,
            RandomNewsAnnouncementSeeder::class,
            NavigationMenuSeeder::class,
            ContentGenerationSeeder::class,
        ]);
    }
}
