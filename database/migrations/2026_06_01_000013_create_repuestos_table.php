<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('repuestos')) {
            return;
        }

        Schema::create('repuestos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('codigo', 60)->unique('codigo');
            $table->string('nro_parte', 100)->nullable();
            $table->string('nombre', 180);
            $table->string('descripcion', 400)->nullable();
            $table->string('marca_id', 36)->nullable();
            $table->string('tipo_dispositivo_id', 36)->nullable();
            $table->datetime('creado_en')->nullable()->useCurrent();
            $table->datetime('modificado_en')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->integer('stock')->default(0);
            $table->decimal('costo', 10, 2)->default(0.00);
            $table->tinyInteger('bodega')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repuestos');
    }
};
