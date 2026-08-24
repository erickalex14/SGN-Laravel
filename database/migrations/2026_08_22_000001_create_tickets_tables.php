<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ticket', 25)->unique(); // UIO-TK-000001 o SYS-000001
            $table->enum('tipo_ticket', ['soporte_tecnico', 'sistemas'])->default('soporte_tecnico');
            $table->string('categoria', 60); // Hardware, Software, Cuentas/Accesos, Redes/CCTV, etc.
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('estado', ['abierto', 'en_proceso', 'en_espera', 'resuelto', 'cerrado', 'cancelado'])->default('abierto');

            // Solicitante externo (Novicompu / ENV)
            $table->unsignedInteger('solicitante_id');
            $table->enum('empresa_origen', ['NOVICOMPU', 'ENV', 'OTRO'])->default('NOVICOMPU');
            $table->unsignedBigInteger('sucursal_cliente_id')->nullable();
            $table->string('tienda_nombre', 120)->nullable();
            $table->string('contacto_telefono', 30)->nullable();

            // Sede de atención (Por el momento 1 = Novitec Quito)
            $table->integer('sucursal_atencion_id')->default(1);
            $table->unsignedInteger('asignado_a_id')->nullable();

            $table->string('titulo', 255);
            $table->text('descripcion');

            // Fechas de ciclo de vida
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_asignacion')->nullable();
            $table->dateTime('fecha_primera_respuesta')->nullable();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->dateTime('fecha_cierre')->nullable();

            $table->text('solucion')->nullable();
            $table->tinyInteger('calificacion')->nullable(); // 1 a 5
            $table->string('comentario_calificacion', 255)->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('tipo_ticket');
            $table->index('solicitante_id');
            $table->index('asignado_a_id');
            $table->index('sucursal_atencion_id');
            $table->index('sucursal_cliente_id');
        });

        Schema::create('ticket_mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedInteger('usuario_id');
            $table->text('mensaje');
            $table->boolean('es_nota_interna')->default(false);
            $table->string('cambio_estado', 50)->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->index('ticket_id');
            $table->index('usuario_id');
        });

        Schema::create('ticket_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('mensaje_id')->nullable();
            $table->unsignedInteger('usuario_id');
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->index('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_adjuntos');
        Schema::dropIfExists('ticket_mensajes');
        Schema::dropIfExists('tickets');
    }
};
