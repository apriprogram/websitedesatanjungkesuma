<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rts')) {
            return;
        }

        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rw_id')->constrained('rws')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nomor', 10);
            $table->string('kode', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rw_id', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rts');
    }
};
