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

            if ($allAdmins->isEmpty()) {
                $allAdmins = User::with(['roles.permissions', 'permissions'])->limit(15)->get();
            }

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

        // 5. إدارة تقييمات المتاجر والموردين
        Route::get('/reviews/stores', [\App\Http\Controllers\Api\Rating\StoreRatingController::class, 'index']);
        Route::patch('/reviews/stores/{storeRating}/toggle-approval', [\App\Http\Controllers\Api\Rating\StoreRatingController::class, 'toggleApproval']);

        // 6. إدارة تقييمات المهندسين
        Route::get('/reviews/engineers', [\App\Http\Controllers\Api\Rating\EngineerRatingController::class, 'index']);
        Route::patch('/reviews/engineers/{rating}/toggle-approval', [\App\Http\Controllers\Api\Rating\EngineerRatingController::class, 'toggleApproval']);

        // 7. إدارة الإشعارات والبث العام
        Route::get('/notifications', [\App\Http\Controllers\Api\Notification\AdminNotificationController::class, 'index']);
        Route::post('/notifications/broadcast', [\App\Http\Controllers\Api\Notification\AdminNotificationController::class, 'broadcast']);

        // 8. عمليات كانت تمر عبر مسارات Nuxt غير المحمية (server/api/admin/*)
        Route::controller(\App\Http\Controllers\Admin\AdminOperationsController::class)->group(function () {
            Route::get('/orders', 'orders');
            Route::post('/orders/{order}/status', 'updateOrderStatus');

            Route::get('/finance/payments', 'payments');
            Route::post('/finance/payments/{payment}/status', 'updatePaymentStatus')->whereNumber('payment');
            Route::get('/finance/payouts', 'payouts');
            Route::post('/finance/payouts/{payout}/status', 'updatePayoutStatus')->whereNumber('payout');
            Route::get('/finance/stats', 'financeStats');
            Route::get('/finance/settings', 'settings');
            Route::post('/finance/settings', 'updateSettings');

            Route::get('/wallet-providers', 'walletProviders');
            Route::post('/wallet-providers', 'storeWalletProvider');
            Route::put('/wallet-providers/{walletProvider}', 'updateWalletProvider')->whereNumber('walletProvider');
            Route::delete('/wallet-providers/{walletProvider}', 'destroyWalletProvider')->whereNumber('walletProvider');
        });
    });
