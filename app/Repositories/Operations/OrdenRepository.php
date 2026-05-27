<?php

namespace App\Repositories\Operations;

use App\DTOs\Operations\ReporteFiltroDTO;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Directory\Sucursal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdenRepository
{
    public function generarNumeroOrden(int $sucursalId): string
    {
        $sucursal = Sucursal::find($sucursalId);
        $secuencial = $sucursal ? strtoupper($sucursal->secuencial) : 'NOV';
        $prefijo = $secuencial . '-';

        // Obtener el maximo consecutivo en ordenes y ordenesempresas
        $maxOrden = Orden::where('sucursal_id', $sucursalId)
            ->where('nro_orden', 'like', $prefijo . '%')
            ->max(DB::raw("CAST(SUBSTRING_INDEX(nro_orden, '-', -1) AS UNSIGNED)"));

        $maxEmpresa = OrdenEmpresa::where('sucursal_id', $sucursalId)
            ->where('nro_orden', 'like', $prefijo . '%')
            ->max(DB::raw("CAST(SUBSTRING_INDEX(nro_orden, '-', -1) AS UNSIGNED)"));

        $siguienteNumero = max((int)$maxOrden, (int)$maxEmpresa) + 1;

        return $prefijo . str_pad((string)$siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    public function obtenerOrdenesPorTecnico(int $tecnicoId): Collection
    {
        return Orden::with([
                'cliente',
                'equipo.credenciales',
                'sucursal',
                'solicitudesNc',
                'informes',
                'usuarioIngreso',
                'repuestoInventario',
            ])
            ->where('tecnico_id', $tecnicoId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function buscarPorId(int $id): ?Orden
    {
        return Orden::find($id);
    }

    public function obtenerOrdenCompleta(int $id): ?Orden
    {
        return Orden::with([
            'cliente',
            'equipo',
            'tecnico',
            'sucursal',
            'precioEstandar',
            'repuestoInventario',
            'usuarioIngreso',
            'usuarioModificacion',
            'cas',
        ])->find($id);
    }

    public function buscarPorNumeroONombre(string $termino): Collection
    {
        return Orden::with(['cliente', 'equipo'])
            ->where('nro_orden', 'like', "%{$termino}%")
            ->orWhereHas('cliente', function ($query) use ($termino) {
                $query->where('identificacion', 'like', "%{$termino}%")
                      ->orWhere('nombres', 'like', "%{$termino}%")
                      ->orWhere('apellidos', 'like', "%{$termino}%");
            })
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();
    }

    public function contarOrdenesActivasGlobales(): int
    {
        return Orden::whereNotIn('estado_orden', [
            'Entregada',
            'Devuelto sin reparar',
            'Nota de Credito',
            'ENTREGADO',
            'DEVUELTO SIN REPARAR'
        ])->count();
    }

    public function contarOrdenesActivasPorTecnico(int $tecnicoId): int
    {
        return Orden::where('tecnico_id', $tecnicoId)
            ->whereNotIn('estado_orden', [
                'Entregada',
                'Devuelto sin reparar',
                'Nota de Credito',
                'ENTREGADO',
                'DEVUELTO SIN REPARAR'
            ])
            ->count();
    }

    public function contarEquiposReparadosMesActual(): int
    {
        return Orden::whereIn('estado_orden', ['Finalizada', 'REPARADO'])
            ->whereMonth('fecha_modificacion', Carbon::now()->month)
            ->whereYear('fecha_modificacion', Carbon::now()->year)
            ->count();
    }

    public function filtrarParaReporte(ReporteFiltroDTO $filtro, bool $esMaster, int $sucursalSesion): BaseCollection
    {
        $incluirPersonal = $filtro->tipo_orden === null || $filtro->tipo_orden === '' || $filtro->tipo_orden === 'personal';
        $incluirEmpresa = $filtro->tipo_orden === null || $filtro->tipo_orden === '' || $filtro->tipo_orden === 'empresa';
        $resultados = collect();

        if ($incluirPersonal) {
            $queryPersonal = Orden::with(['cliente', 'equipo', 'tecnico', 'sucursal']);

            if (!empty($filtro->fecha_inicio)) {
                $queryPersonal->whereDate('fecha_de_ingreso', '>=', $filtro->fecha_inicio);
            }

            if (!empty($filtro->fecha_fin)) {
                $queryPersonal->whereDate('fecha_de_ingreso', '<=', $filtro->fecha_fin);
            }

            if (!empty($filtro->estado)) {
                $queryPersonal->where('estado_orden', $filtro->estado);
            }

            if (!empty($filtro->estado_repuesto)) {
                $queryPersonal->where('estado_repuesto', $filtro->estado_repuesto);
            }

            if (!empty($filtro->estado_garantia)) {
                $queryPersonal->where('estado_garantia', $filtro->estado_garantia);
            }

            if (!empty($filtro->motivo_ingreso)) {
                $queryPersonal->where('motivo_ingreso', $filtro->motivo_ingreso);
            }

            if (!empty($filtro->marca)) {
                $queryPersonal->whereHas('equipo', function ($equipoQuery) use ($filtro) {
                    $equipoQuery->where('marca', $filtro->marca);
                });
            }

            if (!empty($filtro->tipo_equipo)) {
                $queryPersonal->whereHas('equipo', function ($equipoQuery) use ($filtro) {
                    $equipoQuery->where('tipo', $filtro->tipo_equipo);
                });
            }

            if (!empty($filtro->tecnico_id)) {
                $queryPersonal->where('tecnico_id', $filtro->tecnico_id);
            }

            if (!empty($filtro->sucursal_id)) {
                $queryPersonal->where('sucursal_id', $filtro->sucursal_id);
            }
            if (!$esMaster && $sucursalSesion > 0) {
                $queryPersonal->where('sucursal_id', $sucursalSesion);
            }

            $personales = $queryPersonal->get()->map(function (Orden $orden) {
                $fechaIngreso = $orden->fecha_de_ingreso ?: null;
                $fechaPrometida = $orden->fecha_prometido ?: null;
                $fechaEntrega = $orden->fecha_entrega ?: null;
                $clienteNombre = trim((string) (($orden->cliente->nombres ?? '') . ' ' . ($orden->cliente->apellidos ?? '')));
                $equipoNombre = trim((string) (($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')));

                return [
                    'id' => $orden->id,
                    'tipo_orden' => 'personal',
                    'nro_orden' => $orden->nro_orden,
                    'fecha_de_ingreso' => $orden->fecha_de_ingreso,
                    'fecha_prometido' => $fechaPrometida,
                    'fecha_entrega' => $fechaEntrega,
                    'motivo_ingreso' => $orden->motivo_ingreso,
                    'estado_repuesto' => $orden->estado_repuesto,
                    'estado_garantia' => $orden->estado_garantia,
                    'estado_orden' => $orden->estado_orden,
                    'tecnico_id' => $orden->tecnico_id,
                    'sucursal_id' => $orden->sucursal_id,
                    'cliente_nombre' => $clienteNombre,
                    'identificacion' => (string) ($orden->cliente->identificacion ?? ''),
                    'cliente_telefono' => (string) ($orden->cliente->numero_contacto ?? ''),
                    'cliente_correo' => (string) ($orden->cliente->correo ?? ''),
                    'cliente_direccion' => (string) ($orden->cliente->direccion_clientes ?? ''),
                    'equipo_nombre' => $equipoNombre,
                    'marca' => (string) ($orden->equipo->marca ?? ''),
                    'tipo_equipo' => (string) ($orden->equipo->tipo ?? ''),
                    'serie' => (string) ($orden->equipo->serie ?? ''),
                    'tecnico_nombre' => (string) ($orden->tecnico->nombre_tecnico ?? ''),
                    'sucursal_nombre' => (string) ($orden->sucursal->ciudad ?? ''),
                    'dias_transcurridos' => $fechaIngreso ? now()->diffInDays(Carbon::parse($fechaIngreso)) : null,
                    'vencida' => $fechaPrometida && !$fechaEntrega ? Carbon::parse($fechaPrometida)->isPast() : false,
                    'cliente' => $orden->cliente ? [
                        'nombres' => $orden->cliente->nombres,
                        'apellidos' => $orden->cliente->apellidos,
                        'identificacion' => $orden->cliente->identificacion,
                    ] : null,
                    'equipo' => $orden->equipo ? [
                        'marca' => $orden->equipo->marca,
                        'modelo' => $orden->equipo->modelo,
                        'serie' => $orden->equipo->serie,
                        'tipo' => $orden->equipo->tipo,
                    ] : null,
                    'tecnico' => $orden->tecnico ? [
                        'nombre_tecnico' => $orden->tecnico->nombre_tecnico,
                    ] : null,
                    'sucursal' => $orden->sucursal ? [
                        'ciudad' => $orden->sucursal->ciudad,
                    ] : null,
                ];
            });

            $resultados = $resultados->concat($personales);
        }

        if ($incluirEmpresa) {
            $queryEmpresa = OrdenEmpresa::with(['empresa', 'equipo', 'tecnico', 'sucursal']);

            if (!empty($filtro->fecha_inicio)) {
                $queryEmpresa->whereDate('fecha_ingreso', '>=', $filtro->fecha_inicio);
            }

            if (!empty($filtro->fecha_fin)) {
                $queryEmpresa->whereDate('fecha_ingreso', '<=', $filtro->fecha_fin);
            }

            if (!empty($filtro->estado)) {
                $queryEmpresa->where('estado', $filtro->estado);
            }

            if (!empty($filtro->motivo_ingreso)) {
                $queryEmpresa->where('subtipo', $filtro->motivo_ingreso);
            }

            if (!empty($filtro->marca)) {
                $queryEmpresa->whereHas('equipo', function ($equipoQuery) use ($filtro) {
                    $equipoQuery->where('marca', $filtro->marca);
                });
            }

            if (!empty($filtro->tipo_equipo)) {
                $queryEmpresa->whereHas('equipo', function ($equipoQuery) use ($filtro) {
                    $equipoQuery->where('tipo', $filtro->tipo_equipo);
                });
            }

            if (!empty($filtro->tecnico_id)) {
                $queryEmpresa->where('tecnico_id', $filtro->tecnico_id);
            }

            if (!empty($filtro->sucursal_id)) {
                $queryEmpresa->where('sucursal_id', $filtro->sucursal_id);
            }
            if (!$esMaster && $sucursalSesion > 0) {
                $queryEmpresa->where('sucursal_id', $sucursalSesion);
            }

            if (!empty($filtro->estado_repuesto) && mb_strtolower(trim((string) $filtro->estado_repuesto)) !== 'no requerido') {
                $queryEmpresa->whereRaw('1 = 0');
            }
            if (!empty($filtro->estado_garantia)) {
                $queryEmpresa->whereRaw('1 = 0');
            }

            $empresas = $queryEmpresa->get()->map(function (OrdenEmpresa $orden) {
                $nombreEmpresa = $orden->empresa?->nombre ?? 'EMPRESA';
                $identificacionEmpresa = (string) ($orden->empresa?->ruc ?? $orden->empresa?->identificacion ?? '');
                $fechaIngreso = $orden->fecha_ingreso ?: null;
                $equipoNombre = trim((string) (($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')));

                return [
                    'id' => 'empresa-' . $orden->id,
                    'tipo_orden' => 'empresa',
                    'nro_orden' => $orden->nro_orden,
                    'fecha_de_ingreso' => $orden->fecha_ingreso,
                    'fecha_prometido' => $orden->fecha_prometido,
                    'fecha_entrega' => null,
                    'motivo_ingreso' => $orden->subtipo,
                    'estado_repuesto' => 'No requerido',
                    'estado_garantia' => '',
                    'estado_orden' => $orden->estado,
                    'tecnico_id' => $orden->tecnico_id,
                    'sucursal_id' => $orden->sucursal_id,
                    'cliente_nombre' => $nombreEmpresa,
                    'identificacion' => $identificacionEmpresa,
                    'cliente_telefono' => (string) ($orden->empresa?->telefono ?? ''),
                    'cliente_correo' => (string) ($orden->empresa?->correo ?? ''),
                    'cliente_direccion' => (string) ($orden->empresa?->direccion_empresa ?? ''),
                    'equipo_nombre' => $equipoNombre,
                    'marca' => (string) ($orden->equipo->marca ?? ''),
                    'tipo_equipo' => (string) ($orden->equipo->tipo ?? ''),
                    'serie' => (string) ($orden->equipo->serie ?? ''),
                    'tecnico_nombre' => (string) ($orden->tecnico->nombre_tecnico ?? ''),
                    'sucursal_nombre' => (string) ($orden->sucursal->ciudad ?? ''),
                    'dias_transcurridos' => $fechaIngreso ? now()->diffInDays(Carbon::parse($fechaIngreso)) : null,
                    'vencida' => $orden->fecha_prometido ? Carbon::parse($orden->fecha_prometido)->isPast() : false,
                    'cliente' => [
                        'nombres' => $nombreEmpresa,
                        'apellidos' => '',
                        'identificacion' => $identificacionEmpresa,
                    ],
                    'equipo' => $orden->equipo ? [
                        'marca' => $orden->equipo->marca,
                        'modelo' => $orden->equipo->modelo,
                        'serie' => $orden->equipo->serie,
                        'tipo' => $orden->equipo->tipo,
                    ] : null,
                    'tecnico' => $orden->tecnico ? [
                        'nombre_tecnico' => $orden->tecnico->nombre_tecnico,
                    ] : null,
                    'sucursal' => $orden->sucursal ? [
                        'ciudad' => $orden->sucursal->ciudad,
                    ] : null,
                ];
            });

            $resultados = $resultados->concat($empresas);
        }

        return $resultados
            ->sortByDesc(function (array $fila) {
                return strtotime((string) ($fila['fecha_de_ingreso'] ?? '1970-01-01 00:00:00'));
            })
            ->values();
    }

    public function obtenerOrdenesElegiblesParaNc(int $tecnicoId, bool $esAdmin): Collection
    {
        $query = Orden::query()
            ->select('id', 'nro_orden', 'estado_orden')
            ->withCount('solicitudesNc as solicitudes_nc_count')
            ->whereRaw(
                "UPPER(REPLACE(REPLACE(TRIM(COALESCE(motivo_ingreso, '')), 'Á', 'A'), 'Í', 'I')) = 'VALIDACION DE GARANTIA'"
            )
            ->whereRaw(
                "UPPER(TRIM(COALESCE(estado_orden, ''))) IN ('FINALIZADA', 'ENTREGADA', 'NOTA DE CREDITO', 'NOTA DE CRÉDITO')"
            )
            ->orderByDesc('id');

        if (!$esAdmin) {
            $query->where('tecnico_id', $tecnicoId);
        }

        return $query->get();
    }

    public function obtenerOrdenesElegiblesParaRepuesto(int $tecnicoId, bool $esAdmin): Collection
    {
        $query = Orden::query()
            ->select('id', 'nro_orden', 'estado_orden')
            ->whereNotIn('estado_orden', ['Entregada', 'Nota de Credito'])
            ->whereDoesntHave('solicitudesRepuesto')
            ->orderByDesc('id');

        if (!$esAdmin) {
            $query->where('tecnico_id', $tecnicoId);
        }

        return $query->get();
    }
}
