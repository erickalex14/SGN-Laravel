<?php
namespace App\Repositories\Contracts\Directory;
use Illuminate\Database\Eloquent\Collection;

interface SucursalRepositoryInterface
{
    public function crear(array $datos): object;
    public function obtenerTodas(): Collection;
    public function buscarPorId(int $id): ?object;
    public function actualizar(int $id, array $datos): bool;
    public function eliminar(int $id): bool;
}
