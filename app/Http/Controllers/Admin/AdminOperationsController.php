<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderStore;
use App\Models\PlatformSetting;
use App\Models\StorePayout;
use App\Models\WalletProvider;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * عمليات الإدارة التي كانت تُنفَّذ سابقاً عبر مسارات Nuxt (server/api/admin/*)
 * بتشغيل سكربتات PHP مباشرة على قاعدة البيانات وبدون أي مصادقة.
 * كل المسارات هنا محمية بـ auth:sanctum + role:admin (routes/api/admin.php).
 */
class AdminOperationsController extends Controller
{
    public function __construct(private readonly SupabaseStorageService $storage)
    {
    }

    // ===================== الطلبات =====================

    public function orders(): JsonResponse
    {
        $orders = Order::query()
            ->select(['id', 'order_number', 'customer_id', 'delivery_governorate_id', 'total_amount', 'delivery_address', 'status', 'created_at'])
            ->with([
                'customer:id,name,phone,email',
                'governorate:id,name_ar,name_en',
                'orderStores:id,order_id,store_id,subtotal,status,delivered_at,customer_confirmed_at,notes',
                'orderStores.store:id,company_name',
                'orderStores.items:id,order_store_id,store_product_id,unit_price,quantity,total_price',
                'orderStores.items.storeProduct:id,master_product_id',
                'orderStores.items.storeProduct.masterProduct:id,title',
            ])
            ->latest()
            ->get();

        return $this->ok($orders, 'Orders retrieved successfully');
    }

    public function updateOrderStatus(Request $request, string $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
        ]);

        $model = Order::query()
            ->where('order_number', $order)
            ->when(ctype_digit($order), fn ($q) => $q->orWhere('id', (int) $order))
            ->firstOrFail();

        DB::transaction(function () use ($model, $data) {
            $model->status = $data['status'];
            $model->save();

            if (in_array($data['status'], ['completed', 'cancelled'], true)) {
                OrderStore::where('order_id', $model->id)->update(['status' => $data['status']]);
            }
        });

        return response()->json(['success' => true, 'status' => 'success', 'order' => $model, 'data' => $model]);
    }

    // ===================== مدفوعات العملاء =====================

    public function payments(): JsonResponse
    {
        return $this->ok(OrderPayment::with(['order.customer:id,name,phone,email', 'walletProvider'])->latest()->get());
    }

    public function updatePaymentStatus(Request $request, OrderPayment $payment): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'verified', 'rejected'])],
        ]);

        $payment->status = $data['status'];
        if ($data['status'] === 'verified') {
            $payment->verified_at = now();
            $payment->verified_by = $request->user()->id;
        }
        $payment->save();

        return $this->ok($payment);
    }

    // ===================== مستحقات الموردين =====================

    public function payouts(): JsonResponse
    {
        return $this->ok(StorePayout::with(['orderStore.store', 'orderStore.order', 'userWallet.walletProvider'])->latest()->get());
    }

    public function updatePayoutStatus(Request $request, StorePayout $payout): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'completed', 'failed'])],
            'transfer_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $payout->status = $data['status'];
        if (! empty($data['transfer_reference'])) {
            $payout->transfer_reference = $data['transfer_reference'];
        }
        if ($data['status'] === 'completed') {
            $payout->paid_at = now();
        }
        $payout->save();

        return $this->ok($payout);
    }

    // ===================== الملخص المالي =====================

    public function financeStats(): JsonResponse
    {
        $row = DB::selectOne("
            select
                (select coalesce(sum(amount), 0) from order_payments where status = 'verified') as total_payments,
                (select coalesce(sum(amount), 0) from order_payments where status = 'pending') as pending_payments,
                (select coalesce(sum(net_amount), 0) from store_payouts where status = 'completed') as total_payouts,
                (select coalesce(sum(net_amount), 0) from store_payouts where status = 'pending') as pending_payouts,
                (select coalesce(sum(commission_amount), 0) from store_payouts where status = 'completed') as total_commissions,
                (select count(*) from wallet_providers) as providers_count
        ");

        $data = array_map('floatval', (array) $row);
        $data['providers_count'] = (int) $data['providers_count'];

        return $this->ok($data);
    }

    // ===================== الإعدادات المالية =====================

    public function settings(): JsonResponse
    {
        return $this->ok(PlatformSetting::first());
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'currency' => ['sometimes', 'string', 'max:10'],
            'platform_name' => ['sometimes', 'string', 'max:255'],
            'support_phone' => ['sometimes', 'string', 'max:30'],
            'support_email' => ['sometimes', 'email', 'max:255'],
            'is_maintenance_mode' => ['sometimes', 'boolean'],
        ]);

        $settings = PlatformSetting::first() ?? (new PlatformSetting())->forceFill([
            'platform_name' => 'سولار هاب - SolarHub',
            'support_phone' => '777000000',
            'support_email' => 'support@solarhub.com',
            'logo_path' => 'settings/logo.png',
        ]);
        $settings->forceFill($data)->save();

        return $this->ok($settings);
    }

    // ===================== مزودو المحافظ =====================

    public function walletProviders(): JsonResponse
    {
        $providers = WalletProvider::withCount(['userWallets', 'orderPayments'])->orderBy('id')->get()
            ->each(fn ($p) => $this->withLogoUrl($p));

        return $this->ok($providers);
    }

    public function storeWalletProvider(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'logo_base64' => ['nullable', 'string'],
            'logo_filename' => ['nullable', 'string', 'max:255'],
        ]);

        $code = strtoupper(trim($data['code']));
        if (WalletProvider::where('code', $code)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'رمز المحفظة مستخدم بالفعل: ' . $code], 422);
        }

        $provider = WalletProvider::create([
            'code' => $code,
            'name_ar' => trim($data['name_ar']),
            'name_en' => ($data['name_en'] ?? null) ?: null,
            'is_active' => $data['is_active'] ?? true,
            'logo_path' => $this->uploadLogo($data) ?? (($data['logo_path'] ?? null) ?: null),
        ]);

        return $this->ok($this->withLogoUrl($provider), null, 201);
    }

    public function updateWalletProvider(Request $request, WalletProvider $walletProvider): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:50'],
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'logo_path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'logo_base64' => ['nullable', 'string'],
            'logo_filename' => ['nullable', 'string', 'max:255'],
        ]);

        $update = collect($data)->only(['name_ar', 'name_en', 'is_active', 'logo_path'])->all();
        if (isset($data['code'])) {
            $update['code'] = strtoupper(trim($data['code']));
        }

        if ($newPath = $this->uploadLogo($data)) {
            if ($walletProvider->logo_path) {
                $this->storage->deletePublic($walletProvider->logo_path);
            }
            $update['logo_path'] = $newPath;
        }

        $walletProvider->update($update);

        return $this->ok($this->withLogoUrl($walletProvider));
    }

    public function destroyWalletProvider(WalletProvider $walletProvider): JsonResponse
    {
        if ($walletProvider->userWallets()->exists() || $walletProvider->orderPayments()->exists()) {
            $walletProvider->update(['is_active' => false]);

            return $this->ok(
                $this->withLogoUrl($walletProvider),
                'المحفظة مرتبطة بمعاملات سابقة، تم تعطيلها بدلاً من الحذف للحفاظ على سلامة القيود المالية.'
            );
        }

        if ($walletProvider->logo_path) {
            $this->storage->deletePublic($walletProvider->logo_path);
        }
        $walletProvider->delete();

        return response()->json(['status' => 'success', 'message' => 'تم حذف مزود المحفظة بنجاح']);
    }

    // ===================== مساعدات =====================

    private function uploadLogo(array $data): ?string
    {
        if (empty($data['logo_base64'])) {
            return null;
        }

        $binary = base64_decode($data['logo_base64'], true);
        abort_if($binary === false, 422, 'صيغة الشعار غير صالحة');

        $ext = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($data['logo_filename'] ?? 'logo.png', PATHINFO_EXTENSION))) ?: 'png';
        abort_unless(in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'], true), 422, 'نوع ملف الشعار غير مدعوم');

        $path = 'wallet-providers/' . Str::uuid() . '.' . $ext;
        Storage::disk('supabase_public')->put($path, $binary);

        return $path;
    }

    private function withLogoUrl(WalletProvider $provider): WalletProvider
    {
        $provider->logo_url = $provider->logo_path ? $this->storage->publicUrl($provider->logo_path) : null;

        return $provider;
    }

    private function ok(mixed $data, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json(array_filter([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], fn ($v) => $v !== null) + ['data' => $data], $status);
    }
}
