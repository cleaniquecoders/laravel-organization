# Laravel Organization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/laravel-organization.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-organization)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-organization/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/laravel-organization/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-organization/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/laravel-organization/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/laravel-organization.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-organization)

A comprehensive Laravel package for implementing organization-based tenancy in your application. This package provides a complete solution for managing organizations, user memberships, roles, and automatic data scoping based on the authenticated user's organization context.

## Features

- **Organization Management**: Create and manage organizations with owners, descriptions, and comprehensive settings
- **User Membership**: Flexible user-organization relationships with role-based access control
- **Role System**: Built-in roles (Member, Administrator) with extensible enum-based architecture
- **Automatic Data Scoping**: Global scope that automatically filters data by user's current organization
- **UUID Support**: Built-in UUID generation for organizations with slug-based URLs
- **Soft Deletes**: Safe deletion of organizations with data preservation
- **Comprehensive Settings Management**: Extensive JSON-based settings with validation rules covering:
  - Contact information and business details
  - Application preferences (timezone, locale, currency)
  - Feature toggles and UI/UX preferences
  - Security settings and billing configuration
  - Integration settings for external services
- **Settings Validation**: Built-in validation rules ensure data integrity for all organization settings
- **Command Line Tools**: Artisan commands for organization management and user assignment
- **Laravel Actions Integration**: Clean action-based architecture using `lorisleiva/laravel-actions`
- **Factory Support**: Built-in factories for testing and seeding with realistic data
- **Trait-based Integration**: Easy integration with existing models using the `InteractsWithOrganization` trait

## Installation

You can install the package via composer:

```bash
composer require cleaniquecoders/laravel-organization
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-organization-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-organization-config"
```

This is the contents of the published config file:

```php
<?php

/**
 * Configuration file for CleaniqueCoders/LaravelOrganization
 *
 * This configuration file contains settings for the Laravel Organization package
 * that manages organization-based tenancy in your Laravel application.
 */

use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Foundation\Auth\User;

return [

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This option defines the User model class that will be used throughout
    | the organization system. The model should extend Illuminate\Foundation\Auth\User
    | or implement the necessary contracts for authentication.
    |
    */

    'user-model' => User::class,

    /*
    |--------------------------------------------------------------------------
    | Organization Model
    |--------------------------------------------------------------------------
    |
    | This option defines the Organization model class that will be used throughout
    | the organization system. The model should extend CleaniqueCoders\LaravelOrganization\Models\Organization
    | or implement the necessary contracts for organization management.
    |
    */

    'organization-model' => Organization::class,

    /*
    |--------------------------------------------------------------------------
    | Default Organization Settings
    |--------------------------------------------------------------------------
    |
    | These are the default settings that will be applied to new organizations
    | when they are created. Organizations can override these settings individually
    | using the setSetting() method on the Organization model.
    |
    */

    'default-settings' => [
        // Contact information, address, social media, business info,
        // application settings, feature toggles, UI preferences,
        // security settings, billing, and integrations
        // ... (see config/organization.php for full structure)
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Define validation rules for organization settings to ensure data integrity.
    |
    */

    'validation_rules' => [
        // Validation rules for various settings
        // ... (see config/organization.php for complete rules)
    ],

];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-organization-views"
```

## Configuration

The package configuration allows you to customize several aspects of the organization system:

### Model Configuration

- **`user-model`**: Specify your User model class (default: `Illuminate\Foundation\Auth\User`)
- **`organization-model`**: Specify the Organization model class (default: `CleaniqueCoders\LaravelOrganization\Models\Organization`)

### Default Organization Settings

The `default-settings` configuration defines the initial settings for new organizations. These include:

- **Contact Information**: Email, phone, fax, website
- **Address Information**: Street, city, state, postal code, country
- **Social Media Links**: Facebook, Twitter, LinkedIn, Instagram, YouTube, GitHub
- **Business Information**: Industry, company size, founded year, tax ID, registration number
- **Application Settings**: Timezone, locale, currency, date/time formats
- **Feature Toggles**: Notifications, analytics, API access, custom branding, multi-language
- **UI/UX Preferences**: Theme, sidebar state, layout, pagination
- **Security Settings**: Two-factor requirements, password expiry, session timeout, allowed domains
- **Billing & Subscription**: Plan, billing cycle, auto-renewal, billing email
- **Integration Settings**: Email provider, storage provider, payment gateway, SMS provider

### Validation Rules

The package includes built-in validation rules for organization settings to ensure data integrity. You can extend or modify these rules as needed.

## Usage

### Basic Organization Creation

Create organizations programmatically using the `CreateNewOrganization` action:

```php
use CleaniqueCoders\LaravelOrganization\Actions\CreateNewOrganization;
use App\Models\User;

$user = User::find(1);
$action = new CreateNewOrganization();

// Create a default organization for the user
$organization = $action->handle($user);

// Create an additional organization with custom name and description
$customOrg = $action->handle(
    user: $user,
    default: false,
    customName: 'My Company',
    customDescription: 'A great company'
);
```

### Using Artisan Commands

