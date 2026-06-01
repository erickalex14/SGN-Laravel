<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equiposseries')) {
            return;
        }

        Schema::create('equiposseries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('equipo_id');
            $table->string('serie', 100)->default('');
            $table->unsignedTinyInteger('orden')->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('equipo_id', 'idx_equipo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equiposseries');
    }
};
