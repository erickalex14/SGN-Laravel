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
    public function ejecutarConLockSecuencial(int $sucursalId, callable $callback): mixed
    {
        $lockName = 'orden_seq_lock_' . $sucursalId;
        $lock = DB::selectOne('SELECT GET_LOCK(?, 10) AS ok', [$lockName]);

        if (!$lock || (int) $lock->ok !== 1) {
            throw new \RuntimeException('No se pudo obtener el lock de secuencial. Intente de nuevo.');
        }

        try {
            return $callback();
        } finally {
            DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    public function generarNumeroOrden(int $sucursalId, ?int $casId = null): string
    {
        if ($casId) {
            $cas = \App\Models\Directory\Cas::find($casId);
            if ($cas && $cas->prefijo) {
                $prefijo = strtoupper($cas->prefijo) . '-';

                // Obtener el maximo consecutivo en ordenes del respectivo CAS
                $maxOrden = Orden::where('cas_id', $casId)
                    ->where('nro_orden', 'like', $prefijo . '%')
                    ->max(DB::raw("CAST(SUBSTRING_INDEX(nro_orden, '-', -1) AS UNSIGNED)"));

                $siguienteNumero = (int)$maxOrden + 1;
                return $prefijo . str_pad((string)$siguienteNumero, 6, '0', STR_PAD_LEFT);
            }
        }

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
        $nroOrden = $prefijo . str_pad((string)$siguienteNumero, 6, '0', STR_PAD_LEFT);

        $reservados = DB::table('preordenes')
            ->whereNull('orden_id')
            ->where('sucursal_id', $sucursalId)
            ->where('nro_preorden', 'like', $prefijo . '%')
            ->pluck('nro_preorden')
            ->map(fn ($valor) => preg_replace('/^PRE(OR)?-/i', '', (string) $valor))
            ->all();

        $intentos = 0;
        while (in_array($nroOrden, $reservados, true) && $intentos < 500) {
            $siguienteNumero++;
            $nroOrden = $prefijo . str_pad((string)$siguienteNumero, 6, '0', STR_PAD_LEFT);
            $intentos++;
        }

        return $nroOrden;
    }

    public function obtenerOrdenesPorTecnico(int $tecnicoId): BaseCollection
    {
        $personales = Orden::with([
                'cliente',
                'equipo.credenciales',
                'sucursal',
                'solicitudesNc',
                'informes',
                'usuarioIngreso',
                'repuestoInventario',
                'ordenRepuestos.repuesto',
                'llamadas.usuario',
            ])
            ->where('tecnico_id', $tecnicoId)
            ->orderBy('id', 'desc')
            ->get();

        $empresas = OrdenEmpresa::with([
                'empresa',
                'equipo',
                'tecnico',
                'tecnicos',
                'sucursal',
                'ingresadoPor',
                'llamadas.usuario',
                'repuestoInventario',
                'ordenRepuestos.repuesto',
            ])
            ->where(function ($q) use ($tecnicoId) {
                $q->where('tecnico_id', $tecnicoId)
                  ->orWhereHas('tecnicos', function ($sq) use ($tecnicoId) {
                      $sq->where('usuarios.id', $tecnicoId);
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        return $personales
            ->concat($empresas)
            ->sortByDesc(fn ($orden) => $orden instanceof OrdenEmpresa
                ? (string) $orden->fecha_ingreso
                : (string) $orden->fecha_de_ingreso
            )
            ->values();
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
            'preciosOrden',
            'repuestoInventario',
            'ordenRepuestos.repuesto',
            'usuarioIngreso',
            'usuarioModificacion',
            'cas',
            'solicitudesNc',
        ])->find($id);
    }

    public function obtenerOrdenEmpresaCompleta(int $id): ?OrdenEmpresa
    {
        return OrdenEmpresa::with([
            'empresa',
            'equipo.series',
            'equipo.tipoServicio',
            'tecnico',
            'tecnicos',
            'sucursal',
            'cas',
            'ingresadoPor',
            'ordenRepuestos.repuesto',
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
        $sucursalesCliente = \App\Models\Directory\SucursalCliente::all();
        $resolverSucursalCliente = function($nroSuc) use ($sucursalesCliente) {
            if ($nroSuc === null || $nroSuc === '') return '';
            if ($nroSuc === '999' || $nroSuc === '999 - SERVICIO EXTERNO') return '999 - SERVICIO EXTERNO';
            $suc = $sucursalesCliente->firstWhere('codigo', $nroSuc);
            if ($suc) return $suc->codigo . ' - ' . $suc->nombre;
            $numeroInt = (int) $nroSuc;
            if ($numeroInt > 0) {
                $suc = $sucursalesCliente->firstWhere('numero', $numeroInt);
                if ($suc) return $suc->codigo . ' - ' . $suc->nombre;
                return 'Nro. ' . str_pad((string) $numeroInt, 3, '0', STR_PAD_LEFT);
            }
            return $nroSuc;
        };

        $incluirPersonal = $filtro->tipo_orden === null || $filtro->tipo_orden === '' || $filtro->tipo_orden === 'personal';
        $incluirEmpresa = $filtro->tipo_orden === null || $filtro->tipo_orden === '' || $filtro->tipo_orden === 'empresa';
        $resultados = collect();

        if ($incluirPersonal) {
            $queryPersonal = Orden::with(['cliente', 'equipo', 'tecnico', 'sucursal', 'cas', 'informes', 'preciosOrden', 'solicitudesNc']);

            if (!empty($filtro->empresa_id)) {
                $queryPersonal->whereRaw('1 = 0');
            }

            if (!empty($filtro->fecha_inicio)) {
                $queryPersonal->whereDate('fecha_de_ingreso', '>=', $filtro->fecha_inicio);
            }

            if (!empty($filtro->fecha_fin)) {
                $queryPersonal->whereDate('fecha_de_ingreso', '<=', $filtro->fecha_fin);
            }

            if (!empty($filtro->estado)) {
                $estFiltro = trim((string) $filtro->estado);
                if (str_starts_with($estFiltro, 'NC') || str_contains(mb_strtolower($estFiltro), 'nota de cred')) {
                    $queryPersonal->whereIn('estado_orden', ['Nota de Credito', 'NC Aprobada-Abierta', 'NC Aprobada-Cerrada']);
                } else {
                    $queryPersonal->where('estado_orden', $estFiltro);
                }
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

            if (!empty($filtro->cas_id)) {
                $queryPersonal->where('cas_id', $filtro->cas_id);
            }

            if (!empty($filtro->garantia_tipo)) {
                $gtFiltro = strtolower(trim((string) $filtro->garantia_tipo));
                if ($gtFiltro === 'interna' || $gtFiltro === 'propia') {
                    $queryPersonal->where(function ($q) {
                        $q->whereIn('garantia_tipo', ['propia', 'interna'])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('garantia_tipo')
                                 ->whereNull('cas_id')
                                 ->where(function($q3) {
                                     $q3->whereNull('garantia_cas')->orWhere('garantia_cas', '');
                                 });
                          });
                    });
                } elseif ($gtFiltro === 'externa') {
                    $queryPersonal->where(function ($q) {
                        $q->where('garantia_tipo', 'externa')
                          ->orWhereNotNull('cas_id')
                          ->orWhere(function($q2) {
                              $q2->whereNotNull('garantia_cas')->where('garantia_cas', '<>', '');
                          });
                    });
                }
            }

             $personales = $queryPersonal->get()->map(function (Orden $orden) use ($resolverSucursalCliente) {
                $fechaIngreso = $orden->fecha_de_ingreso ?: null;
                $fechaPrometida = $orden->fecha_prometido ?: null;
                $fechaEntrega = $orden->fecha_entrega ?: null;
                $clienteNombre = trim((string) (($orden->cliente->nombres ?? '') . ' ' . ($orden->cliente->apellidos ?? '')));
                $equipoNombre = trim((string) (($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')));

                $subtotalAdicionales = $orden->preciosOrden->sum('precio');
                $subtotalTotal = $subtotalAdicionales + 28.00;
                $esGarantia = mb_strtolower(trim((string) $orden->motivo_ingreso)) === 'validacion de garantia';
                $valorNovicompu = $esGarantia ? round(($subtotalTotal * 1.15) * 0.60, 2) : 0.00;
                $valorOtraEmpresa = 0.00;

                $garantiaTipo = (function() use ($orden) {
                    if ($orden->garantia_tipo === 'externa' || $orden->cas_id || (!empty($orden->garantia_cas) && trim($orden->garantia_cas) !== '')) {
                        return 'Externa';
                    }
                    if ($orden->garantia_tipo === 'propia' || $orden->garantia_tipo === 'interna') {
                        return 'Interna';
                    }
                    $motivo = mb_strtolower(trim((string) $orden->motivo_ingreso));
                    if (str_contains($motivo, 'garantia') || !empty($orden->estado_garantia)) {
                        return 'Interna';
                    }
                    return !empty($orden->garantia_tipo) ? ucfirst($orden->garantia_tipo) : 'Interna';
                })();

                $casDestino = ($garantiaTipo === 'Externa' || $orden->cas_id || (!empty($orden->garantia_cas) && trim($orden->garantia_cas) !== ''))
                    ? ($orden->cas?->nombre ?: ($orden->garantia_cas ?: '-'))
                    : '-';

                return [
                    'id' => $orden->id,
                    'tipo_orden' => 'personal',
                    'informe_id' => $orden->informes->first()?->id ?? null,
                    'valor_novicompu' => $valorNovicompu,
                    'valor_otra_empresa' => $valorOtraEmpresa,
                    'nro_orden' => $orden->nro_orden,
                    'fecha_de_ingreso' => $orden->fecha_de_ingreso,
                    'fecha_prometido' => $fechaPrometida,
                    'fecha_entrega' => $fechaEntrega,
                    'motivo_ingreso' => $orden->motivo_ingreso,
                    'subtipo' => '',
                    'estado_repuesto' => $orden->estado_repuesto,
                    'estado_garantia' => $orden->estado_garantia,
                    'garantia_tipo' => $garantiaTipo,
                    'garantia_destino_cas' => $casDestino,
                    'estado_orden' => (function() use ($orden) {
                        if ($orden->estado_orden === 'Nota de Credito') {
                            $solicitudNc = $orden->solicitudesNc->first();
                            if ($solicitudNc && $solicitudNc->estado === 'Aprobada') {
                                return empty($orden->transferencia_numero) ? 'NC Aprobada-Abierta' : 'NC Aprobada-Cerrada';
                            }
                        }
                        return $orden->estado_orden;
                    })(),
                    'transferencia_plataforma' => $orden->transferencia_plataforma,
                    'transferencia_numero' => $orden->transferencia_numero,
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
                    'cas_nombre' => $casDestino,
                    'sucursal_cliente' => $resolverSucursalCliente($orden->nro_sucursal_cliente),
                    'dias_transcurridos' => $fechaIngreso ? (
                        in_array($orden->estado_orden, ['Finalizada', 'Entregada', 'Devuelto sin reparar', 'Nota de Credito', 'REPARADO', 'ENTREGADO', 'DEVUELTO SIN REPARAR'], true)
                        ? Carbon::parse($fechaIngreso)->diffInDays(Carbon::parse($fechaEntrega ?: ($orden->fecha_finalizacion ?: now())))
                        : now()->diffInDays(Carbon::parse($fechaIngreso))
                    ) : null,
                    'vencida' => $fechaPrometida && !$fechaEntrega ? Carbon::parse($fechaPrometida)->isPast() : false,
                    'falla_reportada' => $orden->equipo->falla ?? '',
                    'observacion' => $orden->equipo->observacion ?? '',
                    'tecnico_lider' => '',
                    'tecnicos_asignados' => '',
                    'cantidad_tecnicos' => 1,
                    'horas_trabajadas' => 0,
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
            $queryEmpresa = OrdenEmpresa::with(['empresa', 'equipo', 'tecnico', 'tecnicos', 'sucursal', 'cas']);

            if (!empty($filtro->empresa_id)) {
                $queryEmpresa->where('empresa_id', $filtro->empresa_id);
            }

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

            if (!empty($filtro->cas_id)) {
                $queryEmpresa->where('cas_id', $filtro->cas_id);
            }

            if (!empty($filtro->garantia_tipo)) {
                $gtFiltro = strtolower(trim((string) $filtro->garantia_tipo));
                if ($gtFiltro === 'interna' || $gtFiltro === 'propia') {
                    $queryEmpresa->where(function ($q) {
                        $q->whereNull('cas_id')->orWhere('cas_id', 0);
                    });
                } elseif ($gtFiltro === 'externa') {
                    $queryEmpresa->whereNotNull('cas_id')->where('cas_id', '>', 0);
                }
            }

            if (!empty($filtro->estado_repuesto) && mb_strtolower(trim((string) $filtro->estado_repuesto)) !== 'no requerido') {
                $queryEmpresa->whereRaw('1 = 0');
            }
            if (!empty($filtro->estado_garantia)) {
                $queryEmpresa->whereRaw('1 = 0');
            }

            $empresas = $queryEmpresa->get()->map(function (OrdenEmpresa $orden) use ($resolverSucursalCliente) {
                $nombreEmpresa = $orden->empresa?->nombre ?? 'EMPRESA';
                $identificacionEmpresa = (string) ($orden->empresa?->ruc ?? $orden->empresa?->identificacion ?? '');
                $fechaIngreso = $orden->fecha_ingreso ?: null;
                $equipoNombre = trim((string) (($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')));

                $esNovisolutionsServicio = ($orden->subtipo === 'Servicios');
                if ($esNovisolutionsServicio) {
                    $cantTecnicos = $orden->tecnicos->isNotEmpty() ? $orden->tecnicos->count() : 1;
                    $horas = (float) ($orden->horas_trabajadas ?? 0);
                    $valHora = (float) ($orden->valor_hora ?? 0);
                    $subtotalTotal = $cantTecnicos * $horas * $valHora;
                } else {
                    $subtotalTotal = 28.00;
                }

                $esNovisolutions = (strtoupper(trim($nombreEmpresa)) === 'NOVISOLUTONS CIA. LTDA.');
                
                $valorNovicompu = 0.00;
                $valorOtraEmpresa = 0.00;

                if ($esNovisolutions) {
                    $valorNovicompu = round($subtotalTotal, 2);
                } else {
                    $valorOtraEmpresa = round($subtotalTotal, 2);
                }

                $garantiaTipo = ($orden->cas_id && $orden->cas_id > 0) ? 'Externa' : (str_contains(mb_strtolower((string)$orden->subtipo), 'garantia') ? 'Interna' : '-');
                $casDestino = ($orden->cas_id && $orden->cas_id > 0) ? ($orden->cas?->nombre ?: 'CAS #'.$orden->cas_id) : '-';

                return [
                    'id' => 'empresa-' . $orden->id,
                    'tipo_orden' => 'empresa',
                    'informe_id' => null,
                    'valor_novicompu' => $valorNovicompu,
                    'valor_otra_empresa' => $valorOtraEmpresa,
                    'nro_orden' => $orden->nro_orden,
                    'fecha_de_ingreso' => $orden->fecha_ingreso,
                    'fecha_prometido' => $orden->fecha_prometido,
                    'fecha_entrega' => $orden->fecha_entrega,
                    'motivo_ingreso' => $orden->subtipo,
                    'subtipo' => $orden->subtipo,
                    'estado_repuesto' => 'No requerido',
                    'estado_garantia' => '',
                    'garantia_tipo' => $garantiaTipo,
                    'garantia_destino_cas' => $casDestino,
                    'estado_orden' => $orden->estado,
                    'transferencia_plataforma' => null,
                    'transferencia_numero' => null,
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
                    'cas_nombre' => $casDestino,
                    'sucursal_cliente' => $resolverSucursalCliente($orden->nro_sucursal_cliente),
                    'dias_transcurridos' => $fechaIngreso ? (
                        in_array($orden->estado, ['Finalizada', 'Entregada', 'Devuelto sin reparar', 'Nota de Credito', 'REPARADO', 'ENTREGADO', 'DEVUELTO SIN REPARAR'], true)
                        ? Carbon::parse($fechaIngreso)->diffInDays(Carbon::parse($orden->fecha_entrega ?: ($orden->fecha_finalizacion ?: now())))
                        : now()->diffInDays(Carbon::parse($fechaIngreso))
                    ) : null,
                    'vencida' => $orden->fecha_prometido ? Carbon::parse($orden->fecha_prometido)->isPast() : false,
                    'falla_reportada' => $esNovisolutionsServicio ? $orden->descripcion : ($orden->equipo->falla ?? ''),
                    'observacion' => $orden->equipo->observacion ?? '',
                    'tecnico_lider' => $esNovisolutionsServicio ? ($orden->tecnico->nombre_tecnico ?? '') : '',
                    'tecnicos_asignados' => $esNovisolutionsServicio ? $orden->tecnicos->pluck('nombre_tecnico')->implode(', ') : '',
                    'cantidad_tecnicos' => $esNovisolutionsServicio ? $cantTecnicos : 1,
                    'horas_trabajadas' => $esNovisolutionsServicio ? (float) ($orden->horas_trabajadas ?? 0) : 0,
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
                return (string) ($fila['nro_orden'] ?? '');
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
