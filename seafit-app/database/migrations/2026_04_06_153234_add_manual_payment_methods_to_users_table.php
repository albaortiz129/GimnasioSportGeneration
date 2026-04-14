<?php

/**
 * Anade columna JSON para metodos de pago manuales.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agrega la columna en users.
     */
    public function up(): void
    {
        // Aplica cambios de esta migracion.
        Schema::table('users', function (Blueprint $table) {
            $table->json('manual_payment_methods')->nullable()->after('metodo_pago');
        });
    }

    /**
     * Elimina la columna agregada.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('manual_payment_methods');
        });
    }
};

