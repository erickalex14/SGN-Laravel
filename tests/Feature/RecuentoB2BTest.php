<?php

use App\Models\Identity\Usuario;
use App\Models\Directory\Sucursal;
use App\Models\Directory\Empresa;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Identity\GrupoAcceso;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

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
    $u->usuario = 'ub2b' . rand(100, 999);
    $u->clave = '';
    $u->clave_hash = bcrypt('password123');
    $u->nombre_tecnico = 'Usuario Prueba B2B';
    $u->rol_id = 3;
    $u->grupo_id = $this->grupoSuper->id;
    $u->sucursal_id = $this->sucursal->id;
    $u->activo = 1;
    $u->save();

    $this->usuario = $u;
});

test('usuario autenticado puede ver la bandeja de recuento b2b', function () {
    $response = $this->actingAs($this->usuario)
        ->withSession(['tecnico_id' => $this->usuario->id, 'sucursal_id' => $this->sucursal->id])
        ->get(route('recuentob2b.index'));
    
    $response->assertStatus(200);
    $response->assertViewHas('ordenes');
    $response->assertViewHas('lotesProcesados');
});

test('usuario puede procesar recuento b2b con seleccion de ordenes', function () {
    $empresa = Empresa::create([
        'nombre' => 'Novicompu Pruebas ' . rand(100, 999),
        'ruc' => '179200000' . rand(100, 999),
    ]);

    $orden = OrdenEmpresa::create([
        'nro_orden' => 'OT-EMP-TEST-' . rand(1000, 9999),
        'empresa_id' => $empresa->id,
        'subtipo' => 'Servicios',
        'estado' => 'Finalizada',
        'horas_trabajadas' => 2.0,
        'tecnico_id' => $this->usuario->id,
        'ingresado_por' => $this->usuario->id,
        'sucursal_id' => $this->sucursal->id,
    ]);

    $response = $this->actingAs($this->usuario)
        ->withSession(['tecnico_id' => $this->usuario->id, 'sucursal_id' => $this->sucursal->id])
        ->postJson(route('recuentob2b.procesar'), [
            'empresa_nombre' => $empresa->nombre,
            'monto_neto_banco' => 50.00,
            'monto_retencion_renta' => 1.75,
            'monto_retencion_iva' => 0.0,
            'nro_retencion' => '001-002-0000123',
            'nro_comprobante_pago' => 'TRF-TEST-123',
            'banco_destino' => 'Banco Pichincha',
            'ordenes' => [
                [
                    'id' => $orden->id,
                    'nro_orden' => $orden->nro_orden,
                    'subtipo' => 'Servicios',
                    'tecnico' => 'Juan Perez',
                    'tecnicos_count' => 1,
                    'horas' => 2.0,
                    'tarifa' => 25.0,
                    'valor_total' => 50.00,
                ]
            ]
        ]);

    $response->assertStatus(200);
    $response->assertJson(['ok' => true]);

    $this->assertDatabaseHas('recuento_b2b_lote', [
        'empresa_nombre' => $empresa->nombre,
        'total_ordenes' => 1,
        'subtotal' => 50.00,
    ]);

    $orden->refresh();
    expect($orden->estado_facturacion)->toBe('Cobrado');
});
