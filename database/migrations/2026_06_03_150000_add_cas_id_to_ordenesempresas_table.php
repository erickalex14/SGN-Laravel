<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenesempresas', function (Blueprint $table) {
            $table->unsignedInteger('cas_id')->nullable()->after('sucursal_id');
            $table->foreign('cas_id')->references('id')->on('cas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ordenesempresas', function (Blueprint $table) {
            $table->dropForeign(['cas_id']);
            $table->dropColumn('cas_id');
        });
    }
};
