<?php

use ActTraining\LaravelModelVersions\Tests\Support\TestModel;
use ActTraining\LaravelModelVersions\Tests\Support\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('complete versioning workflow', function () {
    Auth::login($this->user);

    // Create initial model
    $model = TestModel::create([
        'name' => 'Initial Name',
        'description' => 'Initial Description',
        'data' => ['status' => 'draft'],
        'is_active' => false,
    ]);

    expect($model->versions)->toHaveCount(1);
    $version1 = $model->getCurrentVersion();
    expect($version1->version_number)->toBe(1)
        ->and($version1->created_by)->toBe((string) $this->user->id);

    // Update the model multiple times
    $model->update(['name' => 'Updated Name']);
    expect($model->versions)->toHaveCount(2);

    $model->update(['description' => 'Updated Description']);
    expect($model->versions)->toHaveCount(3);

    $model->update(['data' => ['status' => 'published']]);
    expect($model->versions)->toHaveCount(4);

    // Test version retrieval
    $currentVersion = $model->getCurrentVersion();
    expect($currentVersion->version_number)->toBe(4)
        ->and($currentVersion->data['name'])->toBe('Updated Name')
        ->and($currentVersion->data['description'])->toBe('Updated Description')
        ->and($currentVersion->data['data'])->toBe(['status' => 'published']);

    // Test restoration
    $model->restoreToVersion(2, 'Reverting changes');
    expect($model->versions)->toHaveCount(5)
        ->and($model->name)->toBe('Updated Name')
        ->and($model->description)->toBe('Initial Description');

    $restoreVersion = $model->getCurrentVersion();
    expect($restoreVersion->comment)->toBe('Reverting changes');
});

test('versioning with configuration changes', function () {
    // Test with auto-versioning disabled on update
    config(['model-versions.auto_version_on_update' => false]);

    $model = TestModel::create(['name' => 'Initial']);
    expect($model->versions)->toHaveCount(1);

    $model->update(['name' => 'Updated']);
    expect($model->versions)->toHaveCount(1); // No new version created

    // Manually create version
    $model->createVersion('Manual version');
    expect($model->versions)->toHaveCount(2)
        ->and($model->getCurrentVersion()->comment)->toBe('Manual version');
});

test('versioning with disabled restore versioning', function () {
    config(['model-versions.create_version_on_restore' => false]);

    $model = TestModel::create(['name' => 'Original']);
    $model->update(['name' => 'Updated']);
    expect($model->versions)->toHaveCount(2);

    $model->restoreToVersion(1);
    expect($model->versions)->toHaveCount(2); // No new version created on restore
});

test('performance with multiple versions', function () {
    $model = TestModel::create(['name' => 'Initial']);

    // Create many versions
    for ($i = 1; $i <= 50; $i++) {
        $model->update(['description' => "Version {$i} description"]);
    }

    expect($model->versions)->toHaveCount(51)
        ->and($model->getCurrentVersionNumber())->toBe(51);

    // Test that versions are properly ordered
    $versions = $model->versions;
    $versionNumbers = $versions->pluck('version_number')->toArray();
    expect($versionNumbers)->toBe(range(51, 1));

    // Test specific version retrieval
    $version25 = $model->getVersion(25);
    expect($version25->data['description'])->toBe('Version 24 description');
});

test('versioning without authentication', function () {
    // Ensure no user is authenticated
    Auth::logout();

    $model = TestModel::create(['name' => 'Test']);

    $version = $model->getCurrentVersion();
    expect($version->created_by)->toBeNull();
});

test('complex data versioning', function () {
    $complexData = [
        'settings' => [
            'theme' => 'dark',
            'notifications' => true,
            'features' => ['feature1', 'feature2'],
        ],
        'metadata' => [
            'tags' => ['important', 'draft'],
            'score' => 95.5,
        ],
    ];

    $model = TestModel::create([
        'name' => 'Complex Model',
        'data' => $complexData,
    ]);

    $version = $model->getCurrentVersion();
    expect($version->data['data'])->toBe($complexData);

    // Update nested data
    $model->update([
        'data' => array_merge($complexData, [
            'settings' => array_merge($complexData['settings'], ['theme' => 'light']),
        ]),
    ]);

    $newVersion = $model->getCurrentVersion();
    expect($newVersion->data['data']['settings']['theme'])->toBe('light');

    // Restore to previous version
    $model->restoreToVersion(1);
    expect($model->data['settings']['theme'])->toBe('dark');
});
