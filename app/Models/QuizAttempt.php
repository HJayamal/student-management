<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $table = 'quiz_attempts';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'answers',
        'submitted_at',
        'marks_obtained',
        'total_marks',
        'percentage',
        'graded_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'marks_obtained' => 'integer',
        'total_marks' => 'integer',
        'percentage' => 'decimal:2',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
