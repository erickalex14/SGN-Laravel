<?php

namespace App\Repositories\Contracts\Directory;
use Illuminate\Database\Eloquent\Collection;

interface EmpresaRepositoryInterface
{
    public function obtenerTodas(): Collection;
    public function buscarPorId(int $id): ?object;
    public function buscarPorRuc(string $ruc): ?object;
    public function crear(array $datos): object;
    public function actualizar(int $id, array $datos): bool;
    public function eliminar(int $id): bool;
}
