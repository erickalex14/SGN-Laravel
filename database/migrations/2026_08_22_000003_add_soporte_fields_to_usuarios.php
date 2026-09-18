<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'usuario_mba')) {
                $table->string('usuario_mba', 60)->nullable()->after('empresa_origen');
            }
            if (!Schema::hasColumn('usuarios', 'codigo_usuario')) {
                $table->string('codigo_usuario', 60)->nullable()->after('usuario_mba');
            }
            if (!Schema::hasColumn('usuarios', 'anydesk_id')) {
                $table->string('anydesk_id', 50)->nullable()->after('codigo_usuario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'anydesk_id')) {
                $table->dropColumn('anydesk_id');
            }
            if (Schema::hasColumn('usuarios', 'codigo_usuario')) {
                $table->dropColumn('codigo_usuario');
            }
            if (Schema::hasColumn('usuarios', 'usuario_mba')) {
                $table->dropColumn('usuario_mba');
            }
        });
    }
};
