<?php

/**
 * Crea la tabla de códigos de descuento gestionados por admin.
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
        // Si la tabla ya existe, no falla y marca migración como ejecutada.
        if (Schema::hasTable('discount_codes')) {
            return;
        }

        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // Ej: SEAFIT20
            $table->enum('type', ['percent', 'fixed']); // percent=% | fixed=importe
            $table->decimal('value', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable(); // null = sin límite
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('one_use_per_user')->default(true);
            $table->string('stripe_coupon_id')->nullable(); // Cupón real de Stripe
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla de códigos.
     */
    public function down(): void
    {
        if (Schema::hasTable('discount_codes')) {
            Schema::drop('discount_codes');
        }
    }
};


