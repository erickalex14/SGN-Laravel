<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('informes')) {
            return;
        }

        Schema::create('informes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('orden_id');
            $table->integer('tecnico_id');
            $table->longText('antecedentes')->nullable();
            $table->longText('proceso')->nullable();
            $table->longText('conclusion')->nullable();
            $table->longText('recomendaciones')->nullable();
            $table->string('estado_equipo', 60)->default('Operativo');
            $table->date('fecha_informe')->default(DB::raw('(CURRENT_DATE)'));
            $table->datetime('fecha_creacion')->nullable()->useCurrent();
            $table->mediumText('presupuesto_json')->nullable();

            $table->index('orden_id', 'orden_id');
            $table->index('tecnico_id', 'tecnico_id');

            $table->foreign('tecnico_id', 'informes_ibfk_2')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes');
    }
};
