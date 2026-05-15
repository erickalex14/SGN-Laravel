<?php
namespace App\Repositories\Directory;

use App\Models\Directory\Sucursal;
use App\Repositories\Contracts\Directory\SucursalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;


class SucursalRepository implements SucursalRepositoryInterface
{
    public function crear(array $datos): object
    {
        return Sucursal::create($datos);
    }
    public function obtenerTodas(): Collection
    {
        return Sucursal::orderBy('nombre_sucursal', 'asc')->get();
    }

    public function buscarPorId(int $id): ?object
    {
        return Sucursal::find($id);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sucursal = $this->buscarPorId($id);
        return $sucursal ? $sucursal->update($datos) : false;
    }

    public function eliminar(int $id): bool
    {
        $sucursal = $this->buscarPorId($id);
        return $sucursal ? $sucursal->delete() : false;
    }
}
