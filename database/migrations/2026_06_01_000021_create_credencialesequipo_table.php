<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credencialesequipo')) {
            return;
        }

        Schema::create('credencialesequipo', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('equipo_id');
            $table->string('usuario', 100)->nullable();
            $table->mediumText('contrasena');
            $table->tinyInteger('es_patron')->default(0);

            $table->index('equipo_id', 'equipo_id');
            $table->foreign('equipo_id', 'credencialesequipo_ibfk_1')->references('id')->on('equipos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credencialesequipo');
    }
};
