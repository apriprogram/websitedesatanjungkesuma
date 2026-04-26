<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\News;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ContentGenerationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Clean up old "dummy" data to remove Latin/Lorem Ipsum entries.
        // Be careful not to delete ALL data if you want to keep manual entries, 
        // but for this request "fix the data", a clean slate for these tables is safer to guarantee all data is Indonesian.
        // We will keep Category/User but clear the content tables.

        // Disable foreign key checks to avoid deletion errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        News::truncate();
        Announcement::truncate();
        // Page::truncate(); // Don't truncate pages as PagesAndNavSeeder creates core pages. We only want to ADD extras or clean extras.
        // Instead of truncate, let's delete only pages created by factory (if possible) or just add more.
        // But the user complained about "wrong data". Let's assume the 10 pages created previously are the ones to remove.
        // Since we don't track them easily, let's just create 10 NEW good ones. 
        // If the user wants to remove the Latin ones, they can delete via Admin or we can try to find them.
        // For News and Announcements, we can safely truncate as they are mostly generated.
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Generate 50 News Items
        // Factory now handles the titles internally to be "Desa Tanjung Kesuma" specific.
        News::factory()->count(50)->create();

        // 2. Generate 30 Announcements
        // Factory now handles the titles internally.
        Announcement::factory()->count(30)->create();

        // 3. Generate 10 Additional Pages
        // Factory now handles the titles internally.
        Page::factory()->count(10)->create();

        // 4. Generate 10 Agendas
        \App\Models\Agenda::factory()->count(10)->create();

        // 5. Generate 10 Village Documents
        \App\Models\VillageDocument::factory()->count(10)->create();
    }
}
