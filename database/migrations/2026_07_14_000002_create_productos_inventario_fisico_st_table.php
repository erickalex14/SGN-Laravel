<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos_inventario_fisico_st', function (Blueprint $table) {
            $table->id();
            
            // Relación con ordenes de empresas (coincide con INT UNSIGNED)
            $table->unsignedInteger('orden_empresa_id')->nullable();
            
            // Relación con sucursales (coincide con INT de sucursales.id)
            $table->integer('sucursal_id')->nullable();
            
            $table->string('codigo', 100);
            $table->string('serie', 100);
            $table->string('nombre', 255);
            $table->string('estado', 50)->default('Tienda'); // 'Tienda', 'Incinerox', 'Outlet'
            $table->text('detalle_outlet')->nullable();
            
            $table->timestamps();

            // Índices para optimización de reportes y búsquedas
            $table->index('orden_empresa_id');
            $table->index('sucursal_id');
            $table->index('estado');
            $table->index('serie');
            
            // Llave foránea para integridad de datos
            $table->foreign('orden_empresa_id')
                ->references('id')
                ->on('ordenesempresas')
                ->onDelete('cascade');

            $table->foreign('sucursal_id')
                ->references('id')
                ->on('sucursales')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos_inventario_fisico_st');
    }
};
