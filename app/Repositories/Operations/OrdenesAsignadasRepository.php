<?php

namespace App\Repositories\Operations;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrdenesAsignadasRepository
{
    public function obtenerTecnicosConOrdenes(bool $esSuperadmin, int $sucursalSesion): Collection
    {
        return DB::table('usuarios as u')
            ->join('vista_ordenes as vo', 'vo.tecnico_id', '=', 'u.id')
            ->selectRaw(
                "u.id, u.nombre_tecnico,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(vo.estado_orden, ''))) = 'PENDIENTE' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(vo.estado_orden, ''))) IN ('EN PROCESO','EN_PROCESO') THEN 1 ELSE 0 END) AS en_proceso,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(vo.estado_orden, ''))) = 'ENTREGADA' THEN 1 ELSE 0 END) AS entregadas,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(vo.estado_orden, ''))) NOT IN ('ENTREGADA','NOTA DE CREDITO') THEN 1 ELSE 0 END) AS activas"
            )
            ->when(!$esSuperadmin && $sucursalSesion > 0, function ($q) use ($sucursalSesion) {
                $q->where('u.sucursal_id', $sucursalSesion);
            })
            ->groupBy('u.id', 'u.nombre_tecnico')
            ->orderByRaw('(pendientes + en_proceso) DESC')
            ->orderBy('u.nombre_tecnico')
            ->get();
    }

    public function obtenerOrdenesPorTecnico(int $tecnicoId, bool $entregadas): Collection
    {
        return DB::table('vista_ordenes as vo')
            ->selectRaw(
                'vo.orden_id, vo.nro_orden, vo.tipo_orden, vo.estado_orden, vo.estado_repuesto, vo.fecha_de_ingreso,' .
                " vo.fecha_prometido, vo.motivo_ingreso, vo.estado_garantia,
                vo.cliente, vo.tipo, vo.marca, vo.modelo, vo.serie"
            )
            ->where('vo.tecnico_id', $tecnicoId)
            ->when(
                $entregadas,
                fn ($q) => $q->where('vo.estado_orden', '=', 'Entregada'),
                fn ($q) => $q->where('vo.estado_orden', '!=', 'Entregada')
            )
            ->orderByDesc('vo.fecha_de_ingreso')
            ->get();
    }
}
