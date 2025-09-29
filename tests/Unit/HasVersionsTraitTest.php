<?php

use ActTraining\LaravelModelVersions\Tests\Support\TestModel;
use ActTraining\LaravelModelVersions\Tests\Support\TestModelWithNonVersionableAttributes;
use ActTraining\LaravelModelVersions\Tests\Support\TestModelWithVersionableAttributes;
use ActTraining\LaravelModelVersions\Tests\Support\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('creates version when model is created', function () {
    $model = TestModel::create([
        'name' => 'Test Model',
        'description' => 'Test Description',
        'data' => ['key' => 'value'],
    ]);

    expect($model->versions)->toHaveCount(1);
    expect($model->getCurrentVersionNumber())->toBe(1);

    $version = $model->getCurrentVersion();
    expect($version->data['name'])->toBe('Test Model');
    expect($version->data['description'])->toBe('Test Description');
    expect($version->data['data'])->toBe(['key' => 'value']);
});

test('creates new version when model is updated', function () {
    $model = TestModel::create(['name' => 'Original Name']);

    expect($model->versions)->toHaveCount(1);
    expect($model->getCurrentVersionNumber())->toBe(1);

    $model->update(['name' => 'Updated Name']);

    $model->refresh();
    expect($model->versions)->toHaveCount(2);
    expect($model->getCurrentVersionNumber())->toBe(2);

    $latestVersion = $model->getCurrentVersion();
    expect($latestVersion->data['name'])->toBe('Updated Name');
});

test('can retrieve specific version', function () {
    $model = TestModel::create(['name' => 'Version 1']);
    $model->update(['name' => 'Version 2']);
    $model->update(['name' => 'Version 3']);

    $version1 = $model->getVersion(1);
    $version2 = $model->getVersion(2);
    $version3 = $model->getVersion(3);

    expect($version1->data['name'])->toBe('Version 1');
    expect($version2->data['name'])->toBe('Version 2');
    expect($version3->data['name'])->toBe('Version 3');
});

test('can get version data without version model', function () {
    $model = TestModel::create(['name' => 'Test Model']);
    $model->update(['name' => 'Updated Model']);

    $version1Data = $model->getVersionData(1);
    $version2Data = $model->getVersionData(2);

    expect($version1Data['name'])->toBe('Test Model');
    expect($version2Data['name'])->toBe('Updated Model');
});

test('can restore to previous version', function () {
    $model = TestModel::create(['name' => 'Original', 'description' => 'Original desc']);
    $model->update(['name' => 'Updated', 'description' => 'Updated desc']);
    $model->update(['name' => 'Final', 'description' => 'Final desc']);

    expect($model->name)->toBe('Final');
    expect($model->description)->toBe('Final desc');

    $restored = $model->restoreToVersion(1);

    expect($restored)->toBeTrue();
    $model->refresh();
    expect($model->name)->toBe('Original');
    expect($model->description)->toBe('Original desc');
});

test('creates version when restoring by default', function () {
    $model = TestModel::create(['name' => 'Original']);
    $model->update(['name' => 'Updated']);

    expect($model->versions)->toHaveCount(2);

    $model->restoreToVersion(1);

    expect($model->versions)->toHaveCount(3);
    $restoreVersion = $model->getCurrentVersion();
    expect($restoreVersion->comment)->toBe('Restored to version 1');
});

test('returns false when restoring to non-existent version', function () {
    $model = TestModel::create(['name' => 'Test']);

    $restored = $model->restoreToVersion(999);

    expect($restored)->toBeFalse();
});

test('can disable versioning temporarily', function () {
    $model = TestModel::create(['name' => 'Original']);

    expect($model->versions)->toHaveCount(1);

    $model->withoutVersioning(function () use ($model) {
        $model->update(['name' => 'Updated']);
    });

    expect($model->versions)->toHaveCount(1);
    expect($model->name)->toBe('Updated');
});

