<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gruposacceso')) {
            return;
        }

        Schema::create('gruposacceso', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 80)->unique('uq_grupo_nombre');
            $table->string('descripcion', 255)->nullable();
            $table->tinyInteger('es_superadmin')->default(0);
            $table->datetime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gruposacceso');
    }
};
