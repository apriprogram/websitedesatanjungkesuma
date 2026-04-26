<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rws')) {
            return;
        }

        Schema::create('rws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dusun_id')->constrained('dusuns')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nomor', 10);
            $table->string('kode', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['dusun_id', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rws');
    }
};
