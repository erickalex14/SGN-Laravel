<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operations\OrdenEmpresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

class RecuentoB2BController extends Controller
{
    public function index(Request $request)
    {
        $empresaFiltro = $request->query('empresa', '');

        // Obtener órdenes de empresa terminadas / listas para cobro
        $query = OrdenEmpresa::with(['empresa', 'equipo', 'tecnicos', 'ingresadoPor'])
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

        $ordenes = $query->orderByDesc('id')->get();

        // Calcular el valor a cobrar por cada orden según las reglas del negocio
        $ordenesProcesadas = $ordenes->map(function ($ord) {
            $empresaNombre = strtoupper(trim($ord->empresa->nombre ?? ''));
            $subtipo = trim($ord->subtipo ?? 'Servicios');
            $horas = (float) ($ord->horas_trabajadas ?? 1.0);
            if ($horas <= 0) $horas = 1.0;

            $cantidadTecnicos = $ord->tecnicos ? $ord->tecnicos->count() : 1;
            if ($cantidadTecnicos <= 0) $cantidadTecnicos = 1;

            $tarifa = 0.0;
            $valorTotal = 0.0;

            if (str_contains($empresaNombre, 'RB') || str_contains($empresaNombre, 'HEALTH')) {
                // RB Health Servicios: $50.00 / hora
                $tarifa = 50.0;
                $valorTotal = $horas * $tarifa;
            } elseif (str_contains($empresaNombre, 'NOVICOMPU')) {
                if ($subtipo === 'Servicios') {
                    // Novicompu Servicios: $25.00 / hora por cada técnico
                    $tarifa = 25.0;
                    $valorTotal = $horas * $tarifa * $cantidadTecnicos;
                } elseif ($subtipo === 'Garantia' || $subtipo === 'Garantía') {
                    // Novicompu Garantía: Valor predeterminado asignado
                    $tarifa = (float) ($ord->valor_garantia ?? 35.0);
                    $valorTotal = $tarifa;
                } else {
                    // Novicompu Stock: Valor manual asignado
                    $tarifa = (float) ($ord->presupuesto ?? $ord->total ?? 0.0);
                    $valorTotal = $tarifa;
                }
            } else {
                // Otras empresas
                $tarifa = (float) ($ord->presupuesto ?? $ord->total ?? 50.0);
                $valorTotal = $tarifa;
            }

            $ord->tarifa_calculada = $tarifa;
            $ord->horas_calculadas = $horas;
            $ord->tecnicos_count = $cantidadTecnicos;
            $ord->valor_total_calculado = round($valorTotal, 2);

            return $ord;
        });

        // Intentar obtener historial de lotes procesados desde el microservicio
        $lotesProcesados = [];
        try {
            $apiUrl = config('services.contabilidad.url', 'http://localhost:8085') . '/api/RecuentoB2B';
            $token = session('jwt_token', '');
            $res = Http::withToken($token)->timeout(4)->get($apiUrl);
            if ($res->successful()) {
                $lotesProcesados = $res->json()['data'] ?? [];
            }
        } catch (Exception $e) {
            $lotesProcesados = DB::table('recuento_b2b_lote')->orderByDesc('created_at')->get();
        }

        return view('accounting.recuento_b2b', [
            'empresaFiltro' => $empresaFiltro,
            'ordenes' => $ordenesProcesadas,
            'lotesProcesados' => $lotesProcesados,
        ]);
    }

    public function procesarCobro(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.']);
        }

        $request->validate([
            'empresa_nombre' => 'required|string',
            'monto_neto_banco' => 'required|numeric',
            'monto_retencion_renta' => 'nullable|numeric',
            'monto_retencion_iva' => 'nullable|numeric',
            'nro_retencion' => 'nullable|string',
            'nro_comprobante_pago' => 'nullable|string',
            'banco_destino' => 'nullable|string',
            'ordenes' => 'required|array|min:1',
        ]);

