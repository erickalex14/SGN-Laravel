<?php

namespace App\Repositories\Operations;

use App\Models\Operations\SolicitudRepuesto;
use Carbon\Carbon;
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

    public function buscarPorIdConRelaciones(int $id): ?SolicitudRepuesto
    {
        return SolicitudRepuesto::with([
            'orden',
            'orden.cliente',
            'orden.equipo',
            'tecnico',
            'repuestoAsignado',
        ])->find($id);
    }

    public function existeSolicitudParaOrden(int $ordenId): bool
    {
        return SolicitudRepuesto::where('orden_id', $ordenId)->exists();
    }

    public function generarNumeroSolicitud(): string
    {
        $anio = Carbon::now('America/Guayaquil')->year;
        $sec = SolicitudRepuesto::whereYear('created_at', $anio)->count() + 1;

        $nro = 'SR-' . $anio . '-' . str_pad((string) $sec, 4, '0', STR_PAD_LEFT);
        if (!SolicitudRepuesto::where('nro_solicitud', $nro)->exists()) {
            return $nro;
        }

        // Fallback por si ya existe el secuencial del anio.
        $ultimoDelAnio = SolicitudRepuesto::where('nro_solicitud', 'like', "SR-{$anio}-%")
            ->orderByDesc('id')
            ->value('nro_solicitud');

        if (is_string($ultimoDelAnio) && preg_match('/SR-\d{4}-(\d+)/', $ultimoDelAnio, $matches)) {
            $sec = ((int) $matches[1]) + 1;
        }

        return 'SR-' . $anio . '-' . str_pad((string) $sec, 4, '0', STR_PAD_LEFT);
    }

    public function contarSolicitudesPendientes(): int
    {
        return SolicitudRepuesto::where('estado', 'Pendiente')->count();
    }
}
