<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'customer_id',
        'delivery_governorate_id',
        'total_amount',
        'delivery_address',
        'status',
        'delivery_coordinates',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'delivery_governorate_id' => 'integer',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'delivery_governorate_id');
    }

    public function orderStores(): HasMany
    {
        return $this->hasMany(OrderStore::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class, 'order_id');
    }

    /**
     * ملخص الدفع للطلب (كل الطلبات مدفوعة مسبقاً؛ لا يوجد دفع عند الاستلام):
     *  - paid: مجموع الدفعات المعتمدة (verified) يغطي إجمالي الطلب
     *  - under_review: توجد دفعة قيد المراجعة (pending) ولم يكتمل الدفع
     *  - unpaid: لا دفعات معتمدة كافية ولا دفعات قيد المراجعة (بما فيها حالة رفض آخر إيصال)
     *
     * @return array{status: string, verified_total: float, pending_total: float, remaining: float, total: float, last_rejected: bool}
     */
    public function paymentSummary(): array
    {
        $payments = $this->relationLoaded('payments') ? $this->payments : $this->payments()->get();

        $total = round((float) $this->total_amount, 2);
        $verified = round((float) $payments->where('status', 'verified')->sum('amount'), 2);
        $pending = round((float) $payments->where('status', 'pending')->sum('amount'), 2);
        $latest = $payments->sortByDesc('id')->first();

        $status = match (true) {
            $total > 0 && $verified >= $total => 'paid',
            $pending > 0 => 'under_review',
            default => 'unpaid',
        };

        return [
            'status' => $status,
            'verified_total' => $verified,
            'pending_total' => $pending,
            'remaining' => max(0, round($total - $verified - $pending, 2)),
            'total' => $total,
            'last_rejected' => $latest?->status === 'rejected',
        ];
    }

    /** هل غطّت الدفعات المعتمدة إجمالي الطلب؟ شرط الشحن. */
    public function isFullyPaid(): bool
    {
        return $this->paymentSummary()['status'] === 'paid';
    }
}
