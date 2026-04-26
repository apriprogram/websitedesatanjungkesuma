<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->index();
            $table->enum('category', ['pelaksanaan', 'pendapatan', 'pembelanjaan'])->index();
            $table->string('subcategory', 150);
            $table->text('description')->nullable();
            $table->decimal('anggaran', 18, 2)->default(0);
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->string('icon', 120)->nullable();
            $table->integer('order_no')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
