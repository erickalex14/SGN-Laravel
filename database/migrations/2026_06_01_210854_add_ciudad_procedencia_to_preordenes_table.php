<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('preordenes', function (Blueprint $table) {
            $table->string('ciudad_procedencia', 100)->nullable()->after('nro_sucursal_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preordenes', function (Blueprint $table) {
            $table->dropColumn('ciudad_procedencia');
        });
    }
};
