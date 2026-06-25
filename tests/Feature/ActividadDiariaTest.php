<?php

use App\Models\Identity\Usuario;
use App\Models\Identity\ActividadDiaria;
use App\Models\Identity\Rol;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Services\Identity\ActividadDiariaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearUsuarioTecnicoPrueba(): Usuario
{
    $codigo = (string) random_int(1000, 9999);

    $rol = Rol::firstOrCreate(['rol' => 'tecnico'], ['id' => 2]);

    $sucursal = Sucursal::create([
        'nro_sucursal' => random_int(100, 999),
        'ciudad' => 'Quito',
        'secuencial' => 'UIO'.random_int(1, 9),
        'nro_base' => '02'.random_int(1000000, 9999999),
    ]);

    $grupo = GrupoAcceso::create([
        'nombre' => 'Tecnico '.$codigo,
        'descripcion' => 'Grupo Tecnico de prueba',
        'es_superadmin' => 0,
    ]);

    $usuario = new Usuario;
    $usuario->usuario = '09'.random_int(10000000, 99999999);
    $usuario->clave = '';
    $usuario->clave_hash = Hash::make('password123');
    $usuario->nombre_tecnico = 'Tecnico Test '.$codigo;
    $usuario->telefono = '0999999999';
    $usuario->acceso_nc = 0;
    $usuario->rol_id = 2; // Tecnico
    $usuario->grupo_id = $grupo->id;
    $usuario->sucursal_id = $sucursal->id;
    $usuario->activo = 1;
    $usuario->save();

    return $usuario->fresh();
}

function crearUsuarioAdminPrueba(): Usuario
{
    $codigo = (string) random_int(1000, 9999);

    $rol = Rol::firstOrCreate(['rol' => 'admin'], ['id' => 1]);

    $sucursal = Sucursal::create([
        'nro_sucursal' => random_int(100, 999),
        'ciudad' => 'Guayaquil',
        'secuencial' => 'GYE'.random_int(1, 9),
        'nro_base' => '04'.random_int(1000000, 9999999),
    ]);

    $grupo = GrupoAcceso::create([
        'nombre' => 'Admin '.$codigo,
        'descripcion' => 'Grupo Admin de prueba',
        'es_superadmin' => 0,
    ]);

    $usuario = new Usuario;
    $usuario->usuario = '09'.random_int(10000000, 99999999);
    $usuario->clave = '';
    $usuario->clave_hash = Hash::make('password123');
    $usuario->nombre_tecnico = 'Admin Test '.$codigo;
    $usuario->telefono = '0999999998';
    $usuario->acceso_nc = 0;
    $usuario->rol_id = 1; // Admin
    $usuario->grupo_id = $grupo->id;
    $usuario->sucursal_id = $sucursal->id;
    $usuario->activo = 1;
    $usuario->save();

    return $usuario->fresh();
}

test('mis-actividades requiere autenticación', function () {
    $this->get(route('actividades.index'))
        ->assertRedirect(route('login'));
});

test('mis-actividades carga para técnico autenticado', function () {
    $usuario = crearUsuarioTecnicoPrueba();

    $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'nombre' => $usuario->nombre_tecnico
        ])
        ->get(route('actividades.index'))
        ->assertStatus(200)
        ->assertViewIs('identity.actividades.index');
});

test('listar mis-actividades retorna JSON de actividades', function () {
    $usuario = crearUsuarioTecnicoPrueba();
    $fecha = Carbon::now('America/Guayaquil')->toDateString();

    // Crear una actividad
    ActividadDiaria::create([
        'usuario_id' => $usuario->id,
        'tipo_accion' => 'crear_orden',
        'descripcion' => 'Creó orden #12345',
        'modulo' => 'ordenes',
        'fecha_hora' => Carbon::now('America/Guayaquil')->toDateTimeString(),
        'fecha' => $fecha
    ]);

    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id
        ])
        ->get(route('actividades.listar', ['fecha' => $fecha]));

    $response->assertStatus(200)
        ->assertJson([
            'ok' => true,
            'fecha' => $fecha
        ]);

    expect($response->json('actividades'))->toHaveCount(1);
    expect($response->json('actividades.0.tipo_accion'))->toBe('crear_orden');
});

test('gestion actividades requiere permiso reportes.ver', function () {
    $usuario = crearUsuarioTecnicoPrueba();

    // Sin permisos en sesión debe dar 403
    $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'permisos' => []
        ])
        ->get(route('actividades.admin'))
        ->assertStatus(403);
});

test('gestion actividades carga con permiso reportes.ver', function () {
    $usuario = crearUsuarioAdminPrueba();

    $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'permisos' => [
                'reportes' => ['ver' => true]
            ]
        ])
        ->get(route('actividades.admin'))
        ->assertStatus(200)
        ->assertViewIs('identity.actividades.admin');
});

test('listarAdmin retorna actividades de un técnico', function () {
    $admin = crearUsuarioAdminPrueba();
    $tecnico = crearUsuarioTecnicoPrueba();
    $fecha = Carbon::now('America/Guayaquil')->toDateString();

    ActividadDiaria::create([
        'usuario_id' => $tecnico->id,
        'tipo_accion' => 'crear_informe',
        'descripcion' => 'Creó informe técnico para orden #12345',
        'modulo' => 'informes',
        'fecha_hora' => Carbon::now('America/Guayaquil')->toDateTimeString(),
        'fecha' => $fecha
    ]);

    $response = $this->actingAs($admin)
        ->withSession([
            'tecnico_id' => $admin->id,
            'permisos' => [
                'reportes' => ['ver' => true]
            ]
        ])
        ->get(route('actividades.admin.listar', [
            'tecnico_id' => $tecnico->id,
            'fecha' => $fecha
        ]));

    $response->assertStatus(200)
        ->assertJson([
            'ok' => true,
            'fecha' => $fecha
        ]);

    expect($response->json('actividades'))->toHaveCount(1);
    expect($response->json('actividades.0.tipo_accion'))->toBe('crear_informe');
});

