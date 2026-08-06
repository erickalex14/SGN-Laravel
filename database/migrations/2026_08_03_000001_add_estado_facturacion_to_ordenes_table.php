<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ordenes') && !Schema::hasColumn('ordenes', 'estado_facturacion')) {
            Schema::table('ordenes', function (Blueprint $table) {
                $table->string('estado_facturacion', 30)->nullable()->default('Pendiente');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ordenes') && Schema::hasColumn('ordenes', 'estado_facturacion')) {
            Schema::table('ordenes', function (Blueprint $table) {
                $table->dropColumn('estado_facturacion');
            });
        }
    }
};
