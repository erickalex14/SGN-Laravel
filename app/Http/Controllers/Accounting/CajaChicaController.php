<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Directory\Sucursal;

class CajaChicaController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        // Obtener la sucursal del usuario
        $sucursal = Sucursal::find($usuario->sucursal_id);
        $sucursalNombre = $sucursal ? $sucursal->ciudad : 'QUITO';
        
        // Mapear código de centro de costos para Quito (ACC30)
        $codigoSucursal = 'ACC30'; // Por defecto Quito
        if ($sucursal) {
            if (stripos($sucursal->ciudad, 'manta') !== false) {
                $codigoSucursal = 'ACC16';
            } elseif (stripos($sucursal->ciudad, 'guayaquil') !== false) {
                $codigoSucursal = 'ACC08';
            }
        }

        $token = $this->generateJwt($usuario);
        $apiUrl = env('CONTABILIDAD_API_URL', 'http://YOUR_SERVER_IP:8085');

        return view('accounting.caja_chica', [
            'token' => $token,
            'apiUrl' => $apiUrl,
            'usuario' => $usuario,
            'sucursalId' => $usuario->sucursal_id,
            'codigoSucursal' => $codigoSucursal,
            'sucursalNombre' => $sucursalNombre,
            'esSuperAdmin' => (bool)($usuario->es_superadmin ?? ($usuario->rol_id === 3))
        ]);
    }

    private function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function generateJwt($usuario)
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        
        // JWT válido por 12 horas
        $exp = time() + (12 * 3600);

        $esSuperAdmin = (bool)($usuario->es_superadmin ?? ($usuario->rol_id === 3));

        $payload = json_encode([
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

        $secret = env('CONTABILIDAD_JWT_SECRET', 'YOUR_JWT_SECRET');
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
}
