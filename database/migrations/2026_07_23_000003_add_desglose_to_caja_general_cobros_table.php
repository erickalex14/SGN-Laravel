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
                if (!Schema::hasColumn('caja_general_cobros', 'monto_recibido')) {
                    $table->decimal('monto_recibido', 18, 2)->default(0.00)->after('monto_cobrado');
                }
                if (!Schema::hasColumn('caja_general_cobros', 'vuelto_dado')) {
                    $table->decimal('vuelto_dado', 18, 2)->default(0.00)->after('monto_recibido');
                }
                if (!Schema::hasColumn('caja_general_cobros', 'sobrante')) {
                    $table->decimal('sobrante', 18, 2)->default(0.00)->after('vuelto_dado');
                }
                if (!Schema::hasColumn('caja_general_cobros', 'faltante')) {
                    $table->decimal('faltante', 18, 2)->default(0.00)->after('sobrante');
                }
                if (!Schema::hasColumn('caja_general_cobros', 'monto_neto_caja')) {
                    $table->decimal('monto_neto_caja', 18, 2)->default(0.00)->after('faltante');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('caja_general_cobros')) {
            Schema::table('caja_general_cobros', function (Blueprint $table) {
                $cols = ['monto_recibido', 'vuelto_dado', 'sobrante', 'faltante', 'monto_neto_caja'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('caja_general_cobros', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
