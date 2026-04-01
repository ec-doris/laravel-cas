<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ExternalUser extends Authenticatable
{
    protected $table = 'external_users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
