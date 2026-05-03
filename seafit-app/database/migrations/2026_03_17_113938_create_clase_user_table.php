<?php

/**
 * Migración de la tabla clase_user para reservas.
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
        // Aplica los cambios de esta migración.
        Schema::create('clase_user', function (Blueprint $table) {
            $table->id();

            // Si se borra el usuario, se borran sus reservas.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Si se borra la clase, se borran sus reservas.
            $table->foreignId('clase_id')->constrained()->onDelete('cascade');

            // Fecha de creación y actualización de la reserva.
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla pivote.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        Schema::dropIfExists('clase_user');
    }
};


