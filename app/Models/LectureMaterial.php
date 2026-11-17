<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LectureMaterial extends Model
{
    protected $fillable = [
        'teacher_id',
        'teacher_name',
        'title',
        'description',
        'material_type',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'external_url',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];
}
