<?php
use App\Models\User;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Auth\Admin\AdminApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {
        Route::get('/engineers/pending', [
            AdminApprovalController::class,
            'pendingEngineers',
        ])->middleware('permission:engineers.view-pending');

        Route::patch('/engineers/{engineer}/approve', [
            AdminApprovalController::class,
            'approveEngineer',
        ])->middleware('permission:engineers.approve');

        Route::patch('/engineers/{engineer}/reject', [
            AdminApprovalController::class,
            'rejectEngineer',
        ])->middleware('permission:engineers.reject');

        Route::get('/stores/pending', [
            AdminApprovalController::class,
            'pendingStores',
        ])->middleware('permission:stores.view-pending');

        Route::patch('/stores/{store}/approve', [
            AdminApprovalController::class,
            'approveStore',
        ])->middleware('permission:stores.approve');

        Route::patch('/stores/{store}/reject', [
            AdminApprovalController::class,
            'rejectStore',
        ])->middleware('permission:stores.reject');

        // 0. إحصائيات لوحة التحكم: للمدير فقط، واستعلام واحد بدل ثمانية
        Route::get('/dashboard/stats', function () {
            $data = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_stats_v2', now()->addSeconds(60), function () {
                $row = \Illuminate\Support\Facades\DB::selectOne("
                    select
                        (select count(*) from users where deleted_at is null) as users_count,
                        (select count(*) from stores where approval_status = 'approved') as approved_stores_count,
                        (select count(*) from stores where approval_status = 'pending') as pending_stores_count,
                        (select count(*) from engineer_profile where approval_status = 'approved') as approved_engineers_count,
                        (select count(*) from engineer_profile where approval_status = 'pending') as pending_engineers_count,
                        (select count(*) from orders) as orders_count,
                        (select count(*) from orders where status in ('processing', 'pending')) as pending_orders_count,
                        (select count(*) from service_requests where status in ('open_for_bids', 'open')) as open_services_count
                ");

                return array_map('intval', (array) $row);
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        })->middleware('role:admin');
    });



    Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        // إدارة الصلاحيات والأدوار: تتطلب users.manage حتى لا يرفع مشرف مقيّد صلاحياته بنفسه.
        Route::middleware('permission:users.manage')->group(function () {
            // 1. عرض قائمة الصلاحيات المتاحة.
            Route::get('/permissions', function () {
                $data = Permission::query()
                    ->where('guard_name', 'web')
                    ->orderBy('name')
                    ->get(['id', 'name']);

                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                ]);
            });

            // 2. عرض قائمة المشرفين / الأدمنز مع أدوارهم وصلاحياتهم الحالية.
            Route::get('/permissions/admins', function () {
                $admins = User::role('admin')->with(['roles.permissions', 'permissions'])->get();
                $otherStaff = User::has('permissions')
                    ->whereNotIn('id', $admins->pluck('id'))
                    ->with(['roles.permissions', 'permissions'])
                    ->get();
                $allAdmins = $admins->merge($otherStaff);

                $result = $allAdmins->map(function ($user) {
                    $primaryRole = $user->roles->first()?->name ?? 'admin';
                    $roleTitle = match ($primaryRole) {
                        'admin' => 'مدير نظام عام (Super Admin)',
                        'supplier' => 'حساب مورد معتمد (Supplier)',
                        'engineer' => 'حساب مهندس معتمد (Engineer)',
                        'customer' => 'عميل مسجل (Customer)',
                        default => 'مشرف إداري',
                    };

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'status' => $user->status,
                        'role' => $primaryRole,
                        'role_title' => $roleTitle,
                        'roleTitle' => $roleTitle,
                        'direct_permissions' => $user->permissions->pluck('name')->values()->toArray(),
                        'all_permissions' => $user->getAllPermissions()->pluck('name')->values()->toArray(),
                        'roles' => $user->roles->pluck('name')->values()->toArray(),
                        'created_at' => $user->created_at?->toISOString(),
                    ];
                });

                return response()->json([
                    'status' => 'success',
                    'data' => $result,
                ]);
            });

            // 3. عرض صلاحيات مستخدم.
            Route::get('/users/{user}/permissions', function (User $user) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->roles->first()?->name ?? 'admin',
                        'direct_permissions' => $user->permissions->pluck('name')->values()->toArray(),
                        'all_permissions' => $user->getAllPermissions()->pluck('name')->values()->toArray(),
                        'roles' => $user->roles->pluck('name')->values()->toArray(),
                    ],
                ]);
            });

            Route::get('/permissions/users/{user}', function (User $user) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->roles->first()?->name ?? 'admin',
                        'direct_permissions' => $user->permissions->pluck('name')->values()->toArray(),
                        'all_permissions' => $user->getAllPermissions()->pluck('name')->values()->toArray(),
                        'roles' => $user->roles->pluck('name')->values()->toArray(),
                    ],
                ]);
            });

            // 4. مزامنة وحفظ صلاحيات المستخدم بالكامل.
            Route::post('/permissions/users/{user}/sync', function (Request $request, User $user) {
                $data = $request->validate([
                    'permissions' => 'present|array',
                    'permissions.*' => [
                        'string',
                        Rule::exists('permissions', 'name')->where('guard_name', 'web'),
                    ],
                ]);

                $user->syncPermissions($data['permissions']);
                $user->unsetRelation('permissions');

                return response()->json([
                    'status' => 'success',
                    'message' => 'تم تحديث صلاحيات المشرف بنجاح.',
                    'data' => [
                        'user_id' => $user->id,
                        'direct_permissions' => $user->permissions->pluck('name')->values()->toArray(),
                        'all_permissions' => $user->getAllPermissions()->pluck('name')->values()->toArray(),
                    ],
                ]);
            });

            // 5. تعيين دور إداري لمستخدم.
            Route::post('/permissions/users/{user}/role', function (Request $request, User $user) {
                $data = $request->validate([
                    'role' => 'required|string|exists:roles,name',
                ]);

                $user->syncRoles([$data['role']]);
                $user->unsetRelation('roles');

                return response()->json([
                    'status' => 'success',
                    'message' => "تم تعيين الدور {$data['role']} بنجاح.",
                    'data' => [
                        'user_id' => $user->id,
                        'roles' => $user->roles->pluck('name')->values()->toArray(),
                        'all_permissions' => $user->getAllPermissions()->pluck('name')->values()->toArray(),
                    ],
                ]);
            });

            // 3. منح صلاحية مباشرة دون حذف الصلاحيات الأخرى.
            Route::post('/users/{user}/permissions', function (
                Request $request,
                User $user
            ) {
                $data = $request->validate([
                    'permission' => [
                        'required',
                        'string',
                        Rule::exists('permissions', 'name')
                            ->where('guard_name', 'web'),
                    ],
                ]);

                $permission = Permission::findByName(
                    $data['permission'],
                    'web'
                );

                $user->givePermissionTo($permission);
                $user->unsetRelation('permissions');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Permission granted successfully.',
                    'data' => [
                        'user_id' => $user->id,
                        'direct_permissions' => $user->permissions
                            ->pluck('name')->values(),
                        'all_permissions' => $user->getAllPermissions()
                            ->pluck('name')->values(),
                    ],
                ]);
            });

            // 4. إزالة صلاحية مباشرة.
            Route::delete('/users/{user}/permissions', function (
                Request $request,
                User $user
            ) {
                $data = $request->validate([
                    'permission' => [
                        'required',
                        'string',
                        Rule::exists('permissions', 'name')
                            ->where('guard_name', 'web'),
                    ],
                ]);

                $permission = Permission::findByName(
                    $data['permission'],
                    'web'
                );

                $user->revokePermissionTo($permission);
                $user->unsetRelation('permissions');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Direct permission removed successfully.',
                    'data' => [
                        'user_id' => $user->id,
                        'direct_permissions' => $user->permissions
                            ->pluck('name')->values(),
                        'all_permissions' => $user->getAllPermissions()
                            ->pluck('name')->values(),
                    ],
                ]);
            });
        });

        // 5. إدارة تقييمات المتاجر والموردين
        Route::get('/reviews/stores', [\App\Http\Controllers\Api\Rating\StoreRatingController::class, 'index']);
        Route::patch('/reviews/stores/{storeRating}/toggle-approval', [\App\Http\Controllers\Api\Rating\StoreRatingController::class, 'toggleApproval']);

        // 6. إدارة تقييمات المهندسين
        Route::get('/reviews/engineers', [\App\Http\Controllers\Api\Rating\EngineerRatingController::class, 'index']);
        Route::patch('/reviews/engineers/{rating}/toggle-approval', [\App\Http\Controllers\Api\Rating\EngineerRatingController::class, 'toggleApproval']);

        // 8. عمليات الإدارة (بديل آمن لمسارات خادم Nuxt القديمة server/api/admin)
        $ops = \App\Http\Controllers\Api\Admin\AdminOperationsController::class;

        Route::get('/orders', [$ops, 'orders'])->middleware('permission:orders.manage-status');
        Route::patch('/orders/{order}/status', [$ops, 'updateOrderStatus'])->middleware('permission:orders.manage-status');

        Route::get('/finance/payments', [$ops, 'payments'])->middleware('permission:order-payments.verify');
        Route::get('/finance/payouts', [$ops, 'payouts'])->middleware('permission:store-payouts.manage');
        Route::get('/finance/payouts/eligible', [$ops, 'payoutEligible'])->middleware('permission:store-payouts.manage');
        Route::get('/finance/stats', [$ops, 'financeStats'])->middleware('permission:store-payouts.manage');
        Route::get('/wallet-providers', [$ops, 'walletProviders'])->middleware('permission:wallet-providers.manage');
        Route::get('/permissions/search-users', [$ops, 'searchUsers'])->middleware('permission:users.manage');

        // 7. إدارة الإشعارات والبث العام
        Route::get('/notifications', [\App\Http\Controllers\Api\Notification\AdminNotificationController::class, 'index']);
        Route::post('/notifications/broadcast', [\App\Http\Controllers\Api\Notification\AdminNotificationController::class, 'broadcast']);

        // 8. إدارة الطلبات المركزية للأدمن
        Route::get('/orders', function (Request $request) {
            $perPage = (int) $request->input('per_page', 30);
            $status = $request->input('status');

            $query = \App\Models\Order::query()
                ->select([
                    'id',
                    'order_number',
                    'customer_id',
                    'delivery_governorate_id',
                    'total_amount',
                    'delivery_address',
                    'status',
                    'created_at',
                ])
                ->with([
                    'customer:id,name,phone,email',
                    'governorate:id,name_ar,name_en',
                    'orderStores:id,order_id,store_id,subtotal,status,delivered_at,customer_confirmed_at,notes',
                    'orderStores.store:id,company_name',
                    'orderStores.items:id,order_store_id,store_product_id,unit_price,quantity,total_price',
                    'orderStores.items.storeProduct:id,master_product_id',
                    'orderStores.items.storeProduct.masterProduct:id,title',
                ])
                ->latest();

            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            $orders = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ]);
        });

        Route::get('/orders/{order}', function (\App\Models\Order $order) {
            $order->load([
                'customer:id,name,phone,email',
                'governorate:id,name_ar,name_en',
                'orderStores.store:id,company_name',
                'orderStores.items.storeProduct.masterProduct:id,title',
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        });

        Route::post('/orders/{order}/status', function (Request $request, \App\Models\Order $order) {
            $validated = $request->validate([
                'status' => 'required|string|in:pending,processing,shipped,delivered,completed,cancelled',
            ]);

            $order->status = $validated['status'];
            $order->save();

            if (in_array($validated['status'], ['completed', 'cancelled'])) {
                $order->orderStores()->update(['status' => $validated['status']]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث حالة الطلب بنجاح.',
                'data' => $order,
            ]);
        });

        // 9. الإدارة المالية الشاملة والمدفوعات
        Route::get('/finance/stats', function () {
            return \Illuminate\Support\Facades\Cache::remember('admin_finance_stats', now()->addSeconds(60), function () {
                $stats = \Illuminate\Support\Facades\DB::selectOne("
                    SELECT 
                        COALESCE((SELECT SUM(amount) FROM order_payments WHERE status = 'verified'), 0) as total_payments,
                        COALESCE((SELECT SUM(amount) FROM order_payments WHERE status = 'pending'), 0) as pending_payments,
                        COALESCE((SELECT SUM(net_amount) FROM store_payouts WHERE status = 'completed'), 0) as total_payouts,
                        COALESCE((SELECT SUM(net_amount) FROM store_payouts WHERE status = 'pending'), 0) as pending_payouts,
                        COALESCE((SELECT SUM(commission_amount) FROM store_payouts WHERE status = 'completed'), 0) as total_commissions,
                        (SELECT COUNT(*) FROM wallet_providers) as providers_count
                ");

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'total_payments' => (float) ($stats->total_payments ?? 0),
                        'pending_payments' => (float) ($stats->pending_payments ?? 0),
                        'total_payouts' => (float) ($stats->total_payouts ?? 0),
                        'pending_payouts' => (float) ($stats->pending_payouts ?? 0),
                        'total_commissions' => (float) ($stats->total_commissions ?? 0),
                        'providers_count' => (int) ($stats->providers_count ?? 0),
                    ],
                ]);
            });
        });

        Route::get('/finance/payments', function (Request $request) {
            $perPage = (int) $request->input('per_page', 30);
            $payments = \App\Models\OrderPayment::with([
                'order:id,order_number,customer_id,total_amount,status',
                'order.customer:id,name,phone,email',
                'walletProvider:id,code,name_ar,name_en,logo_path',
            ])->latest()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $payments->items(),
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
            ]);
        });

        Route::post('/finance/payments/{orderPayment}/status', function (Request $request, \App\Models\OrderPayment $orderPayment) {
            $validated = $request->validate([
                'status' => 'required|string|in:verified,rejected,pending',
            ]);

            $orderPayment->status = $validated['status'];
            if ($validated['status'] === 'verified') {
                $orderPayment->verified_at = now();
                $orderPayment->verified_by = $request->user()->id;
            }
            $orderPayment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث حالة الدفعة بنجاح.',
                'data' => $orderPayment,
            ]);
        });

        Route::get('/finance/payouts', function (Request $request) {
            $perPage = (int) $request->input('per_page', 30);
            $payouts = \App\Models\StorePayout::with([
                'orderStore.store:id,company_name',
                'orderStore.order:id,order_number',
            ])->latest()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $payouts->items(),
                'pagination' => [
                    'current_page' => $payouts->currentPage(),
                    'last_page' => $payouts->lastPage(),
                    'per_page' => $payouts->perPage(),
                    'total' => $payouts->total(),
                ],
            ]);
        });

        Route::post('/finance/payouts/{storePayout}/status', function (Request $request, \App\Models\StorePayout $storePayout) {
            $validated = $request->validate([
                'status' => 'required|string|in:completed,pending,failed',
                'transfer_reference' => 'nullable|string|max:100',
            ]);

            $storePayout->status = $validated['status'];
            if (!empty($validated['transfer_reference'])) {
                $storePayout->transfer_reference = $validated['transfer_reference'];
            }
            if ($validated['status'] === 'completed') {
                $storePayout->paid_at = now();
            }
            $storePayout->save();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث حالة المستحقات بنجاح.',
                'data' => $storePayout,
            ]);
        });

        Route::get('/finance/settings', function () {
            $settings = \App\Models\PlatformSetting::first();
            return response()->json([
                'status' => 'success',
                'data' => $settings,
            ]);
        });

        Route::post('/finance/settings', function (Request $request) {
            $validated = $request->validate([
                'currency' => 'nullable|string|max:10',
                'platform_name' => 'nullable|string|max:100',
                'support_phone' => 'nullable|string|max:20',
                'support_email' => 'nullable|email|max:100',
                'is_maintenance_mode' => 'nullable|boolean',
            ]);

            $settings = \App\Models\PlatformSetting::firstOrCreate(
                ['id' => 1],
                [
                    'platform_name' => 'سولار هاب - SolarHub',
                    'support_phone' => '777000000',
                    'support_email' => 'support@solarhub.com',
                ]
            );

            $settings->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث الإعدادات بنجاح.',
                'data' => $settings,
            ]);
        });

        // 10. إدارة مزودي المحافظ الإلكترونية للأدمن
        Route::get('/wallet-providers', function () {
            $providers = \App\Models\WalletProvider::latest()->get();
            $storage = app(\App\Services\SupabaseStorageService::class);
            $providers->transform(function ($provider) use ($storage) {
                $provider->logo_url = $provider->logo_path ? $storage->publicUrl($provider->logo_path) : null;
                return $provider;
            });

            return response()->json([
                'status' => 'success',
                'data' => $providers,
            ]);
        });

        Route::post('/wallet-providers', function (Request $request) {
            $code = strtoupper(trim($request->input('code', '')));
            $nameAr = trim($request->input('name_ar', ''));
            $nameEn = trim($request->input('name_en', ''));
            $isActive = $request->boolean('is_active', true);
            $logoPath = $request->input('logo_path');

            if (!$code || !$nameAr) {
                return response()->json(['status' => 'error', 'message' => 'رمز المحفظة والاسم العربي مطلوبان'], 422);
            }

            if (\App\Models\WalletProvider::where('code', $code)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'رمز المحفظة مستخدم بالفعل: ' . $code], 422);
            }

            if ($request->filled('logo_base64')) {
                $fileData = base64_decode($request->input('logo_base64'));
                $ext = pathinfo($request->input('logo_filename', 'logo.png'), PATHINFO_EXTENSION) ?: 'png';
                $cleanExt = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext)) ?: 'png';
                $path = 'wallet-providers/' . \Illuminate\Support\Str::uuid() . '.' . $cleanExt;
                \Illuminate\Support\Facades\Storage::disk('supabase_public')->put($path, $fileData);
                $logoPath = $path;
            }

            $provider = \App\Models\WalletProvider::create([
                'code' => $code,
                'name_ar' => $nameAr,
                'name_en' => $nameEn ?: null,
                'is_active' => $isActive,
                'logo_path' => $logoPath ?: null,
            ]);

            $storage = app(\App\Services\SupabaseStorageService::class);
            $provider->logo_url = $provider->logo_path ? $storage->publicUrl($provider->logo_path) : null;

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء مزود المحفظة بنجاح.',
                'data' => $provider,
            ], 201);
        });

        Route::put('/wallet-providers/{walletProvider}', function (Request $request, \App\Models\WalletProvider $walletProvider) {
            $updateData = [];
            if ($request->has('code')) $updateData['code'] = strtoupper(trim($request->input('code')));
            if ($request->has('name_ar')) $updateData['name_ar'] = trim($request->input('name_ar'));
            if ($request->has('name_en')) $updateData['name_en'] = trim($request->input('name_en'));
            if ($request->has('is_active')) $updateData['is_active'] = $request->boolean('is_active');

            if ($request->filled('logo_base64')) {
                $storage = app(\App\Services\SupabaseStorageService::class);
                if ($walletProvider->logo_path) {
                    $storage->deletePublic($walletProvider->logo_path);
                }
                $fileData = base64_decode($request->input('logo_base64'));
                $ext = pathinfo($request->input('logo_filename', 'logo.png'), PATHINFO_EXTENSION) ?: 'png';
                $cleanExt = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext)) ?: 'png';
                $newPath = 'wallet-providers/' . \Illuminate\Support\Str::uuid() . '.' . $cleanExt;
                \Illuminate\Support\Facades\Storage::disk('supabase_public')->put($newPath, $fileData);
                $updateData['logo_path'] = $newPath;
            }

            $walletProvider->update($updateData);

            $storage = app(\App\Services\SupabaseStorageService::class);
            $walletProvider->logo_url = $walletProvider->logo_path ? $storage->publicUrl($walletProvider->logo_path) : null;

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث بيانات المحفظة بنجاح.',
                'data' => $walletProvider,
            ]);
        });

        Route::delete('/wallet-providers/{walletProvider}', function (\App\Models\WalletProvider $walletProvider) {
            if ($walletProvider->userWallets()->exists() || $walletProvider->orderPayments()->exists()) {
                $walletProvider->is_active = false;
                $walletProvider->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'المحفظة مرتبطة بمعاملات سابقة، تم تعطيلها بدلاً من الحذف للحفاظ على سلامة القيود المالية.',
                    'data' => $walletProvider,
                ]);
            }

            if ($walletProvider->logo_path) {
                app(\App\Services\SupabaseStorageService::class)->deletePublic($walletProvider->logo_path);
            }

            $walletProvider->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف مزود المحفظة بنجاح.',
            ]);
        });
    });
