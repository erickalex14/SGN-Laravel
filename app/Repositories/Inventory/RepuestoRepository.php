<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Repuesto;
use Illuminate\Database\Eloquent\Collection;

class RepuestoRepository
{
    public function obtenerTodos(): Collection
    {
        // Se omite la carga de relaciones Eloquent para marca y tipo_dispositivo
        // debido a la inconsistencia de tipos de datos en el esquema Legacy (varchar vs int).
        // La vista manejara el mapeo visual mediante los catalogos cargados.
        return Repuesto::orderBy('nombre', 'asc')->get();
    }

    public function buscarPorId(int $id): ?Repuesto
    {
        return Repuesto::find($id);
    }

    public function existeCodigo(string $codigo, ?int $excluirId = null): bool
    {
        $query = Repuesto::where('codigo', $codigo);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }
}
