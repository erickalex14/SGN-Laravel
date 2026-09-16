<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('repuestos')) {
            Schema::table('repuestos', function (Blueprint $table) {
                if (!Schema::hasColumn('repuestos', 'pvp')) {
                    $table->decimal('pvp', 10, 2)->default(0.00)->after('costo');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('repuestos')) {
            Schema::table('repuestos', function (Blueprint $table) {
                if (Schema::hasColumn('repuestos', 'pvp')) {
                    $table->dropColumn('pvp');
                }
            });
        }
    }
};
