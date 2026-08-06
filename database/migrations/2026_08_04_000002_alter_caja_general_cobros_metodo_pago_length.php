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
                $table->string('metodo_pago', 255)->default('Efectivo')->change();
                $table->string('destino_cuenta', 100)->default('Caja General')->change();
                $table->string('tipo_cobro', 100)->default('orden')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('caja_general_cobros')) {
            Schema::table('caja_general_cobros', function (Blueprint $table) {
                $table->string('metodo_pago', 50)->default('Efectivo')->change();
                $table->string('destino_cuenta', 50)->default('Caja General')->change();
                $table->string('tipo_cobro', 50)->default('orden')->change();
            });
        }
    }
};
