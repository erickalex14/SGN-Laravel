<?php

namespace App\Services\Directory;

use App\Repositories\Directory\EmpresaRepository;
use App\DTOs\Directory\EmpresaDTO;
use App\Models\Directory\Empresa;
use Illuminate\Support\Facades\Log;
use Exception;

class EmpresaService
{
    protected EmpresaRepository $repository;

    public function __construct(EmpresaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    //Guardar una empresa
    public function guardar(EmpresaDTO $dto): string
    {
        // Validacion estricta de negocio: RUC duplicado
        if ($this->repository->existeRuc($dto->ruc, $dto->id)) {
            Log::warning('Intento de registro de empresa con RUC duplicado.', ['ruc' => $dto->ruc]);
            throw new Exception('Ya existe una empresa con ese RUC.');
        }

        if ($dto->id) {
            $empresa = $this->repository->buscarPorId($dto->id);
            if (!$empresa) throw new Exception('La empresa no existe.');
            $mensaje = 'Empresa actualizada correctamente.';
        } else {
            $empresa = new Empresa();
            $mensaje = 'Empresa creada correctamente.';
        }

        $empresa->nombre            = $dto->nombre;
        $empresa->ruc               = $dto->ruc;
        $empresa->telefono          = $dto->telefono;
        $empresa->correo            = $dto->correo;
        $empresa->direccion_empresa = $dto->direccion;
        $empresa->save();

        $accionBitacora = $dto->id ? 'EDITAR_EMPRESA' : 'CREAR_EMPRESA';
        \App\Services\Operations\AuditLogger::registrar($accionBitacora, 'directorio', (string)$empresa->id, [
            'nombre' => $empresa->nombre,
            'ruc' => $empresa->ruc,
        ]);

        Log::info('Empresa gestionada exitosamente.', ['empresa_id' => $empresa->id]);
        return $mensaje;
    }

    /**
     * @throws Exception
     */
    public function eliminar(int $id): void
    {
        $empresa = $this->repository->buscarPorId($id);
        if (!$empresa) {
            throw new Exception('ID inválido.');
        }

        $empresa->delete();
        
        \App\Services\Operations\AuditLogger::registrar('ELIMINAR_EMPRESA', 'directorio', (string)$id, [
            'nombre' => $empresa->nombre,
            'ruc' => $empresa->ruc,
        ]);

        Log::info('Empresa eliminada del sistema.', ['empresa_id' => $id]);
    }
}
