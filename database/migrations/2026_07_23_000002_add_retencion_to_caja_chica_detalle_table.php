<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('caja_chica_detalle')) {
            Schema::table('caja_chica_detalle', function (Blueprint $table) {
                if (!Schema::hasColumn('caja_chica_detalle', 'monto_retencion')) {
                    $table->decimal('monto_retencion', 10, 2)->default(0.00)->after('iva');
                }
                if (!Schema::hasColumn('caja_chica_detalle', 'nro_retencion')) {
                    $table->string('nro_retencion', 100)->nullable()->after('monto_retencion');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('caja_chica_detalle')) {
            Schema::table('caja_chica_detalle', function (Blueprint $table) {
                if (Schema::hasColumn('caja_chica_detalle', 'monto_retencion')) {
                    $table->dropColumn('monto_retencion');
                }
                if (Schema::hasColumn('caja_chica_detalle', 'nro_retencion')) {
                    $table->dropColumn('nro_retencion');
                }
            });
        }
    }
};
