<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cas')) {
            return;
        }

        Schema::create('cas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 120);
            $table->string('marca', 80)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 120)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->text('notas')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->datetime('creado_en')->useCurrent();
            $table->datetime('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->index('nombre', 'idx_cas_nombre');
            $table->index('activo', 'idx_cas_activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cas');
    }
};
