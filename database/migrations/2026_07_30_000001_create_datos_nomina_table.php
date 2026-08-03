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
        if (!Schema::hasTable('datos_nomina')) {
            Schema::create('datos_nomina', function (Blueprint $table) {
                $table->id();
                $table->integer('usuario_id')->unique();
                $table->string('nombres_completos', 255)->nullable();
                $table->string('cedula', 20)->nullable();
                $table->string('cargo', 255)->nullable();
                $table->string('telefono', 50)->nullable();
                $table->string('email_personal', 255)->nullable();
                $table->text('contacto_emergencia')->nullable();
                $table->string('foto_url', 255)->nullable();
                $table->string('hoja_vida_url', 255)->nullable();
                $table->date('fecha_ingreso')->nullable();
                $table->date('fecha_salida')->nullable();
                $table->string('estado_afiliacion', 100)->nullable()->default('Por Afiliar');
                $table->decimal('sueldo_base', 10, 2)->default(0.00);
                $table->decimal('bonificaciones', 10, 2)->default(0.00);
                $table->decimal('sanciones', 10, 2)->default(0.00);
                $table->decimal('total_a_recibir', 10, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datos_nomina');
    }
};
