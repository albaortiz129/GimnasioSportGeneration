<?php

/**
 * Migracion de la tabla clases para agenda deportiva.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clases', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');          // Ej: Yoga, HIIT, Spinning
            $table->string('instructor');      // Nombre del monitor
            $table->string('sala');            // Sala 1, Sala Fitness, etc.
            $table->time('hora_inicio');       // 18:30:00
            $table->string('dia_semana');      // Lunes, Martes...
            $table->integer('capacidad_max');  // Número de personas
            $table->text('descripcion')->nullable(); // Breve detalle de la clase
            $table->string('imagen')->nullable();    // Ruta de una imagen si quieres ponerla
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clases');
    }
};
