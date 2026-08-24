<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Models\Identity\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SuggestionController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        if (!session()->has('usuario')) {
            return response()->json(['ok' => false, 'error' => 'No autorizado']);
        }

        $asunto = trim((string) $request->input('asunto', ''));
        $detalle = trim((string) $request->input('detalle', ''));

        if ($asunto === '' || $detalle === '') {
            return response()->json(['ok' => false, 'error' => 'Faltan campos']);
        }

        $destinatario = (string) env('SGN_SUGERENCIAS_TO', 'josuer@novitec.com.ec');
        $correosMaster = collect(\App\Services\Operations\SgnMailService::obtenerCorreosNotificacionAdmins())
            ->reject(fn ($correo) => strcasecmp($correo, $destinatario) === 0)
            ->values()
            ->all();

        $nombreUsuario = (string) (session('nombre') ?? session('usuario') ?? 'Usuario desconocido');
        $rolUsuario = (string) (session('grupo_nombre') ?? '');
        $sucursalUsuario = (string) (session('sucursal_nombre') ?? '');
        if (!$sucursalUsuario && session('sucursal_id')) {
            $suc = \App\Models\Directory\Sucursal::find(session('sucursal_id'));
            if ($suc) {
                $sucursalUsuario = $suc->ciudad;
            }
        }

        $asuntoEmail = '[SGN Buzón] ' . $asunto;
        $cuerpo = view('emails.sugerencia', [
            'asunto' => $asunto,
            'detalle' => $detalle,
            'nombre_usuario' => $nombreUsuario,
            'rol_usuario' => $rolUsuario,
            'sucursal_usuario' => $sucursalUsuario,
            'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
        ])->render();

        try {
            Mail::html($cuerpo, function ($message) use ($destinatario, $correosMaster, $asuntoEmail) {
                $message->to($destinatario)->subject($asuntoEmail);
                if (!empty($correosMaster)) {
                    $message->cc($correosMaster);
                }
            });

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Error enviando sugerencia SGN', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => 'No se pudo enviar el mensaje.']);
        }
    }
}

