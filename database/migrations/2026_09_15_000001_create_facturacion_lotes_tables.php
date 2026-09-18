<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla principal de lotes de facturación externa (Milenium)
        if (!Schema::hasTable('facturacion_lotes')) {
            Schema::create('facturacion_lotes', function (Blueprint $table) {
                $table->id();
                $table->string('nro_factura', 50)->index();
                $table->string('nro_autorizacion', 60)->nullable()->index();
                $table->decimal('valor_facturado', 12, 2)->default(0);
                $table->date('fecha_factura');
                $table->unsignedBigInteger('creado_por')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabla pivote de órdenes vinculadas al lote de facturación
        if (!Schema::hasTable('facturacion_lote_ordenes')) {
            Schema::create('facturacion_lote_ordenes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('facturacion_lote_id');
                $table->enum('tipo_orden', ['personal', 'empresa']);
                $table->unsignedBigInteger('orden_id');
                $table->string('nro_orden', 30)->index();
                $table->timestamps();

                $table->foreign('facturacion_lote_id')
                    ->references('id')
                    ->on('facturacion_lotes')
                    ->onDelete('cascade');

                $table->unique(['tipo_orden', 'orden_id'], 'uq_lote_tipo_orden_id');
                $table->index(['tipo_orden', 'orden_id'], 'ix_lote_orden_lookup');
            });
        }

        // 3. Columnas de apoyo en ordenes
        if (Schema::hasTable('ordenes')) {
            Schema::table('ordenes', function (Blueprint $table) {
                if (!Schema::hasColumn('ordenes', 'nro_autorizacion_factura')) {
                    $table->string('nro_autorizacion_factura', 60)->nullable()->after('fecha_facturacion');
                }
                if (!Schema::hasColumn('ordenes', 'valor_facturado')) {
                    $table->decimal('valor_facturado', 12, 2)->nullable()->after('nro_autorizacion_factura');
                }
            });
        }

        // 4. Columnas de apoyo en ordenesempresas
        if (Schema::hasTable('ordenesempresas')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                if (!Schema::hasColumn('ordenesempresas', 'nro_factura')) {
                    $table->string('nro_factura', 50)->nullable()->after('estado_facturacion');
                }
                if (!Schema::hasColumn('ordenesempresas', 'fecha_facturacion')) {
                    $table->date('fecha_facturacion')->nullable()->after('nro_factura');
                }
                if (!Schema::hasColumn('ordenesempresas', 'nro_autorizacion_factura')) {
                    $table->string('nro_autorizacion_factura', 60)->nullable()->after('fecha_facturacion');
                }
                if (!Schema::hasColumn('ordenesempresas', 'valor_facturado')) {
                    $table->decimal('valor_facturado', 12, 2)->nullable()->after('nro_autorizacion_factura');
                }
            });
        }

        // 5. Actualizar vista_ordenes para proyectar nro_factura, nro_autorizacion_factura y valor_facturado
        DB::statement("DROP VIEW IF EXISTS vista_ordenes");
        DB::statement("CREATE VIEW vista_ordenes AS
            select 
                `o`.`id` AS `orden_id`,
                `o`.`nro_orden` AS `nro_orden`,
                'personal' AS `tipo_orden`,
                `o`.`estado_orden` AS `estado_orden`,
                `o`.`estado_repuesto` AS `estado_repuesto`,
                `o`.`estado_garantia` AS `estado_garantia`,
                `o`.`motivo_ingreso` AS `motivo_ingreso`,
                `o`.`fecha_de_ingreso` AS `fecha_de_ingreso`,
                `o`.`fecha_recibida_tecnico` AS `fecha_recibida_tecnico`,
                `o`.`fecha_prometido` AS `fecha_prometido`,
                `o`.`fecha_finalizacion` AS `fecha_finalizacion`,
                `o`.`fecha_lista_entrega` AS `fecha_lista_entrega`,
                `o`.`fecha_entrega` AS `fecha_entrega`,
                `o`.`fecha_modificacion` AS `fecha_modificacion`,
                `o`.`nro_factura` AS `nro_factura`,
                `o`.`nro_factura_2` AS `nro_factura_2`,
                `o`.`nro_sucursal_cliente` AS `nro_sucursal_cliente`,
                `o`.`tecnico_id` AS `tecnico_id`,
                `o`.`sucursal_id` AS `sucursal_id`,
                `o`.`ingresado_por` AS `ingresado_por`,
                `o`.`modificado_por` AS `modificado_por`,
                `o`.`cliente_id` AS `cliente_id`,
                NULL AS `empresa_id`,
                `o`.`equipo_id` AS `equipo_id`,
                concat(`c`.`nombres`,' ',`c`.`apellidos`) AS `cliente`,
                `c`.`nombres` AS `nombres`,
                `c`.`apellidos` AS `apellidos`,
                `c`.`identificacion` AS `identificacion`,
                `c`.`numero_contacto` AS `numero_contacto`,
                `c`.`correo` AS `correo`,
                `c`.`direccion_clientes` AS `direccion`,
                `e`.`tipo` AS `tipo`,
                `e`.`marca` AS `marca`,
                `e`.`modelo` AS `modelo`,
                `e`.`serie` AS `serie`,
                `e`.`falla` AS `falla`,
                `e`.`observacion` AS `observacion`,
                `o`.`memo_entrega` AS `memo_entrega`,
                `o`.`foto_evidencia_entrega` AS `foto_evidencia_entrega`,
                date_format(`o`.`fecha_facturacion`,'%Y-%m-%d') AS `fecha_facturacion`,
                `o`.`nro_autorizacion_factura` AS `nro_autorizacion_factura`,
                `o`.`valor_facturado` AS `valor_facturado`,
                (coalesce(`o`.`estado_facturacion`, 'Pendiente') collate utf8mb4_0900_ai_ci) AS `estado_facturacion`,
                `u`.`nombre_tecnico` AS `tecnico`,
                `s`.`ciudad` AS `sucursal`,
                date_format(`o`.`fecha_de_ingreso`,'%d/%m/%Y %H:%i') AS `fecha_de_ingreso_fmt`,
                date_format(`o`.`fecha_recibida_tecnico`,'%d/%m/%Y %H:%i') AS `fecha_recibida_tecnico_fmt`,
                date_format(`o`.`fecha_prometido`,'%d/%m/%Y') AS `fecha_prometido_fmt`,
                date_format(`o`.`fecha_finalizacion`,'%d/%m/%Y %H:%i') AS `fecha_finalizacion_fmt`,
                date_format(`o`.`fecha_lista_entrega`,'%d/%m/%Y %H:%i') AS `fecha_lista_entrega_fmt`,
                date_format(`o`.`fecha_entrega`,'%d/%m/%Y %H:%i') AS `fecha_entrega_fmt`,
                date_format(`o`.`fecha_modificacion`,'%d/%m/%Y %H:%i') AS `fecha_modificacion_fmt`
            from ((((`ordenes` `o` 
                join `clientes` `c` on((`o`.`cliente_id` = `c`.`id`))) 
                join `equipos` `e` on((`o`.`equipo_id` = `e`.`id`))) 
                join `usuarios` `u` on((`o`.`tecnico_id` = `u`.`id`))) 
                join `sucursales` `s` on((`o`.`sucursal_id` = `s`.`id`))) 
            union all 
            select 
                `oe`.`id` AS `orden_id`,
                (`oe`.`nro_orden` collate utf8mb4_0900_ai_ci) AS `nro_orden`,
                ('empresa' collate utf8mb4_0900_ai_ci) AS `tipo_orden`,
                (`oe`.`estado` collate utf8mb4_0900_ai_ci) AS `estado_orden`,
                ('No requerido' collate utf8mb4_0900_ai_ci) AS `estado_repuesto`,
                NULL AS `estado_garantia`,
                (concat('Empresa · ',`oe`.`subtipo`) collate utf8mb4_0900_ai_ci) AS `motivo_ingreso`,
                `oe`.`fecha_ingreso` AS `fecha_de_ingreso`,
                `oe`.`fecha_recibida_tecnico` AS `fecha_recibida_tecnico`,
                `oe`.`fecha_prometido` AS `fecha_prometido`,
                `oe`.`fecha_finalizacion` AS `fecha_finalizacion`,
                `oe`.`fecha_lista_entrega` AS `fecha_lista_entrega`,
                `oe`.`fecha_entrega` AS `fecha_entrega`,
                `oe`.`fecha_modificacion` AS `fecha_modificacion`,
                (`oe`.`nro_factura` collate utf8mb4_0900_ai_ci) AS `nro_factura`,
                NULL AS `nro_factura_2`,
                (`oe`.`nro_sucursal_cliente` collate utf8mb4_0900_ai_ci) AS `nro_sucursal_cliente`,
                `oe`.`tecnico_id` AS `tecnico_id`,
                `oe`.`sucursal_id` AS `sucursal_id`,
                `oe`.`ingresado_por` AS `ingresado_por`,
                `oe`.`modificado_por` AS `modificado_por`,
                NULL AS `cliente_id`,
                `oe`.`empresa_id` AS `empresa_id`,
                `oe`.`equipo_id` AS `equipo_id`,
                (`emp`.`nombre` collate utf8mb4_0900_ai_ci) AS `cliente`,
                (`emp`.`nombre` collate utf8mb4_0900_ai_ci) AS `nombres`,
                ('' collate utf8mb4_0900_ai_ci) AS `apellidos`,
                (`emp`.`ruc` collate utf8mb4_0900_ai_ci) AS `identificacion`,
                (`emp`.`telefono` collate utf8mb4_0900_ai_ci) AS `numero_contacto`,
                (`emp`.`correo` collate utf8mb4_0900_ai_ci) AS `correo`,
                (`emp`.`direccion_empresa` collate utf8mb4_0900_ai_ci) AS `direccion`,
                (`e`.`tipo` collate utf8mb4_0900_ai_ci) AS `tipo`,
                (`e`.`marca` collate utf8mb4_0900_ai_ci) AS `marca`,
                (`e`.`modelo` collate utf8mb4_0900_ai_ci) AS `modelo`,
                (`e`.`serie` collate utf8mb4_0900_ai_ci) AS `serie`,
                (`oe`.`descripcion` collate utf8mb4_0900_ai_ci) AS `falla`,
                (`oe`.`descripcion` collate utf8mb4_0900_ai_ci) AS `observacion`,
                (`oe`.`memo_entrega` collate utf8mb4_0900_ai_ci) AS `memo_entrega`,
                (`oe`.`foto_evidencia_entrega` collate utf8mb4_0900_ai_ci) AS `foto_evidencia_entrega`,
                date_format(`oe`.`fecha_facturacion`,'%Y-%m-%d') AS `fecha_facturacion`,
                (`oe`.`nro_autorizacion_factura` collate utf8mb4_0900_ai_ci) AS `nro_autorizacion_factura`,
                `oe`.`valor_facturado` AS `valor_facturado`,
                (coalesce(`oe`.`estado_facturacion`, 'Pendiente') collate utf8mb4_0900_ai_ci) AS `estado_facturacion`,
                `u`.`nombre_tecnico` AS `tecnico`,
                `s`.`ciudad` AS `sucursal`,
                date_format(`oe`.`fecha_ingreso`,'%d/%m/%Y %H:%i') AS `fecha_de_ingreso_fmt`,
                date_format(`oe`.`fecha_recibida_tecnico`,'%d/%m/%Y %H:%i') AS `fecha_recibida_tecnico_fmt`,
                date_format(`oe`.`fecha_prometido`,'%d/%m/%Y') AS `fecha_prometido_fmt`,
                date_format(`oe`.`fecha_finalizacion`,'%d/%m/%Y %H:%i') AS `fecha_finalizacion_fmt`,
                date_format(`oe`.`fecha_lista_entrega`,'%d/%m/%Y %H:%i') AS `fecha_lista_entrega_fmt`,
                date_format(`oe`.`fecha_entrega`,'%d/%m/%Y %H:%i') AS `fecha_entrega_fmt`,
                date_format(`oe`.`fecha_modificacion`,'%d/%m/%Y %H:%i') AS `fecha_modificacion_fmt`
            from ((((`ordenesempresas` `oe` 
                join `empresas` `emp` on((`oe`.`empresa_id` = `emp`.`id`))) 
                left join `equipos` `e` on((`oe`.`equipo_id` = `e`.`id`))) 
                left join `usuarios` `u` on((`oe`.`tecnico_id` = `u`.`id`))) 
                left join `sucursales` `s` on((`oe`.`sucursal_id` = `s`.`id`)))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion_lote_ordenes');
        Schema::dropIfExists('facturacion_lotes');

        if (Schema::hasTable('ordenes')) {
            Schema::table('ordenes', function (Blueprint $table) {
                if (Schema::hasColumn('ordenes', 'valor_facturado')) {
                    $table->dropColumn('valor_facturado');
                }
                if (Schema::hasColumn('ordenes', 'nro_autorizacion_factura')) {
                    $table->dropColumn('nro_autorizacion_factura');
                }
            });
        }

        if (Schema::hasTable('ordenesempresas')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                if (Schema::hasColumn('ordenesempresas', 'valor_facturado')) {
                    $table->dropColumn('valor_facturado');
                }
                if (Schema::hasColumn('ordenesempresas', 'nro_autorizacion_factura')) {
                    $table->dropColumn('nro_autorizacion_factura');
                }
                if (Schema::hasColumn('ordenesempresas', 'fecha_facturacion')) {
                    $table->dropColumn('fecha_facturacion');
                }
                if (Schema::hasColumn('ordenesempresas', 'nro_factura')) {
                    $table->dropColumn('nro_factura');
                }
            });
        }
    }
};
