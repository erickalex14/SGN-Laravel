<?php

namespace App\Services\Operations;

use App\Repositories\Operations\NotaCreditoRepository;
use App\DTOs\Operations\SolicitudNcDTO;
use App\DTOs\Operations\GestionarNcDTO;
use App\Models\Operations\SolicitudNc;
use App\Models\Identity\Notificacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

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
            throw new Exception('Ya existe una solicitud de Nota de Crédito en estado PENDIENTE para esta orden.');
        }

        try {
            return DB::transaction(function () use ($dto) {
                $nroSolicitud = $this->repository->generarNumeroSolicitud();

                $solicitud = new SolicitudNc();
                $solicitud->nro_solicitud   = $nroSolicitud;
                $solicitud->orden_id        = $dto->orden_id;
                $solicitud->fecha_solicitud = Carbon::now('America/Guayaquil')->format('Y-m-d');
                $solicitud->asunto          = trim($dto->asunto);
                $solicitud->detalles        = trim($dto->detalles);
                $solicitud->tecnico_id      = $dto->tecnico_id;
                $solicitud->tecnico_nombre  = $dto->tecnico_nombre;
                $solicitud->estado          = 'PENDIENTE';
                $solicitud->save();

                // 2. Aquí idealmente se despacharía un Evento/Listener (NotificarAdminNuevaNC)
                // Para simplificar y mantener paridad con el vanilla:
                $this->crearNotificacionParaAdmins($solicitud, "Nueva solicitud de NC: {$nroSolicitud}");

                Log::info('Solicitud de Nota de Credito registrada.', ['nc_id' => $solicitud->id, 'orden_id' => $dto->orden_id]);

                return $nroSolicitud;
            });
        } catch (Exception $e) {
            Log::error('Error al registrar solicitud NC.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error al procesar la solicitud.');
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

        if ($solicitud->estado !== 'PENDIENTE') {
            throw new Exception("Esta solicitud ya fue {$solicitud->estado}.");
        }

        try {
            DB::transaction(function () use ($solicitud, $dto) {
                $solicitud->estado = $dto->estado;
                $solicitud->nombre_admin = $dto->nombre_admin;
                
                if ($dto->estado === 'RECHAZADA') {
                    $solicitud->motivo_rechazo = trim($dto->motivo_rechazo);
                }

                $solicitud->save();

                // Crear notificación para el técnico
                $mensajeNotif = "Tu solicitud de Nota de Crédito ({$solicitud->nro_solicitud}) ha sido {$dto->estado}.";
                $this->crearNotificacionTecnico($solicitud->tecnico_id, $solicitud->id, $solicitud->orden_id, $mensajeNotif);

                Log::info('Solicitud de Nota de Credito gestionada.', [
                    'nc_id' => $solicitud->id, 
                    'estado' => $dto->estado,
                    'admin' => $dto->nombre_admin
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error al gestionar solicitud NC.', ['error' => $e->getMessage()]);
            throw new Exception('Error al actualizar el estado de la solicitud.');
        }
    }

    // Metodos auxiliares para notificaciones
    private function crearNotificacionParaAdmins(SolicitudNc $nc, string $mensaje): void
    {
        // En una arquitectura real en Laravel, esto seria un Notification/Event
        $adminsIds = \App\Models\Identity\Usuario::whereHas('grupo', function($q){
            $q->where('es_superadmin', 1);
        })->pluck('id');

        foreach($adminsIds as $adminId) {
            Notificacion::create([
                'usuario_id' => $adminId,
                'tipo'       => 'NC_PENDIENTE',
                'mensaje'    => $mensaje,
                'nc_id'      => $nc->id,
                'orden_id'   => $nc->orden_id
            ]);
        }
    }

    private function crearNotificacionTecnico(int $tecnicoId, int $ncId, int $ordenId, string $mensaje): void
    {
        Notificacion::create([
            'usuario_id' => $tecnicoId,
            'tipo'       => 'NC_RESUELTA',
            'mensaje'    => $mensaje,
            'nc_id'      => $ncId,
            'orden_id'   => $ordenId
        ]);
    }
}