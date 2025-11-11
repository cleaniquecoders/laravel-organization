<?php

use CleaniqueCoders\LaravelOrganization\Database\Factories\OrganizationFactory;
use CleaniqueCoders\LaravelOrganization\Database\Factories\UserFactory;
use CleaniqueCoders\LaravelOrganization\Enums\OrganizationRole;
use CleaniqueCoders\LaravelOrganization\Events\MemberAdded;
use CleaniqueCoders\LaravelOrganization\Events\MemberRemoved;
use CleaniqueCoders\LaravelOrganization\Events\MemberRoleChanged;
use CleaniqueCoders\LaravelOrganization\Events\OrganizationCreated;
use CleaniqueCoders\LaravelOrganization\Events\OrganizationDeleted;
use CleaniqueCoders\LaravelOrganization\Events\OrganizationUpdated;
use CleaniqueCoders\LaravelOrganization\Events\OwnershipTransferred;
use Illuminate\Support\Facades\Event;

describe('Organization Lifecycle Events', function () {
    beforeEach(function () {
        Event::fake();
    });

    describe('OrganizationCreated Event', function () {
        it('can instantiate the event with organization', function () {
            $owner = UserFactory::new()->create();
            $organization = OrganizationFactory::new()->ownedBy($owner)->create();

            $event = new OrganizationCreated($organization);

            expect($event->organization)->toBe($organization)
                ->and($event->organization->owner_id)->toBe($owner->id);
        });

        it('has dispatchable traits', function () {
            $organization = OrganizationFactory::new()->create();
            $event = new OrganizationCreated($organization);

            expect(method_exists($event, 'dispatch'))->toBeTrue();
        });

        it('can be dispatched and listened to', function () {
            $organization = OrganizationFactory::new()->create();

            Event::listen(OrganizationCreated::class, function (OrganizationCreated $event) {
                expect($event->organization)->toBe($organization);
            });

            OrganizationCreated::dispatch($organization);

            Event::assertDispatched(OrganizationCreated::class);
        });
    });

    describe('OrganizationUpdated Event', function () {
        it('can instantiate with organization and changes', function () {
            $organization = OrganizationFactory::new()->create();
            $changes = ['name' => 'New Name', 'description' => 'New Description'];

            $event = new OrganizationUpdated($organization, $changes);

            expect($event->organization)->toBe($organization)
                ->and($event->changes)->toBe($changes);
        });

        it('can have empty changes array', function () {
            $organization = OrganizationFactory::new()->create();
            $event = new OrganizationUpdated($organization);

            expect($event->changes)->toBe([]);
        });

        it('can be dispatched with changes', function () {
            $organization = OrganizationFactory::new()->create();
            $changes = ['name' => 'Updated Name'];

            OrganizationUpdated::dispatch($organization, $changes);

            Event::assertDispatched(OrganizationUpdated::class, function (OrganizationUpdated $event) use ($organization, $changes) {
                return $event->organization->is($organization) && $event->changes === $changes;
            });
        });
    });

    describe('OrganizationDeleted Event', function () {
        it('can instantiate with organization', function () {
            $organization = OrganizationFactory::new()->create();
            $event = new OrganizationDeleted($organization);

            expect($event->organization)->toBe($organization);
        });

        it('works with soft-deleted organizations', function () {
            $organization = OrganizationFactory::new()->create();
            $organization->delete();

            $event = new OrganizationDeleted($organization);

            expect($event->organization->trashed())->toBeTrue();
        });

        it('can be dispatched and verified', function () {
            $organization = OrganizationFactory::new()->create();

            OrganizationDeleted::dispatch($organization);

            Event::assertDispatched(OrganizationDeleted::class);
        });
    });
});

