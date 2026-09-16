<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar columnas a tabla ordenes
        if (Schema::hasTable('ordenes')) {
            Schema::table('ordenes', function (Blueprint $table) {
                if (!Schema::hasColumn('ordenes', 'valor_mano_obra')) {
                    $table->decimal('valor_mano_obra', 10, 2)->default(0.00)->after('tipo_servicio_texto');
                }
                if (!Schema::hasColumn('ordenes', 'titulo_servicio')) {
                    $table->string('titulo_servicio', 255)->nullable()->after('valor_mano_obra');
                }
                if (!Schema::hasColumn('ordenes', 'valor_repuestos')) {
                    $table->decimal('valor_repuestos', 10, 2)->default(0.00)->after('titulo_servicio');
                }
            });
        }

        // 2. Agregar columnas a tabla ordenesempresas
        if (Schema::hasTable('ordenesempresas')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                if (!Schema::hasColumn('ordenesempresas', 'valor_mano_obra')) {
                    $table->decimal('valor_mano_obra', 10, 2)->default(0.00)->after('tipo_servicio');
                }
                if (!Schema::hasColumn('ordenesempresas', 'titulo_servicio')) {
                    $table->string('titulo_servicio', 255)->nullable()->after('valor_mano_obra');
                }
                if (!Schema::hasColumn('ordenesempresas', 'valor_repuestos')) {
                    $table->decimal('valor_repuestos', 10, 2)->default(0.00)->after('titulo_servicio');
                }
            });
        }

        // 3. Agregar columnas a tabla recuento_b2b_item
        if (Schema::hasTable('recuento_b2b_item')) {
            Schema::table('recuento_b2b_item', function (Blueprint $table) {
                if (!Schema::hasColumn('recuento_b2b_item', 'valor_fijo')) {
                    $table->decimal('valor_fijo', 10, 2)->default(0.00)->after('tarifa_aplicada');
                }
                if (!Schema::hasColumn('recuento_b2b_item', 'valor_mano_obra')) {
                    $table->decimal('valor_mano_obra', 10, 2)->default(0.00)->after('valor_fijo');
                }
                if (!Schema::hasColumn('recuento_b2b_item', 'valor_repuestos')) {
                    $table->decimal('valor_repuestos', 10, 2)->default(0.00)->after('valor_mano_obra');
                }
                if (!Schema::hasColumn('recuento_b2b_item', 'titulo_servicio')) {
                    $table->string('titulo_servicio', 255)->nullable()->after('valor_repuestos');
                }
                if (!Schema::hasColumn('recuento_b2b_item', 'repuestos_detalle')) {
                    $table->text('repuestos_detalle')->nullable()->after('titulo_servicio');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ordenes')) {
            Schema::table('ordenes', function (Blueprint $table) {
                $cols = array_filter(['valor_mano_obra', 'titulo_servicio', 'valor_repuestos'], fn($c) => Schema::hasColumn('ordenes', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('ordenesempresas')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                $cols = array_filter(['valor_mano_obra', 'titulo_servicio', 'valor_repuestos'], fn($c) => Schema::hasColumn('ordenesempresas', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('recuento_b2b_item')) {
            Schema::table('recuento_b2b_item', function (Blueprint $table) {
                $cols = array_filter(['valor_fijo', 'valor_mano_obra', 'valor_repuestos', 'titulo_servicio', 'repuestos_detalle'], fn($c) => Schema::hasColumn('recuento_b2b_item', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
