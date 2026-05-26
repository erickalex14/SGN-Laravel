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

    public function buscarParaOrden(string $q = '', bool $stockOnly = true): Collection
    {
        $query = Repuesto::query()
            ->select('id', 'codigo', 'nro_parte', 'nombre', 'descripcion', 'stock')
            ->orderBy('codigo', 'asc');

        $q = trim($q);
        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('codigo', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");
            });
        }

        if ($stockOnly) {
            $query->where('stock', '>', 0);
        }

        return $query->limit(40)->get();
    }
}
