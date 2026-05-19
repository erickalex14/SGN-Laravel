<?php

namespace App\Repositories\Operations;

use App\Models\Operations\PrecioEstandar;
use Illuminate\Database\Eloquent\Collection;

class PrecioEstandarRepository
{
    public function obtenerTodos(): Collection
    {
        return PrecioEstandar::orderBy('servicio', 'asc')->get();
    }

    public function buscarPorId(int $id): ?PrecioEstandar
    {
        return PrecioEstandar::find($id);
    }
}
