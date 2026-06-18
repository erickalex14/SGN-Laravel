<?php

use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Rol;
use App\Models\Identity\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

function crearUsuarioCleanup(array $atributos = []): Usuario
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
    $usuario->nombre_tecnico = 'Tecnico Prueba';
    $usuario->telefono = '0999999999';
    $usuario->correo_tec = null;
    $usuario->acceso_nc = 0;
    $usuario->rol_id = $rol->id;
    $usuario->grupo_id = $grupo->id;
    $usuario->sucursal_id = $sucursal->id;
    $usuario->activo = 1;
    $usuario->save();

    return $usuario->fresh();
}

test('comando audita sin limpiar', function () {
    $limpiable = crearUsuarioCleanup([
        'usuario' => '0910000001',
        'clave' => 'Segura123',
        'clave_hash' => Hash::make('Segura123'),
    ]);

    $this->artisan('auth:cleanup-legacy-passwords --dry-run')
        ->expectsOutputToContain('Modo auditoria')
        ->expectsOutputToContain('Usuarios limpiables')
        ->assertSuccessful();

    expect($limpiable->fresh()->clave)->toBe('Segura123');
});

test('comando limpia solo usuarios seguros', function () {
    $limpiable = crearUsuarioCleanup([
        'usuario' => '0910000002',
        'clave' => 'Segura123',
        'clave_hash' => Hash::make('Segura123'),
    ]);

    $sinHash = crearUsuarioCleanup([
        'usuario' => '0910000003',
        'clave' => 'Legacy123',
        'clave_hash' => null,
    ]);

    $inconsistente = crearUsuarioCleanup([
        'usuario' => '0910000004',
        'clave' => 'Texto123',
        'clave_hash' => Hash::make('Otra123'),
    ]);

    $this->artisan('auth:cleanup-legacy-passwords --execute')
        ->expectsOutputToContain('Modo ejecucion')
        ->expectsOutputToContain('Usuarios limpiados')
        ->assertSuccessful();

    expect($limpiable->fresh()->clave)->toBe('');
    expect($sinHash->fresh()->clave)->toBe('Legacy123');
    expect($inconsistente->fresh()->clave)->toBe('Texto123');
});
