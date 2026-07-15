<?php

use App\Models\Identity\Usuario;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Directory\Empresa;
use App\Models\Inventory\Marca;
use App\Models\Inventory\TipoDispositivo;
use App\Models\Inventory\ProductoInventarioFisicoSt;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Asegurar que no haya basura en la tabla
    DB::table('productos_inventario_fisico_st')->delete();

    // Crear sucursales de prueba
    $this->sucursalQuito = Sucursal::create([
        'nro_sucursal' => random_int(1000, 9999),
        'ciudad' => 'Quito',
        'secuencial' => 'UI' . random_int(1, 9),
        'nro_base' => '02' . random_int(1000000, 9999999),
    ]);

    $this->sucursalManta = Sucursal::create([
        'nro_sucursal' => random_int(1000, 9999),
        'ciudad' => 'Manta',
        'secuencial' => 'MN' . random_int(1, 9),
        'nro_base' => '05' . random_int(1000000, 9999999),
    ]);

    // Crear grupo de prueba
    $this->grupo = GrupoAcceso::create([
        'nombre' => 'G_' . random_int(10000, 99999),
        'descripcion' => 'Grupo de prueba',
        'es_superadmin' => 0,
    ]);

    // Crear un usuario técnico en Quito (max 10 chars for usuario)
    $this->tecnicoQuito = new Usuario();
    $this->tecnicoQuito->usuario = 'uio' . random_int(1000, 9999);
    $this->tecnicoQuito->clave = '';
    $this->tecnicoQuito->clave_hash = bcrypt('password123');
    $this->tecnicoQuito->nombre_tecnico = 'Tecnico Quito';
    $this->tecnicoQuito->telefono = '0999999991';
    $this->tecnicoQuito->rol_id = 2;
    $this->tecnicoQuito->grupo_id = $this->grupo->id;
    $this->tecnicoQuito->sucursal_id = $this->sucursalQuito->id;
    $this->tecnicoQuito->activo = 1;
    $this->tecnicoQuito->save();

    // Crear un usuario técnico en Manta
    $this->tecnicoManta = new Usuario();
    $this->tecnicoManta->usuario = 'mta' . random_int(1000, 9999);
    $this->tecnicoManta->clave = '';
    $this->tecnicoManta->clave_hash = bcrypt('password123');
    $this->tecnicoManta->nombre_tecnico = 'Tecnico Manta';
    $this->tecnicoManta->telefono = '0999999992';
    $this->tecnicoManta->rol_id = 2;
    $this->tecnicoManta->grupo_id = $this->grupo->id;
    $this->tecnicoManta->sucursal_id = $this->sucursalManta->id;
    $this->tecnicoManta->activo = 1;
    $this->tecnicoManta->save();

    // Crear un usuario superadmin
    $this->superadmin = new Usuario();
    $this->superadmin->usuario = 'sa' . random_int(1000, 9999);
    $this->superadmin->clave = '';
    $this->superadmin->clave_hash = bcrypt('password123');
    $this->superadmin->nombre_tecnico = 'Superadmin Auditoria';
    $this->superadmin->telefono = '0999999993';
    $this->superadmin->rol_id = 3;
    $this->superadmin->grupo_id = $this->grupo->id;
    $this->superadmin->sucursal_id = $this->sucursalQuito->id;
    $this->superadmin->activo = 1;
    $this->superadmin->save();

    // Asegurar Empresa Novisolutions con ID = 1
    $this->empresa = Empresa::firstOrCreate(
        ['id' => 1],
        [
            'nombre' => 'NOVISOLUTONS CIA. LTDA.',
            'ruc' => '1792291666001',
            'activo' => 1,
            'direccion' => 'Quito',
            'telefono' => '022999999',
            'correo' => 'novi@test.com'
        ]
    );

    // Asegurar Marcas y Tipos
    Marca::firstOrCreate(['nombre' => 'APPLE']);
    TipoDispositivo::firstOrCreate(['nombre' => 'LAPTOPS', 'codigo' => 'LAP']);
});

