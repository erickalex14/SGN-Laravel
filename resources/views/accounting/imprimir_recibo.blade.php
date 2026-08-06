<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recibo de Cobro REC-{{ $cobro->nro_orden }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 8.5pt; color: #1e293b; background: #fff; }
.no-print { display: inline-flex; }
.wrap { width: 100%; max-width: 200mm; margin: auto; padding: 6mm; }

.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 6px; margin-bottom: 10px; }
.header-info { font-size: 8pt; line-height: 1.4; color: #475569; }
.header-info .empresa { font-size: 11pt; font-weight: 800; color: #0f172a; }
.header img { height: 42px; }

.receipt-banner { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: #ffffff; padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; }
.receipt-banner .nro { font-size: 11pt; font-weight: 800; letter-spacing: 0.02em; }
.receipt-banner .meta { font-size: 7.5pt; text-align: right; line-height: 1.4; opacity: 0.9; }

.sec-titulo { background: #f1f5f9; color: #0f172a; font-weight: 800; font-size: 7.5pt; text-transform: uppercase; padding: 4px 8px; border-left: 4px solid #2563eb; margin-bottom: 6px; margin-top: 10px; }

table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
table.datos td { border: 1px solid #cbd5e1; padding: 6px 10px; font-size: 8pt; vertical-align: top; }
table.datos td .lbl { font-size: 6.5pt; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px; }

table.items-tbl { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5pt; }
table.items-tbl th { background: #0f172a; color: #ffffff; padding: 6px 10px; text-align: left; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.03em; }
table.items-tbl th.r { text-align: right; }
table.items-tbl td { border-bottom: 1px solid #e2e8f0; padding: 8px 10px; vertical-align: middle; }
table.items-tbl td.r { text-align: right; font-weight: 700; }
table.items-tbl tr:nth-child(even) { background: #f8fafc; }

.totales-box { display: flex; justify-content: flex-end; margin-bottom: 16px; }
.totales-inner { border: 1.5px solid #0f172a; border-radius: 6px; overflow: hidden; min-width: 260px; }
.totales-inner table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
.totales-inner table td { padding: 4px 10px; }
.totales-inner table td:last-child { text-align: right; font-weight: 700; }
.totales-inner tr.total-row td { background: #10b981; color: #ffffff; font-size: 10pt; font-weight: 800; }

.btn-print { position: fixed; top: 14px; right: 14px; background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 4px 12px rgba(0,0,0,.2); }

.firmas { display: flex; justify-content: space-between; margin-top: 30px; page-break-inside: avoid; }
.firma-box { width: 44%; text-align: center; }
.firma-linea { border-top: 1.5px solid #0f172a; padding-top: 4px; font-size: 8pt; margin-top: 40px; font-weight: 700; color: #0f172a; }

.foot { text-align: center; margin-top: 18px; font-size: 7.5pt; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 8px; }

@media print {
    @page { size: A4 portrait; margin: 10mm; }
    .no-print { display: none !important; }
    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
}
</style>
</head>
<body>

<button class="btn-print no-print" onclick="window.print()">Imprimir / Guardar PDF</button>

<div class="wrap">
    <!-- ENCABEZADO -->
    <div class="header">
        <div class="header-info">
            <div class="empresa">NOVITECNOLOGIA CIA. LTDA.</div>
            <div>RUC: 1792487811001 &nbsp;|&nbsp; Sucursal {{ $sucursalNombre }}</div>
            <div>GYE: 04-6031337 / 0960500158 &nbsp;&nbsp;|&nbsp;&nbsp; UIO: 02-6001635 / 0960500156</div>
            <div>soporte@novitec.com.ec &nbsp;|&nbsp; www.novitec.com.ec</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="Novitec - SGN">
    </div>

    <!-- BANNER RECIBO -->
    <div class="receipt-banner">
        <div class="nro">COMPROBANTE DE RECIBO DE COBRO N° REC-{{ str_pad($cobro->id, 6, '0', STR_PAD_LEFT) }}</div>
        <div class="meta">
            Fecha de Cobro: {{ \Carbon\Carbon::parse($cobro->fecha_cobro)->format('d/m/Y H:i') }}<br>
            Registrado por: {{ $cobro->usuario_nombre }}
        </div>
    </div>

    <!-- DATOS CLIENTE Y ORDEN -->
    <div class="sec-titulo">Información de la Orden y Cliente</div>
    <table class="datos">
        <tr>
            <td width="30%"><span class="lbl">Número de Orden</span><strong style="color: #2563eb; font-size: 9.5pt;">{{ $cobro->nro_orden }}</strong></td>
            <td width="40%"><span class="lbl">Cliente</span><strong>{{ $cobro->cliente_nombre }}</strong></td>
            <td width="30%"><span class="lbl">Sucursal</span>{{ $sucursalNombre }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="lbl">Equipo / Serie</span>{{ $cobro->equipo_info ?: 'No especificado' }}</td>
            <td><span class="lbl">Observaciones General</span>{{ $cobro->observaciones ?: 'Sin observaciones' }}</td>
        </tr>
    </table>

    <!-- DESGLOSE DE PAGO -->
    <div class="sec-titulo">Desglose de Formas de Pago y Cuentas de Destino</div>
    <table class="items-tbl">
        <thead>
            <tr>
                <th style="width: 35%;">Método de Pago / Banco Destino</th>
                <th style="width: 25%;">Destino Cuenta</th>
                <th style="width: 25%;">Nro. Boucher / Referencia</th>
                <th class="r" style="width: 15%;">Monto ($)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalCobradoCalculado = 0; $totalEfectivoRecibido = 0; $totalVuelto = 0; $hayEfectivo = false; @endphp
            @foreach($cobrosGrupo as $cRow)
                @php 
                    $montoC = (float) $cRow->monto_cobrado; 
                    $totalCobradoCalculado += $montoC;
                    if ($cRow->destino_cuenta === 'Caja General') {
                        $hayEfectivo = true;
                        $totalEfectivoRecibido += (float) ($cRow->monto_recibido ?? $montoC);
                        $totalVuelto += (float) ($cRow->vuelto_dado ?? 0);
                    }
                @endphp
                <tr>
                    <td><strong>{{ $cRow->metodo_pago }}</strong></td>
                    <td>
                        @if($cRow->destino_cuenta === 'Caja General')
                            <span style="color: #15803d; font-weight: 700;">Caja General (Recepción)</span>
                        @else
                            <span style="color: #0369a1; font-weight: 700;">Bancos (Cuenta Empresa)</span>
                        @endif
                    </td>
                    <td>
                        {{ $cRow->observaciones ?: 'N/A' }}
                        @if(!empty($cRow->comprobante_url))
                            @php $compUrl = str_starts_with($cRow->comprobante_url, 'http') ? $cRow->comprobante_url : asset($cRow->comprobante_url); @endphp
                            <br><a href="{{ $compUrl }}" target="_blank" style="color: #2563eb; font-weight: 700; font-size: 7.5pt; text-decoration: underline;">[Ver Comprobante Adjunto]</a>
                        @endif
                    </td>
                    <td class="r">${{ number_format($montoC, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($hayEfectivo)
        <!-- DETALLE DE CONTROL DE EFECTIVO SI APLICA -->
        <div class="sec-titulo" style="border-left-color: #16a34a;">Desglose de Control de Efectivo</div>
        <table class="datos" style="margin-bottom: 12px;">
            <tr>
                <td width="33%"><span class="lbl">Monto Recibido del Cliente</span><strong>${{ number_format($totalEfectivoRecibido, 2) }}</strong></td>
                <td width="33%"><span class="lbl">Vuelto Entregado</span><strong style="color: #d97706;">${{ number_format($totalVuelto, 2) }}</strong></td>
                <td width="34%"><span class="lbl">Neto Ingresado a Caja General</span><strong style="color: #16a34a;">${{ number_format($totalEfectivoRecibido - $totalVuelto, 2) }}</strong></td>
            </tr>
        </table>
    @endif

    <!-- TOTALES -->
    <div class="totales-box">
        <div class="totales-inner">
            <table>
                <tr class="total-row">
                    <td>TOTAL COBRADO RECIBIDO:</td>
                    <td>${{ number_format($totalCobradoCalculado, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- FIRMAS DE CONFORMIDAD -->
    <div class="firmas">
        <div class="firma-box">
            <div class="firma-linea">FIRMA / CONFORMIDAD CLIENTE</div>
        </div>
        <div class="firma-box">
            <div class="firma-linea">RECIBIDO CONFORME (CAJA / RECEPCIÓN)</div>
        </div>
    </div>

    <div class="foot">
        Este documento es un comprobante de recibo de cobro interno sin validez de crédito tributario.<br>
        Gracias por su preferencia — Novitecnologia Cia. Ltda.
    </div>
</div>

</body>
</html>
