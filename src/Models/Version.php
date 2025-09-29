<?php

namespace ActTraining\LaravelModelVersions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Version extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'versionable_type',
        'versionable_id',
        'version_number',
        'data',
        'created_by',
        'comment',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('model-versions.table_name', 'versions'));
    }

    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        $userModel = config('model-versions.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'version_number' => 'integer',
        ];
    }
}
