<?php

use ActTraining\LaravelModelVersions\Models\Version;
use ActTraining\LaravelModelVersions\Tests\Support\TestModel;
use ActTraining\LaravelModelVersions\Tests\Support\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $this->model = TestModel::create([
        'name' => 'Test Model',
        'description' => 'Test Description',
    ]);
});

test('version model has correct fillable attributes', function () {
    $version = new Version;

    expect($version->getFillable())->toBe([
        'versionable_type',
        'versionable_id',
        'version_number',
        'data',
        'created_by',
        'comment',
    ]);
});

test('version model has correct casts', function () {
    $version = new Version;

    expect($version->getCasts())->toEqual([
        'id' => 'int',
        'data' => 'array',
        'version_number' => 'integer',
    ]);
});

test('version model does not have updated_at timestamp', function () {
    expect(Version::UPDATED_AT)->toBeNull();
});

test('version model uses configured table name', function () {
    config(['model-versions.table_name' => 'custom_versions']);

    $version = new Version;

    expect($version->getTable())->toBe('custom_versions');
});

test('version model has versionable relationship', function () {
    $version = $this->model->getCurrentVersion();

    expect($version->versionable)->toBeInstanceOf(TestModel::class);
    expect($version->versionable->id)->toBe($this->model->id);
});

test('version model has creator relationship', function () {
    $version = Version::create([
        'versionable_type' => TestModel::class,
        'versionable_id' => $this->model->id,
        'version_number' => 1,
        'data' => ['name' => 'Test'],
        'created_by' => $this->user->id,
    ]);

    expect($version->creator)->toBeInstanceOf(User::class);
    expect($version->creator->id)->toBe($this->user->id);
});

test('version model creator relationship uses configured user model', function () {
    config(['model-versions.user_model' => User::class]);

    $version = Version::create([
        'versionable_type' => TestModel::class,
        'versionable_id' => $this->model->id,
        'version_number' => 1,
        'data' => ['name' => 'Test'],
        'created_by' => $this->user->id,
    ]);

    expect($version->creator)->toBeInstanceOf(User::class);
});