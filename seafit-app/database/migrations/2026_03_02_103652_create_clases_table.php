<?php

/**
 * Migración de tabla clases para la agenda deportiva.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea la tabla de clases.
     */
    public function up(): void
    {
        Schema::create('clases', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('instructor');
            $table->string('sala');
            $table->time('hora_inicio');
            $table->string('dia_semana');
            $table->integer('capacidad_max'); // Plazas disponibles.
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla de clases.
     */
    public function down(): void
    {
        // Revierte los cambios aplicados en up().
        Schema::dropIfExists('clases');
    }
};


