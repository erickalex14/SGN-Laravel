<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Facturacion\FacturacionClient;
use App\Services\Facturacion\InvoicePayloadFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class FacturaController extends Controller
{
    public function __construct(
        private readonly FacturacionClient $client,
        private readonly InvoicePayloadFactory $payloads
    ) {}

    public function index(Request $request)
    {
        $this->ensureAccess();
        try {
            $result = $this->client->list($request->only(['page', 'pageSize', 'status', 'search']));
            $error = null;
        } catch (Throwable $exception) {
            $result = ['items' => [], 'page' => 1, 'pageSize' => 20, 'totalItems' => 0, 'totalPages' => 0];
            $error = $this->message($exception);
        }

        return view('accounting.facturas.index', compact('result', 'error'));
    }

    public function show(string $invoiceId)
    {
        $this->ensureAccess();
        try {
            return view('accounting.facturas.show', [
                'invoice' => $this->client->detail($invoiceId),
            ]);
        } catch (Throwable $exception) {
            return redirect()->route('facturas.index')->with('error', $this->message($exception));
        }
    }

    public function issueCash(int $collectionId)
    {
        $this->ensureAccess();
        return $this->issue('CAJA_GENERAL', $collectionId,
            fn () => $this->payloads->fromCashCollection($collectionId, auth()->user()));
    }

    public function issueB2b(int $batchId)
    {
        $this->ensureAccess();
        return $this->issue('RECUENTO_B2B', $batchId,
            fn () => $this->payloads->fromB2bBatch($batchId, auth()->user()));
    }

    public function xml(string $invoiceId)
    {
        $this->ensureAccess();
        try {
            return $this->document($this->client->xml($invoiceId), "factura-{$invoiceId}.xml");
        } catch (Throwable $exception) {
            return back()->with('error', $this->message($exception));
        }
    }

    public function ride(string $invoiceId)
    {
        $this->ensureAccess();
        try {
            return $this->document($this->client->ride($invoiceId), "RIDE-{$invoiceId}.pdf");
        } catch (Throwable $exception) {
            return back()->with('error', $this->message($exception));
        }
    }

    private function issue(string $sourceType, int $sourceId, callable $payloadFactory)
    {
        $payload = $payloadFactory();
        $sourceKey = $payload['source']['id'];
        $requestId = (string) Str::uuid();
        $correlationId = (string) Str::uuid();
        $user = auth()->user();
        $now = now();

        $identity = ['source_type' => $sourceType, 'source_key' => $sourceKey];
        $values = [
            'source_id' => $sourceId,
            'external_reference' => $payload['externalReference'],
            'status' => 'REQUESTING',
            'request_id' => $requestId,
            'correlation_id' => $correlationId,
            'requested_by_id' => $user->id,
            'requested_by_name' => $user->nombre_tecnico ?? $user->usuario ?? 'Usuario',
            'request_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'response_payload' => null,
            'last_error' => null,
            'requested_at' => $now,
            'responded_at' => null,
            'updated_at' => $now,
        ];
        if (DB::table('facturacion_sgn_links')->where($identity)->exists()) {
            DB::table('facturacion_sgn_links')->where($identity)->update($values);
        } else {
            DB::table('facturacion_sgn_links')->insert($identity + $values + ['created_at' => $now]);
        }
        DB::table('facturacion_sgn_links')
            ->where($identity)
            ->increment('attempt_count');

        try {
            $result = $this->client->create($payload, $requestId, $correlationId);
            DB::table('facturacion_sgn_links')
                ->where($identity)
                ->update([
                    'invoice_id' => $result['invoiceId'],
                    'status' => $result['status'],
                    'response_payload' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);

            return redirect()->route('facturas.show', $result['invoiceId'])
                ->with('success', 'Factura enviada al flujo electrónico en ambiente de pruebas.');
        } catch (Throwable $exception) {
            $message = $this->message($exception);
            DB::table('facturacion_sgn_links')
                ->where($identity)
                ->update([
                    'status' => 'ERROR',
                    'response_payload' => $exception instanceof RequestException
                        ? $exception->response?->body() : null,
                    'last_error' => $message,
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);
            return back()->with('error', $message);
        }
    }

    private function document($upstream, string $fallbackName)
    {
        if (!$upstream->successful()) {
            return back()->with('error', 'El documento aún no está disponible. Estado API: '
                . $upstream->status());
        }
        $contentDisposition = $upstream->header('Content-Disposition')
            ?: 'attachment; filename="' . $fallbackName . '"';
        return response($upstream->body(), 200)
            ->header('Content-Type', $upstream->header('Content-Type') ?: 'application/octet-stream')
            ->header('Content-Disposition', $contentDisposition);
    }

    private function ensureAccess(): void
    {
        $user = auth()->user();
        abort_if(!$user, 401);
        $permissions = session('permisos', []);
        $allowed = session('es_superadmin')
            || (bool) ($user->grupo->es_superadmin ?? false)
            || !empty($permissions['caja_general']['ver'])
            || !empty($permissions['recuento_b2b']['ver']);
        abort_unless($allowed, 403, 'Acceso denegado al módulo de facturación.');
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
