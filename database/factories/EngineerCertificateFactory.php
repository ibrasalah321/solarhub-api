<?php

namespace Database\Factories;

use App\Models\EngineerCertificate;
use App\Models\EngineerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EngineerCertificate>
 */
class EngineerCertificateFactory extends Factory
{
    protected $model = EngineerCertificate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // ربط الشهادة بمعرف مهندس موجود أو إنشاء بروفايل مهندس جديد تلقائياً
            'engineer_id' => EngineerProfile::inRandomOrder()->value('id') ?? EngineerProfile::factory(),
            
            // مسار ملف وهمي للشهادة
            'file_path' => 'certificates/' . fake()->uuid() . '.pdf',
            
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }
}