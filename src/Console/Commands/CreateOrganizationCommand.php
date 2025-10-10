<?php

namespace CleaniqueCoders\LaravelOrganization\Console\Commands;

use CleaniqueCoders\LaravelOrganization\Actions\CreateNewOrganization;
use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;

class CreateOrganizationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-org {email} {--organization_name= : Optional name for the organization} {--description= : Optional description for the organization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create organization for given user.';

    /**
     * Execute the console command.
     */
    public function handle(CreateNewOrganization $action): int
    {
        try {
            // Find the user by email or fail if not found
            $user = User::where('email', $this->argument('email'))->firstOrFail();
            $organizationName = $this->option('organization_name');
            $description = $this->option('description');

            // If organization_name option is not provided, create default organization
            // If organization_name option is provided, create additional organization
            $default = is_null($organizationName);

            $organization = $action->handle($user, $default, $organizationName, $description);

            $this->info("Organization '{$organization->name}' created successfully for user {$user->email}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
