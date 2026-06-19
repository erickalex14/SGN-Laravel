<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\ListaCompra;
use App\Models\Operations\SolicitudRepuesto;
use Illuminate\Database\Eloquent\Collection;

class ListaCompraRepository
{
    public function obtenerTodas(?int $sucursalId = null): Collection
    {
        $query = ListaCompra::query();
        if ($sucursalId !== null && $sucursalId > 0) {
            $query->whereHas('solicitudes.orden', function ($o) use ($sucursalId) {
                $o->where('sucursal_id', $sucursalId);
            });
        }
        return $query->orderBy('id', 'desc')->get();
    }

    public function obtenerSolicitudesPendientesDeCompra(?int $sucursalId = null): Collection
    {
        // Compatibilidad legacy:
        // - Datos antiguos: estado = 'COMPRA'
        // - Datos actuales (enum): estado = 'Aprobada' + sin repuesto asignado
        $query = SolicitudRepuesto::with('orden')
            ->where(function ($q) {
                $q->where('estado', 'COMPRA')
                    ->orWhere(function ($inner) {
                        $inner->where('estado', 'Aprobada')
                            ->whereNull('repuesto_id');
                    });
            })
            ->whereNull('lista_compra_id');

        if ($sucursalId !== null && $sucursalId > 0) {
            $query->whereHas('orden', function ($o) use ($sucursalId) {
                $o->where('sucursal_id', $sucursalId);
            });
        }

        return $query->orderBy('fecha_solicitud', 'asc')->get();
    }

    public function generarNumeroLista(): string
    {
        $ultima = ListaCompra::orderBy('id', 'desc')->first();
        $secuencial = 1;

        if ($ultima && preg_match('/LC-(\d+)/', $ultima->nro_lista, $matches)) {
            $secuencial = (int)$matches[1] + 1;
        }

        return 'LC-' . str_pad($secuencial, 5, '0', STR_PAD_LEFT);
    }

    public function buscarPorId(int $id): ?ListaCompra
    {
        return ListaCompra::find($id);
    }

    public function obtenerItemsPorLista(int $listaId): Collection
    {
        return SolicitudRepuesto::with([
            'orden',
            'orden.cliente',
            'orden.equipo',
            'orden.sucursal',
            'orden.tecnico',
        ])
            ->where('lista_compra_id', $listaId)
            ->orderBy('id', 'asc')
            ->get();
    }
}
