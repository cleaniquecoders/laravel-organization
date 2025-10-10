<?php

use CleaniqueCoders\LaravelOrganization\Database\Factories\UserFactory;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    // Set up test configuration with default settings
    Config::set('organization.default-settings', [
        'contact' => [
            'email' => null,
            'phone' => null,
        ],
        'app' => [
            'timezone' => 'UTC',
            'locale' => 'en',
        ],
        'features' => [
            'notifications' => true,
            'analytics' => false,
        ],
    ]);

    Config::set('organization.validation_rules', [
        'contact.email' => 'nullable|email',
        'app.timezone' => 'nullable|string',
        'features.notifications' => 'boolean',
    ]);
});

describe('Organization Default Settings', function () {
    it('applies default settings when creating organization', function () {
        $user = UserFactory::new()->create();

        $organization = Organization::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
        ]);

        expect($organization->settings)->not()->toBeNull()
            ->and($organization->getSetting('app.timezone'))->toBe('UTC')
            ->and($organization->getSetting('app.locale'))->toBe('en')
            ->and($organization->getSetting('features.notifications'))->toBeTrue()
            ->and($organization->getSetting('features.analytics'))->toBeFalse()
            ->and($organization->getSetting('contact.email'))->toBeNull();
    });

    it('merges provided settings with defaults', function () {
        $user = UserFactory::new()->create();

        $organization = Organization::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
            'settings' => [
                'contact' => [
                    'email' => 'test@example.com',
                ],
                'app' => [
                    'timezone' => 'America/New_York',
                ],
            ],
        ]);

        // Custom settings should override defaults
        expect($organization->getSetting('contact.email'))->toBe('test@example.com')
            ->and($organization->getSetting('app.timezone'))->toBe('America/New_York');

        // Defaults should still be applied for unspecified settings
        expect($organization->getSetting('app.locale'))->toBe('en')
            ->and($organization->getSetting('features.notifications'))->toBeTrue();
    });

    it('validates settings on save', function () {
        expect(function () {
            $user = UserFactory::new()->create();

            Organization::create([
                'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
                'name' => 'Test Organization',
                'slug' => 'test-organization',
                'owner_id' => $user->id,
                'settings' => [
                    'contact' => [
                        'email' => 'invalid-email',
                    ],
                ],
            ]);
        })->toThrow(ValidationException::class);
    });

    it('can get default settings', function () {
        $defaultSettings = Organization::getDefaultSettings();

        expect($defaultSettings)->toBeArray()
            ->and($defaultSettings['app']['timezone'])->toBe('UTC')
            ->and($defaultSettings['features']['notifications'])->toBeTrue();
    });

    it('can apply default settings manually', function () {
        $user = UserFactory::new()->create();

        $organization = new Organization([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
        ]);

        $organization->applyDefaultSettings();

        expect($organization->getSetting('app.timezone'))->toBe('UTC')
            ->and($organization->getSetting('features.notifications'))->toBeTrue();
    });

    it('can reset settings to defaults', function () {
        $user = UserFactory::new()->create();

        $organization = Organization::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
            'settings' => [
                'contact' => [
                    'email' => 'custom@example.com',
                ],
                'app' => [
                    'timezone' => 'America/New_York',
                ],
            ],
        ]);

        $organization->resetSettingsToDefaults();
        $organization->save();

        expect($organization->getSetting('contact.email'))->toBeNull()
            ->and($organization->getSetting('app.timezone'))->toBe('UTC');
    });

    it('can merge additional settings', function () {
        $user = UserFactory::new()->create();

        $organization = Organization::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
        ]);

        $organization->mergeSettings([
            'contact' => [
                'email' => 'new@example.com',
            ],
            'custom' => [
                'setting' => 'value',
            ],
        ]);

        expect($organization->getSetting('contact.email'))->toBe('new@example.com')
            ->and($organization->getSetting('custom.setting'))->toBe('value');

        // Original defaults should still be present
        expect($organization->getSetting('app.timezone'))->toBe('UTC');
    });

    it('can check if setting exists', function () {
        $user = UserFactory::new()->create();

        $organization = Organization::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
            'settings' => [
                'contact' => [
                    'email' => 'test@example.com',
                ],
            ],
        ]);

        expect($organization->hasSetting('contact.email'))->toBeTrue()
            ->and($organization->hasSetting('app.timezone'))->toBeTrue() // From defaults
            ->and($organization->hasSetting('nonexistent.setting'))->toBeFalse();
    });

    it('can remove settings', function () {
        $user = UserFactory::new()->create();

        $organization = Organization::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
            'settings' => [
                'contact' => [
                    'email' => 'test@example.com',
                ],
            ],
        ]);

        expect($organization->hasSetting('contact.email'))->toBeTrue();

        $organization->removeSetting('contact.email');

        expect($organization->hasSetting('contact.email'))->toBeFalse();
    });

    it('handles empty configuration gracefully', function () {
        Config::set('organization.default-settings', []);
        Config::set('organization.validation_rules', []);

        $user = UserFactory::new()->create();

        $organization = Organization::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
        ]);

        expect($organization->settings)->toBe([]);
    });

    it('validates settings with custom rules', function () {
        Config::set('organization.validation_rules', [
            'contact.email' => 'required|email',
            'features.notifications' => 'required|boolean',
        ]);

        expect(function () {
            $user = UserFactory::new()->create();

            Organization::create([
                'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
                'name' => 'Test Organization',
                'slug' => 'test-organization',
                'owner_id' => $user->id,
                'settings' => [
                    'contact' => [
                        'email' => null, // This will fail required rule
                    ],
                ],
            ]);
        })->toThrow(ValidationException::class);
    });

    it('preserves existing settings when applying defaults', function () {
        $user = UserFactory::new()->create();

        $organization = new Organization([
            'uuid' => \Illuminate\Support\Str::orderedUuid()->toString(),
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'owner_id' => $user->id,
            'settings' => [
                'contact' => [
                    'email' => 'existing@example.com',
                ],
                'custom' => [
                    'value' => 'preserved',
                ],
            ],
        ]);

        $organization->applyDefaultSettings();

        // Existing values should be preserved
        expect($organization->getSetting('contact.email'))->toBe('existing@example.com')
            ->and($organization->getSetting('custom.value'))->toBe('preserved');

        // Defaults should be applied for missing values
        expect($organization->getSetting('app.timezone'))->toBe('UTC');
    });
});
