<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_info_settings', function (Blueprint $table) {
            $table->id();
            $table->string('section_title')->default('Informasi Publik');
            $table->string('section_subtitle')->nullable();

            $table->string('request_title')->nullable();
            $table->text('request_description')->nullable();
            $table->string('request_button_label')->nullable();
            $table->string('request_button_url')->nullable();
            $table->string('request_image')->nullable();

            $table->string('hours_title')->nullable();
            $table->string('hours_description')->nullable();
            $table->string('hours_note')->nullable();

            $table->string('map_title')->nullable();
            $table->string('map_description')->nullable();
            $table->text('map_embed_url')->nullable();

            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_info_settings');
    }
};
