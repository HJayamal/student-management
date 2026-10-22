<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'degree_id',
        'batch_name',
        'start_date',
        'end_date',
    ];

    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }
}
