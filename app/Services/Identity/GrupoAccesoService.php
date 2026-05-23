<?php

namespace App\Services\Identity;

use App\Repositories\Identity\GrupoAccesoRepository;
use App\DTOs\Identity\GrupoAccesoDTO;
use App\Models\Identity\GrupoAcceso;
use Illuminate\Support\Facades\Log;
use Exception;

class GrupoAccesoService
{
    protected GrupoAccesoRepository $repository;
    public function __construct(GrupoAccesoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */

    //Guardar o actualizar iun nuevo grupo
    public function guardarGrupo(GrupoAccesoDTO $dto): string
    {
        if ($dto->id){
            $grupo = $this->repository->buscarPorId($dto->id);
            if (!$grupo) throw new Exception('El grupo de acceso no existe.');
            $mensaje = 'Grupo de acceso actualizado correctamente.';
        } else {
            $grupo = new GrupoAcceso();
            $mensaje = 'Grupo de acceso registrado correctamente.';
        }

        $grupo->nombre        = $dto->nombre;
        $grupo->descripcion   = $dto->descripcion;
        $grupo->es_superadmin = $dto->es_superadmin;
        $grupo->save();

        Log::info('Grupo de acceso gestionado exitosamente.', ['grupo_id' => $grupo->id]);
        return $mensaje;
    }

    /**
     * @throws Exception
     */

    //Eliminar un grupo de acceso, validando que no tenga usuarios asignados
    public function eliminarGrupo(int $id): void
    {
        if ($this->repository->tieneUsuarios($id)) {
            Log::warning('Intento de eliminacion de grupo con usuarios activos.', ['grupo_id' => $id]);
            throw new Exception('No se puede eliminar el grupo porque tiene usuarios asignados.');
        }

        $grupo = $this->repository->buscarPorId($id);
        if (!$grupo) throw new Exception('Grupo no encontrado.');

        // Eloquent manejara la eliminacion en cascada si la base de datos lo permite,
        // o podemos forzar la eliminacion de permisos antes
        $grupo->permisos()->delete();
        $grupo->delete();

        Log::info('Grupo de acceso eliminado.', ['grupo_id' => $id]);
    }

    public function guardarPermisos(int $grupoId, array $permisos): void
    {
        $this->repository->sincronizarPermisos($grupoId, $permisos);
        Log::info('Permisos del grupo de acceso actualizados.', ['grupo_id' => $grupoId]);
    }
}
