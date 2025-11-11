<?php

use CleaniqueCoders\LaravelOrganization\Database\Factories\OrganizationFactory;
use CleaniqueCoders\LaravelOrganization\Database\Factories\UserFactory;
use CleaniqueCoders\LaravelOrganization\Enums\OrganizationRole;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use CleaniqueCoders\LaravelOrganization\Policies\OrganizationPolicy;

beforeEach(function () {
    $this->policy = new OrganizationPolicy;
    $this->owner = UserFactory::new()->create();
    $this->admin = UserFactory::new()->create();
    $this->member = UserFactory::new()->create();
    $this->stranger = UserFactory::new()->create();

    $this->organization = OrganizationFactory::new()->ownedBy($this->owner)->create();

    // Attach users to organization with roles
    $this->organization->users()->attach($this->admin, ['role' => OrganizationRole::ADMINISTRATOR]);
    $this->organization->users()->attach($this->member, ['role' => OrganizationRole::MEMBER]);
});

describe('OrganizationPolicy ViewAny', function () {
    it('allows anyone to view any organizations', function () {
        expect($this->policy->viewAny(null))->toBeTrue()
            ->and($this->policy->viewAny($this->owner))->toBeTrue()
            ->and($this->policy->viewAny($this->stranger))->toBeTrue();
    });
});

