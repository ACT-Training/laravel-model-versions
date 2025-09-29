<?php

namespace ActTraining\LaravelModelVersions\Tests\Support;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait;

    protected $fillable = [
        'name',
        'email',
    ];
}
