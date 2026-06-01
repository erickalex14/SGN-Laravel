<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('informefotos')) {
            return;
        }

        Schema::create('informefotos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('informe_id');
            $table->string('caption', 255)->nullable();
            $table->string('nombre_archivo', 255)->nullable();
            $table->string('tipo_mime', 100)->default('image/jpeg');
            $table->integer('orden_foto')->default(0);

            $table->index('informe_id', 'informe_id');
            $table->foreign('informe_id', 'informefotos_ibfk_1')->references('id')->on('informes')->onDelete('cascade');
        });

        // Add the LONGBLOB field using DB::statement to guarantee MySQL compatibility
        DB::statement('ALTER TABLE informefotos ADD foto_data LONGBLOB AFTER informe_id');
    }

    public function down(): void
    {
        Schema::dropIfExists('informefotos');
    }
};
