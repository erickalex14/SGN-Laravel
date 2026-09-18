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
        Schema::create('solicitudes_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('usuario_id');
            $table->unsignedBigInteger('datos_nomina_id')->nullable();
            $table->integer('dias_solicitados')->default(1);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('observacion_empleado')->nullable();
            $table->string('estado', 50)->default('Pendiente'); // Pendiente, Aprobado, Rechazado
            $table->integer('dias_aprobados')->nullable();
            $table->date('fecha_inicio_aprobada')->nullable();
            $table->date('fecha_fin_aprobada')->nullable();
            $table->text('observacion_admin')->nullable();
            $table->integer('aprobado_por')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('datos_nomina_id')->references('id')->on('datos_nomina')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_vacaciones');
    }
};
