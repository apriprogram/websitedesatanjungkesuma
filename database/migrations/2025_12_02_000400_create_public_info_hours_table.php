<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_info_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_info_setting_id')->constrained('public_info_settings')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 0 = Senin, 6 = Minggu
            $table->string('open_time', 10)->nullable();
            $table->string('close_time', 10)->nullable();
            $table->boolean('is_closed')->default(false);
            $table->string('note', 100)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['public_info_setting_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_info_hours');
    }
};
