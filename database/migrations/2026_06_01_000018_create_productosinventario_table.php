<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('productosinventario')) {
            return;
        }

        Schema::create('productosinventario', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 50)->unique('uq_codigo');
            $table->string('descripcion', 255);
            $table->unsignedInteger('marca_id');
            $table->integer('tipo_dispositivo_id')->nullable();
            $table->string('tipo_dispositivo_codigo', 10)->nullable();

            $table->index('marca_id', 'fk_pi_marca');
            $table->index('tipo_dispositivo_id', 'fk_prod_tipo_dispositivo');
            $table->index('codigo', 'idx_prod_codigo');

            $table->foreign('marca_id', 'fk_pi_marca')->references('id')->on('marcas')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('tipo_dispositivo_id', 'fk_prod_tipo_dispositivo')->references('id')->on('tiposdispositivo')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productosinventario');
    }
};
