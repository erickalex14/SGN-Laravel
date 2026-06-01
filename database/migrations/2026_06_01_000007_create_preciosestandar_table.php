<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preciosestandar')) {
            return;
        }

        Schema::create('preciosestandar', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('servicio', 200);
            $table->decimal('precio', 10, 2);
            $table->string('descripcion', 500)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->datetime('creado_en')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preciosestandar');
    }
};
