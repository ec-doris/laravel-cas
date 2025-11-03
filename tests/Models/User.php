<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected static function newFactory()
    {
        return \App\Models\Factories\UserFactory::new();
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'departmentNumber',
        'department_number',
        'organisation',
    ];
}
