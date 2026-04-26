<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('village_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('document_category_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type', 30)->nullable();
            $table->unsignedInteger('file_size')->nullable(); // KB
            $table->integer('year')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('village_documents');
    }
};
