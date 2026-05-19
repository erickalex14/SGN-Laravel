<?php

namespace App\Repositories\Operations;

use App\Models\Operations\TipoServicio;
use Illuminate\Database\Eloquent\Collection;

class TipoServicioRepository
{
    public function obtenerTodos(): Collection
    {
        return TipoServicio::orderBy('nombre', 'asc')->get();
    }

    public function buscarPorId(int $id): ?TipoServicio
    {
        return TipoServicio::find($id);
    }
}
