<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('degree_id')
                ->constrained('degrees')
                ->restrictOnDelete();

            $table->string('batch_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['degree_id', 'batch_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
