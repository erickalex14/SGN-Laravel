<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('recuento_b2b_lote')) {
            Schema::table('recuento_b2b_lote', function (Blueprint $table) {
                if (!Schema::hasColumn('recuento_b2b_lote', 'monto_iva')) {
                    $table->decimal('monto_iva', 18, 2)->default(0.00)->after('subtotal');
                }
                if (!Schema::hasColumn('recuento_b2b_lote', 'total_con_iva')) {
                    $table->decimal('total_con_iva', 18, 2)->default(0.00)->after('monto_iva');
                }
                if (!Schema::hasColumn('recuento_b2b_lote', 'comprobante_path')) {
                    $table->string('comprobante_path', 255)->nullable()->after('banco_destino');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recuento_b2b_lote')) {
            Schema::table('recuento_b2b_lote', function (Blueprint $table) {
                if (Schema::hasColumn('recuento_b2b_lote', 'monto_iva')) {
                    $table->dropColumn('monto_iva');
                }
                if (Schema::hasColumn('recuento_b2b_lote', 'total_con_iva')) {
                    $table->dropColumn('total_con_iva');
                }
                if (Schema::hasColumn('recuento_b2b_lote', 'comprobante_path')) {
                    $table->dropColumn('comprobante_path');
                }
            });
        }
    }
};
