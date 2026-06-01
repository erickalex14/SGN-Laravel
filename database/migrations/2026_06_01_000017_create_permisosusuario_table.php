<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permisosusuario')) {
            return;
        }

        Schema::create('permisosusuario', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('usuario_id');
            $table->string('modulo', 60);
            $table->string('accion', 20)->default('ver');
            $table->tinyInteger('permitido')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['usuario_id', 'modulo', 'accion'], 'uk_usuario_mod_acc');
            $table->index('usuario_id', 'idx_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisosusuario');
    }
};
