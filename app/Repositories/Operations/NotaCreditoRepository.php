<?php

namespace App\Repositories\Operations;

use App\Models\Operations\SolicitudNc;
use App\Models\Operations\Orden;
use Illuminate\Database\Eloquent\Collection;

class NotaCreditoRepository
{
    public function obtenerTodas(): Collection
    {
        return SolicitudNc::with('orden')->orderBy('creado_en', 'desc')->get();
    }

    public function obtenerPorTecnico(int $tecnicoId): Collection
    {
        return SolicitudNc::with('orden')
            ->where('tecnico_id', $tecnicoId)
            ->orderBy('creado_en', 'desc')
            ->get();
    }

    public function buscarPorId(int $id): ?SolicitudNc
    {
        return SolicitudNc::find($id);
    }

    public function existeSolicitudPendienteParaOrden(int $ordenId): bool
    {
        return SolicitudNc::where('orden_id', $ordenId)
            ->where('estado', 'Pendiente')
            ->exists();
    }

    public function generarNumeroSolicitud(): string
    {
        $ultima = SolicitudNc::orderBy('id', 'desc')->first();
        $secuencial = 1;
        
        if ($ultima && preg_match('/NC-(\d+)/', $ultima->nro_solicitud, $matches)) {
            $secuencial = (int)$matches[1] + 1;
        }

        return 'NC-' . str_pad($secuencial, 5, '0', STR_PAD_LEFT);
    }

    public function contarSolicitudesNcPendientes(): int
    {
        return SolicitudNc::where('estado', 'Pendiente')->count();
    }
}
