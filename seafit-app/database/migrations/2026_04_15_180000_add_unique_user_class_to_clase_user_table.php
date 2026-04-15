<?php

/**
 * Asegura que un usuario no pueda reservar dos veces la misma clase.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agrega indice unico en tabla pivote clase_user.
     */
    public function up(): void
    {
        if (!Schema::hasTable('clase_user')) {
            return;
        }

        // Limpia duplicados historicos conservando el primer registro.
        DB::statement(
            "DELETE cu1
             FROM clase_user cu1
             INNER JOIN clase_user cu2
                ON cu1.user_id = cu2.user_id
               AND cu1.clase_id = cu2.clase_id
               AND cu1.id > cu2.id"
        );

        $indexName = 'clase_user_user_id_clase_id_unique';
        $exists = DB::selectOne(
            "SELECT COUNT(*) AS total
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = 'clase_user'
               AND index_name = ?",
            [$indexName]
        );

        if ((int) ($exists->total ?? 0) === 0) {
            Schema::table('clase_user', function (Blueprint $table) use ($indexName) {
                $table->unique(['user_id', 'clase_id'], $indexName);
            });
        }
    }

    /**
     * Elimina indice unico agregado.
     */
    public function down(): void
    {
        if (!Schema::hasTable('clase_user')) {
            return;
        }

        $indexName = 'clase_user_user_id_clase_id_unique';
        $exists = DB::selectOne(
            "SELECT COUNT(*) AS total
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = 'clase_user'
               AND index_name = ?",
            [$indexName]
        );

        if ((int) ($exists->total ?? 0) > 0) {
            Schema::table('clase_user', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }
};
