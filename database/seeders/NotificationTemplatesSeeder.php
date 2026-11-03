<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            NotificationTemplate::query()->updateOrCreate(
                ['code' => $template['code']],
                $template + ['is_active' => true]
            );
        }
    }

    private function templates(): array
    {
        return [
            $this->template('account_created', 'account', 'مرحبًا بك في SolarHub', 'تم إنشاء حساب {{user_name}} بنوع {{account_type}} بنجاح.', ['user_name', 'account_type']),
            $this->template('professional_application_submitted_admin', 'approval', 'طلب اعتماد جديد', 'قدّم {{user_name}} طلب اعتماد حساب {{account_type}}.', ['user_name', 'account_type']),
            $this->template('engineer_application_approved', 'approval', 'تم اعتماد حساب المهندس', 'تمت الموافقة على طلب اعتمادك يا {{user_name}}.', ['user_name']),
            $this->template('engineer_application_rejected', 'approval', 'رُفض طلب اعتماد المهندس', 'تعذر اعتماد حسابك يا {{user_name}}. السبب: {{rejection_reason}}.', ['user_name', 'rejection_reason']),
            $this->template('supplier_application_approved', 'approval', 'تم اعتماد حساب التاجر', 'تمت الموافقة على اعتماد متجر {{store_name}}.', ['store_name']),
            $this->template('supplier_application_rejected', 'approval', 'رُفض طلب اعتماد التاجر', 'تعذر اعتماد متجر {{store_name}}. السبب: {{rejection_reason}}.', ['store_name', 'rejection_reason']),
            $this->template('order_created_customer', 'order', 'تم إنشاء طلب الشراء', 'تم إنشاء طلبك رقم {{order_number}} بإجمالي {{amount}}.', ['order_number', 'amount']),
            $this->template('order_created_store', 'order', 'طلب شراء جديد', 'وصل إلى متجر {{store_name}} طلب رقم {{order_number}} بإجمالي {{amount}}.', ['store_name', 'order_number', 'amount']),
            $this->template('order_status_changed_customer', 'order', 'تحديث حالة الطلب', 'تغيّرت حالة طلبك رقم {{order_number}} لدى {{store_name}} إلى {{status}}.', ['order_number', 'store_name', 'status']),
            $this->template('order_cancelled_store', 'order', 'أُلغي طلب شراء', 'ألغى العميل {{customer_name}} الطلب رقم {{order_number}}.', ['customer_name', 'order_number']),
            $this->template('delivery_confirmed_store', 'order', 'تأكيد استلام الطلب', 'أكد العميل استلام الطلب رقم {{order_number}} من متجر {{store_name}}.', ['order_number', 'store_name']),
            $this->template('service_request_submitted_engineer', 'service_request', 'طلب خدمة جديد', 'نُشر طلب خدمة رقم {{service_request_id}} من {{customer_name}} ضمن {{service_type}}.', ['service_request_id', 'customer_name', 'service_type']),
            $this->template('offer_submitted_customer', 'offer', 'عرض هندسي جديد', 'أرسل المهندس {{engineer_name}} عرضًا لطلب الخدمة رقم {{service_request_id}} بمبلغ {{amount}}.', ['engineer_name', 'service_request_id', 'amount']),
            $this->template('offer_accepted_engineer', 'offer', 'تم قبول عرضك', 'قبل العميل عرضك رقم {{offer_id}} لطلب الخدمة رقم {{service_request_id}}.', ['offer_id', 'service_request_id']),
            $this->template('offer_rejected_engineer', 'offer', 'تم رفض عرضك', 'لم يُقبل عرضك رقم {{offer_id}} لطلب الخدمة رقم {{service_request_id}}.', ['offer_id', 'service_request_id']),
            $this->template('quote_requested_store', 'quote', 'طلب تسعير جديد', 'طلب {{customer_name}} تسعير المنتج {{product_name}} بكمية {{quantity}}.', ['customer_name', 'product_name', 'quantity']),
            $this->template('quote_responded_customer', 'quote', 'رد جديد على طلب التسعير', 'رد متجر {{store_name}} على طلب التسعير رقم {{quote_id}} بإجمالي {{amount}}.', ['store_name', 'quote_id', 'amount']),
            $this->template('quote_accepted_store', 'quote', 'قُبل عرض التسعير', 'قبل العميل عرض التسعير رقم {{quote_id}}.', ['quote_id']),
            $this->template('quote_rejected_store', 'quote', 'رُفض طلب التسعير', 'رفض العميل طلب التسعير رقم {{quote_id}}.', ['quote_id']),
            $this->template('payment_submitted_admin', 'payment', 'إثبات دفع جديد', 'قدّم {{customer_name}} إثبات دفع للطلب {{order_number}} بمبلغ {{amount}}.', ['customer_name', 'order_number', 'amount']),
            $this->template('payment_verified_customer', 'payment', 'تم التحقق من الدفع', 'تم التحقق من دفعتك للطلب {{order_number}} بمبلغ {{amount}}.', ['order_number', 'amount']),
            $this->template('payment_rejected_customer', 'payment', 'رُفض إثبات الدفع', 'تم رفض إثبات الدفع للطلب {{order_number}} بمبلغ {{amount}}.', ['order_number', 'amount']),
            $this->template('payout_created_store', 'payout', 'تسوية متجر جديدة', 'أُنشئت تسوية للمتجر {{store_name}} عن الطلب {{order_number}} بصافي {{amount}}.', ['store_name', 'order_number', 'amount']),
            $this->template('payout_status_changed_store', 'payout', 'تحديث حالة التسوية', 'تغيّرت حالة تسوية المتجر {{store_name}} للطلب {{order_number}} إلى {{status}}.', ['store_name', 'order_number', 'status']),
        ];
    }

    private function template(
        string $code,
        string $type,
        string $title,
        string $body,
        array $variables
    ): array {
        return compact('code', 'type', 'title', 'body', 'variables');
    }
}
