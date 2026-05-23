<?php

namespace App\Services\Operations;

use App\Repositories\Operations\PrecioEstandarRepository;
use App\DTOs\Operations\PrecioEstandarDTO;
use App\Models\Operations\PrecioEstandar;
use Illuminate\Support\Facades\Log;
use Exception;

class PrecioEstandarService
{
    protected PrecioEstandarRepository $repository;

    public function __construct(PrecioEstandarRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function procesar(PrecioEstandarDTO $dto, string $accion): void
    {
        if ($accion === 'eliminar') {
            $this->eliminar($dto->id);
            return;
        }

        if ($accion === 'editar') {
            $precio = $this->repository->buscarPorId($dto->id);
            if (!$precio) {
                throw new Exception('Registro de precio estándar no encontrado.');
            }
        } else {
            $precio = new PrecioEstandar();
        }

        $precio->servicio    = trim($dto->servicio);
        $precio->precio      = $dto->precio;
        $precio->descripcion = trim($dto->descripcion);
        $precio->activo      = $dto->activo;

        $precio->save();

        Log::info('Precio estandar procesado.', [
            'id'     => $precio->id,
            'accion' => $accion
        ]);
    }

    /**
     * @throws Exception
     */
    private function eliminar(int $id): void
    {
        $precio = $this->repository->buscarPorId($id);
        if (!$precio) {
            throw new Exception('Registro de precio estándar no encontrado.');
        }

        try {
            $precio->delete();
            Log::info('Precio estandar eliminado.', ['id' => $id]);
        } catch (Exception $e) {
            Log::error('Restriccion de integridad al eliminar precio estandar.', ['id' => $id, 'error' => $e->getMessage()]);
            throw new Exception('No es posible eliminar el registro debido a dependencias en el sistema.');
        }
    }
}
