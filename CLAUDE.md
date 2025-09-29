# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package called **Laravel Model Versions** that provides automatic model versioning capabilities for Eloquent models. The package allows tracking changes to models with complete version history, selective attribute versioning, and restore capabilities.

## Commands

### Testing
```bash
# Run tests using PHPUnit
vendor/bin/phpunit

# Run tests using Pest (preferred test framework)
vendor/bin/pest

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage-html
```

### Composer
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Autoload optimization
composer dump-autoload
```

## Package Architecture

### Core Components

1. **HasVersions Trait** (`src/HasVersions.php`)
   - Main trait that models use to enable versioning
   - Handles automatic versioning on create/update via Eloquent events
   - Provides methods for creating versions, restoring to versions, and managing versioning state
   - Supports selective attribute versioning via `versionableAttributes` or `nonVersionableAttributes` properties

2. **Version Model** (`src/Models/Version.php`)
   - Eloquent model representing a single version record
   - Stores versioned data as JSON in the `data` column
   - Has polymorphic relationship to any versionable model
   - Tracks who created the version and optional comments

3. **Configuration** (`src/Config/model-versions.php`)
   - Configurable table name, user model, and version model
   - Auto-versioning toggles for create/update operations
   - Default non-versionable attributes (id, timestamps, etc.)

4. **Service Provider** (`src/LaravelModelVersionsServiceProvider.php`)
   - Registers configuration and publishes migrations

### Database Schema

The package uses a single `versions` table with:
- `versionable_type` and `versionable_id` for polymorphic relationship
- `version_number` for sequential versioning per model
- `data` JSON column storing the model attributes
- `created_by` to track the user who created the version
- `comment` for optional version descriptions

### Key Features

1. **Automatic Versioning**: Versions are created automatically on model create/update (configurable)
2. **Selective Versioning**: Use `versionableAttributes` (whitelist) or `nonVersionableAttributes` (blacklist)
3. **Version Restoration**: Restore models to any previous version
4. **Temporary Disabling**: Use `withoutVersioning()` or `versioningDisabled` property
5. **User Tracking**: Automatically tracks authenticated user for each version
6. **Comments**: Add descriptive comments to versions

### Usage Patterns

Models using versioning should:
- Add `use HasVersions;` trait
- Optionally define `$versionableAttributes` or `$nonVersionableAttributes` arrays
- Use `createVersion()` for manual version creation
- Use `restoreToVersion($number)` to restore previous versions
- Use `withoutVersioning(callable)` to temporarily disable versioning

## Testing Structure

- Uses **Pest** as the primary testing framework with PHPUnit as fallback
- **TestCase** (`tests/TestCase.php`) extends Orchestra Testbench for package testing
- Test models in `tests/Support/` demonstrate different versioning configurations
- Tests are organized into Feature and Unit directories
- Uses in-memory SQLite database for testing