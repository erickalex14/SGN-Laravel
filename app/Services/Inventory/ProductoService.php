<?php

namespace App\Services\Inventory;
use App\Repositories\Inventory\ProductoRepository;
use App\Repositories\Inventory\TipoDispositivoRepository;
use App\DTOs\Inventory\ProductoDTO;
use App\Models\Inventory\ProductoInventario;
use Illuminate\Support\Facades\Log;
use Exception;
class ProductoService
{
    protected ProductoRepository $repository;
    protected TipoDispositivoRepository $tipoRepository;

    public function __construct(ProductoRepository $repository, TipoDispositivoRepository $tipoRepository)
    {
        $this->repository = $repository;
        $this->tipoRepository = $tipoRepository;
    }

    /**
     * Procesar el registro o modificacion de un producto.
     * * @throws Exception
     */
    public function guardar(ProductoDTO $dto, string $accion): void
    {
        $codigoNormalizado = strtoupper(trim($dto->codigo));
        if ($this->repository->existeCodigo($codigoNormalizado, $dto->id)) {
            Log::warning('Intento de registro de producto con código duplicado.', ['codigo' => $codigoNormalizado]);
            throw new Exception('Ya existe un producto con ese código.');
        }

        if ($accion === 'editar') {
            $producto =$this->repository->buscarPorId($dto->id);
            if (!$producto) {
                throw new Exception('El producto no existe.');
            }
        } else {
            $producto = new ProductoInventario();
        }

        // Obtener el registro de Tipo de Dispositivo para llenar el campo legacy dependiente
        $tipoDispositivo = $this->tipoRepository->buscarPorId($dto->tipo_dispositivo_id);
        if (!$tipoDispositivo)
        {
            throw new Exception('El tipo de dispositivo no existe.');
        }

        $producto->codigo = $codigoNormalizado;
        $producto->descripcion = strtoupper(trim($dto->descripcion));
        $producto->marca_id = $dto->marca_id;
        $producto->tipo_dispositivo_id = $dto->tipo_dispositivo_id;
        $producto->tipo_dispositivo_codigo = $tipoDispositivo->codigo;

        $producto->save();

        Log::info('Registro de producto guardado.',[
            'producto_id' => $producto->id,
            'accion' => $accion
        ]);
    }

    /**
     * Procesar la eliminacion de un producto.
     * * @throws Exception
     */

    public function eliminar(int $id): void
    {
        $producto = $this->repository->buscarPorId($id);
        if (!$producto) {
            throw new Exception('Registro de producto no encontrado.');
        }

        try {
            $producto->delete();
            Log::info('Producto de inventario eliminado.', ['producto_id' => $id]);
        } catch (Exception $e) {
            Log::error('Restriccion de integridad al eliminar producto.', [
                'producto_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('No es posible eliminar el producto debido a que cuenta con dependencias en el sistema.');
        }
    }
}
