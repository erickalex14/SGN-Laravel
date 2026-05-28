<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\ProductoInventario;
use Illuminate\Database\Eloquent\Collection;

class ProductoRepository
{
    public function obtenerTodos(): Collection
    {
        return ProductoInventario::with(['marca', 'tipoDispositivo'])
            ->orderByDesc('id')
            ->get();
    }

    public function buscarPorId(int $id): ?ProductoInventario
    {
        return ProductoInventario::find($id);
    }

    public function buscarPorCodigo(string $codigo): ?ProductoInventario
    {
        $codigoNormalizado = $this->normalizarCodigo($codigo);

        return ProductoInventario::with(['marca', 'tipoDispositivo'])
            ->whereRaw('UPPER(TRIM(codigo)) = ?', [$codigoNormalizado])
            ->first();
    }

    public function existeCodigo(string $codigo, ?int $excluirId = null): bool
    {
        $query = ProductoInventario::whereRaw('UPPER(TRIM(codigo)) = ?', [$this->normalizarCodigo($codigo)]);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

    private function normalizarCodigo(string $codigo): string
    {
        return strtoupper(trim($codigo));
    }
}
