<?php

/**
 * Añade campos de facturación y estado de pago en users.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agrega columnas de facturación manual y estado.
     */
    public function up(): void
    {
        // Aplica los cambios de esta migración.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('is_admin');
            $table->string('payment_status')->default('pendiente')->after('metodo_pago'); // al_dia | pendiente | impagado
            $table->date('next_payment_at')->nullable()->after('payment_status');
            $table->timestamp('last_manual_payment_at')->nullable()->after('next_payment_at');
            $table->string('manual_payment_note')->nullable()->after('last_manual_payment_at');
        });
    }

    /**
     * Elimina las columnas anadidas.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'must_change_password',
                'payment_status',
                'next_payment_at',
                'last_manual_payment_at',
                'manual_payment_note',
            ]);
        });
    }
};


