<?php

namespace App\Services\Operations;

use App\DTOs\Operations\GestionarSolicitudRepuestoDTO;
use App\DTOs\Operations\SolicitudRepuestoDTO;
use App\Models\Inventory\Repuesto;
use App\Models\Operations\OrdenRepuesto;
use App\Models\Operations\SolicitudRepuesto;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\SolicitudRepuestoRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitudRepuestoService
{
    protected SolicitudRepuestoRepository $repository;

    protected OrdenRepository $ordenRepository;

    public function __construct(
        SolicitudRepuestoRepository $repository,
        OrdenRepository $ordenRepository
    ) {
        $this->repository = $repository;
        $this->ordenRepository = $ordenRepository;
    }

    /**
     * @throws Exception
     */
    public function registrarSolicitud(SolicitudRepuestoDTO $dto, bool $esAdmin = false): string
    {
        $esEmpresa = $dto->tipo_orden === 'empresa';

        if ($esEmpresa) {
            $orden = \App\Models\Operations\OrdenEmpresa::find($dto->orden_id);
        } else {
            $orden = $this->ordenRepository->buscarPorId($dto->orden_id);
        }
        if (! $orden) {
            throw new Exception('Orden no encontrada.');
        }

        if (! $esAdmin && (int) $orden->tecnico_id !== (int) $dto->tecnico_id) {
            throw new Exception('No puedes solicitar repuesto para una orden que no te esta asignada.');
        }

        $estadoOrden = $esEmpresa ? $orden->estado : $orden->estado_orden;
        if (in_array((string) $estadoOrden, ['Entregada', 'Nota de Credito'], true)) {
            throw new Exception('La orden no admite cambios en su estado actual.');
        }

        if ($this->repository->existeSolicitudParaOrden($dto->orden_id, $dto->tipo_orden)) {
            throw new Exception('Esta orden ya tiene una solicitud de repuesto registrada.');
        }

        $sol = null;
        try {
            $nro = DB::transaction(function () use ($dto, $esEmpresa, $orden, &$sol) {
                $nro = $this->repository->generarNumeroSolicitud();

                $sol = new SolicitudRepuesto;
                $sol->nro_solicitud = $nro;
                if ($esEmpresa) {
                    $sol->orden_empresa_id = $dto->orden_id;
                    $sol->orden_id = null;
                } else {
                    $sol->orden_id = $dto->orden_id;
                    $sol->orden_empresa_id = null;
                }
                $sol->tecnico_id = $dto->tecnico_id;
                $sol->tecnico_nombre = $dto->tecnico_nombre;
                $sol->repuesto_nombre = $dto->repuesto_nombre;
                $sol->repuesto_inv_id = $dto->repuesto_inv_id; // Relación con el catalogo si la hay
                $sol->nro_parte = $dto->nro_parte;
                $sol->link_compra = $dto->link_compra;
                $sol->cantidad = $dto->cantidad;
                $sol->descripcion = $dto->descripcion;
                $sol->estado = 'Pendiente';
                $sol->fecha_solicitud = Carbon::now('America/Guayaquil');
                $sol->save();

                // Legacy: al registrar solicitud se marca estado de repuesto como requerido.
                $orden->estado_repuesto = 'Requerido';
                $orden->save();

                Log::info('Solicitud de repuesto creada', ['sr_id' => $sol->id, 'orden_id' => $dto->orden_id, 'tipo_orden' => $dto->tipo_orden]);

                return $nro;
            });

            if ($sol) {
                try {
                    SgnMailService::enviarSolicitudRepuestoCreada($sol);
                } catch (\Throwable $e) {
                    Log::error('Error al enviar notificacion de solicitud de repuesto creada', ['error' => $e->getMessage()]);
                }
            }

            return $nro;
        } catch (Exception $e) {
            Log::error('Fallo al registrar solicitud de repuesto.', ['error' => $e->getMessage()]);
            throw new Exception('No se pudo procesar la solicitud.');
        }
    }

    /**
     * @throws Exception
     */
    public function gestionar(GestionarSolicitudRepuestoDTO $dto): void
    {
        $solicitud = $this->repository->buscarPorId($dto->solicitud_id);
        if (! $solicitud) {
            throw new Exception('Solicitud no encontrada.');
        }
        if ($solicitud->estado !== 'Pendiente') {
            throw new Exception("La solicitud ya fue procesada ({$solicitud->estado}).");
        }

        try {
            DB::transaction(function () use ($solicitud, $dto) {
                $estadoRecibido = strtoupper(trim($dto->estado));
                $esCompra = $estadoRecibido === 'COMPRA';
                $estadoFinal = match ($estadoRecibido) {
                    'RECHAZADA' => 'Rechazada',
                    default => 'Aprobada',
                };

                $solicitud->estado = $estadoFinal;
                $solicitud->aprobado_por = $dto->aprobado_por;
                $solicitud->fecha_gestion = Carbon::now('America/Guayaquil');
                $solicitud->repuesto_id = null;

                if ($dto->cantidad !== null && $dto->cantidad > 0) {
                    $solicitud->cantidad = $dto->cantidad;
                }

                if ($estadoFinal === 'Rechazada') {
                    $solicitud->motivo_rechazo = trim($dto->motivo_rechazo);
                } else {
                    $solicitud->motivo_rechazo = null;
                }

                $esEmpresa = ($solicitud->orden_empresa_id !== null && $solicitud->orden_empresa_id > 0);
                if ($esEmpresa) {
                    $orden = \App\Models\Operations\OrdenEmpresa::find($solicitud->orden_empresa_id);
                } else {
                    $orden = $this->ordenRepository->buscarPorId((int) $solicitud->orden_id);
                }
                if (! $orden) {
                    throw new Exception('La orden asociada a la solicitud no existe.');
                }

                if ($estadoFinal === 'Aprobada' && ! $esCompra) {
                    $repuestoIdAsignado = $dto->repuesto_id ?: $solicitud->repuesto_inv_id;
                    $repuestoIdAsignado = $repuestoIdAsignado ? (int) $repuestoIdAsignado : null;

                    if (! $repuestoIdAsignado) {
                        throw new Exception('Seleccione un repuesto para aprobar y despachar, o use la opcion Mandar a compras.');
                    }

                    $repuesto = Repuesto::find($repuestoIdAsignado);
                    if (! $repuesto) {
                        throw new Exception('El repuesto seleccionado no existe.');
                    }
                    if ($repuesto->stock < (int) $solicitud->cantidad) {
                        throw new Exception("Stock insuficiente del repuesto '{$repuesto->nombre}' (Solicitado: {$solicitud->cantidad}, Stock: {$repuesto->stock}).");
                    }
                    $repuesto->stock -= (int) $solicitud->cantidad;
                    $repuesto->save();

                    // Log in the audit history (orden_repuestos)
                    $queryVinculo = OrdenRepuesto::query()
                        ->where('repuesto_id', $repuestoIdAsignado);
                    if ($esEmpresa) {
                        $queryVinculo->where('orden_empresa_id', $solicitud->orden_empresa_id);
                    } else {
                        $queryVinculo->where('orden_id', $solicitud->orden_id);
                    }
                    $existeVinculo = $queryVinculo->first();

                    if ($existeVinculo) {
                        $existeVinculo->cantidad += $solicitud->cantidad;
                        $existeVinculo->fecha = Carbon::now('America/Guayaquil');
                        $existeVinculo->save();
                    } else {
                        $ordenRepuesto = new OrdenRepuesto;
                        if ($esEmpresa) {
                            $ordenRepuesto->orden_empresa_id = $solicitud->orden_empresa_id;
                        } else {
                            $ordenRepuesto->orden_id = $solicitud->orden_id;
                        }
                        $ordenRepuesto->repuesto_id = $repuestoIdAsignado;
                        $ordenRepuesto->cantidad = $solicitud->cantidad;
                        $ordenRepuesto->usuario_id = $solicitud->tecnico_id; // El tecnico que solicito el repuesto
                        $ordenRepuesto->fecha = Carbon::now('America/Guayaquil');
                        $ordenRepuesto->save();
                    }

                    $solicitud->repuesto_id = $repuestoIdAsignado;
                    $orden->estado_repuesto = 'Con stock';
                    $orden->repuesto_inventario_id = $repuestoIdAsignado;
                } elseif ($estadoFinal === 'Rechazada') {
                    $orden->estado_repuesto = 'Sin stock';
                } else {
                    // Flujo COMPRA: en BD se almacena como Aprobada por enum legacy.
                    $solicitud->repuesto_id = null;
                    $orden->estado_repuesto = 'Requerido';
                    $orden->repuesto_inventario_id = null;
                }

                $solicitud->save();
                $orden->save();
                Log::info('Solicitud repuesto gestionada.', [
                    'sr_id' => $solicitud->id,
                    'estado' => $estadoFinal,
                    'es_compra' => $esCompra,
                ]);
            });

            try {
                SgnMailService::enviarSolicitudRepuestoGestionada($solicitud);
            } catch (\Throwable $e) {
                Log::error('Error al enviar notificacion de solicitud de repuesto gestionada', ['error' => $e->getMessage()]);
            }
        } catch (Exception $e) {
            Log::error('Error al gestionar SR', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage()); // Pasa el error (ej. Stock insuficiente) al controlador
        }
    }
}
