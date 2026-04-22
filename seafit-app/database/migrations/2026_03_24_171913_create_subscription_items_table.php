<?php

/**
 * Migración de items de suscripción de Cashier/Stripe.
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
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'stripe_price']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        Schema::dropIfExists('subscription_items');
    }
};