test('servicio de actividad diaria no registra para usuarios excluidos pero si para el resto', function () {
    $excluido = crearUsuarioAdminPrueba(); // rol_id = 1
    $excluido->nombre_tecnico = 'Jahaira Cisneros';
    $excluido->save();

    $tecnico = crearUsuarioTecnicoPrueba(); // rol_id = 2

    $service = app(ActividadDiariaService::class);

    // Intentar registrar para excluido
    $service->registrar($excluido->id, 'crear_orden', 'Intento excluido', 'ordenes');
    
    // Intentar registrar para técnico (rol_id 2)
    $service->registrar($tecnico->id, 'crear_orden', 'Intento tecnico', 'ordenes');

    $fecha = Carbon::now('America/Guayaquil')->toDateString();
    
    // Verificar en BD
    $actividadesExcluido = ActividadDiaria::where('usuario_id', $excluido->id)->where('fecha', $fecha)->get();
    $actividadesTecnico = ActividadDiaria::where('usuario_id', $tecnico->id)->where('fecha', $fecha)->get();

    expect($actividadesExcluido)->toHaveCount(0);
    expect($actividadesTecnico)->toHaveCount(1);
    expect($actividadesTecnico->first()->descripcion)->toBe('Intento tecnico');
});

test('guardar actividades diarias permite registrar manual y recuperarlas', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 24, 15, 0, 0, 'UTC'));
    $usuario = crearUsuarioTecnicoPrueba();
    $fechaHoy = Carbon::now('America/Guayaquil')->toDateString();

    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id
        ])
        ->postJson(route('actividades.guardar'), [
            'fecha' => $fechaHoy,
            'actividades' => [
                '9' => [
                    'actividad' => 'Revisión y Diagnóstico',
                    'novedad' => 'Todo OK',
                    'estado' => 'Terminado',
                    'modalidad' => 'presencial',
                    'ot' => '12345',
                    'observacion' => 'Prueba manual 9AM'
                ],
                '14' => [
                    'actividad' => 'Almuerzo / Receso',
                    'novedad' => 'sn',
                    'estado' => 'sn',
                    'modalidad' => 'presencial',
                    'ot' => 'sn',
                    'observacion' => 'Hora de almuerzo'
                ]
            ]
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'ok' => true,
            'mensaje' => 'Actividades de hoy guardadas correctamente.'
        ]);

    // Verificar que se guardaron en base de datos
    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $usuario->id,
        'tipo_accion' => 'registro_manual',
        'fecha' => $fechaHoy,
        'descripcion' => 'Prueba manual 9AM'
    ]);

    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $usuario->id,
        'tipo_accion' => 'registro_manual',
        'fecha' => $fechaHoy,
        'descripcion' => 'Hora de almuerzo'
    ]);

    // Consultar el listado y verificar la fusión/override
    $listResponse = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id
        ])
        ->get(route('actividades.listar', ['fecha' => $fechaHoy]));

    $listResponse->assertStatus(200);
    $actividades = collect($listResponse->json('actividades'));

    $act9 = $actividades->first(function ($act) {
        return \Carbon\Carbon::parse($act['fecha_hora'])->hour === 9;
    });
    expect($act9)->not->toBeNull();
    expect($act9['metadata_json']['actividad'] ?? null)->toBe('Revisión y Diagnóstico');
    expect($act9['descripcion'])->toBe('Prueba manual 9AM');

    $act14 = $actividades->first(function ($act) {
        return \Carbon\Carbon::parse($act['fecha_hora'])->hour === 14;
    });
    expect($act14)->not->toBeNull();
    expect($act14['metadata_json']['actividad'] ?? null)->toBe('Almuerzo / Receso');
    expect($act14['descripcion'])->toBe('Hora de almuerzo');

    Carbon::setTestNow(); // reset time
});

test('guardar actividades diarias rechaza fechas distintas a hoy', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 24, 15, 0, 0, 'UTC'));
    $usuario = crearUsuarioTecnicoPrueba();
    $fechaAyer = Carbon::now('America/Guayaquil')->subDay()->toDateString();

    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id
        ])
        ->postJson(route('actividades.guardar'), [
            'fecha' => $fechaAyer,
            'actividades' => [
                '9' => [
                    'actividad' => 'Revisión y Diagnóstico',
                    'observacion' => 'Intento ayer'
                ]
            ]
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'ok' => false,
            'error' => 'Solo se permite editar las actividades del día de hoy.'
        ]);

    Carbon::setTestNow(); // reset time
});

test('guardar actividades diarias rechaza guardar despues de las 6:30 PM', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 24, 23, 31, 0, 'UTC'));
    $usuario = crearUsuarioTecnicoPrueba();
    $fechaHoy = Carbon::now('America/Guayaquil')->toDateString();

    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id
        ])
        ->postJson(route('actividades.guardar'), [
            'fecha' => $fechaHoy,
            'actividades' => [
                '9' => [
                    'actividad' => 'Revisión y Diagnóstico',
                    'observacion' => 'Intento tarde'
                ]
            ]
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'ok' => false,
            'error' => 'La edición de actividades está permitida solo hasta las 6:30 PM de hoy.'
        ]);

    Carbon::setTestNow(); // reset time
});

