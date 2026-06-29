<?php

use App\Models\Identity\Usuario;
use App\Models\Identity\Rol;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Directory\Empresa;
use App\Models\Operations\Equipo;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Inventory\Repuesto;
use App\Models\Operations\OrdenRepuesto;
use App\Models\Operations\SolicitudRepuesto;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearDatosRepuestoPrueba()
{
    $codigo = (string) random_int(1000, 9999);

    $rol = Rol::firstOrCreate(['rol' => 'tecnico'], ['id' => 2]);

    $sucursal = Sucursal::create([
        'nro_sucursal' => random_int(100, 999),
        'ciudad' => 'Quito',
        'secuencial' => 'UIO' . random_int(1, 9),
        'nro_base' => '02' . random_int(1000000, 9999999),
    ]);

    $grupo = GrupoAcceso::create([
        'nombre' => 'Tecnico ' . $codigo,
        'descripcion' => 'Grupo Tecnico de prueba',
        'es_superadmin' => 0,
    ]);

    $tecnico = new Usuario;
    $tecnico->usuario = '09' . random_int(10000000, 99999999);
    $tecnico->clave = '';
    $tecnico->clave_hash = bcrypt('password123');
    $tecnico->nombre_tecnico = 'Tecnico Test ' . $codigo;
    $tecnico->telefono = '0999999999';
    $tecnico->acceso_nc = 0;
    $tecnico->rol_id = 2; // Tecnico
    $tecnico->grupo_id = $grupo->id;
    $tecnico->sucursal_id = $sucursal->id;
    $tecnico->activo = 1;
    $tecnico->save();

    $empresa = Empresa::create([
        'ruc' => '17922' . random_int(10000, 99999) . '001',
        'nombre' => 'EMPRESA TEST ' . $codigo,
        'direccion' => 'AV. GENERAL',
        'telefono' => '022222222',
        'correo' => 'empresa@test.com',
        'activo' => 1,
    ]);

    $equipo = Equipo::create([
        'tipo' => 'LAPTOPS',
        'marca' => 'APPLE',
        'modelo' => 'MACBOOK PRO',
        'falla' => 'No enciende',
        'observacion' => 'Prueba',
        'serie' => 'SN-' . $codigo,
    ]);

    $ordenEmpresa = new OrdenEmpresa;
    $ordenEmpresa->nro_orden = 'UIO-EMP-' . $codigo;
    $ordenEmpresa->empresa_id = $empresa->id;
    $ordenEmpresa->equipo_id = $equipo->id;
    $ordenEmpresa->tecnico_id = $tecnico->id;
    $ordenEmpresa->sucursal_id = $sucursal->id;
    $ordenEmpresa->fecha_ingreso = Carbon::now('America/Guayaquil');
    $ordenEmpresa->estado = 'En proceso';
    $ordenEmpresa->subtipo = 'Stock'; // subtipo Stock
    $ordenEmpresa->estado_repuesto = 'No requerido';
    $ordenEmpresa->ingresado_por = $tecnico->id;
    $ordenEmpresa->save();

    $repuesto = Repuesto::create([
        'codigo' => 'REP-' . $codigo,
        'nombre' => 'Pantalla Macbook ' . $codigo,
        'descripcion' => 'Pantalla retina Macbook PRO',
        'stock' => 10,
        'categoria' => 'Pantallas',
        'precio_costo' => 100.00,
        'precio_venta' => 150.00,
    ]);

    return [
        'tecnico' => $tecnico,
        'ordenEmpresa' => $ordenEmpresa,
        'repuesto' => $repuesto,
    ];
}

test('tecnico puede cambiar estado de repuesto a requerido en orden de empresa', function () {
    $datos = crearDatosRepuestoPrueba();
    $tecnico = $datos['tecnico'];
    $ordenEmpresa = $datos['ordenEmpresa'];

    $response = $this->actingAs($tecnico)
        ->withSession([
            'tecnico_id' => $tecnico->id,
            'usuario' => $tecnico->usuario,
            'nombre' => $tecnico->nombre_tecnico,
            'sucursal_id' => $tecnico->sucursal_id,
            'permisos' => [
                'ordenes_mis' => ['ver' => true],
            ],
        ])
        ->post(route('mis_ordenes.repuesto_estado'), [
            'orden_id' => $ordenEmpresa->id,
            'estado_repuesto' => 'Requerido',
            'tipo_orden' => 'empresa',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
        'mensaje' => 'Estado de repuesto actualizado.',
    ]);

    $this->assertDatabaseHas('ordenesempresas', [
        'id' => $ordenEmpresa->id,
        'estado_repuesto' => 'Requerido',
    ]);

    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $tecnico->id,
        'tipo_accion' => 'cambiar_estado_repuesto',
        'referencia_id' => $ordenEmpresa->id,
        'referencia_tipo' => 'orden_empresa',
    ]);
});

