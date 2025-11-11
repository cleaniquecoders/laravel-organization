<?php

use CleaniqueCoders\LaravelOrganization\Enums\OrganizationRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $organizationsTable = config('organization.tables.organizations', 'organizations');
        $organizationUsersTable = config('organization.tables.organization_users', 'organization_users');
        $usersTable = (new (config('organization.user-model')))->getTable();

        Schema::create($organizationsTable, function (Blueprint $table) use ($usersTable) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(config('organization.user-model'), 'owner_id')->constrained($usersTable)->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create($organizationUsersTable, function (Blueprint $table) use ($organizationsTable, $usersTable) {
            $table->id();
            $table->foreignIdFor(config('organization.organization-model'))->constrained($organizationsTable)->cascadeOnDelete();
            $table->foreignIdFor(config('organization.user-model'))->constrained($usersTable)->cascadeOnDelete();
            $table->enum('role', array_column(OrganizationRole::cases(), 'value'))->default(OrganizationRole::MEMBER->value);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure unique combination of organization and user
            $table->unique(['organization_id', 'user_id']);

            // Add indexes for better query performance
            $table->index(['organization_id', 'role']);
            $table->index(['user_id', 'role']);
        });

        $hasUuid = Schema::hasColumn($usersTable, 'uuid');
        Schema::table($usersTable, function (Blueprint $table) use ($organizationsTable, $hasUuid) {
            $column = $table->foreignIdFor(config('organization.organization-model'))->nullable();
            if ($hasUuid) {
                $column->after('uuid');
            } else {
                $column->after('id');
            }
            $column->constrained($organizationsTable)->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $organizationsTable = config('organization.tables.organizations', 'organizations');
        $organizationUsersTable = config('organization.tables.organization_users', 'organization_users');
        $usersTable = (new (config('organization.user-model')))->getTable();

        Schema::table($usersTable, function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::dropIfExists($organizationUsersTable);

        Schema::dropIfExists($organizationsTable);
    }
};
