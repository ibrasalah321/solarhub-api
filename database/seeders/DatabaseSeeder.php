<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\EngineerCertificate;
use App\Models\EngineerProfile;
use App\Models\EngineerRating;
use App\Models\MasterProduct;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\OrderStore;
use App\Models\PortfolioItem;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\Specialization;
use App\Models\Store;
use App\Models\StorePayout;
use App\Models\StoreProduct;
use App\Models\StoreRating;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\WalletProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            GovernoratesSeeder::class,
            SpecializationsSeeder::class,
            ServiceTypesSeeder::class,
            CategoriesSeeder::class,
            BrandsSeeder::class,
            MasterProductsSeeder::class,
            WalletProviderSeeder::class,
            NotificationTemplatesSeeder::class,
            PlatformSettingsSeeder::class,
            AdminUserSeeder::class,
        ]);

        $customers = $this->seedCustomers();
        $engineers = $this->seedEngineers();
        $stores = $this->seedStores();

        $walletUsers = $customers
            ->concat($stores->map->user)
            ->filter()
            ->unique('id')
            ->values();

        $this->seedWallets($walletUsers);
        $this->seedCarts($customers);
        $this->seedQuoteRequests($customers);
        $this->seedServiceMarketplace($customers, $engineers);
        $this->seedOrders($customers, $stores);
    }

    private function seedCustomers(): Collection
    {
        $missingCount = max(0, 10 - User::role('customer')->count());

        if ($missingCount > 0) {
            User::factory()->count($missingCount)->customer()->create();
        }

        return User::role('customer')->oldest('id')->limit(10)->get();
    }

    private function seedEngineers(): Collection
    {
        $missingCount = max(0, 5 - EngineerProfile::query()->count());

        if ($missingCount > 0) {
            EngineerProfile::factory()->count($missingCount)->create();
        }

        $engineers = EngineerProfile::query()
            ->oldest('id')
            ->limit(5)
            ->get();

        $specializationIds = Specialization::query()
            ->pluck('id')
            ->all();

        foreach ($engineers as $engineer) {
            if ($engineer->certificates()->doesntExist()) {
                EngineerCertificate::factory()->count(2)->create([
                    'engineer_id' => $engineer->id,
                ]);
            }

            if ($engineer->portfolioItems()->doesntExist()) {
                PortfolioItem::factory()->count(2)->create([
                    'engineer_id' => $engineer->id,
                    'location_coordinates' => null,
                ]);
            }

            if ($specializationIds !== []) {
                $engineer->specializations()->syncWithoutDetaching(
                    array_slice($specializationIds, 0, 2)
                );
            }
        }

        return $engineers;
    }

    private function seedStores(): Collection
    {
        $missingCount = max(0, 4 - Store::query()->count());

        for ($index = 0; $index < $missingCount; $index++) {
            $supplier = User::factory()->supplier()->create();

            Store::factory()->create([
                'user_id' => $supplier->id,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);
        }

        $stores = Store::query()
            ->with('user')
            ->oldest('id')
            ->limit(4)
            ->get();

        $products = MasterProduct::query()
            ->where('is_active', true)
            ->oldest('id')
            ->limit(3)
            ->get();

        foreach ($stores as $store) {
            foreach ($products as $product) {
                StoreProduct::query()->updateOrCreate(
                    [
                        'master_product_id' => $product->id,
                        'store_id' => $store->id,
                        'governorate_id' => $store->user?->governorate_id,
                    ],
                    [
                        'price' => fake()->randomFloat(2, 100, 3000),
                        'stock_quantity' => fake()->numberBetween(10, 100),
                        'min_order_qty' => 1,
                        'warranty_period' => '2 Years',
                        'status' => 'active',
                    ]
                );
            }
        }

        return $stores;
    }

    private function seedWallets(Collection $users): void
    {
        $provider = WalletProvider::query()
            ->where('is_active', true)
            ->oldest('id')
            ->first();

        if ($provider === null) {
            return;
        }

        foreach ($users as $user) {
            UserWallet::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'wallet_provider_id' => $provider->id,
                    'account_number' => '77'.str_pad(
                        (string) $user->id,
                        7,
                        '0',
                        STR_PAD_LEFT
                    ),
                ],
                [
                    'account_name' => $user->name,
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedCarts(Collection $customers): void
    {
        $products = StoreProduct::query()
            ->where('status', 'active')
            ->oldest('id')
            ->limit(2)
            ->get();

        foreach ($customers->take(5) as $customer) {
            $cart = Cart::query()->firstOrCreate([
                'user_id' => $customer->id,
            ]);

            foreach ($products as $product) {
                CartItem::query()->updateOrCreate(
                    [
                        'cart_id' => $cart->id,
                        'store_product_id' => $product->id,
                    ],
                    [
                        'quantity' => fake()->numberBetween(1, 3),
                    ]
                );
            }
        }
    }

    private function seedQuoteRequests(Collection $customers): void
    {
        $products = StoreProduct::query()
            ->where('status', 'active')
            ->oldest('id')
            ->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $missingCount = max(0, 8 - QuoteRequest::query()->count());

        for ($index = 0; $index < $missingCount; $index++) {
            QuoteRequest::factory()->create([
                'customer_id' => $customers[$index % $customers->count()]->id,
                'store_product_id' => $products[$index % $products->count()]->id,
            ]);
        }
    }

    private function seedServiceMarketplace(
        Collection $customers,
        Collection $engineers
    ): void {
        if ($customers->isEmpty() || $engineers->isEmpty()) {
            return;
        }

        $missingCount = max(0, 8 - ServiceRequest::query()->count());

        for ($index = 0; $index < $missingCount; $index++) {
            ServiceRequest::factory()->create([
                'customer_id' => $customers[$index % $customers->count()]->id,
                'location_coordinates' => null,
            ]);
        }

        $requests = ServiceRequest::query()
            ->oldest('id')
            ->limit(8)
            ->get();

        foreach ($requests as $requestIndex => $request) {
            foreach ($engineers->take(2) as $engineer) {
                Offer::query()->firstOrCreate(
                    [
                        'service_request_id' => $request->id,
                        'engineer_id' => $engineer->id,
                    ],
                    [
                        'proposed_cost' => fake()->randomFloat(2, 150, 3500),
                        'execution_time_days' => fake()->numberBetween(2, 20),
                        'technical_proposal' => fake()->paragraph(3),
                        'proposal_file' => null,
                        'status' => 'pending',
                    ]
                );
            }

            if ($requestIndex < 2) {
                $engineer = $engineers[$requestIndex % $engineers->count()];

                $request->update(['status' => 'completed']);

                EngineerRating::query()->updateOrCreate(
                    ['service_request_id' => $request->id],
                    [
                        'customer_id' => $request->customer_id,
                        'engineer_id' => $engineer->id,
                        'rating' => 5,
                        'comment' => 'خدمة ممتازة وتنفيذ احترافي.',
                        'is_approved' => true,
                    ]
                );
            }
        }
    }

    private function seedOrders(
        Collection $customers,
        Collection $stores
    ): void {
        if ($customers->isEmpty() || $stores->isEmpty()) {
            return;
        }

        $providerId = WalletProvider::query()
            ->where('is_active', true)
            ->oldest('id')
            ->value('id');

        $adminId = User::role('admin')->value('id');
        $missingCount = max(0, 5 - Order::query()->count());

        for ($index = 0; $index < $missingCount; $index++) {
            $customer = $customers[$index % $customers->count()];
            $store = $stores[$index % $stores->count()];

            $storeProduct = StoreProduct::query()
                ->where('store_id', $store->id)
                ->where('status', 'active')
                ->oldest('id')
                ->first();

            if ($storeProduct === null) {
                continue;
            }

            $quantity = fake()->numberBetween(1, 3);
            $unitPrice = (float) ($storeProduct->price ?? 100);
            $total = round($quantity * $unitPrice, 2);

            $order = Order::factory()->create([
                'customer_id' => $customer->id,
                'delivery_governorate_id' => $customer->governorate_id,
                'total_amount' => $total,
                'status' => 'completed',
            ]);

            $orderStore = OrderStore::factory()->create([
                'order_id' => $order->id,
                'store_id' => $store->id,
                'subtotal' => $total,
                'status' => 'completed',
                'delivered_at' => now(),
                'customer_confirmed_at' => now(),
            ]);

            OrderItem::factory()->create([
                'order_store_id' => $orderStore->id,
                'store_product_id' => $storeProduct->id,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $total,
            ]);

            OrderPayment::factory()->create([
                'order_id' => $order->id,
                'wallet_provider_id' => $providerId,
                'amount' => $total,
                'transaction_reference' => 'PAY-'.strtoupper(
                    fake()->bothify('??########')
                ),
                'status' => 'verified',
                'verified_by' => $adminId,
                'paid_at' => now(),
                'verified_at' => now(),
            ]);

            StoreRating::query()->updateOrCreate(
                ['order_store_id' => $orderStore->id],
                [
                    'customer_id' => $customer->id,
                    'store_id' => $store->id,
                    'rating' => 5,
                    'comment' => 'منتجات أصلية وتسليم ممتاز.',
                    'is_approved' => true,
                ]
            );

            $walletId = UserWallet::query()
                ->where('user_id', $store->user_id)
                ->where('is_active', true)
                ->value('id');

            $commissionRate = 5.00;
            $commissionAmount = round(
                $total * ($commissionRate / 100),
                2
            );

            StorePayout::query()->updateOrCreate(
                ['order_store_id' => $orderStore->id],
                [
                    'user_wallet_id' => $walletId,
                    'total_amount' => $total,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                    'net_amount' => round($total - $commissionAmount, 2),
                    'status' => 'completed',
                    'transfer_reference' => 'TRX-'.strtoupper(
                        fake()->bothify('??########')
                    ),
                    'paid_at' => now(),
                ]
            );
        }
    }
}
