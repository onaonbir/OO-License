<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Key Generator
    |--------------------------------------------------------------------------
    |
    | Specify the default key generator to use when creating new license keys.
    | Available options: 'bfb.v1', 'bfb.v2', or any custom generator you register.
    |
    */
    'default_generator' => env('OO_LICENSE_DEFAULT_GENERATOR', 'bfb.v2'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Method
    |--------------------------------------------------------------------------
    |
    | The encryption method used for encrypting sensitive data.
    | Supported: 'AES-256-CBC', 'AES-128-CBC'
    |
    */
    'encryption_method' => env('OO_LICENSE_ENCRYPTION', 'AES-256-CBC'),

    /*
    |--------------------------------------------------------------------------
    | Custom Key Generators
    |--------------------------------------------------------------------------
    |
    | Register your custom key generators here. Each generator must extend
    | the AbstractKeyGenerator class.
    |
    | Example:
    | 'custom_generators' => [
    |     'my-custom.v1' => \App\Services\MyCustomGenerator::class,
    | ],
    |
    */
    'custom_generators' => [],

    /*
    |--------------------------------------------------------------------------
    | Model Overwrites
    |--------------------------------------------------------------------------
    |
    | If you need to extend or replace the default models, you can specify
    | your custom model classes here.
    |
    */
    'models' => [
        'project' => \OnaOnbir\OOLicense\Models\Project::class,
        'project_user' => \OnaOnbir\OOLicense\Models\ProjectUser::class,
        'project_user_key' => \OnaOnbir\OOLicense\Models\ProjectUserKey::class,
        'project_user_key_activation' => \OnaOnbir\OOLicense\Models\ProjectUserKeyActivation::class,
        'project_user_key_validation' => \OnaOnbir\OOLicense\Models\ProjectUserKeyValidation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how license key validation should behave.
    |
    */
    'validation' => [
        // Check device info matches during validation
        'check_device_info' => true,

        // Strict mode: fail validation on any suspicious activity
        'strict_mode' => false,

        // Allow offline validation (future feature)
        'allow_offline' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default values for new projects and licenses.
    |
    */
    'defaults' => [
        'max_devices' => 1,
        'features' => [],
        'trial_days' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    |
    | Customize table names if needed. Leave empty to use default names.
    |
    */
    'table_names' => [
        'projects' => 'projects',
        'project_users' => 'project_users',
        'project_user_keys' => 'project_user_keys',
        'project_user_key_activations' => 'project_user_key_activations',
        'project_user_key_validations' => 'project_user_key_validations',
    ],
];
