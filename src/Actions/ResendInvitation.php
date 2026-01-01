<?php

namespace CleaniqueCoders\LaravelOrganization\Actions;

use CleaniqueCoders\LaravelOrganization\Events\InvitationSent;
use CleaniqueCoders\LaravelOrganization\Models\Invitation;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ResendInvitation
{
    /**
     * The default expiration time for invitations in days.
     */
    private const DEFAULT_EXPIRATION_DAYS = 7;

    /**
     * Resend an invitation, generating a new token and expiration.
     *
     * @param  User|null  $user  The user performing the action (defaults to authenticated user)
     *
     * @throws InvalidArgumentException
     */
    public function handle(
        Invitation $invitation,
        ?User $user = null,
        int $expirationDays = self::DEFAULT_EXPIRATION_DAYS
    ): Invitation {
        // Get the user performing the action
        $user = $user ?? Auth::user();

        if (! $user) {
            throw new InvalidArgumentException('You must be authenticated to resend invitations.');
        }

        // Validate authorization - user must be owner or active member of the organization
        $organization = $invitation->organization;
        if (! $organization->isOwnedBy($user) && ! $organization->hasActiveMember($user)) {
            throw new InvalidArgumentException('You do not have permission to resend invitations for this organization.');
        }

        // Only allow resending pending invitations
        if (! $invitation->isPending()) {
            throw new InvalidArgumentException('Cannot resend an invitation that has been '.($invitation->isAccepted() ? 'accepted' : 'declined').'.');
        }

        // Check if invitation is expired (still allow resending expired invitations)
        // This is intentional - resending refreshes the expiration

        // Update the invitation with a new token and expiration
        $invitation->update([
            'token' => Str::random(40),
            'expires_at' => now()->addDays($expirationDays),
        ]);

        // Dispatch the invitation sent event (for email resend)
        InvitationSent::dispatch($invitation);

        return $invitation;
    }
}
