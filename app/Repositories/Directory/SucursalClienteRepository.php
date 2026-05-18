<?php

namespace App\Repositories\Directory;
use App\Models\Directory\SucursalCliente;
use Illuminate\Database\Eloquent\Collection;

class SucursalClienteRepository
{
    //Listar todas las entidades sucursal-cliente ordenadas por número de sucursal
    public function obtenerTodas(): Collection
    {
        return SucursalCliente::orderBy('numero', 'asc')->get();
    }

    //validar si existe el numero de sucursal antes de seguir
    public function existeNumero(int $numero): bool
    {
        return SucursalCliente::where('numero', $numero)->exists();
    }

    //Buscar entidad por id
    public function buscarPorId(int $id): ?SucursalCliente
    {
        return SucursalCliente::find($id);
    }

    //Validar que exista el codigo
    public function existeCodigo(string $codigo): bool
    {
        return SucursalCliente::where('codigo', $codigo)->exists();
    }


}
