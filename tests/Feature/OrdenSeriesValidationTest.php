<?php

use App\Models\Directory\Sucursal;
use App\Models\Directory\Empresa;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Rol;
use App\Models\Identity\Usuario;
use App\Models\Inventory\Marca;
use App\Models\Inventory\TipoDispositivo;
use App\Models\Operations\OrdenEmpresa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearUsuarioParaOrden(int $rolId): Usuario
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

test('crear orden estandar de cliente requiere la serie del equipo', function () {
    $usuario = crearUsuarioParaOrden(2); // Tecnico
    
    $payload = [
        'cli_identificacion' => '1725324782',
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
        'ord_tecnico_id' => $usuario->id,
        'fecha_prometido' => Carbon::now('America/Guayaquil')->addDays(3)->format('Y-m-d'),
        // 'series' is missing
    ];

    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_crear' => ['ver' => true],
            ],
        ])
        ->post(route('ordenes.store'), $payload);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => false,
    ]);
    expect($response->json('error'))->toContain('series');
});

test('crear orden de empresa no requiere la serie del equipo y autogenera una serie valida', function () {
    $usuario = crearUsuarioParaOrden(2); // Tecnico

    $empresa = Empresa::create([
        'ruc' => '1792286746001',
        'nombre' => 'EMPRESA TEST S.A.',
        'direccion' => 'AV. GENERAL',
        'telefono' => '022222222',
        'correo' => 'empresa@test.com',
        'activo' => 1,
    ]);

    Marca::firstOrCreate(['nombre' => 'APPLE']);
    TipoDispositivo::firstOrCreate(['nombre' => 'LAPTOPS', 'codigo' => 'LAP']);

    $payload = [
        'motivo_ingreso' => 'Servicios a Empresas',
        'subtipo_empresa' => 'Autoconsumo',
        'empresa_id' => $empresa->id,
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_marca' => 'APPLE',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_falla' => 'Revision general',
        'emp_observacion' => 'Buen estado',
        'emp_fecha_prometido' => Carbon::now('America/Guayaquil')->addDays(3)->format('Y-m-d'),
        'ord_tecnico_id' => $usuario->id,
        // 'emp_series' is missing/null/empty
    ];

    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_crear' => ['ver' => true],
            ],
        ])
        ->post(route('ordenes.store'), $payload);

    $response->assertStatus(200);
    $response->assertJson(['ok' => true]);

    $orden = OrdenEmpresa::where('empresa_id', $empresa->id)->first();
    expect($orden)->not->toBeNull();
    expect($orden->equipo->serie)->toStartWith('SN-');
});

test('editar orden de empresa permite guardar sin series y mantiene o genera una serie valida', function () {
    $usuario = crearUsuarioParaOrden(1); // Admin (only admin can edit)

    $empresa = Empresa::create([
        'ruc' => '1792286746001',
        'nombre' => 'EMPRESA TEST S.A.',
        'direccion' => 'AV. GENERAL',
        'telefono' => '022222222',
        'correo' => 'empresa@test.com',
        'activo' => 1,
    ]);

    Marca::firstOrCreate(['nombre' => 'APPLE']);
    TipoDispositivo::firstOrCreate(['nombre' => 'LAPTOPS', 'codigo' => 'LAP']);

    // First create the order
    $payloadCreate = [
        'motivo_ingreso' => 'Servicios a Empresas',
        'subtipo_empresa' => 'Autoconsumo',
        'empresa_id' => $empresa->id,
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_marca' => 'APPLE',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_falla' => 'Revision general',
        'emp_observacion' => 'Buen estado',
        'emp_fecha_prometido' => Carbon::now('America/Guayaquil')->addDays(3)->format('Y-m-d'),
        'ord_tecnico_id' => $usuario->id,
    ];

    $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_crear' => ['ver' => true],
            ],
        ])
        ->post(route('ordenes.store'), $payloadCreate);

    $orden = OrdenEmpresa::where('empresa_id', $empresa->id)->first();
    expect($orden)->not->toBeNull();

    // Now edit the order without any series
    $payloadEdit = [
        'orden_id' => $orden->id,
        'equipo_id' => $orden->equipo_id,
        'estado' => 'En proceso',
        'descripcion' => 'Actualizado la falla de la orden',
        'eq_observacion' => 'Observacion editada',
        'fecha_prometido' => Carbon::now('America/Guayaquil')->addDays(5)->format('Y-m-d'),
        'tecnico_id' => $usuario->id,
        'eq_tipo' => 'LAPTOPS',
        'eq_marca' => 'APPLE',
        'eq_modelo' => 'MACBOOK PRO 16',
        // 'series' is missing/null/empty
    ];

    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'grupo_nombre' => 'admin master',
            'permisos' => [
                'ordenes_editar' => ['ver' => true, 'editar' => true],
            ],
        ])
        ->post(route('ordenes_empresa.update'), $payloadEdit);

    $response->assertStatus(200);
    $response->assertJson(['ok' => true]);

    $orden->refresh();
    expect($orden->estado)->toBe('En proceso');
    expect($orden->equipo->modelo)->toBe('MACBOOK PRO 16');
    expect($orden->equipo->serie)->toStartWith('SN-');
});
