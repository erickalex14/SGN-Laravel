<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprobante Arqueo ARQ-{{ str_pad($arqueo->id, 6, '0', STR_PAD_LEFT) }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 7.6pt; color: #000; background: #fff; }
.no-print { display: inline-flex; }
.wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }

.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 3px; margin-bottom: 4px; gap: 10px; }
.header-info { font-size: 7pt; line-height: 1.35; }
.header-info .empresa { font-size: 9pt; font-weight: 700; }
.header img { height: 34px; }

.orden-header { display: flex; justify-content: space-between; align-items: center; background: #1a56db; color: #fff; padding: 4px 8px; border-radius: 3px; margin-bottom: 5px; }
.orden-header .nro { font-size: 9.5pt; font-weight: 700; }
.orden-header .meta { font-size: 6.5pt; text-align: right; line-height: 1.4; }

.sec-titulo { background: #dbeafe; font-weight: 700; font-size: 6.5pt; text-transform: uppercase; padding: 2px 6px; border-left: 3px solid #1a56db; margin-bottom: 2px; margin-top: 4px; }

table.datos { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
table.datos td { border: 1px solid #d1d5db; padding: 3px 6px; font-size: 7pt; vertical-align: top; }
table.datos td .lbl { font-size: 5.5pt; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 1px; }

table.items-tbl { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 7pt; }
table.items-tbl th { background: #1a56db; color: #fff; padding: 3px 6px; text-align: left; font-size: 6.5pt; text-transform: uppercase; }
table.items-tbl th.r { text-align: right; }
table.items-tbl td { border-bottom: 1px solid #e5e7eb; padding: 3px 6px; vertical-align: middle; }
table.items-tbl td.r { text-align: right; font-weight: 700; }
table.items-tbl tr:nth-child(even) { background: #f8fafc; }

.badge { display: inline-block; padding: 1px 7px; border-radius: 3px; font-size: 6.5pt; font-weight: 700; text-transform: uppercase; }
.badge-exacto { background: #dcfce7; color: #166534; }
.badge-faltante { background: #fee2e2; color: #991b1b; }
.badge-sobrante { background: #fef9c3; color: #92400e; }
.badge-depositado { background: #dbeafe; color: #1e40af; }

.totales-box { display: flex; justify-content: flex-end; margin-bottom: 6px; }
.totales-inner { border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden; min-width: 220px; }
.totales-inner table { width: 100%; border-collapse: collapse; font-size: 7pt; }
.totales-inner table td { padding: 2px 8px; }
.totales-inner table td:last-child { text-align: right; font-weight: 700; }
.totales-inner tr.subtotal td { background: #f8fafc; }
.totales-inner tr.total-row td { background: #1a56db; color: #fff; font-size: 8pt; }

.btn-print { position: fixed; top: 10px; right: 10px; background: #1a56db; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,.2); }

.deposit-box { border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; margin-bottom: 8px; background: #fafafa; }
.deposit-box .title { font-size: 6.5pt; font-weight: 700; color: #1a56db; text-transform: uppercase; margin-bottom: 4px; }
.receipt-img { max-width: 100%; max-height: 320px; border-radius: 4px; border: 1px solid #cbd5e1; margin-top: 6px; display: block; }

.firmas { display: flex; justify-content: space-between; margin-top: 25px; page-break-inside: avoid; }
.firma-box { width: 44%; text-align: center; }
.firma-linea { border-top: 1px solid #000; padding-top: 3px; font-size: 7pt; margin-top: 30px; font-weight: 700; }

.foot { text-align: center; margin-top: 10px; font-size: 6.5pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 4px; }

@media print {
    @page { size: A4 portrait; margin: 8mm; }
    .no-print { display: none !important; }
    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
}
</style>
</head>
<body>

<button class="btn-print no-print" onclick="window.print()">Imprimir / Guardar PDF</button>

<div class="wrap">
    <!-- ENCABEZADO CON LOGO DE NOVITEC -->
    <div class="header">
        <div class="header-info">
            <div class="empresa">Novitecnologia Cia. Ltda.</div>
            <div><strong>GYE:</strong> 04-6031337 / 0960500158 &nbsp;&nbsp; <strong>UIO:</strong> 02-6001635 / 0960500156</div>
            <div>soporte@novitec.com.ec &nbsp; www.novitec.com.ec</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="SGN - Novitec">
    </div>

    <!-- FRANJA AZUL PRINCIPAL -->
    <div class="orden-header">
        <div class="nro">ARQUEO CIEGO DE CAJA GENERAL: {{ $arqueo->codigo_sucursal }}-ARQ-{{ str_pad($arqueo->id, 6, '0', STR_PAD_LEFT) }}</div>
        <div class="meta">
            Fecha de Cierre: {{ \Carbon\Carbon::parse($arqueo->fecha)->format('d/m/Y H:i') }}<br>
            Estado: {{ strtoupper($arqueo->estado) }}
        </div>
    </div>

    <!-- DATOS DEL CIERRE Y SUCURSAL -->
    <div class="sec-titulo">Datos del Cierre y Sucursal</div>
    <table class="datos">
        <tr>
            <td width="30%"><span class="lbl">Sucursal</span>{{ $sucursalNombre }} ({{ $arqueo->codigo_sucursal }})</td>
            <td width="35%"><span class="lbl">Responsable del Arqueo</span>{{ $arqueo->usuario_nombre }}</td>
            <td width="35%"><span class="lbl">Nro. Comprobante / Papeleta Depósito</span>{{ $arqueo->nro_comprobante_deposito ?: 'Pendiente de adjuntar' }}</td>
        </tr>
        <tr>
            <td colspan="3"><span class="lbl">Observaciones / Justificación</span>{{ $arqueo->observaciones ?: 'Sin observaciones' }}</td>
        </tr>
    </table>

    <!-- CUADRE FINANCIERO -->
    <div class="sec-titulo">Resumen de Cuadre Financiero de Caja</div>
    <table class="items-tbl">
        <thead>
            <tr>
                <th class="r">Monto Sistema (Calculado)</th>
                <th class="r">Monto Físico Contado</th>
                <th class="r">Diferencia ($)</th>
                <th style="text-align: center;">Resultado Cuadre</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="r" style="font-size: 8.5pt;">${{ number_format((float)$arqueo->monto_sistema, 2) }}</td>
                <td class="r" style="font-size: 8.5pt;">${{ number_format((float)$arqueo->monto_fisico, 2) }}</td>
                <td class="r" style="font-size: 8.5pt; color: {{ (float)$arqueo->diferencia < 0 ? '#991b1b' : ((float)$arqueo->diferencia > 0 ? '#92400e' : '#059669') }};">
                    ${{ number_format((float)$arqueo->diferencia, 2) }}
                </td>
                <td style="text-align: center;">
                    @if((float)$arqueo->diferencia < 0)
                        <span class="badge badge-faltante">Faltante</span>
                    @elseif((float)$arqueo->diferencia > 0)
                        <span class="badge badge-sobrante">Sobrante</span>
                    @else
                        <span class="badge badge-exacto">Cuadre Exacto</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- DETALLE DE ÓRDENES INCLUIDAS EN EL ARQUEO -->
    <div class="sec-titulo">Detalle de Órdenes de Trabajo e Ingresos en Efectivo ({{ count($cobros) }} cobro(s))</div>
    <table class="items-tbl">
        <thead>
            <tr>
                <th width="15%">Nro. Orden</th>
                <th width="25%">Cliente</th>
                <th width="25%">Equipo / Serie</th>
                <th width="10%">Hora</th>
                <th width="12%" class="r">Cobrado</th>
                <th width="13%" class="r">Neto Caja</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cobros as $cbr)
                @php
                    $recibido = (float)($cbr->monto_recibido ?? $cbr->monto_cobrado);
                    $vuelto = (float)($cbr->vuelto_dado ?? 0);
                    $neto = (float)($cbr->monto_neto_caja ?? $cbr->monto_cobrado);
                @endphp
                <tr>
                    <td><strong>{{ $cbr->nro_orden }}</strong></td>
                    <td>{{ $cbr->cliente_nombre }}</td>
                    <td>{{ $cbr->equipo_info ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($cbr->fecha_cobro)->format('H:i') }}</td>
                    <td class="r">${{ number_format((float)$cbr->monto_cobrado, 2) }}</td>
                    <td class="r" style="color: #059669;">${{ number_format($neto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 8px;">No hay cobros individuales asociados a este arqueo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TOTALES CONSOLIDADOS -->
    @if(count($cobros) > 0)
        <div class="totales-box">
            <div class="totales-inner">
                <table>
                    <tr class="subtotal">
                        <td>Total Cobrado Bruto:</td>
                        <td>${{ number_format($cobros->sum('monto_cobrado'), 2) }}</td>
                    </tr>
                    <tr class="subtotal">
                        <td>Total Vueltos Entregados:</td>
                        <td>${{ number_format($cobros->sum('vuelto_dado'), 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Neto Ingresado a Caja:</td>
                        <td>${{ number_format($cobros->sum('monto_neto_caja'), 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

    <!-- COMPROBANTE DE DEPÓSITO ADJUNTO -->
    @if(!empty($arqueo->comprobante_deposito_url))
        <div class="deposit-box">
            <div class="title">Comprobante / Papeleta de Depósito Bancario Adjunto</div>
            <div style="font-size: 6.8pt; color: #475569;">
                Nro. Papeleta: <strong>{{ $arqueo->nro_comprobante_deposito ?: 'N/A' }}</strong> — 
                Enlace directo: <a href="{{ $arqueo->comprobante_deposito_url }}" target="_blank">{{ $arqueo->comprobante_deposito_url }}</a>
            </div>
            @php
                $ext = strtolower(pathinfo($arqueo->comprobante_deposito_url, PATHINFO_EXTENSION));
            @endphp
            @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                <img src="{{ $arqueo->comprobante_deposito_url }}" class="receipt-img" alt="Comprobante de Depósito">
            @else
                <div style="background: #e0f2fe; padding: 6px 10px; border-radius: 4px; color: #0369a1; font-weight: 700; font-size: 7pt; margin-top: 6px;">
                    Documento adjunto en formato PDF. <a href="{{ $arqueo->comprobante_deposito_url }}" target="_blank">Haga clic aquí para abrir el PDF completo.</a>
                </div>
            @endif
        </div>
    @endif

    <!-- SECCIÓN DE FIRMAS -->
    <div class="firmas">
        <div class="firma-box">
            <div class="firma-linea">
                {{ $arqueo->usuario_nombre }}<br>
                <span style="font-weight: 400; color: #475569;">FIRMA CAJERO / RESPONSABLE</span>
            </div>
        </div>
        <div class="firma-box">
            <div class="firma-linea">
                SUPERVISIÓN / CONTABILIDAD<br>
                <span style="font-weight: 400; color: #475569;">RECIBIDO Y AUDITADO</span>
            </div>
        </div>
    </div>

    <div class="foot">
        Documento generado automáticamente por el Sistema de Gestión Novitec (SGN) — {{ date('d/m/Y H:i:s') }}
    </div>
</div>

</body>
</html>
