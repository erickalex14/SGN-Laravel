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
                if (!Schema::hasColumn('caja_general_cobros', 'arqueo_id')) {
                    $table->unsignedBigInteger('arqueo_id')->nullable()->after('sucursal_id');
                }
                if (!Schema::hasColumn('caja_general_cobros', 'estado_arqueo')) {
                    $table->string('estado_arqueo', 50)->default('Pendiente')->after('arqueo_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('caja_general_cobros')) {
            Schema::table('caja_general_cobros', function (Blueprint $table) {
                if (Schema::hasColumn('caja_general_cobros', 'arqueo_id')) {
                    $table->dropColumn('arqueo_id');
                }
                if (Schema::hasColumn('caja_general_cobros', 'estado_arqueo')) {
                    $table->dropColumn('estado_arqueo');
                }
            });
        }
    }
};
