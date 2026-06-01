<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipos')) {
            return;
        }

        Schema::create('equipos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('tipo', 50);
            $table->integer('tipo_servicio_id')->nullable();
            $table->string('tipo_servicio_texto', 100)->nullable();
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->string('serie', 100);
            $table->string('contrasena_equipo', 100)->nullable();
            $table->text('falla')->nullable();
            $table->text('observacion')->nullable();
            $table->date('fecha_facturacion')->nullable();
            $table->string('producto_inventario_codigo', 30)->nullable();

            $table->index('tipo_servicio_id', 'fk_equipos_tipo_servicio');
            $table->index('producto_inventario_codigo', 'idx_eq_prod_inv');
            $table->foreign('tipo_servicio_id', 'fk_equipos_tipo_servicio')->references('id')->on('tiposservicio')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
