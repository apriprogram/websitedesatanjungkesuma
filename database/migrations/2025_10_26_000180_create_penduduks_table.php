<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penduduks')) {
            return;
        }

        Schema::create('penduduks', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk', 30);
            $table->string('nik', 20)->unique();
            $table->string('nama', 100);
            $table->foreignId('jenis_kelamin_id')->constrained('ref_jenis_kelamin')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->foreignId('agama_id')->constrained('ref_agama')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('pendidikan_kk_id')->constrained('ref_pendidikan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('pendidikan_sedang_id')->constrained('ref_pendidikan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('pekerjaan_id')->constrained('ref_pekerjaan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('status_kawin_id')->constrained('ref_status_kawin')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('kk_level_id')->constrained('ref_hub_keluarga')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('warganegara_id')->constrained('ref_warganegara')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('ayah_nik', 20)->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('ibu_nik', 20)->nullable();
            $table->string('nama_ibu', 100)->nullable();
            $table->foreignId('golongan_darah_id')->nullable()->constrained('ref_golongan_darah')->cascadeOnUpdate()->nullOnDelete();
            $table->string('akta_lahir', 100)->nullable();
            $table->string('dokumen_pasport', 100)->nullable();
            $table->date('tanggal_akhir_paspor')->nullable();
            $table->string('dokumen_kitas', 100)->nullable();
            $table->string('akta_perkawinan', 100)->nullable();
            $table->date('tanggal_perkawinan')->nullable();
            $table->string('akta_perceraian', 100)->nullable();
            $table->date('tanggal_perceraian')->nullable();
            $table->foreignId('cacat_id')->nullable()->constrained('ref_cacat')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('cara_kb_id')->nullable()->constrained('ref_cara_kb')->cascadeOnUpdate()->nullOnDelete();
            $table->boolean('hamil')->nullable();
            $table->boolean('ktp_el')->default(false);
            $table->foreignId('status_rekam_id')->nullable()->constrained('ref_status_rekam')->cascadeOnUpdate()->nullOnDelete();
            $table->text('alamat')->nullable();
            $table->text('alamat_sekarang')->nullable();
            $table->foreignId('dusun_id')->constrained('dusuns')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('rw_id')->constrained('rws')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('status_dasar_id')->constrained('ref_status_dasar')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('suku_id')->nullable()->constrained('ref_suku')->cascadeOnUpdate()->nullOnDelete();
            $table->string('tag_id_card', 100)->nullable();
            $table->string('id_asuransi', 100)->nullable();
            $table->string('no_asuransi', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('no_kk');
            $table->index(['dusun_id', 'rw_id', 'rt_id'], 'penduduk_wilayah_index');
            $table->index('status_dasar_id');
            $table->index('status_kawin_id');

            $table->foreign('no_kk')
                ->references('no_kk')
                ->on('keluargas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penduduks');
    }
};
