<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\TipoDispositivoRepository;
use App\DTOs\Inventory\TipoDispositivoDTO;
use App\Models\Inventory\TipoDispositivo;
use Illuminate\Support\Facades\Log;
use Exception;

class TipoDispositivoService
{
    protected TipoDispositivoRepository $repository;

    public function __construct(TipoDispositivoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function procesar(TipoDispositivoDTO $dto, string $accion): void
    {
        if ($accion === 'eliminar') {
            $this->eliminar($dto->id);
            return;
        }

        $codigoNormalizado = strtoupper(trim($dto->codigo));
        $nombreNormalizado = strtoupper(trim($dto->nombre));

        $conflicto = $this->repository->existeCodigoONombre($codigoNormalizado, $nombreNormalizado, $dto->id);
        if ($conflicto) {
            Log::warning('Intento de creacion de tipo de dispositivo duplicado.', ['conflicto' => $conflicto, 'valor' => $conflicto === 'código' ? $codigoNormalizado : $nombreNormalizado]);
            throw new Exception("Ya existe un tipo de dispositivo con ese {$conflicto}.");
        }

        if ($accion === 'editar') {
            $tipo = $this->repository->buscarPorId($dto->id);
            if (!$tipo) throw new Exception("Tipo de dispositivo no encontrado.");
        } else {
            $tipo = new TipoDispositivo();
        }

        $tipo->codigo = $codigoNormalizado;
        $tipo->nombre = $nombreNormalizado;
        $tipo->save();

        Log::info('Tipo de dispositivo guardado satisfactoriamente.', ['tipo_id' => $tipo->id, 'accion' => $accion]);
    }

    /**
     * @throws Exception
     */
    private function eliminar(int $id): void
    {
        $tipo = $this->repository->buscarPorId($id);
        if (!$tipo) throw new Exception("Tipo de dispositivo no encontrado.");

        try {
            $tipo->delete();
            Log::info('Tipo de dispositivo eliminado.', ['tipo_id' => $id]);
        } catch (Exception $e) {
            Log::error('Fallo al eliminar tipo de dispositivo debido a restricciones de integridad referencial.', ['tipo_id' => $id, 'error' => $e->getMessage()]);
            throw new Exception("No se puede eliminar este tipo porque está en uso por uno o más productos.");
        }
    }
}
