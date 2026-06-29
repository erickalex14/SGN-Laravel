<?php

use App\Models\Identity\Usuario;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('un administrador puede activar o desactivar un usuario', function () {
    $sucursal = Sucursal::create([
        'nro_sucursal' => random_int(100, 999),
        'ciudad' => 'Quito',
        'secuencial' => 'UIO'.random_int(1, 9),
        'nro_base' => '02'.random_int(1000000, 9999999),
    ]);

    $grupo = GrupoAcceso::create([
        'nombre' => 'Admin Grupo',
        'descripcion' => 'Grupo Admin de prueba',
        'es_superadmin' => 1,
    ]);

    $admin = new Usuario;
    $admin->usuario = '09'.random_int(10000000, 99999999);
    $admin->clave = '';
    $admin->clave_hash = bcrypt('password123');
    $admin->nombre_tecnico = 'Admin Test';
    $admin->telefono = '0999999998';
    $admin->rol_id = 1; // Admin
    $admin->grupo_id = $grupo->id;
    $admin->sucursal_id = $sucursal->id;
    $admin->activo = 1;
    $admin->save();

    $tecnico = new Usuario;
    $tecnico->usuario = '09'.random_int(10000000, 99999999);
    $tecnico->clave = '';
    $tecnico->clave_hash = bcrypt('password123');
    $tecnico->nombre_tecnico = 'Tecnico Test';
    $tecnico->telefono = '0999999999';
    $tecnico->rol_id = 2; // Tecnico
    $tecnico->grupo_id = $grupo->id;
    $tecnico->sucursal_id = $sucursal->id;
    $tecnico->activo = 1;
    $tecnico->save();

    expect($tecnico->activo)->toBeTrue();

    // Desactivar
    $response = $this->actingAs($admin)
        ->withSession([
            'tecnico_id' => $admin->id,
            'sucursal_id' => $admin->sucursal_id,
            'grupo_nombre' => 'admin master',
            'permisos' => [
                'usuarios' => ['editar' => true],
            ],
        ])
        ->post(route('usuarios.toggle'), [
            'id' => $tecnico->id,
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
        'activo' => false,
    ]);

    $tecnico->refresh();
    expect($tecnico->activo)->toBeFalse();

    // Activar
    $response2 = $this->actingAs($admin)
        ->withSession([
            'tecnico_id' => $admin->id,
            'sucursal_id' => $admin->sucursal_id,
            'grupo_nombre' => 'admin master',
            'permisos' => [
                'usuarios' => ['editar' => true],
            ],
        ])
        ->post(route('usuarios.toggle'), [
            'id' => $tecnico->id,
        ]);

    $response2->assertStatus(200);
    $response2->assertJson([
        'ok' => true,
        'activo' => true,
    ]);

    $tecnico->refresh();
    expect($tecnico->activo)->toBeTrue();
});
