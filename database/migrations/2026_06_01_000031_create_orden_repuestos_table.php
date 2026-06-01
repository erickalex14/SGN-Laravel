<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orden_repuestos')) {
            return;
        }

        Schema::create('orden_repuestos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('orden_id');
            $table->unsignedInteger('repuesto_id');
            $table->integer('cantidad')->default(1);
            $table->timestamp('fecha')->useCurrent();
            $table->unsignedInteger('usuario_id')->nullable();

            $table->index('orden_id', 'orden_id');
            $table->index('repuesto_id', 'repuesto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_repuestos');
    }
};
