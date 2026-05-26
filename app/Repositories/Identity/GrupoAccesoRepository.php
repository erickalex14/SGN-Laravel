<?php

namespace App\Repositories\Identity;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\PermisoGrupo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
class GrupoAccesoRepository
{
    //Obtener los usuarios en el grupo de acceso
    public function obtenerTodos(): Collection
    {
        // Replicando la consulta que trae el conteo de usuarios
        return GrupoAcceso::withCount('usuarios')->orderBy('nombre', 'asc')->get();
    }

    //Buscar por id
    public function buscarPorId(int $id): ?GrupoAcceso
    {
        return GrupoAcceso::find($id);
    }

    //Validar si tiene usuarios
    public function tieneUsuarios(int $id): bool
    {
        $grupo = $this->buscarPorId($id);
        return $grupo ? $grupo->usuarios()->exists() : false;
    }

    //Obtener permisos
    public function obtenerPermisos(int $grupoId): Collection
    {
        return PermisoGrupo::where('grupo_id', $grupoId)->get();
    }

    //Sincronizar Permisos
    public function sincronizarPermisos(int $grupoId, array $permisosEstructurados): void
    {
        DB::transaction(function () use ($grupoId, $permisosEstructurados) {
            //Eliminar permisos anteriores
            PermisoGrupo::where('grupo_id', $grupoId)->delete();

            //Insertar nuevos permisos
            $insertData = [];
            foreach ($permisosEstructurados as $modulo => $acciones) {
                foreach ($acciones as $accion => $permitido) {
                    if ($permitido) {
                        $insertData[] = [
                            'grupo_id' => $grupoId,
                            'modulo' => $modulo,
                            'accion' => $accion,
                            'permitido' => 1
                        ];
                    }
                }
            }
            if (!empty($insertData)) {
                PermisoGrupo::insert($insertData);
            }
        });

    }

}
