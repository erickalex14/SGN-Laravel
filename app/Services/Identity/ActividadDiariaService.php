<?php

namespace App\Services\Identity;

use App\Models\Identity\Usuario;
use App\Models\Identity\ActividadDiaria;
use App\Repositories\Identity\ActividadDiariaRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ActividadDiariaService
{
    public function __construct(protected ActividadDiariaRepository $repository)
    {
    }

    /**
     * Registra una actividad en la base de datos si el usuario es técnico.
     */
    public function registrar(
        int $usuarioId,
        string $tipoAccion,
        string $descripcion,
        string $modulo,
        ?int $referenciaId = null,
        ?string $referenciaTipo = null,
        ?array $metadata = null,
        ?string $ipAddress = null
    ): void {
        try {
            $usuario = Usuario::find($usuarioId);
            if (!$usuario || !$usuario->debeLlenarActividades()) {
                return;
            }

            $now = Carbon::now('America/Guayaquil');

            $this->repository->guardar([
                'usuario_id' => $usuarioId,
                'tipo_accion' => $tipoAccion,
                'descripcion' => $descripcion,
                'modulo' => $modulo,
                'referencia_id' => $referenciaId,
                'referencia_tipo' => $referenciaTipo,
                'metadata_json' => $metadata,
                'ip_address' => $ipAddress ?: request()->ip(),
                'fecha_hora' => $now->toDateTimeString(),
                'fecha' => $now->toDateString()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar actividad diaria: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las actividades del día para un técnico (incluye automáticas y manuales).
     */
    public function obtenerActividadesDelDia(int $usuarioId, string $fecha): Collection
    {
        return $this->repository->obtenerPorUsuarioYFecha($usuarioId, $fecha);
    }

    /**
     * Guarda o actualiza un registro manual de actividad diaria para un técnico.
     */
    public function guardarRegistroManual(int $usuarioId, string $fecha, int $hora, array $data): void
    {
        $usuario = Usuario::find($usuarioId);
        if (!$usuario || !$usuario->debeLlenarActividades()) {
            return;
        }

        $horaPad = str_pad((string) $hora, 2, '0', STR_PAD_LEFT);
        $fechaHora = Carbon::createFromFormat('Y-m-d H:i:s', "{$fecha} {$horaPad}:00:00", 'America/Guayaquil')->toDateTimeString();

        $actividad = ActividadDiaria::where('usuario_id', $usuarioId)
            ->where('fecha', $fecha)
            ->where('tipo_accion', 'registro_manual')
            ->whereRaw('HOUR(fecha_hora) = ?', [$hora])
            ->first();

        $ot = (isset($data['ot']) && trim($data['ot']) !== '') ? trim($data['ot']) : 'sn';
        $serie = (isset($data['serie']) && trim($data['serie']) !== '') ? trim($data['serie']) : 'sn';
        $observacion = (isset($data['observacion']) && trim($data['observacion']) !== '') ? trim($data['observacion']) : 'sn';

        $otCleaned = ($ot === 'sn') ? [] : array_filter(array_map('trim', explode(',', $ot)));
        $cantidad = empty($otCleaned) ? 'sn' : count($otCleaned);

        $metadata = [
            'actividad' => $data['actividad'] ?? 'sn',
            'novedad' => $data['novedad'] ?? 'sn',
            'estado' => $data['estado'] ?? 'sn',
            'modalidad' => $data['modalidad'] ?? 'presencial',
            'ot' => $ot,
            'cantidad' => $cantidad,
            'codigo_equipo' => $data['codigo_equipo'] ?? 'sn',
            'clase' => $data['clase'] ?? 'sn',
            'serie' => $serie,
            'codigo_repuesto' => $data['codigo_repuesto'] ?? 'sn'
        ];

        if ($actividad) {
            $actividad->update([
                'descripcion' => $observacion,
                'metadata_json' => $metadata
            ]);
        } else {
            $this->repository->guardar([
                'usuario_id' => $usuarioId,
                'tipo_accion' => 'registro_manual',
                'descripcion' => $observacion,
                'modulo' => 'registro_manual',
                'fecha_hora' => $fechaHora,
                'fecha' => $fecha,
                'metadata_json' => $metadata,
                'ip_address' => request()->ip()
            ]);
        }
    }

    /**
     * Obtiene todas las actividades de un día para administración.
     */
    public function obtenerActividadesDelDiaAdmin(string $fecha): Collection
    {
        return $this->repository->obtenerPorFecha($fecha);
    }

    /**
     * Obtiene los técnicos activos para filtrado administrativo.
     */
    public function obtenerTecnicosActivos(): Collection
    {
        $nombresExcluidos = [
            'Jahaira Cisneros',
            'Carlos Ramos',
            'Antonio Pulido',
            'Evelin Vaca'
        ];
        $usuariosExcluidos = [
            '1725324782',
            '1721443610',
            '0921998878',
            '0957967847'
        ];

        return Usuario::tecnicosOperativos()
            ->whereNotIn('nombre_tecnico', $nombresExcluidos)
            ->whereNotIn('usuario', $usuariosExcluidos)
            ->orderBy('nombre_tecnico', 'asc')
            ->get();
    }
}
