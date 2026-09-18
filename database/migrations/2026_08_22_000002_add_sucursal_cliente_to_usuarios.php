<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'sucursal_cliente_id')) {
                $table->unsignedBigInteger('sucursal_cliente_id')->nullable()->after('sucursal_id');
            }
            if (!Schema::hasColumn('usuarios', 'empresa_origen')) {
                $table->string('empresa_origen', 30)->default('NOVICOMPU')->after('sucursal_cliente_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'empresa_origen')) {
                $table->dropColumn('empresa_origen');
            }
            if (Schema::hasColumn('usuarios', 'sucursal_cliente_id')) {
                $table->dropColumn('sucursal_cliente_id');
            }
        });
    }
};
