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

```php
$laravelOrganization = new CleaniqueCoders\LaravelOrganization();
echo $laravelOrganization->echoPhrase('Hello, CleaniqueCoders!');
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
