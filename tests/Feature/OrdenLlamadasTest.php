<?php

use App\Models\Identity\Usuario;
use App\Models\Identity\Rol;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Directory\Cliente;
use App\Models\Directory\Empresa;
use App\Models\Operations\Equipo;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Operations\LlamadaOrden;
use App\Models\Identity\ActividadDiaria;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearDatosBasicosPrueba()
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

    $cliente = Cliente::create([
        'identificacion' => '172532' . random_int(1000, 9999),
        'nombres' => 'JUAN',
        'apellidos' => 'PEREZ',
        'numero_contacto' => '0999999999',
        'correo' => 'juan@test.com',
    ]);

    $empresa = Empresa::create([
        'ruc' => '17922' . random_int(10000, 99999) . '001', // Exactamente 13 dígitos (5 + 5 + 3)
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

    $orden = new Orden;
    $orden->nro_orden = 'UIO-' . $codigo;
    $orden->cliente_id = $cliente->id;
    $orden->equipo_id = $equipo->id;
    $orden->tecnico_id = $tecnico->id;
    $orden->sucursal_id = $sucursal->id;
    $orden->fecha_de_ingreso = Carbon::now('America/Guayaquil');
    $orden->estado_orden = 'En proceso';
    $orden->estado_repuesto = 'No requerido';
    $orden->estado_garantia = 'Pendiente';
    $orden->ingresado_por = $tecnico->id;
    $orden->save();

    $ordenEmpresa = new OrdenEmpresa;
    $ordenEmpresa->nro_orden = 'UIO-EMP-' . $codigo;
    $ordenEmpresa->empresa_id = $empresa->id;
    $ordenEmpresa->equipo_id = $equipo->id;
    $ordenEmpresa->tecnico_id = $tecnico->id;
    $ordenEmpresa->sucursal_id = $sucursal->id;
    $ordenEmpresa->fecha_ingreso = Carbon::now('America/Guayaquil');
    $ordenEmpresa->estado = 'En proceso';
    $ordenEmpresa->subtipo = 'Mantenimiento'; // Campo requerido subtipo
    $ordenEmpresa->ingresado_por = $tecnico->id;
    $ordenEmpresa->save();

    return [
        'tecnico' => $tecnico,
        'orden' => $orden,
        'ordenEmpresa' => $ordenEmpresa,
    ];
}

test('un tecnico autenticado puede registrar llamadas a una orden de cliente y se genera bitacora', function () {
    $datos = crearDatosBasicosPrueba();
    $tecnico = $datos['tecnico'];
    $orden = $datos['orden'];

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
        ->post(route('ordenes.llamadas.registrar'), [
            'orden_id' => $orden->id,
            'observacion' => 'Coordinación de entrega de equipo con el cliente.',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
        'mensaje' => 'Llamada registrada con éxito.',
    ]);

    $response->assertJsonStructure([
        'llamada' => [
            'id',
            'fecha_hora',
            'usuario_nombre',
            'observacion',
        ]
    ]);

    // Verificar en BD la llamada
    $this->assertDatabaseHas('ordenes_llamadas', [
        'orden_id' => $orden->id,
        'usuario_id' => $tecnico->id,
        'observacion' => 'Coordinación de entrega de equipo con el cliente.',
    ]);

    // Verificar que se haya registrado en la bitácora
    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $tecnico->id,
        'tipo_accion' => 'llamada_cliente',
        'modulo' => 'ordenes',
    ]);
});

test('un tecnico autenticado puede registrar llamadas a una orden de empresa y se genera bitacora', function () {
    $datos = crearDatosBasicosPrueba();
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
        ->post(route('ordenes.llamadas.registrar'), [
            'orden_empresa_id' => $ordenEmpresa->id,
            'observacion' => 'Llamada para notificar avance de orden empresa.',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
        'mensaje' => 'Llamada registrada con éxito.',
    ]);

    // Verificar en BD
    $this->assertDatabaseHas('ordenes_llamadas', [
        'orden_empresa_id' => $ordenEmpresa->id,
        'usuario_id' => $tecnico->id,
        'observacion' => 'Llamada para notificar avance de orden empresa.',
    ]);

    // Verificar bitácora
    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $tecnico->id,
        'tipo_accion' => 'llamada_cliente',
        'modulo' => 'ordenes',
    ]);
});

test('registro de llamada valida que la observacion no exceda 1000 caracteres', function () {
    $datos = crearDatosBasicosPrueba();
    $tecnico = $datos['tecnico'];
    $orden = $datos['orden'];

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
        ->post(route('ordenes.llamadas.registrar'), [
            'orden_id' => $orden->id,
            'observacion' => str_repeat('a', 1001),
        ]);

    // Debe retornar 200 con ok => false
    $response->assertStatus(200);
    $response->assertJson([
        'ok' => false,
    ]);
    expect($response->json('error'))->toContain('observacion');
});
