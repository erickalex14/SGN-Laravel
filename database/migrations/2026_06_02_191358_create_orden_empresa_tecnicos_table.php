<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('orden_empresa_tecnicos');

        Schema::create('orden_empresa_tecnicos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('orden_empresa_id');
            $table->integer('tecnico_id');

            $table->foreign('orden_empresa_id')->references('id')->on('ordenesempresas')->onDelete('cascade');
            $table->foreign('tecnico_id')->references('id')->on('usuarios')->onDelete('cascade');

            $table->index('orden_empresa_id', 'idx_oet_orden');
            $table->index('tecnico_id', 'idx_oet_tecnico');
        });

        Schema::table('ordenesempresas', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenesempresas', 'valor_hora')) {
                $table->decimal('valor_hora', 10, 2)->nullable()->after('estado');
            }
            if (!Schema::hasColumn('ordenesempresas', 'horas_trabajadas')) {
                $table->decimal('horas_trabajadas', 8, 2)->nullable()->after('valor_hora');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenesempresas', function (Blueprint $table) {
            $table->dropColumn(['valor_hora', 'horas_trabajadas']);
        });

        Schema::dropIfExists('orden_empresa_tecnicos');
    }
};
