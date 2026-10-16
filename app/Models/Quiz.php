<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'grade',
        'subject',
        'description',
        'duration_minutes',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'integer',
            'duration_minutes' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }
}
