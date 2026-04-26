<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('social_links')) {
            return;
        }

        Schema::table('social_links', function (Blueprint $table) {
            if (! Schema::hasColumn('social_links', 'type')) {
                $table->string('type', 20)->default('icon')->after('slug');
            }
        });

        // Set existing rows to icon
        // DB facade intentionally not used to avoid missing import during migration in some environments
    }

    public function down(): void
    {
        if (! Schema::hasTable('social_links')) {
            return;
        }

        Schema::table('social_links', function (Blueprint $table) {
            if (Schema::hasColumn('social_links', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
