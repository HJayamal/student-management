<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Store the answers submitted by the student.
            $table->json('answers');

            // Record when the student submitted the quiz.
            $table->dateTime('submitted_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
