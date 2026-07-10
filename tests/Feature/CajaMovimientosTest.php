<?php

use App\Models\Directory\Sucursal;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Usuario;
use App\Models\Operations\Caja;
use App\Models\Operations\CajaMensualidad;
use App\Models\Operations\CajaMovimiento;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

uses(DatabaseTransactions::class);

function crearUsuarioHelperCaja(int $rolId, int $sucursalId, bool $esSuperadmin = false): Usuario
{
    $codigo = (string) random_int(1000, 9999);

    $grupo = GrupoAcceso::create([
        'nombre' => 'Grupo ' . $codigo,
        'descripcion' => 'Prueba',
        'es_superadmin' => $esSuperadmin ? 1 : 0,
    ]);

    $usuario = new Usuario;
    $usuario->usuario = '09' . random_int(10000000, 99999999);
    $usuario->clave = '';
    $usuario->clave_hash = bcrypt('password123');
    $usuario->nombre_tecnico = 'Usuario ' . $codigo;
    $usuario->telefono = '0999999999';
    $usuario->rol_id = $rolId;
    $usuario->grupo_id = $grupo->id;
    $usuario->sucursal_id = $sucursalId;
    $usuario->activo = 1;
    $usuario->save();

    return $usuario->fresh();
}

test('acceso a caja restringido a superadmin y admin de sucursal 1 (UIO)', function () {
    // 1. Superadmin tiene acceso
    $superadmin = crearUsuarioHelperCaja(1, 1, true);
    $response = $this->actingAs($superadmin)
        ->withSession([
            'tecnico_id' => $superadmin->id,
            'sucursal_id' => 1,
            'es_superadmin' => true,
            'grupo_nombre' => 'superadmin'
        ])
        ->get(route('caja.movimientos'));
    $response->assertStatus(200);

    // 2. Admin de sucursal 1 (UIO) tiene acceso
    $adminUio = crearUsuarioHelperCaja(2, 1, false);
    $response = $this->actingAs($adminUio)
        ->withSession([
            'tecnico_id' => $adminUio->id,
            'sucursal_id' => 1,
            'es_superadmin' => false,
            'grupo_nombre' => 'administrador'
        ])
        ->get(route('caja.movimientos'));
    $response->assertStatus(200);

    // 3. Admin de sucursal 2 (Guayaquil) NO tiene acceso
    $adminGye = crearUsuarioHelperCaja(2, 2, false);
    $response = $this->actingAs($adminGye)
        ->withSession([
            'tecnico_id' => $adminGye->id,
            'sucursal_id' => 2,
            'es_superadmin' => false,
            'grupo_nombre' => 'administrador'
        ])
        ->get(route('caja.movimientos'));
    $response->assertStatus(403);

    // 4. Técnico común de sucursal 1 (UIO) NO tiene acceso
    $tecnico = crearUsuarioHelperCaja(2, 1, false);
    $response = $this->actingAs($tecnico)
        ->withSession([
            'tecnico_id' => $tecnico->id,
            'sucursal_id' => 1,
            'es_superadmin' => false,
            'grupo_nombre' => 'tecnico'
        ])
        ->get(route('caja.movimientos'));
    $response->assertStatus(403);
});

test('aperturar mes crea mensualidad y recarga saldo en caja', function () {
    $superadmin = crearUsuarioHelperCaja(1, 1, true);

    $mes = 6;
    $anio = 2026;

    // Eliminar si ya existe de otra prueba
    CajaMensualidad::query()->delete();
    Caja::query()->delete();

    $response = $this->actingAs($superadmin)
        ->withSession([
            'tecnico_id' => $superadmin->id,
            'sucursal_id' => 1,
            'es_superadmin' => true,
        ])
        ->post(route('caja.apertura.store'), [
            'mes' => $mes,
            'anio' => $anio,
            'monto_ingreso_chica' => 250.00,
            'monto_ingreso_grande' => 1200.00,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $cajaChica = Caja::where('tipo', 'chica')->first();
    $cajaGrande = Caja::where('tipo', 'grande')->first();

    expect((float)$cajaChica->balance)->toBe(250.00);
    expect((float)$cajaGrande->balance)->toBe(1200.00);

    $this->assertDatabaseHas('cajas_mensualidades', [
        'caja_id' => $cajaChica->id,
        'mes' => $mes,
        'anio' => $anio,
        'monto_ingreso' => 250.00,
        'cerrado' => 0
    ]);
});

test('registrar egreso requiere saldo y justificante obligatorio', function () {
    Storage::fake('public');

    $superadmin = crearUsuarioHelperCaja(1, 1, true);
    
    CajaMensualidad::query()->delete();
    Caja::query()->delete();

    $cajaChica = Caja::create(['tipo' => 'chica', 'balance' => 100.00, 'sucursal_id' => 1]);

    CajaMensualidad::create([
        'caja_id' => $cajaChica->id,
        'mes' => 7,
        'anio' => 2026,
        'saldo_inicial' => 0,
        'monto_ingreso' => 100.00,
        'cerrado' => false
    ]);

    // 1. Egreso sin saldo suficiente
    $responseNoSaldo = $this->actingAs($superadmin)
        ->withSession([
            'tecnico_id' => $superadmin->id,
            'sucursal_id' => 1,
            'es_superadmin' => true,
        ])
        ->post(route('caja.movimiento.store'), [
            'caja_id' => $cajaChica->id,
            'tipo' => 'egreso',
            'monto' => 150.00,
            'descripcion' => 'Gasto excesivo',
            'fecha' => '2026-07-15',
        ]);
    $responseNoSaldo->assertSessionHas('error', 'Saldo insuficiente en la caja para registrar este egreso.');

    // 2. Egreso sin justificante 1
    $responseNoFile = $this->actingAs($superadmin)
        ->withSession([
            'tecnico_id' => $superadmin->id,
            'sucursal_id' => 1,
            'es_superadmin' => true,
        ])
        ->post(route('caja.movimiento.store'), [
            'caja_id' => $cajaChica->id,
            'tipo' => 'egreso',
            'monto' => 30.00,
            'descripcion' => 'Gasto normal',
            'fecha' => '2026-07-15',
        ]);
    $responseNoFile->assertSessionHas('error', 'El primer justificante es obligatorio para registrar egresos.');

    // 3. Egreso exitoso con justificante
    $file = UploadedFile::fake()->create('factura.pdf', 500);

    $responseOk = $this->actingAs($superadmin)
        ->withSession([
            'tecnico_id' => $superadmin->id,
            'sucursal_id' => 1,
            'es_superadmin' => true,
        ])
        ->post(route('caja.movimiento.store'), [
            'caja_id' => $cajaChica->id,
            'tipo' => 'egreso',
            'monto' => 30.00,
            'descripcion' => 'Gasto exitoso',
            'fecha' => '2026-07-15',
            'justificante_1' => $file
        ]);

    $responseOk->assertSessionHas('success');
    
    $cajaChica->refresh();
    expect((float)$cajaChica->balance)->toBe(70.00);
});
