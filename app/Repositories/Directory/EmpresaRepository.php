<?php

namespace App\Repositories\Directory;

use App\Models\Directory\Empresa;
use App\Repositories\Contracts\Directory\EmpresaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EmpresaRepository implements EmpresaRepositoryInterface
{
    public function obtenerTodas(): Collection
    {
        return Empresa::orderBy('nombre', 'asc')->get();
    }

    public function buscarPorId(int $id): ?object
    {
        return Empresa::find($id);
    }

    public function buscarPorRuc(string $ruc): ?object
    {
        return Empresa::where('ruc', $ruc)->first();
    }

    public function crear(array $datos): object
    {
        return Empresa::create($datos);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $empresa = $this->buscarPorId($id);
        if (!$empresa) return false;

        return $empresa->update($datos);
    }

    public function eliminar(int $id): bool
    {
        $empresa = $this->buscarPorId($id);
        if (!$empresa) return false;

        return $empresa->delete();
    }
}
