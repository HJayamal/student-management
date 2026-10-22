<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_code',
        'course_name',
        'degree_id',
        'subject_name',
        'teacher',
        'description',
    ];

    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }
}
