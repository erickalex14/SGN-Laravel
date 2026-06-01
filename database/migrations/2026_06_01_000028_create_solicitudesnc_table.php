<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('solicitudesnc')) {
            return;
        }

        Schema::create('solicitudesnc', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nro_solicitud', 20)->unique('nro_solicitud');
            $table->integer('orden_id')->unique('orden_id');
            $table->date('fecha_solicitud');
            $table->string('asunto', 200);
            $table->text('detalles');
            $table->string('nombre_admin', 100)->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->string('tecnico_nombre', 100);
            $table->integer('tecnico_id');
            $table->enum('estado', ['Pendiente', 'Aprobada', 'Rechazada'])->default('Pendiente');
            $table->datetime('creado_en')->nullable()->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('tecnico_id', 'tecnico_id');
            $table->foreign('orden_id', 'solicitudesnc_ibfk_1')->references('id')->on('ordenes')->onDelete('cascade');
            $table->foreign('tecnico_id', 'solicitudesnc_ibfk_2')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudesnc');
    }
};
