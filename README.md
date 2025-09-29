# Laravel Model Versions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/act-training/laravel-model-versions.svg?style=flat-square)](https://packagist.org/packages/act-training/laravel-model-versions)
[![Total Downloads](https://img.shields.io/packagist/dt/act-training/laravel-model-versions.svg?style=flat-square)](https://packagist.org/packages/act-training/laravel-model-versions)

A Laravel package for automatic model versioning with selective attribute tracking and restore capabilities. Track changes to your Eloquent models with complete version history, restore to previous versions, and maintain a complete audit trail.

## Features

- 🚀 **Automatic Versioning**: Automatically create versions when models are created or updated
- 🎯 **Selective Versioning**: Choose which attributes to version using `versionableAttributes` or `nonVersionableAttributes`
- ⏪ **Version Restoration**: Restore models to any previous version with a single method call
- 👤 **User Tracking**: Automatically track which user created each version
- 💬 **Version Comments**: Add comments to versions for better change tracking
- ⚙️ **Configurable**: Extensive configuration options to fit your needs
- 🧪 **Well Tested**: Comprehensive test suite with 100% code coverage
- 🔒 **Temporary Disabling**: Disable versioning temporarily when needed

## Installation

You can install the package via composer:

```bash
composer require act-training/laravel-model-versions
```

Publish and run the migrations:

```bash
php artisan vendor:publish --provider="ActTraining\LaravelModelVersions\LaravelModelVersionsServiceProvider" --tag="model-versions-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="ActTraining\LaravelModelVersions\LaravelModelVersionsServiceProvider" --tag="model-versions-config"
```

## Quick Start

Add the `HasVersions` trait to your model:

```php
<?php

use ActTraining\LaravelModelVersions\HasVersions;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasVersions;

    protected $fillable = ['title', 'content', 'status'];
}
```

That's it! Your model will now automatically create versions when created or updated:

```php
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is the content...',
    'status' => 'draft'
]);

// Automatically creates version 1

$post->update(['title' => 'My Updated Post']);

// Automatically creates version 2

// Get all versions
$versions = $post->versions; // Collection of Version models

// Get current version number
$currentVersion = $post->getCurrentVersionNumber(); // 2

// Get specific version data
$version1Data = $post->getVersionData(1);
// Returns: ['title' => 'My First Post', 'content' => 'This is the content...', 'status' => 'draft']

// Restore to previous version
$post->restoreToVersion(1);
// Post is now back to original state, creates version 3 with restore info
```

## Usage

### Selective Attribute Versioning

#### Using `versionableAttributes` (Whitelist)

Only version specific attributes:

```php
class Post extends Model
{
    use HasVersions;

    protected $fillable = ['title', 'content', 'view_count', 'status'];

    // Only version title and content
    protected $versionableAttributes = ['title', 'content'];
}
```

#### Using `nonVersionableAttributes` (Blacklist)

Version all attributes except specified ones:

```php
class Post extends Model
{
    use HasVersions;

    protected $fillable = ['title', 'content', 'view_count', 'last_viewed_at'];

    // Version everything except view tracking fields
    protected $nonVersionableAttributes = ['view_count', 'last_viewed_at'];
}
```

### Manual Version Creation

Create versions manually with optional comments:

```php
$post = Post::create(['title' => 'Draft Post']);

// Create version with comment
$version = $post->createVersion('Initial draft created');

$post->update(['status' => 'published']);
$post->createVersion('Published the post');
```

### Version Restoration

Restore to any previous version:

```php
// Restore to version 2
$success = $post->restoreToVersion(2);

// Restore with custom comment
$success = $post->restoreToVersion(2, 'Reverted due to content issue');

// Check if restore was successful
if ($success) {
    echo "Restored successfully!";
} else {
    echo "Version not found!";
}
```

### Temporary Versioning Disabling

Disable versioning for specific operations:

```php
$post->withoutVersioning(function () use ($post) {
    $post->update(['view_count' => $post->view_count + 1]);
    $post->update(['last_viewed_at' => now()]);
    // No versions created for these updates
});

// Or disable for a single operation
$post->versioningDisabled = true;
$post->update(['view_count' => 100]);
$post->versioningDisabled = false;
```

### Working with Versions

```php
// Get all versions (ordered by version number descending)
$versions = $post->versions;

// Get current version
$currentVersion = $post->getCurrentVersion();

// Get specific version
$version = $post->getVersion(3);

// Get version data without loading the version model
$data = $post->getVersionData(3);

// Check current version number
$versionNumber = $post->getCurrentVersionNumber();

// Check if model has versionable changes
if ($post->hasVersionableChanges()) {
    $post->createVersion('Manual save');
}
```

### Version Model Relationships

```php
// Get the version creator (User model)
$version = $post->getVersion(1);
$creator = $version->creator; // User model or null

// Get the versionable model from version
$originalPost = $version->versionable; // Post model

// Access version data
$versionData = $version->data; // Array of model attributes
$versionNumber = $version->version_number; // Integer
$comment = $version->comment; // String or null
$createdAt = $version->created_at; // Carbon instance
```

## Configuration

The package comes with sensible defaults, but you can customize behavior in `config/model-versions.php`:

```php
return [
    // Database table name for versions
    'table_name' => env('MODEL_VERSIONS_TABLE', 'versions'),

    // User model for created_by relationships
    'user_model' => env('MODEL_VERSIONS_USER_MODEL', 'App\\Models\\User'),

    // Custom version model (if you need to extend it)
    'version_model' => ActTraining\LaravelModelVersions\Models\Version::class,

    // Auto-versioning settings
    'auto_version_on_create' => env('MODEL_VERSIONS_AUTO_CREATE', true),
    'auto_version_on_update' => env('MODEL_VERSIONS_AUTO_UPDATE', true),

    // Create version when restoring
    'create_version_on_restore' => env('MODEL_VERSIONS_VERSION_ON_RESTORE', true),

    // Default attributes excluded from versioning
    'default_non_versionable_attributes' => [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ],
];
```

### Environment Variables

You can configure the package using environment variables:

```env
MODEL_VERSIONS_TABLE=custom_versions
MODEL_VERSIONS_USER_MODEL=App\Models\CustomUser
MODEL_VERSIONS_AUTO_CREATE=true
MODEL_VERSIONS_AUTO_UPDATE=true
MODEL_VERSIONS_VERSION_ON_RESTORE=false
```

## Advanced Usage

### Custom Version Model

Create a custom version model to add additional functionality:

```php
<?php

namespace App\Models;

use ActTraining\LaravelModelVersions\Models\Version as BaseVersion;

class CustomVersion extends BaseVersion
{
    protected $fillable = [
        ...parent::getFillable(),
        'custom_field',
    ];

    public function customMethod()
    {
        // Your custom logic
    }
}
```

Then update your config:

```php
// config/model-versions.php
'version_model' => App\Models\CustomVersion::class,
```

### Disabling Auto-Versioning Globally

You can disable auto-versioning globally and create versions manually:

```php
// config/model-versions.php
'auto_version_on_create' => false,
'auto_version_on_update' => false,
```

```php
$post = Post::create(['title' => 'New Post']);
// No version created automatically

$post->createVersion('Initial creation');
// Manual version creation
```

### Performance Considerations

For models with frequent updates, consider:

1. **Selective versioning**: Only version important attributes
2. **Disable auto-versioning**: Create versions manually at important milestones
3. **Cleanup old versions**: Implement a cleanup strategy for very old versions

```php
// Example: Only keep last 10 versions
$oldVersions = $post->versions()
    ->orderBy('version_number', 'desc')
    ->skip(10)
    ->get();

$oldVersions->each->delete();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ACT Training](https://github.com/ACT-Training)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
