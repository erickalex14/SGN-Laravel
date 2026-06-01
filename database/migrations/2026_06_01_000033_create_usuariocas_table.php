<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuariocas')) {
            return;
        }

        Schema::create('usuariocas', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('usuario_id');
            $table->unsignedInteger('cas_id');

            $table->unique(['usuario_id', 'cas_id'], 'uq_usuario_cas');
            $table->index('cas_id', 'fk_uc_cas');
            $table->index('usuario_id', 'idx_uc_usuario');

            $table->foreign('cas_id', 'fk_uc_cas')->references('id')->on('cas')->onDelete('cascade');
            $table->foreign('usuario_id', 'fk_uc_usuario')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuariocas');
    }
};
