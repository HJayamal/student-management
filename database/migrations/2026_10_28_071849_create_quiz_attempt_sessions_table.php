<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempt_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->json('answers')->nullable();

            $table->dateTime('started_at');
            $table->dateTime('deadline_at');

            $table->string('status', 24)
                ->default('in_progress');

            $table->dateTime('submitted_at')->nullable();

            $table->foreignId('quiz_attempt_id')
                ->nullable()
                ->constrained('quiz_attempts')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['student_id', 'quiz_id', 'status'],
                'quiz_attempt_sessions_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_sessions');
    }
};
