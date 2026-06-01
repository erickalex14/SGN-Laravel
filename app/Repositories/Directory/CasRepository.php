<?php

namespace App\Repositories\Directory;
use App\Models\Directory\Cas;
use Illuminate\Database\Eloquent\Collection;

class CasRepository
{
    //Obtiene todos los CAS, incluyendo los inactivos, ordenados por nombre
    public function obtenerTodOs(): Collection
    {
        return Cas::select('id', 'nombre', 'prefijo', 'marca', 'telefono', 'correo', 'direccion', 'ciudad', 'contacto', 'notas', 'activo')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    //Obtiene solo los CAS activos, ordenados por nombre
    public function obtenerActivos(): Collection
    {
        return Cas::select('id', 'nombre', 'prefijo', 'marca', 'telefono', 'correo', 'ciudad', 'contacto')
            ->where('activo', 1)
            ->orderBy('nombre', 'asc')
            ->get();
    }

    //Obtiene un CAS por su ID
    public function buscarPorId(int $id): ?Cas
    {
        return Cas::find($id);
    }

    //Verifica si existe un CAS con el mismo nombre (excluyendo el ID actual para actualizaciones)
    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $query = Cas::where('nombre', $nombre);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

}
