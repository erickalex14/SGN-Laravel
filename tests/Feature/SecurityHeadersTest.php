<?php

use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Rol;
use App\Models\Identity\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function crearUsuarioCabeceras(array $atributos = []): Usuario
{
    $codigo = (string) random_int(1000, 9999);

    $rol = Rol::create([
        'rol' => 'sec_'.$codigo,
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
    $usuario->clave = '';
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

test('login publico expone headers de seguridad', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        ->assertHeader('Content-Security-Policy-Report-Only');
});

test('dashboard autenticado expone headers de seguridad', function () {
    $usuario = crearUsuarioCabeceras();

    $this->actingAs($usuario)
        ->withSession([
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico,
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'Grupo Prueba',
            'permisos' => [],
        ])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Content-Security-Policy-Report-Only');
});
