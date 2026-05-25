<?php

namespace App\Repositories\Operations;

use App\Models\Operations\SolicitudRepuesto;
use Illuminate\Database\Eloquent\Collection;

class SolicitudRepuestoRepository
{
    public function obtenerTodas(): Collection
    {
        return SolicitudRepuesto::with(['orden', 'repuestoAsignado'])
            ->orderBy('fecha_solicitud', 'desc')
            ->get();
    }

    public function obtenerPorTecnico(int $tecnicoId): Collection
    {
        return SolicitudRepuesto::with('orden')
            ->where('tecnico_id', $tecnicoId)
            ->orderBy('fecha_solicitud', 'desc')
            ->get();
    }

    public function buscarPorId(int $id): ?SolicitudRepuesto
    {
        return SolicitudRepuesto::find($id);
    }

    public function generarNumeroSolicitud(): string
    {
        $ultima = SolicitudRepuesto::orderBy('id', 'desc')->first();
        $sec = 1;
        if ($ultima && preg_match('/SR-(\d+)/', $ultima->nro_solicitud, $matches)) {
            $sec = (int)$matches[1] + 1;
        }
        return 'SR-' . str_pad($sec, 5, '0', STR_PAD_LEFT);
    }

    public function contarSolicitudesPendientes(): int
    {
        return SolicitudRepuesto::where('estado', 'Pendiente')->count();
    }
}
