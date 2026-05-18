<?php

namespace App\Services\Directory;
use App\Repositories\Directory\SucursalClienteRepository;
use App\DTOs\Directory\SucursalClienteDTO;
use App\Models\Directory\SucursalCliente;
use Illuminate\Support\Facades\Log;
use Exception;

class SucursalClienteService
{
    private SucursalClienteRepository $repository;

    public function __construct(SucursalClienteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */

    //Guardar entidad
    public function guardar(SucursalClienteDTO $dto): array
    {
        if ($dto->id){
            $sucursal = $this ->repository->buscarPorId($dto->id);
            if (!$sucursal) throw new Exception('La sucursal no existe.');
            $mensaje = 'Sucursal actualizada correctamente.';

            //Al actualizar no se toca ni el numero ni el codigo, como en el proyecto vanilla
            $sucursal->nombre           = strtoupper($dto->nombre);
            $sucursal->provincia        = $dto->provincia ? strtoupper($dto->provincia) : null;
            $sucursal->novitec_sucursal = $dto->novitec_sucursal ? strtoupper($dto->novitec_sucursal) : null;
            $sucursal->activa           = $dto->activa;
        } else {
            if ($this->repository->existeNumero($dto->numero)) {
                Log::warning('Intento de registro de sucursal con número duplicado.', ['numero' => $dto->numero]);
                throw new Exception('Ya existe una sucursal con ese número.');
            }
            if ($this->repository->existeCodigo($dto->codigo)) {
                $codigoUpper = strtoupper($dto->codigo);
                throw new Exception("El código '{$codigoUpper}' ya está registrado.");
            }

            $sucursal = new SucursalCliente();
            $sucursal->numero           = $dto->numero;
            $sucursal->codigo           = strtoupper($dto->codigo);
            $sucursal->nombre           = strtoupper($dto->nombre);
            $sucursal->provincia        = $dto->provincia ? strtoupper($dto->provincia) : null;
            $sucursal->novitec_sucursal = $dto->novitec_sucursal ? strtoupper($dto->novitec_sucursal) : null;
            $sucursal->activa           = $dto->activa;

            $numeroFormat = str_pad($dto->numero, 3, '0', STR_PAD_LEFT);
            $mensaje = "Sucursal {$numeroFormat} - " . strtoupper($dto->nombre) . " creada correctamente.";
        }
        $sucursal->save();

        return [
            'mensaje' => $mensaje,
            'sucursal' => $this->mapearArray($sucursal)
        ];
    }

    //Cambiar status de la entidad
    public function toggleEstatus(int $id, int $activa): array
    {
        $sucursal = $this->repository->buscarPorId($id);
        if (!$sucursal) throw new Exception('Sucursal no encontrada.');

        $sucursal->activa = $activa;
        $sucursal->save();

        Log::info('Estado de sucursal cliente cambiado', ['id' => $id, 'activa' => $activa]);

        return [
            'mensaje'  => 'Sucursal ' . ($activa ? 'activada' : 'desactivada') . ' correctamente.',
            'sucursal' => $this->mapearArray($sucursal)
        ];
    }

    //metodo auxiliar para mapear la entidad en un array
    private function mapearArray(SucursalCliente $sucursal): array
    {
        return [
            'id'               => $sucursal->id,
            'numero'           => $sucursal->numero,
            'codigo'           => $sucursal->codigo,
            'nombre'           => $sucursal->nombre,
            'provincia'        => $sucursal->provincia ?? '',
            'novitec_sucursal' => $sucursal->novitec_sucursal ?? '',
            'activa'           => $sucursal->activa ? 1 : 0
        ];
    }

}
