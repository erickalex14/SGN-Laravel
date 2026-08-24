<?php

use App\Models\Operations\Equipo;
use App\Models\Operations\Informe;
use App\Models\Operations\Orden;
use App\Models\Operations\PrecioOrden;
use App\Services\Facturacion\OrderBillingCalculator;
use Tests\TestCase;

uses(TestCase::class);

test('calcula subtotal iva total y descripcion fiscal de una orden', function () {
    $equipment = new Equipo(['tipo' => 'Laptop', 'marca' => 'Dell', 'modelo' => 'Latitude', 'serie' => 'SN-123']);
    $equipment->setRelation('series', collect());
    $order = new Orden(['nro_orden' => 'UIO-TEST', 'motivo_ingreso' => 'Servicio Cliente Externo']);
    $order->setRelation('equipo', $equipment);
    $order->setRelation('preciosOrden', collect([new PrecioOrden(['precio' => 72])]));
    $order->setRelation('informes', collect([new Informe(['proceso' => 'Cambio de teclado', 'conclusion' => 'Equipo operativo'])]));

    $result = app(OrderBillingCalculator::class)->calculate($order);

    expect($result['subtotal'])->toBe(100.0)
        ->and($result['tax'])->toBe(15.0)
        ->and($result['total'])->toBe(115.0)
        ->and($result['description'])->toContain('OT UIO-TEST', 'Laptop Dell Latitude', 'Cambio de teclado', 'Equipo operativo')
        ->and(mb_strlen($result['description']))->toBeLessThanOrEqual(300);
});

test('aplica descuento total a validacion de garantia no rechazada', function () {
    $order = new Orden(['nro_orden' => 'GAR-TEST', 'motivo_ingreso' => 'Validacion de Garantia']);
    $order->setRelation('equipo', null);
    $order->setRelation('preciosOrden', collect());
    $order->setRelation('informes', collect());

    $result = app(OrderBillingCalculator::class)->calculate($order);

    expect($result['subtotal'])->toBe(28.0)
        ->and($result['discount'])->toBe(28.0)
        ->and($result['tax'])->toBe(0.0)
        ->and($result['total'])->toBe(0.0);
});