describe('OrganizationPolicy View', function () {
    it('denies unauthenticated users from viewing', function () {
        expect($this->policy->view(null, $this->organization))->toBeFalse();
    });

    it('allows owner to view organization', function () {
        expect($this->policy->view($this->owner, $this->organization))->toBeTrue();
    });

    it('allows members to view organization', function () {
        expect($this->policy->view($this->member, $this->organization))->toBeTrue();
    });

    it('allows administrators to view organization', function () {
        expect($this->policy->view($this->admin, $this->organization))->toBeTrue();
    });

    it('denies strangers from viewing organization', function () {
        expect($this->policy->view($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy Create', function () {
    it('denies unauthenticated users from creating', function () {
        expect($this->policy->create(null))->toBeFalse();
    });

    it('allows authenticated users to create', function () {
        expect($this->policy->create($this->owner))->toBeTrue()
            ->and($this->policy->create($this->member))->toBeTrue()
            ->and($this->policy->create($this->stranger))->toBeTrue();
    });
});

describe('OrganizationPolicy Update', function () {
    it('denies unauthenticated users from updating', function () {
        expect($this->policy->update(null, $this->organization))->toBeFalse();
    });

    it('allows owner to update', function () {
        expect($this->policy->update($this->owner, $this->organization))->toBeTrue();
    });

    it('allows administrators to update', function () {
        expect($this->policy->update($this->admin, $this->organization))->toBeTrue();
    });

    it('denies members from updating', function () {
        expect($this->policy->update($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from updating', function () {
        expect($this->policy->update($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy Delete', function () {
    it('denies unauthenticated users from deleting', function () {
        expect($this->policy->delete(null, $this->organization))->toBeFalse();
    });

    it('allows owner to delete', function () {
        expect($this->policy->delete($this->owner, $this->organization))->toBeTrue();
    });

    it('denies administrators from deleting', function () {
        expect($this->policy->delete($this->admin, $this->organization))->toBeFalse();
    });

    it('denies members from deleting', function () {
        expect($this->policy->delete($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from deleting', function () {
        expect($this->policy->delete($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy Restore', function () {
    it('denies unauthenticated users from restoring', function () {
        expect($this->policy->restore(null, $this->organization))->toBeFalse();
    });

    it('allows owner to restore', function () {
        expect($this->policy->restore($this->owner, $this->organization))->toBeTrue();
    });

    it('denies administrators from restoring', function () {
        expect($this->policy->restore($this->admin, $this->organization))->toBeFalse();
    });

    it('denies members from restoring', function () {
        expect($this->policy->restore($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from restoring', function () {
        expect($this->policy->restore($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy ForceDelete', function () {
    it('denies unauthenticated users from force deleting', function () {
        expect($this->policy->forceDelete(null, $this->organization))->toBeFalse();
    });

    it('allows owner to force delete', function () {
        expect($this->policy->forceDelete($this->owner, $this->organization))->toBeTrue();
    });

    it('denies administrators from force deleting', function () {
        expect($this->policy->forceDelete($this->admin, $this->organization))->toBeFalse();
    });

    it('denies members from force deleting', function () {
        expect($this->policy->forceDelete($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from force deleting', function () {
        expect($this->policy->forceDelete($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy ManageMembers', function () {
    it('denies unauthenticated users from managing members', function () {
        expect($this->policy->manageMembers(null, $this->organization))->toBeFalse();
    });

    it('allows owner to manage members', function () {
        expect($this->policy->manageMembers($this->owner, $this->organization))->toBeTrue();
    });

    it('allows administrators to manage members', function () {
        expect($this->policy->manageMembers($this->admin, $this->organization))->toBeTrue();
    });

    it('denies members from managing members', function () {
        expect($this->policy->manageMembers($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from managing members', function () {
        expect($this->policy->manageMembers($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy AddMember', function () {
    it('denies unauthenticated users from adding members', function () {
        expect($this->policy->addMember(null, $this->organization))->toBeFalse();
    });

    it('allows owner to add members', function () {
        expect($this->policy->addMember($this->owner, $this->organization))->toBeTrue();
    });

    it('allows administrators to add members', function () {
        expect($this->policy->addMember($this->admin, $this->organization))->toBeTrue();
    });

    it('denies members from adding members', function () {
        expect($this->policy->addMember($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from adding members', function () {
        expect($this->policy->addMember($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy RemoveMember', function () {
    it('denies unauthenticated users from removing members', function () {
        expect($this->policy->removeMember(null, $this->organization))->toBeFalse();
    });

    it('allows owner to remove members', function () {
        expect($this->policy->removeMember($this->owner, $this->organization))->toBeTrue();
    });

    it('allows administrators to remove members', function () {
        expect($this->policy->removeMember($this->admin, $this->organization))->toBeTrue();
    });

    it('denies members from removing members', function () {
        expect($this->policy->removeMember($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from removing members', function () {
        expect($this->policy->removeMember($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy ChangeMemberRole', function () {
    it('denies unauthenticated users from changing roles', function () {
        expect($this->policy->changeMemberRole(null, $this->organization))->toBeFalse();
    });

    it('allows owner to change member roles', function () {
        expect($this->policy->changeMemberRole($this->owner, $this->organization))->toBeTrue();
    });

    it('allows administrators to change member roles', function () {
        expect($this->policy->changeMemberRole($this->admin, $this->organization))->toBeTrue();
    });

    it('denies members from changing roles', function () {
        expect($this->policy->changeMemberRole($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from changing roles', function () {
        expect($this->policy->changeMemberRole($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy TransferOwnership', function () {
    it('denies unauthenticated users from transferring ownership', function () {
        expect($this->policy->transferOwnership(null, $this->organization))->toBeFalse();
    });

    it('allows owner to transfer ownership', function () {
        expect($this->policy->transferOwnership($this->owner, $this->organization))->toBeTrue();
    });

    it('denies administrators from transferring ownership', function () {
        expect($this->policy->transferOwnership($this->admin, $this->organization))->toBeFalse();
    });

    it('denies members from transferring ownership', function () {
        expect($this->policy->transferOwnership($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from transferring ownership', function () {
        expect($this->policy->transferOwnership($this->stranger, $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy ManageSettings', function () {
    it('denies unauthenticated users from managing settings', function () {
        expect($this->policy->manageSettings(null, $this->organization))->toBeFalse();
    });

    it('allows owner to manage settings', function () {
        expect($this->policy->manageSettings($this->owner, $this->organization))->toBeTrue();
    });

    it('allows administrators to manage settings', function () {
        expect($this->policy->manageSettings($this->admin, $this->organization))->toBeTrue();
    });

    it('denies members from managing settings', function () {
        expect($this->policy->manageSettings($this->member, $this->organization))->toBeFalse();
    });

    it('denies strangers from managing settings', function () {
        expect($this->policy->manageSettings($this->stranger, $this->organization))->toBeFalse();
    });
});