describe('Member Management Events', function () {
    beforeEach(function () {
        Event::fake();
    });

    describe('MemberAdded Event', function () {
        it('can instantiate with organization, member, and role', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();
            $role = OrganizationRole::MEMBER->value;

            $event = new MemberAdded($organization, $member, $role);

            expect($event->organization)->toBe($organization)
                ->and($event->member)->toBe($member)
                ->and($event->role)->toBe($role);
        });

        it('defaults to member role', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();

            $event = new MemberAdded($organization, $member);

            expect($event->role)->toBe('member');
        });

        it('can use administrator role', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();

            $event = new MemberAdded($organization, $member, OrganizationRole::ADMINISTRATOR->value);

            expect($event->role)->toBe(OrganizationRole::ADMINISTRATOR->value);
        });

        it('can be dispatched and verified', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();

            MemberAdded::dispatch($organization, $member, OrganizationRole::MEMBER->value);

            Event::assertDispatched(MemberAdded::class, function (MemberAdded $event) use ($organization, $member) {
                return $event->organization->is($organization)
                    && $event->member->is($member)
                    && $event->role === OrganizationRole::MEMBER->value;
            });
        });
    });

    describe('MemberRemoved Event', function () {
        it('can instantiate with organization and member', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();

            $event = new MemberRemoved($organization, $member);

            expect($event->organization)->toBe($organization)
                ->and($event->member)->toBe($member);
        });

        it('can be dispatched and verified', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();

            MemberRemoved::dispatch($organization, $member);

            Event::assertDispatched(MemberRemoved::class, function (MemberRemoved $event) use ($organization, $member) {
                return $event->organization->is($organization) && $event->member->is($member);
            });
        });
    });

    describe('MemberRoleChanged Event', function () {
        it('can instantiate with organization, member, and role changes', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();
            $oldRole = OrganizationRole::MEMBER->value;
            $newRole = OrganizationRole::ADMINISTRATOR->value;

            $event = new MemberRoleChanged($organization, $member, $oldRole, $newRole);

            expect($event->organization)->toBe($organization)
                ->and($event->member)->toBe($member)
                ->and($event->oldRole)->toBe($oldRole)
                ->and($event->newRole)->toBe($newRole);
        });

        it('tracks role transition correctly', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();

            $event = new MemberRoleChanged(
                $organization,
                $member,
                OrganizationRole::MEMBER->value,
                OrganizationRole::ADMINISTRATOR->value
            );

            expect($event->oldRole)->toBe(OrganizationRole::MEMBER->value)
                ->and($event->newRole)->toBe(OrganizationRole::ADMINISTRATOR->value);
        });

        it('can be dispatched and verified', function () {
            $organization = OrganizationFactory::new()->create();
            $member = UserFactory::new()->create();

            MemberRoleChanged::dispatch(
                $organization,
                $member,
                OrganizationRole::MEMBER->value,
                OrganizationRole::ADMINISTRATOR->value
            );

            Event::assertDispatched(MemberRoleChanged::class, function (MemberRoleChanged $event) use ($organization, $member) {
                return $event->organization->is($organization)
                    && $event->member->is($member)
                    && $event->oldRole === OrganizationRole::MEMBER->value
                    && $event->newRole === OrganizationRole::ADMINISTRATOR->value;
            });
        });
    });

    describe('OwnershipTransferred Event', function () {
        it('can instantiate with organization and both owners', function () {
            $organization = OrganizationFactory::new()->create();
            $previousOwner = $organization->owner;
            $newOwner = UserFactory::new()->create();

            $event = new OwnershipTransferred($organization, $previousOwner, $newOwner);

            expect($event->organization)->toBe($organization)
                ->and($event->previousOwner)->toBe($previousOwner)
                ->and($event->newOwner)->toBe($newOwner);
        });

        it('correctly tracks owner change', function () {
            $organization = OrganizationFactory::new()->create();
            $previousOwner = $organization->owner;
            $newOwner = UserFactory::new()->create();

            expect($previousOwner->id)->not->toBe($newOwner->id);

            $event = new OwnershipTransferred($organization, $previousOwner, $newOwner);

            expect($event->previousOwner->id)->toBe($previousOwner->id)
                ->and($event->newOwner->id)->toBe($newOwner->id);
        });

        it('can be dispatched and verified', function () {
            $organization = OrganizationFactory::new()->create();
            $previousOwner = $organization->owner;
            $newOwner = UserFactory::new()->create();

            OwnershipTransferred::dispatch($organization, $previousOwner, $newOwner);

            Event::assertDispatched(OwnershipTransferred::class, function (OwnershipTransferred $event) use ($organization, $previousOwner, $newOwner) {
                return $event->organization->is($organization)
                    && $event->previousOwner->is($previousOwner)
                    && $event->newOwner->is($newOwner);
            });
        });
    });
});

describe('Event Serialization', function () {
    it('can serialize OrganizationCreated event', function () {
        $organization = OrganizationFactory::new()->create();
        $event = new OrganizationCreated($organization);

        // The event should be serializable for queuing
        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        expect($unserialized)->toBeInstanceOf(OrganizationCreated::class);
    });

    it('can serialize MemberAdded event', function () {
        $organization = OrganizationFactory::new()->create();
        $member = UserFactory::new()->create();
        $event = new MemberAdded($organization, $member, OrganizationRole::MEMBER->value);

        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        expect($unserialized)->toBeInstanceOf(MemberAdded::class)
            ->and($unserialized->role)->toBe(OrganizationRole::MEMBER->value);
    });

    it('can serialize OwnershipTransferred event', function () {
        $organization = OrganizationFactory::new()->create();
        $previousOwner = $organization->owner;
        $newOwner = UserFactory::new()->create();
        $event = new OwnershipTransferred($organization, $previousOwner, $newOwner);

        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        expect($unserialized)->toBeInstanceOf(OwnershipTransferred::class);
    });
});
