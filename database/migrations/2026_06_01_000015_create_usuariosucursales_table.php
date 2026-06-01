<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuariosucursales')) {
            return;
        }

        Schema::create('usuariosucursales', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('usuario_id');
            $table->integer('sucursal_id');

            $table->unique(['usuario_id', 'sucursal_id'], 'uq_usuario_sucursal');
            $table->index('sucursal_id', 'fk_us_sucursal');
            $table->index('usuario_id', 'idx_us_usuario');

            $table->foreign('sucursal_id', 'fk_us_sucursal')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('usuario_id', 'fk_us_usuario')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuariosucursales');
    }
};
