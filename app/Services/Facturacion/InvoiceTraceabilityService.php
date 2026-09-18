<?php

namespace App\Services\Facturacion;

use App\Models\Operations\Orden;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceTraceabilityService
{
    public function attach(array $result): array
    {
        $items = collect($result['items'] ?? []);
        $invoiceIds = $items->pluck('id')->filter()->values();
        if ($invoiceIds->isEmpty()) {
            return $result;
        }

        $links = DB::table('facturacion_sgn_links')
            ->whereIn('invoice_id', $invoiceIds)
            ->get()
            ->keyBy(fn ($link) => strtolower((string) $link->invoice_id));

        $result['items'] = $items->map(function (array $invoice) use ($links) {
            $link = $links->get(strtolower((string) $invoice['id']));
            $invoice['traceability'] = $link ? $this->trace($link) : null;

            return $invoice;
        })->all();

        return $result;
    }

    private function trace(object $link): ?array
    {
        return match ($link->source_type) {
            'CAJA_GENERAL' => $this->cashTrace($link),
            'RECUENTO_B2B' => $this->b2bTrace($link),
            default => null,
        };
    }

    private function cashTrace(object $link): ?array
    {
        $anchor = DB::table('caja_general_cobros')->where('id', $link->source_id)->first();
        if (!$anchor) {
            return null;
        }

        $payments = !empty($anchor->grupo_cobro_uuid)
            ? DB::table('caja_general_cobros')
                ->where('grupo_cobro_uuid', $anchor->grupo_cobro_uuid)
                ->orderBy('id')->get()
            : collect([$anchor]);
        $orderIds = $payments->pluck('orden_id')->filter()->unique()->values();
        $orders = Orden::with(['tecnico', 'informes' => fn ($query) => $query->orderByDesc('id')])
            ->whereIn('id', $orderIds)->get()->keyBy('id');

        return [
            'source' => 'Caja General',
            'chargedAt' => $this->dateTime($payments->min('fecha_cobro')),
            'chargedBy' => $payments->pluck('usuario_nombre')->filter()->unique()->implode(', ')
                ?: $link->requested_by_name,
            'amount' => round((float) $payments->sum('monto_cobrado'), 2),
            'paymentMethods' => $payments->pluck('metodo_pago')->filter()->unique()->implode(' + '),
            'orders' => $payments->unique('orden_id')->map(function ($payment) use ($orders) {
                $order = $orders->get($payment->orden_id);
                $report = $order?->informes->first();

                return [
                    'number' => $order?->nro_orden ?? $payment->nro_orden,
                    'technician' => $order?->tecnico?->nombre_tecnico ?? 'No asignado',
                    'orderUrl' => $order ? route('ordenes.imprimir', $order->id) : null,
                    'reportUrl' => $report ? route('informes.imprimir', $report->id) : null,
                ];
            })->values()->all(),
        ];
    }

    private function b2bTrace(object $link): ?array
    {
        $batch = DB::table('recuento_b2b_lote')->where('id', $link->source_id)->first();
        if (!$batch) {
            return null;
        }

        $items = DB::table('recuento_b2b_item')->where('lote_id', $batch->id)->orderBy('id')->get();
        $personalIds = $items->where('tipo_orden', 'personal')->pluck('orden_id')->unique()->values();
        $personalOrders = Orden::with(['informes' => fn ($query) => $query->orderByDesc('id')])
            ->whereIn('id', $personalIds)->get()->keyBy('id');

        return [
            'source' => 'Recuento B2B',
            'chargedAt' => $this->dateTime($batch->created_at),
            'chargedBy' => $batch->usuario_nombre ?: $link->requested_by_name,
            'amount' => round((float) ($batch->total_con_iva ?? $batch->subtotal), 2),
            'paymentMethods' => trim('Transferencia ' . ($batch->banco_destino ?? '')),
            'orders' => $items->map(function ($item) use ($personalOrders) {
                $personal = $item->tipo_orden === 'personal';
                $order = $personal ? $personalOrders->get($item->orden_id) : null;
                $report = $order?->informes->first();

                return [
                    'number' => $item->nro_orden,
                    'technician' => $item->tecnico_nombre ?: 'No asignado',
                    'orderUrl' => route($personal ? 'ordenes.imprimir' : 'ordenes_empresa.imprimir', $item->orden_id),
                    'reportUrl' => $report ? route('informes.imprimir', $report->id) : null,
                ];
            })->all(),
        ];
    }

    private function dateTime(mixed $value): string
    {
        return $value ? Carbon::parse($value)->timezone('America/Guayaquil')->format('d/m/Y H:i') : 'No registrada';
    }
}