test('tecnico puede solicitar repuesto para orden de empresa y se genera ticket SR', function () {
    $datos = crearDatosRepuestoPrueba();
    $tecnico = $datos['tecnico'];
    $ordenEmpresa = $datos['ordenEmpresa'];

    $response = $this->actingAs($tecnico)
        ->withSession([
            'tecnico_id' => $tecnico->id,
            'usuario' => $tecnico->usuario,
            'nombre' => $tecnico->nombre_tecnico,
            'sucursal_id' => $tecnico->sucursal_id,
            'permisos' => [
                'ordenes_mis' => ['ver' => true],
                'solicitar_repuesto' => ['ver' => true],
            ],
        ])
        ->post(route('solicitudes_repuestos.solicitar'), [
            'orden_id' => $ordenEmpresa->id,
            'cantidad' => 1,
            'repuesto_nombre' => 'Teclado de repuesto',
            'nro_parte' => 'P-123',
            'descripcion' => 'Necesario para reparación',
            'tipo_orden' => 'empresa',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
    ]);
    expect($response->json('mensaje'))->toContain('Ticket');

    $this->assertDatabaseHas('solicitudesrepuesto', [
        'orden_empresa_id' => $ordenEmpresa->id,
        'tecnico_id' => $tecnico->id,
        'repuesto_nombre' => 'Teclado de repuesto',
        'cantidad' => 1,
    ]);

    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $tecnico->id,
        'tipo_accion' => 'solicitar_repuesto',
        'referencia_id' => $ordenEmpresa->id,
        'referencia_tipo' => 'orden_empresa',
    ]);
});

test('tecnico puede asignar repuesto en stock a orden de empresa', function () {
    $datos = crearDatosRepuestoPrueba();
    $tecnico = $datos['tecnico'];
    $ordenEmpresa = $datos['ordenEmpresa'];
    $repuesto = $datos['repuesto'];

    $response = $this->actingAs($tecnico)
        ->withSession([
            'tecnico_id' => $tecnico->id,
            'usuario' => $tecnico->usuario,
            'nombre' => $tecnico->nombre_tecnico,
            'sucursal_id' => $tecnico->sucursal_id,
            'permisos' => [
                'ordenes_mis' => ['ver' => true],
            ],
        ])
        ->post(route('mis_ordenes.repuesto_asignar'), [
            'orden_id' => $ordenEmpresa->id,
            'repuesto_inventario_id' => $repuesto->id,
            'tipo_orden' => 'empresa',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
        'mensaje' => 'Repuesto asignado correctamente.',
    ]);

    $this->assertDatabaseHas('ordenesempresas', [
        'id' => $ordenEmpresa->id,
        'estado_repuesto' => 'Con stock',
        'repuesto_inventario_id' => $repuesto->id,
    ]);

    $this->assertDatabaseHas('orden_repuestos', [
        'orden_empresa_id' => $ordenEmpresa->id,
        'repuesto_id' => $repuesto->id,
    ]);

    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $tecnico->id,
        'tipo_accion' => 'asignar_repuesto',
        'referencia_id' => $ordenEmpresa->id,
        'referencia_tipo' => 'orden_empresa',
    ]);

    // Verificar descuento de stock
    expect(Repuesto::find($repuesto->id)->stock)->toBe(9);
});

test('los movimientos de repuestos de ordenes de empresa aparecen en la pagina de auditoria', function () {
    $datos = crearDatosRepuestoPrueba();
    $tecnico = $datos['tecnico'];
    $ordenEmpresa = $datos['ordenEmpresa'];
    $repuesto = $datos['repuesto'];

    // 1. Asignamos repuesto
    $response = $this->actingAs($tecnico)
        ->withSession([
            'tecnico_id' => $tecnico->id,
            'usuario' => $tecnico->usuario,
            'nombre' => $tecnico->nombre_tecnico,
            'sucursal_id' => $tecnico->sucursal_id,
            'permisos' => [
                'ordenes_mis' => ['ver' => true],
            ],
        ])
        ->post(route('mis_ordenes.repuesto_asignar'), [
            'orden_id' => $ordenEmpresa->id,
            'repuesto_inventario_id' => $repuesto->id,
            'tipo_orden' => 'empresa',
        ]);

    $response->assertStatus(200);

    // 2. Cargamos la página de auditoría (necesita un usuario administrador o con permiso inv_repuestos ver)
    $admin = Usuario::create([
        'usuario' => '08' . random_int(10000000, 99999999),
        'clave' => '',
        'clave_hash' => bcrypt('password123'),
        'nombre_tecnico' => 'Admin Test',
        'telefono' => '0999999999',
        'acceso_nc' => 1,
        'rol_id' => 1, // Admin
        'sucursal_id' => $tecnico->sucursal_id,
        'activo' => 1,
    ]);

    $response2 = $this->actingAs($admin)
        ->withSession([
            'tecnico_id' => $admin->id,
            'usuario' => $admin->usuario,
            'nombre' => $admin->nombre_tecnico,
            'permisos' => [
                'inv_repuestos' => ['ver' => true],
            ],
        ])
        ->get(route('repuestos.auditoria'));

    $response2->assertStatus(200);
    $response2->assertSee($ordenEmpresa->nro_orden);
    $response2->assertSee($repuesto->codigo);
});
