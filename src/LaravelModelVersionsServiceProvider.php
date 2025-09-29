<?php

namespace ActTraining\LaravelModelVersions;

use Illuminate\Support\ServiceProvider;

class LaravelModelVersionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/model-versions.php',
            'model-versions'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Config/model-versions.php' => config_path('model-versions.php'),
            ], 'model-versions-config');

            $this->publishes([
                __DIR__.'/../stubs/create_versions_table.php.stub' => $this->getMigrationFileName('create_versions_table.php'),
            ], 'model-versions-migrations');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getMigrationFileName(string $migrationName): string
    {
        $timestamp = date('Y_m_d_His');

        return database_path("migrations/{$timestamp}_{$migrationName}");
    }
}