<?php

namespace App\Services\Facturacion;

use App\Models\Directory\Empresa;
use App\Models\Operations\Orden;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoicePayloadFactory
{
    public function __construct(private readonly OrderBillingCalculator $billing) {}

    public function fromCashCollection(int $collectionId, object $user): array
    {
        $selected = DB::table('caja_general_cobros')->where('id', $collectionId)->first();
        abort_if(!$selected, 404, 'Cobro no encontrado.');

        $collectionsQuery = DB::table('caja_general_cobros');
        if (!empty($selected->grupo_cobro_uuid)) {
            $collectionsQuery->where('grupo_cobro_uuid', $selected->grupo_cobro_uuid);
        } else {
            $collectionsQuery->where('nro_orden', $selected->nro_orden)
                ->where('fecha_cobro', $selected->fecha_cobro);
        }
        $collections = $collectionsQuery->orderBy('id')->get();
        $total = round((float) $collections->sum('monto_cobrado'), 2);
        abort_if($total <= 0, 422, 'El cobro no tiene un monto facturable.');

        $order = !empty($selected->orden_id)
            ? Orden::with(['cliente', 'equipo.series', 'preciosOrden', 'informes'])->find($selected->orden_id)
            : null;
        $buyer = $order?->cliente;
        $identification = trim((string) ($buyer->identificacion ?? ''));
        $sourceKey = self::cashSourceKey($selected);
        $externalReference = 'SGN-CG-' . substr(hash('sha256', $sourceKey), 0, 32);
        $unitPrice = $this->netFromGross($total, 15.0);

        return $this->envelope(
            $externalReference,
            [
                'type' => 'CAJA_GENERAL',
                'id' => $sourceKey,
                'number' => $selected->nro_orden,
                'establishmentCode' => $this->establishmentCode((int) $selected->sucursal_id),
            ],
            $identification === ''
                ? $this->finalConsumer()
                : [
                    'identificationType' => strlen($identification) === 13 ? '04' : '05',
                    'identification' => $identification,
                    'legalName' => trim(($buyer->nombres ?? '') . ' ' . ($buyer->apellidos ?? '')),
                    'address' => $buyer->direccion_clientes ?? null,
                    'email' => $buyer->correo ?? null,
                    'phone' => $buyer->numero_contacto ?? null,
                ],
            [[
                'mainCode' => $selected->codigo_producto ?: 'SERVICIO',
                'description' => $order
                    ? $this->billing->description($order)
                    : ($selected->equipo_info ?: "Servicio orden {$selected->nro_orden}"),
                'quantity' => 1,
                'unitPrice' => $unitPrice,
                'discount' => 0,
                'taxRate' => 15,
            ]],
            $collections->map(fn ($collection) => [
                'sriCode' => $this->paymentCode((string) $collection->metodo_pago),
                'amount' => round((float) $collection->monto_cobrado, 2),
            ])->values()->all(),
            $user
        );
    }

    public function fromB2bBatch(int $batchId, object $user): array
    {
        $batch = DB::table('recuento_b2b_lote')->where('id', $batchId)->first();
        abort_if(!$batch, 404, 'Lote B2B no encontrado.');
        $items = DB::table('recuento_b2b_item')->where('lote_id', $batchId)->orderBy('id')->get();
        abort_if($items->isEmpty(), 422, 'El lote B2B no contiene órdenes facturables.');
        $company = Empresa::where('nombre', $batch->empresa_nombre)->first()
            ?? Empresa::where('nombre', 'LIKE', '%' . $batch->empresa_nombre . '%')->first();
        abort_if(!$company || empty($company->ruc), 422,
            'La empresa del lote no tiene RUC configurado en SGN.');

        $lines = $items->map(fn ($item) => [
            'mainCode' => 'B2B-' . $item->orden_id,
            'description' => trim("{$item->subtipo} - Orden {$item->nro_orden}"),
            'quantity' => 1,
            'unitPrice' => round((float) $item->valor_total, 2),
            'discount' => 0,
            'taxRate' => 15,
        ])->values()->all();
        $invoiceTotal = round(array_sum(array_map(
            fn ($line) => $line['unitPrice'] + round($line['unitPrice'] * .15, 2), $lines)), 2);

        return $this->envelope(
            'SGN-B2B-' . $batch->id,
            [
                'type' => 'RECUENTO_B2B',
                'id' => (string) $batch->id,
                'number' => $batch->nro_lote,
                'establishmentCode' => $this->establishmentCode($this->b2bBranchId($items)),
            ],
            [
                'identificationType' => '04',
                'identification' => $company->ruc,
                'legalName' => $company->nombre,
                'address' => $company->direccion_empresa,
                'email' => $company->correo,
                'phone' => $company->telefono,
            ],
            $lines,
            [['sriCode' => '20', 'amount' => $invoiceTotal]],
            $user
        );
    }

    public static function cashSourceKey(object $collection): string
    {
        if (!empty($collection->grupo_cobro_uuid)) {
            return 'COBRO|' . $collection->grupo_cobro_uuid;
        }
        return 'COBRO|' . $collection->nro_orden . '|' . $collection->fecha_cobro;
    }

    private function envelope(string $reference, array $source, array $buyer, array $lines,
        array $payments, object $user): array
    {
        return [
            'externalReference' => $reference,
            'source' => $source,
            'buyer' => $buyer,
            'lines' => $lines,
            'payments' => $payments,
            'requestedBy' => [
                'id' => (string) $user->id,
                'name' => $user->nombre_tecnico ?? $user->usuario ?? 'Usuario SGN',
                'role' => (string) ($user->rol_id ?? ''),
            ],
        ];
    }

    private function finalConsumer(): array
    {
        return [
            'identificationType' => '07',
            'identification' => '9999999999999',
            'legalName' => 'CONSUMIDOR FINAL',
            'address' => null,
            'email' => null,
            'phone' => null,
        ];
    }

    private function b2bBranchId($items): int
    {
        $personalIds = $items->where('tipo_orden', 'personal')->pluck('orden_id');
        $companyIds = $items->where('tipo_orden', '!=', 'personal')->pluck('orden_id');
        $branchIds = collect();

        if ($personalIds->isNotEmpty()) {
            $branchIds = $branchIds->concat(
                DB::table('ordenes')->whereIn('id', $personalIds)->pluck('sucursal_id')
            );
        }
        if ($companyIds->isNotEmpty()) {
            $branchIds = $branchIds->concat(
                DB::table('ordenesempresas')->whereIn('id', $companyIds)->pluck('sucursal_id')
            );
        }

        abort_if($branchIds->count() !== $items->count(), 422,
            'No se pudo determinar la sucursal de todas las órdenes del lote B2B.');
        $unique = $branchIds->filter()->unique()->values();
        abort_if($unique->count() !== 1, 422,
            'Un lote B2B debe contener órdenes de una sola sucursal para facturarse.');

        return (int) $unique->first();
    }

    private function establishmentCode(int $branchId): string
    {
        $city = DB::table('sucursales')->where('id', $branchId)->value('ciudad');
        abort_if(!$city, 422, 'La orden no tiene una sucursal válida.');
        $key = Str::lower(Str::ascii(trim((string) $city)));
        $code = config("facturacion.establishments.{$key}");
        abort_if(!$code, 422, "La sucursal {$city} no tiene establecimiento fiscal configurado.");

        return $code;
    }

    private function paymentCode(string $method): string
    {
        if (stripos($method, 'efectivo') !== false) return '01';
        if (stripos($method, 'tarjeta') !== false || stripos($method, 'datafast') !== false
            || stripos($method, 'kushki') !== false) return '19';
        return '20';
    }

    private function netFromGross(float $gross, float $taxRate): float
    {
        $net = round($gross / (1 + $taxRate / 100), 2);
        for ($i = 0; $i < 5; $i++) {
            $calculated = round($net + round($net * $taxRate / 100, 2), 2);
            if ($calculated === $gross) return $net;
            $net = round($net + ($calculated < $gross ? .01 : -.01), 2);
        }
        abort(422, 'No se pudo cuadrar el IVA del cobro.');
    }
}
