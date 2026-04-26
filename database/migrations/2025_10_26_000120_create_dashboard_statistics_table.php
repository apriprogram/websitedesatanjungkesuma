<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('label', 120);
            $table->unsignedInteger('value')->default(0);
            $table->unsignedInteger('order_column')->default(0);
            $table->json('meta')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['type', 'label']);
            $table->index(['type', 'order_column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_statistics');
    }
};
