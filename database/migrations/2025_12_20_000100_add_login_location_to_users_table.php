<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom informasi login terakhir (IP dan lokasi) ke tabel users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 64)->nullable()->after('last_login');
            }

            if (! Schema::hasColumn('users', 'last_login_location')) {
                $table->string('last_login_location')->nullable()->after('last_login_ip');
            }
        });
    }

    /**
     * Hapus kolom yang ditambahkan ketika rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_login_location')) {
                $table->dropColumn('last_login_location');
            }
            if (Schema::hasColumn('users', 'last_login_ip')) {
                $table->dropColumn('last_login_ip');
            }
        });
    }
};
