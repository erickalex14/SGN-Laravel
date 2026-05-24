<?php

namespace App\Services\Operations;

use App\Repositories\Operations\SolicitudRepuestoRepository;
use App\DTOs\Operations\SolicitudRepuestoDTO;
use App\DTOs\Operations\GestionarSolicitudRepuestoDTO;
use App\Models\Operations\SolicitudRepuesto;
use App\Models\Inventory\Repuesto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SolicitudRepuestoService
{
    protected SolicitudRepuestoRepository $repository;

    public function __construct(SolicitudRepuestoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function registrarSolicitud(SolicitudRepuestoDTO $dto): string
    {
        try {
            return DB::transaction(function () use ($dto) {
                $nro = $this->repository->generarNumeroSolicitud();

                $sol = new SolicitudRepuesto();
                $sol->nro_solicitud   = $nro;
                $sol->orden_id        = $dto->orden_id;
                $sol->tecnico_id      = $dto->tecnico_id;
                $sol->tecnico_nombre  = $dto->tecnico_nombre;
                $sol->repuesto_nombre = $dto->repuesto_nombre;
                $sol->repuesto_inv_id = $dto->repuesto_inv_id; // Relación con el catalogo si la hay
                $sol->nro_parte       = $dto->nro_parte;
                $sol->link_compra     = $dto->link_compra;
                $sol->cantidad        = $dto->cantidad;
                $sol->descripcion     = $dto->descripcion;
                $sol->estado          = 'PENDIENTE';
                $sol->fecha_solicitud = Carbon::now('America/Guayaquil');
                $sol->save();

                Log::info('Solicitud de repuesto creada', ['sr_id' => $sol->id, 'orden_id' => $dto->orden_id]);
                return $nro;
            });
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
        if (!$solicitud) throw new Exception('Solicitud no encontrada.');
        if ($solicitud->estado !== 'PENDIENTE') throw new Exception("La solicitud ya fue procesada ({$solicitud->estado}).");

        try {
            DB::transaction(function () use ($solicitud, $dto) {
                $solicitud->estado = $dto->estado;
                $solicitud->aprobado_por = $dto->aprobado_por;
                $solicitud->fecha_gestion = Carbon::now('America/Guayaquil');

                if ($dto->estado === 'RECHAZADA') {
                    $solicitud->motivo_rechazo = trim($dto->motivo_rechazo);
                } 
                elseif ($dto->estado === 'APROBADA' && $solicitud->repuesto_inv_id) {
                    // Descontar del inventario automaticamente
                    $repuesto = Repuesto::find($solicitud->repuesto_inv_id);
                    if ($repuesto) {
                        if ($repuesto->stock < $solicitud->cantidad) {
                            throw new Exception("Stock insuficiente del repuesto '{$repuesto->nombre}'.");
                        }
                        $repuesto->stock -= $solicitud->cantidad;
                        $repuesto->save();
                    }
                }

                $solicitud->save();
                Log::info('Solicitud repuesto gestionada.', ['sr_id' => $solicitud->id, 'estado' => $dto->estado]);
            });
        } catch (Exception $e) {
            Log::error('Error al gestionar SR', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage()); // Pasa el error (ej. Stock insuficiente) al controlador
        }
    }
}