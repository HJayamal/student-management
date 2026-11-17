
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPushToken extends Model
{
    protected $fillable = [
        'student_id',
        'expo_push_token',
        'platform',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
