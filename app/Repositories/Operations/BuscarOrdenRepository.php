<?php

namespace App\Repositories\Operations;

use App\DTOs\Operations\BuscarOrdenDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BuscarOrdenRepository
{
    /**
     * Ejecuta la búsqueda unificada sobre vista_ordenes.
     * Soporta: nro_orden, cedula, nombre, serie, factura, tecnico, empresa.
     * Aplica filtros opcionales: estado, tecnico_id, fecha_desde, fecha_hasta.
     */
    public function buscar(BuscarOrdenDTO $dto): Collection
    {
        // Collation fijada explícitamente para evitar SQLSTATE 1267
        // cuando el servidor usa utf8mb4_0900_ai_ci y el driver otra collation.
        $collate = 'COLLATE utf8mb4_0900_ai_ci';

        $query = DB::table('vista_ordenes as vo')
            ->leftJoin('ordenesempresas as oe', function ($join) {
                $join->on('oe.id', '=', 'vo.orden_id')
                     ->whereRaw("vo.tipo_orden COLLATE utf8mb4_0900_ai_ci = 'empresa'");
            })
            ->leftJoin('informes as inf', function ($join) {
                $join->on('inf.orden_id', '=', DB::raw(
                    "CASE WHEN vo.tipo_orden COLLATE utf8mb4_0900_ai_ci = 'empresa' THEN -vo.orden_id ELSE vo.orden_id END"
                ));
            })
            ->select([
                'vo.orden_id',
                'vo.nro_orden',
                'vo.tipo_orden',
                'vo.estado_orden',
                'vo.estado_repuesto',
                'vo.estado_garantia',
                DB::raw("CASE WHEN vo.tipo_orden COLLATE utf8mb4_0900_ai_ci = 'empresa' THEN oe.nro_ticket ELSE vo.nro_factura END as nro_factura"),
                'vo.nro_factura_2',
                'vo.motivo_ingreso',
                'vo.nro_sucursal_cliente',
                'vo.tecnico_id',
                'vo.cliente_id',
                'vo.empresa_id',
                'vo.equipo_id',
                DB::raw("(SELECT producto_inventario_codigo FROM equipos WHERE id = vo.equipo_id) as producto_inventario_codigo"),
                'vo.cliente',
                'vo.nombres',
                'vo.apellidos',
                'vo.identificacion',
                'vo.numero_contacto',
                'vo.correo',
                'vo.tipo',
                'vo.marca',
                'vo.modelo',
                DB::raw("COALESCE((SELECT GROUP_CONCAT(serie ORDER BY orden SEPARATOR ' | ') FROM equiposseries WHERE equipo_id = vo.equipo_id), vo.serie) as serie"),
                'vo.falla',
                'vo.observacion',
                'vo.fecha_facturacion',
                DB::raw('vo.fecha_de_ingreso_fmt as fecha_de_ingreso'),
                DB::raw('vo.fecha_entrega_fmt as fecha_entrega'),
                'vo.tecnico',
                'vo.sucursal',
                'inf.id as informe_id',
                'inf.antecedentes',
                'inf.proceso',
                'inf.conclusion',
                'inf.recomendaciones',
                'inf.estado_equipo',
                DB::raw("DATE_FORMAT(inf.fecha_informe, '%d/%m/%Y') as fecha_informe"),
            ]);

        // ── Criterio de búsqueda principal ──────────────────────────
        $q     = trim($dto->q);
        $qLike = '%' . $q . '%';

        switch ($dto->tipo) {
            case 'nro_orden':
                $query->where(function ($inner) use ($q, $qLike) {
                    $inner->whereRaw("vo.nro_orden COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                    if (is_numeric($q)) {
                        $inner->orWhereRaw(
                            "SUBSTRING_INDEX(vo.nro_orden COLLATE utf8mb4_0900_ai_ci, '-', -1) LIKE ?",
                            [$qLike]
                        );
                    }
                });
                break;

            case 'cedula':
                $query->whereRaw("vo.identificacion COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;

            case 'nombre':
                $query->where(function ($inner) use ($qLike) {
                    $inner->whereRaw("vo.nombres   COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike])
                          ->orWhereRaw("vo.apellidos COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike])
                          ->orWhereRaw("vo.cliente   COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                });
                break;

            case 'serie':
                $query->whereRaw("vo.serie COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;

            case 'factura':
                $query->where(function ($inner) use ($qLike) {
                    $inner->whereRaw("vo.nro_factura   COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike])
                          ->orWhereRaw("vo.nro_factura_2 COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike])
                          ->orWhereRaw("oe.nro_ticket    COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                });
                break;

            case 'tecnico':
                $query->whereRaw("vo.tecnico COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;

            case 'empresa':
                $query->whereRaw("vo.tipo_orden COLLATE utf8mb4_0900_ai_ci = 'empresa'")
                      ->whereRaw("vo.cliente COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
        }

        // ── Filtros adicionales ──────────────────────────────────────
        if ($dto->estado !== '') {
            $query->whereRaw("vo.estado_orden COLLATE utf8mb4_0900_ai_ci = ?", [$dto->estado]);
        }

        if ($dto->tecnico_id > 0) {
            $query->where('vo.tecnico_id', '=', $dto->tecnico_id);
        }

        if ($dto->fecha_desde !== '') {
            $query->whereRaw("DATE(vo.fecha_de_ingreso) >= ?", [$dto->fecha_desde]);
        }

        if ($dto->fecha_hasta !== '') {
            $query->whereRaw("DATE(vo.fecha_de_ingreso) <= ?", [$dto->fecha_hasta]);
        }

        // ── Scope por sucursal (no superadmin) ───────────────────────
        if (!$dto->es_superadmin && $dto->sucursal_id > 0) {
            $query->where('vo.sucursal_id', '=', $dto->sucursal_id);
        }

        return $query
            ->orderByDesc('vo.fecha_de_ingreso')
            ->limit(100)
            ->get();
    }

    /**
     * Obtiene lista plana de técnicos activos para el selector del formulario.
     */
    public function obtenerTecnicos(int $sucursalId, bool $esSuperadmin): Collection
    {
        return DB::table('usuarios as u')
            ->select(['u.id', 'u.nombre_tecnico'])
            ->where('u.activo', 1)
            ->when(!$esSuperadmin && $sucursalId > 0, fn ($q) => $q->where('u.sucursal_id', $sucursalId))
            ->orderBy('u.nombre_tecnico')
            ->get();
    }

    /**
     * Lista de estados únicos para el selector de filtro.
     */
    public function obtenerEstados(): array
    {
        return [
            'Pendiente',
            'En proceso',
            'Finalizada',
            'Entregada',
            'Nota de credito',
        ];
    }
}
