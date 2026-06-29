<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ordenes_llamadas', function (Blueprint $table) {
            $table->id();
            $table->integer('orden_id')->nullable();
            $table->unsignedInteger('orden_empresa_id')->nullable();
            $table->integer('usuario_id');
            $table->timestamp('fecha_hora');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('orden_id')->references('id')->on('ordenes')->onDelete('cascade');
            $table->foreign('orden_empresa_id')->references('id')->on('ordenesempresas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_llamadas');
    }
};
