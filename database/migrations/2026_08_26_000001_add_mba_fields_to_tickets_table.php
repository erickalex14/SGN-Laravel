<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar ENUM de estado para incluir 'en_mba'
        DB::statement("ALTER TABLE tickets MODIFY COLUMN estado ENUM('abierto','en_proceso','en_espera','en_mba','resuelto','cerrado','cancelado') NOT NULL DEFAULT 'abierto'");

        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'numero_ticket_mba')) {
                $table->string('numero_ticket_mba', 60)->nullable()->after('solucion');
            }
            if (!Schema::hasColumn('tickets', 'fecha_escalado_mba')) {
                $table->dateTime('fecha_escalado_mba')->nullable()->after('numero_ticket_mba');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'fecha_escalado_mba')) {
                $table->dropColumn('fecha_escalado_mba');
            }
            if (Schema::hasColumn('tickets', 'numero_ticket_mba')) {
                $table->dropColumn('numero_ticket_mba');
            }
        });

        DB::statement("ALTER TABLE tickets MODIFY COLUMN estado ENUM('abierto','en_proceso','en_espera','resuelto','cerrado','cancelado') NOT NULL DEFAULT 'abierto'");
    }
};
