<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante B2B - Cliente {{ $lote->nro_lote }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 11px; color: #0f172a; margin: 0; padding: 20px; background: #ffffff; }
        .receipt-card { max-width: 850px; margin: 0 auto; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 28px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2563eb; padding-bottom: 16px; margin-bottom: 20px; }
        .logo-text { font-size: 22px; font-weight: 900; color: #1e3a8a; letter-spacing: -0.5px; }
        .logo-sub { font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .receipt-title { text-align: right; }
        .receipt-title h2 { font-size: 14px; font-weight: 800; color: #2563eb; margin: 0; text-transform: uppercase; }
        .receipt-title p { font-size: 11px; font-weight: 700; color: #475569; margin: 4px 0 0 0; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-box h4 { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; margin: 0 0 6px 0; letter-spacing: 0.5px; }
        .info-box p { font-size: 11px; margin: 2px 0; color: #1e293b; }

        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items-table th { background: #1e293b; color: #ffffff; font-size: 10px; font-weight: 700; text-transform: uppercase; text-align: left; padding: 8px 10px; }
        table.items-table td { border-bottom: 1px solid #e2e8f0; padding: 8px 10px; font-size: 10.5px; vertical-align: middle; }
        table.items-table tr:nth-child(even) { background: #f8fafc; }

        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 28px; }
        .totals-box { width: 320px; background: #f1f5f9; padding: 14px 18px; border-radius: 8px; border: 1px solid #cbd5e1; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 11px; color: #334155; }
        .totals-row.final { border-top: 2px solid #2563eb; padding-top: 8px; font-size: 13px; font-weight: 800; color: #059669; }

        .signatures-section { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 45px; pt-4; }
        .signature-box { text-align: center; border-top: 1.5px solid #94a3b8; padding-top: 8px; }
        .signature-box p { font-size: 10px; font-weight: 700; color: #475569; margin: 2px 0; }

        .no-print { margin-bottom: 20px; text-align: right; }
        .btn-print { background: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 12px; }
        .btn-print:hover { background: #1d4ed8; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt-card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div>
                <div class="logo-text">NOVITEC SGN</div>
                <div class="logo-sub">Sistema de Gestión Novitec · Facturación B2B</div>
            </div>
            <div class="receipt-title">
                <h2>Comprobante de Recuento B2B</h2>
                <p>NRO. LOTE: {{ $lote->nro_lote }}</p>
                <p style="font-weight: 400; color: #64748b; font-size: 10px;">Fecha: {{ \Carbon\Carbon::parse($lote->created_at)->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h4>Datos de la Empresa Cliente</h4>
                <p><strong>Razón Social:</strong> {{ $lote->empresa_nombre }}</p>
                <p><strong>RUC:</strong> {{ $empresaInfo->ruc ?? 'N/A' }}</p>
                <p><strong>Dirección:</strong> {{ $empresaInfo->direccion_empresa ?? 'Ecuador' }}</p>
                <p><strong>Teléfono:</strong> {{ $empresaInfo->telefono ?? 'N/A' }}</p>
            </div>
            <div class="info-box">
                <h4>Detalles de Pago y Depósito Banco</h4>
                <p><strong>Banco Destino:</strong> {{ $lote->banco_destino ?? 'Banco Pichincha Cta Cte' }}</p>
                <p><strong>Nro. Transf. / Comprobante:</strong> {{ $lote->nro_comprobante_pago ?: 'N/A' }}</p>
                <p><strong>Nro. Retención SRI:</strong> {{ $lote->nro_retencion ?: 'N/A' }}</p>
                <p><strong>Estado Cobro:</strong> <span style="color: #059669; font-weight: 800;">{{ $lote->estado }}</span></p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Nro. Orden</th>
                    <th style="width: 12%;">Subtipo</th>
                    <th style="width: 25%;">Técnico(s)</th>
                    <th style="width: 15%;">Horas Trab.</th>
                    <th style="width: 18%;">Tarifa Aplicada</th>
                    <th style="width: 18%; text-align: right;">Total ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td><strong>{{ $item->nro_orden }}</strong></td>
                        <td>{{ $item->subtipo ?: 'Servicios' }}</td>
                        <td>{{ $item->tecnico_nombre ?: 'Sin técnico' }}</td>
                        <td>{{ number_format((float)$item->horas_trabajadas, 1) }} hrs</td>
                        <td>${{ number_format((float)$item->tarifa_aplicada, 2) }}</td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;">${{ number_format((float)$item->valor_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-box">
                @php
                    $montoIvaCalc = (float)($lote->monto_iva ?? round($lote->subtotal * 0.15, 2));
                    $totalConIvaCalc = (float)($lote->total_con_iva ?? round($lote->subtotal + $montoIvaCalc, 2));
                @endphp
                <div class="totals-row">
                    <span>Subtotal Factura Lote:</span>
                    <span>${{ number_format((float)$lote->subtotal, 2) }}</span>
                </div>
                <div class="totals-row" style="color: #2563eb;">
                    <span>(+) IVA 15%:</span>
                    <span>${{ number_format($montoIvaCalc, 2) }}</span>
                </div>
                <div class="totals-row" style="font-weight: 700; color: #059669; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 4px;">
                    <span>Total con IVA:</span>
                    <span>${{ number_format($totalConIvaCalc, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>(-) Retención Renta:</span>
                    <span>${{ number_format((float)$lote->monto_retencion_renta, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>(-) Retención IVA:</span>
                    <span>${{ number_format((float)$lote->monto_retencion_iva, 2) }}</span>
                </div>
                <div class="totals-row final">
                    <span>NETO RECIBIDO EN BANCO:</span>
                    <span>${{ number_format((float)$lote->monto_neto_banco, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="signatures-section">
            <div class="signature-box">
                <p>___________________________________</p>
                <p>NOVITEC SGN - DEPARTAMENTO CONTABLE</p>
                <p>Emisión y Entrega Autorizada</p>
            </div>
            <div class="signature-box">
                <p>___________________________________</p>
                <p>{{ strtoupper($lote->empresa_nombre) }}</p>
                <p>Recibido Conforme Cliente B2B</p>
            </div>
        </div>
    </div>
</body>
</html>
