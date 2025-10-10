<?php

use CleaniqueCoders\LaravelOrganization\Database\Factories\OrganizationFactory;
use CleaniqueCoders\LaravelOrganization\Database\Factories\UserFactory;
use CleaniqueCoders\LaravelOrganization\Models\Organization;
use CleaniqueCoders\LaravelOrganization\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

// Create a test model that uses the organization scope
class TestScopedModel extends Model
{
    protected $table = 'test_scoped_models';

    protected $fillable = ['name', 'organization_id'];

    protected static function booted()
    {
        static::addGlobalScope(new OrganizationScope);
    }
}

beforeEach(function () {
    // Create test table for scoped model
    Schema::create('test_scoped_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->unsignedBigInteger('organization_id');
        $table->timestamps();
    });

    $this->user1 = UserFactory::new()->create();
    $this->user2 = UserFactory::new()->create();

    $this->org1 = OrganizationFactory::new()->ownedBy($this->user1)->create();
    $this->org2 = OrganizationFactory::new()->ownedBy($this->user2)->create();

    // Assign users to their organizations
    $this->user1->organization_id = $this->org1->id;
    $this->user2->organization_id = $this->org2->id;

    // Create test records
    TestScopedModel::withoutGlobalScopes()->create(['name' => 'Record 1', 'organization_id' => $this->org1->id]);
    TestScopedModel::withoutGlobalScopes()->create(['name' => 'Record 2', 'organization_id' => $this->org1->id]);
    TestScopedModel::withoutGlobalScopes()->create(['name' => 'Record 3', 'organization_id' => $this->org2->id]);
});

afterEach(function () {
    Schema::dropIfExists('test_scoped_models');
    Auth::logout();
});

describe('OrganizationScope Basic Functionality', function () {
    it('implements Scope interface', function () {
        $scope = new OrganizationScope;

        expect($scope)->toBeInstanceOf(Illuminate\Database\Eloquent\Scope::class);
    });

    it('filters records by authenticated user organization', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::all();

        expect($records)->toHaveCount(2)
            ->and($records->pluck('organization_id')->unique()->toArray())->toBe([$this->org1->id]);
    });

    it('filters records for different authenticated users', function () {
        Auth::login($this->user2);

        $records = TestScopedModel::all();

        expect($records)->toHaveCount(1)
            ->and($records->first()->organization_id)->toBe($this->org2->id);
    });

    it('returns all records when no user is authenticated', function () {
        Auth::logout();

        $records = TestScopedModel::all();

        expect($records)->toHaveCount(3);
    });

    it('returns all records when user has no organization', function () {
        $userWithoutOrg = UserFactory::new()->create(['organization_id' => null]);
        Auth::login($userWithoutOrg);

        $records = TestScopedModel::all();

        expect($records)->toHaveCount(3);
    });
});

describe('OrganizationScope Query Builder Extensions', function () {
    it('can remove scope with withoutOrganizationScope macro', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::withoutOrganizationScope()->get();

        expect($records)->toHaveCount(3);
    });

    it('can filter by specific organization with withOrganization macro', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::withOrganization($this->org2->id)->get();

        expect($records)->toHaveCount(1)
            ->and($records->first()->organization_id)->toBe($this->org2->id);
    });

    it('can get all organizations records with allOrganizations macro', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::allOrganizations()->get();

        expect($records)->toHaveCount(3);
    });

    it('withOrganization macro includes table prefix in column reference', function () {
        Auth::login($this->user1);

        $query = TestScopedModel::withOrganization($this->org2->id);
        $sql = $query->toSql();

        expect($sql)->toContain('test_scoped_models.organization_id');
    });
});

describe('OrganizationScope with Complex Queries', function () {
    it('works with additional where clauses', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::where('name', 'Record 1')->get();

        expect($records)->toHaveCount(1)
            ->and($records->first()->name)->toBe('Record 1')
            ->and($records->first()->organization_id)->toBe($this->org1->id);
    });

    it('works with ordering', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::orderBy('name', 'desc')->get();

        expect($records)->toHaveCount(2)
            ->and($records->first()->name)->toBe('Record 2')
            ->and($records->last()->name)->toBe('Record 1');
    });

    it('works with limits', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::limit(1)->get();

        expect($records)->toHaveCount(1)
            ->and($records->first()->organization_id)->toBe($this->org1->id);
    });

    it('works with joins', function () {
        Auth::login($this->user1);

        $records = TestScopedModel::join('organizations', 'test_scoped_models.organization_id', '=', 'organizations.id')
            ->select('test_scoped_models.*', 'organizations.name as org_name')
            ->get();

        expect($records)->toHaveCount(2)
            ->and($records->first()->org_name)->toBe($this->org1->name);
    });
});

describe('OrganizationScope Edge Cases', function () {
    it('handles user switching organizations', function () {
        Auth::login($this->user1);

        // Initially user1 sees 2 records
        expect(TestScopedModel::count())->toBe(2);

        // Switch user1 to org2
        $this->user1->organization_id = $this->org2->id;

        // Clear any cached auth user data
        Auth::setUser($this->user1->fresh());

        // Now user1 should see org2's records
        expect(TestScopedModel::count())->toBe(1);
    });

    it('handles creating records with scope active', function () {
        Auth::login($this->user1);

        $record = TestScopedModel::create(['name' => 'New Record']);

        // The organization_id should be auto-set by the scope or model logic
        // This test verifies the scope doesn't interfere with creation
        expect($record)->toBeInstanceOf(TestScopedModel::class)
            ->and($record->name)->toBe('New Record');
    });

    it('scope does not affect update operations', function () {
        Auth::login($this->user1);

        $record = TestScopedModel::first();
        $record->update(['name' => 'Updated Record']);

        expect($record->fresh()->name)->toBe('Updated Record');
    });

    it('scope does not affect delete operations', function () {
        Auth::login($this->user1);

        $initialCount = TestScopedModel::count();
        TestScopedModel::first()->delete();

        expect(TestScopedModel::count())->toBe($initialCount - 1);
    });
});
