<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('keluargas')) {
            return;
        }

        Schema::create('keluargas', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk', 30)->unique();
            $table->string('kepala_nik', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->foreignId('dusun_id')->nullable()->constrained('dusuns')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('rw_id')->nullable()->constrained('rws')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dusun_id', 'rw_id', 'rt_id'], 'keluargas_wilayah_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keluargas');
    }
};
