<?php

use App\Models\Identity\Usuario;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/_test-error-403', function () {
        abort(403);
    });

    Route::get('/_test-error-419', function () {
        abort(419);
    });

    Route::get('/_test-error-500', function () {
        abort(500);
    });
});

test('guest missing route renders sgn 404 page', function () {
    $response = $this->get('/ruta-que-no-existe');

    $response->assertStatus(404);
    $response->assertSee('Error 404');
    $response->assertSee('Recurso no encontrado');
});

test('authenticated forbidden route renders sgn 403 page', function () {
    $usuario = new Usuario;
    $usuario->id = 999;
    $usuario->usuario = 'tester';

    $response = $this->actingAs($usuario)->get('/_test-error-403');

    $response->assertStatus(403);
    $response->assertSee('Error 403');
    $response->assertSee('Acceso restringido');
    $response->assertSee('Ir al dashboard');
});

test('token mismatch renders sgn 419 page', function () {
    $response = $this->get('/_test-error-419');

    $response->assertStatus(419);
    $response->assertSee('Error 419');
    $response->assertSee('Sesion expirada');
});

test('internal server error renders sgn 500 page', function () {
    $response = $this->get('/_test-error-500');

    $response->assertStatus(500);
    $response->assertSee('Error 500');
    $response->assertSee('Fallo interno del sistema');
});

test('login maps legacy query errors to unified feedback cards', function () {
    $response = $this->get('/?error=throttle');

    $response->assertOk();
    $response->assertSee('Acceso pausado temporalmente');
    $response->assertSee('Detectamos demasiados intentos fallidos');
});

test('login validation errors use unified feedback card', function () {
    $response = $this->from('/')->post('/validar_login', [
        'usuario' => '',
        'clave' => '',
    ]);

    $response->assertRedirect('/');

    $followUp = $this->get('/');
    $followUp->assertOk();
    $followUp->assertSee('Datos incompletos');
    $followUp->assertSee('El campo usuario es obligatorio');
});
