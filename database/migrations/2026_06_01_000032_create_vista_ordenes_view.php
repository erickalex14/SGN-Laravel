<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
                `o`.`fecha_entrega` AS `fecha_entrega`,
                `o`.`nro_factura` AS `nro_factura`,
                `o`.`nro_factura_2` AS `nro_factura_2`,
                `o`.`nro_sucursal_cliente` AS `nro_sucursal_cliente`,
                `o`.`tecnico_id` AS `tecnico_id`,
                `o`.`sucursal_id` AS `sucursal_id`,
                `o`.`ingresado_por` AS `ingresado_por`,
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
                date_format(`e`.`fecha_facturacion`,'%Y-%m-%d') AS `fecha_facturacion`,
                `u`.`nombre_tecnico` AS `tecnico`,
                `s`.`ciudad` AS `sucursal`,
                date_format(`o`.`fecha_de_ingreso`,'%d/%m/%Y %H:%i') AS `fecha_de_ingreso_fmt`,
                date_format(`o`.`fecha_entrega`,'%d/%m/%Y') AS `fecha_entrega_fmt` 
            from ((((`ordenes` `o` 
                join `clientes` `c` on((`o`.`cliente_id` = `c`.`id`))) 
                join `equipos` `e` on((`o`.`equipo_id` = `e`.`id`))) 
                join `usuarios` `u` on((`o`.`tecnico_id` = `u`.`id`))) 
                join `sucursales` `s` on((`o`.`sucursal_id` = `s`.`id`))) 
            union all 
            select 
                `oe`.`id` AS `orden_id`,
                (`oe`.`nro_orden` collate utf8mb4_0900_ai_ci) AS `nro_orden`,
                'empresa' AS `tipo_orden`,
                (`oe`.`estado` collate utf8mb4_0900_ai_ci) AS `estado_orden`,
                'No requerido' AS `estado_repuesto`,
                NULL AS `estado_garantia`,
                (concat('Empresa · ',`oe`.`subtipo`) collate utf8mb4_0900_ai_ci) AS `motivo_ingreso`,
                `oe`.`fecha_ingreso` AS `fecha_de_ingreso`,
                NULL AS `fecha_entrega`,
                NULL AS `nro_factura`,
                NULL AS `nro_factura_2`,
                NULL AS `nro_sucursal_cliente`,
                `oe`.`tecnico_id` AS `tecnico_id`,
                `oe`.`sucursal_id` AS `sucursal_id`,
                `oe`.`ingresado_por` AS `ingresado_por`,
                NULL AS `cliente_id`,
                `oe`.`empresa_id` AS `empresa_id`,
                `oe`.`equipo_id` AS `equipo_id`,
                (`emp`.`nombre` collate utf8mb4_0900_ai_ci) AS `cliente`,
                (`emp`.`nombre` collate utf8mb4_0900_ai_ci) AS `nombres`,
                '' AS `apellidos`,
                (`emp`.`ruc` collate utf8mb4_0900_ai_ci) AS `identificacion`,
                (`emp`.`telefono` collate utf8mb4_0900_ai_ci) AS `numero_contacto`,
                (`emp`.`correo` collate utf8mb4_0900_ai_ci) AS `correo`,
                (`emp`.`direccion_empresa` collate utf8mb4_0900_ai_ci) AS `direccion`,
                `e`.`tipo` AS `tipo`,
                `e`.`marca` AS `marca`,
                `e`.`modelo` AS `modelo`,
                `e`.`serie` AS `serie`,
                (`oe`.`descripcion` collate utf8mb4_0900_ai_ci) AS `falla`,
                (`oe`.`descripcion` collate utf8mb4_0900_ai_ci) AS `observacion`,
                NULL AS `fecha_facturacion`,
                `u`.`nombre_tecnico` AS `tecnico`,
                `s`.`ciudad` AS `sucursal`,
                date_format(`oe`.`fecha_ingreso`,'%d/%m/%Y %H:%i') AS `fecha_de_ingreso_fmt`,
                NULL AS `fecha_entrega_fmt` 
            from ((((`ordenesempresas` `oe` 
                join `empresas` `emp` on((`oe`.`empresa_id` = `emp`.`id`))) 
                join `equipos` `e` on((`oe`.`equipo_id` = `e`.`id`))) 
                join `usuarios` `u` on((`oe`.`tecnico_id` = `u`.`id`))) 
                join `sucursales` `s` on((`oe`.`sucursal_id` = `s`.`id`)))"
        );
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vista_ordenes");
    }
};
