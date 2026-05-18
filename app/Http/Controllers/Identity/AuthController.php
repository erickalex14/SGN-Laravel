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
        $dto = new LoginDTO(
            $request->validated('usuario'),
            $request->validated('clave')
        );

        try {
            $this->authService->autenticar($dto);
            return redirect()->route('dashboard');
        } catch (Exception $e) {
            $errorParam = $e->getMessage() === 'usuario_inactivo' ? 'inactivo' : '1';

            // Mantenemos la redireccion con GET params como en el sistema original
            return redirect()->route('login', ['error' => $errorParam]);
        }
    }

    public function logout(): RedirectResponse
    {
        $this->authService->cerrarSesion();
        return redirect()->route('login');
    }
}
