<?php

namespace App\Services\Directory;

use App\DTOs\Directory\SucursalDTO;
use App\Repositories\Contracts\Directory\SucursalRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class SucursalService
{
    protected SucursalRepositoryInterface $sucursalRepository;

    public function __construct(SucursalRepositoryInterface $sucursalRepository)
    {
        $this->sucursalRepository = $sucursalRepository;
    }

    public function registrarSucursal(SucursalDTO $dto): object
    {
        Log::info('Iniciando proceso de registro para nueva sucursal.', [
            'nombre_sucursal' => $dto->nombreSucursal
        ]);

        try {
            $sucursal = $this->sucursalRepository->crear($dto->toArray());

            Log::info('Sucursal registrada exitosamente en el sistema.', [
                'sucursal_id' => $sucursal->id
            ]);

            return $sucursal;

        } catch (Exception $e) {
            Log::error('Fallo critico al registrar la sucursal.', [
                'mensaje_error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function listarSucursales(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->sucursalRepository->obtenerTodas();
    }

    public function modificarSucursal(int $id, SucursalDTO $dto): bool
    {
        Log::info('Procesando actualizacion de datos para sucursal.', ['sucursal_id' => $id]);

        try {
            $actualizado = $this->sucursalRepository->actualizar($id, $dto->toArray());

            if (!$actualizado) {
                Log::warning('Se intento actualizar una sucursal inexistente.', ['sucursal_id' => $id]);
                throw new Exception('La sucursal indicada no se encuentra en los registros.');
            }

            Log::info('Sucursal actualizada con exito.', ['sucursal_id' => $id]);
            return true;

        } catch (Exception $e) {
            Log::error('Error critico al modificar la sucursal.', ['mensaje' => $e->getMessage()]);
            throw $e;
        }
    }

    public function removerSucursal(int $id): bool
    {
        Log::info('Solicitud de eliminacion de sucursal iniciada.', ['sucursal_id' => $id]);

        try {
            $eliminado = $this->sucursalRepository->eliminar($id);
            if (!$eliminado) {
                throw new Exception('No fue posible ubicar la sucursal para su eliminacion.');
            }

            Log::info('Sucursal eliminada de la base de datos de forma permanente.', ['sucursal_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error('Fallo al intentar eliminar la sucursal.', ['mensaje' => $e->getMessage()]);
            throw $e;
        }
    }
}
