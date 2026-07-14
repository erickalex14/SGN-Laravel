<?php

namespace App\Services\Directory;

use App\Repositories\Directory\CasRepository;
use App\DTOs\Directory\CasDTO;
use App\Models\Directory\Cas;
use Illuminate\Support\Facades\Log;
use Exception;

class CasService
{
    protected CasRepository $repository;

    public function __construct(CasRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function guardar(CasDTO $dto, string $accion): string
    {
        // El legacy convierte el nombre a mayúsculas
        $nombreMayusculas = strtoupper($dto->nombre);

        if ($this->repository->existeNombre($nombreMayusculas, $dto->id)) {
            Log::warning('Intento de registro duplicado de CAS.', ['nombre' => $nombreMayusculas]);
            throw new Exception('Ya existe un cas con ese nombre.');
        }

        if ($accion === 'editar') {
            $cas = $this->repository->buscarPorId($dto->id);
            if (!$cas) throw new Exception('ID inválido.');
            $mensaje = 'CAS actualizado correctamente.';
        } else {
            $cas = new Cas();
            $mensaje = 'CAS registrado correctamente.';
        }

        $cas->nombre    = $nombreMayusculas;
        $cas->prefijo   = $dto->prefijo;
        $cas->marca     = $dto->marca;
        $cas->telefono  = $dto->telefono;
        $cas->correo    = $dto->correo;
        $cas->ciudad    = $dto->ciudad;
        $cas->direccion = $dto->direccion;
        $cas->contacto  = $dto->contacto;
        $cas->notas     = $dto->notas;
        $cas->activo    = $dto->activo;

        $cas->save();

        $accionBitacora = $accion === 'editar' ? 'EDITAR_CAS' : 'CREAR_CAS';
        \App\Services\Operations\AuditLogger::registrar($accionBitacora, 'directorio', (string)$cas->id, [
            'nombre' => $cas->nombre,
            'prefijo' => $cas->prefijo,
        ]);

        Log::info('CAS gestionado exitosamente.', ['cas_id' => $cas->id, 'accion' => $accion]);

        return $mensaje;
    }
}
