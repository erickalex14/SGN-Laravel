<?php

namespace App\Repositories\Operations;

use App\Models\Operations\Informe;
use App\Models\Operations\InformeFoto;
use App\Models\Operations\Orden;
use Illuminate\Database\Eloquent\Collection;

class InformeRepository
{
    public function obtenerOrdenesSinInforme(int $tecnicoId): Collection
    {
        // Obtiene ordenes asignadas al tecnico que no tienen un informe finalizado
        return Orden::with(['cliente', 'equipo'])
            ->where('tecnico_id', $tecnicoId)
            ->whereDoesntHave('informes')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function obtenerInformesPorTecnico(int $tecnicoId): Collection
    {
        return Informe::with(['orden', 'orden.cliente', 'orden.equipo'])
            ->where('tecnico_id', $tecnicoId)
            ->orderBy('fecha_creacion', 'desc')
            ->get();
    }

    public function buscarPorOrdenId(int $ordenId): ?Informe
    {
        return Informe::with('fotos')->where('orden_id', $ordenId)->first();
    }
}