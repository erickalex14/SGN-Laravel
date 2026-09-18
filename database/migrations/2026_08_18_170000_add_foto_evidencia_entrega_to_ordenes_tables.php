<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ordenes') && !Schema::hasColumn('ordenes', 'foto_evidencia_entrega')) {
            Schema::table('ordenes', function (Blueprint $table) {
                $table->string('foto_evidencia_entrega', 500)->nullable()->after('memo_entrega');
            });
        }

        if (Schema::hasTable('ordenesempresas') && !Schema::hasColumn('ordenesempresas', 'foto_evidencia_entrega')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                $table->string('foto_evidencia_entrega', 500)->nullable()->after('memo_entrega');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ordenes') && Schema::hasColumn('ordenes', 'foto_evidencia_entrega')) {
            Schema::table('ordenes', function (Blueprint $table) {
                $table->dropColumn('foto_evidencia_entrega');
            });
        }

        if (Schema::hasTable('ordenesempresas') && Schema::hasColumn('ordenesempresas', 'foto_evidencia_entrega')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                $table->dropColumn('foto_evidencia_entrega');
            });
        }
    }
};
