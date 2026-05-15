<?php

namespace App\Repositories\Contracts\Directory;
use App\Models\Directory\Cliente;
use Illuminate\Database\Eloquent\Collection;

interface ClienteRepositoryInterface
{
    public function crear(array $datos): object;
    public function buscarPorIdentificacion(string $identificacion): ?object;
    public function obtenerTodos(): Collection;
    public function buscarPorId(int $id): ?object;
    public function actualizar(int $id, array $datos): bool;
    public function eliminar(int $id): bool;
}
