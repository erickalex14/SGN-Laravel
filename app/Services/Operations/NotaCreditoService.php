<?php

namespace App\Services\Operations;

use App\DTOs\Operations\GestionarNcDTO;
use App\DTOs\Operations\SolicitudNcDTO;
use App\Models\Identity\Notificacion;
use App\Models\Operations\SolicitudNc;
use App\Repositories\Operations\NotaCreditoRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotaCreditoService
{
    protected NotaCreditoRepository $repository;

    public function __construct(NotaCreditoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function solicitar(SolicitudNcDTO $dto): string
    {
        if ($this->repository->existeSolicitudPendienteParaOrden($dto->orden_id)) {
            throw new Exception('Ya existe una solicitud de Nota de Credito en estado Pendiente para esta orden.');
        }

        try {
            return DB::transaction(function () use ($dto) {
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

                return $nroSolicitud;
            });
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
            });
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

