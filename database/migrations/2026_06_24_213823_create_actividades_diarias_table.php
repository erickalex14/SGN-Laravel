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
        if (Schema::hasTable('actividades_diarias')) {
            return;
        }

        Schema::create('actividades_diarias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->string('tipo_accion', 60);
            $table->string('descripcion', 500);
            $table->string('modulo', 60);
            $table->unsignedInteger('referencia_id')->nullable();
            $table->string('referencia_tipo', 60)->nullable();
            $table->json('metadata_json')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('fecha_hora');
            $table->date('fecha');

            $table->index(['usuario_id', 'fecha'], 'idx_usuario_fecha');
            $table->index('fecha', 'idx_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades_diarias');
    }
};
