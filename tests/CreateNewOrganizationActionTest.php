<?php

use CleaniqueCoders\LaravelOrganization\Actions\CreateNewOrganization;
use CleaniqueCoders\LaravelOrganization\Database\Factories\UserFactory;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->action = new CreateNewOrganization;
    $this->user = UserFactory::new()->create(['name' => 'John Doe']);

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
});

describe('CreateNewOrganization Action Basic Functionality', function () {
    it('can create a default organization for a user', function () {
        $organization = $this->action->handle($this->user);

        expect($organization)->toBeInstanceOf(Organization::class)
            ->and($organization->name)->toBe("John's Organization")
            ->and($organization->slug)->toBe(Str::slug("John's Organization"))
            ->and($organization->description)->toBe('Default organization for John Doe')
            ->and($organization->owner_id)->toBe($this->user->id);

        // User should be assigned this organization as default
        expect($this->user->fresh()->organization_id)->toBe($organization->id);
    });

    it('can create an additional organization with custom name', function () {
        // First create a default organization
        $defaultOrg = $this->action->handle($this->user);

        // Then create an additional organization
        $additionalOrg = $this->action->handle($this->user, false, 'My Company', 'A great company');

        expect($additionalOrg)->toBeInstanceOf(Organization::class)
            ->and($additionalOrg->name)->toBe('My Company')
            ->and($additionalOrg->slug)->toBe('my-company')
            ->and($additionalOrg->description)->toBe('A great company')
            ->and($additionalOrg->owner_id)->toBe($this->user->id);

        // User's default organization should remain unchanged
        expect($this->user->fresh()->organization_id)->toBe($defaultOrg->id);
    });

    it('prevents creating duplicate default organizations', function () {
        // Create first default organization
        $this->action->handle($this->user);

        // Attempt to create another default organization should throw exception
        expect(fn () => $this->action->handle($this->user, true))
            ->toThrow(InvalidArgumentException::class, 'User already has a default organization');
    });

    it('can create additional organizations even when default exists', function () {
        // Create default organization
        $defaultOrg = $this->action->handle($this->user);

        // Create multiple additional organizations
        $org1 = $this->action->handle($this->user, false, 'Company 1');
        $org2 = $this->action->handle($this->user, false, 'Company 2');

        expect($org1->name)->toBe('Company 1')
            ->and($org2->name)->toBe('Company 2')
            ->and($this->user->fresh()->organization_id)->toBe($defaultOrg->id);
    });

    it('generates default description when custom name is provided without description', function () {
        $organization = $this->action->handle($this->user, false, 'Custom Company');

        expect($organization->description)->toBe('Organization for John Doe');
    });

    it('allows creating default organization when user has invalid organization reference', function () {
        // Set user to have an invalid organization_id
        $this->user->organization_id = 999;
        $this->user->save();

        // Should be able to create default organization
        $organization = $this->action->handle($this->user, true);

        expect($organization)->toBeInstanceOf(Organization::class)
            ->and($this->user->fresh()->organization_id)->toBe($organization->id);
    });
});

describe('CreateNewOrganization Action Convenience Methods', function () {
    it('can create additional organization using convenience method', function () {
        $organization = $this->action->createAdditionalOrganization($this->user, 'Test Company', 'Test Description');

        expect($organization)->toBeInstanceOf(Organization::class)
            ->and($organization->name)->toBe('Test Company')
            ->and($organization->description)->toBe('Test Description')
            ->and($organization->owner_id)->toBe($this->user->id);

        // Should not be set as default organization
        expect($this->user->fresh()->organization_id)->toBeNull();
    });

    it('can check if user can create default organization', function () {
        // User without default organization can create one
        expect($this->action->canCreateDefaultOrganization($this->user))->toBeTrue();

        // After creating default organization, should not be able to create another
        $this->action->handle($this->user);
        expect($this->action->canCreateDefaultOrganization($this->user))->toBeFalse();

        // If organization reference is invalid, should be able to create
        $this->user->organization_id = 999;
        $this->user->save();
        expect($this->action->canCreateDefaultOrganization($this->user))->toBeTrue();
    });
});

