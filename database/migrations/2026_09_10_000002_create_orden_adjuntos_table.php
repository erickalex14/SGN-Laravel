<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orden_adjuntos')) {
            return;
        }

        Schema::create('orden_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('orden_id')->nullable()->index();
            $table->unsignedInteger('orden_empresa_id')->nullable()->index();
            $table->string('tipo_orden', 20)->default('personal');
            $table->string('tipo_adjunto', 50);
            $table->string('archivo_path', 500);
            $table->string('nombre_original', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamanio_bytes')->nullable();
            $table->timestamps();

            $table->index(['orden_id', 'tipo_adjunto'], 'idx_orden_tipo_adjunto');
            $table->index(['orden_empresa_id', 'tipo_adjunto'], 'idx_orden_emp_tipo_adjunto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_adjuntos');
    }
};
