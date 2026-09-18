<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear el grupo 'Recepcion' si no existe
        $grupoRecepcion = DB::table('gruposacceso')->whereRaw('LOWER(TRIM(nombre)) = ?', ['recepcion'])->first();
        if (!$grupoRecepcion) {
            $grupoId = DB::table('gruposacceso')->insertGetId([
                'nombre' => 'Recepcion',
                'descripcion' => 'Rol de recepción - ingresa órdenes personales, monitorea y realiza la entrega al cliente',
                'es_superadmin' => 0,
                'created_at' => now(),
            ]);
        } else {
            $grupoId = $grupoRecepcion->id;
        }

        // 2. Asignar permisos al grupo Recepcion
        $permisosRecepcion = [
            ['modulo' => 'ordenes_crear', 'accion' => 'ver', 'permitido' => 1],
            ['modulo' => 'ordenes_crear', 'accion' => 'crear', 'permitido' => 1],
            ['modulo' => 'ordenes_recepcion', 'accion' => 'ver', 'permitido' => 1],
            ['modulo' => 'ordenes_recepcion', 'accion' => 'crear', 'permitido' => 1],
            ['modulo' => 'ordenes_recepcion', 'accion' => 'editar', 'permitido' => 1],
            ['modulo' => 'ordenes_buscar', 'accion' => 'ver', 'permitido' => 1],
            ['modulo' => 'informes', 'accion' => 'ver', 'permitido' => 1],
            ['modulo' => 'mi_cuenta', 'accion' => 'ver', 'permitido' => 1],
            ['modulo' => 'mi_cuenta', 'accion' => 'editar', 'permitido' => 1],
        ];

        foreach ($permisosRecepcion as $p) {
            DB::table('permisosgrupo')->updateOrInsert(
                ['grupo_id' => $grupoId, 'modulo' => $p['modulo'], 'accion' => $p['accion']],
                ['permitido' => $p['permitido']]
            );
        }

        // 3. Agregar columnas de timer a 'ordenes'
        if (Schema::hasTable('ordenes')) {
            Schema::table('ordenes', function (Blueprint $table) {
                if (!Schema::hasColumn('ordenes', 'fecha_recibida_tecnico')) {
                    $table->datetime('fecha_recibida_tecnico')->nullable()->after('fecha_de_ingreso');
                }
                if (!Schema::hasColumn('ordenes', 'fecha_lista_entrega')) {
                    $table->datetime('fecha_lista_entrega')->nullable()->after('fecha_finalizacion');
                }
            });
        }

        // 4. Agregar columnas de timer a 'ordenesempresas'
        if (Schema::hasTable('ordenesempresas')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                if (!Schema::hasColumn('ordenesempresas', 'fecha_recibida_tecnico')) {
                    $table->datetime('fecha_recibida_tecnico')->nullable()->after('fecha_ingreso');
                }
                if (!Schema::hasColumn('ordenesempresas', 'fecha_lista_entrega')) {
                    $table->datetime('fecha_lista_entrega')->nullable()->after('fecha_finalizacion');
                }
            });
        }

        // 5. Recrear vista_ordenes proyectando las nuevas columnas de timer
        DB::statement("DROP VIEW IF EXISTS vista_ordenes");
        DB::statement("CREATE VIEW vista_ordenes AS
            select 
                o.id AS orden_id,
                o.nro_orden AS nro_orden,
                'personal' AS tipo_orden,
                o.estado_orden AS estado_orden,
                o.estado_repuesto AS estado_repuesto,
                o.estado_garantia AS estado_garantia,
                o.motivo_ingreso AS motivo_ingreso,
                o.fecha_de_ingreso AS fecha_de_ingreso,
                o.fecha_recibida_tecnico AS fecha_recibida_tecnico,
                o.fecha_prometido AS fecha_prometido,
                o.fecha_finalizacion AS fecha_finalizacion,
                o.fecha_lista_entrega AS fecha_lista_entrega,
                o.fecha_entrega AS fecha_entrega,
                o.fecha_modificacion AS fecha_modificacion,
                o.nro_factura AS nro_factura,
                o.nro_factura_2 AS nro_factura_2,
                o.nro_sucursal_cliente AS nro_sucursal_cliente,
                o.tecnico_id AS tecnico_id,
                o.sucursal_id AS sucursal_id,
                o.ingresado_por AS ingresado_por,
                o.modificado_por AS modificado_por,
                o.cliente_id AS cliente_id,
                NULL AS empresa_id,
                o.equipo_id AS equipo_id,
                concat(c.nombres,' ',c.apellidos) AS cliente,
                c.nombres AS nombres,
                c.apellidos AS apellidos,
                c.identificacion AS identificacion,
                c.numero_contacto AS numero_contacto,
                c.correo AS correo,
                c.direccion_clientes AS direccion,
                e.tipo AS tipo,
                e.marca AS marca,
                e.modelo AS modelo,
                e.serie AS serie,
                e.falla AS falla,
                e.observacion AS observacion,
                o.memo_entrega AS memo_entrega,
                o.foto_evidencia_entrega AS foto_evidencia_entrega,
                date_format(e.fecha_facturacion,'%Y-%m-%d') AS fecha_facturacion,
                u.nombre_tecnico AS tecnico,
                s.ciudad AS sucursal,
                date_format(o.fecha_de_ingreso,'%d/%m/%Y %H:%i') AS fecha_de_ingreso_fmt,
                date_format(o.fecha_recibida_tecnico,'%d/%m/%Y %H:%i') AS fecha_recibida_tecnico_fmt,
                date_format(o.fecha_prometido,'%d/%m/%Y') AS fecha_prometido_fmt,
                date_format(o.fecha_finalizacion,'%d/%m/%Y %H:%i') AS fecha_finalizacion_fmt,
                date_format(o.fecha_lista_entrega,'%d/%m/%Y %H:%i') AS fecha_lista_entrega_fmt,
                date_format(o.fecha_entrega,'%d/%m/%Y %H:%i') AS fecha_entrega_fmt,
                date_format(o.fecha_modificacion,'%d/%m/%Y %H:%i') AS fecha_modificacion_fmt
            from ((((ordenes o 
                join clientes c on((o.cliente_id = c.id))) 
                join equipos e on((o.equipo_id = e.id))) 
                join usuarios u on((o.tecnico_id = u.id))) 
                join sucursales s on((o.sucursal_id = s.id))) 
            union all 
            select 
                oe.id AS orden_id,
                (oe.nro_orden collate utf8mb4_0900_ai_ci) AS nro_orden,
                'empresa' AS tipo_orden,
                (oe.estado collate utf8mb4_0900_ai_ci) AS estado_orden,
                'No requerido' AS estado_repuesto,
                NULL AS estado_garantia,
                (concat('Empresa · ',oe.subtipo) collate utf8mb4_0900_ai_ci) AS motivo_ingreso,
                oe.fecha_ingreso AS fecha_de_ingreso,
                oe.fecha_recibida_tecnico AS fecha_recibida_tecnico,
                oe.fecha_prometido AS fecha_prometido,
                oe.fecha_finalizacion AS fecha_finalizacion,
                oe.fecha_lista_entrega AS fecha_lista_entrega,
                oe.fecha_entrega AS fecha_entrega,
                oe.fecha_modificacion AS fecha_modificacion,
                NULL AS nro_factura,
                NULL AS nro_factura_2,
                NULL AS nro_sucursal_cliente,
                oe.tecnico_id AS tecnico_id,
                oe.sucursal_id AS sucursal_id,
                oe.ingresado_por AS ingresado_por,
                oe.modificado_por AS modificado_por,
                NULL AS cliente_id,
                oe.empresa_id AS empresa_id,
                oe.equipo_id AS equipo_id,
                (emp.nombre collate utf8mb4_0900_ai_ci) AS cliente,
                (emp.nombre collate utf8mb4_0900_ai_ci) AS nombres,
                '' AS apellidos,
                (emp.ruc collate utf8mb4_0900_ai_ci) AS identificacion,
                (emp.telefono collate utf8mb4_0900_ai_ci) AS numero_contacto,
                (emp.correo collate utf8mb4_0900_ai_ci) AS correo,
                (emp.direccion_empresa collate utf8mb4_0900_ai_ci) AS direccion,
                e.tipo AS tipo,
                e.marca AS marca,
                e.modelo AS modelo,
                e.serie AS serie,
                (oe.descripcion collate utf8mb4_0900_ai_ci) AS falla,
                (oe.descripcion collate utf8mb4_0900_ai_ci) AS observacion,
                (oe.memo_entrega collate utf8mb4_0900_ai_ci) AS memo_entrega,
                (oe.foto_evidencia_entrega collate utf8mb4_0900_ai_ci) AS foto_evidencia_entrega,
                NULL AS fecha_facturacion,
                u.nombre_tecnico AS tecnico,
                s.ciudad AS sucursal,
                date_format(oe.fecha_ingreso,'%d/%m/%Y %H:%i') AS fecha_de_ingreso_fmt,
                date_format(oe.fecha_recibida_tecnico,'%d/%m/%Y %H:%i') AS fecha_recibida_tecnico_fmt,
                date_format(oe.fecha_prometido,'%d/%m/%Y') AS fecha_prometido_fmt,
                date_format(oe.fecha_finalizacion,'%d/%m/%Y %H:%i') AS fecha_finalizacion_fmt,
                date_format(oe.fecha_lista_entrega,'%d/%m/%Y %H:%i') AS fecha_lista_entrega_fmt,
                date_format(oe.fecha_entrega,'%d/%m/%Y %H:%i') AS fecha_entrega_fmt,
                date_format(oe.fecha_modificacion,'%d/%m/%Y %H:%i') AS fecha_modificacion_fmt
            from ((((ordenesempresas oe 
                join empresas emp on((oe.empresa_id = emp.id))) 
                left join equipos e on((oe.equipo_id = e.id))) 
                left join usuarios u on((oe.tecnico_id = u.id))) 
                left join sucursales s on((oe.sucursal_id = s.id)))"
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('ordenes')) {
            Schema::table('ordenes', function (Blueprint $table) {
                if (Schema::hasColumn('ordenes', 'fecha_lista_entrega')) {
                    $table->dropColumn('fecha_lista_entrega');
                }
                if (Schema::hasColumn('ordenes', 'fecha_recibida_tecnico')) {
                    $table->dropColumn('fecha_recibida_tecnico');
                }
            });
        }

        if (Schema::hasTable('ordenesempresas')) {
            Schema::table('ordenesempresas', function (Blueprint $table) {
                if (Schema::hasColumn('ordenesempresas', 'fecha_lista_entrega')) {
                    $table->dropColumn('fecha_lista_entrega');
                }
                if (Schema::hasColumn('ordenesempresas', 'fecha_recibida_tecnico')) {
                    $table->dropColumn('fecha_recibida_tecnico');
                }
            });
        }
    }
};
