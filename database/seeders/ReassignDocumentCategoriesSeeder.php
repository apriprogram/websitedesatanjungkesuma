<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\VillageDocument;
use Illuminate\Database\Seeder;

class ReassignDocumentCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = DocumentCategory::pluck('id');

        if ($categories->isEmpty()) {
            return;
        }

        VillageDocument::all()->each(function ($doc) use ($categories) {
            $doc->update(['document_category_id' => $categories->random()]);
        });
    }
}
