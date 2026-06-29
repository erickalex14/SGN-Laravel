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
use App\Models\Identity\ActividadDiaria;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearDatosBasicosEmailPrueba()
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
        'correo' => 'cliente@test.com',
    ]);

    $clienteSinCorreo = Cliente::create([
        'identificacion' => '172533' . random_int(1000, 9999),
        'nombres' => 'MARIA',
        'apellidos' => 'GOMEZ',
        'numero_contacto' => '0999999998',
        'correo' => null, // Sin correo
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

    $ordenSinCorreo = new Orden;
    $ordenSinCorreo->nro_orden = 'UIO-SC-' . $codigo;
    $ordenSinCorreo->cliente_id = $clienteSinCorreo->id;
    $ordenSinCorreo->equipo_id = $equipo->id;
    $ordenSinCorreo->tecnico_id = $tecnico->id;
    $ordenSinCorreo->sucursal_id = $sucursal->id;
    $ordenSinCorreo->fecha_de_ingreso = Carbon::now('America/Guayaquil');
    $ordenSinCorreo->estado_orden = 'En proceso';
    $ordenSinCorreo->estado_repuesto = 'No requerido';
    $ordenSinCorreo->estado_garantia = 'Pendiente';
    $ordenSinCorreo->ingresado_por = $tecnico->id;
    $ordenSinCorreo->save();

    $ordenEmpresa = new OrdenEmpresa;
    $ordenEmpresa->nro_orden = 'UIO-EMP-' . $codigo;
    $ordenEmpresa->empresa_id = $empresa->id;
    $ordenEmpresa->equipo_id = $equipo->id;
    $ordenEmpresa->tecnico_id = $tecnico->id;
    $ordenEmpresa->sucursal_id = $sucursal->id;
    $ordenEmpresa->fecha_ingreso = Carbon::now('America/Guayaquil');
    $ordenEmpresa->estado = 'En proceso';
    $ordenEmpresa->subtipo = 'Mantenimiento';
    $ordenEmpresa->ingresado_por = $tecnico->id;
    $ordenEmpresa->save();

    return [
        'tecnico' => $tecnico,
        'orden' => $orden,
        'ordenSinCorreo' => $ordenSinCorreo,
        'ordenEmpresa' => $ordenEmpresa,
    ];
}

test('tecnico puede enviar email al cliente en orden personal y se genera bitacora', function () {
    Mail::fake();

    $datos = crearDatosBasicosEmailPrueba();
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
        ->post(route('mis_ordenes.enviar_email'), [
            'orden_id' => $orden->id,
            'asunto' => 'Novedades sobre su reparación',
            'contenido' => 'El repuesto ha sido instalado y el equipo está listo para entrega.',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
        'mensaje' => 'Correo electrónico enviado al cliente correctamente.',
    ]);

    // Verificar bitácora
    $this->assertDatabaseHas('actividades_diarias', [
        'usuario_id' => $tecnico->id,
        'tipo_accion' => 'enviar_email_cliente',
        'modulo' => 'ordenes',
    ]);
});

test('tecnico puede enviar email al cliente en orden de empresa', function () {
    Mail::fake();

    $datos = crearDatosBasicosEmailPrueba();
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
        ->post(route('mis_ordenes.enviar_email'), [
            'orden_empresa_id' => $ordenEmpresa->id,
            'asunto' => 'Confirmación de mantenimiento corporativo',
            'contenido' => 'El mantenimiento de su servidor ha sido programado con éxito.',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => true,
    ]);
});

test('no se puede enviar email si el cliente no tiene correo registrado', function () {
    Mail::fake();

    $datos = crearDatosBasicosEmailPrueba();
    $tecnico = $datos['tecnico'];
    $ordenSinCorreo = $datos['ordenSinCorreo'];

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
        ->post(route('mis_ordenes.enviar_email'), [
            'orden_id' => $ordenSinCorreo->id,
            'asunto' => 'Intento de email sin correo',
            'contenido' => 'Este correo no debería enviarse.',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ok' => false,
        'error' => 'El cliente no tiene un correo electrónico registrado.',
    ]);
});
