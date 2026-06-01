<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cas', function (Blueprint $table) {
            if (!Schema::hasColumn('cas', 'prefijo')) {
                $table->string('prefijo', 10)->nullable()->after('nombre')->comment('Prefijo único para códigos de órdenes del CAS');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cas', function (Blueprint $table) {
            if (Schema::hasColumn('cas', 'prefijo')) {
                $table->dropColumn('prefijo');
            }
        });
    }
};
