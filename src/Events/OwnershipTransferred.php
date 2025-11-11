<?php

namespace CleaniqueCoders\LaravelOrganization\Events;

use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OwnershipTransferred
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Organization $organization,
        public User $previousOwner,
        public User $newOwner,
    ) {}
}
