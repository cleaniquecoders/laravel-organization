<?php

namespace CleaniqueCoders\LaravelOrganization;

use Illuminate\Support\Facades\Auth;

/**
 * Laravel Organization utility class.
 *
 * Provides constants and helper methods for organization management.
 */
class LaravelOrganization
{
    /**
     * Session key for storing current organization ID.
     */
    public const SESSION_KEY = 'organization_current_id';

    /**
     * Get the current organization ID from session or authenticated user.
     */
    public static function getCurrentOrganizationId(): ?int
    {
        // Check session first (for active switching without DB writes)
        if (session()->has(self::SESSION_KEY)) {
            return session(self::SESSION_KEY);
        }

        if (! Auth::check()) {
            return null;
        }

        $user = Auth::user();

        // Use getAttribute() to get the value safely
        // @phpstan-ignore-next-line - Authenticatable may have getAttribute via Model
        if ($user instanceof \Illuminate\Database\Eloquent\Model) {
            return $user->getAttribute('organization_id');
        }

        return $user->organization_id ?? null;
    }

    /**
     * Set the current organization ID in session.
     */
    public static function setCurrentOrganizationId(?int $organizationId): void
    {
        if ($organizationId === null) {
            session()->forget(self::SESSION_KEY);
        } else {
            session([self::SESSION_KEY => $organizationId]);
        }
    }

    /**
     * Clear the organization session.
     */
    public static function clearSession(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
