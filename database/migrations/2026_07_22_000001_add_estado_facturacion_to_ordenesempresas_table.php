<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ordenesempresas') && !Schema::hasColumn('ordenesempresas', 'estado_facturacion')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                $table->string('estado_facturacion', 30)->nullable()->default('Pendiente')->after('estado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ordenesempresas') && Schema::hasColumn('ordenesempresas', 'estado_facturacion')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                $table->dropColumn('estado_facturacion');
            });
        }
    }
};
