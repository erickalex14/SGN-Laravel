<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tiposdispositivo')) {
            return;
        }

        Schema::create('tiposdispositivo', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('codigo', 10)->unique('uq_tipo_codigo');
            $table->string('nombre', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiposdispositivo');
    }
};
