<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preordenes', function (Blueprint $table) {
            $table->string('serie', 100)->nullable()->after('detalle_equipo');
        });
    }

    public function down(): void
    {
        Schema::table('preordenes', function (Blueprint $table) {
            $table->dropColumn('serie');
        });
    }
};
