<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_info_settings', function (Blueprint $table) {
            $table->text('footer_address')->nullable()->after('map_embed_url');
        });
    }

    public function down(): void
    {
        Schema::table('public_info_settings', function (Blueprint $table) {
            $table->dropColumn('footer_address');
        });
    }
};
