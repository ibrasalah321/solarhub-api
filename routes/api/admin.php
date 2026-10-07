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
    });



    Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        // 1. عرض قائمة الصلاحيات المتاحة.
        Route::get('/permissions', function () {
            return response()->json([
                'status' => 'success',
                'data' => Permission::query()
                    ->where('guard_name', 'web')
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ]);
        });

        // 2. عرض صلاحيات مستخدم.
        Route::get('/users/{user}/permissions', function (User $user) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_id' => $user->id,
                    'direct_permissions' => $user->permissions
                        ->pluck('name')->values(),
                    'all_permissions' => $user->getAllPermissions()
                        ->pluck('name')->values(),
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
