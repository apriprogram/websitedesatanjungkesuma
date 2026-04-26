<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penduduk_pindahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penduduk_id')->constrained('penduduks')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_pindah');
            $table->text('alasan_pindah')->nullable();
            $table->text('alamat_tujuan')->nullable();
            $table->string('desa_tujuan', 100)->nullable();
            $table->string('kecamatan_tujuan', 100)->nullable();
            $table->string('kabupaten_tujuan', 100)->nullable();
            $table->string('provinsi_tujuan', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal_pindah');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penduduk_pindahs');
    }
};
