<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('public_info_settings', function (Blueprint $table) {
            $table->boolean('show_budget_section')->default(true)->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_info_settings', function (Blueprint $table) {
            $table->dropColumn('show_budget_section');
        });
    }
};
