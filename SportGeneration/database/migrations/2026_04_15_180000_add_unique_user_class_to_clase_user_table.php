<?php

/**
 * Asegura que un usuario no pueda reservar dos veces la misma clase.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agrega un índice único en tabla clase_user.
     */
    public function up(): void
    {
        if (!Schema::hasTable('clase_user')) {
            return;
        }

        // Limpia duplicados históricos conservando el primer registro.
        $idsDuplicados = DB::table('clase_user as nuevo')
            ->join('clase_user as viejo', function ($join) {
                $join->on('nuevo.user_id', '=', 'viejo.user_id')
                    ->on('nuevo.clase_id', '=', 'viejo.clase_id')
                    ->whereColumn('nuevo.id', '>', 'viejo.id');
            })
            ->select('nuevo.id')
            ->pluck('nuevo.id')
            ->unique()
            ->values();

        if ($idsDuplicados->isNotEmpty()) {
            foreach ($idsDuplicados->chunk(500) as $chunk) {
                DB::table('clase_user')->whereIn('id', $chunk->all())->delete();
            }
        }

        $indexName = 'clase_user_user_id_clase_id_unique';
        try {
            Schema::table('clase_user', function (Blueprint $table) use ($indexName) {
                $table->unique(['user_id', 'clase_id'], $indexName);
            });
        } catch (QueryException $e) {
            // Si el índice ya existe en esta base de datos, no interrumpimos el despliegue.
            if (
                str_contains(strtolower($e->getMessage()), 'already exists')
                || str_contains(strtolower($e->getMessage()), 'duplicate')
                || str_contains(strtolower($e->getMessage()), 'duplicate key name')
            ) {
                return;
            }

            throw $e;
        }
    }

    /**
     * Elimina el índice único agregado.
     */
    public function down(): void
    {
        if (!Schema::hasTable('clase_user')) {
            return;
        }

        $indexName = 'clase_user_user_id_clase_id_unique';
        try {
            Schema::table('clase_user', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (QueryException $e) {
            // Si el índice no existe, ignoramos para permitir rollback.
            if (
                str_contains(strtolower($e->getMessage()), 'no such index')
                || str_contains(strtolower($e->getMessage()), 'cannot drop')
                || str_contains(strtolower($e->getMessage()), 'doesn\'t exist')
                || str_contains(strtolower($e->getMessage()), 'check that column/key exists')
            ) {
                return;
            }

            throw $e;
        }
    }
};

