# Laravel Organization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/laravel-organization.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-organization)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-organization/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/laravel-organization/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-organization/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/laravel-organization/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/laravel-organization.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-organization)

A comprehensive Laravel package for implementing organization-based tenancy in your application. This package provides a complete solution for managing organizations, user memberships, roles, and automatic data scoping based on the authenticated user's organization context.

## Features

- **Organization Management**: Create and manage organizations with owners, descriptions, and custom settings
- **User Membership**: Flexible user-organization relationships with role-based access control
- **Role System**: Built-in roles (Member, Administrator) with extensible enum-based architecture
- **Automatic Data Scoping**: Global scope that automatically filters data by user's current organization
- **UUID Support**: Built-in UUID generation for organizations with slug-based URLs
- **Soft Deletes**: Safe deletion of organizations with data preservation
- **Settings Management**: JSON-based settings storage for organization-specific configurations
- **Command Line Tools**: Artisan commands for organization management
- **Laravel Actions Integration**: Clean action-based architecture using `lorisleiva/laravel-actions`
- **Factory Support**: Built-in factories for testing and seeding

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

// config for CleaniqueCoders/LaravelOrganization

use Illuminate\Foundation\Auth\User;

return [
    'user-model' => User::class,
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-organization-views"
```

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

Store and retrieve organization-specific settings:

```php
$organization = Organization::find(1);

// Set nested settings
$organization->setSetting('features.notifications', true);
$organization->setSetting('ui.theme', 'dark');
$organization->save();

// Get settings with defaults
$notifications = $organization->getSetting('features.notifications', false);
$theme = $organization->getSetting('ui.theme', 'light');
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
