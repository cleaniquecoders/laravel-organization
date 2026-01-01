<?php

namespace CleaniqueCoders\LaravelOrganization\Actions;

use CleaniqueCoders\LaravelOrganization\Events\InvitationAccepted;
use CleaniqueCoders\LaravelOrganization\Models\Invitation;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AcceptInvitation
{
    /**
     * Accept an invitation and add the user to the organization.
     *
     * @throws InvalidArgumentException
     */
    public function handle(Invitation $invitation, User $user): Organization
    {
        // Validate invitation status
        if (! $invitation->isPending()) {
            throw new InvalidArgumentException('This invitation has already been '.($invitation->isAccepted() ? 'accepted' : 'declined').'.');
        }

        // Validate invitation expiration
        if ($invitation->isExpired()) {
            throw new InvalidArgumentException('This invitation has expired.');
        }

        // Validate email matches (with null check)
        $userEmail = $user->email ?? $user->getAttribute('email');
        if ($userEmail === null || strtolower($invitation->email) !== strtolower($userEmail)) {
            throw new InvalidArgumentException('The email address does not match the invitation.');
        }

        // Check if user is already a member
        if ($invitation->organization->users()->where('users.id', $user->id)->exists()) {
            throw new InvalidArgumentException('This user is already a member of the organization.');
        }

        // Use transaction to ensure atomic operation
        DB::transaction(function () use ($invitation, $user) {
            // Accept the invitation
            $invitation->accept($user);

            // Add user to organization with the invitation role
            $invitation->organization->addUser($user, $invitation->getRoleEnum());
        });

        // Dispatch the invitation accepted event (after successful transaction)
        InvitationAccepted::dispatch($invitation);

        return $invitation->organization;
    }
}
