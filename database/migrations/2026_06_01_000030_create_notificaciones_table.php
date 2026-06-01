<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notificaciones')) {
            return;
        }

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->enum('tipo', ['nc_solicitud', 'nc_aprobada', 'nc_rechazada']);
            $table->string('mensaje', 300);
            $table->unsignedInteger('nc_id')->nullable();
            $table->unsignedInteger('orden_id')->nullable();
            $table->string('nro_orden', 30)->nullable();
            $table->tinyInteger('leida')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['usuario_id', 'leida'], 'idx_usuario_leida');
            $table->index('created_at', 'idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
