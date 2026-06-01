<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permisosgrupo')) {
            return;
        }

        Schema::create('permisosgrupo', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grupo_id');
            $table->string('modulo', 60);
            $table->enum('accion', ['ver', 'crear', 'editar', 'eliminar']);
            $table->tinyInteger('permitido')->default(0);

            $table->unique(['grupo_id', 'modulo', 'accion'], 'uq_permiso');
            $table->index(['grupo_id', 'modulo', 'accion'], 'idx_permisos_grupo_modulo');
            $table->foreign('grupo_id', 'fk_pg_grupo')->references('id')->on('gruposacceso')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisosgrupo');
    }
};
