<?php

use CleaniqueCoders\LaravelOrganization\Database\Factories\OrganizationFactory;
use CleaniqueCoders\LaravelOrganization\Database\Factories\UserFactory;
use CleaniqueCoders\LaravelOrganization\Enums\OrganizationRole;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = UserFactory::new()->create();
    $this->organization = OrganizationFactory::new()->ownedBy($this->user)->create();
});

describe('Organization Model Basic Functionality', function () {
    it('can create an organization', function () {
        expect($this->organization)
            ->toBeInstanceOf(Organization::class)
            ->and($this->organization->name)->not->toBeEmpty()
            ->and($this->organization->slug)->not->toBeEmpty()
            ->and($this->organization->owner_id)->toBe($this->user->id);
    });

    it('has fillable attributes', function () {
        $organization = new Organization;
        $expected = ['uuid', 'name', 'slug', 'description', 'settings', 'owner_id'];

        expect($organization->getFillable())->toBe($expected);
    });

    it('casts settings to array', function () {
        $settings = ['timezone' => 'UTC', 'locale' => 'en'];
        $this->organization->update(['settings' => $settings]);

        expect($this->organization->fresh()->settings)->toBe($settings);
    });

    it('uses soft deletes', function () {
        $organizationId = $this->organization->id;
        $this->organization->delete();

        expect(Organization::find($organizationId))->toBeNull()
            ->and(Organization::withTrashed()->find($organizationId))->not->toBeNull();
    });

    it('generates uuid automatically', function () {
        expect($this->organization->uuid)->not->toBeEmpty();
    });

    it('generates slug automatically', function () {
        $organization = OrganizationFactory::new()->create();

        expect($organization->slug)->toBe(Str::slug($organization->name));
    });
});

describe('Organization Relationships', function () {
    it('belongs to an owner', function () {
        expect($this->organization->owner)->toBeInstanceOf(User::class)
            ->and($this->organization->owner->id)->toBe($this->user->id);
    });

    it('has many users through pivot table', function () {
        $member = UserFactory::new()->create();
        $this->organization->addUser($member, OrganizationRole::MEMBER);

        expect($this->organization->users()->count())->toBe(1)
            ->and($this->organization->users->first())->toBeInstanceOf(User::class)
            ->and($this->organization->users->first()->pivot->role)->toBe('member');
    });

    it('filters active users', function () {
        $activeUser = UserFactory::new()->create();
        $inactiveUser = UserFactory::new()->create();

        $this->organization->addUser($activeUser, OrganizationRole::MEMBER, true);
        $this->organization->addUser($inactiveUser, OrganizationRole::MEMBER, false);

        expect($this->organization->activeUsers()->count())->toBe(1)
            ->and($this->organization->activeUsers->first()->id)->toBe($activeUser->id);
    });

    it('filters administrators', function () {
        $admin = UserFactory::new()->create();
        $member = UserFactory::new()->create();

        $this->organization->addUser($admin, OrganizationRole::ADMINISTRATOR);
        $this->organization->addUser($member, OrganizationRole::MEMBER);

        expect($this->organization->administrators()->count())->toBe(1)
            ->and($this->organization->administrators->first()->id)->toBe($admin->id);
    });

    it('filters members', function () {
        $admin = UserFactory::new()->create();
        $member = UserFactory::new()->create();

        $this->organization->addUser($admin, OrganizationRole::ADMINISTRATOR);
        $this->organization->addUser($member, OrganizationRole::MEMBER);

        expect($this->organization->members()->count())->toBe(1)
            ->and($this->organization->members->first()->id)->toBe($member->id);
    });
});

