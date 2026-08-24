<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ticket_llamadas')) {
            Schema::create('ticket_llamadas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->unsignedBigInteger('iniciador_id');
                $table->unsignedBigInteger('receptor_id')->nullable();
                $table->enum('estado', ['timbrando', 'en_curso', 'finalizada', 'rechazada', 'perdida'])->default('timbrando');
                $table->longText('signal_offer')->nullable();
                $table->longText('signal_answer')->nullable();
                $table->longText('signal_ice_iniciador')->nullable();
                $table->longText('signal_ice_receptor')->nullable();
                $table->integer('duracion_segundos')->default(0);
                $table->dateTime('fecha_inicio')->nullable();
                $table->dateTime('fecha_fin')->nullable();
                $table->timestamps();

                $table->index('ticket_id');
                $table->index(['ticket_id', 'estado']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_llamadas');
    }
};
