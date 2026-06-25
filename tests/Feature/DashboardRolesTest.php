<?php

use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Rol;
use App\Models\Identity\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function crearUsuarioParaDashboard(int $rolId, array $permisos = []): Usuario
{
    $codigo = (string) random_int(1000, 9999);

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
    $usuario->usuario = '09'.random_int(10000000, 99999999);
    $usuario->clave = '';
    $usuario->clave_hash = bcrypt('password123');
    $usuario->nombre_tecnico = 'Tecnico '.$codigo;
    $usuario->telefono = '0999999999';
    $usuario->rol_id = $rolId;
    $usuario->grupo_id = $grupo->id;
    $usuario->sucursal_id = $sucursal->id;
    $usuario->activo = 1;
    $usuario->save();

    return $usuario->fresh();
}

test('usuario admin ve dashboard de gestion si tiene permisos', function () {
    $usuario = crearUsuarioParaDashboard(1); // Admin

    $this->actingAs($usuario)
        ->withSession([
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico,
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'Admin Grupo',
            'permisos' => [
                'ordenes_asignadas' => ['ver' => true]
            ],
        ])
        ->get(route('dashboard.metricas'))
        ->assertOk()
        ->assertJsonPath('data.dashboard.modo', 'gestion');
});

test('tecnico de rol 2 ve dashboard tecnico incluso si tiene permiso de ver ordenes asignadas', function () {
    $usuario = crearUsuarioParaDashboard(2); // Tecnico

    $this->actingAs($usuario)
        ->withSession([
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico,
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'Técnico',
            'permisos' => [
                'ordenes_asignadas' => ['ver' => true]
            ],
        ])
        ->get(route('dashboard.metricas'))
        ->assertOk()
        ->assertJsonPath('data.dashboard.modo', 'tecnico');
});

test('tecnico de rol 4 ve dashboard tecnico incluso si tiene permiso de ver ordenes asignadas', function () {
    $usuario = crearUsuarioParaDashboard(4); // Tecnico Master

    $this->actingAs($usuario)
        ->withSession([
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico,
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'Técnico Master',
            'permisos' => [
                'ordenes_asignadas' => ['ver' => true]
            ],
        ])
        ->get(route('dashboard.metricas'))
        ->assertOk()
        ->assertJsonPath('data.dashboard.modo', 'tecnico');
});