describe('Organization User Management', function () {
    it('can check if user is owner', function () {
        $otherUser = UserFactory::new()->create();

        expect($this->organization->isOwnedBy($this->user))->toBeTrue()
            ->and($this->organization->isOwnedBy($otherUser))->toBeFalse();
    });

    it('can check if user is a member', function () {
        $member = UserFactory::new()->create();
        $nonMember = UserFactory::new()->create();

        $this->organization->addUser($member, OrganizationRole::MEMBER);

        expect($this->organization->hasMember($member))->toBeTrue()
            ->and($this->organization->hasMember($nonMember))->toBeFalse();
    });

    it('can check if user is an active member', function () {
        $activeMember = UserFactory::new()->create();
        $inactiveMember = UserFactory::new()->create();

        $this->organization->addUser($activeMember, OrganizationRole::MEMBER, true);
        $this->organization->addUser($inactiveMember, OrganizationRole::MEMBER, false);

        expect($this->organization->hasActiveMember($activeMember))->toBeTrue()
            ->and($this->organization->hasActiveMember($inactiveMember))->toBeFalse();
    });

    it('can add a user with specific role', function () {
        $user = UserFactory::new()->create();

        $this->organization->addUser($user, OrganizationRole::ADMINISTRATOR, true);

        expect($this->organization->hasMember($user))->toBeTrue()
            ->and($this->organization->getUserRole($user))->toBe(OrganizationRole::ADMINISTRATOR);
    });

    it('can remove a user', function () {
        $user = UserFactory::new()->create();
        $this->organization->addUser($user, OrganizationRole::MEMBER);

        expect($this->organization->hasMember($user))->toBeTrue();

        $this->organization->removeUser($user);

        expect($this->organization->hasMember($user))->toBeFalse();
    });

    it('can update user role', function () {
        $user = UserFactory::new()->create();
        $this->organization->addUser($user, OrganizationRole::MEMBER);

        expect($this->organization->getUserRole($user))->toBe(OrganizationRole::MEMBER);

        $this->organization->updateUserRole($user, OrganizationRole::ADMINISTRATOR);

        expect($this->organization->getUserRole($user))->toBe(OrganizationRole::ADMINISTRATOR);
    });

    it('can set user active status', function () {
        $user = UserFactory::new()->create();
        $this->organization->addUser($user, OrganizationRole::MEMBER, true);

        expect($this->organization->hasActiveMember($user))->toBeTrue();

        $this->organization->setUserActiveStatus($user, false);

        expect($this->organization->hasActiveMember($user))->toBeFalse();
    });

    it('can check if user has specific role', function () {
        $admin = UserFactory::new()->create();
        $member = UserFactory::new()->create();

        $this->organization->addUser($admin, OrganizationRole::ADMINISTRATOR);
        $this->organization->addUser($member, OrganizationRole::MEMBER);

        expect($this->organization->userHasRole($admin, OrganizationRole::ADMINISTRATOR))->toBeTrue()
            ->and($this->organization->userHasRole($admin, OrganizationRole::MEMBER))->toBeFalse()
            ->and($this->organization->userHasRole($member, OrganizationRole::MEMBER))->toBeTrue()
            ->and($this->organization->userHasRole($member, OrganizationRole::ADMINISTRATOR))->toBeFalse();
    });

    it('returns null for non-member user role', function () {
        $nonMember = UserFactory::new()->create();

        expect($this->organization->getUserRole($nonMember))->toBeNull();
    });
});

describe('Organization Settings Management', function () {
    it('can get setting value', function () {
        $this->organization->update([
            'settings' => ['timezone' => 'UTC', 'notifications' => ['email' => true]],
        ]);

        expect($this->organization->getSetting('timezone'))->toBe('UTC')
            ->and($this->organization->getSetting('notifications.email'))->toBeTrue()
            ->and($this->organization->getSetting('non_existent', 'default'))->toBe('default');
    });

    it('can set setting value', function () {
        $this->organization->setSetting('new_setting', 'new_value');
        $this->organization->save();

        expect($this->organization->fresh()->getSetting('new_setting'))->toBe('new_value');
    });

    it('can set nested setting value', function () {
        $this->organization->setSetting('notifications.email', false);
        $this->organization->save();

        expect($this->organization->fresh()->getSetting('notifications.email'))->toBeFalse();
    });

    it('preserves existing settings when adding new ones', function () {
        $this->organization->update(['settings' => ['existing' => 'value']]);
        $this->organization->setSetting('new', 'setting');
        $this->organization->save();

        $settings = $this->organization->fresh()->settings;
        expect($settings['existing'])->toBe('value')
            ->and($settings['new'])->toBe('setting');
    });
});

describe('Organization Factory', function () {
    it('can create organization with custom settings', function () {
        $customSettings = ['custom' => 'value'];
        $organization = OrganizationFactory::new()->withSettings($customSettings)->create();

        expect($organization->getSetting('custom'))->toBe('value');
    });

    it('can create organization without settings', function () {
        $organization = OrganizationFactory::new()->withoutSettings()->create();

        expect($organization->settings)->toBeNull();
    });

    it('can create organization with specific owner', function () {
        $owner = UserFactory::new()->create();
        $organization = OrganizationFactory::new()->ownedBy($owner)->create();

        expect($organization->owner_id)->toBe($owner->id);
    });
});
