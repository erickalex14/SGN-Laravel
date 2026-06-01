<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('solicitudesrepuesto')) {
            return;
        }

        Schema::create('solicitudesrepuesto', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nro_solicitud', 30);
            $table->integer('orden_id')->unique('uk_orden');
            $table->integer('tecnico_id');
            $table->string('tecnico_nombre', 120)->default('');
            $table->string('repuesto_nombre', 200)->default('');
            $table->string('nro_parte', 100)->nullable();
            $table->integer('nro_parte_inv_id')->nullable();
            $table->string('repuesto_codigo', 60)->nullable();
            $table->integer('repuesto_inv_id')->nullable();
            $table->string('link_compra', 500)->nullable();
            $table->integer('cantidad')->default(1);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['Pendiente', 'Aprobada', 'Rechazada'])->default('Pendiente');
            $table->text('motivo_rechazo')->nullable();
            $table->string('aprobado_por', 120)->nullable();
            $table->integer('repuesto_id')->nullable();
            $table->integer('lista_compra_id')->nullable();
            $table->date('fecha_solicitud');
            $table->datetime('fecha_gestion')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('estado', 'idx_estado');
            $table->index('tecnico_id', 'idx_tecnico');
            $table->index('created_at', 'idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudesrepuesto');
    }
};
