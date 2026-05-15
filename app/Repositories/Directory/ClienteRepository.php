<?php

namespace App\Repositories\Directory;

use App\Models\Directory\Cliente;
use App\Repositories\Contracts\Directory\ClienteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ClienteRepository implements ClienteRepositoryInterface
{
    public function crear(array $datos): object
    {
        return Cliente::create($datos);
    }
    public function obtenerTodos(): Collection
    {
        return Cliente::orderBy('apellidos', 'asc')->get();
    }

    public function buscarPorId(int $id): ?object
    {
        return Cliente::find($id);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $cliente = $this->buscarPorId($id);
        return $cliente ? $cliente->update($datos) : false;
    }

    public function eliminar(int $id): bool
    {
        $cliente = $this->buscarPorId($id);
        return $cliente ? $cliente->delete() : false;
    }

    public function buscarPorIdentificacion(string $identificacion): ?object
    {
        return Cliente::where('identificacion', $identificacion)->first();
    }
}
