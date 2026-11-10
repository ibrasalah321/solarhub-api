<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\EngineerProfile;
use App\Models\Governorate;
use App\Models\MasterProduct;
use App\Models\Specialization;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;

/**
 * حسابات تجريبية لكل دور، للتطوير المحلي فقط.
 *
 *   php artisan db:seed --class=TestAccountsSeeder
 *
 * - لا يعمل إلا في بيئة local أو testing.
 * - كلمة المرور من TEST_ACCOUNTS_PASSWORD، والافتراضية Test@12345.
 * - يمكن تشغيله أكثر من مرة (updateOrCreate على البريد).
 * - ليس مضافاً إلى DatabaseSeeder عمداً.
 */
class TestAccountsSeeder extends Seeder
{
    private const ROLES = ['admin', 'customer', 'supplier', 'engineer'];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->error('TestAccountsSeeder runs only in local/testing environments. Aborting.');

            return;
        }

        $this->ensureRolesExist();

        // ?: وليس قيمة افتراضية لـ env(): المتغير الفارغ (TEST_ACCOUNTS_PASSWORD=) يرجع '' لا null
        $password = Hash::make((string) (env('TEST_ACCOUNTS_PASSWORD') ?: 'Test@12345'));
        $governorateId = $this->governorateId();

        DB::transaction(function () use ($password, $governorateId) {
            $this->upsertUser('customer@solarhub.test', 'عميل تجريبي', '779900101', 'customer', $password, $governorateId);

            $supplier = $this->upsertUser('supplier@solarhub.test', 'مورد تجريبي', '779900102', 'supplier', $password, $governorateId);
            $this->seedSupplierStore($supplier, $governorateId);

            $engineer = $this->upsertUser('engineer@solarhub.test', 'مهندس تجريبي', '779900103', 'engineer', $password, $governorateId);
            $this->seedEngineerProfile($engineer);

            $admin = $this->upsertUser('admin@solarhub.test', 'مدير تجريبي', '779900104', 'admin', $password, $governorateId);
            // مدير عام: صلاحياته كلها من الدور (و Gate::before)، بدون صلاحيات مباشرة
            $admin->syncPermissions([]);
        });

        $this->command?->info('Test accounts ready: customer|supplier|engineer|admin @solarhub.test');
    }

    private function ensureRolesExist(): void
    {
        $existing = Role::query()->where('guard_name', 'web')->whereIn('name', self::ROLES)->count();

        // RolePermissionSeeder يعيد مزامنة صلاحيات الأدوار، لذلك لا نشغّله إلا إذا كان دور ناقصاً
        if ($existing < count(self::ROLES)) {
            $this->call(RolePermissionSeeder::class);
        }
    }

    private function governorateId(): int
    {
        $id = Governorate::query()->orderBy('id')->value('id');

        if ($id === null) {
            throw new RuntimeException('No governorates found. Run GovernoratesSeeder first.');
        }

        return (int) $id;
    }

    private function upsertUser(string $email, string $name, string $phone, string $role, string $passwordHash, int $governorateId): User
    {
        $phoneOwner = User::withTrashed()->where('phone', $phone)->value('email');
        if ($phoneOwner !== null && $phoneOwner !== $email) {
            throw new RuntimeException("Phone {$phone} already belongs to another user.");
        }

        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'phone' => $phone,
            'password' => $passwordHash,
            'status' => 'active',
            'email_verified_at' => $user->email_verified_at ?? now(),
            'governorate_id' => $governorateId,
            'deleted_at' => null,
        ])->save();

        $user->syncRoles([$role]);

        return $user;
    }

    private function seedSupplierStore(User $supplier, int $governorateId): void
    {
        $store = Store::query()->updateOrCreate(
            ['user_id' => $supplier->id],
            [
                'company_name' => 'متجر الطاقة التجريبي',
                'commercial_registry' => 'CR-TEST-0001',
                'tax_number' => 'TAX-TEST-0001',
                'bio' => 'متجر تجريبي لاختبار لوحة المورد.',
                'whatsapp_number' => '779900102',
                'store_type' => 'retailer',
                'address_details' => 'صنعاء - شارع تجريبي',
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'approved_at' => now(),
            ]
        );

        $products = MasterProduct::query()->where('is_active', true)->orderBy('id')->limit(3)->get();
        if ($products->isEmpty()) {
            $products = collect([$this->createFallbackMasterProduct()]);
        }

        foreach ($products->values() as $i => $product) {
            StoreProduct::query()->updateOrCreate(
                [
                    'master_product_id' => $product->id,
                    'store_id' => $store->id,
                    'governorate_id' => $governorateId,
                ],
                [
                    'price' => 150000 + ($i * 50000),
                    'stock_quantity' => 20 + ($i * 5),
                    'min_order_qty' => 1,
                    'warranty_period' => '12 شهر',
                    'status' => 'active',
                ]
            );
        }
    }

    private function createFallbackMasterProduct(): MasterProduct
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'test-solar-panels'],
            ['name_ar' => 'ألواح شمسية (تجريبي)', 'name_en' => 'Solar Panels (Test)']
        );

        $brand = Brand::query()->firstOrCreate(['name' => 'SolarHub Test Brand'], ['is_active' => true]);

        return MasterProduct::query()->firstOrCreate(
            ['brand_id' => $brand->id, 'model_number' => 'TEST-PANEL-450'],
            [
                'category_id' => $category->id,
                'title' => 'لوح شمسي تجريبي 450 واط',
                'description' => 'منتج رئيسي تجريبي.',
                'is_active' => true,
            ]
        );
    }

    private function seedEngineerProfile(User $engineer): void
    {
        $profile = EngineerProfile::query()->updateOrCreate(
            ['user_id' => $engineer->id],
            [
                'license_number' => 'ENG-TEST-0001',
                'years_of_experience' => 5,
                'bio' => 'مهندس تجريبي لاختبار لوحة المهندس.',
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'approved_at' => now(),
            ]
        );

        $specializationId = Specialization::query()->orderBy('id')->value('id')
            ?? Specialization::query()->create([
                'name' => 'تركيب منظومات شمسية',
                'is_active' => true,
            ])->id;

        $profile->specializations()->syncWithoutDetaching([$specializationId]);
    }
}