Create organizations via command line:

```bash
# Create default organization for user
php artisan user:create-org user@example.com

# Create additional organization with custom name
php artisan user:create-org user@example.com --organization_name="My Company"

# Create with custom name and description
php artisan user:create-org user@example.com --organization_name="My Company" --description="A great company"
```

### Organization Model Usage

Work with organizations and their members:

```php
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use CleaniqueCoders\LaravelOrganization\Enums\OrganizationRole;

$organization = Organization::find(1);

// Check ownership
if ($organization->isOwnedBy($user)) {
    // User owns this organization
}

// Add users to organization
$organization->addUser($user, OrganizationRole::ADMINISTRATOR);
$organization->addUser($anotherUser, OrganizationRole::MEMBER);

// Check membership
if ($organization->hasMember($user)) {
    // User is a member
}

// Get organization members
$administrators = $organization->administrators;
$members = $organization->members;
$allActiveMembers = $organization->allMembers();

// Update user role
$organization->updateUserRole($user, OrganizationRole::ADMINISTRATOR);

// Remove user from organization
$organization->removeUser($user);

// Manage settings
$organization->setSetting('timezone', 'UTC');
$timezone = $organization->getSetting('timezone', 'UTC');
```

### Automatic Data Scoping

The package automatically applies organization scoping to the default Laravel User model. This means:

- Users are automatically scoped to the authenticated user's organization
- When creating new users, the `organization_id` is automatically set
- You can bypass scoping when needed using the provided scope methods

```php
use App\Models\User;

// Only users from authenticated user's organization (automatic)
$users = User::all();

// Users from all organizations
$allUsers = User::allOrganizations()->get();

// Users from specific organization
$orgUsers = User::forOrganization(5)->get();
```

#### Adding Scoping to Your Own Models

You can also add the `InteractsWithOrganization` trait to your own models for automatic data scoping:

```php
use CleaniqueCoders\LaravelOrganization\Concerns\InteractsWithOrganization;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use InteractsWithOrganization;

    protected $fillable = ['title', 'content', 'organization_id'];
}
```

With this trait applied to your models:

- Models are automatically scoped to the authenticated user's organization
- The `organization_id` is automatically set when creating new records
- You can bypass scoping when needed using the same methods as shown above

### Working with Roles

Use the `OrganizationRole` enum for type-safe role management:

```php
use CleaniqueCoders\LaravelOrganization\Enums\OrganizationRole;

// Check role capabilities
if ($role->isAdmin()) {
    // User has admin privileges
}

// Get role information
$label = OrganizationRole::ADMINISTRATOR->label(); // "Administrator"
$description = OrganizationRole::MEMBER->description(); // "Regular member with basic access..."

// Check user's role in organization
if ($organization->userHasRole($user, OrganizationRole::ADMINISTRATOR)) {
    // User is an administrator
}
```

### Organization Settings

Store and retrieve organization-specific settings using the comprehensive configuration structure:

```php
$organization = Organization::find(1);

// Set contact information
$organization->setSetting('contact.email', 'info@company.com');
$organization->setSetting('contact.phone', '+1-234-567-8900');
$organization->setSetting('contact.website', 'https://company.com');

// Configure business information
$organization->setSetting('business.industry', 'Technology');
$organization->setSetting('business.company_size', '50-100');
$organization->setSetting('business.founded_year', 2020);

// Application preferences
$organization->setSetting('app.timezone', 'America/New_York');
$organization->setSetting('app.locale', 'en');
$organization->setSetting('app.currency', 'USD');

// Feature toggles
$organization->setSetting('features.notifications', true);
$organization->setSetting('features.api_access', true);
$organization->setSetting('features.custom_branding', false);

// UI/UX preferences
$organization->setSetting('ui.theme', 'dark');
$organization->setSetting('ui.items_per_page', 50);
$organization->setSetting('ui.sidebar_collapsed', false);

// Security settings
$organization->setSetting('security.two_factor_required', true);
$organization->setSetting('security.session_timeout_minutes', 60);

$organization->save();

// Get settings with defaults from config
$email = $organization->getSetting('contact.email');
$theme = $organization->getSetting('ui.theme', 'light');
$timezone = $organization->getSetting('app.timezone', 'UTC');

// Check if specific settings exist
if ($organization->hasSetting('features.api_access')) {
    // Setting exists
}

// Apply default settings to existing organization
$organization->applyDefaultSettings();

// Reset all settings to defaults
$organization->resetToDefaults();
```

### Settings Validation

The package includes comprehensive validation for organization settings to ensure data integrity:

```php
$organization = Organization::find(1);

// These will be automatically validated when saving
$organization->setSetting('contact.email', 'invalid-email'); // Will fail validation
$organization->setSetting('app.currency', 'INVALID'); // Must be 3-letter ISO code
$organization->setSetting('ui.theme', 'purple'); // Must be 'light', 'dark', or 'auto'

try {
    $organization->save();
} catch (\Illuminate\Validation\ValidationException $e) {
    // Handle validation errors
    $errors = $e->errors();
}
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

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
