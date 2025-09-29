<?php

namespace ActTraining\LaravelModelVersions;

use ActTraining\LaravelModelVersions\Models\Version;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasVersions
{
    protected bool $versioningDisabled = false;

    protected static function bootHasVersions(): void
    {
        static::created(function ($model) {
            if (! $model->isVersioningDisabled() && config('model-versions.auto_version_on_create', true)) {
                $model->createVersion();
            }
        });

        static::updated(function ($model) {
            if (! $model->isVersioningDisabled() &&
                config('model-versions.auto_version_on_update', true) &&
                $model->hasVersionableChanges()) {
                $model->createVersion();
            }
        });
    }

    public function initializeHasVersions(): void
    {
        $this->mergeFillable([]);
        $this->mergeGuarded(['versioningDisabled']);
    }

    public function versions(): MorphMany
    {
        $versionModel = config('model-versions.version_model', Version::class);

        return $this->morphMany($versionModel, 'versionable')
            ->orderBy('version_number', 'desc');
    }

    public function createVersion(?string $comment = null): Version
    {
        $nextVersionNumber = $this->getNextVersionNumber();

        $version = $this->versions()->create([
            'version_number' => $nextVersionNumber,
            'data' => $this->getVersionableData(),
            'created_by' => $this->getVersionCreatedBy(),
            'comment' => $comment,
        ]);

        // Refresh the relationship to ensure the new version is loaded
        $this->load('versions');

        return $version;
    }

    public function getVersion(int $versionNumber): ?Version
    {
        return $this->versions()
            ->where('version_number', $versionNumber)
            ->first();
    }

    public function getCurrentVersion(): ?Version
    {
        return $this->versions()
            ->orderBy('version_number', 'desc')
            ->first();
    }

    public function getCurrentVersionNumber(): int
    {
        $currentVersion = $this->getCurrentVersion();

        return $currentVersion ? $currentVersion->version_number : 0;
    }

    public function getVersionData(int $versionNumber): ?array
    {
        $version = $this->getVersion($versionNumber);

        return $version ? $version->data : null;
    }

    public function restoreToVersion(int $versionNumber, ?string $comment = null): bool
    {
        $version = $this->getVersion($versionNumber);

        if (! $version) {
            return false;
        }

        $this->withoutVersioning(function () use ($version) {
            $this->fill($version->data);
            $this->save();
        });

        if (config('model-versions.create_version_on_restore', true)) {
            $this->createVersion($comment ?? "Restored to version {$versionNumber}");
        }

        return true;
    }

    public function withoutVersioning(callable $callback): mixed
    {
        $this->versioningDisabled = true;

        try {
            return $callback();
        } finally {
            $this->versioningDisabled = false;
        }
    }

    public function hasVersionableChanges(): bool
    {
        $changes = $this->getDirty();

        if (empty($changes)) {
            return false;
        }

        $versionableChanges = $this->filterVersionableAttributes($changes);

        return ! empty($versionableChanges);
    }

    protected function getNextVersionNumber(): int
    {
        $lastVersion = $this->versions()
            ->orderBy('version_number', 'desc')
            ->first();

        return $lastVersion ? $lastVersion->version_number + 1 : 1;
    }

    protected function getVersionableData(): array
    {
        $data = $this->attributesToArray();

        return $this->filterVersionableAttributes($data);
    }

    protected function filterVersionableAttributes(array $attributes): array
    {
        $data = $attributes;

        // Remove default non-versionable attributes
        $defaultNonVersionable = config('model-versions.default_non_versionable_attributes', [
            'id', 'created_at', 'updated_at', 'deleted_at',
        ]);
        $data = array_diff_key($data, array_flip($defaultNonVersionable));

        // Apply whitelist if defined
        if (property_exists($this, 'versionableAttributes') && is_array($this->versionableAttributes)) {
            $data = array_intersect_key($data, array_flip($this->versionableAttributes));
        }

        // Apply blacklist if defined
        if (property_exists($this, 'nonVersionableAttributes') && is_array($this->nonVersionableAttributes)) {
            $data = array_diff_key($data, array_flip($this->nonVersionableAttributes));
        }

        return $data;
    }

    protected function getVersionCreatedBy(): ?string
    {
        if (Auth::check()) {
            return Auth::id();
        }

        return null;
    }

    protected function isVersioningDisabled(): bool
    {
        return $this->versioningDisabled === true;
    }
}