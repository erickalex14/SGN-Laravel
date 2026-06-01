<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preordenes')) {
            return;
        }

        Schema::create('preordenes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('orden_id')->nullable();
            $table->datetime('fecha_registro')->nullable()->useCurrent();
            $table->string('nro_preorden', 20)->unique('nro_preorden');
            $table->unsignedInteger('sucursal_id');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('identificacion', 13)->nullable();
            $table->string('telefono', 15);
            $table->string('correo', 150);
            $table->string('nro_factura', 20)->nullable();
            $table->string('codigo_producto', 50)->nullable();
            $table->string('desc_producto', 255)->nullable();
            $table->string('marca_producto', 100)->nullable();
            $table->string('tipo_producto', 100)->nullable();
            $table->text('detalle_equipo')->nullable();
            $table->string('foto_1', 255)->nullable();
            $table->string('foto_2', 255)->nullable();
            $table->string('foto_3', 255)->nullable();
            $table->string('foto_4', 255)->nullable();
            $table->enum('estado', ['pendiente', 'atendida', 'cancelada'])->default('pendiente');
            $table->datetime('created_at')->nullable()->useCurrent();
            $table->unsignedSmallInteger('nro_sucursal_cliente')->nullable()->comment('FK a SucursalesCliente.id');
            $table->date('fecha_facturacion')->nullable();

            $table->index('nro_sucursal_cliente', 'idx_nro_sucursal_cliente');
            $table->index('orden_id', 'idx_preordenes_orden_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preordenes');
    }
};
