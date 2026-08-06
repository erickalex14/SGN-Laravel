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

        $empresaFiltro = $request->query('empresa', '');
        $sucursalFiltro = $request->query('sucursal_id', '');
        $buscarFiltro = trim((string) $request->query('buscar', ''));

        // Determinar sucursales permitidas según rol
        $sucursalesUserIds = session('sucursales_ids', []);
        if (empty($sucursalesUserIds) && !empty($usuario->sucursal_id)) {
            $sucursalesUserIds = [(int) $usuario->sucursal_id];
        }

        // --- 1. Obtener Órdenes de Empresa ---
        $query = OrdenEmpresa::with(['empresa', 'equipo', 'tecnicos', 'ingresadoPor', 'sucursal'])
            ->whereIn('estado', ['Finalizada', 'Entregada'])
            ->where(function($q) {
                $q->whereNull('estado_facturacion')
                  ->orWhere('estado_facturacion', 'Pendiente');
            });

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

            $tarifa = 0.0;
            $valorTotal = 0.0;

            if (str_contains($empresaNombre, 'RB') || str_contains($empresaNombre, 'HEALTH')) {
                $tarifa = 50.0;
                $valorTotal = $horas * $tarifa;
            } elseif (str_contains($empresaNombre, 'NOVI') || str_contains($empresaNombre, 'SOLUT')) {
                if ($subtipoNorm === 'Servicios') {
                    $tarifa = 25.0;
                    $valorTotal = $horas * $tarifa * $cantidadTecnicos;
                } elseif ($subtipoNorm === 'Garantía') {
                    $tarifa = (float) ($ord->valor_garantia ?? 19.32);
                    $valorTotal = $tarifa > 0 ? $tarifa : 19.32;
                } else {
                    $tarifa = (float) ($ord->presupuesto ?? $ord->total ?? 35.0);
                    $valorTotal = $tarifa > 0 ? $tarifa : 35.0;
                }
            } else {
                $tarifa = (float) ($ord->presupuesto ?? $ord->total ?? 50.0);
                $valorTotal = $tarifa > 0 ? $tarifa : 50.0;
            }

            $ord->tipo_orden_origen = 'empresa';
            $ord->subtipo_normalizado = $subtipoNorm;
            $ord->tarifa_calculada = $tarifa;
            $ord->horas_calculadas = $horas;
            $ord->tecnicos_count = $cantidadTecnicos;
            $ord->valor_total_calculado = round($valorTotal, 2);

            return $ord;
        });

        // --- 2. Obtener Órdenes de Garantía (Personales asignadas a NOVISOLUTONS CIA. LTDA.) ---
        $empNovisolutions = Empresa::where('nombre', 'LIKE', '%NOVI%')
            ->orWhere('nombre', 'LIKE', '%SOLUT%')
            ->first();

        $ordenesGarantiaProcesadas = collect();

        if ($empNovisolutions && ($empresaFiltro === '' || str_contains(strtoupper($empresaFiltro), 'NOVI') || str_contains(strtoupper($empresaFiltro), 'SOLUT'))) {
            $queryGarantia = Orden::with(['cliente', 'equipo', 'tecnico', 'sucursal', 'preciosOrden'])
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
                // Fórmulas oficiales del reporte: ($subtotalAdicionales + 28.00) * 1.15 * 0.60
                $subtotalAdicionales = $ord->preciosOrden ? (float) $ord->preciosOrden->sum('precio') : 0.00;
                $subtotalTotal = $subtotalAdicionales + 28.00;
                $valorCobroGarantia = round(($subtotalTotal * 1.15) * 0.60, 2); // Resulta $19.32 por defecto

                $ord->empresa = $empNovisolutions;
                $ord->tipo_orden_origen = 'personal';
                $ord->subtipo = 'Garantía';
                $ord->subtipo_normalizado = 'Garantía';
                $ord->horas_calculadas = 1.0;
                $ord->tecnicos_count = 1;
                $ord->tarifa_calculada = $valorCobroGarantia;
                $ord->valor_total_calculado = $valorCobroGarantia;
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
                    $ord = Orden::with(['cliente', 'equipo', 'tecnico', 'sucursal', 'preciosOrden'])->find($ordId);
                    if ($ord) {
                        $empNovisolutions = Empresa::where('nombre', 'LIKE', '%NOVI%')->orWhere('nombre', 'LIKE', '%SOLUT%')->first();
                        $subtotalAdicionales = $ord->preciosOrden ? (float) $ord->preciosOrden->sum('precio') : 0.00;
                        $subtotalTotal = $subtotalAdicionales + 28.00;
                        $valorGarantia = round(($subtotalTotal * 1.15) * 0.60, 2);

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
                        $ord->tarifa_calculada = $valorGarantia;
                        $ord->valor_total_calculado = $valorGarantia;
                        $ord->descripcion_servicio = $ord->motivo_ingreso ?? 'Garantía Novicompu';
                        $ordenesProcesadas->push($ord);
                    }
                } else {
                    $ord = OrdenEmpresa::with(['empresa', 'equipo', 'tecnicos', 'ingresadoPor', 'sucursal'])->find($ordId);
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

                        if (str_contains($empNombre, 'RB') || str_contains($empNombre, 'HEALTH')) {
                            $tarifa = 50.0; $valorTotal = $horas * $tarifa;
                        } elseif (str_contains($empNombre, 'NOVI') || str_contains($empNombre, 'SOLUT')) {
                            if ($subtipoNorm === 'Servicios') { $tarifa = 25.0; $valorTotal = $horas * $tarifa * $cantidadTecnicos; }
                            elseif ($subtipoNorm === 'Garantía') { $tarifa = 19.32; $valorTotal = 19.32; }
                            else { $tarifa = (float) ($ord->presupuesto ?? 35.0); $valorTotal = $tarifa > 0 ? $tarifa : 35.0; }
                        } else {
                            $tarifa = (float) ($ord->presupuesto ?? 50.0); $valorTotal = $tarifa > 0 ? $tarifa : 50.0;
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
                        $ord->valor_total_calculado = round($valorTotal, 2);
                        $ord->descripcion_servicio = $ord->descripcion ?? 'Servicio técnico B2B';
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
