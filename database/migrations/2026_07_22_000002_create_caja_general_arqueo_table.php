<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('caja_general_arqueo')) {
            Schema::create('caja_general_arqueo', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sucursal_id');
                $table->string('codigo_sucursal', 20)->default('ACC30');
                $table->dateTime('fecha');
                $table->decimal('monto_sistema', 18, 2)->default(0.00);
                $table->decimal('monto_fisico', 18, 2)->default(0.00);
                $table->decimal('diferencia', 18, 2)->default(0.00);
                $table->string('tipo_diferencia', 20)->default('Cuadre Exacto');
                $table->text('observaciones')->nullable();
                $table->string('comprobante_deposito_url', 500)->nullable();
                $table->string('nro_comprobante_deposito', 100)->nullable();
                $table->unsignedBigInteger('usuario_id');
                $table->string('usuario_nombre', 150);
                $table->string('estado', 30)->default('Pendiente Deposito');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_general_arqueo');
    }
};
