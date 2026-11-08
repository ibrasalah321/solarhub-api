<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_stores_approval_status ON stores (approval_status);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_engineer_profile_approval_status ON engineer_profile (approval_status);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_status ON orders (status);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_service_requests_status ON service_requests (status);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_store_ratings_lookup ON store_ratings (store_id, is_approved);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_engineer_ratings_lookup ON engineer_ratings (engineer_id, is_approved);');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_stores_approval_status;');
            DB::statement('DROP INDEX IF EXISTS idx_engineer_profile_approval_status;');
            DB::statement('DROP INDEX IF EXISTS idx_orders_status;');
            DB::statement('DROP INDEX IF EXISTS idx_service_requests_status;');
            DB::statement('DROP INDEX IF EXISTS idx_store_ratings_lookup;');
            DB::statement('DROP INDEX IF EXISTS idx_engineer_ratings_lookup;');
        }
    }
};
