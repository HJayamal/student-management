<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'reg_no',
        'name',
        'first_name',
        'last_name',
        'gender',
        'nic',
        'email',
        'phone',
        'dob',
        'password',
        'address',
        'image',
    ];

    protected $hidden = [
        'password',
    ];

    public function admission(): HasOne
    {
        return $this->hasOne(Admission::class, 'reg_no', 'reg_no');
    }
}
