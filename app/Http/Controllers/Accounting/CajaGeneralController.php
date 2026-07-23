<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Directory\Sucursal;
use App\Models\Operations\Orden;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

class CajaGeneralController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
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

        $hoy = date('Y-m-d');

        // Obtener cobros registrados manualmente hoy para cliente externo
        $cobrosHoy = DB::table('caja_general_cobros')
            ->where('sucursal_id', $sucursalId)
            ->whereDate('fecha_cobro', $hoy)
            ->orderByDesc('fecha_cobro')
            ->get();

        $cobrosEfectivo = $cobrosHoy->where('destino_cuenta', 'Caja General');
        $cobrosBancos = $cobrosHoy->where('destino_cuenta', 'Bancos');

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

        // Historial de arqueos desde microservicio C# o fallback local
        $arqueos = [];
        try {
            $apiUrl = config('services.contabilidad.url', 'http://localhost:8085') . '/api/CajaGeneral?sucursalId=' . $sucursalId;
            $token = session('jwt_token', '');
            $res = Http::withToken($token)->timeout(3)->get($apiUrl);
            if ($res->successful()) {
                $arqueos = $res->json()['data'] ?? [];
            }
        } catch (Exception $e) {
            $arqueos = DB::table('caja_general_arqueo')
                ->where('sucursal_id', $sucursalId)
                ->orderByDesc('fecha')
                ->get();
        }

        return view('accounting.caja_general', [
            'sucursalNombre' => $sucursalNombre,
            'codigoSucursal' => $codigoSucursal,
            'sucursalId' => $sucursalId,
            'totalEfectivoCalculado' => $totalEfectivoCalculado,
            'totalBancosCalculado' => $totalBancosCalculado,
            'cobrosEfectivo' => $cobrosEfectivo,
            'cobrosBancos' => $cobrosBancos,
            'arqueos' => $arqueos,
        ]);
    }

    public function buscarOrden(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (empty($q) || strlen($q) < 2) {
            return response()->json(['ok' => true, 'ordenes' => []]);
        }

        $ordenes = Orden::with(['cliente', 'equipo'])
            ->where(function($query) use ($q) {
                $query->where('nro_orden', 'LIKE', "%{$q}%")
                      ->orWhereHas('cliente', function($cq) use ($q) {
                          $cq->where('nombres', 'LIKE', "%{$q}%")
                            ->orWhere('apellidos', 'LIKE', "%{$q}%")
                            ->orWhere('identificacion', 'LIKE', "%{$q}%");
                      });
            })
            ->limit(10)
            ->get();

        $resultados = $ordenes->map(function($ord) {
            $clienteNombre = trim(($ord->cliente->nombres ?? '') . ' ' . ($ord->cliente->apellidos ?? ''));
            $equipoInfo = trim(($ord->equipo->tipo ?? 'Equipo') . ' ' . ($ord->equipo->marca ?? '') . ' ' . ($ord->equipo->modelo ?? '')) . ' (SN: ' . ($ord->equipo->serie ?? 'N/A') . ')';
            $montoSugerido = (float) ($ord->total ?? $ord->presupuesto ?? 0.00);

            return [
                'id' => $ord->id,
                'nro_orden' => $ord->nro_orden,
                'cliente' => !empty($clienteNombre) ? $clienteNombre : 'Cliente Generico',
                'equipo' => $equipoInfo,
                'estado' => $ord->estado_orden ?? 'Registrada',
                'total_sugerido' => $montoSugerido,
            ];
        });

        return response()->json(['ok' => true, 'ordenes' => $resultados]);
    }

    public function guardarCobro(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.']);
        }

        $request->validate([
            'nro_orden' => 'required|string',
            'monto_cobrado' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        $ordenId = $request->input('orden_id') ? (int) $request->input('orden_id') : null;
        $nroOrden = (string) $request->input('nro_orden');
        $clienteNombre = (string) $request->input('cliente_nombre', 'Cliente Externo');
        $equipoInfo = (string) $request->input('equipo_info', '');
        $montoCobrado = (float) $request->input('monto_cobrado');
        $metodoPago = (string) $request->input('metodo_pago');
        $observaciones = $request->input('observaciones');
        $sucursalId = (int) ($usuario->sucursal_id ?? session('sucursal_id', 1));

        $montoRecibido = $request->input('monto_recibido') ? (float) $request->input('monto_recibido') : $montoCobrado;
        $vueltoDado = $request->input('vuelto_dado') ? (float) $request->input('vuelto_dado') : 0.00;
        if ($metodoPago === 'Efectivo' && $vueltoDado == 0 && $montoRecibido > $montoCobrado) {
            $vueltoDado = $montoRecibido - $montoCobrado;
        }

        $sobrante = max(0, (float) $request->input('sobrante', 0.00));
        $faltante = max(0, (float) $request->input('faltante', 0.00));

        $montoNetoCaja = ($montoRecibido - $vueltoDado) + $sobrante - $faltante;
        if ($montoNetoCaja <= 0) {
            $montoNetoCaja = $montoCobrado + $sobrante - $faltante;
        }

        $destinoCuenta = ($metodoPago === 'Efectivo') ? 'Caja General' : 'Bancos';
        $now = now();

        try {
            $cobroId = DB::table('caja_general_cobros')->insertGetId([
                'orden_id' => $ordenId,
                'nro_orden' => $nroOrden,
                'cliente_nombre' => $clienteNombre,
                'equipo_info' => $equipoInfo,
                'monto_cobrado' => $montoCobrado,
                'monto_recibido' => $montoRecibido,
                'vuelto_dado' => $vueltoDado,
                'sobrante' => $sobrante,
                'faltante' => $faltante,
                'monto_neto_caja' => $montoNetoCaja,
                'metodo_pago' => $metodoPago,
                'destino_cuenta' => $destinoCuenta,
                'sucursal_id' => $sucursalId,
                'usuario_id' => $usuario->id,
                'usuario_nombre' => $usuario->nombre_tecnico ?? $usuario->usuario ?? 'Usuario',
                'observaciones' => $observaciones,
                'fecha_cobro' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            try {
                $apiUrl = config('services.contabilidad.url', 'http://localhost:8085') . '/api/CajaGeneral/cobro';
                $token = session('jwt_token', '');
                Http::withToken($token)->timeout(3)->post($apiUrl, [
                    'ordenId' => $ordenId,
                    'nroOrden' => $nroOrden,
                    'clienteNombre' => $clienteNombre,
                    'equipoInfo' => $equipoInfo,
                    'montoCobrado' => $montoCobrado,
                    'montoRecibido' => $montoRecibido,
                    'vueltoDado' => $vueltoDado,
                    'sobrante' => $sobrante,
                    'faltante' => $faltante,
                    'metodoPago' => $metodoPago,
                    'destinoCuenta' => $destinoCuenta,
                    'sucursalId' => $sucursalId,
                    'observaciones' => $observaciones,
                ]);
            } catch (Exception $exMs) {
                // Microservicio offline fallback silencioso
            }

            return response()->json([
                'ok' => true,
                'mensaje' => "Cobro de orden {$nroOrden} registrado con éxito en {$destinoCuenta}.",
                'cobro_id' => $cobroId
            ]);
        } catch (Exception $e) {
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
            'monto_sistema' => 'required|numeric',
            'monto_fisico' => 'required|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $sucursalId = (int) $request->input('sucursal_id');
        $codigoSucursal = $request->input('codigo_sucursal');
        $montoSistema = (float) $request->input('monto_sistema');
        $montoFisico = (float) $request->input('monto_fisico');
        $observaciones = $request->input('observaciones');

        try {
            $apiUrl = config('services.contabilidad.url', 'http://localhost:8085') . '/api/CajaGeneral/arqueo';
            $token = session('jwt_token', '');
            $res = Http::withToken($token)->timeout(3)->post($apiUrl, [
                'sucursalId' => $sucursalId,
                'codigoSucursal' => $codigoSucursal,
                'montoSistema' => $montoSistema,
                'montoFisico' => $montoFisico,
                'observaciones' => $observaciones,
            ]);

            if ($res->successful()) {
                return response()->json(['ok' => true, 'mensaje' => 'Arqueo registrado exitosamente en microservicio.']);
            }
        } catch (Exception $e) {
            // Fallback DB
        }

        $diferencia = $montoFisico - $montoSistema;
        $tipoDiferencia = 'Cuadre Exacto';
        if ($diferencia < 0) {
            $tipoDiferencia = 'Faltante';
        } elseif ($diferencia > 0) {
            $tipoDiferencia = 'Sobrante';
        }

        DB::table('caja_general_arqueo')->insert([
            'sucursal_id' => $sucursalId,
            'codigo_sucursal' => $codigoSucursal,
            'fecha' => now(),
            'monto_sistema' => $montoSistema,
            'monto_fisico' => $montoFisico,
            'diferencia' => $diferencia,
            'tipo_diferencia' => $tipoDiferencia,
            'observaciones' => $observaciones,
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre_tecnico ?? $usuario->usuario ?? 'Usuario',
            'estado' => 'Pendiente Deposito',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'mensaje' => 'Arqueo diario guardado exitosamente en base de datos.']);
    }

    public function subirDeposito(Request $request)
    {
        $request->validate([
            'arqueo_id' => 'required|integer',
            'nro_comprobante_deposito' => 'required|string',
        ]);

        $arqueoId = (int) $request->input('arqueo_id');
        $nroDep = $request->input('nro_comprobante_deposito');

        try {
            $apiUrl = config('services.contabilidad.url', 'http://localhost:8085') . '/api/CajaGeneral/deposito';
            $token = session('jwt_token', '');
            $res = Http::withToken($token)->timeout(3)->post($apiUrl, [
                'arqueoId' => $arqueoId,
                'nroComprobanteDeposito' => $nroDep,
            ]);

            if ($res->successful()) {
                return response()->json(['ok' => true, 'mensaje' => 'Comprobante de depósito bancario guardado.']);
            }
        } catch (Exception $e) {
            // Fallback DB
        }

        DB::table('caja_general_arqueo')
            ->where('id', $arqueoId)
            ->update([
                'nro_comprobante_deposito' => $nroDep,
                'estado' => 'Depositado',
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true, 'mensaje' => 'Comprobante de depósito actualizado correctamente en base de datos.']);
    }
}
