<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empresas')) {
            return;
        }

        Schema::create('empresas', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nombre', 200);
            $table->string('ruc', 13)->unique('uq_empresas_ruc');
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 200)->nullable();
            $table->string('direccion_empresa', 100)->nullable();
            $table->datetime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
