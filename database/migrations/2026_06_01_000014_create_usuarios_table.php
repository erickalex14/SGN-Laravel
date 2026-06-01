<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuarios')) {
            return;
        }

        Schema::create('usuarios', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->char('usuario', 10)->unique('usuario');
            $table->string('clave', 12);
            $table->string('nombre_tecnico', 100);
            $table->string('telefono', 15)->nullable();
            $table->string('correo_tec', 100)->nullable();
            $table->tinyInteger('acceso_nc')->default(0);
            $table->integer('rol_id');
            $table->integer('sucursal_id');
            $table->tinyInteger('activo')->default(1);
            $table->string('session_token', 64)->nullable();
            $table->unsignedInteger('grupo_id')->nullable();

            $table->index('rol_id', 'rol_id');
            $table->index('sucursal_id', 'sucursal_id');

            $table->foreign('rol_id', 'usuarios_ibfk_1')->references('id')->on('roles');
            $table->foreign('sucursal_id', 'usuarios_ibfk_2')->references('id')->on('sucursales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
