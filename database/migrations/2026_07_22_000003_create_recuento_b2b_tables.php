<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recuento_b2b_lote')) {
            Schema::create('recuento_b2b_lote', function (Blueprint $table) {
                $table->id();
                $table->string('nro_lote', 50)->unique();
                $table->string('empresa_nombre', 150);
                $table->integer('total_ordenes')->default(0);
                $table->decimal('subtotal', 18, 2)->default(0.00);
                $table->decimal('monto_neto_banco', 18, 2)->default(0.00);
                $table->decimal('monto_retencion_renta', 18, 2)->default(0.00);
                $table->decimal('monto_retencion_iva', 18, 2)->default(0.00);
                $table->string('nro_retencion', 100)->nullable();
                $table->string('nro_comprobante_pago', 100)->nullable();
                $table->string('banco_destino', 150)->nullable();
                $table->string('estado', 30)->default('Cobrado');
                $table->unsignedBigInteger('usuario_id');
                $table->string('usuario_nombre', 150);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('recuento_b2b_item')) {
            Schema::create('recuento_b2b_item', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lote_id')->constrained('recuento_b2b_lote')->onDelete('cascade');
                $table->unsignedBigInteger('orden_id');
                $table->string('tipo_orden', 20)->default('empresa');
                $table->string('nro_orden', 50);
                $table->string('subtipo', 50)->nullable();
                $table->string('tecnico_nombre', 150)->nullable();
                $table->integer('cantidad_tecnicos')->default(1);
                $table->decimal('horas_trabajadas', 18, 2)->default(0.00);
                $table->decimal('tarifa_aplicada', 18, 2)->default(0.00);
                $table->decimal('valor_total', 18, 2)->default(0.00);
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recuento_b2b_item');
        Schema::dropIfExists('recuento_b2b_lote');
    }
};
