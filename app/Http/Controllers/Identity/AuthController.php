<?php

namespace App\Http\Controllers\Identity;

use App\DTOs\Identity\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\LoginRequest;
use App\Services\Identity\AuthService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = $this->throttleKey((string) $request->input('usuario', ''), (string) $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return redirect()->route('login', ['error' => 'throttle']);
        }

        $dto = new LoginDTO(
            $request->validated('usuario'),
            $request->validated('clave')
        );

        try {
            $this->authService->autenticar($dto);
            RateLimiter::clear($throttleKey);

            return redirect()->route('dashboard');
        } catch (QueryException $e) {
            Log::error('Fallo de conexion a BD durante login.', [
                'usuario' => $dto->usuario,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login', ['error' => 'db']);
        } catch (Exception $e) {
            RateLimiter::hit($throttleKey, 60);
            $errorParam = $e->getMessage() === 'usuario_inactivo' ? 'inactivo' : '1';

            // Mantenemos la redireccion con GET params como en el sistema original
            return redirect()->route('login', ['error' => $errorParam]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($request->isMethod('get')) {
            // ponytail: compat temporal, retirar GET /logout en el siguiente deploy si no hay uso legitimo.
            Log::warning('Uso legacy de GET /logout.', [
                'ip' => $request->ip(),
                'usuario_id' => auth()->id(),
                'usuario' => auth()->user()?->usuario,
            ]);
        }

        $this->authService->cerrarSesion();

        return redirect()->route('login');
    }

    private function throttleKey(string $usuario, string $ip): string
    {
        $normalizado = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $usuario);

        return mb_strtolower(trim((string) $normalizado)).'|'.$ip;
    }
}
