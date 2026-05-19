<?php

namespace App\Services\Operations;

use App\Repositories\Operations\TipoServicioRepository;
use App\DTOs\Operations\TipoServicioDTO;
use App\Models\Operations\TipoServicio;
use Illuminate\Support\Facades\Log;
use Exception;

class TipoServicioService
{
    protected TipoServicioRepository $repository;

    public function __construct(TipoServicioRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function procesar(TipoServicioDTO $dto, string $accion): void
    {
        if ($accion === 'eliminar') {
            $this->eliminar($dto->id);
            return;
        }

        if ($accion === 'editar') {
            $tipo = $this->repository->buscarPorId($dto->id);
            if (!$tipo) {
                throw new Exception('Tipo de servicio no encontrado.');
            }
        } else {
            $tipo = new TipoServicio();
        }

        $tipo->nombre      = trim($dto->nombre);
        $tipo->precio      = $dto->precio;
        $tipo->descripcion = trim($dto->descripcion);
        $tipo->activo      = $dto->activo;

        $tipo->save();

        Log::info('Tipo de servicio procesado.', [
            'id'     => $tipo->id,
            'accion' => $accion
        ]);
    }

    /**
     * @throws Exception
     */
    private function eliminar(int $id): void
    {
        $tipo = $this->repository->buscarPorId($id);
        if (!$tipo) {
            throw new Exception('Tipo de servicio no encontrado.');
        }

        try {
            $tipo->delete();
            Log::info('Tipo de servicio eliminado.', ['id' => $id]);
        } catch (Exception $e) {
            Log::error('Restriccion de integridad al eliminar tipo de servicio.', ['id' => $id, 'error' => $e->getMessage()]);
            throw new Exception('No es posible eliminar el registro debido a dependencias asociadas en órdenes.');
        }
    }
}
