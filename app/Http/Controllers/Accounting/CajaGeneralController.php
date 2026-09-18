<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Directory\Sucursal;
use App\Models\Operations\Orden;
use App\Services\Facturacion\AutomaticInvoiceService;
use App\Services\Facturacion\InvoicePayloadFactory;
use App\Services\Facturacion\OrderBillingCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class CajaGeneralController extends Controller
{
    public function __construct(
        private readonly OrderBillingCalculator $billing,
        private readonly InvoicePayloadFactory $invoicePayloads,
        private readonly AutomaticInvoiceService $automaticInvoices
    ) {}

    private function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function generateJwt($usuario)
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $exp = time() + (12 * 3600);
        $esSuperAdmin = (bool)($usuario->es_superadmin ?? ($usuario->rol_id === 3));

        $payload = json_encode([
            'id' => (string) $usuario->id,
            'nameid' => (string) $usuario->id,
            'unique_name' => (string) $usuario->usuario,
            'name' => (string) ($usuario->nombre_tecnico ?? $usuario->usuario),
            'sucursal_id' => (string) $usuario->sucursal_id,
            'rol_id' => (string) $usuario->rol_id,
            'es_superadmin' => $esSuperAdmin ? 'true' : 'false',
            'nbf' => time(),
            'exp' => $exp
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        $secret = env('CONTABILIDAD_JWT_SECRET', 'NovitecSecretSharedKeyContabilidadSgn2026$');
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function index()
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

        $tienePermisoCajaGeneral = !empty($p['caja_general']['ver']) || !empty($p['caja_general']['crear']) || !empty($p['caja_general']['editar']);

        if (!$esAdminMaster && !$tienePermisoCajaGeneral) {
            abort(403, 'Acceso denegado. No tienes permisos para acceder a Caja General.');
        }

        $sucursalId = (int) ($usuario->sucursal_id ?? session('sucursal_id', 1));
        $sucursal = Sucursal::find($sucursalId);
        $sucursalNombre = $sucursal ? $sucursal->ciudad : 'QUITO';

        $codigoSucursal = 'ACC30';
        if ($sucursal) {
            if (stripos($sucursal->ciudad, 'manta') !== false) {
                $codigoSucursal = 'ACC16';
            } elseif (stripos($sucursal->ciudad, 'guayaquil') !== false) {
                $codigoSucursal = 'ACC08';
            }
        }

        // Obtener todos los cobros de la sucursal para cliente externo (sin límite de 72 horas para que permanezcan en el historial)
        $cobrosPeriodo = DB::table('caja_general_cobros')
            ->where('sucursal_id', $sucursalId)
            ->orderByDesc('fecha_cobro')
            ->get();

        // Obtener historial de arqueos desde la base de datos local
        $arqueos = DB::table('caja_general_arqueo')
            ->where('sucursal_id', $sucursalId)
            ->orderByDesc('fecha')
            ->get();

        // Separar cobros en efectivo: Pendientes vs Arqueados/Depositados
        $cobrosEfectivoTodos = $cobrosPeriodo->where('destino_cuenta', 'Caja General');
        $cobrosBancos = $cobrosPeriodo->where('destino_cuenta', 'Bancos');

        $cobrosEfectivo = $cobrosEfectivoTodos->filter(function($c) {
            return empty($c->arqueo_id) || ($c->estado_arqueo ?? 'Pendiente') === 'Pendiente';
        })->values();

        $cobrosArqueados = $cobrosEfectivoTodos->filter(function($c) {
            return !empty($c->arqueo_id) || ($c->estado_arqueo ?? 'Pendiente') !== 'Pendiente';
        })->map(function($c) use ($arqueos, $codigoSucursal) {
            $arq = $arqueos->firstWhere('id', $c->arqueo_id);
            $c->nro_arqueo = $arq 
                ? (($arq->codigo_sucursal ?? $codigoSucursal) . '-ARQ-' . str_pad($arq->id, 6, '0', STR_PAD_LEFT))
                : ($c->arqueo_id ? ($codigoSucursal . '-ARQ-' . str_pad($c->arqueo_id, 6, '0', STR_PAD_LEFT)) : 'N/A');
            return $c;
        })->values();

        $totalEfectivoCalculado = (float) $cobrosEfectivo->sum(function($c) {
            if (isset($c->monto_neto_caja) && (float)$c->monto_neto_caja > 0) {
                return (float)$c->monto_neto_caja;
            }
            $cob = (float)($c->monto_cobrado ?? 0);
            $sob = (float)($c->sobrante ?? 0);
            $fal = (float)($c->faltante ?? 0);
            return $cob + $sob - $fal;
        });

        $totalBancosCalculado = (float) $cobrosBancos->sum('monto_cobrado');

        return view('accounting.caja_general', [
            'sucursalNombre' => $sucursalNombre,
            'codigoSucursal' => $codigoSucursal,
            'sucursalId' => $sucursalId,
            'totalEfectivoCalculado' => $totalEfectivoCalculado,
            'totalBancosCalculado' => $totalBancosCalculado,
            'cobrosEfectivo' => $cobrosEfectivo,
            'cobrosArqueados' => $cobrosArqueados,
            'cobrosBancos' => $cobrosBancos,
            'arqueos' => $arqueos,
        ]);
    }

    public function buscarOrden(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (empty($q) || strlen($q) < 1) {
            return response()->json(['ok' => true, 'ordenes' => []]);
        }

        $isNumeric = is_numeric($q);
        $padded6 = $isNumeric ? str_pad($q, 6, '0', STR_PAD_LEFT) : null;
        $padded5 = $isNumeric ? str_pad($q, 5, '0', STR_PAD_LEFT) : null;

        $ordenes = Orden::with(['cliente', 'equipo.series', 'informes'])
            ->where('motivo_ingreso', 'Servicio Cliente Externo')
            ->where(function($query) use ($q, $isNumeric, $padded6, $padded5) {
                $query->where('nro_orden', 'LIKE', "%{$q}%")
                      ->orWhereHas('cliente', function($cq) use ($q) {
                          $cq->where('nombres', 'LIKE', "%{$q}%")
                            ->orWhere('apellidos', 'LIKE', "%{$q}%")
                            ->orWhere('identificacion', 'LIKE', "%{$q}%");
                      });

                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                    if ($padded6) {
                        $query->orWhere('nro_orden', 'LIKE', "%{$padded6}%");
                    }
                    if ($padded5) {
                        $query->orWhere('nro_orden', 'LIKE', "%{$padded5}%");
                    }
                }
            })
            ->limit(15)
            ->get();

        $resultados = $ordenes->map(function($ord) {
            $clienteNombre = trim(($ord->cliente->nombres ?? '') . ' ' . ($ord->cliente->apellidos ?? ''));
            $equipoInfo = trim(($ord->equipo->tipo ?? 'Equipo') . ' ' . ($ord->equipo->marca ?? '') . ' ' . ($ord->equipo->modelo ?? '')) . ' (SN: ' . ($ord->equipo->serie ?? 'N/A') . ')';
            $cobrosPrevios = DB::table('caja_general_cobros')
                ->where('nro_orden', $ord->nro_orden)
                ->get();
            $countPrev = $cobrosPrevios->count();
            $totalPrev = (float) $cobrosPrevios->sum('monto_cobrado');

            return [
                'id' => $ord->id,
                'nro_orden' => $ord->nro_orden,
                'cliente' => !empty($clienteNombre) ? $clienteNombre : 'Cliente Generico',
                'equipo' => $equipoInfo,
                'motivo_ingreso' => $ord->motivo_ingreso,
                'estado' => $ord->estado_orden ?? 'Registrada',
                'total_sugerido' => 0,
                'tiene_cobros_previos' => ($countPrev > 0),
                'cobros_previos_count' => $countPrev,
                'total_cobrado_previo' => $totalPrev,
            ];
        });

        return response()->json(['ok' => true, 'ordenes' => $resultados]);
    }

    public function buscarProducto(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (empty($q) || strlen($q) < 1) {
            return response()->json(['ok' => true, 'productos' => []]);
        }

        $repuestos = DB::table('repuestos')
            ->where('codigo', 'LIKE', "%{$q}%")
            ->orWhere('nombre', 'LIKE', "%{$q}%")
            ->limit(10)
            ->get();

        $productosInv = DB::table('productosinventario')
            ->where('codigo', 'LIKE', "%{$q}%")
            ->orWhere('descripcion', 'LIKE', "%{$q}%")
            ->limit(10)
            ->get();

        $resultados = [];
        foreach ($repuestos as $r) {
            $resultados[] = [
                'id' => $r->id,
                'codigo' => $r->codigo,
                'nombre' => $r->nombre,
                'costo' => (float) $r->costo,
                'tipo' => 'Repuesto'
            ];
        }
        foreach ($productosInv as $pi) {
            $resultados[] = [
                'id' => $pi->id,
                'codigo' => $pi->codigo,
                'nombre' => $pi->descripcion,
                'costo' => 0.00,
                'tipo' => 'Producto'
            ];
        }

        return response()->json(['ok' => true, 'productos' => array_slice($resultados, 0, 15)]);
    }

    public function guardarCobro(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.']);
        }

        $request->validate([
            'monto_cobrado' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string',
        ]);

        $tipoCobro = $request->input('tipo_cobro', 'orden'); // 'orden' vs 'venta_directa'
        $ordenId = $request->input('orden_id') ? (int) $request->input('orden_id') : null;
        $codigoProducto = $request->input('codigo_producto') ? trim((string) $request->input('codigo_producto')) : null;
        $serieProducto = $request->input('serie_producto') ? trim((string) $request->input('serie_producto')) : null;

        if ($tipoCobro === 'venta_directa') {
            $nroOrden = $request->input('nro_orden') ? (string) $request->input('nro_orden') : ('VD-' . date('YmdHis'));
            $clienteNombre = (string) $request->input('cliente_nombre', 'Consumidor Final');
            $equipoInfo = (string) $request->input('equipo_info', '');
            if (empty($equipoInfo)) {
                $equipoInfo = trim(($codigoProducto ? "[{$codigoProducto}] " : "") . ($request->input('descripcion_producto') ?? 'Venta Directa Mostrador') . ($serieProducto ? " (SN: {$serieProducto})" : ""));
            }
        } else {
            $nroOrden = (string) $request->input('nro_orden', '');
            $clienteNombre = (string) $request->input('cliente_nombre', 'Cliente Externo');
            $equipoInfo = (string) $request->input('equipo_info', '');
        }

        $montoCobradoGeneral = (float) $request->input('monto_cobrado');
        $observacionesGeneral = $request->input('observaciones');
        $sucursalId = (int) ($usuario->sucursal_id ?? session('sucursal_id', 1));
        $now = now();

        $pagosInput = $request->input('pagos', []);

        $order = null;
        if ($tipoCobro === 'orden' && $ordenId) {
            $order = Orden::with(['cliente', 'equipo.series', 'informes'])->find($ordenId);
            if (!$order) {
                return response()->json(['ok' => false, 'error' => 'La orden seleccionada ya no existe.'], 422);
            }
            if (trim((string) $order->motivo_ingreso) !== 'Servicio Cliente Externo') {
                return response()->json(['ok' => false, 'error' => 'Caja General sólo permite cobrar órdenes B2C de Servicio Cliente Externo.'], 422);
            }
            $nroOrden = (string) $order->nro_orden;
            $clienteNombre = trim(($order->cliente->nombres ?? '') . ' ' . ($order->cliente->apellidos ?? ''));
            $equipoInfo = $this->billing->description($order);
            $sucursalId = (int) $order->sucursal_id;
        }

        if (empty($pagosInput) || !is_array($pagosInput)) {
            $metodo = (string) $request->input('metodo_pago', 'Efectivo');
            $montoRecibido = $request->input('monto_recibido') ? (float) $request->input('monto_recibido') : $montoCobradoGeneral;
            $vueltoDado = $request->input('vuelto_dado') ? (float) $request->input('vuelto_dado') : 0.00;
            if ($metodo === 'Efectivo' && $vueltoDado == 0 && $montoRecibido > $montoCobradoGeneral) {
                $vueltoDado = $montoRecibido - $montoCobradoGeneral;
            }
            $sobrante = max(0, (float) $request->input('sobrante', 0.00));
            $faltante = max(0, (float) $request->input('faltante', 0.00));

            $pagosInput = [
                [
                    'metodo_pago' => $metodo,
                    'monto_cobrado' => $montoCobradoGeneral,
                    'monto_recibido' => $montoRecibido,
                    'vuelto_dado' => $vueltoDado,
                    'sobrante' => $sobrante,
                    'faltante' => $faltante,
                    'observaciones' => $observacionesGeneral
                ]
            ];
        }

        $paymentTotal = round(collect($pagosInput)->sum(fn ($payment) => (float) ($payment['monto_cobrado'] ?? 0)), 2);
        if (abs($paymentTotal - $montoCobradoGeneral) > .01) {
            return response()->json(['ok' => false, 'error' => 'El desglose de pagos no coincide con el total a cobrar.'], 422);
        }

        try {
            DB::beginTransaction();
            $ids = [];
            $grupoCobroUuid = (string) Str::uuid();

            foreach ($pagosInput as $index => $p) {
                $metodo = (string) ($p['metodo_pago'] ?? 'Efectivo');
                $montoCobrado = (float) ($p['monto_cobrado'] ?? $montoCobradoGeneral);
                $montoRecibido = isset($p['monto_recibido']) ? (float)$p['monto_recibido'] : $montoCobrado;
                $vueltoDado = isset($p['vuelto_dado']) ? (float)$p['vuelto_dado'] : 0.00;
                $sobrante = max(0, (float)($p['sobrante'] ?? 0.00));
                $faltante = max(0, (float)($p['faltante'] ?? 0.00));
                $obsRow = !empty($p['observaciones']) ? $p['observaciones'] : $observacionesGeneral;

                $montoNetoCaja = ($montoRecibido - $vueltoDado) + $sobrante - $faltante;
                if ($montoNetoCaja <= 0 || stripos($metodo, 'Efectivo') === false) {
                    $montoNetoCaja = $montoCobrado + $sobrante - $faltante;
                }

                $destinoCuenta = (stripos($metodo, 'Efectivo') !== false) ? 'Caja General' : 'Bancos';

                // Procesar archivo de comprobante si se adjuntó para esta fila
                $comprobanteUrl = null;
                if ($request->hasFile("comprobante_file_{$index}")) {
                    $file = $request->file("comprobante_file_{$index}");
                    if ($file && $file->isValid()) {
                        $path = $file->store('comprobantes_cobros', 'public');
                        $comprobanteUrl = asset('storage/' . $path);
                    }
                }

                $cobroId = DB::table('caja_general_cobros')->insertGetId([
                    'grupo_cobro_uuid' => $grupoCobroUuid,
                    'orden_id' => $ordenId,
                    'nro_orden' => $nroOrden,
                    'tipo_cobro' => $tipoCobro,
                    'codigo_producto' => $codigoProducto,
                    'serie_producto' => $serieProducto,
                    'cliente_nombre' => $clienteNombre,
                    'equipo_info' => $equipoInfo,
                    'monto_cobrado' => $montoCobrado,
                    'monto_recibido' => $montoRecibido,
                    'vuelto_dado' => $vueltoDado,
                    'sobrante' => $sobrante,
                    'faltante' => $faltante,
                    'monto_neto_caja' => $montoNetoCaja,
                    'metodo_pago' => $metodo,
                    'destino_cuenta' => $destinoCuenta,
                    'sucursal_id' => $sucursalId,
                    'usuario_id' => $usuario->id,
                    'usuario_nombre' => $usuario->nombre_tecnico ?? $usuario->usuario ?? 'Usuario',
                    'estado_arqueo' => 'Pendiente',
                    'observaciones' => $obsRow,
                    'comprobante_url' => $comprobanteUrl,
                    'fecha_cobro' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $ids[] = $cobroId;
            }

            $invoiceLinkId = null;
            if (config('facturacion.enabled', false) && $order && !empty($ids)) {
                $payload = $this->invoicePayloads->fromCashCollection($ids[0], $usuario);
                $invoiceLinkId = $this->automaticInvoices->createIntent($ids[0], $payload, $usuario);
            }

            DB::commit();

            $invoice = ($invoiceLinkId && config('facturacion.enabled', false))
                ? $this->automaticInvoices->dispatch($invoiceLinkId)
                : null;

            $cant = count($ids);
            $tipoEtiqueta = $tipoCobro === 'venta_directa' ? 'venta directa' : "orden {$nroOrden}";
            $msg = $cant > 1 
                ? "Cobro mixto de {$tipoEtiqueta} registrado con éxito ({$cant} desgloses)."
                : "Cobro de {$tipoEtiqueta} registrado con éxito.";

            return response()->json([
                'ok' => true,
                'mensaje' => $msg,
                'cobro_ids' => $ids,
                'facturacion' => $invoice,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'error' => 'Error al registrar cobro: ' . $e->getMessage()]);
        }
    }

    public function guardarArqueo(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.']);
        }

        $request->validate([
            'sucursal_id' => 'required|integer',
            'codigo_sucursal' => 'required|string',
            'monto_fisico' => 'required|numeric',
            'observaciones' => 'nullable|string',
            'comprobante_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp|max:10240',
        ]);

        $sucursalId = (int) $request->input('sucursal_id');
        $codigoSucursal = $request->input('codigo_sucursal');
        $montoFisico = (float) $request->input('monto_fisico');
        $observaciones = $request->input('observaciones');
        $nroComprobanteDeposito = $request->input('nro_comprobante_deposito');
        $cobroIdsRaw = $request->input('cobro_ids');
        $cobroIds = is_string($cobroIdsRaw) ? json_decode($cobroIdsRaw, true) : ($cobroIdsRaw ?? []);

        $comprobanteUrl = null;
        if ($request->hasFile('comprobante_file')) {
            $path = $request->file('comprobante_file')->store('depositos_caja_general', 'public');
            $comprobanteUrl = asset('storage/' . $path);
        }

        // Determinar estado si adjunta depósito directamente
        $estado = (!empty($nroComprobanteDeposito) || !empty($comprobanteUrl)) ? 'Depositado' : 'Pendiente Deposito';

        // Calcular el monto sistema basado en los cobros seleccionados
        if (!empty($cobroIds) && is_array($cobroIds)) {
            $montoSistema = (float) DB::table('caja_general_cobros')
                ->whereIn('id', $cobroIds)
                ->sum('monto_neto_caja');
        } else {
            $montoSistema = (float) $request->input('monto_sistema', 0.00);
        }

        $diferencia = $montoFisico - $montoSistema;
        $tipoDiferencia = 'Cuadre Exacto';
        if ($diferencia < 0) {
            $tipoDiferencia = 'Faltante';
        } elseif ($diferencia > 0) {
            $tipoDiferencia = 'Sobrante';
        }

        // Guardar arqueo en DB
        $arqueoId = DB::table('caja_general_arqueo')->insertGetId([
            'sucursal_id' => $sucursalId,
            'codigo_sucursal' => $codigoSucursal,
            'fecha' => now(),
            'monto_sistema' => $montoSistema,
            'monto_fisico' => $montoFisico,
            'diferencia' => $diferencia,
            'tipo_diferencia' => $tipoDiferencia,
            'observaciones' => $observaciones,
            'nro_comprobante_deposito' => $nroComprobanteDeposito,
            'comprobante_deposito_url' => $comprobanteUrl,
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre_tecnico ?? $usuario->usuario ?? 'Usuario',
            'estado' => $estado,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $estadoCobro = ($estado === 'Depositado') ? 'Depositado' : 'Arqueado';

        // Vincular los cobros seleccionados al arqueo registrado
        if (!empty($cobroIds) && is_array($cobroIds)) {
            DB::table('caja_general_cobros')
                ->whereIn('id', $cobroIds)
                ->update([
                    'arqueo_id' => $arqueoId,
                    'estado_arqueo' => $estadoCobro,
                    'updated_at' => now(),
                ]);
        } else {
            $hace72Horas = now()->subHours(72);
            DB::table('caja_general_cobros')
                ->where('sucursal_id', $sucursalId)
                ->where('destino_cuenta', 'Caja General')
                ->where('fecha_cobro', '>=', $hace72Horas)
                ->whereNull('arqueo_id')
                ->update([
                    'arqueo_id' => $arqueoId,
                    'estado_arqueo' => $estadoCobro,
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'ok' => true,
            'mensaje' => ($estado === 'Depositado') ? 'Arqueo y comprobante de depósito registrados exitosamente.' : 'Arqueo diario guardado exitosamente.',
            'arqueo_id' => $arqueoId
        ]);
    }

    public function subirDeposito(Request $request)
    {
        $usuario = auth()->user();
        $request->validate([
            'arqueo_id' => 'required|integer',
            'nro_comprobante_deposito' => 'nullable|string',
            'comprobante_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp|max:10240',
        ]);

        $arqueoId = (int) $request->input('arqueo_id');
        $nroDep = $request->input('nro_comprobante_deposito');

        $updateData = [
            'estado' => 'Depositado',
            'updated_at' => now(),
        ];
        if ($nroDep) {
            $updateData['nro_comprobante_deposito'] = $nroDep;
        }

        if ($request->hasFile('comprobante_file')) {
            $path = $request->file('comprobante_file')->store('depositos_caja_general', 'public');
            $updateData['comprobante_deposito_url'] = asset('storage/' . $path);
        }

        DB::table('caja_general_arqueo')
            ->where('id', $arqueoId)
            ->update($updateData);

        DB::table('caja_general_cobros')
            ->where('arqueo_id', $arqueoId)
            ->update([
                'estado_arqueo' => 'Depositado',
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true, 'mensaje' => 'Comprobante de depósito actualizado correctamente.']);
    }

    public function imprimirArqueo($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $arqueo = DB::table('caja_general_arqueo')->where('id', $id)->first();
        abort_if(!$arqueo, 404, 'Registro de arqueo no encontrado.');

        $sucursal = Sucursal::find($arqueo->sucursal_id);
        $sucursalNombre = $sucursal ? $sucursal->ciudad : 'QUITO';

        $cobros = DB::table('caja_general_cobros')
            ->where('arqueo_id', $id)
            ->orderBy('fecha_cobro')
            ->get();

        return view('accounting.imprimir_arqueo', [
            'arqueo' => $arqueo,
            'cobros' => $cobros,
            'sucursalNombre' => $sucursalNombre,
            'codigoSucursal' => $arqueo->codigo_sucursal ?? 'UIO',
        ]);
    }

    public function imprimirRecibo(Request $request, $id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $cobro = DB::table('caja_general_cobros')->where('id', $id)->first();
        abort_if(!$cobro, 404, 'Cobro no encontrado.');

        // Obtener todos los cobros de la misma orden realizados en la misma ventana de tiempo (pago mixto)
        $cobrosGrupo = DB::table('caja_general_cobros')
            ->where('nro_orden', $cobro->nro_orden)
            ->whereBetween('fecha_cobro', [
                \Carbon\Carbon::parse($cobro->fecha_cobro)->subMinutes(5),
                \Carbon\Carbon::parse($cobro->fecha_cobro)->addMinutes(5)
            ])
            ->get();

        if ($cobrosGrupo->isEmpty()) {
            $cobrosGrupo = collect([$cobro]);
        }

        $sucursal = Sucursal::find($cobro->sucursal_id);
        $sucursalNombre = $sucursal ? $sucursal->ciudad : 'QUITO';

        $tipo = $request->query('tipo', 'cliente');
        $viewName = ($tipo === 'interno') ? 'accounting.imprimir_recibo' : 'accounting.imprimir_recibo_cliente';

        return view($viewName, [
            'cobro' => $cobro,
            'cobrosGrupo' => $cobrosGrupo,
            'sucursalNombre' => $sucursalNombre
        ]);
    }

    /**
     * Permite subir o actualizar el comprobante PDF/imagen de un cobro específico.
     */
    public function subirComprobanteCobro(Request $request, $id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
        }

        $cobro = DB::table('caja_general_cobros')->where('id', $id)->first();
        if (!$cobro) {
            return response()->json(['ok' => false, 'error' => 'Registro de cobro no encontrado.'], 404);
        }

        if ($request->hasFile('comprobante_file')) {
            $file = $request->file('comprobante_file');
            if ($file && $file->isValid()) {
                $path = $file->store('comprobantes_cobros', 'public');
                $comprobanteUrl = asset('storage/' . $path);

                DB::table('caja_general_cobros')
                    ->where('id', $id)
                    ->update([
                        'comprobante_url' => $comprobanteUrl,
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'ok' => true,
                    'mensaje' => 'Comprobante de depósito / transferencia adjuntado con éxito.',
                    'comprobante_url' => $comprobanteUrl
                ]);
            }
        }

        return response()->json(['ok' => false, 'error' => 'Debe adjuntar un archivo PDF o imagen válido.'], 422);
    }
}
