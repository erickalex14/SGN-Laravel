<?php

use App\Models\Directory\Sucursal;
use App\Models\Directory\Empresa;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Usuario;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Operations\Equipo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearUsuarioHelperRb(int $rolId): Usuario
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
        'es_superadmin' => $rolId === 1 ? 1 : 0,
    ]);

    $usuario = new Usuario;
    $usuario->usuario = '09'.random_int(10000000, 99999999);
    $usuario->clave = '';
    $usuario->clave_hash = bcrypt('password123');
    $usuario->nombre_tecnico = 'Usuario '.$codigo;
    $usuario->telefono = '0999999999';
    $usuario->rol_id = $rolId;
    $usuario->grupo_id = $grupo->id;
    $usuario->sucursal_id = $sucursal->id;
    $usuario->activo = 1;
    $usuario->save();

    return $usuario->fresh();
}

test('crear orden empresa para RB-HEALTH ECUADOR CIA LTDA fuerza valor_hora a 52', function () {
    $usuario = crearUsuarioHelperRb(2);

    $empresa = Empresa::create([
        'nombre' => 'RB-HEALTH ECUADOR CIA LTDA',
        'ruc' => '1791234567001',
    ]);

    $equipo = Equipo::create([
        'tipo' => 'Servicio',
        'marca' => '',
        'modelo' => '',
        'serie' => '',
    ]);

    // Usar el servicio directamente para simular creación
    $crearService = app(\App\Services\Operations\CrearOrdenService::class);
    
    $data = [
        'empresa_id' => $empresa->id,
        'sucursal_id' => $usuario->sucursal_id,
        'ord_tecnico_id' => $usuario->id,
        'ingresado_por' => $usuario->id,
        'emp_fecha_prometido' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'fecha_ingreso' => Carbon::now()->format('Y-m-d H:i:s'),
        'emp_falla' => 'Revision',
        'subtipo_empresa' => 'Servicios',
        'emp_tipo_servicio' => 'Mantenimiento',
        'emp_descripcion' => 'Revision de equipos',
        'tecnico_encargado' => $usuario->id,
        'tecnicos_asignados' => [$usuario->id],
        'valor_hora' => 10.0, // el usuario manda 10
        'horas_trabajadas' => 5,
    ];

    $orden = $crearService->crearOrdenEmpresa($data, 'Servicios', $usuario->sucursal_id);

    expect((float)$orden->valor_hora)->toBe(52.0); // Debe forzar a 52.0
});

test('cerrar orden de RB-HEALTH ECUADOR CIA LTDA requiere horas_trabajadas', function () {
    $usuario = crearUsuarioHelperRb(2);

    $empresa = Empresa::create([
        'nombre' => 'RB-HEALTH ECUADOR CIA LTDA',
        'ruc' => '1791234567001',
    ]);

    $equipo = Equipo::create([
        'tipo' => 'Servicio',
        'marca' => '',
        'modelo' => '',
        'serie' => '',
    ]);

    $orden = OrdenEmpresa::create([
        'nro_orden' => 'UIO-123456',
        'empresa_id' => $empresa->id,
        'subtipo' => 'Servicios',
        'equipo_id' => $equipo->id,
        'tecnico_id' => $usuario->id,
        'sucursal_id' => $usuario->sucursal_id,
        'ingresado_por' => $usuario->id,
        'fecha_prometido' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'estado' => 'En proceso',
        'fecha_ingreso' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);

    // 1. Intento sin mandar horas_trabajadas
    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_mis' => ['ver' => true],
            ],
        ])
        ->postJson(route('mis_ordenes.estado'), [
            'id' => $orden->id,
            'tipo_orden' => 'empresa',
            'estado' => 'Finalizada',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => false,
        'error' => 'Debe ingresar el número de horas trabajadas para la empresa RB-HEALTH ECUADOR CIA LTDA.',
    ]);

    // 2. Intento mandando horas_trabajadas válidas
    $responseOk = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_mis' => ['ver' => true],
            ],
        ])
        ->postJson(route('mis_ordenes.estado'), [
            'id' => $orden->id,
            'tipo_orden' => 'empresa',
            'estado' => 'Finalizada',
            'horas_trabajadas' => 4.5,
        ]);

    $responseOk->assertStatus(200);
    $responseOk->assertJson([
        'ok' => true,
    ]);

    $orden->refresh();
    expect($orden->estado)->toBe('Finalizada');
    expect((float)$orden->horas_trabajadas)->toBe(4.5);
    expect((float)$orden->valor_hora)->toBe(52.0); // Se fuerza a 52.0
});
