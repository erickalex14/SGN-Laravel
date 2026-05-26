<?php

namespace App\Http\Controllers\Identity;

use App\DTOs\Identity\MiCuentaPasswordDTO;
use App\DTOs\Identity\MiCuentaPerfilDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\GuardarMiCuentaRequest;
use App\Services\Identity\MiCuentaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MiCuentaController extends Controller
{
    protected MiCuentaService $service;

    public function __construct(MiCuentaService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $usuarioId = (int) session('tecnico_id', 0);
        $contexto = $this->service->obtenerContextoUsuario($usuarioId);

        return view('identity.mi_cuenta.index', [
            'usuario_actual' => (string) session('usuario', ''),
            'nombre_actual' => (string) session('nombre', ''),
            'grupo_nombre' => (string) session('grupo_nombre', ''),
            'telefono_actual' => $contexto['telefono_actual'],
            'correo_actual' => $contexto['correo_actual'],
        ]);
    }

    public function guardar(GuardarMiCuentaRequest $request): JsonResponse
    {
        if (!session()->has('usuario')) {
            return response()->json(['ok' => false, 'error' => 'No autorizado']);
        }

        try {
            $accion = trim((string) $request->input('accion', ''));
            $usuarioId = (int) session('tecnico_id', 0);

            if ($accion === 'nombre' || $accion === 'perfil') {
                $dto = new MiCuentaPerfilDTO(
                    $usuarioId,
                    trim((string) $request->input('nombre', ''))
                );

                $this->service->actualizarPerfil(
                    $dto,
                    (string) $request->input('telefono', ''),
                    (string) $request->input('correo', '')
                );

                session(['nombre' => $dto->nombre]);
                return response()->json(['ok' => true, 'mensaje' => 'Nombre actualizado correctamente.']);
            }

            if ($accion === 'password') {
                $dto = new MiCuentaPasswordDTO(
                    $usuarioId,
                    trim((string) $request->input('actual', '')),
                    trim((string) $request->input('nueva', ''))
                );

                $this->service->actualizarPassword($dto);
                return response()->json(['ok' => true, 'mensaje' => 'Contrasena cambiada correctamente.']);
            }

            return response()->json(['ok' => false, 'error' => 'Accion no valida.']);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}

