<?php

namespace CleaniqueCoders\LaravelOrganization\Livewire;

use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrganizationSwitcher extends Component
{
    public ?Authenticatable $user = null;

    public ?Organization $currentOrganization = null;

    public $organizations = [];

    public bool $showDropdown = false;

    public ?string $errorMessage = null;

    protected $listeners = [
        'organization-created' => 'refreshOrganizations',
        'organization-updated' => 'refreshOrganizations',
        'organization-deleted' => 'handleOrganizationDeleted',
    ];

    public function mount($user = null)
    {
        // Use passed user or fallback to Auth::user()
        $this->user = $user ?? Auth::user();

        // Load current organization if user has one set
        if ($this->user) {
            $organizationId = $this->user instanceof Model ? $this->user->getAttribute('organization_id') : null;
            if ($organizationId) {
                $this->currentOrganization = Organization::find($organizationId);
            }
        }

        $this->loadOrganizations();
    }

    public function loadOrganizations()
    {
        if (! $this->user) {
            $this->organizations = [];

            return;
        }

        $userId = $this->user instanceof Model ? $this->user->getKey() : null;

        if (! $userId) {
            $this->organizations = [];

            return;
        }

        // Get organizations where user is owner or member
        $ownedOrganizations = Organization::where('owner_id', $userId)->get();

        // Get organizations where user is a member (if the relationship exists)
        $memberOrganizations = collect([]);
        if ($this->user instanceof Model) {
            try {
                // Attempt to access organizations relationship if it exists
                $memberOrganizations = $this->user->relationLoaded('organizations')
                    ? $this->user->getRelation('organizations')
                    : $this->user->organizations()->get();
            } catch (\BadMethodCallException $e) {
                // Relationship doesn't exist, keep empty collection
            }
        }

        $this->organizations = $ownedOrganizations->merge($memberOrganizations)->unique('id')->all();
    }

    public function switchOrganization($organizationId)
    {
        $this->errorMessage = null;

        try {
            $organization = Organization::find($organizationId);

            if (! $organization) {
                $this->errorMessage = __('Organization not found.');

                return;
            }

            // Check if user has access to this organization (cast to User for type safety)
            if ($this->user instanceof \Illuminate\Foundation\Auth\User) {
                if (! $organization->isOwnedBy($this->user) && ! $organization->hasActiveMember($this->user)) {
                    $this->errorMessage = __('You do not have access to this organization.');

                    return;
                }
            }

            // Update user's organization - cast to Model for save/refresh methods
            if ($this->user instanceof Model) {
                $this->user->setAttribute('organization_id', $organization->id);
                $this->user->save();
                $this->user->refresh();

                // Force refresh the authenticated user in session
                Auth::setUser($this->user);
            }

            $this->currentOrganization = $organization;
            $this->showDropdown = false;

            // Emit event for other components to listen to
            $this->dispatch('organization-switched', organizationId: $organization->id);
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to switch organization: '.$e->getMessage();
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    /**
     * Refresh the organizations list when an organization is created or updated.
     */
    public function refreshOrganizations()
    {
        $this->loadOrganizations();

        // Refresh current organization if it was updated
        if ($this->currentOrganization) {
            $this->currentOrganization = Organization::find($this->currentOrganization->id);
        }
    }

    /**
     * Handle organization deletion event.
     *
     * @param  int  $organizationId  The ID of the deleted organization
     */
    public function handleOrganizationDeleted($organizationId)
    {
        // If the deleted organization was the current one, clear it
        if ($this->currentOrganization && $this->currentOrganization->id == $organizationId) {
            $this->currentOrganization = null;

            if ($this->user instanceof Model) {
                $this->user->update(['organization_id' => null]);
            }
        }

        // Refresh the organizations list
        $this->loadOrganizations();
    }

    public function render()
    {
        return view('org::livewire.organization-switcher');
    }
}
