<?php

namespace App\Services\Operations;

use App\Repositories\Operations\InformeRepository;
use App\DTOs\Operations\InformeDTO;
use App\Models\Operations\Informe;
use App\Models\Operations\InformeFoto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class InformeService
{
    protected InformeRepository $repository;

    public function __construct(InformeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea un nuevo informe con fotos asociadas.
     * @throws Exception
     */
    public function procesarInforme(
        InformeDTO $dto,
        bool $esAdmin,
        bool $esMaster,
        int $sucursalSesion
    ): void
    {
        $ordenValida = $this->repository->buscarOrdenValidaParaInforme(
            $dto->orden_id,
            $dto->tecnico_id,
            $esAdmin,
            $esMaster,
            $sucursalSesion
        );

        if (!$ordenValida) {
            throw new Exception('No tiene permisos sobre la orden seleccionada.');
        }

        $estadoActual = mb_strtolower(trim((string) ($ordenValida['estado'] ?? '')));
        if (in_array($estadoActual, ['nota de credito', 'finalizado', 'entregada'], true)) {
            throw new Exception('No se puede modificar el informe de una orden en estado "' . ($ordenValida['estado'] ?? '') . '".');
        }

        $informeExistente = $this->repository->buscarPorOrdenId($dto->orden_id);

        try {
            DB::transaction(function () use ($dto, $informeExistente) {
                $fechaActual = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

                $informe = $informeExistente ?: new Informe();
                $informe->orden_id        = $dto->orden_id;
                $informe->tecnico_id      = $dto->tecnico_id;
                $informe->antecedentes    = trim($dto->antecedentes);
                $informe->proceso         = trim($dto->proceso);
                $informe->conclusion      = trim($dto->conclusion);
                $informe->recomendaciones = trim($dto->recomendaciones);
                $informe->estado_equipo   = $this->normalizarEstadoEquipo($dto->estado_equipo);
                $informe->fecha_informe   = Carbon::now('America/Guayaquil')->format('Y-m-d');
                if (!$informeExistente) {
                    $informe->fecha_creacion = $fechaActual;
                }
                $informe->save();

                // Procesamiento de fotografias adjuntas
                if (!empty($dto->fotos)) {
                    $ordenFoto = 1;
                    foreach ($dto->fotos as $foto) {
                        // Se almacena en la ruta storage/app/public/informes
                        $ruta = $foto->store('informes', 'public');
                        
                        $informeFoto = new InformeFoto();
                        $informeFoto->informe_id     = $informe->id;
                        $informeFoto->nombre_archivo = $foto->getClientOriginalName();
                        $informeFoto->tipo_mime      = $foto->getMimeType();
                        // Guardamos la ruta relativa en el campo foto_data o la columna destinada para la ruta
                        $informeFoto->foto_data      = $ruta; 
                        $informeFoto->orden_foto     = $ordenFoto;
                        $informeFoto->save();
                        
                        $ordenFoto++;
                    }
                }

                Log::info('Informe tecnico guardado.', [
                    'informe_id' => $informe->id,
                    'orden_id'   => $dto->orden_id,
                    'tecnico_id' => $dto->tecnico_id,
                    'accion' => $informeExistente ? 'actualizar' : 'crear'
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error transaccional al generar informe tecnico.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error al procesar el informe. Verifique los datos adjuntos.');
        }
    }

    private function normalizarEstadoEquipo(string $estado): string
    {
        $valor = trim($estado);
        return match (mb_strtoupper($valor)) {
            'OPERATIVO', 'OPERATIVO / REPARADO' => 'Operativo',
            'REPARADO PARCIALMENTE', 'OPERATIVO PARCIAL', 'OPERATIVO PARCIAL / FUNCIONES LIMITADAS' => 'Reparado parcialmente',
            'DESGUACE', 'NO OPERATIVO', 'NO OPERATIVO / DAÑO IRREPARABLE', 'NO OPERATIVO / DA?O IRREPARABLE' => 'Desguace',
            'EN ESPERA DE REPUESTO' => 'En espera de repuesto',
            default => $valor
        };
    }
}
