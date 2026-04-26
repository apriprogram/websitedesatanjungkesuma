<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('public_info_settings')) {
            return;
        }

        Schema::table('public_info_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('public_info_settings', 'footer_links')) {
                $table->json('footer_links')->nullable()->after('footer_address');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('public_info_settings')) {
            return;
        }

        Schema::table('public_info_settings', function (Blueprint $table) {
            if (Schema::hasColumn('public_info_settings', 'footer_links')) {
                $table->dropColumn('footer_links');
            }
        });
    }
};
