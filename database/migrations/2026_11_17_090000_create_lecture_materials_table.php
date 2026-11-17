<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_materials', function (Blueprint $table) {
            $table->id();
            // Keep this as an indexed identifier because Teacher authentication may
            // use the existing teachers table/model. No existing table is modified.
            $table->unsignedBigInteger('teacher_id')->index();
            $table->string('teacher_name', 255)->nullable();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('material_type', 10); // file | link
            $table->string('original_filename')->nullable();
            $table->string('file_path')->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('external_url')->nullable();
            $table->timestamps();

            $table->index(['material_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecture_materials');
    }
};
