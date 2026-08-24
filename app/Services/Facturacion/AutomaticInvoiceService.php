<?php

namespace App\Services\Facturacion;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AutomaticInvoiceService
{
    public function __construct(private readonly FacturacionClient $client) {}

    public function createIntent(int $collectionId, array $payload, object $user): int
    {
        $identity = ['source_type' => 'CAJA_GENERAL', 'source_key' => $payload['source']['id']];
        $existing = DB::table('facturacion_sgn_links')->where($identity)->first();
        if ($existing) {
            return (int) $existing->id;
        }

        $now = now();
        DB::table('facturacion_sgn_links')->insertOrIgnore($identity + [
            'source_id' => $collectionId,
            'external_reference' => $payload['externalReference'],
            'status' => 'PENDING_DISPATCH',
            'attempt_count' => 0,
            'request_id' => (string) Str::uuid(),
            'correlation_id' => (string) Str::uuid(),
            'requested_by_id' => $user->id,
            'requested_by_name' => $user->nombre_tecnico ?? $user->usuario ?? 'Usuario',
            'request_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'requested_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) DB::table('facturacion_sgn_links')->where($identity)->value('id');
    }

    public function dispatch(int $linkId): array
    {
        $link = DB::table('facturacion_sgn_links')->where('id', $linkId)->first();
        if (!$link) {
            return ['status' => 'ERROR', 'invoice_id' => null, 'error' => 'Intención de factura no encontrada.'];
        }
        if (!empty($link->invoice_id)) {
            return ['status' => $link->status, 'invoice_id' => $link->invoice_id, 'error' => $link->last_error];
        }

        DB::table('facturacion_sgn_links')->where('id', $linkId)->update([
            'status' => 'REQUESTING',
            'attempt_count' => DB::raw('attempt_count + 1'),
            'last_error' => null,
            'updated_at' => now(),
        ]);

        try {
            $result = $this->client->create(
                json_decode($link->request_payload, true, flags: JSON_THROW_ON_ERROR),
                $link->request_id,
                $link->correlation_id
            );
            DB::table('facturacion_sgn_links')->where('id', $linkId)->update([
                'invoice_id' => $result['invoiceId'],
                'status' => $result['status'],
                'response_payload' => json_encode($result, JSON_UNESCAPED_UNICODE),
                'responded_at' => now(),
                'updated_at' => now(),
            ]);
            return ['status' => $result['status'], 'invoice_id' => $result['invoiceId'], 'error' => null];
        } catch (Throwable $exception) {
            $permanent = $exception instanceof RequestException
                && $exception->response
                && $exception->response->status() >= 400
                && $exception->response->status() < 500;
            $message = $this->message($exception);
            $status = $permanent ? 'ERROR' : 'RETRY_PENDING';
            DB::table('facturacion_sgn_links')->where('id', $linkId)->update([
                'status' => $status,
                'response_payload' => $exception instanceof RequestException ? $exception->response?->body() : null,
                'last_error' => $message,
                'responded_at' => now(),
                'updated_at' => now(),
            ]);
            return ['status' => $status, 'invoice_id' => null, 'error' => $message];
        }
    }

    public function processPending(int $limit = 50): array
    {
        $dispatched = 0;
        $synced = 0;
        $links = DB::table('facturacion_sgn_links')
            ->where('attempt_count', '<', 10)
            ->whereIn('status', ['PENDING_DISPATCH', 'RETRY_PENDING'])
            ->orderBy('id')->limit($limit)->get();
        foreach ($links as $link) {
            $this->dispatch((int) $link->id);
            $dispatched++;
        }

        $active = DB::table('facturacion_sgn_links')
            ->whereNotNull('invoice_id')
            ->whereNotIn('status', ['AUTHORIZED', 'REJECTED', 'NOT_AUTHORIZED', 'ERROR'])
            ->orderBy('id')->limit($limit)->get();
        foreach ($active as $link) {
            try {
                $result = $this->client->detail($link->invoice_id);
                DB::table('facturacion_sgn_links')->where('id', $link->id)->update([
                    'status' => $result['status'],
                    'response_payload' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'last_error' => $result['lastError'] ?? null,
                    'updated_at' => now(),
                ]);
                $synced++;
            } catch (Throwable) {
                // El siguiente ciclo vuelve a intentar sin alterar el estado conocido.
            }
        }

        return compact('dispatched', 'synced');
    }

    private function message(Throwable $exception): string
    {
        if ($exception instanceof RequestException && $exception->response) {
            $body = $exception->response->json();
            return $body['detail'] ?? $body['title'] ?? 'La API de facturación rechazó la solicitud.';
        }
        return 'No fue posible comunicarse con la API local de facturación: ' . $exception->getMessage();
    }
}
