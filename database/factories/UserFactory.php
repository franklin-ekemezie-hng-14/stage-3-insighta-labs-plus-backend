<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id'            => $this->faker->uuid(),
            'github_id'     => $this->faker->uuid(),
            'username'      => $this->faker->userName(),
            'email'         => $this->faker->unique()->safeEmail(),
            'avatar_url'    => $this->faker->imageUrl(),
            'role'          => $this->faker->randomElement(Role::cases()),
            'is_active'     => true,
            'last_login_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
