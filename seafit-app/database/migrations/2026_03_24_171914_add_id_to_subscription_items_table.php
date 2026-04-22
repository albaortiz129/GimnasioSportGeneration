<?php

/**
 * Migración que agrega meter_id a subscription_items.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        // Aplica los cambios de esta migración.
        if (!Schema::hasTable('subscription_items')) {
            return;
        }

        if (!Schema::hasColumn('subscription_items', 'meter_id')) {
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->string('meter_id')->nullable()->after('stripe_price');
            });
        }
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        if (!Schema::hasTable('subscription_items')) {
            return;
        }

        if (Schema::hasColumn('subscription_items', 'meter_id')) {
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->dropColumn('meter_id');
            });
        }
    }
};



