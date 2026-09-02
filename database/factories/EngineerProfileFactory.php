<?php

namespace Database\Factories;

use App\Models\EngineerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<EngineerProfile>
 */
class EngineerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->engineer(),
            'license_number' => fake()->unique()->numerify('ENG-#####'),
            'years_of_experience' => fake()->numberBetween(2,20),
            'bio' => fake()->paragraph(3),
            'profile_photo' => fake()->imageUrl(300, 300, 'people'),
            'rejection_reason' => null,
            'approval_status' => 'approved',
            'approved_at'=> now(),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
    /**
     * حالة مهندس بانتظار الموافقة (Pending)
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
            'approved_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * حالة مهندس مرفوض مع ذكر السبب (Rejected)
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'rejected',
            'approved_at' => null,
            'rejection_reason' => 'الشهادات والمؤهلات المرفقة غير واضحة أو غير مكتملة.',
        ]);
    }
}
