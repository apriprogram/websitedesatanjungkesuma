<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('budget_items')) {
            return;
        }

        Schema::table('budget_items', function (Blueprint $table) {
            if (!Schema::hasColumn('budget_items', 'is_active_year')) {
                $table->boolean('is_active_year')->default(false)->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('budget_items')) {
            return;
        }

        Schema::table('budget_items', function (Blueprint $table) {
            if (Schema::hasColumn('budget_items', 'is_active_year')) {
                $table->dropColumn('is_active_year');
            }
        });
    }
};
