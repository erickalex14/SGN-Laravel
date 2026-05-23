<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\TipoDispositivo;
use Illuminate\Database\Eloquent\Collection;

class TipoDispositivoRepository
{
    public function obtenerTodos(): Collection
    {
        return TipoDispositivo::orderBy('nombre', 'asc')->get();
    }

    public function buscarPorId(int $id): ?TipoDispositivo
    {
        return TipoDispositivo::find($id);
    }

    public function existeCodigoONombre(string $codigo, string $nombre, ?int $excluirId = null): ?string
    {
        $queryCodigo = TipoDispositivo::where('codigo', $codigo);
        $queryNombre = TipoDispositivo::where('nombre', $nombre);

        if ($excluirId) {
            $queryCodigo->where('id', '!=', $excluirId);
            $queryNombre->where('id', '!=', $excluirId);
        }

        if ($queryCodigo->exists()) return 'código';
        if ($queryNombre->exists()) return 'nombre';

        return null;
    }
}
