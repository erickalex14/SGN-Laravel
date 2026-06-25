<?php

namespace App\Repositories\Identity;

use App\Models\Identity\ActividadDiaria;
use Illuminate\Database\Eloquent\Collection;

class ActividadDiariaRepository
{
    public function guardar(array $data): ActividadDiaria
    {
        return ActividadDiaria::create($data);
    }

    public function obtenerPorUsuarioYFecha(int $usuarioId, string $fecha): Collection
    {
        return ActividadDiaria::where('usuario_id', $usuarioId)
            ->where('fecha', $fecha)
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    public function obtenerPorFecha(string $fecha): Collection
    {
        return ActividadDiaria::with('usuario')
            ->where('fecha', $fecha)
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }
}
