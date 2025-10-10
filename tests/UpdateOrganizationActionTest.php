<?php

use CleaniqueCoders\LaravelOrganization\Actions\UpdateOrganization;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create(['owner_id' => $this->user->id]);
});

it('can update organization with valid data', function () {
    $result = UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'Updated Organization Name',
            'description' => 'Updated description',
        ]
    );

    expect($result->name)->toBe('Updated Organization Name')
        ->and($result->description)->toBe('Updated description');
});

it('validates required name field', function () {
    UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => '',
            'description' => 'Some description',
        ]
    );
})->throws(ValidationException::class);

it('validates minimum name length', function () {
    UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'A',
            'description' => 'Some description',
        ]
    );
})->throws(ValidationException::class);

it('validates maximum name length', function () {
    UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => str_repeat('A', 256),
            'description' => 'Some description',
        ]
    );
})->throws(ValidationException::class);

it('validates unique organization name', function () {
    Organization::factory()->create([
        'owner_id' => $this->user->id,
        'name' => 'Existing Organization',
    ]);

    UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'Existing Organization',
            'description' => 'Some description',
        ]
    );
})->throws(ValidationException::class);

it('allows updating to same name', function () {
    $originalName = $this->organization->name;

    $result = UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => $originalName,
            'description' => 'Updated description',
        ]
    );

    expect($result->name)->toBe($originalName)
        ->and($result->description)->toBe('Updated description');
});

it('validates maximum description length', function () {
    UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'Valid Name',
            'description' => str_repeat('A', 1001),
        ]
    );
})->throws(ValidationException::class);

it('allows null description', function () {
    $result = UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'Updated Name',
            'description' => null,
        ]
    );

    expect($result->name)->toBe('Updated Name')
        ->and($result->description)->toBeNull();
});

it('prevents non-owner from updating organization', function () {
    $anotherUser = User::factory()->create();

    UpdateOrganization::run(
        $this->organization,
        $anotherUser,
        [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]
    );
})->throws(Exception::class, 'You do not have permission to update this organization.');

it('allows administrator to update organization', function () {
    $admin = User::factory()->create();

    // Attach admin as administrator
    $this->organization->users()->attach($admin->id, [
        'role' => 'administrator',
        'is_active' => true,
    ]);

    $result = UpdateOrganization::run(
        $this->organization,
        $admin,
        [
            'name' => 'Updated by Admin',
            'description' => 'Admin update',
        ]
    );

    expect($result->name)->toBe('Updated by Admin')
        ->and($result->description)->toBe('Admin update');
});

it('returns fresh organization instance after update', function () {
    $result = UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'Fresh Name',
            'description' => 'Fresh description',
        ]
    );

    expect($result->wasRecentlyCreated)->toBeFalse()
        ->and($result->name)->toBe('Fresh Name')
        ->and($result->exists)->toBeTrue();
});

it('provides validation rules method', function () {
    $rules = UpdateOrganization::rules($this->organization);

    expect($rules)->toBeArray()
        ->and($rules)->toHaveKey('name')
        ->and($rules)->toHaveKey('description');
});

it('provides validation messages method', function () {
    $messages = UpdateOrganization::messages();

    expect($messages)->toBeArray()
        ->and($messages)->toHaveKey('name.required')
        ->and($messages)->toHaveKey('name.unique');
});

it('updates slug when organization name changes', function () {
    $originalSlug = $this->organization->slug;

    $result = UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'Completely New Name',
            'description' => 'Updated description',
        ]
    );

    expect($result->slug)->not->toBe($originalSlug)
        ->and($result->slug)->toStartWith('completely-new-name-')
        ->and(strlen($result->slug))->toBe(26); // 'completely-new-name' (19) + '-' (1) + 6 random chars
});

it('keeps slug unchanged when name does not change', function () {
    $originalSlug = $this->organization->slug;
    $originalName = $this->organization->name;

    $result = UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => $originalName,
            'description' => 'Updated description only',
        ]
    );

    expect($result->slug)->toBe($originalSlug)
        ->and($result->description)->toBe('Updated description only');
});

it('generates new slug with random suffix on name change', function () {
    // Update name twice and verify slugs are different (due to random suffix)
    $result1 = UpdateOrganization::run(
        $this->organization,
        $this->user,
        [
            'name' => 'Test Organization',
            'description' => 'First update',
        ]
    );

    $slug1 = $result1->slug;

    $result2 = UpdateOrganization::run(
        $result1,
        $this->user,
        [
            'name' => 'Another Organization',
            'description' => 'Second update',
        ]
    );

    $slug2 = $result2->slug;

    expect($slug1)->toStartWith('test-organization-')
        ->and($slug2)->toStartWith('another-organization-')
        ->and($slug1)->not->toBe($slug2);
});
