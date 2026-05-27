<?php

namespace App\Repositories\Operations;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrdenesAsignadasRepository
{
    public function obtenerTecnicosConOrdenes(bool $esSuperadmin, int $sucursalSesion): Collection
    {
        return DB::table('usuarios as u')
            ->join('ordenes as o', 'o.tecnico_id', '=', 'u.id')
            ->selectRaw(
                "u.id, u.nombre_tecnico,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(o.estado_orden, ''))) = 'PENDIENTE' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(o.estado_orden, ''))) IN ('EN PROCESO','EN_PROCESO') THEN 1 ELSE 0 END) AS en_proceso,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(o.estado_orden, ''))) = 'ENTREGADA' THEN 1 ELSE 0 END) AS entregadas,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(o.estado_orden, ''))) NOT IN ('ENTREGADA','NOTA DE CREDITO') THEN 1 ELSE 0 END) AS activas"
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
        return DB::table('ordenes as o')
            ->join('clientes as c', 'o.cliente_id', '=', 'c.id')
            ->join('equipos as e', 'o.equipo_id', '=', 'e.id')
            ->selectRaw(
                'o.id as orden_id, o.nro_orden, o.estado_orden, o.estado_repuesto, o.fecha_de_ingreso,' .
                " o.fecha_prometido, o.motivo_ingreso, o.estado_garantia,
                CONCAT(c.nombres, ' ', c.apellidos) as cliente, e.tipo, e.marca, e.modelo, e.serie"
            )
            ->where('o.tecnico_id', $tecnicoId)
            ->when(
                $entregadas,
                fn ($q) => $q->where('o.estado_orden', '=', 'Entregada'),
                fn ($q) => $q->where('o.estado_orden', '!=', 'Entregada')
            )
            ->orderByDesc('o.fecha_de_ingreso')
            ->get();
    }
}
