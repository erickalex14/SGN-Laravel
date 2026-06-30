<?php

use App\Models\Identity\Usuario;
use App\Models\Identity\Rol;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Directory\Cliente;
use App\Models\Operations\Equipo;
use App\Models\Operations\Orden;
use App\Models\Operations\SolicitudNc;
use App\Repositories\Operations\OrdenRepository;
use App\Services\Operations\GestionOrdenService;
use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearDatosPruebaCierre()
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
    $tecnico->acceso_nc = 0;
    $tecnico->rol_id = 2; // Tecnico
    $tecnico->grupo_id = $grupo->id;
    $tecnico->sucursal_id = $sucursal->id;
    $tecnico->activo = 1;
    $tecnico->save();

    $cliente = Cliente::create([
        'identificacion' => '17' . random_int(10000000, 99999999) . '001',
        'nombres' => 'Cliente Test',
        'apellidos' => $codigo,
        'numero_contacto' => '0999999999',
        'correo' => 'cliente_' . $codigo . '@example.com',
        'sucursal_id' => $sucursal->id,
        'tipo_cliente' => 'natural',
    ]);

    $equipo = Equipo::create([
        'tipo' => 'LAPTOP',
        'marca' => 'ASUS',
        'modelo' => 'ROG',
        'serie' => 'SN-' . $codigo,
    ]);

    return compact('tecnico', 'cliente', 'equipo', 'sucursal');
}

test('las ordenes de tipo garantia no se cierran automaticamente al pasar a Nota de Credito', function () {
    $datos = crearDatosPruebaCierre();

    $orden = Orden::create([
        'nro_orden' => 'UIO-NCC-' . random_int(100000, 999999),
        'motivo_ingreso' => 'Validacion de Garantia',
        'cliente_id' => $datos['cliente']->id,
        'equipo_id' => $datos['equipo']->id,
        'tecnico_id' => $datos['tecnico']->id,
        'sucursal_id' => $datos['sucursal']->id,
        'fecha_de_ingreso' => Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'),
        'estado_orden' => 'Pendiente',
        'estado_garantia' => 'Aceptada',
    ]);

    \App\Models\Operations\Informe::create([
        'orden_id' => $orden->id,
        'tecnico_id' => $datos['tecnico']->id,
        'antecedentes' => 'Daño físico',
        'proceso' => 'Revisión y descarte',
        'conclusion' => 'Cambio de parte',
        'recomendaciones' => 'Emitir NC',
        'estado_equipo' => 'Inoperable',
        'fecha_informe' => Carbon::now('America/Guayaquil')->format('Y-m-d'),
        'fecha_creacion' => Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'),
    ]);

    $service = app(GestionOrdenService::class);
    $dto = new CambiarEstadoOrdenDTO(
        orden_id: $orden->id,
        estado_orden: 'Nota de Credito',
        nc_asunto: 'Defecto de fábrica',
        nc_detalles: 'Pantalla dañada'
    );

    $service->actualizarEstado($dto, $datos['tecnico']->id, $datos['tecnico']->nombre_tecnico, false);

    $ordenRefrescada = Orden::find($orden->id);
    expect($ordenRefrescada->estado_orden)->toBe('Nota de Credito');
    expect($ordenRefrescada->fecha_finalizacion)->toBeNull(); // Sigue abierta
});

test('las ordenes de tipo servicio comun si se cierran automaticamente al pasar a Nota de Credito', function () {
    $datos = crearDatosPruebaCierre();

    $orden = Orden::create([
        'nro_orden' => 'UIO-NCC-' . random_int(100000, 999999),
        'motivo_ingreso' => 'Servicio Cliente Externo',
        'cliente_id' => $datos['cliente']->id,
        'equipo_id' => $datos['equipo']->id,
        'tecnico_id' => $datos['tecnico']->id,
        'sucursal_id' => $datos['sucursal']->id,
        'fecha_de_ingreso' => Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'),
        'estado_orden' => 'Pendiente',
    ]);

    // Usar el servicio de actualización (ActualizarOrdenService) que usan los administradores
    $service = app(\App\Services\Operations\ActualizarOrdenService::class);
    $dto = new \App\DTOs\Operations\ActualizarOrdenDTO(
        orden_id: $orden->id,
        equipo_id: $datos['equipo']->id,
        estado_orden: 'Nota de Credito',
        falla: 'No enciende',
        observacion: 'Prueba',
        tipo_servicio_id: null,
        valor_estandar_id: null,
        repuesto_inventario_id: null,
        fecha_prometido: Carbon::now('America/Guayaquil')->addDays(3)->format('Y-m-d'),
        usuario_modificacion_id: $datos['tecnico']->id,
        cas_id: null,
        cli_identificacion: $datos['cliente']->identificacion,
        cli_nombres: $datos['cliente']->nombres,
        cli_apellidos: $datos['cliente']->apellidos,
        cli_telefono: $datos['cliente']->numero_contacto,
        cli_correo: $datos['cliente']->correo,
        cli_direccion: 'Direccion Prueba',
        nro_factura: null,
        nro_factura_2: null,
        nro_sucursal_cliente: null,
        fecha_facturacion: null,
        series: [],
        tecnico_id: $datos['tecnico']->id,
        eq_tipo: $datos['equipo']->tipo,
        eq_marca: $datos['equipo']->marca,
        eq_modelo: $datos['equipo']->modelo,
        eq_contrasena: null,
        motivo_ingreso: 'Servicio Cliente Externo',
        garantia_tipo: null,
        observacion_orden: 'Obs'
    );

    $service->actualizarOrden($dto);

    $ordenRefrescada = Orden::find($orden->id);
    expect($ordenRefrescada->estado_orden)->toBe('Nota de Credito');
    expect($ordenRefrescada->fecha_finalizacion)->not->toBeNull(); // Se cierra de inmediato
});

