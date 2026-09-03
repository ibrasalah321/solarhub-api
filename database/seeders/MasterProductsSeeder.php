<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterProductsSeeder extends Seeder
{
    public function run(): void
    {
        $jinkoId = DB::table('brands')->where('name', 'Jinko Solar')->value('id');
        $deyeId  = DB::table('brands')->where('name', 'Deye')->value('id');
        $pylonId = DB::table('brands')->where('name', 'Pylontech')->value('id');

        $panelCatId   = DB::table('categories')->where('slug', 'solar-panels')->value('id');
        $inverterCatId = DB::table('categories')->where('slug', 'inverters')->value('id');
        $batteryCatId = DB::table('categories')->where('slug', 'batteries')->value('id');

        // 1. لوح شمسي: Jinko Tiger Neo
        $p1 = DB::table('master_products')->insertGetId([
            'category_id'    => $panelCatId,
            'brand_id'       => $jinkoId,
            'title'          => 'ألواح جينكو 620 وات Tiger Neo',
            'model_number'   => 'JKM620N-78HL4-BDV',
            'description'    => 'لوح شمسي N-Type عالي الكفاءة ومقاوم للظروف الجوية القاسية في اليمن',
            'main_image'     => 'products/jinko_620w.png',
            'datasheet_file' => 'datasheets/jinko_620w.pdf',
            'is_active'      => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('product_specifications')->insert([
            ['product_id' => $p1, 'name' => 'القدرة القصوى', 'value' => '620', 'unit' => 'W', 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $p1, 'name' => 'نوع الخلية', 'value' => 'N-Type Monocrystalline', 'unit' => null, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $p1, 'name' => 'عدد البازبار', 'value' => '16', 'unit' => 'BB', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. إنفرتر: Deye Off-Grid Inverter
        $p2 = DB::table('master_products')->insertGetId([
            'category_id'    => $inverterCatId,
            'brand_id'       => $deyeId,
            'title'          => 'انفرتر دايا 6 ك اوف جرايد',
            'model_number'   => 'SUN-6K-SG04LP1-EU',
            'description'    => 'إنفرتر هجين عالي الاعتمادية يدعم البطاريات وشبكة الكهرباء والمولد',
            'main_image'     => 'products/deye_6kw.png',
            'datasheet_file' => 'datasheets/deye_6kw.pdf',
            'is_active'      => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('product_specifications')->insert([
            ['product_id' => $p2, 'name' => 'القدرة الاسمية', 'value' => '6', 'unit' => 'kW', 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $p2, 'name' => 'جهد البطارية', 'value' => '48', 'unit' => 'V', 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $p2, 'name' => 'أقصى تيار شحن', 'value' => '135', 'unit' => 'A', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. بطارية: Pylontech US5000
        $p3 = DB::table('master_products')->insertGetId([
            'category_id'    => $batteryCatId,
            'brand_id'       => $pylonId,
            'title'          => 'بطارية بايلون تيك ليثيوم US5000',
            'model_number'   => 'US5000',
            'description'    => 'بطارية ليثيوم فوسفات الحديد LiFePO4 طويلة العمر بنظام إدارة ذكي BMS',
            'main_image'     => 'products/pylontech_us5000.png',
            'datasheet_file' => 'datasheets/pylontech_us5000.pdf',
            'is_active'      => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('product_specifications')->insert([
            ['product_id' => $p3, 'name' => 'السعة التخزينية', 'value' => '4.8', 'unit' => 'kWh', 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $p3, 'name' => 'الجهد الاسمي', 'value' => '48', 'unit' => 'V', 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $p3, 'name' => 'سعة الأمبير', 'value' => '100', 'unit' => 'Ah', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
