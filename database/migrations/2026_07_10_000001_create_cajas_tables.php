<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cajas')) {
            Schema::create('cajas', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->integer('sucursal_id')->nullable();
                $table->enum('tipo', ['chica', 'grande']);
                $table->decimal('balance', 10, 2)->default(0.00);
                $table->timestamps();

                $table->unique(['tipo']); // Única caja chica y única caja grande global
                $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('cajas_mensualidades')) {
            Schema::create('cajas_mensualidades', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->integer('caja_id');
                $table->tinyInteger('mes');
                $table->smallInteger('anio');
                $table->decimal('saldo_inicial', 10, 2)->default(0.00);
                $table->decimal('monto_ingreso', 10, 2)->default(0.00);
                $table->decimal('saldo_cierre', 10, 2)->nullable();
                $table->boolean('cerrado')->default(false);
                $table->timestamps();

                $table->unique(['caja_id', 'mes', 'anio']);
                $table->foreign('caja_id')->references('id')->on('cajas')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('cajas_movimientos')) {
            Schema::create('cajas_movimientos', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->integer('caja_id');
                $table->enum('tipo', ['ingreso', 'egreso']);
                $table->enum('categoria', ['mensualidad', 'individual', 'gasto']);
                $table->decimal('monto', 10, 2);
                $table->text('descripcion');
                $table->integer('usuario_id');
                $table->integer('tecnico_id')->nullable();
                $table->date('fecha');
                $table->string('justificante_1', 255)->nullable();
                $table->string('justificante_2', 255)->nullable();
                $table->timestamps();

                $table->foreign('caja_id')->references('id')->on('cajas')->onDelete('cascade');
                $table->foreign('usuario_id')->references('id')->on('usuarios');
                $table->foreign('tecnico_id')->references('id')->on('usuarios');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas_movimientos');
        Schema::dropIfExists('cajas_mensualidades');
        Schema::dropIfExists('cajas');
    }
};