describe('CreateNewOrganization Action as Artisan Command', function () {
    it('can execute as artisan command with minimal parameters', function () {
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('argument')->with('email')->andReturn($this->user->email);
        $command->shouldReceive('option')->with('organization_name')->andReturn(null);
        $command->shouldReceive('option')->with('description')->andReturn(null);
        $command->shouldReceive('info')->once();

        $this->action->asCommand($command);

        expect($this->user->fresh()->organization_id)->not->toBeNull();
    });

    it('can execute as artisan command with custom organization name', function () {
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('argument')->with('email')->andReturn($this->user->email);
        $command->shouldReceive('option')->with('organization_name')->andReturn('Custom Company');
        $command->shouldReceive('option')->with('description')->andReturn('Custom Description');
        $command->shouldReceive('info')->once();

        $this->action->asCommand($command);

        $organization = Organization::where('name', 'Custom Company')->first();
        expect($organization)->not->toBeNull()
            ->and($organization->description)->toBe('Custom Description')
            ->and($organization->owner_id)->toBe($this->user->id);
    });

    it('fails when user email is not found', function () {
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('argument')->with('email')->andReturn('nonexistent@example.com');

        expect(fn () => $this->action->asCommand($command))
            ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

describe('CreateNewOrganization Action Edge Cases', function () {
    it('handles users with single name correctly', function () {
        $singleNameUser = UserFactory::new()->create(['name' => 'Madonna']);
        $organization = $this->action->handle($singleNameUser);

        expect($organization->name)->toBe("Madonna's Organization");
    });

    it('handles special characters in organization names', function () {
        $organization = $this->action->handle($this->user, false, 'Company & Co.', 'Special chars');

        expect($organization->name)->toBe('Company & Co.')
            ->and($organization->slug)->toBe('company-co');
    });

    it('preserves null values correctly', function () {
        $organization = $this->action->handle($this->user, false, 'Test Company', null);

        expect($organization->description)->toBe('Organization for John Doe');
    });

    it('handles empty string description', function () {
        $organization = $this->action->handle($this->user, false, 'Test Company', '');

        expect($organization->description)->toBe('');
    });
});

describe('CreateNewOrganization Action Default Settings', function () {
    it('applies default settings when creating organization', function () {
        $organization = $this->action->handle($this->user);

        expect($organization->settings)->not()->toBeEmpty()
            ->and($organization->getSetting('app.timezone'))->toBe('UTC')
            ->and($organization->getSetting('app.locale'))->toBe('en')
            ->and($organization->getSetting('features.notifications'))->toBeTrue()
            ->and($organization->getSetting('features.analytics'))->toBeFalse()
            ->and($organization->getSetting('contact.email'))->toBeNull();
    });

    it('creates organization with default settings via command', function () {
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('argument')->with('email')->andReturn($this->user->email);
        $command->shouldReceive('option')->with('organization_name')->andReturn(null);
        $command->shouldReceive('option')->with('description')->andReturn(null);
        $command->shouldReceive('info')->once();

        $this->action->asCommand($command);

        $organization = Organization::where('owner_id', $this->user->id)->first();

        expect($organization->getSetting('app.timezone'))->toBe('UTC')
            ->and($organization->getSetting('features.notifications'))->toBeTrue();
    });

    it('creates additional organization with default settings', function () {
        // Create default organization first
        $defaultOrg = $this->action->handle($this->user);

        // Create additional organization
        $additionalOrg = $this->action->handle($this->user, false, 'My Company');

        expect($additionalOrg->getSetting('app.timezone'))->toBe('UTC')
            ->and($additionalOrg->getSetting('features.notifications'))->toBeTrue()
            ->and($defaultOrg->getSetting('app.timezone'))->toBe('UTC');
    });
});
