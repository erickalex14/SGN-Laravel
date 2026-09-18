<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('caja_general_cobros')) {
            Schema::table('caja_general_cobros', function (Blueprint $table) {
                if (!Schema::hasColumn('caja_general_cobros', 'tipo_cobro')) {
                    $table->string('tipo_cobro', 50)->default('orden')->after('nro_orden');
                }
                if (!Schema::hasColumn('caja_general_cobros', 'codigo_producto')) {
                    $table->string('codigo_producto', 100)->nullable()->after('tipo_cobro');
                }
                if (!Schema::hasColumn('caja_general_cobros', 'serie_producto')) {
                    $table->string('serie_producto', 100)->nullable()->after('codigo_producto');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('caja_general_cobros')) {
            Schema::table('caja_general_cobros', function (Blueprint $table) {
                $cols = ['tipo_cobro', 'codigo_producto', 'serie_producto'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('caja_general_cobros', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
