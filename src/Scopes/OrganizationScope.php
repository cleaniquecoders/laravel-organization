<?php

namespace CleaniqueCoders\LaravelOrganization\Scopes;

use CleaniqueCoders\LaravelOrganization\LaravelOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $organizationId = $this->getCurrentOrganizationId();

        if ($organizationId) {
            $builder->where('organization_id', $organizationId);
        }
    }

    /**
     * Get the current organization ID safely without triggering recursive queries.
     *
     * Uses hybrid session/database approach:
     * 1. Check session first (for active switching without DB writes)
     * 2. Fall back to database (user's default organization)
     */
    protected function getCurrentOrganizationId(): ?int
    {
        return LaravelOrganization::getCurrentOrganizationId();
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutOrganizationScope', function (Builder $builder) {
            return $builder->withoutGlobalScope(self::class);
        });

        $builder->macro('withOrganization', function (Builder $builder, $organizationId) {
            return $builder->withoutGlobalScope(self::class)
                ->where($builder->getModel()->getTable().'.organization_id', $organizationId);
        });

        $builder->macro('allOrganizations', function (Builder $builder) {
            return $builder->withoutGlobalScope(self::class);
        });
    }
}