test('el tecnico puede registrar la transferencia para cerrar la orden de garantia', function () {
    $datos = crearDatosPruebaCierre();

    $orden = Orden::create([
        'nro_orden' => 'UIO-NCC-' . random_int(100000, 999999),
        'motivo_ingreso' => 'Validacion de Garantia',
        'cliente_id' => $datos['cliente']->id,
        'equipo_id' => $datos['equipo']->id,
        'tecnico_id' => $datos['tecnico']->id,
        'sucursal_id' => $datos['sucursal']->id,
        'fecha_de_ingreso' => Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'),
        'estado_orden' => 'Nota de Credito',
        'fecha_finalizacion' => null, // Sigue abierta
    ]);

    // Crear solicitud de NC Aprobada
    SolicitudNc::create([
        'nro_solicitud' => 'NC-' . random_int(1000, 9999),
        'orden_id' => $orden->id,
        'fecha_solicitud' => Carbon::now('America/Guayaquil')->format('Y-m-d'),
        'asunto' => 'Cambio de equipo',
        'detalles' => 'Garantía aceptada',
        'tecnico_id' => $datos['tecnico']->id,
        'tecnico_nombre' => $datos['tecnico']->nombre_tecnico,
        'estado' => 'Aprobada',
    ]);

    // Simular sesión de técnico y autenticación
    $response = $this->actingAs($datos['tecnico'])->withSession([
        'tecnico_id' => $datos['tecnico']->id,
        'nombre_tecnico' => $datos['tecnico']->nombre_tecnico,
        'grupo_nombre' => 'tecnico',
        'es_superadmin' => true,
    ])->postJson(route('mis_ordenes.registrar_transferencia'), [
        'orden_id' => $orden->id,
        'plataforma' => 'Milenium',
        'numero' => '123456789'
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('ok', true);

    $ordenCerrada = Orden::find($orden->id);
    expect($ordenCerrada->transferencia_plataforma)->toBe('Milenium');
    expect($ordenCerrada->transferencia_numero)->toBe('123456789');
    expect($ordenCerrada->fecha_finalizacion)->not->toBeNull(); // Ahora sí se cerró
});

test('los reportes muestran subestados correctos segun el numero de transferencia en garantias', function () {
    $datos = crearDatosPruebaCierre();

    $orden = Orden::create([
        'nro_orden' => 'UIO-NCC-' . random_int(100000, 999999),
        'motivo_ingreso' => 'Validacion de Garantia',
        'cliente_id' => $datos['cliente']->id,
        'equipo_id' => $datos['equipo']->id,
        'tecnico_id' => $datos['tecnico']->id,
        'sucursal_id' => $datos['sucursal']->id,
        'fecha_de_ingreso' => Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'),
        'estado_orden' => 'Nota de Credito',
        'fecha_finalizacion' => null,
    ]);

    $solicitud = SolicitudNc::create([
        'nro_solicitud' => 'NC-' . random_int(1000, 9999),
        'orden_id' => $orden->id,
        'fecha_solicitud' => Carbon::now('America/Guayaquil')->format('Y-m-d'),
        'asunto' => 'Cambio de equipo',
        'detalles' => 'Garantía aceptada',
        'tecnico_id' => $datos['tecnico']->id,
        'tecnico_nombre' => $datos['tecnico']->nombre_tecnico,
        'estado' => 'Aprobada',
    ]);

    $repo = app(OrdenRepository::class);
    $filtro = new \App\DTOs\Operations\ReporteFiltroDTO(
        fecha_inicio: null,
        fecha_fin: null,
        estado: null,
        estado_repuesto: null,
        estado_garantia: null,
        motivo_ingreso: null,
        marca: null,
        tipo_equipo: null,
        tipo_orden: null,
        tecnico_id: null,
        sucursal_id: null,
        cas_id: null
    );

    // 1. Sin número de transferencia: NC Aprobada-Abierta
    $reporte1 = $repo->filtrarParaReporte($filtro, true, $datos['sucursal']->id);
    $item1 = $reporte1->firstWhere('id', $orden->id);
    expect($item1['estado_orden'])->toBe('NC Aprobada-Abierta');

    // 2. Con número de transferencia: NC Aprobada-Cerrada
    $orden->transferencia_plataforma = 'MBA3';
    $orden->transferencia_numero = '987654321';
    $orden->save();

    // Limpiar caché de relaciones cargadas si es necesario
    $orden->unsetRelations();

    $reporte2 = $repo->filtrarParaReporte($filtro, true, $datos['sucursal']->id);
    $item2 = $reporte2->firstWhere('id', $orden->id);
    expect($item2['estado_orden'])->toBe('NC Aprobada-Cerrada');
});
