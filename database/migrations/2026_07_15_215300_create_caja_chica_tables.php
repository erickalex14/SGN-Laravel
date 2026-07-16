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
        Schema::create('caja_chica_cabecera', function (Blueprint $table) {
            $table->id();
            $table->integer('sucursal_id'); // Matches signed int of sucursales.id
            $table->string('codigo_sucursal', 20);
            $table->string('nro_caja_chica', 50)->unique();
            $table->integer('custodio_usuario_id'); // Matches signed int of usuarios.id
            $table->string('custodio_nombre', 150);
            $table->date('fecha_creacion');
            $table->date('fecha_cierre')->nullable();
            $table->string('estado', 20)->default('Abierta'); // Abierta, Cerrada, Reembolsada
            $table->decimal('fondo_inicial', 10, 2)->default(1000.00);
            $table->timestamps();

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('custodio_usuario_id')->references('id')->on('usuarios')->onDelete('restrict');
            
            $table->index('sucursal_id');
            $table->index('estado');
        });

        Schema::create('caja_chica_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_chica_id'); // Matches id() of caja_chica_cabecera
            $table->date('fecha_comprobante');
            $table->string('nro_comprobante', 50);
            $table->string('proveedor', 150)->nullable(); // Nullable since we use beneficiary dropdown
            $table->text('descripcion');
            $table->string('tipo_gasto', 50);
            $table->decimal('subtotal_sin_iva', 10, 2)->default(0.00);
            $table->decimal('subtotal_con_iva', 10, 2)->default(0.00);
            $table->decimal('iva', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->decimal('valor_entregado', 10, 2)->default(0.00);
            $table->string('usuario_beneficiado', 150)->nullable();
            $table->decimal('vuelto_esperado', 10, 2)->default(0.00);
            $table->string('estado_vuelto', 20)->default('No Aplica'); // Pendiente, Devuelto, No Aplica
            $table->timestamps();

            $table->foreign('caja_chica_id')->references('id')->on('caja_chica_cabecera')->onDelete('cascade');
            
            $table->index('caja_chica_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_chica_detalle');
        Schema::dropIfExists('caja_chica_cabecera');
    }
};
