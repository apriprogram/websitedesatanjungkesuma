<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'nik')) {
                $table->string('nik', 20)->nullable()->after('nama');
            }
            if (!Schema::hasColumn('pegawais', 'nip')) {
                $table->string('nip', 20)->nullable()->after('nik');
            }
            if (!Schema::hasColumn('pegawais', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('email');
            }
            if (!Schema::hasColumn('pegawais', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
            if (!Schema::hasColumn('pegawais', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('tanggal_lahir');
            }
            if (!Schema::hasColumn('pegawais', 'agama')) {
                $table->string('agama')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('pegawais', 'alamat')) {
                $table->text('alamat')->nullable()->after('agama');
            }
            if (!Schema::hasColumn('pegawais', 'foto_ktp')) {
                $table->string('foto_ktp')->nullable()->after('gambar');
            }
            if (!Schema::hasColumn('pegawais', 'sk_pengangkatan')) {
                $table->string('sk_pengangkatan')->nullable()->after('foto_ktp');
            }
            if (!Schema::hasColumn('pegawais', 'sk_pemberhentian')) {
                $table->string('sk_pemberhentian')->nullable()->after('sk_pengangkatan');
            }
            if (!Schema::hasColumn('pegawais', 'masa_jabatan_mulai')) {
                $table->date('masa_jabatan_mulai')->nullable()->after('sk_pemberhentian');
            }
            if (!Schema::hasColumn('pegawais', 'masa_jabatan_selesai')) {
                $table->date('masa_jabatan_selesai')->nullable()->after('masa_jabatan_mulai');
            }
            if (!Schema::hasColumn('pegawais', 'universitas')) {
                $table->string('universitas')->nullable()->after('masa_jabatan_selesai');
            }
            if (!Schema::hasColumn('pegawais', 'pendidikan_terakhir')) {
                $table->string('pendidikan_terakhir')->nullable()->after('universitas');
            }
            if (!Schema::hasColumn('pegawais', 'tahun_lulus')) {
                $table->string('tahun_lulus', 4)->nullable()->after('pendidikan_terakhir');
            }
            if (!Schema::hasColumn('pegawais', 'sertifikat_pelatihan')) {
                $table->text('sertifikat_pelatihan')->nullable()->after('tahun_lulus');
            }
            if (!Schema::hasColumn('pegawais', 'bahasa')) {
                $table->string('bahasa')->nullable()->after('sertifikat_pelatihan');
            }
            if (!Schema::hasColumn('pegawais', 'last_login')) {
                $table->timestamp('last_login')->nullable()->after('bahasa');
            }
            if (!Schema::hasColumn('pegawais', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('last_login')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pegawais', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pegawais', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('pegawais', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('pegawais', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            foreach ([
                'nik',
                'nip',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'agama',
                'alamat',
                'foto_ktp',
                'sk_pengangkatan',
                'sk_pemberhentian',
                'masa_jabatan_mulai',
                'masa_jabatan_selesai',
                'universitas',
                'pendidikan_terakhir',
                'tahun_lulus',
                'sertifikat_pelatihan',
                'bahasa',
                'last_login',
            ] as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

