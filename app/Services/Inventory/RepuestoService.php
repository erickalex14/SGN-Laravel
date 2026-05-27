<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\RepuestoRepository;
use App\DTOs\Inventory\BuscarRepuestoOrdenDTO;
use App\DTOs\Inventory\RepuestoDTO;
use App\Models\Inventory\Repuesto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
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
        $repuesto->nro_parte           = $this->normalizarTextoOpcional($dto->nro_parte, true);
        $repuesto->nombre              = strtoupper(trim($dto->nombre));
        $repuesto->stock               = $dto->stock;
        $repuesto->costo               = $dto->costo;
        $repuesto->bodega              = $this->normalizarBodegaParaEsquema($dto->bodega);
        $repuesto->descripcion         = $this->normalizarTextoOpcional($dto->descripcion);
        $repuesto->marca_id            = $this->normalizarTextoOpcional($dto->marca_id, true);
        $repuesto->tipo_dispositivo_id = $this->normalizarTextoOpcional($dto->tipo_dispositivo_id, true);

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

    public function buscarParaOrden(BuscarRepuestoOrdenDTO $dto): Collection
    {
        return $this->repository->buscarParaOrden(
            trim($dto->q),
            $dto->stock_only
        );
    }

    private function normalizarBodegaParaEsquema(?string $bodega): int|string
    {
        $valor = strtoupper(trim((string) $bodega));

        if ($this->columnaBodegaEsNumerica()) {
            if ($valor === '') {
                return 1;
            }

            if (is_numeric($valor)) {
                return (int) $valor;
            }

            if (str_contains($valor, 'QUITO')) {
                return 1;
            }

            if (str_contains($valor, 'GUAYAQUIL') || str_contains($valor, 'GYE')) {
                return 2;
            }

            return 1;
        }

        return $valor;
    }

    private function columnaBodegaEsNumerica(): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        try {
            $database = (string) DB::getDatabaseName();
            $dataType = DB::table('information_schema.columns')
                ->where('TABLE_SCHEMA', $database)
                ->where('TABLE_NAME', 'repuestos')
                ->where('COLUMN_NAME', 'bodega')
                ->value('DATA_TYPE');

            $cache = in_array(strtolower((string) $dataType), [
                'tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'numeric', 'float', 'double'
            ], true);
        } catch (Exception) {
            $cache = false;
        }

        return $cache;
    }

    private function normalizarTextoOpcional(?string $valor, bool $upper = false): ?string
    {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }

        return $upper ? strtoupper($texto) : $texto;
    }
}
