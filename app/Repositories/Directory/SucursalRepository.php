<?php

namespace App\Repositories\Directory;

use App\Models\Directory\Sucursal;
use Illuminate\Database\Eloquent\Collection;

class SucursalRepository
{
    public function obtenerTodas(): Collection
    {
        return Sucursal::select('id', 'nro_sucursal', 'secuencial', 'ciudad', 'nro_base')
            ->orderBy('nro_sucursal', 'asc')
            ->get();
    }

    public function buscarPorId(int $id): ?Sucursal
    {
        return Sucursal::find($id);
    }

    public function existeNroSucursal(int $nroSucursal, ?int $excluirId = null): bool
    {
        $query = Sucursal::where('nro_sucursal', $nroSucursal);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

}
