<?php

namespace App\Repositories\Operations;

use App\DTOs\Operations\PresupuestoContextDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PresupuestoRepository
{
    public function obtenerOrdenes(PresupuestoContextDTO $contexto): Collection
    {
        $query = DB::table('ordenes as o')
            ->join('clientes as c', 'o.cliente_id', '=', 'c.id')
            ->join('equipos as e', 'o.equipo_id', '=', 'e.id')
            ->selectRaw(
                'o.id, o.nro_orden, o.estado_orden, o.motivo_ingreso, o.estado_garantia,' .
                " CONCAT(c.nombres, ' ', c.apellidos) AS cliente, e.tipo, e.marca, e.modelo"
            )
            ->orderByDesc('o.fecha_de_ingreso');

        if (!$contexto->es_admin) {
            if ($contexto->puede_ver_asignadas && $contexto->sucursal_id > 0) {
                $query->where('o.sucursal_id', $contexto->sucursal_id);
            } else {
                $query->where('o.tecnico_id', $contexto->tecnico_id);
            }
        }

        return $query->get();
    }

    public function obtenerCatalogoActivo(): Collection
    {
        return DB::table('preciosestandar')
            ->select('id', 'servicio', 'precio', 'descripcion')
            ->where('activo', 1)
            ->orderBy('servicio')
            ->get();
    }
}

