<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('caja_general_cobros')) {
            Schema::create('caja_general_cobros', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('orden_id')->nullable();
                $table->string('nro_orden', 50);
                $table->string('cliente_nombre', 255)->nullable();
                $table->string('equipo_info', 255)->nullable();
                $table->decimal('monto_cobrado', 18, 2)->default(0.00);
                $table->string('metodo_pago', 50)->default('Efectivo'); // Efectivo, Tarjeta, Transferencia, Deposito
                $table->string('destino_cuenta', 50)->default('Caja General'); // Caja General, Bancos
                $table->unsignedBigInteger('sucursal_id');
                $table->unsignedBigInteger('usuario_id');
                $table->string('usuario_nombre', 150);
                $table->text('observaciones')->nullable();
                $table->dateTime('fecha_cobro');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_general_cobros');
    }
};
