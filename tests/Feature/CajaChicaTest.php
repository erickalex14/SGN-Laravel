<?php

use App\Models\Identity\Usuario;
use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Crear sucursal de prueba
    $this->sucursal = Sucursal::create([
        'nro_sucursal' => 1234,
        'ciudad' => 'Quito',
        'secuencial' => 'UIO',
        'nro_base' => '022999999',
    ]);

    // Crear grupo
    $this->grupo = GrupoAcceso::create([
        'nombre' => 'Grupo Test Contabilidad',
        'descripcion' => 'Prueba',
        'es_superadmin' => 1,
    ]);

    // Crear usuario
    $this->usuario = new Usuario();
    $this->usuario->usuario = 'custodio1';
    $this->usuario->clave = '';
    $this->usuario->clave_hash = bcrypt('password123');
    $this->usuario->nombre_tecnico = 'Maria Custodia';
    $this->usuario->telefono = '0999999999';
    $this->usuario->rol_id = 3; // Superadmin
    $this->usuario->grupo_id = $this->grupo->id;
    $this->usuario->sucursal_id = $this->sucursal->id;
    $this->usuario->activo = 1;
    $this->usuario->save();
});

test('redireccion a login si no esta autenticado', function () {
    $response = $this->get(route('cajachica.index'));
    $response->assertRedirect(route('login'));
});

test('usuario autenticado accede a modulo caja chica y recibe JWT valido', function () {
    $response = $this->actingAs($this->usuario)
        ->withSession([
            'usuario' => $this->usuario->usuario,
            'tecnico_id' => $this->usuario->id,
            'sucursal_id' => $this->usuario->sucursal_id,
        ])
        ->get(route('cajachica.index'));

    $response->assertStatus(200);
    $response->assertViewHas('token');
    $response->assertViewHas('apiUrl');

    $token = $response->viewData('token');
    
    // Decodificar JWT
    $parts = explode('.', $token);
    expect($parts)->toHaveCount(3);

    $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
    $payload = json_decode($payloadJson, true);

    expect($payload)->toBeArray();
    expect($payload['nameid'])->toBe((string) $this->usuario->id);
    expect($payload['unique_name'])->toBe('custodio1');
    expect($payload['sucursal_id'])->toBe((string) $this->usuario->sucursal_id);
    expect($payload['rol_id'])->toBe((string) $this->usuario->rol_id);
    expect($payload['es_superadmin'])->toBe('true');
});
