<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penduduk_meninggals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penduduk_id')->constrained('penduduks')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_meninggal');
            $table->string('penyebab', 150)->nullable();
            $table->string('tempat_meninggal', 150)->nullable();
            $table->string('akta_meninggal_no', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal_meninggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penduduk_meninggals');
    }
};
