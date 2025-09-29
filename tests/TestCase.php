<?php

namespace ActTraining\LaravelModelVersions\Tests;

use ActTraining\LaravelModelVersions\LaravelModelVersionsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'ActTraining\\LaravelModelVersions\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelModelVersionsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('model-versions.user_model', 'ActTraining\\LaravelModelVersions\\Tests\\Support\\User');

        $migration = include __DIR__.'/../database/migrations/2024_01_01_000000_create_versions_table.php';
        $migration->up();

        // Create a users table for testing
        $app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Create a test_models table for testing
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }
}
