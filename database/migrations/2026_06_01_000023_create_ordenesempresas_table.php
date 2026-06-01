<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ordenesempresas')) {
            return;
        }

        Schema::create('ordenesempresas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nro_orden', 30)->unique('nro_orden');
            $table->unsignedInteger('empresa_id');
            $table->enum('subtipo', ['Autoconsumo', 'Servicios']);
            $table->unsignedInteger('equipo_id')->nullable();
            $table->string('tipo_servicio', 255)->nullable();
            $table->string('nro_ticket', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('tecnico_id');
            $table->unsignedInteger('sucursal_id');
            $table->unsignedInteger('ingresado_por')->nullable();
            $table->date('fecha_prometido')->nullable();
            $table->string('estado', 50)->default('Abierta');
            $table->datetime('fecha_ingreso')->useCurrent();

            $table->index('empresa_id', 'idx_empresa');
            $table->index('equipo_id', 'idx_equipo');
            $table->index('tecnico_id', 'idx_tecnico');
            $table->index('sucursal_id', 'idx_sucursal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenesempresas');
    }
};
