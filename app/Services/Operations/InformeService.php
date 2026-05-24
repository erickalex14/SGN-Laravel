<?php

namespace App\Services\Operations;

use App\Repositories\Operations\InformeRepository;
use App\DTOs\Operations\InformeDTO;
use App\Models\Operations\Informe;
use App\Models\Operations\InformeFoto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    public function procesarInforme(InformeDTO $dto): void
    {
        $informeExistente = $this->repository->buscarPorOrdenId($dto->orden_id);

        if ($informeExistente) {
            Log::warning('Intento de duplicidad en generacion de informe tecnico.', ['orden_id' => $dto->orden_id]);
            throw new Exception('La orden especificada ya cuenta con un informe técnico registrado.');
        }
        
        try {
            DB::transaction(function () use ($dto) {
                $fechaActual = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

                $informe = new Informe();
                $informe->orden_id        = $dto->orden_id;
                $informe->tecnico_id      = $dto->tecnico_id;
                $informe->antecedentes    = trim($dto->antecedentes);
                $informe->proceso         = trim($dto->proceso);
                $informe->conclusion      = trim($dto->conclusion);
                $informe->recomendaciones = trim($dto->recomendaciones);
                $informe->estado_equipo   = strtoupper(trim($dto->estado_equipo));
                $informe->fecha_informe   = Carbon::now('America/Guayaquil')->format('Y-m-d');
                $informe->fecha_creacion  = $fechaActual;
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

                Log::info('Informe tecnico generado y almacenado.', [
                    'informe_id' => $informe->id,
                    'orden_id'   => $dto->orden_id,
                    'tecnico_id' => $dto->tecnico_id
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error transaccional al generar informe tecnico.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error al procesar el informe. Verifique los datos adjuntos.');
        }
    }
}