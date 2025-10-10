<?php

namespace CleaniqueCoders\LaravelOrganization\Console\Commands;

use CleaniqueCoders\LaravelOrganization\Actions\UpdateOrganization;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;
use Illuminate\Validation\ValidationException;

class UpdateOrganizationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-org {email} {organization : Organization ID or slug} {--name= : New name for the organization} {--description= : New description for the organization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update an organization for the given user.';

    /**
     * Execute the console command.
     */
    public function handle(UpdateOrganization $action): int
    {
        try {
            // Find the user by email or fail if not found
            $user = User::where('email', $this->argument('email'))->firstOrFail();
            $organizationIdentifier = $this->argument('organization');

            // Find organization by ID or slug
            $organization = Organization::where('id', $organizationIdentifier)
                ->orWhere('slug', $organizationIdentifier)
                ->firstOrFail();

            // Build update data from options
            $data = [];
            if ($this->option('name')) {
                $data['name'] = $this->option('name');
            }
            if ($this->option('description') !== null) {
                $data['description'] = $this->option('description');
            }

            if (empty($data)) {
                $this->error('Please provide at least one field to update (--name or --description).');

                return self::FAILURE;
            }

            // Perform update
            $updatedOrganization = $action->handle($organization, $user, $data);

            $this->info("Organization '{$updatedOrganization->name}' updated successfully!");

            return self::SUCCESS;
        } catch (ValidationException $e) {
            $this->error('Validation failed:');
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $error) {
                    $this->error("- {$error}");
                }
            }

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
