<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptSession extends Model
{
    use HasFactory;

    protected $table = 'quiz_attempt_sessions';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'answers',
        'started_at',
        'deadline_at',
        'status',
        'submitted_at',
        'quiz_attempt_id',
    ];

    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'deadline_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }
}
