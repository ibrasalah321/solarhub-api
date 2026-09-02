<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notificationsData = [
            [
                'title' => 'عرض سعر جديد على مشروعك',
                'body' => 'قام أحد المهندسين المعتمدين بتقديم عرض سعر جديد على طلب الخدمة الخاص بك.',
                'action_url' => '/dashboard/requests',
            ],
            [
                'title' => 'تمت الموافقة على اعتماد حسابك المهني',
                'body' => 'تهانينا! قامت إدارة المنصة بمراجعة وتوثيق ملفك المهني وأصبحت قادراً على تقديم العروض.',
                'action_url' => '/dashboard/profile',
            ],
            [
                'title' => 'تحديث في حالة الطلب',
                'body' => 'تم قبول عرض السعر الخاص بك من قبل العميل، يرجى التواصل لبدء التنفيذ الميداني.',
                'action_url' => '/dashboard/offers',
            ],
            [
                'title' => 'تقييم جديد لخدمتك',
                'body' => 'قام العميل بإضافة تقييم ومراجعة جديدة على إنجاز مشروع الطاقة الشمسية الأخير.',
                'action_url' => '/dashboard/ratings',
            ],
            [
                'title' => 'إشعار أمني',
                'body' => 'تم تسجيل الدخول إلى حسابك من جهاز جديد، إذا لم تكن أنت يرجى تغيير كلمة المرور فوراً.',
                'action_url' => '/dashboard/settings',
            ],
        ];

        $selected = fake()->randomElement($notificationsData);

        return [
            // توليد UUID للمفتاح الأساسي لأن العمود من نوع character/uuid
            'id' => (string) Str::uuid(),
            
            // ربط الإشعار بمستخدم عشوائي موجود أو إنشاء مستخدم جديد
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            
            'title' => $selected['title'],
            'body' => $selected['body'],
            'action_url' => $selected['action_url'],
            
            // احتمال 60% أن يكون الإشعار مقروءاً وله تاريخ، و40% غير مقروء (null)
            'read_at' => fake()->optional(0.6)->dateTimeBetween('-1 month', 'now'),
            
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * حالة: إشعار غير مقروء (Unread)
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }
}