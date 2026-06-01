<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clientes')) {
            return;
        }

        Schema::create('clientes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('identificacion', 13)->unique('identificacion');
            $table->string('numero_contacto', 10);
            $table->string('correo', 50)->nullable();
            $table->string('direccion_clientes', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
