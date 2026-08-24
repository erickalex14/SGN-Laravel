<?php

namespace App\Services\Facturacion;

use App\Models\Operations\Orden;
use Illuminate\Support\Str;

class OrderBillingCalculator
{
    private const TAX_RATE = 15.0;
    private const REVIEW_PRICE = 28.0;

    public function calculate(Orden $order): array
    {
        $order->loadMissing(['preciosOrden', 'equipo.series', 'informes']);

        $subtotal = round(self::REVIEW_PRICE + (float) $order->preciosOrden->sum('precio'), 2);
        $isWarranty = trim((string) $order->motivo_ingreso) === 'Validacion de Garantia';
        $warrantyRejected = Str::lower(trim((string) $order->estado_garantia)) === 'rechazada';
        $discount = $isWarranty && !$warrantyRejected ? $subtotal : 0.0;
        $taxable = round($subtotal - $discount, 2);
        $tax = round($taxable * self::TAX_RATE / 100, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'taxable' => $taxable,
            'tax_rate' => self::TAX_RATE,
            'tax' => $tax,
            'total' => round($taxable + $tax, 2),
            'description' => $this->description($order),
        ];
    }

    public function description(Orden $order): string
    {
        $equipment = $order->equipo;
        $serials = $equipment?->series?->pluck('serie')->filter()->values() ?? collect();
        if ($serials->isEmpty() && !empty($equipment?->serie)) {
            $serials = collect(explode(',', (string) $equipment->serie))->map(fn ($value) => trim($value))->filter();
        }

        $equipmentText = trim(collect([$equipment?->tipo, $equipment?->marca, $equipment?->modelo])
            ->filter()->implode(' '));
        if ($serials->isNotEmpty()) {
            $equipmentText .= ' SN ' . $serials->implode(', ');
        }

        $report = $order->informes->sortByDesc(fn ($item) => $item->fecha_creacion ?? $item->id)->first();
        $work = trim((string) ($report?->proceso ?: $order->tipo_servicio_texto ?: $order->motivo_ingreso));
        $result = trim((string) ($report?->conclusion ?: $order->memo_entrega ?: $order->observacion));

        $parts = array_filter([
            'OT ' . $order->nro_orden,
            $equipmentText !== '' ? 'Equipo: ' . $equipmentText : null,
            $work !== '' ? 'Trabajo: ' . $work : null,
            $result !== '' ? 'Resultado: ' . $result : null,
        ]);

        return Str::limit(preg_replace('/\s+/u', ' ', implode(' | ', $parts)), 300, '');
    }
}
