<?php

namespace ActTraining\LaravelModelVersions\Tests\Support;

use ActTraining\LaravelModelVersions\HasVersions;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasVersions;

    protected $table = 'test_models';

    protected $fillable = [
        'name',
        'description',
        'data',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }
}