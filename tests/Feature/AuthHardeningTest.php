<?php

use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Rol;
use App\Models\Identity\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

uses(DatabaseTransactions::class);

function crearUsuarioPrueba(array $atributos = []): Usuario
{
    $codigo = (string) random_int(1000, 9999);

    $rol = Rol::create([
        'rol' => 'tec_'.$codigo,
    ]);

    $sucursal = Sucursal::create([
        'nro_sucursal' => random_int(100, 999),
        'ciudad' => 'Quito',
        'secuencial' => 'UIO'.random_int(1, 9),
        'nro_base' => '02'.random_int(1000000, 9999999),
    ]);

    $grupo = GrupoAcceso::create([
        'nombre' => 'Grupo '.$codigo,
        'descripcion' => 'Prueba',
        'es_superadmin' => 0,
    ]);

    $usuario = new Usuario;
    $usuario->usuario = $atributos['usuario'] ?? ('09'.random_int(10000000, 99999999));
    $usuario->clave = $atributos['clave'] ?? '';
    $usuario->clave_hash = $atributos['clave_hash'] ?? null;
    $usuario->nombre_tecnico = $atributos['nombre_tecnico'] ?? 'Tecnico Prueba';
    $usuario->telefono = $atributos['telefono'] ?? '0999999999';
    $usuario->correo_tec = $atributos['correo_tec'] ?? null;
    $usuario->acceso_nc = 0;
    $usuario->rol_id = $rol->id;
    $usuario->grupo_id = $grupo->id;
    $usuario->sucursal_id = $sucursal->id;
    $usuario->activo = $atributos['activo'] ?? 1;
    $usuario->save();

    return $usuario->fresh();
}

test('login exitoso con clave hash', function () {
    $usuario = crearUsuarioPrueba([
        'usuario' => '0911111111',
        'clave' => '',
        'clave_hash' => Hash::make('Segura123'),
    ]);

    $this->post(route('auth.validar'), [
        'usuario' => '0911111111',
        'clave' => 'Segura123',
    ])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($usuario);
    $this->assertSame($usuario->id, session('tecnico_id'));
});

test('login legacy migra la clave al hash', function () {
    $usuario = crearUsuarioPrueba([
        'usuario' => '0922222222',
        'clave' => 'Legacy12',
        'clave_hash' => null,
    ]);

    $this->post(route('auth.validar'), [
        'usuario' => '0922222222',
        'clave' => 'Legacy12',
    ])
        ->assertRedirect(route('dashboard'));

    $usuario->refresh();

    expect($usuario->clave_hash)->not->toBeNull();
    expect(Hash::check('Legacy12', $usuario->clave_hash))->toBeTrue();
    expect($usuario->clave)->toBe('');
});

test('login aplica throttle tras multiples fallos', function () {
    crearUsuarioPrueba([
        'usuario' => '0933333333',
        'clave' => '',
        'clave_hash' => Hash::make('Correcta12'),
    ]);

    foreach (range(1, 5) as $intento) {
        $this->post(route('auth.validar'), [
            'usuario' => '0933333333',
            'clave' => 'Incorrecta12',
        ])->assertRedirect(route('login', ['error' => '1']));
    }

    $this->post(route('auth.validar'), [
        'usuario' => '0933333333',
        'clave' => 'Incorrecta12',
    ])->assertRedirect(route('login', ['error' => 'throttle']));
});

test('login con metacaracteres redirige siempre al login con error', function () {
    $this->post(route('auth.validar'), [
        'usuario' => "admin' OR '1'='1",
        'clave' => 'x',
    ])->assertRedirect(route('login', ['error' => '1']));
});

test('login con tautologia redirige siempre al login con error', function () {
    $this->post(route('auth.validar'), [
        'usuario' => "' OR 1=1 --",
        'clave' => 'x',
    ])->assertRedirect(route('login', ['error' => '1']));
});

test('mi cuenta cambia la clave usando hash', function () {
    $usuario = crearUsuarioPrueba([
        'usuario' => '0944444444',
        'clave' => '',
        'clave_hash' => Hash::make('Actual12'),
    ]);

    $this->actingAs($usuario)
        ->withSession([
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico,
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'Grupo Prueba',
            'permisos' => [
                'mi_cuenta' => [
                    'ver' => 1,
                ],
            ],
        ])
        ->post(route('mi_cuenta.guardar'), [
            'accion' => 'password',
            'actual' => 'Actual12',
            'nueva' => 'Nueva123',
        ])
        ->assertOk()
        ->assertJson([
            'ok' => true,
            'mensaje' => 'Contrasena cambiada correctamente.',
        ]);

    $usuario->refresh();

    expect(Hash::check('Nueva123', $usuario->clave_hash))->toBeTrue();
    expect($usuario->clave)->toBe('');
});

test('logout funciona por post y get por compatibilidad', function () {
    $usuario = crearUsuarioPrueba([
        'usuario' => '0955555555',
        'clave' => '',
        'clave_hash' => Hash::make('Salir123'),
    ]);

    $this->actingAs($usuario)
        ->withSession([
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico,
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'Grupo Prueba',
        ])
        ->post(route('auth.logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    Log::spy();

    $this->actingAs($usuario)
        ->withSession([
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico,
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'Grupo Prueba',
        ])
        ->get('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
    Log::shouldHaveReceived('warning')->once();
});
