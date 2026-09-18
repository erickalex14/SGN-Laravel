<?php

use App\Services\Facturacion\FacturacionClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('cliente de facturacion envia idempotencia y trazabilidad a la api local', function () {
    config(['facturacion.base_url' => 'http://127.0.0.1:5080']);
    Http::fake([
        'http://127.0.0.1:5080/api/facturas' => Http::response([
            'invoiceId' => '13bfb48d-8f2d-4e2e-94f4-c28809235408',
            'status' => 'QUEUED',
        ], 201),
    ]);
    $payload = ['externalReference' => 'SGN-CG-TEST'];

    $result = app(FacturacionClient::class)->create(
        $payload,
        'd6a02c10-27ee-4236-914b-3769233b4cb6',
        'ff40656d-3481-41d6-b81c-29eff74743a4'
    );

    expect($result['status'])->toBe('QUEUED');
    Http::assertSent(function (Request $request) use ($payload) {
        return $request->url() === 'http://127.0.0.1:5080/api/facturas'
            && $request['externalReference'] === $payload['externalReference']
            && $request->hasHeader('Idempotency-Key', 'SGN-CG-TEST')
            && $request->hasHeader('X-Request-Id', 'd6a02c10-27ee-4236-914b-3769233b4cb6')
            && $request->hasHeader('X-Correlation-Id', 'ff40656d-3481-41d6-b81c-29eff74743a4');
    });
});
