<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\StorePayout;
use App\Models\StoreProduct;
use App\Models\User;
use App\Models\WalletProvider;
use App\Services\SupabaseStorageService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * مسارات الإدارة التي كانت الواجهة تنفذها سابقاً عبر سكربتات PHP داخل خادم Nuxt
 * (server/api/admin + server/utils/*.php) بدون أي مصادقة.
 *
 * كل المسارات هنا محمية بـ auth:sanctum + role:admin + صلاحية مناسبة (انظر routes/api/admin.php)،
 * وتُرجع نفس شكل البيانات الذي كانت الواجهة تتوقعه.
 */
class AdminOperationsController extends Controller
{
    use ApiResponseTrait;

    private const MAX_ROWS = 500;

    /**
     * قائمة طلبات الشراء مع العميل والمحافظة والطلبات الفرعية لكل متجر وعناصرها.
     */
    public function orders(Request $request)
    {
        $orders = Order::query()
            ->with([
                'customer:id,name,phone,email',
                'governorate:id,name_ar,name_en',
                'orderStores.store:id,company_name',
                'orderStores.items.storeProduct:id,master_product_id',
                'orderStores.items.storeProduct.masterProduct:id,title',
            ])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->limit(self::MAX_ROWS)
            ->get();

        return $this->successResponse($orders, 'Orders retrieved successfully.');
    }

    /**
     * تغيير حالة طلب الشراء من الإدارة.
     *
     * - cancelled: يلغي الطلبات الفرعية النشطة ويعيد المخزون.
     * - completed: يكمل الطلبات الفرعية النشطة.
     * - pending / processing: يغيّر حالة الطلب الرئيسي فقط.
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
        ]);

        $status = $data['status'];

        abort_if(
            $order->status === 'cancelled' && $status !== 'cancelled',
            422,
            'لا يمكن إعادة تفعيل طلب ملغي.'
        );

        DB::transaction(function () use ($order, $status) {
            $order->loadMissing('orderStores.items');
            $closed = ['cancelled', 'rejected'];

            foreach ($order->orderStores as $orderStore) {
                if (in_array($orderStore->status, $closed, true)) {
                    continue;
                }

                if ($status === 'cancelled') {
                    foreach ($orderStore->items as $item) {
                        $product = StoreProduct::query()->lockForUpdate()->find($item->store_product_id);
                        if ($product) {
                            $product->increment('stock_quantity', $item->quantity);
                            if ($product->status === 'out_of_stock' && $product->stock_quantity > 0) {
                                $product->update(['status' => 'active']);
                            }
                        }
                    }
                    $orderStore->update(['status' => 'cancelled']);
                }

                if ($status === 'completed') {
                    $orderStore->update([
                        'status' => 'completed',
                        'delivered_at' => $orderStore->delivered_at ?? now(),
                    ]);
                }
            }

            $order->update(['status' => $status]);
        });

        return $this->successResponse(
            $order->fresh(['orderStores']),
            'Order status updated successfully.'
        );
    }

    /**
     * كل مدفوعات العملاء مع الطلب والعميل ومزود المحفظة ورابط مؤقت للإيصال.
     */
    public function payments(Request $request)
    {
        $storage = app(SupabaseStorageService::class);

        $payments = OrderPayment::query()
            ->with([
                'order:id,order_number,customer_id,total_amount,status',
                'order.customer:id,name,phone,email',
                'walletProvider',
            ])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->limit(self::MAX_ROWS)
            ->get()
            ->map(function (OrderPayment $payment) use ($storage) {
                $row = $payment->toArray();
                $row['receipt_url'] = $payment->receipt_image
                    ? $storage->temporaryPrivateUrl($payment->receipt_image, 15)
                    : null;

                return $row;
            });

        return $this->successResponse($payments, 'Payments retrieved successfully.');
    }

    /**
     * كل مستحقات الموردين مع الطلب الفرعي والمتجر والمحفظة.
     */
    public function payouts(Request $request)
    {
        $payouts = StorePayout::query()
            ->with([
                'orderStore.store:id,company_name,commercial_registry',
                'orderStore.order:id,order_number',
                'userWallet.walletProvider',
            ])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->limit(self::MAX_ROWS)
            ->get();

        return $this->successResponse($payouts, 'Payouts retrieved successfully.');
    }

    /**
     * الملخص المالي العام.
     */
    public function financeStats()
    {
        return $this->successResponse([
            'total_payments' => (float) OrderPayment::where('status', 'verified')->sum('amount'),
            'pending_payments' => (float) OrderPayment::where('status', 'pending')->sum('amount'),
            'total_payouts' => (float) StorePayout::where('status', 'completed')->sum('net_amount'),
            'pending_payouts' => (float) StorePayout::where('status', 'pending')->sum('net_amount'),
            'total_commissions' => (float) StorePayout::where('status', 'completed')->sum('commission_amount'),
            'providers_count' => WalletProvider::count(),
        ], 'Finance summary retrieved successfully.');
    }

    /**
     * قائمة مزودي المحافظ للإدارة (تشمل غير المفعّلين وعدد الاستخدامات).
     */
    public function walletProviders()
    {
        $storage = app(SupabaseStorageService::class);

        $providers = WalletProvider::query()
            ->withCount(['userWallets', 'orderPayments'])
            ->orderBy('id')
            ->get()
            ->map(function (WalletProvider $provider) use ($storage) {
                $row = $provider->toArray();
                $row['logo_url'] = $storage->publicUrl($provider->logo_path);

                return $row;
            });

        return $this->successResponse($providers, 'Wallet providers retrieved successfully.');
    }

    /**
     * البحث عن مستخدمين لإسناد الصلاحيات.
     */
    public function searchUsers(Request $request)
    {
        $term = trim((string) $request->query('q', $request->query('search', '')));

        $users = User::query()
            ->with('roles:id,name')
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email', 'phone', 'status'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'roles' => $user->roles->pluck('name')->values(),
                'role' => $user->roles->first()?->name,
            ]);

        return $this->successResponse($users, 'Users retrieved successfully.');
    }
}
