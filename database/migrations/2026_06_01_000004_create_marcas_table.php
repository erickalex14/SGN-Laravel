<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marcas')) {
            return;
        }

        Schema::create('marcas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcas');
    }
};