        $empresaNombre = $request->input('empresa_nombre');
        $montoNetoBanco = (float) $request->input('monto_neto_banco');
        $montoRetencionRenta = (float) ($request->input('monto_retencion_renta') ?? 0);
        $montoRetencionIva = (float) ($request->input('monto_retencion_iva') ?? 0);
        $nroRetencion = $request->input('nro_retencion');
        $nroComprobantePago = $request->input('nro_comprobante_pago');
        $bancoDestino = $request->input('banco_destino', 'Banco Pichincha');
        $itemsRequest = $request->input('ordenes');

        try {
            $apiUrl = config('services.contabilidad.url', 'http://localhost:8085') . '/api/RecuentoB2B/procesar';
            $token = session('jwt_token', '');
            $res = Http::withToken($token)->post($apiUrl, [
                'empresaNombre' => $empresaNombre,
                'montoNetoBanco' => $montoNetoBanco,
                'montoRetencionRenta' => $montoRetencionRenta,
                'montoRetencionIva' => $montoRetencionIva,
                'nroRetencion' => $nroRetencion,
                'nroComprobantePago' => $nroComprobantePago,
                'bancoDestino' => $bancoDestino,
                'items' => array_map(function($it) {
                    return [
                        'ordenId' => (int) $it['id'],
                        'tipoOrden' => 'empresa',
                        'nroOrden' => (string) $it['nro_orden'],
                        'subtipo' => (string) ($it['subtipo'] ?? ''),
                        'tecnicoNombre' => (string) ($it['tecnico'] ?? ''),
                        'cantidadTecnicos' => (int) ($it['tecnicos_count'] ?? 1),
                        'horasTrabajadas' => (float) ($it['horas'] ?? 1.0),
                        'tarifaAplicada' => (float) ($it['tarifa'] ?? 0.0),
                        'valorTotal' => (float) ($it['valor_total'] ?? 0.0),
                    ];
                }, $itemsRequest)
            ]);

            if ($res->successful()) {
                // Actualizar estado de las órdenes en la DB local
                $ordenesIds = array_column($itemsRequest, 'id');
                OrdenEmpresa::whereIn('id', $ordenesIds)->update([
                    'estado_facturacion' => 'Cobrado',
                ]);

                return response()->json(['ok' => true, 'mensaje' => 'Recuento B2B procesado y cobrado exitosamente.']);
            }
        } catch (Exception $e) {
            // Fallback DB
        }

        // Fallback local DB
        $nroLote = 'LOTE-B2B-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $subtotal = array_sum(array_column($itemsRequest, 'valor_total'));

        $loteId = DB::table('recuento_b2b_lote')->insertGetId([
            'nro_lote' => $nroLote,
            'empresa_nombre' => $empresaNombre,
            'total_ordenes' => count($itemsRequest),
            'subtotal' => $subtotal,
            'monto_neto_banco' => $montoNetoBanco,
            'monto_retencion_renta' => $montoRetencionRenta,
            'monto_retencion_iva' => $montoRetencionIva,
            'nro_retencion' => $nroRetencion,
            'nro_comprobante_pago' => $nroComprobantePago,
            'banco_destino' => $bancoDestino,
            'estado' => 'Cobrado',
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre_tecnico ?? $usuario->usuario ?? 'Usuario',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($itemsRequest as $it) {
            DB::table('recuento_b2b_item')->insert([
                'lote_id' => $loteId,
                'orden_id' => (int) $it['id'],
                'tipo_orden' => 'empresa',
                'nro_orden' => (string) $it['nro_orden'],
                'subtipo' => (string) ($it['subtipo'] ?? ''),
                'tecnico_nombre' => (string) ($it['tecnico'] ?? ''),
                'cantidad_tecnicos' => (int) ($it['tecnicos_count'] ?? 1),
                'horas_trabajadas' => (float) ($it['horas'] ?? 1.0),
                'tarifa_aplicada' => (float) ($it['tarifa'] ?? 0.0),
                'valor_total' => (float) ($it['valor_total'] ?? 0.0),
                'created_at' => now(),
            ]);

            OrdenEmpresa::where('id', (int) $it['id'])->update([
                'estado_facturacion' => 'Cobrado',
            ]);
        }

        return response()->json(['ok' => true, 'mensaje' => 'Recuento B2B registrado en base de datos local.']);
    }
}
