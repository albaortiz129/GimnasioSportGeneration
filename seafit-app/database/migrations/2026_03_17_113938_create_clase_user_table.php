<?php

/**
 * Migracion de la tabla pivote clase_user para reservas.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea la tabla que relaciona usuarios y clases.
     */
    public function up(): void
    {
        Schema::create('clase_user', function (Blueprint $table) {
            $table->id();

            // Si se borra el usuario, se borran sus reservas.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Si se borra la clase, se borran sus reservas.
            $table->foreignId('clase_id')->constrained()->onDelete('cascade');

            // Fecha de creacion y actualizacion de la reserva.
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla pivote.
     */
    public function down(): void
    {
        Schema::dropIfExists('clase_user');
    }
};
