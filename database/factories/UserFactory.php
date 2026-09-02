<?php

namespace Database\Factories;


use App\Models\Governorate;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
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

        $lng = fake()->longitude(42.5, 48.5);
        $lat = fake()->latitude(13.0, 16.5);
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'governorate_id' => Governorate::inRandomOrder()->value('id') ?? DB::table('governorates')->value('id') ?? 1,
            'user_type' => fake()->randomElement(['customer', 'engineer', 'supplier']),
            'address' => fake()->streetAddress(),
            'status' => 'approved', 
            'default_coordinates' => DB::raw("ST_GeographyFromText('SRID=4326;POINT({$lng} {$lat})')"),

            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }

    /**
     * حالة خاصة: توليد مستخدم عميل فقط (Customer)
     */
    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'customer',
            'status' => 'approved',
        ]);
    }

    /**
     * حالة خاصة: توليد مستخدم مهندس (Engineer)
     */
    public function engineer(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'engineer',
            'status' => fake()->randomElement(['approved', 'approved', 'pending']), // أغلبهم معتمدون
        ]);
    }

    /**
     * حالة خاصة: توليد مستخدم مورد / متجر (Supplier)
     */
    public function supplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'supplier',
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
