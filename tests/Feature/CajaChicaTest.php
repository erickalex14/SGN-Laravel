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

    // Crear grupo superadmin
    $this->grupoSuper = GrupoAcceso::create([
        'nombre' => 'Superadmin Grupo',
        'descripcion' => 'Prueba',
        'es_superadmin' => 1,
    ]);

    // Crear grupo regular
    $this->grupoRegular = GrupoAcceso::create([
        'nombre' => 'Tecnico Grupo',
        'descripcion' => 'Prueba Regular',
        'es_superadmin' => 0,
    ]);

    // Crear usuario admin (Superadmin)
    $this->adminUsuario = new Usuario();
    $this->adminUsuario->usuario = 'admin1';
    $this->adminUsuario->clave = '';
    $this->adminUsuario->clave_hash = bcrypt('password123');
    $this->adminUsuario->nombre_tecnico = 'Maria Administradora';
    $this->adminUsuario->telefono = '0999999999';
    $this->adminUsuario->rol_id = 3; // Superadmin
    $this->adminUsuario->grupo_id = $this->grupoSuper->id;
    $this->adminUsuario->sucursal_id = $this->sucursal->id;
    $this->adminUsuario->activo = 1;
    $this->adminUsuario->save();

    // Crear usuario regular (Tecnico/Custodio)
    $this->regularUsuario = new Usuario();
    $this->regularUsuario->usuario = 'tecnico1';
    $this->regularUsuario->clave = '';
    $this->regularUsuario->clave_hash = bcrypt('password123');
    $this->regularUsuario->nombre_tecnico = 'Juan Tecnico';
    $this->regularUsuario->telefono = '0988888888';
    $this->regularUsuario->rol_id = 4; // Tecnico
    $this->regularUsuario->grupo_id = $this->grupoRegular->id;
    $this->regularUsuario->sucursal_id = $this->sucursal->id;
    $this->regularUsuario->activo = 1;
    $this->regularUsuario->save();
});

test('redireccion a login si no esta autenticado', function () {
    $response = $this->get(route('cajachica.gestion'));
    $response->assertRedirect(route('login'));
});

test('index general redirige a gestion', function () {
    $response = $this->actingAs($this->adminUsuario)
        ->get(route('cajachica.index'));
    
    $response->assertRedirect(route('cajachica.gestion'));
});

test('usuario regular no puede entrar al panel de administracion', function () {
    $response = $this->actingAs($this->regularUsuario)
        ->get(route('cajachica.admin'));
    
    $response->assertStatus(403);
});

test('administrador puede entrar al panel de administracion y recibe JWT valido', function () {
    $response = $this->actingAs($this->adminUsuario)
        ->get(route('cajachica.admin'));
    
    $response->assertStatus(200);
    $response->assertViewHas('token');
    $response->assertViewHas('usuarios');
    $response->assertViewHas('sucursales');
});

test('custodio accede a gestion y recibe variables del frontend', function () {
    $response = $this->actingAs($this->regularUsuario)
        ->get(route('cajachica.gestion'));

    $response->assertStatus(200);
    $response->assertViewHas('token');
    $response->assertViewHas('apiUrl');

    $token = $response->viewData('token');
    $parts = explode('.', $token);
    expect($parts)->toHaveCount(3);

    $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
    $payload = json_decode($payloadJson, true);

    expect($payload['nameid'])->toBe((string) $this->regularUsuario->id);
    expect($payload['unique_name'])->toBe('tecnico1');
    expect($payload['es_superadmin'])->toBe('false');
});
