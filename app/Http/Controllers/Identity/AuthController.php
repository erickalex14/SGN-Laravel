<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\LoginRequest;
use App\DTOs\Identity\LoginDTO;
use App\Services\Identity\AuthService;
use Illuminate\Http\RedirectResponse;
use Exception;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $dto = new LoginDTO(
                $request->input('usuario'),
                $request->input('clave')
            );

            $this->authService->validarAcceso($dto);

            // Redireccion a la ruta de dashboard de Laravel
            return redirect()->route('dashboard');

        } catch (Exception $e) {
            // Replicamos el codigo de error '?error=1' para compatibilidad con el frontend
            return redirect()->to('/login?error=1');
        }
    }
}
