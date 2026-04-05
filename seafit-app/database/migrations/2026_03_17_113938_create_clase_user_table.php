<?php

/**
 * Migracion de tabla pivote clase_user para reservas de socios.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * TABLA PIVOTE: RELACIÓN MUCHOS A MUCHOS (Usuarios <-> Clases)
     * * Esta tabla es el "corazón" de las reservas de SeaFit.
     * Sirve para saber qué socio se ha apuntado a qué clase.
     * * Lógica:
     * - Un usuario puede reservar muchas clases.
     * - Una clase puede tener muchos usuarios apuntados.
     */
    public function up(): void
    {
        Schema::create('clase_user', function (Blueprint $table) {
            $table->id();

            // Referencia al socio (users.id)
            // onDelete('cascade') borra las reservas si el usuario se da de baja
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Referencia a la clase (clases.id)
            // onDelete('cascade') borra las reservas si la clase se cancela/elimina
            $table->foreignId('clase_id')->constrained()->onDelete('cascade');

            $table->timestamps(); // Para saber CUÁNDO se hizo la reserva
        });
    }

    /**
     * Revierte la migración (borra la tabla de reservas).
     */
    public function down(): void
    {
        Schema::dropIfExists('clase_user');
    }
};
