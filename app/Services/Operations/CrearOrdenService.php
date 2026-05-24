<?php

namespace App\Services\Operations;

use App\Repositories\Directory\ClienteRepository;
use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\CrearOrdenDTO;
use App\Models\Operations\Equipo;
use App\Models\Operations\Orden;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CrearOrdenService
{
    protected ClienteRepository $clienteRepo;
    protected OrdenRepository $ordenRepo;

    public function __construct(ClienteRepository $clienteRepo, OrdenRepository $ordenRepo)
    {
        $this->clienteRepo = $clienteRepo;
        $this->ordenRepo = $ordenRepo;
    }

    /**
     * @throws Exception
     */
    public function crearOrden(CrearOrdenDTO $dto): Orden
    {
        try {
            return DB::transaction(function () use ($dto) {
                
                // 1. Gestionar Cliente (Crear o Actualizar si ya existe)
                $cliente = $this->clienteRepo->actualizarOCrear([
                    'identificacion'     => $dto->identificacion,
                    'nombres'            => strtoupper(trim($dto->nombres)),
                    'apellidos'          => strtoupper(trim($dto->apellidos)),
                    'numero_contacto'    => $dto->telefono,
                    'correo'             => $dto->correo,
                    'direccion_clientes' => strtoupper(trim($dto->direccion))
                ]);

                // 2. Crear Registro del Equipo
                $equipo = new Equipo();
                $equipo->tipo             = strtoupper(trim($dto->tipo_equipo));
                $equipo->marca            = strtoupper(trim($dto->marca));
                $equipo->modelo           = strtoupper(trim($dto->modelo));
                $equipo->serie            = strtoupper(trim($dto->serie));
                $equipo->falla            = trim($dto->falla);
                $equipo->observacion      = trim($dto->observacion);
                $equipo->tipo_servicio_id = $dto->tipo_servicio_id;
                $equipo->contrasena_equipo = $dto->contrasena_equipo;
                $equipo->save();

                // 3. Generar Nro de Orden y Crear la Orden
                $nroOrden = $this->ordenRepo->generarNumeroOrden($dto->sucursal_id);

                $orden = new Orden();
                $orden->nro_orden        = $nroOrden;
                $orden->cliente_id       = $cliente->id;
                $orden->equipo_id        = $equipo->id;
                $orden->tecnico_id       = $dto->tecnico_id;
                $orden->sucursal_id      = $dto->sucursal_id;
                $orden->ingresado_por    = $dto->ingresado_por;
                $orden->fecha_de_ingreso = $dto->fecha_ingreso;
                $orden->estado_orden     = 'INGRESO'; // Estado inicial legacy
                $orden->motivo_ingreso   = trim($dto->motivo_ingreso);
                
                $orden->save();

                Log::info('Orden de Servicio creada exitosamente.', [
                    'nro_orden' => $nroOrden,
                    'cliente_id' => $cliente->id
                ]);

                return $orden;
            });
        } catch (Exception $e) {
            Log::error('Fallo transaccional al crear orden de servicio.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error al generar la orden. Los cambios han sido revertidos.');
        }
    }
}