test('versions are ordered by version number descending', function () {
    $model = TestModel::create(['name' => 'Version 1']);
    $model->update(['name' => 'Version 2']);
    $model->update(['name' => 'Version 3']);

    $versions = $model->versions;

    expect($versions->pluck('version_number')->toArray())->toBe([3, 2, 1]);
});

test('version stores created_by when user is authenticated', function () {
    Auth::login($this->user);

    $model = TestModel::create(['name' => 'Test Model']);

    $version = $model->getCurrentVersion();
    expect($version->created_by)->toBe((string) $this->user->id);
});

test('version created_by is null when no user is authenticated', function () {
    $model = TestModel::create(['name' => 'Test Model']);

    $version = $model->getCurrentVersion();
    expect($version->created_by)->toBeNull();
});

test('only creates version when versionable attributes change with nonVersionableAttributes', function () {
    $model = new TestModelWithNonVersionableAttributes;
    $model->fill([
        'name' => 'Test Model',
        'description' => 'Test Description',
        'data' => ['key' => 'value'],
    ]);
    $model->save();

    expect($model->versions)->toHaveCount(1);
    $initialVersionData = $model->getCurrentVersion()->data;
    expect($initialVersionData)->toHaveKey('name');
    expect($initialVersionData)->toHaveKey('data');
    expect($initialVersionData)->not->toHaveKey('description');
    expect($initialVersionData)->not->toHaveKey('is_active');

    // Update non-versionable attribute - should not create new version
    $model->update(['description' => 'Updated Description']);
    $model->refresh();
    expect($model->versions)->toHaveCount(1);

    // Update versionable attribute - should create new version
    $model->update(['name' => 'Updated Name']);
    $model->refresh();
    expect($model->versions)->toHaveCount(2);
});

test('only versions specified attributes with versionableAttributes', function () {
    $model = new TestModelWithVersionableAttributes;
    $model->fill([
        'name' => 'Test Model',
        'description' => 'Test Description',
        'data' => ['key' => 'value'],
    ]);
    $model->save();

    expect($model->versions)->toHaveCount(1);
    $initialVersionData = $model->getCurrentVersion()->data;
    expect($initialVersionData)->toHaveKey('name');
    expect($initialVersionData)->toHaveKey('data');
    expect($initialVersionData)->not->toHaveKey('description');
    expect($initialVersionData)->not->toHaveKey('is_active');

    // Update non-versionable attributes - should not create new version
    $model->update(['description' => 'Updated Description', 'is_active' => false]);
    $model->refresh();
    expect($model->versions)->toHaveCount(1);

    // Update versionable attribute - should create new version
    $model->update(['data' => ['key' => 'updated_value']]);
    $model->refresh();
    expect($model->versions)->toHaveCount(2);

    $latestVersionData = $model->getCurrentVersion()->data;
    expect($latestVersionData['data'])->toBe(['key' => 'updated_value']);
    expect($latestVersionData)->not->toHaveKey('description');
    expect($latestVersionData)->not->toHaveKey('is_active');
});

test('hasVersionableChanges works correctly', function () {
    $model = new TestModelWithNonVersionableAttributes;
    $model->fill([
        'name' => 'Test Model',
        'description' => 'Test Description',
        'data' => ['key' => 'value'],
    ]);
    $model->save();

    // Change non-versionable attribute
    $model->description = 'Updated Description';
    expect($model->hasVersionableChanges())->toBeFalse();

    // Change versionable attribute
    $model->name = 'Updated Name';
    expect($model->hasVersionableChanges())->toBeTrue();
});

test('can create version with custom comment', function () {
    $model = TestModel::create(['name' => 'Test Model']);

    $version = $model->createVersion('Custom comment');

    expect($version->comment)->toBe('Custom comment');
    expect($model->versions)->toHaveCount(2); // Initial + manual
});

test('restoreToVersion accepts custom comment', function () {
    $model = TestModel::create(['name' => 'Original']);
    $model->update(['name' => 'Updated']);

    $model->restoreToVersion(1, 'Manual restore');

    $restoreVersion = $model->getCurrentVersion();
    expect($restoreVersion->comment)->toBe('Manual restore');
});