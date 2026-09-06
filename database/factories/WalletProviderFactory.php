<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WalletProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name_ar' => $this->faker->randomElement(['الكريمي', 'جيب', 'وون كاش', 'موبايل موني']),
            'name_en' => $this->faker->unique()->randomElement(['Kuraimi', 'Jeeb', 'OneCash', 'MobileMoney']),
            'logo' => 'wallets/default.png',
            'is_active' => true,
        ];
    }
}
