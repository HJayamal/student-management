
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_notifications', function (Blueprint $table) {
            $table->id();

            // Student who receives the notification
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Related quiz (nullable for general announcements)
            $table->foreignId('quiz_id')
                ->nullable()
                ->constrained('quizzes')
                ->nullOnDelete();

            $table->string('title');
            $table->text('message');

            // quiz, exam, announcement
            $table->string('type', 30)->default('quiz');

            // Null means unread; timestamp means read
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['student_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notifications');
    }
};
