<?php

namespace CleaniqueCoders\LaravelOrganization\Concerns;

use CleaniqueCoders\LaravelOrganization\LaravelOrganization;
use CleaniqueCoders\LaravelOrganization\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that are scoped to organizations.
 *
 * Models using this trait should implement OrganizationScopingContract
 * to ensure they provide all required organization scoping functionality.
 */
trait InteractsWithOrganization
{
    /**
     * Boot the trait.
     */
    protected static function bootInteractsWithOrganization(): void
    {
        // Apply global scope
        static::addGlobalScope(new OrganizationScope);

        // Auto-set organization_id on creating
        static::creating(function ($model) {
            if (! $model->organization_id) {
                $organizationId = static::getCurrentOrganizationId();
                if ($organizationId) {
                    $model->organization_id = $organizationId;
                }
            }
        });
    }

    /**
     * Define the relationship to organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(config('organization.organization-model'));
    }

    /**
     * Scope to include models from all organizations.
     */
    public function scopeAllOrganizations(Builder $query): Builder
    {
        return $query->withoutGlobalScope(OrganizationScope::class);
    }

    /**
     * Scope to filter by specific organization.
     */
    public function scopeForOrganization(Builder $query, ?int $organizationId): Builder
    {
        return $query->withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId);
    }

    /**
     * Get the current organization ID from auth user.
     *
     * Uses hybrid session/database approach:
     * 1. Check session first (for active switching without DB writes)
     * 2. Fall back to database (user's default organization)
     *
     * Safely retrieves the organization_id without triggering relationships
     * or causing recursive query execution.
     */
    public static function getCurrentOrganizationId(): ?int
    {
        return LaravelOrganization::getCurrentOrganizationId();
    }

    /**
     * Get the organization ID for this model.
     */
    public function getOrganizationId(): ?int
    {
        return $this->organization_id;
    }
}