test('acceso a inventario fisico permitido para todos los usuarios autenticados', function () {
    // Tecnico de Quito accede a index sin problemas
    $response = $this->actingAs($this->tecnicoQuito)
        ->withSession([
            'usuario' => $this->tecnicoQuito->usuario,
            'tecnico_id' => $this->tecnicoQuito->id,
            'sucursal_id' => $this->sucursalQuito->id,
            'es_superadmin' => false,
            'grupo_nombre' => 'Tecnicos',
        ])
        ->get(route('inventario_fisico.index'));

    $response->assertStatus(200);
});

test('segregacion por sucursales en listado de inventario fisico', function () {
    $crearOrdenService = app(\App\Services\Operations\CrearOrdenService::class);

    // 1. Crear orden para Quito
    $crearOrdenService->crearOrdenEmpresa([
        'sucursal_id' => $this->sucursalQuito->id,
        'empresa_id' => 1,
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO UIO',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Revision',
        'emp_series' => ['SERIE_UIO_1'],
        'tecnico_encargado' => $this->tecnicoQuito->id,
        'tecnicos_asignados' => [$this->tecnicoQuito->id],
        'ord_tecnico_id' => $this->tecnicoQuito->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ]);

    // 2. Crear orden para Manta
    $crearOrdenService->crearOrdenEmpresa([
        'sucursal_id' => $this->sucursalManta->id,
        'empresa_id' => 1,
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO MEC',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Revision',
        'emp_series' => ['SERIE_MEC_1'],
        'tecnico_encargado' => $this->tecnicoManta->id,
        'tecnicos_asignados' => [$this->tecnicoManta->id],
        'ord_tecnico_id' => $this->tecnicoManta->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ]);

    // 3. Técnico de Manta solo debe ver la serie de Manta
    $responseManta = $this->actingAs($this->tecnicoManta)
        ->withSession([
            'usuario' => $this->tecnicoManta->usuario,
            'tecnico_id' => $this->tecnicoManta->id,
            'sucursal_id' => $this->sucursalManta->id,
            'es_superadmin' => false,
            'grupo_nombre' => 'Tecnicos',
        ])
        ->get(route('inventario_fisico.index'));

    $responseManta->assertStatus(200)
        ->assertSee('SERIE_MEC_1')
        ->assertDontSee('SERIE_UIO_1');

    // 4. Superadmin debe ver ambas series
    $responseSuper = $this->actingAs($this->superadmin)
        ->withSession([
            'usuario' => $this->superadmin->usuario,
            'tecnico_id' => $this->superadmin->id,
            'sucursal_id' => $this->sucursalQuito->id,
            'es_superadmin' => true,
            'grupo_nombre' => 'Superadministradores',
        ])
        ->get(route('inventario_fisico.index'));

    $responseSuper->assertStatus(200)
        ->assertSee('SERIE_MEC_1')
        ->assertSee('SERIE_UIO_1');
});

test('crear orden empresa novisolutions stock registra productos en st con sucursal', function () {
    $crearOrdenService = app(\App\Services\Operations\CrearOrdenService::class);

    $payload = [
        'sucursal_id' => $this->sucursalQuito->id,
        'empresa_id' => 1,
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Test fallas',
        'emp_series' => ['SERIENOVITEC1'],
        'tecnico_encargado' => $this->tecnicoQuito->id,
        'tecnicos_asignados' => [$this->tecnicoQuito->id],
        'ord_tecnico_id' => $this->tecnicoQuito->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ];

    $orden = $crearOrdenService->crearOrdenEmpresa($payload);

    $this->assertDatabaseHas('productos_inventario_fisico_st', [
        'orden_empresa_id' => $orden->id,
        'sucursal_id' => $this->sucursalQuito->id,
        'serie' => 'SERIENOVITEC1',
        'codigo' => 'MACBOOK PRO',
        'estado' => 'Tienda',
    ]);
});

