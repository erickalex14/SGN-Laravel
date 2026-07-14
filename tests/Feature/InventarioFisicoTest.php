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

    // Crear sucursal de prueba
    $this->sucursal = Sucursal::create([
        'nro_sucursal' => random_int(1000, 9999),
        'ciudad' => 'Quito',
        'secuencial' => 'UI' . random_int(1, 9),
        'nro_base' => '02' . random_int(1000000, 9999999),
    ]);

    // Crear grupo de prueba
    $this->grupo = GrupoAcceso::create([
        'nombre' => 'G_' . random_int(10000, 99999),
        'descripcion' => 'Grupo de prueba',
        'es_superadmin' => 0,
    ]);

    // Crear un usuario técnico
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

test('acceso a inventario fisico restringido para tecnicos', function () {
    $response = $this->actingAs($this->tecnico)
        ->withSession([
            'usuario' => $this->tecnico->usuario,
            'tecnico_id' => $this->tecnico->id,
            'es_superadmin' => false,
            'grupo_nombre' => 'Tecnicos',
        ])
        ->get(route('inventario_fisico.index'));

    $response->assertStatus(403);
});

test('acceso a inventario fisico permitido para superadmin', function () {
    $response = $this->actingAs($this->superadmin)
        ->withSession([
            'usuario' => $this->superadmin->usuario,
            'tecnico_id' => $this->superadmin->id,
            'es_superadmin' => true,
            'grupo_nombre' => 'Superadministradores',
        ])
        ->get(route('inventario_fisico.index'));

    $response->assertStatus(200);
});

test('crear orden empresa novisolutions stock registra productos en st', function () {
    $crearOrdenService = app(\App\Services\Operations\CrearOrdenService::class);

    $payload = [
        'sucursal_id' => $this->sucursal->id,
        'empresa_id' => 1, // Novisolutions
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Test fallas',
        'emp_series' => ['SERIENOVITEC1', 'SERIENOVITEC2'],
        'tecnico_encargado' => $this->tecnico->id,
        'tecnicos_asignados' => [$this->tecnico->id],
        'ord_tecnico_id' => $this->tecnico->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ];

    $orden = $crearOrdenService->crearOrdenEmpresa($payload);

    // Verificar que se hayan insertado 2 registros en el inventario físico ST
    $this->assertDatabaseHas('productos_inventario_fisico_st', [
        'orden_empresa_id' => $orden->id,
        'serie' => 'SERIENOVITEC1',
        'codigo' => 'MACBOOK PRO',
        'estado' => 'Tienda',
    ]);

    $this->assertDatabaseHas('productos_inventario_fisico_st', [
        'orden_empresa_id' => $orden->id,
        'serie' => 'SERIENOVITEC2',
        'codigo' => 'MACBOOK PRO',
        'estado' => 'Tienda',
    ]);
});

test('editar orden empresa novisolutions stock sincroniza productos en st', function () {
    $crearOrdenService = app(\App\Services\Operations\CrearOrdenService::class);
    $actualizarOrdenService = app(\App\Services\Operations\ActualizarOrdenService::class);

    $payloadCreate = [
        'sucursal_id' => $this->sucursal->id,
        'empresa_id' => 1,
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Test fallas',
        'emp_series' => ['SERIE_A', 'SERIE_B'],
        'tecnico_encargado' => $this->tecnico->id,
        'tecnicos_asignados' => [$this->tecnico->id],
        'ord_tecnico_id' => $this->tecnico->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ];

    $orden = $crearOrdenService->crearOrdenEmpresa($payloadCreate);

    // Simular actualización: eliminamos SERIE_B, agregamos SERIE_C
    $payloadUpdate = [
        'orden_id' => $orden->id,
        'equipo_id' => $orden->equipo_id,
        'estado' => 'En proceso',
        'fecha_prometido' => now()->addDays(2)->format('Y-m-d'),
        'descripcion' => 'Se cambia la serie',
        'eq_modelo' => 'MACBOOK PRO',
        'eq_marca' => 'APPLE',
        'eq_tipo' => 'LAPTOPS',
        'series' => ['SERIE_A', 'SERIE_C'], // Nueva lista de series
    ];

    $actualizarOrdenService->actualizarOrdenEmpresa($payloadUpdate, $this->superadmin->id);

    // SERIE_A debe seguir existiendo
    $this->assertDatabaseHas('productos_inventario_fisico_st', [
        'orden_empresa_id' => $orden->id,
        'serie' => 'SERIE_A',
    ]);

    // SERIE_B debe haber sido eliminada
    $this->assertDatabaseMissing('productos_inventario_fisico_st', [
        'orden_empresa_id' => $orden->id,
        'serie' => 'SERIE_B',
    ]);

    // SERIE_C debe haber sido agregada
    $this->assertDatabaseHas('productos_inventario_fisico_st', [
        'orden_empresa_id' => $orden->id,
        'serie' => 'SERIE_C',
        'estado' => 'Tienda',
    ]);
});

test('guardar y obtener estados de inventario fisico via api', function () {
    $crearOrdenService = app(\App\Services\Operations\CrearOrdenService::class);

    $payloadCreate = [
        'sucursal_id' => $this->sucursal->id,
        'empresa_id' => 1,
        'subtipo_empresa' => 'Stock',
        'emp_modelo' => 'MACBOOK PRO',
        'emp_marca' => 'APPLE',
        'emp_tipo_equipo' => 'LAPTOPS',
        'emp_falla' => 'Test fallas',
        'emp_series' => ['SERIE_X'],
        'tecnico_encargado' => $this->tecnico->id,
        'tecnicos_asignados' => [$this->tecnico->id],
        'ord_tecnico_id' => $this->tecnico->id,
        'emp_fecha_prometido' => now()->addDays(3)->format('Y-m-d'),
        'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
        'ingresado_por' => $this->superadmin->id,
    ];

    $orden = $crearOrdenService->crearOrdenEmpresa($payloadCreate);
    $prod = ProductoInventarioFisicoSt::where('orden_empresa_id', $orden->id)->first();

    // 1. Validar endpoint GET obtenerPorOrden
    $responseGet = $this->actingAs($this->tecnico)
        ->get("/operaciones/ordenes-empresa/inventario-fisico/{$orden->id}");

    $responseGet->assertStatus(200)
        ->assertJsonFragment([
            'serie' => 'SERIE_X',
            'estado' => 'Tienda'
        ]);

    // 2. Validar endpoint POST guardarEstados
    $payloadSave = [
        'orden_empresa_id' => $orden->id,
        'productos' => [
            [
                'id' => $prod->id,
                'estado' => 'Outlet',
                'detalle_outlet' => 'Tiene rayón en la tapa trasera'
            ]
        ]
    ];

    $responsePost = $this->actingAs($this->tecnico)
        ->postJson(route('inventario_fisico.guardar'), $payloadSave);

    $responsePost->assertStatus(200);

    // Verificar en BD que cambió a Outlet y guardó detalle
    $this->assertDatabaseHas('productos_inventario_fisico_st', [
        'id' => $prod->id,
        'estado' => 'Outlet',
        'detalle_outlet' => 'Tiene rayón en la tapa trasera'
    ]);
});
