<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\WalletProvider;

class UserWalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'wallet_provider_id' => WalletProvider::inRandomOrder()->first()?->id ?? 1,
            'account_number' => $this->faker->numerify('77#######'),
            'account_name' => $this->faker->name(),
            'is_default' => $this->faker->boolean(50),
        ];
    }
}
