<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sucursales')) {
            return;
        }

        Schema::create('sucursales', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('nro_sucursal')->unique('nro_sucursal');
            $table->string('ciudad', 100);
            $table->string('secuencial', 10);
            $table->string('nro_base', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
