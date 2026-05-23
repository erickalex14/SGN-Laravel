<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Marca;
use Illuminate\Database\Eloquent\Collection;

class MarcaRepository
{
    public function obtenerTodas(): Collection
    {
        return Marca::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
    }

    public function buscarPorId(int $id): ?Marca
    {
        return Marca::find($id);
    }

    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $query = Marca::where('nombre', $nombre);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }
}
