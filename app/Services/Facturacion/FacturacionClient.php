<?php

namespace App\Services\Facturacion;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FacturacionClient
{
    public function create(array $payload, string $requestId, string $correlationId): array
    {
        return $this->request()
            ->withHeaders([
                'Idempotency-Key' => $payload['externalReference'],
                'X-Request-Id' => $requestId,
                'X-Correlation-Id' => $correlationId,
            ])
            ->post('/api/facturas', $payload)
            ->throw()
            ->json();
    }

    public function list(array $filters = []): array
    {
        return $this->request()->get('/api/facturas', $filters)->throw()->json();
    }

    public function detail(string $invoiceId): array
    {
        return $this->request()->get("/api/facturas/{$invoiceId}")->throw()->json();
    }

    public function xml(string $invoiceId): Response
    {
        return $this->request()->get("/api/facturas/{$invoiceId}/xml", ['type' => 'authorized']);
    }

    public function ride(string $invoiceId): Response
    {
        return $this->request()->get("/api/facturas/{$invoiceId}/ride");
    }

    private function request()
    {
        return Http::baseUrl(rtrim((string) config('facturacion.base_url'), '/'))
            ->acceptJson()
            ->timeout((int) config('facturacion.timeout'));
    }
}
