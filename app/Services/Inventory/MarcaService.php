<?php

namespace App\Services\Inventory;
use App\Repositories\Inventory\MarcaRepository;
use App\DTOs\Inventory\MarcaDTO;
use App\Models\Inventory\Marca;
use Illuminate\Support\Facades\Log;
use Exception;
class MarcaService
{
    protected MarcaRepository $repository;

    public function __construct(MarcaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function procesar(MarcaDTO $dto, string $accion): void
    {
        if ($accion === "eliminar") {
            $this->eliminar($dto->id);
            return;
        }
        $nombreNormalizado = strtoupper(trim($dto->nombre));

        if ($this->repository->existeNombre($nombreNormalizado, $dto->id)) {
            Log::warning('Intento de creacion de marca duplicada.', ['nombre' => $nombreNormalizado]);
            throw new Exception("La marca '{$nombreNormalizado}' ya está registrada.");
        }

        if ($accion === 'editar') {
            $marca = $this->repository->buscarPorId($dto->id);
            if (!$marca) throw new Exception("Marca no encontrada.");
        } else {
            $marca = new Marca();
        }

        $marca->nombre = $nombreNormalizado;
        $marca->save();

        Log::info('Marca guardada satisfactoriamente.', ['marca_id' => $marca->id, 'accion' => $accion]);
    }

    /**
     * @throws Exception
     */
    private function eliminar(int $id): void
    {
        $marca = $this->repository->buscarPorId($id);
        if (!$marca) throw new Exception("Marca no encontrada.");

        try {
            $marca->delete();
            Log::info('Marca eliminada.', ['marca_id' => $id]);
        } catch (Exception $e) {
            Log::error('Fallo al eliminar marca debido a restricciones de integridad referencial.', ['marca_id' => $id, 'error' => $e->getMessage()]);
            throw new Exception("No se puede eliminar la marca porque está en uso por uno o más productos.");
        }
    }
}
