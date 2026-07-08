<?php

use App\Models\Directory\Sucursal;
use App\Models\Directory\Cliente;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Usuario;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Operations\Equipo;
use App\Models\Operations\EquipoSerie;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearUsuarioHelper(int $rolId): Usuario
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

test('endpoint verificar-duplicado detecta serie y factura duplicada', function () {
    $usuario = crearUsuarioHelper(2);

    $cliente = Cliente::create([
        'identificacion' => '1725324782',
        'nombres' => 'JUAN',
        'apellidos' => 'PEREZ',
        'numero_contacto' => '0999999999',
        'correo' => 'juan@gmail.com',
    ]);

    $equipo = Equipo::create([
        'tipo' => 'LAPTOPS',
        'marca' => 'APPLE',
        'modelo' => 'MACBOOK',
        'serie' => 'TESTSERIE123',
    ]);

    $nro = 'UIO-' . str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

    $orden = Orden::create([
        'nro_orden' => $nro,
        'nro_factura' => '123456',
        'cliente_id' => $cliente->id,
        'equipo_id' => $equipo->id,
        'tecnico_id' => $usuario->id,
        'sucursal_id' => $usuario->sucursal_id,
        'fecha_de_ingreso' => Carbon::now()->format('Y-m-d H:i:s'),
        'estado_orden' => 'Pendiente',
        'ingresado_por' => $usuario->id,
    ]);

    // 1. Verificar duplicado de serie
    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_crear' => ['ver' => true],
            ],
        ])
        ->postJson(route('ordenes.verificar_duplicado'), [
            'series' => ['TESTSERIE123'],
            'facturas' => [],
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'duplicated' => true,
    ]);
    expect($response->json('coincidencias'))->toHaveCount(1);
    expect($response->json('coincidencias.0.nro_orden'))->toBe($nro);

    // 2. Verificar duplicado de factura (deshabilitado, no debe duplicar)
    $responseFactura = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_crear' => ['ver' => true],
            ],
        ])
        ->postJson(route('ordenes.verificar_duplicado'), [
            'series' => [],
            'facturas' => ['123456'],
        ]);

    $responseFactura->assertStatus(200);
    $responseFactura->assertJson([
        'duplicated' => false,
    ]);
    expect($responseFactura->json('coincidencias'))->toHaveCount(0);

    // 3. Ignorar serie comodin "sn"
    $responseSn = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
            'permisos' => [
                'ordenes_crear' => ['ver' => true],
            ],
        ])
        ->postJson(route('ordenes.verificar_duplicado'), [
            'series' => ['sn', 's/n', ''],
            'facturas' => [],
        ]);

    $responseSn->assertStatus(200);
    $responseSn->assertJson([
        'duplicated' => false,
        'coincidencias' => [],
    ]);
});

test('vistas imprimir e imprimirEmpresa cargan historial de reingresos', function () {
    $usuario = crearUsuarioHelper(2);

    $cliente = Cliente::create([
        'identificacion' => '1725324782',
        'nombres' => 'JUAN',
        'apellidos' => 'PEREZ',
        'numero_contacto' => '0999999999',
    ]);

    // Crear primera orden (ingreso 1)
    $equipo1 = Equipo::create([
        'tipo' => 'LAPTOPS',
        'marca' => 'APPLE',
        'modelo' => 'MACBOOK',
        'serie' => 'DUPLICADA999',
    ]);

    $nro1 = 'UIO-' . str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $orden1 = Orden::create([
        'nro_orden' => $nro1,
        'cliente_id' => $cliente->id,
        'equipo_id' => $equipo1->id,
        'tecnico_id' => $usuario->id,
        'sucursal_id' => $usuario->sucursal_id,
        'fecha_de_ingreso' => Carbon::now()->subDays(5)->format('Y-m-d H:i:s'),
        'estado_orden' => 'Pendiente',
        'ingresado_por' => $usuario->id,
    ]);

    // Crear segunda orden (ingreso 2)
    $equipo2 = Equipo::create([
        'tipo' => 'LAPTOPS',
        'marca' => 'APPLE',
        'modelo' => 'MACBOOK',
        'serie' => 'DUPLICADA999',
    ]);

    $nro2 = 'UIO-' . str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $orden2 = Orden::create([
        'nro_orden' => $nro2,
        'cliente_id' => $cliente->id,
        'equipo_id' => $equipo2->id,
        'tecnico_id' => $usuario->id,
        'sucursal_id' => $usuario->sucursal_id,
        'fecha_de_ingreso' => Carbon::now()->format('Y-m-d H:i:s'),
        'estado_orden' => 'Pendiente',
        'ingresado_por' => $usuario->id,
    ]);

    // Acceder a la vista de impresion de orden2
    $response = $this->actingAs($usuario)
        ->withSession([
            'tecnico_id' => $usuario->id,
            'sucursal_id' => $usuario->sucursal_id,
        ])
        ->get(route('ordenes.imprimir', ['id' => $orden2->id]));

    $response->assertStatus(200);
    $response->assertViewHas('historialIngresos');
    $historial = $response->viewData('historialIngresos');
    expect($historial)->toHaveCount(1);
    expect($historial[0]['nro_orden'])->toBe($nro1);
});
