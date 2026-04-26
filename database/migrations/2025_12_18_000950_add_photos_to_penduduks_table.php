<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penduduks')) {
            return;
        }

        Schema::table('penduduks', function (Blueprint $table) {
            if (! Schema::hasColumn('penduduks', 'foto_profil')) {
                $table->string('foto_profil')->nullable()->after('nama');
            }
            if (! Schema::hasColumn('penduduks', 'foto_ktp')) {
                $table->string('foto_ktp')->nullable()->after('foto_profil');
            }
            if (! Schema::hasColumn('penduduks', 'foto_kk')) {
                $table->string('foto_kk')->nullable()->after('foto_ktp');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('penduduks')) {
            return;
        }

        Schema::table('penduduks', function (Blueprint $table) {
            $columns = collect(['foto_profil', 'foto_ktp', 'foto_kk'])
                ->filter(fn (string $column) => Schema::hasColumn('penduduks', $column))
                ->all();

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
