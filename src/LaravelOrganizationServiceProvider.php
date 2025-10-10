<?php

namespace CleaniqueCoders\LaravelOrganization;

use CleaniqueCoders\LaravelOrganization\Actions\CreateNewOrganization;
use CleaniqueCoders\LaravelOrganization\Contracts\OrganizationContract;
use CleaniqueCoders\LaravelOrganization\Contracts\OrganizationMembershipContract;
use CleaniqueCoders\LaravelOrganization\Contracts\OrganizationOwnershipContract;
use CleaniqueCoders\LaravelOrganization\Contracts\OrganizationSettingsContract;
use CleaniqueCoders\LaravelOrganization\Livewire\CreateOrganizationForm;
use CleaniqueCoders\LaravelOrganization\Livewire\ManageOrganization;
use CleaniqueCoders\LaravelOrganization\Livewire\OrganizationList;
use CleaniqueCoders\LaravelOrganization\Livewire\OrganizationSwitcher;
use CleaniqueCoders\LaravelOrganization\Livewire\OrganizationWidget;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelOrganizationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-organization')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_organization_table')
            ->hasCommand(CreateNewOrganization::class);
    }

    public function packageRegistered(): void
    {
        // Bind contracts to the configured organization model
        // This follows the Dependency Inversion Principle
        $this->app->bind(OrganizationContract::class, function ($app) {
            return $app->make(config('organization.organization-model'));
        });

        $this->app->bind(OrganizationMembershipContract::class, function ($app) {
            return $app->make(config('organization.organization-model'));
        });

        $this->app->bind(OrganizationOwnershipContract::class, function ($app) {
            return $app->make(config('organization.organization-model'));
        });

        $this->app->bind(OrganizationSettingsContract::class, function ($app) {
            return $app->make(config('organization.organization-model'));
        });
    }

    public function packageBooted(): void
    {
        // Register Livewire components
        if (class_exists(Livewire::class)) {
            Livewire::component('org::switcher', OrganizationSwitcher::class);
            Livewire::component('org::form', CreateOrganizationForm::class);
            Livewire::component('org::manage', ManageOrganization::class);
            Livewire::component('org::list', OrganizationList::class);
            Livewire::component('org::widget', OrganizationWidget::class);
        }
    }
}
