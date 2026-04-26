<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_menus')->onDelete('cascade');
            $table->string('title');
            $table->string('icon')->nullable();
            $table->enum('type', ['page', 'custom'])->default('page');
            $table->string('page_slug')->nullable();
            $table->string('url')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('target_blank')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_menus');
    }
};
