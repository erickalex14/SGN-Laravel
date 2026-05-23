<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\RepuestoRepository;
use App\DTOs\Inventory\RepuestoDTO;
use App\Models\Inventory\Repuesto;
use Illuminate\Support\Facades\Log;
use Exception;

class RepuestoService
{
    protected RepuestoRepository $repository;

    public function __construct(RepuestoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function guardar(RepuestoDTO $dto, string $accion): void
    {
        $codigoNormalizado = strtoupper(trim($dto->codigo));

        if ($this->repository->existeCodigo($codigoNormalizado, $dto->id)) {
            Log::warning('Intento de registro de repuesto con codigo duplicado.', ['codigo' => $codigoNormalizado]);
            throw new Exception('El código ingresado ya se encuentra registrado para otro repuesto.');
        }

        if ($accion === 'editar') {
            $repuesto = $this->repository->buscarPorId($dto->id);
            if (!$repuesto) {
                throw new Exception('Registro de repuesto no encontrado.');
            }
        } else {
            $repuesto = new Repuesto();
        }

        $repuesto->codigo              = $codigoNormalizado;
        $repuesto->nro_parte           = strtoupper(trim($dto->nro_parte));
        $repuesto->nombre              = strtoupper(trim($dto->nombre));
        $repuesto->stock               = $dto->stock;
        $repuesto->costo               = $dto->costo;
        $repuesto->bodega              = strtoupper(trim($dto->bodega));
        $repuesto->descripcion         = trim($dto->descripcion);
        $repuesto->marca_id            = $dto->marca_id;
        $repuesto->tipo_dispositivo_id = $dto->tipo_dispositivo_id;

        $repuesto->save();

        Log::info('Repuesto procesado exitosamente.', [
            'repuesto_id' => $repuesto->id,
            'accion'      => $accion
        ]);
    }

    /**
     * @throws Exception
     */
    public function eliminar(int $id): void
    {
        $repuesto = $this->repository->buscarPorId($id);
        if (!$repuesto) {
            throw new Exception('Registro de repuesto no encontrado.');
        }

        try {
            $repuesto->delete();
            Log::info('Repuesto eliminado del inventario.', ['repuesto_id' => $id]);
        } catch (Exception $e) {
            Log::error('Error de integridad referencial al eliminar repuesto.', [
                'repuesto_id' => $id,
                'error'       => $e->getMessage()
            ]);
            throw new Exception('No es posible eliminar el repuesto debido a que cuenta con transacciones asociadas.');
        }
    }
}
