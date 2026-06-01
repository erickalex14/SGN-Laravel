<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ordenes')) {
            return;
        }

        Schema::create('ordenes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nro_orden', 20)->nullable()->unique('nro_orden');
            $table->string('nro_factura', 20)->nullable();
            $table->string('nro_factura_2', 17)->nullable();
            $table->enum('motivo_ingreso', [
                'Validacion de Garantia',
                'Servicio Cliente Externo',
                'Servicio Tecnico',
                'Servicios a Empresas'
            ])->default('Servicio Cliente Externo');
            $table->char('nro_sucursal_cliente', 5)->nullable();
            $table->integer('cliente_id');
            $table->integer('equipo_id');
            $table->integer('tecnico_id');
            $table->integer('sucursal_id');
            $table->datetime('fecha_de_ingreso')->nullable()->useCurrent();
            $table->string('estado_orden', 50)->default('Pendiente');
            $table->string('estado_repuesto', 50)->default('No requerido');
            $table->string('estado_garantia', 20)->nullable();
            $table->enum('garantia_tipo', ['propia', 'externa'])->nullable();
            $table->string('garantia_cas', 100)->nullable();
            $table->unsignedInteger('cas_id')->nullable();
            $table->date('cas_fecha_envio')->nullable();
            $table->date('cas_fecha_retorno')->nullable();
            $table->string('cas_numero_caso', 60)->nullable();
            $table->integer('ingresado_por')->nullable();
            $table->date('fecha_prometido')->nullable();
            $table->integer('modificado_por')->nullable();
            $table->datetime('fecha_modificacion')->nullable();
            $table->datetime('fecha_entrega')->nullable();
            $table->datetime('fecha_finalizacion')->nullable();
            $table->integer('valor_estandar_id')->nullable();
            $table->integer('repuesto_inventario_id')->nullable()->comment('FK opcional a ProductosInventario.id — repuesto asignado a la orden');
            $table->text('observacion')->nullable();
            $table->unsignedInteger('tipo_servicio_id')->nullable();
            $table->string('tipo_servicio_texto', 255)->nullable();
            $table->date('fecha_facturacion')->nullable();

            $table->index('cliente_id', 'cliente_id');
            $table->index('equipo_id', 'equipo_id');
            $table->index('tecnico_id', 'tecnico_id');
            $table->index('sucursal_id', 'sucursal_id');
            $table->index('ingresado_por', 'fk_ingresado_por');
            $table->index('modificado_por', 'fk_modificado_por');
            $table->index('valor_estandar_id', 'fk_ordenes_valor_estandar');
            $table->index('repuesto_inventario_id', 'idx_repuesto_inventario_id');

            $table->foreign('valor_estandar_id', 'fk_ordenes_valor_estandar')->references('id')->on('preciosestandar')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('cliente_id', 'ordenes_ibfk_1')->references('id')->on('clientes');
            $table->foreign('equipo_id', 'ordenes_ibfk_2')->references('id')->on('equipos');
            $table->foreign('tecnico_id', 'ordenes_ibfk_3')->references('id')->on('usuarios');
            $table->foreign('sucursal_id', 'ordenes_ibfk_4')->references('id')->on('sucursales');
            $table->foreign('ingresado_por', 'ordenes_ibfk_5')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('modificado_por', 'ordenes_ibfk_6')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
