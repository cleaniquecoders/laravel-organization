<?php

namespace CleaniqueCoders\LaravelOrganization\Policies;

use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Foundation\Auth\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any organizations.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the organization.
     */
    public function view(?User $user, Organization $organization): bool
    {
        // Allow unauthenticated users to view (can be overridden by app)
        if ($user === null) {
            return false;
        }

        // Owner can always view
        if ($organization->owner_id === $user->id) {
            return true;
        }

        // Organization members can view
        return $organization->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create organizations.
     */
    public function create(?User $user): bool
    {
        // Only authenticated users can create organizations
        return $user !== null;
    }

    /**
     * Determine whether the user can update the organization.
     */
    public function update(?User $user, Organization $organization): bool
    {
        // Only owner and administrators can update
        if ($user === null) {
            return false;
        }

        // Owner can always update
        if ($organization->owner_id === $user->id) {
            return true;
        }

        // Administrators can update
        return $organization->administrators()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the organization.
     */
    public function delete(?User $user, Organization $organization): bool
    {
        // Only owner can delete
        if ($user === null) {
            return false;
        }

        return $organization->owner_id === $user->id;
    }

    /**
     * Determine whether the user can restore the organization.
     */
    public function restore(?User $user, Organization $organization): bool
    {
        // Only owner can restore
        if ($user === null) {
            return false;
        }

        return $organization->owner_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the organization.
     */
    public function forceDelete(?User $user, Organization $organization): bool
    {
        // Only owner can force delete
        if ($user === null) {
            return false;
        }

        return $organization->owner_id === $user->id;
    }

    /**
     * Determine whether the user can manage members of the organization.
     */
    public function manageMembers(?User $user, Organization $organization): bool
    {
        // Only owner and administrators can manage members
        if ($user === null) {
            return false;
        }

        // Owner can always manage members
        if ($organization->owner_id === $user->id) {
            return true;
        }

        // Administrators can manage members
        return $organization->administrators()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can add members to the organization.
     */
    public function addMember(?User $user, Organization $organization): bool
    {
        return $this->manageMembers($user, $organization);
    }

    /**
     * Determine whether the user can remove members from the organization.
     */
    public function removeMember(?User $user, Organization $organization): bool
    {
        return $this->manageMembers($user, $organization);
    }

    /**
     * Determine whether the user can change member roles in the organization.
     */
    public function changeMemberRole(?User $user, Organization $organization): bool
    {
        return $this->manageMembers($user, $organization);
    }

    /**
     * Determine whether the user can transfer ownership of the organization.
     */
    public function transferOwnership(?User $user, Organization $organization): bool
    {
        // Only current owner can transfer ownership
        if ($user === null) {
            return false;
        }

        return $organization->owner_id === $user->id;
    }

    /**
     * Determine whether the user can manage organization settings.
     */
    public function manageSettings(?User $user, Organization $organization): bool
    {
        // Only owner and administrators can manage settings
        if ($user === null) {
            return false;
        }

        // Owner can always manage settings
        if ($organization->owner_id === $user->id) {
            return true;
        }

        // Administrators can manage settings
        return $organization->administrators()->where('users.id', $user->id)->exists();
    }
}
