<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('nama');
            $table->string('nip', 20)->nullable()->after('nik');
            $table->string('tempat_lahir')->nullable()->after('email');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('tanggal_lahir');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->text('alamat')->nullable()->after('agama');
            $table->string('foto_ktp')->nullable()->after('gambar');
            $table->string('sk_pengangkatan')->nullable()->after('foto_ktp');
            $table->string('sk_pemberhentian')->nullable()->after('sk_pengangkatan');
            $table->date('masa_jabatan_mulai')->nullable()->after('sk_pemberhentian');
            $table->date('masa_jabatan_selesai')->nullable()->after('masa_jabatan_mulai');
            $table->string('universitas')->nullable()->after('masa_jabatan_selesai');
            $table->string('pendidikan_terakhir')->nullable()->after('universitas');
            $table->string('tahun_lulus', 10)->nullable()->after('pendidikan_terakhir');
            $table->text('sertifikat_pelatihan')->nullable()->after('tahun_lulus');
            $table->string('bahasa')->nullable()->after('sertifikat_pelatihan');
            $table->timestamp('last_login')->nullable()->after('bahasa');
            $table->foreignId('created_by')->nullable()->after('last_login')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
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
                'created_by',
                'updated_by',
            ]);
        });
    }
};

