<?php

namespace App\Services\Operations;

use App\DTOs\Operations\GestionarNcDTO;
use App\DTOs\Operations\SolicitudNcDTO;
use App\Models\Identity\Notificacion;
use App\Models\Operations\SolicitudNc;
use App\Repositories\Operations\NotaCreditoRepository;
use App\Repositories\Operations\OrdenRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Operations\AuditLogger;

class NotaCreditoService
{
    protected NotaCreditoRepository $repository;
    protected OrdenRepository $ordenRepository;

    public function __construct(NotaCreditoRepository $repository, OrdenRepository $ordenRepository)
    {
        $this->repository = $repository;
        $this->ordenRepository = $ordenRepository;
    }

    /**
     * @throws Exception
     */
    public function solicitar(SolicitudNcDTO $dto, bool $esAdmin = false): string
    {
        $orden = $this->ordenRepository->buscarPorId($dto->orden_id);
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        if (!$esAdmin && (int) $orden->tecnico_id !== (int) $dto->tecnico_id) {
            throw new Exception('No puedes solicitar NC para una orden que no te esta asignada.');
        }

        if (trim((string) $orden->motivo_ingreso) !== 'Validacion de Garantia') {
            throw new Exception('La Nota de Credito solo aplica a ordenes de Validacion de Garantia.');
        }

        if (!in_array((string) $orden->estado_orden, ['Finalizada', 'Entregada', 'Nota de Credito'], true)) {
            throw new Exception('La orden no cumple el estado requerido para solicitar NC.');
        }

        if ($this->repository->existeSolicitudParaOrden($dto->orden_id)) {
            throw new Exception('Esta orden ya tiene una solicitud de Nota de Credito registrada.');
        }

        $solicitud = null;
        try {
            $nroSolicitud = DB::transaction(function () use ($dto, &$solicitud) {
                $nroSolicitud = $this->repository->generarNumeroSolicitud();

                $solicitud = new SolicitudNc();
                $solicitud->nro_solicitud = $nroSolicitud;
                $solicitud->orden_id = $dto->orden_id;
                $solicitud->fecha_solicitud = Carbon::now('America/Guayaquil')->format('Y-m-d');
                $solicitud->asunto = trim($dto->asunto);
                $solicitud->detalles = trim($dto->detalles);
                $solicitud->tecnico_id = $dto->tecnico_id;
                $solicitud->tecnico_nombre = $dto->tecnico_nombre;
                $solicitud->estado = 'Pendiente';
                $solicitud->save();

                $this->crearNotificacionParaAdmins($solicitud, "Nueva solicitud de NC: {$nroSolicitud}");

                Log::info('Solicitud de Nota de Credito registrada.', [
                    'nc_id' => $solicitud->id,
                    'orden_id' => $dto->orden_id,
                ]);

                AuditLogger::registrar('CREAR_SOLICITUD_NC', 'notas_credito', (string)$solicitud->orden_id, [
                    'nro_solicitud' => $solicitud->nro_solicitud,
                    'asunto' => $solicitud->asunto,
                    'detalles' => $solicitud->detalles,
                ]);

                return $nroSolicitud;
            });

            if ($solicitud) {
                try {
                    \App\Services\Operations\SgnMailService::enviarSolicitudNcCreada($solicitud);
                } catch (\Throwable $e) {
                    Log::error('Error al enviar notificacion de solicitud de NC creada', ['error' => $e->getMessage()]);
                }
            }

            return $nroSolicitud;
        } catch (Exception $e) {
            Log::error('Error al registrar solicitud NC.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrio un error al procesar la solicitud.');
        }
    }

    /**
     * @throws Exception
     */
    public function gestionar(GestionarNcDTO $dto): void
    {
        $solicitud = $this->repository->buscarPorId($dto->solicitud_id);

        if (!$solicitud) {
            throw new Exception('La solicitud especificada no existe.');
        }

        if ($solicitud->estado !== 'Pendiente') {
            throw new Exception("Esta solicitud ya fue {$solicitud->estado}.");
        }

        try {
            DB::transaction(function () use ($solicitud, $dto) {
                $estadoRecibido = strtoupper(trim($dto->estado));
                $estadoFinal = $estadoRecibido === 'RECHAZADA' ? 'Rechazada' : 'Aprobada';

                $solicitud->estado = $estadoFinal;
                $solicitud->nombre_admin = $dto->nombre_admin;

                if ($estadoFinal === 'Rechazada') {
                    $solicitud->motivo_rechazo = trim($dto->motivo_rechazo);
                }

                $solicitud->save();

                $mensajeNotif = "Tu solicitud de Nota de Credito ({$solicitud->nro_solicitud}) ha sido {$estadoFinal}.";
                $tipoNotif = $estadoFinal === 'Rechazada' ? 'nc_rechazada' : 'nc_aprobada';
                $this->crearNotificacionTecnico(
                    $solicitud->tecnico_id,
                    $solicitud->id,
                    $solicitud->orden_id,
                    $tipoNotif,
                    $mensajeNotif
                );

                Log::info('Solicitud de Nota de Credito gestionada.', [
                    'nc_id' => $solicitud->id,
                    'estado' => $estadoFinal,
                    'admin' => $dto->nombre_admin,
                ]);

                $accion = $estadoFinal === 'Rechazada' ? 'RECHAZAR_NC' : 'APROBAR_NC';
                AuditLogger::registrar($accion, 'notas_credito', (string)$solicitud->orden_id, [
                    'nro_solicitud' => $solicitud->nro_solicitud,
                    'motivo_rechazo' => $solicitud->motivo_rechazo,
                    'admin' => $dto->nombre_admin,
                ]);
            });

            try {
                \App\Services\Operations\SgnMailService::enviarSolicitudNcGestionada($solicitud);
            } catch (\Throwable $e) {
                Log::error('Error al enviar notificacion de solicitud de NC gestionada', ['error' => $e->getMessage()]);
            }
        } catch (Exception $e) {
            Log::error('Error al gestionar solicitud NC.', ['error' => $e->getMessage()]);
            throw new Exception('Error al actualizar el estado de la solicitud.');
        }
    }

    private function crearNotificacionParaAdmins(SolicitudNc $nc, string $mensaje): void
    {
        $adminsIds = \App\Models\Identity\Usuario::whereHas('grupo', function ($q) {
            $q->where('es_superadmin', 1);
        })->pluck('id');

        foreach ($adminsIds as $adminId) {
            Notificacion::create([
                'usuario_id' => $adminId,
                'tipo' => 'nc_solicitud',
                'mensaje' => $mensaje,
                'nc_id' => $nc->id,
                'orden_id' => $nc->orden_id,
            ]);
        }
    }

    private function crearNotificacionTecnico(
        int $tecnicoId,
        int $ncId,
        int $ordenId,
        string $tipo,
        string $mensaje
    ): void {
        Notificacion::create([
            'usuario_id' => $tecnicoId,
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'nc_id' => $ncId,
            'orden_id' => $ordenId,
        ]);
    }
}
