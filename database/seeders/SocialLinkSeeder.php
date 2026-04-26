<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            ['name' => 'Facebook', 'slug' => 'facebook', 'icon' => 'fab fa-facebook-f', 'url' => 'https://facebook.com/desatkj'],
            ['name' => 'LinkedIn', 'slug' => 'linkedin', 'icon' => 'fab fa-linkedin-in', 'url' => 'https://linkedin.com/company/desatkj'],
            ['name' => 'Instagram', 'slug' => 'instagram', 'icon' => 'fab fa-instagram', 'url' => 'https://instagram.com/desatkj'],
            ['name' => 'Twitter', 'slug' => 'twitter', 'icon' => 'fab fa-twitter', 'url' => 'https://twitter.com/desatkj'],
            ['name' => 'YouTube', 'slug' => 'youtube', 'icon' => 'fab fa-youtube', 'url' => 'https://youtube.com/desatkj'],
            ['name' => 'TikTok', 'slug' => 'tiktok', 'icon' => 'fab fa-tiktok', 'url' => 'https://tiktok.com/@desatkj'],
        ];

        foreach ($links as $index => $link) {
            SocialLink::updateOrCreate(
                ['slug' => $link['slug']],
                array_merge($link, ['sort_order' => $index])
            );
        }
    }
}
