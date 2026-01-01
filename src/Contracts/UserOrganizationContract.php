<?php

namespace CleaniqueCoders\LaravelOrganization\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User Organization Contract
 *
 * Defines methods that users must implement to interact with organizations.
 * This contract follows the Interface Segregation Principle by focusing
 * on user-side organization functionality.
 */
interface UserOrganizationContract
{
    /**
     * Get the user's current organization ID.
     */
    public function getOrganizationId(): ?int;

    /**
     * Set the user's current organization ID (session-based).
     */
    public function setOrganizationId(?int $organizationId): void;

    /**
     * Get the user's default organization ID from database.
     */
    public function getDefaultOrganizationId(): ?int;

    /**
     * Set the user's default organization ID (persisted to database).
     */
    public function setDefaultOrganizationId(?int $organizationId): void;

    /**
     * Sync organization from default (load DB value into session).
     */
    public function syncOrganizationFromDefault(): void;

    /**
     * Check if user belongs to a specific organization.
     */
    public function belongsToOrganization(int $organizationId): bool;

    /**
     * Get all organizations the user is a member of.
     */
    public function organizations(): BelongsToMany;

    /**
     * Get organizations the user owns.
     */
    public function ownedOrganizations(): HasMany;
}
