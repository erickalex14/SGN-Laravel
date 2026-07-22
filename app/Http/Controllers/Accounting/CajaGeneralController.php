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

        // Obtener órdenes cobradas hoy en recepción en efectivo
        $hoy = date('Y-m-d');
        $ordenesEfectivo = Orden::with(['cliente', 'equipo'])
            ->where('sucursal_id', $sucursalId)
            ->whereDate('fecha_de_ingreso', $hoy)
            ->where(function($q) {
                $q->where('estado_orden', 'Entregada')
                  ->orWhere('estado_orden', 'Finalizada')
                  ->orWhere('estado_orden', 'ENTREGADO')
                  ->orWhere('estado_orden', 'REPARADO');
            })
            ->get();

        $totalEfectivoCalculado = 0.0;
        foreach ($ordenesEfectivo as $ord) {
            $totalEfectivoCalculado += (float) ($ord->total ?? $ord->presupuesto ?? 0);
        }

        // Intentar obtener historial de arqueos desde el microservicio
        $arqueos = [];
        try {
            $apiUrl = config('services.contabilidad.url', 'http://localhost:8085') . '/api/CajaGeneral?sucursalId=' . $sucursalId;
            $token = session('jwt_token', '');
            $res = Http::withToken($token)->timeout(4)->get($apiUrl);
            if ($res->successful()) {
                $arqueos = $res->json()['data'] ?? [];
            }
        } catch (Exception $e) {
            // Fallback en caso de microservicio desconectado
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
            'ordenesEfectivo' => $ordenesEfectivo,
            'arqueos' => $arqueos,
        ]);
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
            $res = Http::withToken($token)->post($apiUrl, [
                'sucursalId' => $sucursalId,
                'codigoSucursal' => $codigoSucursal,
                'montoSistema' => $montoSistema,
                'montoFisico' => $montoFisico,
                'observaciones' => $observaciones,
            ]);

            if ($res->successful()) {
                return response()->json(['ok' => true, 'mensaje' => 'Arqueo diario guardado exitosamente.']);
            }
        } catch (Exception $e) {
            // Fallback DB directo
        }

        $diferencia = $montoFisico - $montoSistema;
        $tipoDiferencia = 'Cuadre Exacto';
        if ($diferencia < 0) $tipoDiferencia = 'Faltante';
        elseif ($diferencia > 0) $tipoDiferencia = 'Sobrante';

        DB::table('caja_general_arqueo')->insert([
            'sucursal_id' => $sucursalId,
            'codigo_sucursal' => $codigoSucursal,
            'fecha' => date('Y-m-d H:i:s'),
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

        return response()->json(['ok' => true, 'mensaje' => 'Arqueo registrado en base de datos.']);
    }

    public function subirDeposito(Request $request)
    {
        $request->validate([
            'arqueo_id' => 'required|integer',
            'nro_comprobante_deposito' => 'required|string',
            'comprobante_file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        $arqueoId = (int) $request->input('arqueo_id');
        $nroComprobante = $request->input('nro_comprobante_deposito');
        $url = '';

        if ($request->hasFile('comprobante_file')) {
            $path = $request->file('comprobante_file')->store('depositos_caja_general', 'public');
            $url = '/storage/' . $path;
        }

        DB::table('caja_general_arqueo')
            ->where('id', $arqueoId)
            ->update([
                'comprobante_deposito_url' => $url,
                'nro_comprobante_deposito' => $nroComprobante,
                'estado' => 'Depositado',
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true, 'mensaje' => 'Comprobante de depósito bancario adjuntado con éxito.']);
    }
}
