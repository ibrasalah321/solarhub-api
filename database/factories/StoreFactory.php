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
            'user_id'              => User::factory()->state(['user_type' => 'supplier']),
            'company_name'         => fake()->company() . ' للطاقة الشمسية',
            'commercial_registry'  => (string) fake()->numerify('CR-######'),
            'commercial_file_path' => 'documents/cr_sample.pdf',
            'tax_number'           => (string) fake()->numerify('TAX-######'),
            'bio'                  => fake()->paragraph(),
            'company_logo_path'    => 'logos/store_' . fake()->numberBetween(1, 5) . '.png',
            'approval_status'      => 'approved',
            'store_type'           => fake()->randomElement(['wholesaler', 'retailer', 'authorized_agent']),
            'address_details'      => fake()->address(),
            'approved_at'          => now(),
        ];
    }
}
