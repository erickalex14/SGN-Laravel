<?php

namespace App\Repositories\Directory;

use App\Models\Directory\Empresa;
use Illuminate\Database\Eloquent\Collection;

class EmpresaRepository
{
    //Listar todas las empresas
    public function obtenerTodas(): Collection
    {
        return Empresa::select('id', 'nombre', 'ruc', 'telefono', 'correo', 'direccion_empresa')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    //Verificar que exista ruc
    public function existeRuc(string $ruc, ?int $excluirId = null): bool
    {
        $query = Empresa::where('ruc', $ruc);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

    //Buscar por ID
    public function buscarPorId(int $id): ?Empresa
    {
        return Empresa::find($id);
    }
}
