<?php

/**
 * Crea la tabla historica de usos de descuentos.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea la estructura de discount_redemptions.
     */
    public function up(): void
    {
        Schema::create('discount_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('context', 40)->default('registro');
            $table->decimal('discount_applied', 10, 2)->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->index(['discount_code_id', 'user_id']);
        });
    }

    /**
     * Elimina la tabla de usos de descuentos.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_redemptions');
    }
};
