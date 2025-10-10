<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views from workbench
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'workbench');

        // Load package views for Livewire components
        $this->loadViewsFrom(__DIR__.'/../../../resources/views', 'laravel-organization');

        // Also make views available without namespace
        $viewPath = __DIR__.'/../../resources/views';
        if (is_dir($viewPath)) {
            $this->app['view']->addLocation($viewPath);
        }

        // Publish package assets for testing
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../../config/organization.php' => config_path('organization.php'),
            ], 'laravel-organization-config');

            $this->publishes([
                __DIR__.'/../../../database/migrations/create_organization_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_organization_table.php'),
            ], 'laravel-organization-migrations');
        }
    }
}
