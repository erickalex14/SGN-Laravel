<?php

namespace App\Repositories\Operations;

use App\Models\Operations\SolicitudNc;
use App\Models\Operations\Orden;
use Illuminate\Database\Eloquent\Collection;

class NotaCreditoRepository
{
    public function obtenerTodas(): Collection
    {
        return SolicitudNc::with(['orden.informes', 'tecnico'])->orderBy('creado_en', 'desc')->get();
    }

    public function obtenerPorTecnico(int $tecnicoId): Collection
    {
        return SolicitudNc::with(['orden.informes', 'tecnico'])
            ->where('tecnico_id', $tecnicoId)
            ->orderBy('creado_en', 'desc')
            ->get();
    }

    public function buscarPorId(int $id): ?SolicitudNc
    {
        return SolicitudNc::find($id);
    }

    public function buscarPorIdConRelaciones(int $id): ?SolicitudNc
    {
        return SolicitudNc::with([
            'orden',
            'orden.cliente',
            'orden.equipo',
            'tecnico',
        ])->find($id);
    }

    public function existeSolicitudPendienteParaOrden(int $ordenId): bool
    {
        return SolicitudNc::where('orden_id', $ordenId)
            ->where('estado', 'Pendiente')
            ->exists();
    }

    public function existeSolicitudParaOrden(int $ordenId): bool
    {
        return SolicitudNc::where('orden_id', $ordenId)->exists();
    }

    public function generarNumeroSolicitud(): string
    {
        $ultima = SolicitudNc::orderBy('id', 'desc')->value('nro_solicitud');
        $secuencial = 1;

        if (is_string($ultima) && preg_match('/SOL-NC-(\d+)/', $ultima, $matches)) {
            $secuencial = ((int) $matches[1]) + 1;
        } else {
            // Legacy: secuencial basado en total historico
            $secuencial = SolicitudNc::count() + 1;
        }

        return 'SOL-NC-' . str_pad((string) $secuencial, 6, '0', STR_PAD_LEFT);
    }

    public function contarSolicitudesNcPendientes(): int
    {
        return SolicitudNc::where('estado', 'Pendiente')->count();
    }
}
