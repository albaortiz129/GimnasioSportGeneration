<?php

/**
 * Crea la tabla de codigos de descuento gestionados por admin.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea la estructura de discount_codes.
     */
    public function up(): void
    {
        // Aplica cambios de esta migracion.
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // Ej: SEAFIT20
            $table->enum('type', ['percent', 'fixed']); // percent=% | fixed=importe
            $table->decimal('value', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable(); // null = sin limite
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('one_use_per_user')->default(true);
            $table->string('stripe_coupon_id')->nullable(); // Cupon real de Stripe
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla de codigos.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        Schema::dropIfExists('discount_codes');
    }
};

