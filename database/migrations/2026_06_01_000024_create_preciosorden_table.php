<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preciosorden')) {
            return;
        }

        Schema::create('preciosorden', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('orden_id');
            $table->integer('precio_estandar_id')->nullable();
            $table->string('servicio', 200);
            $table->decimal('precio', 10, 2);
            $table->string('descripcion', 500)->nullable();
            $table->datetime('creado_en')->nullable()->useCurrent();

            $table->index('orden_id', 'orden_id');
            $table->index('precio_estandar_id', 'precio_estandar_id');

            $table->foreign('orden_id', 'preciosorden_ibfk_1')->references('id')->on('ordenes')->onDelete('cascade');
            $table->foreign('precio_estandar_id', 'preciosorden_ibfk_2')->references('id')->on('preciosestandar')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preciosorden');
    }
};
