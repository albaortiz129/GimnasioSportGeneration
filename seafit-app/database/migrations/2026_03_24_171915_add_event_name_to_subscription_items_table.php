<?php

/**
 * Migracion que agrega meter_event_name a subscription_items.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aplica cambios de esta migracion.
        if (!Schema::hasTable('subscription_items')) {
            return;
        }

        if (!Schema::hasColumn('subscription_items', 'meter_event_name')) {
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->string('meter_event_name')->nullable()->after('quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        if (!Schema::hasTable('subscription_items')) {
            return;
        }

        if (Schema::hasColumn('subscription_items', 'meter_event_name')) {
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->dropColumn('meter_event_name');
            });
        }
    }
};


