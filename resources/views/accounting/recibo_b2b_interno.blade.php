<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante Interno B2B - {{ $lote->nro_lote }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 11px; color: #0f172a; margin: 0; padding: 20px; background: #ffffff; }
        .receipt-card { max-width: 880px; margin: 0 auto; border: 2px solid #0f172a; border-radius: 12px; padding: 28px; background: #ffffff; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px dashed #475569; padding-bottom: 16px; margin-bottom: 20px; }
        .logo-text { font-size: 20px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; }
        .logo-sub { font-size: 10px; color: #dc2626; font-weight: 800; text-transform: uppercase; }
        .receipt-title { text-align: right; }
        .receipt-title h2 { font-size: 14px; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; }
        .receipt-title p { font-size: 11px; font-weight: 700; color: #475569; margin: 4px 0 0 0; }

        .audit-bar { background: #f1f5f9; padding: 12px 16px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 20px; font-size: 11px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
        .audit-bar strong { color: #0f172a; }

        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items-table th { background: #0f172a; color: #ffffff; font-size: 10px; font-weight: 700; text-transform: uppercase; text-align: left; padding: 8px 10px; }
        table.items-table td { border-bottom: 1px solid #cbd5e1; padding: 8px 10px; font-size: 10.5px; vertical-align: middle; }
        table.items-table tr:nth-child(even) { background: #f8fafc; }

        .totals-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
        .notes-box { width: 450px; background: #fffbe6; padding: 12px 16px; border-radius: 8px; border: 1px solid #ffe58f; font-size: 10.5px; color: #854d0e; }
        .totals-box { width: 320px; background: #f8fafc; padding: 14px 18px; border-radius: 8px; border: 1.5px solid #0f172a; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 11px; color: #334155; }
        .totals-row.final { border-top: 2px solid #0f172a; padding-top: 8px; font-size: 13px; font-weight: 800; color: #1e3a8a; }

        .signatures-section { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-top: 45px; }
        .signature-box { text-align: center; border-top: 1.5px solid #64748b; padding-top: 8px; }
        .signature-box p { font-size: 9.5px; font-weight: 700; color: #475569; margin: 2px 0; }

        .no-print { margin-bottom: 20px; text-align: right; }
        .btn-print { background: #0f172a; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 12px; }
        .btn-print:hover { background: #334155; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt-card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print()">Imprimir / Guardar PDF Interno</button>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div>
                <div class="logo-text">NOVITEC SGN - CONTROL INTERNO</div>
                <div class="logo-sub">Comprobante Interno de Auditoría y Facturación B2B</div>
            </div>
            <div class="receipt-title">
                <h2>Lote B2B: {{ $lote->nro_lote }}</h2>
                <p>Empresa: {{ $lote->empresa_nombre }}</p>
                <p style="font-weight: 400; color: #64748b; font-size: 10px;">Procesado el: {{ \Carbon\Carbon::parse($lote->created_at)->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="audit-bar">
            <div><strong>Custodio Registrador:</strong><br>{{ $lote->usuario_nombre ?? 'Usuario' }}</div>
            <div><strong>Banco Destino:</strong><br>{{ $lote->banco_destino ?? 'Banco Pichincha' }}</div>
            <div><strong>Total Órdenes:</strong><br>{{ $lote->total_ordenes }} órdenes</div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Nro. Orden</th>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 14%;">Subtipo</th>
                    <th style="width: 25%;">Técnico(s) Asignados</th>
                    <th style="width: 12%;">Horas</th>
                    <th style="width: 13%;">Tarifa ($)</th>
                    <th style="width: 12%; text-align: right;">Total ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td><strong>{{ $item->nro_orden }}</strong></td>
                        <td>{{ strtoupper($item->tipo_orden ?: 'empresa') }}</td>
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
            <div class="notes-box">
                <strong>Detalles de Comprobantes SRI y Depósito:</strong><br>
                <span>Nro. Comprobante / Transf: <strong>{{ $lote->nro_comprobante_pago ?: 'N/A' }}</strong></span><br>
                <span>Nro. Retención SRI: <strong>{{ $lote->nro_retencion ?: 'N/A' }}</strong></span>
            </div>
            <div class="totals-box">
                @php
                    $montoIvaCalc = (float)($lote->monto_iva ?? round($lote->subtotal * 0.15, 2));
                    $totalConIvaCalc = (float)($lote->total_con_iva ?? round($lote->subtotal + $montoIvaCalc, 2));
                @endphp
                <div class="totals-row">
                    <span>Subtotal Facturado:</span>
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
                    <span>NETO CUADRE BANCO:</span>
                    <span>${{ number_format((float)$lote->monto_neto_banco, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="signatures-section">
            <div class="signature-box">
                <p>___________________________</p>
                <p>CUSTODIO DE CAJA / B2B</p>
                <p>Elaborado por</p>
            </div>
            <div class="signature-box">
                <p>___________________________</p>
                <p>CONTABILIDAD GENERAL</p>
                <p>Revisado y Cuadrado</p>
            </div>
            <div class="signature-box">
                <p>___________________________</p>
                <p>GERENCIA / AUDITORÍA</p>
                <p>Aprobado</p>
            </div>
        </div>
    </div>
</body>
</html>
