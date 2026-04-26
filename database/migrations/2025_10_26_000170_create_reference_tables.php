<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createReferenceTable('ref_agama');
        $this->createReferenceTable('ref_pendidikan');
        $this->createReferenceTable('ref_pekerjaan');
        $this->createReferenceTable('ref_status_kawin');
        $this->createReferenceTable('ref_hub_keluarga');
        $this->createReferenceTable('ref_warganegara');
        $this->createReferenceTable('ref_golongan_darah');
        $this->createReferenceTable('ref_cacat');
        $this->createReferenceTable('ref_cara_kb');
        $this->createReferenceTable('ref_status_rekam');
        $this->createReferenceTable('ref_status_dasar');
        $this->createReferenceTable('ref_jenis_kelamin');
        $this->createReferenceTable('ref_suku');
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_suku');
        Schema::dropIfExists('ref_jenis_kelamin');
        Schema::dropIfExists('ref_status_dasar');
        Schema::dropIfExists('ref_status_rekam');
        Schema::dropIfExists('ref_cara_kb');
        Schema::dropIfExists('ref_cacat');
        Schema::dropIfExists('ref_golongan_darah');
        Schema::dropIfExists('ref_warganegara');
        Schema::dropIfExists('ref_hub_keluarga');
        Schema::dropIfExists('ref_status_kawin');
        Schema::dropIfExists('ref_pekerjaan');
        Schema::dropIfExists('ref_pendidikan');
        Schema::dropIfExists('ref_agama');
    }

    private function createReferenceTable(string $table): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique('nama');
        });
    }
};
