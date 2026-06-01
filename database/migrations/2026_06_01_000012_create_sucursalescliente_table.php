<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sucursalescliente')) {
            return;
        }

        Schema::create('sucursalescliente', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('codigo', 10)->unique('codigo')->comment('Código interno (N001, E001, 999)');
            $table->unsignedSmallInteger('numero')->comment('Número de sucursal');
            $table->string('nombre', 100)->comment('Nombre de la sucursal');
            $table->string('provincia', 60)->nullable()->comment('Provincia del Ecuador');
            $table->string('novitec_sucursal', 10)->nullable()->comment('Sucursal Novitec responsable: UIO / GYE / MTA');
            $table->tinyInteger('activa')->default(1);
            $table->datetime('created_at')->nullable()->useCurrent();

            $table->index('provincia', 'idx_provincia');
            $table->index('novitec_sucursal', 'idx_novitec_sucursal');
            $table->index('activa', 'idx_activa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursalescliente');
    }
};
