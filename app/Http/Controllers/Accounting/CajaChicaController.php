<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Directory\Sucursal;

class CajaChicaController extends Controller
{
    public function index()
    {
        return redirect()->route('cajachica.gestion');
    }

    public function adminIndex()
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

        $tienePermisoCajaChica = !empty($p['caja_chica']['ver']);

        if (!$esAdminMaster && !$tienePermisoCajaChica) {
            abort(403, 'Acceso denegado. Solo personal autorizado puede gestionar la administración de Caja Chica.');
        }

        // Obtener la sucursal del usuario y sucursales
        $sucursal = Sucursal::find($usuario->sucursal_id);
        $sucursalNombre = $sucursal ? $sucursal->ciudad : 'QUITO';
        
        $codigoSucursal = 'ACC30';
        if ($sucursal) {
            if (stripos($sucursal->ciudad, 'manta') !== false) {
                $codigoSucursal = 'ACC16';
            } elseif (stripos($sucursal->ciudad, 'guayaquil') !== false) {
                $codigoSucursal = 'ACC08';
            }
        }

        // Obtener usuarios activos para el desplegable de custodios
        $usuarios = \App\Models\Identity\Usuario::where('activo', 1)
            ->orderBy('nombre_tecnico')
            ->get(['id', 'nombre_tecnico', 'usuario']);

        // Obtener todas las sucursales
        $sucursales = Sucursal::all(['id', 'ciudad', 'secuencial']);

        $token = $this->generateJwt($usuario);
        $apiUrl = config('services.contabilidad.url', env('CONTABILIDAD_API_URL', 'https://novitec.com.ec/contabilidad-api'));

        return view('accounting.caja_chica_admin', [
            'token' => $token,
            'apiUrl' => $apiUrl,
            'usuario' => $usuario,
            'sucursalId' => $usuario->sucursal_id,
            'codigoSucursal' => $codigoSucursal,
            'sucursalNombre' => $sucursalNombre,
            'esSuperAdmin' => $esSuperAdmin,
            'usuarios' => $usuarios,
            'sucursales' => $sucursales
        ]);
    }

    public function gestionIndex()
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

        $tienePermisoCajaChica = !empty($p['caja_chica']['ver']) || !empty($p['caja_chica']['crear']) || !empty($p['caja_chica']['editar']);

        if (!$esAdminMaster && !$tienePermisoCajaChica) {
            abort(403, 'Acceso denegado. No tienes permisos para acceder a Caja Chica.');
        }

        $sucursal = Sucursal::find($usuario->sucursal_id);
        $sucursalNombre = $sucursal ? $sucursal->ciudad : 'QUITO';
        
        $codigoSucursal = 'ACC30';
        if ($sucursal) {
            if (stripos($sucursal->ciudad, 'manta') !== false) {
                $codigoSucursal = 'ACC16';
            } elseif (stripos($sucursal->ciudad, 'guayaquil') !== false) {
                $codigoSucursal = 'ACC08';
            }
        }

        // Obtener usuarios activos para el desplegable de beneficiarios
        $usuarios = \App\Models\Identity\Usuario::where('activo', 1)
            ->orderBy('nombre_tecnico')
            ->get(['id', 'nombre_tecnico', 'usuario']);

        $token = $this->generateJwt($usuario);
        $apiUrl = config('services.contabilidad.url', env('CONTABILIDAD_API_URL', 'https://novitec.com.ec/contabilidad-api'));

        return view('accounting.caja_chica_gestion', [
            'token' => $token,
            'apiUrl' => $apiUrl,
            'usuario' => $usuario,
            'sucursalId' => $usuario->sucursal_id,
            'codigoSucursal' => $codigoSucursal,
            'sucursalNombre' => $sucursalNombre,
            'esSuperAdmin' => (bool)($usuario->es_superadmin ?? ($usuario->rol_id === 3)),
            'usuarios' => $usuarios
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

        $secret = env('CONTABILIDAD_JWT_SECRET', 'NovitecSecretSharedKeyContabilidadSgn2026$');
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function subirComprobante(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('comprobantes_caja_chica', 'public');
            $url = asset('storage/' . $path);
            return response()->json(['ok' => true, 'url' => $url]);
        }

        return response()->json(['ok' => false, 'error' => 'No se pudo procesar el archivo.']);
    }
}
