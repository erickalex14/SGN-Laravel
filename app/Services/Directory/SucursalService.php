<?php

namespace App\Services\Directory;

use App\Repositories\Directory\SucursalRepository;
use App\DTOs\Directory\SucursalDTO;
use App\Models\Directory\Sucursal;
use Illuminate\Support\Facades\Log;
use Exception;
class SucursalService
{
    protected SucursalRepository $repository;

    public function __construct(SucursalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function guardar(SucursalDTO $dto): array
    {
        //Validar el numero de la sucursal
        if ($this->repository->existeNroSucursal($dto->nro_sucursal, $dto->id)) {
            $mensaje = $dto->id
                ? "El número de sucursal {$dto->nro_sucursal} ya está en uso por otra sucursal."
                : "El número de sucursal {$dto->nro_sucursal} ya existe.";
            Log::warning('Intento de registro de sucursal duplicada.', ['nro_sucursal' => $dto->nro_sucursal]);
            throw new Exception($mensaje);
        }

        if ($dto->id)
        {
            $sucursal = $this->repository->buscarPorId($dto->id);
            if (!$sucursal) throw new Exception('La sucursal no existe.');
            $mensajeExito = "Sucursal {$dto->nro_sucursal} actualizada correctamente.";
        } else {
            $sucursal = new Sucursal();
            $mensajeExito = "Sucursal {$dto->nro_sucursal} creada correctamente.";
        }

        $sucursal->nro_sucursal = $dto->nro_sucursal;
        $sucursal->ciudad = $dto->ciudad;
        $sucursal->secuencial = $dto->secuencial;
        $sucursal->nro_base = $dto->nro_base;
        $sucursal->save();

        Log::info('Sucursal gestionada exitosamente.', ['sucursal_id' => $sucursal->id]);

        return [
            'mensaje' => $mensajeExito,
            'sucursal' => [
                'id' => $sucursal->id,
                'nro_sucursal' => $sucursal->nro_sucursal,
                'ciudad' => $sucursal->ciudad,
                'secuencial' => $sucursal->secuencial,
                'nro_base' => $sucursal->nro_base
            ]
        ];
    }
}