test('editar orden empresa novisolutions stock actualiza sucursal de productos', function () {
    $crearOrdenService = app(\App\Services\Operations\CrearOrdenService::class);
    $actualizarOrdenService = app(\App\Services\Operations\ActualizarOrdenService::class);

    $payloadCreate = [
        'sucursal_id' => $this->sucursalQuito->id,
        'empresa_id' => 1,
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Test fallas',
        'emp_series' => ['SERIE_A'],
        'tecnico_encargado' => $this->tecnicoQuito->id,
        'tecnicos_asignados' => [$this->tecnicoQuito->id],
        'ord_tecnico_id' => $this->tecnicoQuito->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ];

    $orden = $crearOrdenService->crearOrdenEmpresa($payloadCreate);

    // Simular traslado de sucursal de la orden en DB
    $orden->sucursal_id = $this->sucursalManta->id;
    $orden->save();

    // Simular actualización del resto de los datos
    $payloadUpdate = [
        'orden_id' => $orden->id,
        'equipo_id' => $orden->equipo_id,
        'estado' => 'En proceso',
        'fecha_prometido' => now()->addDays(2)->format('Y-m-d'),
        'descripcion' => 'Traslado de sucursal',
        'eq_modelo' => 'MACBOOK PRO',
        'eq_marca' => 'APPLE',
        'eq_tipo' => 'LAPTOPS',
        'series' => ['SERIE_A'],
    ];

    $actualizarOrdenService->actualizarOrdenEmpresa($payloadUpdate, $this->superadmin->id);

    // Debe haberse actualizado la sucursal_id de los registros físicos
    $this->assertDatabaseHas('productos_inventario_fisico_st', [
        'orden_empresa_id' => $orden->id,
        'sucursal_id' => $this->sucursalManta->id,
        'serie' => 'SERIE_A',
    ]);
});

test('guardar y obtener estados de sucursal via api con restricciones', function () {
    $crearOrdenService = app(\App\Services\Operations\CrearOrdenService::class);

    $payloadCreate = [
        'sucursal_id' => $this->sucursalQuito->id,
        'empresa_id' => 1,
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Test fallas',
        'emp_series' => ['SERIE_X'],
        'tecnico_encargado' => $this->tecnicoQuito->id,
        'tecnicos_asignados' => [$this->tecnicoQuito->id],
        'ord_tecnico_id' => $this->tecnicoQuito->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ];

    $orden = $crearOrdenService->crearOrdenEmpresa($payloadCreate);
    $prod = ProductoInventarioFisicoSt::where('orden_empresa_id', $orden->id)->first();

    // Técnico de Manta intenta obtener productos de la orden de Quito -> Debe retornar vacío (sin registros visibles para su sucursal)
    $responseGetForbidden = $this->actingAs($this->tecnicoManta)
        ->withSession([
            'sucursal_id' => $this->sucursalManta->id,
        ])
        ->get("/operaciones/ordenes-empresa/inventario-fisico/{$orden->id}");

    $responseGetForbidden->assertStatus(200);
    $this->assertEmpty($responseGetForbidden->json('productos'));

    // Técnico de Quito intenta obtener productos de la orden de Quito -> Exito
    $responseGetOk = $this->actingAs($this->tecnicoQuito)
        ->withSession([
            'sucursal_id' => $this->sucursalQuito->id,
        ])
        ->get("/operaciones/ordenes-empresa/inventario-fisico/{$orden->id}");

    $responseGetOk->assertStatus(200)
        ->assertJsonFragment([
            'serie' => 'SERIE_X',
            'estado' => 'Tienda'
        ]);

    // Técnico de Manta intenta guardar estados para Quito -> Retorna 403
    $payloadSave = [
        'orden_empresa_id' => $orden->id,
        'productos' => [
            [
                'id' => $prod->id,
                'estado' => 'Outlet',
                'detalle_outlet' => 'Traslado rayado'
            ]
        ]
    ];

    $responsePostForbidden = $this->actingAs($this->tecnicoManta)
        ->withSession([
            'sucursal_id' => $this->sucursalManta->id,
        ])
        ->postJson(route('inventario_fisico.guardar'), $payloadSave);

    $responsePostForbidden->assertStatus(403);
});
