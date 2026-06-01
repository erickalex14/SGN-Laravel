<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tiposservicio')) {
            return;
        }

        Schema::create('tiposservicio', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nombre', 200);
            $table->string('descripcion', 500)->nullable();
            $table->decimal('precio', 10, 2)->default(0.00);
            $table->tinyInteger('activo')->default(1);
            $table->datetime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiposservicio');
    }
};
