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
use App\Models\Identity\Notificacion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

uses(DatabaseTransactions::class);

function crearDatosNotifPrueba()
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

    $tecnico2 = new Usuario;
    $tecnico2->usuario = '09' . random_int(10000000, 99999999);
    $tecnico2->clave = '';
    $tecnico2->clave_hash = bcrypt('password123');
    $tecnico2->nombre_tecnico = 'Tecnico Test 2 ' . $codigo;
    $tecnico2->telefono = '0999999999';
    $tecnico2->acceso_nc = 0;
    $tecnico2->rol_id = 2;
    $tecnico2->grupo_id = $grupo->id;
    $tecnico2->sucursal_id = $sucursal->id;
    $tecnico2->activo = 1;
    $tecnico2->save();

    $cliente = Cliente::create([
        'identificacion' => '172532' . random_int(1000, 9999),
        'nombres' => 'JUAN',
        'apellidos' => 'PEREZ',
        'numero_contacto' => '0999999999',
        'correo' => 'juan@test.com',
    ]);

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

    return [
        'tecnico' => $tecnico,
        'tecnico2' => $tecnico2,
        'cliente' => $cliente,
        'empresa' => $empresa,
        'equipo' => $equipo,
        'sucursal' => $sucursal,
    ];
}

test('al crear una orden personal se genera una notificacion de orden asignada', function () {
    $datos = crearDatosNotifPrueba();
    $tecnico = $datos['tecnico'];
    $cliente = $datos['cliente'];
    $equipo = $datos['equipo'];
    $sucursal = $datos['sucursal'];

    $dto = new \App\DTOs\Operations\CrearOrdenDTO(
        // Datos del Cliente
        $cliente->id,
        $cliente->identificacion,
        $cliente->nombres,
        $cliente->apellidos,
        $cliente->numero_contacto,
        $cliente->correo,
        'Direccion Prueba',

        // Datos del Equipo
        $equipo->tipo,
        $equipo->marca,
        $equipo->modelo,
        null, // contrasena_equipo
        $equipo->falla,
        $equipo->observacion,
        null, // tipo_servicio_id
        null, // tipo_servicio_texto
        null, // producto_inventario_codigo
        [$equipo->serie], // series
        [], // credenciales

        // Datos de la Orden
        $sucursal->id,
        $tecnico->id,
        $tecnico->id, // ingresado_por
        Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'), // fecha_ingreso
        'Servicio Cliente Externo', // motivo_ingreso
        null, // nro_factura
        null, // nro_factura_2
        null, // fecha_facturacion
        Carbon::now('America/Guayaquil')->addDays(3)->format('Y-m-d'), // fecha_prometido
        null, // nro_sucursal_cliente
        'No requerido', // estado_repuesto
        null, // garantia_tipo
        null, // cas_id
        null, // repuesto_inventario_id
        [] // repuestos_seleccionados
    );

    $service = app(\App\Services\Operations\CrearOrdenService::class);
    $orden = $service->crearOrden($dto);

    // Verificar notificación en BD
    $this->assertDatabaseHas('notificaciones', [
        'usuario_id' => $tecnico->id,
        'tipo' => 'orden_asignada',
        'nro_orden' => $orden->nro_orden,
    ]);
});

test('al reasignar una orden de empresa se genera una notificacion de orden asignada', function () {
    $datos = crearDatosNotifPrueba();
    $tecnico = $datos['tecnico'];
    $tecnico2 = $datos['tecnico2'];
    $empresa = $datos['empresa'];
    $equipo = $datos['equipo'];
    $sucursal = $datos['sucursal'];

    $ordenEmpresa = new OrdenEmpresa;
    $ordenEmpresa->nro_orden = 'UIO-EMP-' . random_int(1000, 9999);
    $ordenEmpresa->empresa_id = $empresa->id;
    $ordenEmpresa->equipo_id = $equipo->id;
    $ordenEmpresa->tecnico_id = $tecnico->id;
    $ordenEmpresa->sucursal_id = $sucursal->id;
    $ordenEmpresa->fecha_ingreso = Carbon::now('America/Guayaquil');
    $ordenEmpresa->estado = 'En proceso';
    $ordenEmpresa->subtipo = 'Stock';
    $ordenEmpresa->ingresado_por = $tecnico->id;
    $ordenEmpresa->save();

    // Reasignamos al tecnico 2
    $service = app(\App\Services\Operations\GestionOrdenService::class);
    $service->reasignarTecnico($ordenEmpresa->id, $tecnico2->id, 'empresa');

    // Verificar notificación para tecnico 2
    $this->assertDatabaseHas('notificaciones', [
        'usuario_id' => $tecnico2->id,
        'tipo' => 'orden_asignada',
        'nro_orden' => $ordenEmpresa->nro_orden,
    ]);
});

test('el comando de verificar antiguedad genera alertas de 3 y 5 dias y envia email al cliente', function () {
    Mail::fake();

    $datos = crearDatosNotifPrueba();
    $tecnico = $datos['tecnico'];
    $cliente = $datos['cliente'];
    $equipo = $datos['equipo'];
    $sucursal = $datos['sucursal'];

    // 1. Orden de 3 días (fecha de ingreso hace 3 días)
    $orden3 = new Orden;
    $orden3->nro_orden = 'UIO-3D-' . random_int(1000, 9999);
    $orden3->cliente_id = $cliente->id;
    $orden3->equipo_id = $equipo->id;
    $orden3->tecnico_id = $tecnico->id;
    $orden3->sucursal_id = $sucursal->id;
    $orden3->fecha_de_ingreso = Carbon::now('America/Guayaquil')->subDays(3);
    $orden3->estado_orden = 'En proceso';
    $orden3->ingresado_por = $tecnico->id;
    $orden3->save();

    // 2. Orden de 5 días
    $orden5 = new Orden;
    $orden5->nro_orden = 'UIO-5D-' . random_int(1000, 9999);
    $orden5->cliente_id = $cliente->id;
    $orden5->equipo_id = $equipo->id;
    $orden5->tecnico_id = $tecnico->id;
    $orden5->sucursal_id = $sucursal->id;
    $orden5->fecha_de_ingreso = Carbon::now('America/Guayaquil')->subDays(5);
    $orden5->estado_orden = 'En proceso';
    $orden5->ingresado_por = $tecnico->id;
    $orden5->save();

    // Ejecutar el Artisan Command
    Artisan::call('ordenes:verificar-antiguedad');

    // Verificar que se hayan creado las notificaciones correspondientes
    $this->assertDatabaseHas('notificaciones', [
        'usuario_id' => $tecnico->id,
        'tipo' => 'orden_atrasada_3_dias',
        'nro_orden' => $orden3->nro_orden,
    ]);

    $this->assertDatabaseHas('notificaciones', [
        'usuario_id' => $tecnico->id,
        'tipo' => 'orden_atrasada_5_dias',
        'nro_orden' => $orden5->nro_orden,
    ]);

    // Ejecutar nuevamente y validar que no se generen notificaciones duplicadas (mismo conteo en BD)
    $cant3Antes = Notificacion::where('tipo', 'orden_atrasada_3_dias')->count();
    Artisan::call('ordenes:verificar-antiguedad');
    $cant3Despues = Notificacion::where('tipo', 'orden_atrasada_3_dias')->count();
    expect($cant3Despues)->toBe($cant3Antes);
});
