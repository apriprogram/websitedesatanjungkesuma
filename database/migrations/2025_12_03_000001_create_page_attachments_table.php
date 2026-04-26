<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_attachments')) {
            return;
        }

        Schema::create('page_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type', 20)->default('file'); // image or file
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime', 150)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_attachments');
    }
};
