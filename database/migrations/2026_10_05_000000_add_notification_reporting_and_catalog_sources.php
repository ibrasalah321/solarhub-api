<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('sender_id')
                ->nullable()
                ->after('notifiable_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('sender_type', 32)
                ->default('legacy_unknown')
                ->after('sender_id');
            $table->string('template_code', 100)
                ->nullable()
                ->after('type');
            $table->uuid('event_id')->nullable()->after('template_code');
            $table->string('event_key', 191)->nullable()->after('event_id');
            $table->char('dedupe_key', 64)->nullable()->unique();
            $table->index(['sender_id', 'created_at']);
            $table->index(['notifiable_type', 'notifiable_id', 'created_at'], 'notifications_recipient_created_index');
            $table->index('event_id');
        });

        Schema::table('master_products', function (Blueprint $table) {
            $table->string('source_key', 64)->nullable()->unique();
            $table->string('source_name')->nullable();
            $table->unsignedSmallInteger('source_page')->nullable();
            $table->text('source_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('master_products', function (Blueprint $table) {
            $table->dropUnique(['source_key']);
            $table->dropColumn([
                'source_key',
                'source_name',
                'source_page',
                'source_notes',
            ]);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropUnique(['dedupe_key']);
            $table->dropIndex('notifications_sender_id_created_at_index');
            $table->dropIndex('notifications_recipient_created_index');
            $table->dropIndex(['event_id']);
            $table->dropColumn([
                'sender_id',
                'sender_type',
                'template_code',
                'event_id',
                'event_key',
                'dedupe_key',
            ]);
        });
    }
};