<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('listascompra')) {
            return;
        }

        Schema::create('listascompra', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nro_lista', 30);
            $table->string('creado_por', 120)->default('');
            $table->integer('creado_por_id')->nullable();
            $table->date('fecha_creacion');
            $table->enum('estado', ['Pendiente', 'Completada', 'Cancelada'])->default('Pendiente');
            $table->text('observacion')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listascompra');
    }
};
