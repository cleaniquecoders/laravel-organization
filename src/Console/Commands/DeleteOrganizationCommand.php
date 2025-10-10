<?php

namespace CleaniqueCoders\LaravelOrganization\Console\Commands;

use CleaniqueCoders\LaravelOrganization\Actions\DeleteOrganization;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;

class DeleteOrganizationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete-org {email} {organization : Organization ID or slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an organization for the given user.';

    /**
     * Execute the console command.
     */
    public function handle(DeleteOrganization $action): int
    {
        try {
            // Find the user by email or fail if not found
            $user = User::where('email', $this->argument('email'))->firstOrFail();
            $organizationIdentifier = $this->argument('organization');

            // Find organization by ID or slug
            $organization = Organization::where('id', $organizationIdentifier)
                ->orWhere('slug', $organizationIdentifier)
                ->firstOrFail();

            // Check if deletion is possible
            $canDelete = DeleteOrganization::canDelete($organization, $user);

            if (! $canDelete['can_delete']) {
                $this->error("Cannot delete organization: {$canDelete['reason']}");

                return self::FAILURE;
            }

            // Confirm deletion
            if (! $this->confirm("Are you sure you want to permanently delete organization '{$organization->name}'?")) {
                $this->info('Deletion cancelled.');

                return self::SUCCESS;
            }

            // Perform deletion
            $result = $action->handle($organization, $user);

            $this->info($result['message']);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
