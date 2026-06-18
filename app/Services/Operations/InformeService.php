<?php

namespace App\Services\Operations;

use App\DTOs\Operations\InformeDTO;
use App\Models\Operations\Informe;
use App\Models\Operations\InformeFoto;
use App\Repositories\Operations\InformeRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InformeService
{
    protected InformeRepository $repository;

    public function __construct(InformeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea o actualiza el informe tecnico con sus evidencias.
     *
     * @throws Exception
     */
    public function procesarInforme(
        InformeDTO $dto,
        bool $puedeEscribir,
        bool $esMaster,
        int $sucursalSesion
    ): void {
        // Solo los admins puros (sin sufijo master) no pueden crear ni editar informes.
        // Técnico, Técnico Master, Admin Master y Superadmin sí pueden.
        if (! $puedeEscribir) {
            throw new Exception('No tiene permiso para crear ni modificar informes técnicos.');
        }

        $ordenValida = $this->repository->buscarOrdenValidaParaInforme(
            $dto->orden_id,
            $dto->tecnico_id,
            false,      // Al guardar, siempre validar que la orden es del técnico
            $esMaster,
            $sucursalSesion
        );

        if (! $ordenValida) {
            throw new Exception('No tiene permisos sobre la orden seleccionada.');
        }

        $estadoActual = mb_strtolower(trim((string) ($ordenValida['estado'] ?? '')));
        if (in_array($estadoActual, ['finalizado', 'finalizada', 'entregada'], true)) {
            throw new Exception('No se puede modificar el informe de una orden en estado "'.($ordenValida['estado'] ?? '').'".');
        }

        $informeExistente = $this->repository->buscarPorOrdenId($dto->orden_id);

        try {
            DB::transaction(function () use ($dto, $informeExistente): void {
                $fechaActual = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

                $informe = $informeExistente ?: new Informe;
                $informe->orden_id = $dto->orden_id;
                // Si ya existe informe, conserva el tecnico propietario original.
                $informe->tecnico_id = $informeExistente ? (int) $informeExistente->tecnico_id : $dto->tecnico_id;
                $informe->antecedentes = trim($dto->antecedentes);
                $informe->proceso = trim($dto->proceso);
                $informe->conclusion = trim((string) ($dto->conclusion ?? ''));
                $informe->recomendaciones = trim((string) ($dto->recomendaciones ?? ''));
                $informe->estado_equipo = $this->normalizarEstadoEquipo($dto->estado_equipo);
                $informe->fecha_informe = $dto->fecha_informe
                    ? Carbon::parse($dto->fecha_informe, 'America/Guayaquil')->format('Y-m-d')
                    : Carbon::now('America/Guayaquil')->format('Y-m-d');

                if (! $informeExistente) {
                    $informe->fecha_creacion = $fechaActual;
                }
                $informe->save();

                if (! empty($dto->fotos)) {
                    $this->reemplazarFotosInforme($informe->id, $dto->fotos, $dto->captions);
                }

                Log::info('Informe tecnico guardado.', [
                    'informe_id' => $informe->id,
                    'orden_id' => $dto->orden_id,
                    'tecnico_id' => $dto->tecnico_id,
                    'accion' => $informeExistente ? 'actualizar' : 'crear',
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error transaccional al generar informe tecnico.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrio un error al procesar el informe. Verifique los datos adjuntos.');
        }
    }

    public function actualizarInformeComoAdmin(
        int $informeId,
        InformeDTO $dto,
        bool $esMaster,
        int $sucursalSesion
    ): void {
        $informeExistente = $this->repository->buscarPorId($informeId);
        if (! $informeExistente) {
            throw new Exception('Informe no encontrado.');
        }

        $ordenValida = $this->repository->buscarOrdenValidaParaInforme(
            $dto->orden_id,
            0,
            true,
            $esMaster,
            $sucursalSesion
        );

        if (! $ordenValida) {
            throw new Exception('No tiene permisos sobre la orden seleccionada.');
        }

        $estadoActual = mb_strtolower(trim((string) ($ordenValida['estado'] ?? '')));
        if (in_array($estadoActual, ['finalizado', 'finalizada', 'entregada'], true)) {
            throw new Exception('No se puede modificar el informe de una orden en estado "'.($ordenValida['estado'] ?? '').'".');
        }

        try {
            DB::transaction(function () use ($dto, $informeExistente): void {
                $informeExistente->antecedentes = trim($dto->antecedentes);
                $informeExistente->proceso = trim($dto->proceso);
                $informeExistente->conclusion = trim((string) ($dto->conclusion ?? ''));
                $informeExistente->recomendaciones = trim((string) ($dto->recomendaciones ?? ''));
                $informeExistente->estado_equipo = $this->normalizarEstadoEquipo($dto->estado_equipo);
                $informeExistente->fecha_informe = $dto->fecha_informe
                    ? Carbon::parse($dto->fecha_informe, 'America/Guayaquil')->format('Y-m-d')
                    : Carbon::now('America/Guayaquil')->format('Y-m-d');
                $informeExistente->save();

                if (! empty($dto->fotos)) {
                    $this->reemplazarFotosInforme($informeExistente->id, $dto->fotos, $dto->captions);
                }

                Log::info('Informe tecnico actualizado por admin.', [
                    'informe_id' => $informeExistente->id,
                    'orden_id' => $dto->orden_id,
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error transaccional al actualizar informe tecnico por admin.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrio un error al actualizar el informe. Verifique los datos adjuntos.');
        }
    }

    private function reemplazarFotosInforme(int $informeId, array $fotos, array $captions): void
    {
        $anteriores = InformeFoto::query()->where('informe_id', $informeId)->get();
        foreach ($anteriores as $anterior) {
            $rutaAnterior = (string) ($anterior->foto_data ?? '');
            if ($rutaAnterior !== '' && ! str_starts_with($rutaAnterior, 'data:') && Storage::disk('public')->exists($rutaAnterior)) {
                Storage::disk('public')->delete($rutaAnterior);
            }
        }

        InformeFoto::query()->where('informe_id', $informeId)->delete();

        $ordenFoto = 1;
        foreach ($fotos as $foto) {
            $ruta = $foto->store('informes', 'public');
            $caption = trim((string) ($captions[$ordenFoto - 1] ?? ''));

            $informeFoto = new InformeFoto;
            $informeFoto->informe_id = $informeId;
            $informeFoto->nombre_archivo = $foto->getClientOriginalName();
            $informeFoto->tipo_mime = $foto->getMimeType();
            $informeFoto->foto_data = $ruta;
            $informeFoto->caption = $caption;
            $informeFoto->orden_foto = $ordenFoto;
            $informeFoto->save();

            $ordenFoto++;
        }
    }

    private function normalizarEstadoEquipo(string $estado): string
    {
        $valor = trim($estado);

        return match (mb_strtoupper($valor)) {
            'OPERATIVO', 'OPERATIVO / REPARADO' => 'Operativo',
            'REPARADO PARCIALMENTE', 'OPERATIVO PARCIAL', 'OPERATIVO PARCIAL / FUNCIONES LIMITADAS' => 'Reparado parcialmente',
            'SIN REPARACION POSIBLE', 'SIN REPARACIÓN POSIBLE', 'DESGUACE', 'NO OPERATIVO', 'NO OPERATIVO / DANO IRREPARABLE', 'NO OPERATIVO / DAÑO IRREPARABLE' => 'Sin reparación posible',
            'EN ESPERA DE REPUESTO' => 'En espera de repuesto',
            default => $valor,
        };
    }
}
