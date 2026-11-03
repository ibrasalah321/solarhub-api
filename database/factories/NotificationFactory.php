<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'system',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'sender_id' => null,
            'sender_type' => 'system',
            'template_code' => null,
            'event_id' => (string) Str::uuid(),
            'event_key' => fake()->uuid(),
            'dedupe_key' => hash('sha256', fake()->uuid()),
            'data' => [
                'title' => fake()->sentence(4),
                'body' => fake()->sentence(),
                'action_url' => null,
            ],
            'read_at' => fake()->optional(0.6)->dateTimeBetween('-1 month'),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn () => ['read_at' => null]);
    }

    public function legacyUnknown(): static
    {
        return $this->state(fn () => [
            'sender_id' => null,
            'sender_type' => 'legacy_unknown',
            'event_id' => null,
            'event_key' => null,
            'dedupe_key' => null,
        ]);
    }
}
