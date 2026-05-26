<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\ListaCompra;
use App\Models\Operations\SolicitudRepuesto;
use Illuminate\Database\Eloquent\Collection;

class ListaCompraRepository
{
    public function obtenerTodas(): Collection
    {
        return ListaCompra::orderBy('id', 'desc')->get();
    }

    public function obtenerSolicitudesPendientesDeCompra(): Collection
    {
        // Traemos las solicitudes que bodega envio a compras y aun no estan en una lista
        return SolicitudRepuesto::with('orden')
            ->where('estado', 'COMPRA')
            ->whereNull('lista_compra_id')
            ->orderBy('fecha_solicitud', 'asc')
            ->get();
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
}