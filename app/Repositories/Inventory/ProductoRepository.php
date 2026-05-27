<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\ProductoInventario;
use Illuminate\Database\Eloquent\Collection;

class ProductoRepository
{
    public function obtenerTodos(): Collection
    {
        return ProductoInventario::with(['marca', 'tipoDispositivo'])
            ->orderBy('descripcion', 'asc')
            ->get();
    }

    public function buscarPorId(int $id): ?ProductoInventario
    {
        return ProductoInventario::find($id);
    }

    public function buscarPorCodigo(string $codigo): ?ProductoInventario
    {
        return ProductoInventario::with(['marca', 'tipoDispositivo'])
            ->where('codigo', strtoupper(trim($codigo)))
            ->first();
    }

    public function existeCodigo(string $codigo, ?int $excluirId = null): bool
    {
        $query = ProductoInventario::where('codigo', $codigo);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }
}
