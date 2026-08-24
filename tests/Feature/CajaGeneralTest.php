<?php

use App\Models\Identity\Usuario;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Operations\Orden;
use App\Models\Directory\Cliente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Facturacion\InvoicePayloadFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

test('payload selecciona establecimiento fiscal desde ciudad de sucursal', function (string $city, string $code) {
    $branchId = DB::table('sucursales')->insertGetId([
        'nro_sucursal' => random_int(100000, 999999999),
        'ciudad' => $city,
        'secuencial' => 'TEST-' . random_int(1000, 9999),
        'nro_base' => '000000000',
    ]);
    $collectionId = DB::table('caja_general_cobros')->insertGetId([
        'nro_orden' => 'FACT-TEST-' . random_int(1000, 9999),
        'monto_cobrado' => 11.50,
        'metodo_pago' => 'Efectivo',
        'destino_cuenta' => 'Caja General',
        'sucursal_id' => $branchId,
        'usuario_id' => 1,
        'usuario_nombre' => 'Prueba',
        'fecha_cobro' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = app(InvoicePayloadFactory::class)->fromCashCollection(
        $collectionId,
        (object) ['id' => 1, 'usuario' => 'prueba', 'rol_id' => 1]
    );

    expect($payload['source']['establishmentCode'])->toBe($code);
})->with([
    ['Quito', '002'],
    ['Novitec Quito', '002'],
    ['Guayaquil', '003'],
    ['Novitec Guayaquil', '003'],
    ['Manta', '004'],
    ['Novitec Manta', '004'],
]);

beforeEach(function () {
    $this->sucursal = Sucursal::firstOrCreate(
        ['id' => 1],
        [
            'nro_sucursal' => 10,
            'ciudad' => 'Quito',
            'secuencial' => 'ACC30',
            'nro_base' => '022999999',
        ]
    );

    $this->grupoSuper = GrupoAcceso::create([
        'nombre' => 'Superadmin Grupo Test ' . rand(100, 999),
        'descripcion' => 'Prueba',
        'es_superadmin' => 1,
    ]);

    $u = new Usuario();
    $u->usuario = 'ucg' . rand(100, 999);
    $u->clave = '';
    $u->clave_hash = bcrypt('password123');
    $u->nombre_tecnico = 'Usuario Prueba Caja';
    $u->rol_id = 3;
    $u->grupo_id = $this->grupoSuper->id;
    $u->sucursal_id = $this->sucursal->id;
    $u->activo = 1;
    $u->save();

    $this->usuario = $u;
});

test('usuario autenticado puede ver el modulo de caja general', function () {
    $response = $this->actingAs($this->usuario)
        ->withSession(['tecnico_id' => $this->usuario->id, 'sucursal_id' => $this->sucursal->id])
        ->get(route('cajageneral.index'));
    
    $response->assertStatus(200);
    $response->assertViewHas('cobrosEfectivo');
    $response->assertViewHas('cobrosBancos');
    $response->assertViewHas('arqueos');
});

test('usuario puede buscar ordenes de cliente externo', function () {
    $cliente = Cliente::create([
        'nombres' => 'Juan Pedro',
        'apellidos' => 'Perez ' . rand(100, 999),
        'identificacion' => '1712345' . rand(10, 99),
        'numero_contacto' => '0999999999',
    ]);

    $orden = Orden::create([
        'nro_orden' => 'OT-TEST-' . rand(1000, 9999),
        'cliente_id' => $cliente->id,
        'equipo_id' => 1,
        'tecnico_id' => $this->usuario->id,
        'ingresado_por' => $this->usuario->id,
        'sucursal_id' => $this->sucursal->id,
        'estado_orden' => 'Entregada',
        'fecha_de_ingreso' => now()->format('Y-m-d H:i:s'),
    ]);

    $response = $this->actingAs($this->usuario)
        ->get(route('cajageneral.buscar_orden', ['q' => $orden->nro_orden]));

    $response->assertStatus(200);
    $response->assertJson(['ok' => true]);
    $response->assertJsonFragment(['nro_orden' => $orden->nro_orden]);
    $response->assertJsonPath('ordenes.0.total_sugerido', 0);
});

test('usuario puede registrar cobro manual de cliente externo en caja general o bancos con desglose de vuelto', function () {
    $response = $this->actingAs($this->usuario)
        ->postJson(route('cajageneral.guardar_cobro'), [
            'nro_orden' => 'OT-TEST-5544',
            'cliente_nombre' => 'Carlos Client',
            'monto_cobrado' => 45.00,
            'monto_recibido' => 50.00,
            'vuelto_dado' => 5.00,
            'metodo_pago' => 'Efectivo',
            'observaciones' => 'Pago en efectivo recepcion con billete de 50',
        ]);

    $response->assertStatus(200);
    $response->assertJson(['ok' => true]);

    $this->assertDatabaseHas('caja_general_cobros', [
        'nro_orden' => 'OT-TEST-5544',
        'monto_cobrado' => 45.00,
        'monto_recibido' => 50.00,
        'vuelto_dado' => 5.00,
        'monto_neto_caja' => 45.00,
        'metodo_pago' => 'Efectivo',
        'destino_cuenta' => 'Caja General',
    ]);
});

test('cobrar una orden crea y despacha una factura automatica idempotente', function () {
    $cliente = Cliente::create([
        'nombres' => 'Cliente',
        'apellidos' => 'Factura Automatica',
        'identificacion' => '17' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'numero_contacto' => '0999999999',
        'correo' => 'cliente.factura@example.com',
        'direccion_clientes' => 'Av. Siempre Viva 123',
    ]);
    $orden = Orden::create([
        'nro_orden' => 'OT-AUTO-' . rand(1000, 9999),
        'cliente_id' => $cliente->id,
        'equipo_id' => 1,
        'tecnico_id' => $this->usuario->id,
        'ingresado_por' => $this->usuario->id,
        'sucursal_id' => $this->sucursal->id,
        'motivo_ingreso' => 'Servicio Cliente Externo',
        'estado_orden' => 'Entregada',
        'fecha_de_ingreso' => now()->format('Y-m-d H:i:s'),
    ]);
    $invoiceId = (string) Str::uuid();
    Http::fake(['*/api/facturas' => Http::response(['invoiceId' => $invoiceId, 'status' => 'QUEUED'], 202)]);

    $response = $this->actingAs($this->usuario)->postJson(route('cajageneral.guardar_cobro'), [
        'tipo_cobro' => 'orden',
        'orden_id' => $orden->id,
        'nro_orden' => $orden->nro_orden,
        'monto_cobrado' => 57.50,
        'pagos' => [[
            'metodo_pago' => 'Efectivo',
            'monto_cobrado' => 57.50,
            'monto_recibido' => 60,
            'vuelto_dado' => 2.50,
        ]],
    ]);

    $response->assertOk()->assertJsonPath('facturacion.status', 'QUEUED')
        ->assertJsonPath('facturacion.invoice_id', $invoiceId);
    $this->assertDatabaseHas('facturacion_sgn_links', [
        'source_type' => 'CAJA_GENERAL',
        'invoice_id' => $invoiceId,
        'status' => 'QUEUED',
        'attempt_count' => 1,
    ]);
    Http::assertSent(fn ($request) => $request['lines'][0]['unitPrice'] === 50
        && $request['payments'][0]['amount'] === 57.50
        && $request['buyer']['address'] === 'Av. Siempre Viva 123'
        && $request['buyer']['email'] === 'cliente.factura@example.com'
        && $request['buyer']['phone'] === '0999999999');
});

test('caja rechaza ordenes de garantia', function () {
    $orden = Orden::create([
        'nro_orden' => 'OT-GAR-' . rand(1000, 9999),
        'cliente_id' => 1,
        'equipo_id' => 1,
        'tecnico_id' => $this->usuario->id,
        'ingresado_por' => $this->usuario->id,
        'sucursal_id' => $this->sucursal->id,
        'motivo_ingreso' => 'Validacion de Garantia',
        'estado_orden' => 'Entregada',
        'fecha_de_ingreso' => now()->format('Y-m-d H:i:s'),
    ]);
    Http::fake();

    $response = $this->actingAs($this->usuario)->postJson(route('cajageneral.guardar_cobro'), [
        'tipo_cobro' => 'orden',
        'orden_id' => $orden->id,
        'monto_cobrado' => 57.50,
        'pagos' => [['metodo_pago' => 'Efectivo', 'monto_cobrado' => 57.50]],
    ]);

    $response->assertStatus(422)->assertJsonPath('error',
        'Caja General sólo permite cobrar órdenes B2C de Servicio Cliente Externo.');
    Http::assertNothingSent();
});

test('listado de facturas muestra trazabilidad completa del cobro y la orden', function () {
    $cliente = Cliente::create([
        'nombres' => 'Cliente',
        'apellidos' => 'Trazable',
        'identificacion' => '17' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'numero_contacto' => '0999999999',
    ]);
    $orden = Orden::create([
        'nro_orden' => 'UIO-TRACE-' . rand(1000, 9999),
        'cliente_id' => $cliente->id,
        'equipo_id' => 1,
        'tecnico_id' => $this->usuario->id,
        'ingresado_por' => $this->usuario->id,
        'sucursal_id' => $this->sucursal->id,
        'motivo_ingreso' => 'Servicio Cliente Externo',
        'estado_orden' => 'Entregada',
        'fecha_de_ingreso' => now()->format('Y-m-d H:i:s'),
    ]);
    $informe = \App\Models\Operations\Informe::create([
        'orden_id' => $orden->id,
        'tecnico_id' => $this->usuario->id,
        'antecedentes' => 'Prueba',
        'proceso' => 'Revisión',
        'conclusion' => 'Operativo',
        'fecha_informe' => now()->toDateString(),
        'fecha_creacion' => now(),
    ]);
    $group = (string) Str::uuid();
    $chargedAt = now()->setTime(14, 35);
    $collectionId = DB::table('caja_general_cobros')->insertGetId([
        'grupo_cobro_uuid' => $group,
        'orden_id' => $orden->id,
        'nro_orden' => $orden->nro_orden,
        'cliente_nombre' => 'Cliente Trazable',
        'monto_cobrado' => 62.10,
        'metodo_pago' => 'Transferencia',
        'destino_cuenta' => 'Bancos',
        'sucursal_id' => $this->sucursal->id,
        'usuario_id' => $this->usuario->id,
        'usuario_nombre' => 'CAJERO TRAZABLE',
        'fecha_cobro' => $chargedAt,
        'created_at' => $chargedAt,
        'updated_at' => $chargedAt,
    ]);
    $invoiceId = (string) Str::uuid();
    DB::table('facturacion_sgn_links')->insert([
        'source_type' => 'CAJA_GENERAL',
        'source_key' => 'COBRO|' . $group,
        'source_id' => $collectionId,
        'external_reference' => 'SGN-CG-TRACE',
        'invoice_id' => $invoiceId,
        'status' => 'AUTHORIZED',
        'attempt_count' => 1,
        'request_id' => (string) Str::uuid(),
        'correlation_id' => (string) Str::uuid(),
        'requested_by_id' => $this->usuario->id,
        'requested_by_name' => 'CAJERO TRAZABLE',
        'request_payload' => '{}',
        'requested_at' => $chargedAt,
        'responded_at' => $chargedAt,
        'created_at' => $chargedAt,
        'updated_at' => $chargedAt,
    ]);
    Http::fake(['*/api/facturas*' => Http::response([
        'items' => [[
            'id' => $invoiceId,
            'externalReference' => 'SGN-CG-TRACE',
            'buyerName' => 'Cliente Trazable',
            'buyerIdentification' => '1712345678',
            'issueDate' => now()->toDateString(),
            'status' => 'AUTHORIZED',
            'grandTotal' => 62.10,
            'sequenceNumber' => 7,
        ]],
        'page' => 1,
        'pageSize' => 20,
        'totalItems' => 1,
        'totalPages' => 1,
    ])]);

    $response = $this->actingAs($this->usuario)
        ->withSession(['es_superadmin' => true])
        ->get(route('facturas.index'));

    $response->assertOk()
        ->assertSee($orden->nro_orden)
        ->assertSee('Usuario Prueba Caja')
        ->assertSee('CAJERO TRAZABLE')
        ->assertSee('Transferencia')
        ->assertSee('$62.10')
        ->assertSee(route('ordenes.imprimir', $orden->id), false)
        ->assertSee(route('informes.imprimir', $informe->id), false);
});

test('usuario puede registrar arqueo ciego diario en caja general', function () {
    $response = $this->actingAs($this->usuario)
        ->withSession(['tecnico_id' => $this->usuario->id, 'sucursal_id' => $this->sucursal->id])
        ->postJson(route('cajageneral.guardar_arqueo'), [
            'sucursal_id' => $this->sucursal->id,
            'codigo_sucursal' => 'ACC30',
            'monto_sistema' => 150.00,
            'monto_fisico' => 150.00,
            'observaciones' => 'Arqueo de prueba sin diferencias',
        ]);

    $response->assertStatus(200);
    $response->assertJson(['ok' => true]);

    $this->assertDatabaseHas('caja_general_arqueo', [
        'sucursal_id' => $this->sucursal->id,
        'monto_sistema' => 150.00,
        'monto_fisico' => 150.00,
        'tipo_diferencia' => 'Cuadre Exacto',
    ]);
});

test('usuario puede visualizar e imprimir comprobante de arqueo ciego', function () {
    $arqueoId = \Illuminate\Support\Facades\DB::table('caja_general_arqueo')->insertGetId([
        'sucursal_id' => $this->sucursal->id,
        'codigo_sucursal' => 'ACC30',
        'fecha' => now(),
        'monto_sistema' => 100.00,
        'monto_fisico' => 100.00,
        'diferencia' => 0.00,
        'tipo_diferencia' => 'Cuadre Exacto',
        'usuario_id' => $this->usuario->id,
        'usuario_nombre' => $this->usuario->nombre_tecnico,
        'estado' => 'Pendiente Deposito',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($this->usuario)
        ->get(route('cajageneral.imprimir_arqueo', $arqueoId));

    $response->assertStatus(200);
    $response->assertSee('ARQUEO CIEGO');
});
