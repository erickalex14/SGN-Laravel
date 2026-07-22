<?php

use App\Models\Identity\Usuario;
use App\Models\Directory\Sucursal;
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
    $response->assertViewHas('ordenesEfectivo');
    $response->assertViewHas('arqueos');
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
