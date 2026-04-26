<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class RandomNewsAnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $authorId = User::query()->value('id') ?? null;

        $categoryPool = collect([
            'Program Desa',
            'Kegiatan Warga',
            'Layanan Publik',
            'Ekonomi',
            'Infrastruktur',
            'Pendidikan',
            'Kesehatan',
            'UMKM',
            'Lingkungan',
            'Budaya & Wisata',
        ])->mapWithKeys(function ($name) {
            $cat = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
            return [$cat->slug => $cat->id];
        });

        // 50 berita
        for ($i = 0; $i < 50; $i++) {
            $title = $faker->unique()->sentence(6);
            $slug = Str::slug($title) . '-' . Str::random(6);
            $categoryId = $faker->randomElement($categoryPool->values()->all());
            $publishedAt = $faker->dateTimeBetween('-90 days', 'now');

            News::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => ucfirst($title),
                    'summary' => $faker->sentence(12),
                    'content' => '<p>' . implode('</p><p>', $faker->paragraphs(3)) . '</p>',
                    'category_id' => $categoryId,
                    'author_id' => $authorId,
                    'status' => 'published',
                    'published_at' => $publishedAt,
                    'views' => $faker->numberBetween(25, 900),
                ]
            );
        }

        $announcementCategories = array_keys(Announcement::categories());

        // 50 pengumuman
        for ($i = 0; $i < 50; $i++) {
            $title = $faker->unique()->sentence(5);
            $slug = Str::slug($title) . '-' . Str::random(5);
            $category = $faker->randomElement($announcementCategories);
            $publishedAt = $faker->dateTimeBetween('-60 days', 'now');

            Announcement::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => ucfirst($title),
                    'body' => '<p>' . implode('</p><p>', $faker->paragraphs(2)) . '</p>',
                    'excerpt' => $faker->sentence(15),
                    'category' => $category,
                    'status' => Announcement::STATUS_PUBLISHED,
                    'published_at' => $publishedAt,
                    'created_by' => $authorId,
                ]
            );
        }
    }
}
