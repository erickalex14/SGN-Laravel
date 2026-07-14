<?php

use App\Models\Identity\Usuario;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Operations\Orden;
use App\Models\Identity\Bitacora;
use App\Models\Inventory\Marca;
use App\Models\Inventory\TipoDispositivo;
use App\Services\Operations\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Limpiar tabla de bitácoras antes de cada test
    DB::table('bitacoras')->delete();

    // Crear sucursal de prueba
    $this->sucursal = Sucursal::create([
        'nro_sucursal' => random_int(1000, 9999),
        'ciudad' => 'Quito',
        'secuencial' => 'UI' . random_int(1, 9),
        'nro_base' => '02' . random_int(1000000, 9999999),
    ]);

    // Crear grupo de prueba con nombre único
    $this->grupo = GrupoAcceso::create([
        'nombre' => 'G_' . random_int(10000, 99999),
        'descripcion' => 'Grupo de prueba',
        'es_superadmin' => 0,
    ]);

    // Crear un usuario técnico común (rol_id = 2, usuario de 8 caracteres)
    $this->tecnico = new Usuario();
    $this->tecnico->usuario = 't_' . random_int(100000, 999999);
    $this->tecnico->clave = '';
    $this->tecnico->clave_hash = bcrypt('password123');
    $this->tecnico->nombre_tecnico = 'Tecnico Auditoria';
    $this->tecnico->telefono = '0999999991';
    $this->tecnico->rol_id = 2;
    $this->tecnico->grupo_id = $this->grupo->id;
    $this->tecnico->sucursal_id = $this->sucursal->id;
    $this->tecnico->activo = 1;
    $this->tecnico->save();

    // Crear un usuario superadmin
    $this->superadmin = new Usuario();
    $this->superadmin->usuario = 's_' . random_int(100000, 999999);
    $this->superadmin->clave = '';
    $this->superadmin->clave_hash = bcrypt('password123');
    $this->superadmin->nombre_tecnico = 'Superadmin Auditoria';
    $this->superadmin->telefono = '0999999992';
    $this->superadmin->rol_id = 3;
    $this->superadmin->grupo_id = $this->grupo->id;
    $this->superadmin->sucursal_id = $this->sucursal->id;
    $this->superadmin->activo = 1;
    $this->superadmin->save();

    // Asegurar marcas y tipos para creación de órdenes
    Marca::firstOrCreate(['nombre' => 'APPLE']);
    TipoDispositivo::firstOrCreate(['nombre' => 'LAPTOPS', 'codigo' => 'LAP']);
});

test('acceso a bitacora restringido para tecnicos comunes', function () {
    $response = $this->actingAs($this->tecnico)
        ->withSession([
            'usuario' => $this->tecnico->usuario,
            'tecnico_id' => $this->tecnico->id,
            'es_superadmin' => false,
            'grupo_nombre' => 'Tecnicos',
        ])
        ->get(route('bitacora.index'));

    $response->assertStatus(403);
});

test('acceso a bitacora permitido para superadmin', function () {
    $response = $this->actingAs($this->superadmin)
        ->withSession([
            'usuario' => $this->superadmin->usuario,
            'tecnico_id' => $this->superadmin->id,
            'es_superadmin' => true,
            'grupo_nombre' => 'Superadministradores',
        ])
        ->get(route('bitacora.index'));

    $response->assertStatus(200);
});

test('inicio de sesion exitoso registra bitacora', function () {
    $clavePlana = 'Segura123#';
    $username = 'u_' . random_int(100000, 999999);
    
    $usuario = new Usuario();
    $usuario->usuario = $username;
    $usuario->clave = '';
    $usuario->establecerClaveSegura($clavePlana);
    $usuario->nombre_tecnico = 'Login Test';
    $usuario->telefono = '0999999993';
    $usuario->rol_id = 3;
    $usuario->grupo_id = $this->grupo->id;
    $usuario->sucursal_id = $this->sucursal->id;
    $usuario->activo = 1;
    $usuario->save();

    // Ejecutar login
    $response = $this->post(route('auth.validar'), [
        'usuario' => $username,
        'clave' => $clavePlana,
    ]);

    $response->assertRedirect(route('dashboard'));

    // Verificar que se haya creado el log de login
    $this->assertDatabaseHas('bitacoras', [
        'usuario_id' => $usuario->id,
        'accion' => 'LOGIN',
        'modulo' => 'auth',
    ]);
});

test('crear orden personal registra bitacora', function () {
    $payload = [
        'cli_identificacion' => '17' . random_int(10000000, 99999999),
        'cli_nombres' => 'JUAN',
        'cli_apellidos' => 'PEREZ',
        'cli_telefono' => '0999999999',
        'eq_tipo' => 'LAPTOPS',
        'eq_marca' => 'APPLE',
        'eq_modelo' => 'MACBOOK PRO',
        'eq_falla' => 'No enciende',
        'motivo_ingreso' => 'Servicio Cliente Externo',
        'tipo_servicio_texto' => 'REPARACION',
        'producto_inventario_codigo' => 'GENERICO',
        'ord_tecnico_id' => $this->tecnico->id,
        'fecha_prometido' => now('America/Guayaquil')->addDays(3)->format('Y-m-d'),
        'series' => ['APPLE123456'],
    ];

    $response = $this->actingAs($this->superadmin)
        ->withSession([
            'tecnico_id' => $this->superadmin->id,
            'sucursal_id' => $this->sucursal->id,
            'permisos' => [
                'ordenes_crear' => ['ver' => true],
            ],
        ])
        ->post(route('ordenes.store'), $payload);

    $response->assertStatus(200);

    // Verificar bitácora
    $this->assertDatabaseHas('bitacoras', [
        'accion' => 'CREAR_ORDEN',
        'modulo' => 'ordenes',
    ]);
});
