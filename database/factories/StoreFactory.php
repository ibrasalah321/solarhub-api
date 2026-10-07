<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company() . ' للطاقة الشمسية',
            'commercial_registry' => fake()->numerify('CR-######'),
            'commercial_file_path' => 'documents/cr_sample.pdf',
            'tax_number' => fake()->numerify('TAX-######'),
            'bio' => fake()->paragraph(),
            'company_logo_path' => 'logos/store_'
                . fake()->numberBetween(1, 5) . '.png',
            'whatsapp_number' => fake()->numerify('77#######'),

            'approval_status' => 'rejected',
            'rejection_reason' => 'رفض تجريبي: السجل التجاري غير واضح، يرجى رفع نسخة واضحة.',
            'approved_at' => null,

            'store_type' => fake()->randomElement([
                'wholesaler',
                'retailer',
                'authorized_agent',
            ]),
            'address_details' => fake()->address(),
            'location_coordinates' => null,
        ];
    }
}
