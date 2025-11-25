<?php

namespace OnaOnbir\OOLicense;

use Illuminate\Support\ServiceProvider;
use OnaOnbir\OOLicense\Services\KeyGeneratorRegistry;
use OnaOnbir\OOLicense\Services\KeyGenerators\BfbKeyGeneratorV1;
use OnaOnbir\OOLicense\Services\KeyGenerators\BfbKeyGeneratorV2;
use OnaOnbir\OOLicense\Services\LicenseService;

class OOLicenseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/oo-license.php',
            'oo-license'
        );

        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
    }

    /**
     * Register package services
     */
    protected function registerServices(): void
    {
        // Register KeyGeneratorRegistry as singleton
        $this->app->singleton(KeyGeneratorRegistry::class, function ($app) {
            $registry = new KeyGeneratorRegistry();

            // Register built-in generators
            $registry->register('bfb.v1', BfbKeyGeneratorV1::class);
            $registry->register('bfb.v2', BfbKeyGeneratorV2::class);

            // Load custom generators from config
            $customGenerators = config('oo-license.custom_generators', []);

            foreach ($customGenerators as $identifier => $className) {
                if (class_exists($className)) {
                    $registry->register($identifier, $className);
                }
            }

            return $registry;
        });

        // Register LicenseService as singleton
        $this->app->singleton(LicenseService::class, function ($app) {
            return new LicenseService($app->make(KeyGeneratorRegistry::class));
        });

        // Register helper alias
        $this->app->alias(LicenseService::class, 'oo-license');
    }

    /**
     * Register publishing
     */
    protected function registerPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'oo-license-migrations');

        $this->publishes([
            __DIR__.'/../config/oo-license.php' => config_path('oo-license.php'),
        ], 'oo-license-config');
    }

    /**
     * Register migrations
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
