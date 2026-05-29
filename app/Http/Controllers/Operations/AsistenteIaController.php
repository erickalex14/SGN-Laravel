<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Services\Operations\AsistenteIaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class AsistenteIaController extends Controller
{
    protected AsistenteIaService $aiService;

    public function __construct(AsistenteIaService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Endpoint asíncrono para responder preguntas en lenguaje natural sobre el estado operativo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function preguntar(Request $request): JsonResponse
    {
        $consulta = trim((string) $request->input('consulta'));

        if (empty($consulta)) {
            return response()->json([
                'ok' => false,
                'error' => 'Por favor, ingresa una pregunta o consulta válida.'
            ]);
        }

        try {
            // Resolver contexto básico de la sesión activa
            $contextoSesion = [
                'tecnico_id'   => (int) session('tecnico_id', 0),
                'sucursal_id'  => (int) session('sucursal_id', 0),
                'es_superadmin'=> (bool) session('es_superadmin', false),
                'nombre'       => (string) session('nombre', session('usuario', 'Usuario')),
                'es_admin'     => $this->resolverEsAdmin()
            ];

            $respuesta = $this->aiService->responderConsulta($consulta, $contextoSesion);

            return response()->json([
                'ok' => true,
                'respuesta' => $respuesta
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Resuelve si el rol actual tiene alcances administrativos amplios.
     */
    private function resolverEsAdmin(): bool
    {
        $esSuperadmin = (bool) session('es_superadmin', false);
        $grupoNombre  = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $rolNombre    = mb_strtolower(trim((string) (auth()->user()?->rol?->rol ?? '')));

        $rolesAdmin = ['admin', 'administrador', 'master', 'admin master', 'administrador master', 'tecnico master'];
        return $esSuperadmin
            || in_array($grupoNombre, $rolesAdmin, true)
            || in_array($rolNombre, $rolesAdmin, true);
    }
}
