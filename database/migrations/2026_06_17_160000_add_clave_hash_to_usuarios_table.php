<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuarios', 'clave_hash')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('clave_hash')->nullable()->after('clave');
            });
        }

        DB::table('usuarios')
            ->select(['id', 'clave'])
            ->whereNull('clave_hash')
            ->whereNotNull('clave')
            ->where('clave', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($usuarios): void {
                foreach ($usuarios as $usuario) {
                    DB::table('usuarios')
                        ->where('id', $usuario->id)
                        ->update([
                            'clave_hash' => Hash::make((string) $usuario->clave),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuarios', 'clave_hash')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('clave_hash');
            });
        }
    }
};
