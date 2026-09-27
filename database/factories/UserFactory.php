<?php

namespace Database\Factories;

use App\Models\Governorate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('7########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'governorate_id' => Governorate::query()->inRandomOrder()->value('id'),
            'status' => 'active',
            'default_coordinates' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            $this->assignRole($user, 'customer');
        });
    }

    public function customer(): static
    {
        return $this->afterCreating(function (User $user): void {
            $this->assignRole($user, 'customer');
        });
    }

    public function engineer(): static
    {
        return $this->afterCreating(function (User $user): void {
            $this->assignRole($user, 'engineer');
        });
    }

    public function supplier(): static
    {
        return $this->afterCreating(function (User $user): void {
            $this->assignRole($user, 'supplier');
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function assignRole(User $user, string $role): void
    {
        Role::findOrCreate($role, 'web');
        $user->syncRoles([$role]);
    }
}
