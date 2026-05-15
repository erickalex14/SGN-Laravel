<?php

namespace App\Services\Directory;
use App\DTOs\Directory\EmpresaDTO;
use App\Repositories\Contracts\Directory\EmpresaRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class EmpresaService
{
    //Inyeccion de dependencias
    protected EmpresaRepositoryInterface $empresaRepository;

    public function __construct(EmpresaRepositoryInterface $empresaRepository)
    {
        $this->empresaRepository = $empresaRepository;
    }

    //Procesar el registro de una nueva empresa
    public function registrarEmpresa(EmpresaDTO $dto): object
    {
        Log::info('Iniciando proceso de registro para entidad corporativa.', [
            'ruc' => $dto->ruc,
            'nombre' => $dto->nombre
        ]);

        try {
            //Validar que no exixsta empresa con el mismo ruc
            if ($this->empresaRepository->buscarPorRuc($dto->ruc)) {
                Log::warning('Intento de registro fallido: RUC ya registrado.', [
                    'ruc' => $dto->ruc
                ]);
                throw new Exception('Ya existe una empresa registrada con este RUC.');
            }

            //Si pasa la validacion crear la empresa
            $empresa = $this->empresaRepository->crear($dto->toArray());

            log::info();

            return $empresa;

            //Manejo de las excepciones con mensajes para mejor log
        }catch (Exception $e){
            Log::error('Fallo critico al registrar la empresa.', [
                'mensaje' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    //Listar Todas las empresas
    public function listarEmpresas(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->empresaRepository->obtenerTodas();
    }

    //Actualizar una empresa existente
    public function modificarEmpresa(int $id, EmpresaDTO $dto): bool
    {
        Log::info('Iniciando proceso de actualizacion de entidad corporativa.', ['empresa_id' => $id]);
        try {
            $empresaActual = $this->empresaRepository->buscarPorId($id);
            if (!$empresaActual) {
                Log::warning('Intento de actualizacion sobre empresa inexistente.', ['empresa_id' => $id]);
                throw new Exception('El registro de la empresa solicitada no existe en la base de datos.');
            }

            $actualizado = $this->empresaRepository->actualizar($id, $dto->toArray());

            if ($actualizado) {
                Log::info('Entidad corporativa actualizada exitosamente.', ['empresa_id' => $id]);
            }

            return $actualizado;

        } catch (Exception $e) {
            Log::error('Fallo critico al intentar modificar la empresa.', [
                'empresa_id' => $id,
                'mensaje' => $e->getMessage()
            ]);
            throw $e;
        }
    }


}
