<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Versions Table Name
    |--------------------------------------------------------------------------
    |
    | This option allows you to specify the database table name that will be
    | used to store model versions. You can customize this to fit your
    | application's naming conventions or to avoid conflicts.
    |
    */

    'table_name' => env('MODEL_VERSIONS_TABLE', 'versions'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This option specifies the user model that should be used for tracking
    | who created each version. This should be the fully qualified class name
    | of your application's user model.
    |
    */

    'user_model' => env('MODEL_VERSIONS_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Version Model
    |--------------------------------------------------------------------------
    |
    | If you need to customize the Version model (for example, to add custom
    | methods or relationships), you can specify your custom model here.
    | It should extend the base Version model provided by this package.
    |
    */

    'version_model' => env('MODEL_VERSIONS_VERSION_MODEL', ActTraining\LaravelModelVersions\Models\Version::class),

    /*
    |--------------------------------------------------------------------------
    | Auto-versioning Configuration
    |--------------------------------------------------------------------------
    |
    | These options control when versions are automatically created.
    | You can disable auto-versioning for specific events and create
    | versions manually using the createVersion() method.
    |
    */

    'auto_version_on_create' => env('MODEL_VERSIONS_AUTO_CREATE', true),

    'auto_version_on_update' => env('MODEL_VERSIONS_AUTO_UPDATE', true),

    /*
    |--------------------------------------------------------------------------
    | Version on Restore
    |--------------------------------------------------------------------------
    |
    | When true, restoring a model to a previous version will create a new
    | version entry documenting the restore operation. This maintains a
    | complete audit trail of all changes, including rollbacks.
    |
    */

    'create_version_on_restore' => env('MODEL_VERSIONS_VERSION_ON_RESTORE', true),

    /*
    |--------------------------------------------------------------------------
    | Default Non-Versionable Attributes
    |--------------------------------------------------------------------------
    |
    | These attributes will be excluded from versioning by default across
    | all models using the HasVersions trait. You can override this on
    | individual models using the versionableAttributes or
    | nonVersionableAttributes properties.
    |
    */

    'default_non_versionable_attributes' => [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

];