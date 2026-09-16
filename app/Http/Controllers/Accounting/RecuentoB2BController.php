<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Operations\Orden;
use App\Models\Directory\Empresa;
use App\Models\Directory\Sucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

class RecuentoB2BController extends Controller
{
    public function index(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $sa = session('es_superadmin');
        $p = session('permisos', []);
        $rolNombre = mb_strtolower(trim((string) ($usuario->rol->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) ($usuario->grupo->nombre ?? '')));
        $esAdminMaster = $sa
            || (bool) ($usuario->grupo->es_superadmin ?? false)
            || in_array($rolNombre, ['admin master', 'administrador master'], true)
            || in_array($grupoNombre, ['admin master', 'administrador master', 'superadministrador'], true);

        $tienePermisoRecuentoB2B = !empty($p['recuento_b2b']['ver']) || !empty($p['recuento_b2b']['crear']) || !empty($p['recuento_b2b']['editar']);

        if (!$esAdminMaster && !$tienePermisoRecuentoB2B) {
            abort(403, 'Acceso denegado. No tienes permisos para acceder a Recuento B2B.');
        }

        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $empresaFiltro = $request->query('empresa', '');
        $sucursalFiltro = $request->query('sucursal_id', '');
        $buscarFiltro = trim((string) $request->query('buscar', ''));
        $fechaDesde = $request->query('fecha_desde', '');
        $fechaHasta = $request->query('fecha_hasta', '');

        // Determinar sucursales permitidas según rol
        $sucursalesUserIds = session('sucursales_ids', []);
        if (empty($sucursalesUserIds) && !empty($usuario->sucursal_id)) {
            $sucursalesUserIds = [(int) $usuario->sucursal_id];
        }

        // --- 1. Obtener Órdenes de Empresa ---
        $query = OrdenEmpresa::with(['empresa', 'equipo', 'tecnicos', 'ingresadoPor', 'sucursal', 'ordenRepuestos.repuesto'])
            ->whereIn('estado', ['Finalizada', 'Entregada'])
            ->where(function($q) {
                $q->whereNull('estado_facturacion')
                  ->orWhere('estado_facturacion', 'Pendiente');
            });

        if ($fechaDesde !== '') {
            $query->whereDate('fecha_ingreso', '>=', $fechaDesde);
        }
        if ($fechaHasta !== '') {
            $query->whereDate('fecha_ingreso', '<=', $fechaHasta);
        }

        if ($empresaFiltro !== '') {
            $query->whereHas('empresa', function($q) use ($empresaFiltro) {
                $q->where('nombre', 'LIKE', '%' . $empresaFiltro . '%');
            });
        }

        if ($buscarFiltro !== '') {
            $numDigits = preg_replace('/\D/', '', $buscarFiltro);
            $numUnpadded = ltrim($numDigits, '0');

            $query->where(function($q) use ($buscarFiltro, $numUnpadded) {
                $q->where('nro_orden', 'LIKE', '%' . $buscarFiltro . '%')
                  ->orWhere('memo_entrega', 'LIKE', '%' . $buscarFiltro . '%')
                  ->orWhere('subtipo', 'LIKE', '%' . $buscarFiltro . '%')
                  ->orWhereHas('empresa', function($e) use ($buscarFiltro) {
                      $e->where('nombre', 'LIKE', '%' . $buscarFiltro . '%')
                        ->orWhere('ruc', 'LIKE', '%' . $buscarFiltro . '%');
                  })
                  ->orWhereHas('equipo', function($eq) use ($buscarFiltro) {
                      $eq->where('serie', 'LIKE', '%' . $buscarFiltro . '%')
                         ->orWhere('marca', 'LIKE', '%' . $buscarFiltro . '%')
                         ->orWhere('modelo', 'LIKE', '%' . $buscarFiltro . '%');
                  })
                  ->orWhereHas('tecnicos', function($t) use ($buscarFiltro) {
                      $t->where('nombre_tecnico', 'LIKE', '%' . $buscarFiltro . '%');
                  });

                if ($numUnpadded !== '') {
                    $q->orWhere('nro_orden', 'LIKE', '%' . $numUnpadded . '%')
                      ->orWhere('nro_orden', 'LIKE', '%' . sprintf('%06d', (int)$numUnpadded) . '%')
                      ->orWhere('nro_orden', 'LIKE', '%' . sprintf('%05d', (int)$numUnpadded) . '%')
                      ->orWhere('nro_orden', 'LIKE', '%' . sprintf('%04d', (int)$numUnpadded) . '%');
                }
            });
        }

        if ($esAdminMaster) {
            if ($sucursalFiltro !== '') {
                $query->where('sucursal_id', (int) $sucursalFiltro);
            }
        } else {
            if (!empty($sucursalesUserIds)) {
                $query->whereIn('sucursal_id', $sucursalesUserIds);
            }
        }

        $ordenesEmpresa = $query->orderByDesc('id')->get();

        // Procesar órdenes de empresa
        $ordenesEmpresaProcesadas = $ordenesEmpresa->map(function ($ord) {
            $empresaNombre = strtoupper(trim($ord->empresa->nombre ?? ''));
            $subtipoRaw = trim($ord->subtipo ?? 'Servicios');
            
            $subtipoLower = mb_strtolower($subtipoRaw);
            if ($subtipoLower === 'stock') {
                $subtipoNorm = 'Stock';
            } elseif ($subtipoLower === 'autoconsumo') {
                $subtipoNorm = 'Autoconsumo';
            } elseif (str_contains($subtipoLower, 'garant')) {
                $subtipoNorm = 'Garantía';
            } else {
                $subtipoNorm = 'Servicios';
            }

            $horas = (float) ($ord->horas_trabajadas ?? 1.0);
            if ($horas <= 0) $horas = 1.0;

            $cantidadTecnicos = $ord->tecnicos ? $ord->tecnicos->count() : 1;
            if ($cantidadTecnicos <= 0) $cantidadTecnicos = 1;

            $isNovisolutions = str_contains($empresaNombre, 'NOVI') || str_contains($empresaNombre, 'SOLUT') || (int)($ord->empresa_id ?? 0) === 1;

            // Extraer y calcular repuestos usados (cobrados al 100%)
            $repuestosDetalleArr = [];
            $totalRepuestosUsados = 0.0;
            if ($ord->ordenRepuestos) {
                foreach ($ord->ordenRepuestos as $orp) {
                    $repNombre = $orp->repuesto->nombre ?? 'Repuesto';
                    $repCant = (int) ($orp->cantidad ?? 1);
                    $repPrecio = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                    $repSubtotal = round($repCant * $repPrecio, 2);
                    $totalRepuestosUsados += $repSubtotal;
                    $repuestosDetalleArr[] = "{$repCant}x {$repNombre} ($" . number_format($repPrecio, 2) . ")";
                }
            }
            if ($totalRepuestosUsados <= 0 && (float)($ord->valor_repuestos ?? 0) > 0) {
                $totalRepuestosUsados = (float)$ord->valor_repuestos;
            }

            $valorManoObra = (float) ($ord->valor_mano_obra ?? 0.0);
            $tituloServicio = trim((string) ($ord->titulo_servicio ?? ''));

            $tarifa = 0.0;
            $valorFijo = 0.0;
            $valorManoObraCobrado = 0.0;
            $valorTotal = 0.0;

            $valHora = (float) ($ord->valor_hora ?? 0.0);
            $esServicio = ($subtipoNorm === 'Servicios') || ($valHora > 0 && (float)($ord->horas_trabajadas ?? 0) > 0);

            if ($esServicio) {
                if ($valHora <= 0) {
                    $valHora = (str_contains($empresaNombre, 'RB') || str_contains($empresaNombre, 'HEALTH')) ? 52.0 : 50.0;
                }
                $tarifa = $valHora;
                $valorFijo = round($cantidadTecnicos * $horas * $valHora, 2);
                $valorManoObraCobrado = $valorManoObra;
                $valorTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);
            } elseif ($isNovisolutions) {
                // REGLA OFICIAL NOVISOLUTIONS PARA STOCK / AUTOCONSUMO / GARANTIA:
                // Tarifa Base Fija: $14.25
                // Mano de Obra: -50% (valor_mano_obra * 0.50)
                // Repuestos Usados: 100% cobrado
                // Subtotal Orden = 14.25 + (valor_mano_obra * 0.50) + repuestos_usados
                $valorFijo = 14.25;
                $valorManoObraCobrado = round($valorManoObra * 0.50, 2);
                $valorTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);
                $tarifa = $valorFijo;
            } elseif (str_contains($empresaNombre, 'RB') || str_contains($empresaNombre, 'HEALTH')) {
                $tarifa = 52.0;
                $valorTotal = round(($horas * $tarifa) + $totalRepuestosUsados, 2);
                $valorFijo = $valorTotal;
            } else {
                $tarifa = (float) ($ord->presupuesto ?? $ord->total ?? 50.0);
                $valorTotal = round(($tarifa > 0 ? $tarifa : 50.0) + $totalRepuestosUsados, 2);
                $valorFijo = $valorTotal;
            }

            $ord->tipo_orden_origen = 'empresa';
            $ord->subtipo_normalizado = $subtipoNorm;
            $ord->tarifa_calculada = $tarifa;
            $ord->horas_calculadas = $horas;
            $ord->tecnicos_count = $cantidadTecnicos;
            $ord->is_novisolutions = $isNovisolutions;
            $ord->valor_fijo_calculado = $valorFijo;
            $ord->valor_mano_obra_original = $valorManoObra;
            $ord->valor_mano_obra_cobrado = $valorManoObraCobrado;
            $ord->valor_repuestos_calculado = round($totalRepuestosUsados, 2);
            $ord->repuestos_detalle_str = implode(', ', $repuestosDetalleArr);
            $ord->titulo_servicio_mostrado = $tituloServicio;
            $ord->valor_total_calculado = round($valorTotal, 2);

            return $ord;
        });

        // --- 2. Obtener Órdenes de Garantía (Personales asignadas a NOVISOLUTONS CIA. LTDA.) ---
        $empNovisolutions = Empresa::where('nombre', 'LIKE', '%NOVI%')
            ->orWhere('nombre', 'LIKE', '%SOLUT%')
            ->first();

        $ordenesGarantiaProcesadas = collect();

        if ($empNovisolutions && ($empresaFiltro === '' || str_contains(strtoupper($empresaFiltro), 'NOVI') || str_contains(strtoupper($empresaFiltro), 'SOLUT'))) {
            $queryGarantia = Orden::with(['cliente', 'equipo', 'tecnico', 'sucursal', 'preciosOrden', 'ordenRepuestos.repuesto'])
                ->whereIn('estado_orden', ['Finalizada', 'Entregada'])
                ->where(function($q) {
                    $q->whereNull('estado_facturacion')
                      ->orWhere('estado_facturacion', 'Pendiente');
                })
                ->where(function($q) {
                    $q->whereNotNull('garantia_tipo')->where('garantia_tipo', '!=', '')
                      ->orWhereNotNull('estado_garantia')->where('estado_garantia', '!=', '')
                      ->orWhere('motivo_ingreso', 'LIKE', '%garant%');
                });

            if ($fechaDesde !== '') {
                $queryGarantia->whereDate('fecha_de_ingreso', '>=', $fechaDesde);
            }
            if ($fechaHasta !== '') {
                $queryGarantia->whereDate('fecha_de_ingreso', '<=', $fechaHasta);
            }

            if ($buscarFiltro !== '') {
                $numDigits = preg_replace('/\D/', '', $buscarFiltro);
                $numUnpadded = ltrim($numDigits, '0');

                $queryGarantia->where(function($q) use ($buscarFiltro, $numUnpadded) {
                    $q->where('nro_orden', 'LIKE', '%' . $buscarFiltro . '%')
                      ->orWhere('memo_entrega', 'LIKE', '%' . $buscarFiltro . '%')
                      ->orWhere('motivo_ingreso', 'LIKE', '%' . $buscarFiltro . '%')
                      ->orWhereHas('cliente', function($c) use ($buscarFiltro) {
                          $c->where('nombres', 'LIKE', '%' . $buscarFiltro . '%')
                            ->orWhere('apellidos', 'LIKE', '%' . $buscarFiltro . '%')
                            ->orWhere('identificacion', 'LIKE', '%' . $buscarFiltro . '%');
                      })
                      ->orWhereHas('equipo', function($eq) use ($buscarFiltro) {
                          $eq->where('serie', 'LIKE', '%' . $buscarFiltro . '%')
                             ->orWhere('marca', 'LIKE', '%' . $buscarFiltro . '%')
                             ->orWhere('modelo', 'LIKE', '%' . $buscarFiltro . '%');
                      })
                      ->orWhereHas('tecnico', function($t) use ($buscarFiltro) {
                          $t->where('nombre_tecnico', 'LIKE', '%' . $buscarFiltro . '%');
                      });

                if ($numUnpadded !== '') {
                    $q->orWhere('nro_orden', 'LIKE', '%' . $numUnpadded . '%')
                      ->orWhere('nro_orden', 'LIKE', '%' . sprintf('%06d', (int)$numUnpadded) . '%')
                      ->orWhere('nro_orden', 'LIKE', '%' . sprintf('%05d', (int)$numUnpadded) . '%')
                      ->orWhere('nro_orden', 'LIKE', '%' . sprintf('%04d', (int)$numUnpadded) . '%');
                }
            });
        }

        if ($esAdminMaster) {
            if ($sucursalFiltro !== '') {
                $queryGarantia->where('sucursal_id', (int) $sucursalFiltro);
            }
        } else {
            if (!empty($sucursalesUserIds)) {
                $queryGarantia->whereIn('sucursal_id', $sucursalesUserIds);
            }
        }

        $ordenesGarantia = $queryGarantia->orderByDesc('id')->get();

        $ordenesGarantiaProcesadas = $ordenesGarantia->map(function ($ord) use ($empNovisolutions) {
            // Extraer repuestos usados
            $repuestosDetalleArr = [];
            $totalRepuestosUsados = 0.0;
            if ($ord->ordenRepuestos) {
                foreach ($ord->ordenRepuestos as $orp) {
                    $repNombre = $orp->repuesto->nombre ?? 'Repuesto';
                    $repCant = (int) ($orp->cantidad ?? 1);
                    $repPrecio = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                    $repSubtotal = round($repCant * $repPrecio, 2);
                    $totalRepuestosUsados += $repSubtotal;
                    $repuestosDetalleArr[] = "{$repCant}x {$repNombre} ($" . number_format($repPrecio, 2) . ")";
                }
            }
            if ($totalRepuestosUsados <= 0 && (float)($ord->valor_repuestos ?? 0) > 0) {
                $totalRepuestosUsados = (float)$ord->valor_repuestos;
            }

            $valorManoObra = (float) ($ord->valor_mano_obra ?? 0.0);
            $tituloServicio = trim((string) ($ord->titulo_servicio ?? ''));

            // Regla Novisolutions para Garantías / Personales:
            // Base Fija: $28.50 - 50% = $14.25
            // Mano de obra: - 50%
            // Repuestos: 100%
            $valorFijo = 14.25;
            $valorManoObraCobrado = round($valorManoObra * 0.50, 2);
            $valorTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);

            $ord->empresa = $empNovisolutions;
            $ord->tipo_orden_origen = 'personal';
            $ord->subtipo = 'Garantía';
            $ord->subtipo_normalizado = 'Garantía';
            $ord->horas_calculadas = 1.0;
            $ord->tecnicos_count = 1;
            $ord->is_novisolutions = true;
            $ord->tarifa_calculada = $valorFijo;
            $ord->valor_fijo_calculado = $valorFijo;
            $ord->valor_mano_obra_original = $valorManoObra;
            $ord->valor_mano_obra_cobrado = $valorManoObraCobrado;
            $ord->valor_repuestos_calculado = round($totalRepuestosUsados, 2);
            $ord->repuestos_detalle_str = implode(', ', $repuestosDetalleArr);
            $ord->titulo_servicio_mostrado = $tituloServicio;
            $ord->valor_total_calculado = $valorTotal;
            $ord->descripcion = $ord->motivo_ingreso ?? 'Garantía de producto Novicompu / Novisolutions';

            return $ord;
        });
    }

        // Combinar ambas listas
        $ordenesProcesadas = $ordenesEmpresaProcesadas->concat($ordenesGarantiaProcesadas);

        // Agrupar por Empresa y Subtipo
        $ordenesPorEmpresa = $ordenesProcesadas->groupBy(function ($ord) {
            return trim($ord->empresa->nombre ?? 'OTRA EMPRESA');
        })->map(function ($grupoEmpresa) {
            return [
                'todas' => $grupoEmpresa,
                'servicio' => $grupoEmpresa->filter(fn($o) => $o->subtipo_normalizado === 'Servicios'),
                'stock' => $grupoEmpresa->filter(fn($o) => $o->subtipo_normalizado === 'Stock'),
                'autoconsumo' => $grupoEmpresa->filter(fn($o) => $o->subtipo_normalizado === 'Autoconsumo'),
                'garantia' => $grupoEmpresa->filter(fn($o) => $o->subtipo_normalizado === 'Garantía'),
            ];
        });

        // Historial de Lotes con Paginación
        $tabActiva = $request->query('tab', ($request->has('page_lotes') ? 'historial' : 'pendientes'));
        $lotesProcesados = DB::table('recuento_b2b_lote')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'page_lotes')
            ->withQueryString();

        $empresasSelect = Empresa::orderBy('nombre')->get();
        $sucursalesSelect = $esAdminMaster ? Sucursal::orderBy('ciudad')->get() : collect();

        return view('accounting.recuento_b2b', [
            'esAdminMaster' => $esAdminMaster,
            'empresaFiltro' => $empresaFiltro,
            'sucursalFiltro' => $sucursalFiltro,
            'buscarFiltro' => $buscarFiltro,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'tabActiva' => $tabActiva,
            'ordenes' => $ordenesProcesadas,
            'ordenesPorEmpresa' => $ordenesPorEmpresa,
            'empresasSelect' => $empresasSelect,
            'sucursalesSelect' => $sucursalesSelect,
            'lotesProcesados' => $lotesProcesados,
        ]);
    }

    public function procesarCobro(Request $request)
    {
        ini_set('memory_limit', '512M');
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.']);
        }

        $itemsRequest = $request->input('ordenes');
        if (empty($itemsRequest) && $request->has('ordenes_json')) {
            $itemsRequest = json_decode($request->input('ordenes_json'), true);
        }

        if (empty($itemsRequest) || !is_array($itemsRequest)) {
            return response()->json(['ok' => false, 'error' => 'Debe seleccionar al menos una orden para cobrar.']);
        }

        $empresaNombre = $request->input('empresa_nombre');
        $subtotal = (float) ($request->input('subtotal') ?? array_sum(array_column($itemsRequest, 'valor_total')));
        $montoIva = (float) ($request->input('monto_iva') ?? round($subtotal * 0.15, 2));
        $totalConIva = (float) ($request->input('total_con_iva') ?? round($subtotal + $montoIva, 2));

        $montoNetoBanco = (float) $request->input('monto_neto_banco');
        $montoRetencionRenta = (float) ($request->input('monto_retencion_renta') ?? 0);
        $montoRetencionIva = (float) ($request->input('monto_retencion_iva') ?? 0);
        $nroRetencion = $request->input('nro_retencion');
        $nroComprobantePago = $request->input('nro_comprobante_pago');
        $bancoDestino = $request->input('banco_destino', 'Banco Pichincha');

        // Procesar archivo de comprobante (PDF o imagen)
        $comprobantePath = null;
        if ($request->hasFile('comprobante_file')) {
            $request->validate([
                'comprobante_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp|max:10240'
            ]);
            $file = $request->file('comprobante_file');
            $fileName = 'b2b_comprobante_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('comprobantes_b2b', $fileName, 'public');
            $comprobantePath = 'storage/' . $path;
        }

        // Generar número de lote secuencial limpio (ej. LOTE-B2B-2026-000001)
        $anio = date('Y');
        $prefix = 'LOTE-B2B-' . $anio . '-';

        $ultimoLote = DB::table('recuento_b2b_lote')
            ->where('nro_lote', 'LIKE', $prefix . '%')
            ->orderByDesc('id')
            ->value('nro_lote');

        if ($ultimoLote) {
            $ultimoNum = (int) substr($ultimoLote, strlen($prefix));
            $secuencial = $ultimoNum + 1;
        } else {
            $totalExistentes = DB::table('recuento_b2b_lote')->count();
            $secuencial = $totalExistentes + 1;
        }

        $nroLote = $prefix . sprintf('%06d', $secuencial);

        $loteId = DB::table('recuento_b2b_lote')->insertGetId([
            'nro_lote' => $nroLote,
            'empresa_nombre' => $empresaNombre,
            'total_ordenes' => count($itemsRequest),
            'subtotal' => $subtotal,
            'monto_iva' => $montoIva,
            'total_con_iva' => $totalConIva,
            'monto_neto_banco' => $montoNetoBanco,
            'monto_retencion_renta' => $montoRetencionRenta,
            'monto_retencion_iva' => $montoRetencionIva,
            'nro_retencion' => $nroRetencion,
            'nro_comprobante_pago' => $nroComprobantePago,
            'banco_destino' => $bancoDestino,
            'comprobante_path' => $comprobantePath,
            'estado' => 'Cobrado',
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre_tecnico ?? $usuario->usuario ?? 'Usuario',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($itemsRequest as $it) {
            $tipoOrden = strtolower(trim((string) ($it['tipo_orden'] ?? 'empresa')));
            $ordId = (int) $it['id'];

            DB::table('recuento_b2b_item')->insert([
                'lote_id' => $loteId,
                'orden_id' => $ordId,
                'tipo_orden' => $tipoOrden,
                'nro_orden' => (string) $it['nro_orden'],
                'subtipo' => (string) ($it['subtipo'] ?? ''),
                'tecnico_nombre' => (string) ($it['tecnico'] ?? ''),
                'cantidad_tecnicos' => (int) ($it['tecnicos_count'] ?? 1),
                'horas_trabajadas' => (float) ($it['horas'] ?? 1.0),
                'tarifa_aplicada' => (float) ($it['tarifa'] ?? 0.0),
                'valor_fijo' => (float) ($it['valor_fijo'] ?? 0.0),
                'valor_mano_obra' => (float) ($it['valor_mano_obra'] ?? 0.0),
                'valor_repuestos' => (float) ($it['valor_repuestos'] ?? 0.0),
                'titulo_servicio' => (string) ($it['titulo_servicio'] ?? ''),
                'repuestos_detalle' => (string) ($it['repuestos_detalle'] ?? ''),
                'valor_total' => (float) ($it['valor_total'] ?? 0.0),
                'created_at' => now(),
            ]);

            if ($tipoOrden === 'personal') {
                Orden::where('id', $ordId)->update(['estado_facturacion' => 'Cobrado']);
            } else {
                OrdenEmpresa::where('id', $ordId)->update(['estado_facturacion' => 'Cobrado']);
            }
        }

        return response()->json([
            'ok' => true, 
            'mensaje' => 'Recuento B2B registrado exitosamente.',
            'lote_id' => $loteId
        ]);
    }

    /**
     * Permite a Contabilidad/Admin registrar o modificar la Mano de Obra y Título de Servicio
     * directamente desde el panel de Recuento B2B para órdenes finalizadas o cerradas.
     */
    public function actualizarManoObraOrden(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $request->validate([
            'orden_id' => 'required|integer',
            'tipo_orden' => 'required|string|in:personal,empresa',
            'valor_mano_obra' => 'required|numeric|min:0',
            'titulo_servicio' => 'nullable|string|max:255'
        ]);

        $ordenId = (int) $request->input('orden_id');
        $tipoOrden = strtolower(trim($request->input('tipo_orden')));
        $valorManoObra = round((float) $request->input('valor_mano_obra'), 2);
        $tituloServicio = trim((string) $request->input('titulo_servicio'));

        $totalRepuestosUsados = 0.0;
        $repuestosDetalleArr = [];

        if ($tipoOrden === 'personal') {
            $orden = Orden::with(['ordenRepuestos.repuesto'])->find($ordenId);
            if (!$orden) {
                return response()->json(['ok' => false, 'error' => 'Orden personal no encontrada.'], 404);
            }

            $orden->valor_mano_obra = $valorManoObra;
            if ($tituloServicio !== '') {
                $orden->titulo_servicio = $tituloServicio;
            }

            if ($orden->ordenRepuestos) {
                foreach ($orden->ordenRepuestos as $orp) {
                    $c = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                    $q = (int) ($orp->cantidad ?? 1);
                    $totalRepuestosUsados += ($c * $q);
                    $repuestosDetalleArr[] = "{$q}x " . ($orp->repuesto->nombre ?? 'Repuesto') . " ($" . number_format($c, 2) . ")";
                }
            }
            if ($totalRepuestosUsados <= 0 && (float)($orden->valor_repuestos ?? 0) > 0) {
                $totalRepuestosUsados = (float)$orden->valor_repuestos;
            }
            $orden->valor_repuestos = round($totalRepuestosUsados, 2);
            $orden->save();

            // Sincronizar con preciosorden si aplica
            if ($valorManoObra > 0) {
                \App\Models\Operations\PrecioOrden::updateOrCreate(
                    [
                        'orden_id' => $orden->id,
                        'servicio' => $tituloServicio ?: 'Mano de Obra Técnica'
                    ],
                    [
                        'precio' => $valorManoObra,
                        'tipo' => 'adicional'
                    ]
                );
            }
        } else {
            $orden = OrdenEmpresa::with(['empresa', 'ordenRepuestos.repuesto'])->find($ordenId);
            if (!$orden) {
                return response()->json(['ok' => false, 'error' => 'Orden de empresa no encontrada.'], 404);
            }

            $orden->valor_mano_obra = $valorManoObra;
            if ($tituloServicio !== '') {
                $orden->titulo_servicio = $tituloServicio;
            }

            if ($orden->ordenRepuestos) {
                foreach ($orden->ordenRepuestos as $orp) {
                    $c = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                    $q = (int) ($orp->cantidad ?? 1);
                    $totalRepuestosUsados += ($c * $q);
                    $repuestosDetalleArr[] = "{$q}x " . ($orp->repuesto->nombre ?? 'Repuesto') . " ($" . number_format($c, 2) . ")";
                }
            }
            if ($totalRepuestosUsados <= 0 && (float)($orden->valor_repuestos ?? 0) > 0) {
                $totalRepuestosUsados = (float)$orden->valor_repuestos;
            }
            $orden->valor_repuestos = round($totalRepuestosUsados, 2);
            $orden->save();
        }

        // Recalcular subtotal según tipo y subtipo
        $valorFijo = 14.25;
        $valorManoObraCobrado = round($valorManoObra * 0.50, 2);

        if ($tipoOrden === 'empresa') {
            $subt = mb_strtolower(trim((string)($orden->subtipo ?? '')));
            $valH = (float)($orden->valor_hora ?? 0.0);
            $hrs = (float)($orden->horas_trabajadas ?? 1.0);
            if ($hrs <= 0) $hrs = 1.0;
            $cTec = $orden->tecnicos ? $orden->tecnicos->count() : 1;
            if ($cTec <= 0) $cTec = 1;

            if ($subt === 'servicios' || ($valH > 0 && (float)($orden->horas_trabajadas ?? 0) > 0)) {
                if ($valH <= 0) $valH = 50.0;
                $valorFijo = round($cTec * $hrs * $valH, 2);
                $valorManoObraCobrado = $valorManoObra;
                $nuevoTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);
            } else {
                $nuevoTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);
            }
        } else {
            $nuevoTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);
        }

        return response()->json([
            'ok' => true,
            'mensaje' => 'Mano de obra actualizada correctamente.',
            'valor_mano_obra' => $valorManoObra,
            'valor_mano_obra_cobrado' => $valorManoObraCobrado,
            'valor_fijo' => $valorFijo,
            'valor_repuestos' => round($totalRepuestosUsados, 2),
            'repuestos_detalle' => implode(', ', $repuestosDetalleArr),
            'titulo_servicio' => $tituloServicio ?: ($orden->titulo_servicio ?? 'Servicio Técnico'),
            'nuevo_total' => $nuevoTotal
        ]);
    }

    public function exportarExcel(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $itemsJson = $request->input('ordenes_json');
        $items = json_decode($itemsJson ?: '[]', true);

        $ordenesProcesadas = collect();

        if (!empty($items) && is_array($items)) {
            foreach ($items as $it) {
                $tipo = strtolower(trim((string) ($it['tipo_orden'] ?? 'empresa')));
                $ordId = (int) $it['id'];

                if ($tipo === 'personal') {
                    $ord = Orden::with(['cliente', 'equipo', 'tecnico', 'sucursal', 'preciosOrden', 'ordenRepuestos.repuesto'])->find($ordId);
                    if ($ord) {
                        $empNovisolutions = Empresa::where('nombre', 'LIKE', '%NOVI%')->orWhere('nombre', 'LIKE', '%SOLUT%')->first();

                        $repuestosDetalleArr = [];
                        $totalRepuestosUsados = 0.0;
                        if ($ord->ordenRepuestos) {
                            foreach ($ord->ordenRepuestos as $orp) {
                                $repNombre = $orp->repuesto->nombre ?? 'Repuesto';
                                $repCant = (int) ($orp->cantidad ?? 1);
                                $repPrecio = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                                $repSubtotal = round($repCant * $repPrecio, 2);
                                $totalRepuestosUsados += $repSubtotal;
                                $repuestosDetalleArr[] = "{$repCant}x {$repNombre} ($" . number_format($repPrecio, 2) . ")";
                            }
                        }
                        if ($totalRepuestosUsados <= 0 && (float)($ord->valor_repuestos ?? 0) > 0) {
                            $totalRepuestosUsados = (float)$ord->valor_repuestos;
                        }

                        $valorManoObra = (float) ($ord->valor_mano_obra ?? 0.0);
                        $valorFijo = 14.25;
                        $valorManoObraCobrado = round($valorManoObra * 0.50, 2);
                        $valorTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);

                        $ord->empresa_nombre = $empNovisolutions->nombre ?? 'NOVISOLUTONS CIA. LTDA.';
                        $ord->tipo_orden_origen = 'personal';
                        $ord->subtipo_normalizado = 'Garantía';
                        $ord->cliente_nombre = trim(($ord->cliente->nombres ?? '') . ' ' . ($ord->cliente->apellidos ?? ''));
                        $ord->identificacion = $ord->cliente->identificacion ?? 'N/A';
                        $ord->cliente_telefono = $ord->cliente->numero_contacto ?? 'N/A';
                        $ord->cliente_correo = $ord->cliente->correo ?? 'N/A';
                        $ord->equipo_info = trim(($ord->equipo->tipo ?? '') . ' ' . ($ord->equipo->marca ?? '') . ' ' . ($ord->equipo->modelo ?? '')) . ' (S/N: ' . ($ord->equipo->serie ?? 'N/A') . ')';
                        $ord->tecnico_nombre = $ord->tecnico->nombre_tecnico ?? 'N/A';
                        $ord->sucursal_nombre = $ord->sucursal->ciudad ?? 'N/A';
                        $ord->tarifa_calculada = $valorFijo;
                        $ord->valor_fijo = $valorFijo;
                        $ord->valor_mano_obra_cobrado = $valorManoObraCobrado;
                        $ord->valor_repuestos = round($totalRepuestosUsados, 2);
                        $ord->repuestos_detalle_str = implode(', ', $repuestosDetalleArr);
                        $ord->valor_total_calculado = $valorTotal;
                        $ord->descripcion_servicio = $ord->titulo_servicio ?: ($ord->motivo_ingreso ?? 'Garantía Novicompu');
                        $ordenesProcesadas->push($ord);
                    }
                } else {
                    $ord = OrdenEmpresa::with(['empresa', 'equipo', 'tecnicos', 'ingresadoPor', 'sucursal', 'ordenRepuestos.repuesto'])->find($ordId);
                    if ($ord) {
                        $empNombre = strtoupper(trim($ord->empresa->nombre ?? ''));
                        $subtipoRaw = trim($ord->subtipo ?? 'Servicios');
                        $subtipoLower = mb_strtolower($subtipoRaw);
                        if ($subtipoLower === 'stock') $subtipoNorm = 'Stock';
                        elseif ($subtipoLower === 'autoconsumo') $subtipoNorm = 'Autoconsumo';
                        elseif (str_contains($subtipoLower, 'garant')) $subtipoNorm = 'Garantía';
                        else $subtipoNorm = 'Servicios';

                        $horas = (float) ($ord->horas_trabajadas ?? 1.0);
                        if ($horas <= 0) $horas = 1.0;
                        $cantidadTecnicos = $ord->tecnicos ? $ord->tecnicos->count() : 1;

                        $isNovisolutions = str_contains($empNombre, 'NOVI') || str_contains($empNombre, 'SOLUT') || (int)($ord->empresa_id ?? 0) === 1;

                        $repuestosDetalleArr = [];
                        $totalRepuestosUsados = 0.0;
                        if ($ord->ordenRepuestos) {
                            foreach ($ord->ordenRepuestos as $orp) {
                                $repNombre = $orp->repuesto->nombre ?? 'Repuesto';
                                $repCant = (int) ($orp->cantidad ?? 1);
                                $repPrecio = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                                $repSubtotal = round($repCant * $repPrecio, 2);
                                $totalRepuestosUsados += $repSubtotal;
                                $repuestosDetalleArr[] = "{$repCant}x {$repNombre} ($" . number_format($repPrecio, 2) . ")";
                            }
                        }
                        if ($totalRepuestosUsados <= 0 && (float)($ord->valor_repuestos ?? 0) > 0) {
                            $totalRepuestosUsados = (float)$ord->valor_repuestos;
                        }

                        $valorManoObra = (float) ($ord->valor_mano_obra ?? 0.0);

                        $valHora = (float) ($ord->valor_hora ?? 0.0);
                        $esServicio = ($subtipoNorm === 'Servicios') || ($valHora > 0 && (float)($ord->horas_trabajadas ?? 0) > 0);

                        if ($esServicio) {
                            if ($valHora <= 0) {
                                $valHora = (str_contains($empNombre, 'RB') || str_contains($empNombre, 'HEALTH')) ? 52.0 : 50.0;
                            }
                            $tarifa = $valHora;
                            $valorFijo = round($cantidadTecnicos * $horas * $valHora, 2);
                            $valorManoObraCobrado = $valorManoObra;
                            $valorTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);
                        } elseif ($isNovisolutions) {
                            $valorFijo = 14.25;
                            $valorManoObraCobrado = round($valorManoObra * 0.50, 2);
                            $valorTotal = round($valorFijo + $valorManoObraCobrado + $totalRepuestosUsados, 2);
                            $tarifa = $valorFijo;
                        } elseif (str_contains($empNombre, 'RB') || str_contains($empNombre, 'HEALTH')) {
                            $tarifa = 52.0;
                            $valorTotal = round(($horas * $tarifa) + $totalRepuestosUsados, 2);
                            $valorFijo = $valorTotal;
                            $valorManoObraCobrado = 0.0;
                        } else {
                            $tarifa = (float) ($ord->presupuesto ?? 50.0);
                            $valorTotal = round(($tarifa > 0 ? $tarifa : 50.0) + $totalRepuestosUsados, 2);
                            $valorFijo = $valorTotal;
                            $valorManoObraCobrado = 0.0;
                        }

                        $ord->empresa_nombre = $ord->empresa->nombre ?? 'N/A';
                        $ord->tipo_orden_origen = 'empresa';
                        $ord->subtipo_normalizado = $subtipoNorm;
                        $ord->cliente_nombre = $ord->empresa->nombre ?? 'N/A';
                        $ord->identificacion = $ord->empresa->ruc ?? 'N/A';
                        $ord->cliente_telefono = $ord->empresa->telefono ?? 'N/A';
                        $ord->cliente_correo = $ord->empresa->correo ?? 'N/A';
                        $ord->equipo_info = trim(($ord->equipo->tipo ?? '') . ' ' . ($ord->equipo->marca ?? '') . ' ' . ($ord->equipo->modelo ?? '')) . ' (S/N: ' . ($ord->equipo->serie ?? 'N/A') . ')';
                        $ord->tecnico_nombre = implode(', ', $ord->tecnicos->pluck('nombre_tecnico')->toArray());
                        $ord->sucursal_nombre = $ord->sucursal->ciudad ?? 'N/A';
                        $ord->tarifa_calculada = $tarifa;
                        $ord->valor_fijo = $valorFijo;
                        $ord->valor_mano_obra_cobrado = $valorManoObraCobrado;
                        $ord->valor_repuestos = round($totalRepuestosUsados, 2);
                        $ord->repuestos_detalle_str = implode(', ', $repuestosDetalleArr);
                        $ord->valor_total_calculado = round($valorTotal, 2);
                        $ord->descripcion_servicio = $ord->titulo_servicio ?: ($ord->descripcion ?? 'Servicio técnico B2B');
                        $ordenesProcesadas->push($ord);
                    }
                }
            }
        }

        $headers = [
            "Content-Type" => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Recuento_B2B_Cobros_" . date('Y-m-d_H-i') . ".xls",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $html = view('accounting.excel_recuento_b2b', [
            'ordenes' => $ordenesProcesadas,
            'usuario' => $usuario,
            'fechaExportacion' => now()->format('d/m/Y H:i')
        ])->render();

        return response($html, 200, $headers);
    }

    public function reciboCliente($id)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');

        $lote = DB::table('recuento_b2b_lote')->where('id', $id)->orWhere('nro_lote', $id)->first();
        if (!$lote) abort(404, 'Lote de Recuento B2B no encontrado.');

        $items = DB::table('recuento_b2b_item')->where('lote_id', $lote->id)->get();
        $empresaInfo = Empresa::where('nombre', 'LIKE', '%' . $lote->empresa_nombre . '%')->first();

        return view('accounting.recibo_b2b_cliente', [
            'lote' => $lote,
            'items' => $items,
            'empresaInfo' => $empresaInfo,
            'usuario' => $usuario
        ]);
    }

    public function reciboInterno($id)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');

        $lote = DB::table('recuento_b2b_lote')->where('id', $id)->orWhere('nro_lote', $id)->first();
        if (!$lote) abort(404, 'Lote de Recuento B2B no encontrado.');

        $items = DB::table('recuento_b2b_item')->where('lote_id', $lote->id)->get();

        return view('accounting.recibo_b2b_interno', [
            'lote' => $lote,
            'items' => $items,
            'usuario' => $usuario
        ]);
    }
}
