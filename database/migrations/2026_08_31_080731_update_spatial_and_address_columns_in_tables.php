<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. تفعيل PostGIS
        |--------------------------------------------------------------------------
        */

        DB::statement(
            'CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA extensions;'
        );

        DB::statement(
            'SET search_path TO public, extensions;'
        );


        /*
        |--------------------------------------------------------------------------
        | 2. Users
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('users', 'default_coordinates')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('default_coordinates');
            });
        }

        DB::statement('
            ALTER TABLE users
            ADD COLUMN default_coordinates
            extensions.geography(Point, 4326) NULL
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_users_coordinates
            ON users
            USING GIST (default_coordinates)
        ');


        /*
        |--------------------------------------------------------------------------
        | 3. Stores
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('stores', 'location_coordinates')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropColumn('location_coordinates');
            });
        }

        DB::statement('
            ALTER TABLE stores
            ADD COLUMN location_coordinates
            extensions.geography(Point, 4326) NULL
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_stores_coordinates
            ON stores
            USING GIST (location_coordinates)
        ');


        /*
        |--------------------------------------------------------------------------
        | 4. Orders
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('orders', 'delivery_coordinates')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('delivery_coordinates');
            });
        }

        DB::statement('
            ALTER TABLE orders
            ADD COLUMN delivery_coordinates
            extensions.geography(Point, 4326) NULL
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_orders_coordinates
            ON orders
            USING GIST (delivery_coordinates)
        ');


        /*
        |--------------------------------------------------------------------------
        | 5. Service Requests
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('service_requests', 'location_coordinates')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropColumn('location_coordinates');
            });
        }

        DB::statement('
            ALTER TABLE service_requests
            ADD COLUMN location_coordinates
            extensions.geography(Point, 4326) NULL
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_service_requests_coordinates
            ON service_requests
            USING GIST (location_coordinates)
        ');


        /*
        |--------------------------------------------------------------------------
        | 6. Portfolio Items
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('portfolio_items', 'location_coordinates')) {
            Schema::table('portfolio_items', function (Blueprint $table) {
                $table->dropColumn('location_coordinates');
            });
        }

        DB::statement('
            ALTER TABLE portfolio_items
            ADD COLUMN location_coordinates
            extensions.geography(Point, 4326) NULL
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_portfolio_items_coordinates
            ON portfolio_items
            USING GIST (location_coordinates)
        ');
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Portfolio Items
        |--------------------------------------------------------------------------
        */

        DB::statement(
            'DROP INDEX IF EXISTS idx_portfolio_items_coordinates'
        );

        DB::statement(
            'ALTER TABLE portfolio_items
             DROP COLUMN IF EXISTS location_coordinates'
        );


        /*
        |--------------------------------------------------------------------------
        | Service Requests
        |--------------------------------------------------------------------------
        */

        DB::statement(
            'DROP INDEX IF EXISTS idx_service_requests_coordinates'
        );

        DB::statement(
            'ALTER TABLE service_requests
             DROP COLUMN IF EXISTS location_coordinates'
        );


        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        DB::statement(
            'DROP INDEX IF EXISTS idx_orders_coordinates'
        );

        DB::statement(
            'ALTER TABLE orders
             DROP COLUMN IF EXISTS delivery_coordinates'
        );


        /*
        |--------------------------------------------------------------------------
        | Stores
        |--------------------------------------------------------------------------
        */

        DB::statement(
            'DROP INDEX IF EXISTS idx_stores_coordinates'
        );

        DB::statement(
            'ALTER TABLE stores
             DROP COLUMN IF EXISTS location_coordinates'
        );


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        DB::statement(
            'DROP INDEX IF EXISTS idx_users_coordinates'
        );

        DB::statement(
            'ALTER TABLE users
             DROP COLUMN IF EXISTS default_coordinates'
        );
    }
};