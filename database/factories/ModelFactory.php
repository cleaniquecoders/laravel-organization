<?php

namespace CleaniqueCoders\LaravelOrganization\Database\Factories;

use CleaniqueCoders\LaravelOrganization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'uuid' => Str::orderedUuid()->toString(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'owner_id' => UserFactory::new(),
            'settings' => [
                'timezone' => $this->faker->timezone(),
                'locale' => $this->faker->randomElement(['en', 'es', 'fr', 'de']),
                'notifications' => [
                    'email' => $this->faker->boolean(),
                    'sms' => $this->faker->boolean(),
                ],
            ],
        ];
    }

    /**
     * Create an organization with specific settings.
     */
    public function withSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => array_merge($attributes['settings'] ?? [], $settings),
        ]);
    }

    /**
     * Create an organization with a specific owner.
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn () => [
            'owner_id' => $user->id,
        ]);
    }

    /**
     * Create an organization with no settings.
     */
    public function withoutSettings(): static
    {
        return $this->state(fn () => [
            'settings' => null,
        ]);
    }
}
