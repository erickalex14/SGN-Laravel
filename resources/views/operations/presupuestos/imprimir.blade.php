<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto {{ $orden->nro_orden }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 7.6pt; color: #000; background: #fff; }
        .wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }
        .btn-print { position: fixed; top: 10px; right: 10px; background: #1a56db; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 3px; margin-bottom: 4px; gap: 10px; }
        .header-info { font-size: 7pt; line-height: 1.35; }
        .header-info .empresa { font-size: 9pt; font-weight: 700; }
        .header img { height: 34px; }
        .doc-header { display: flex; justify-content: space-between; align-items: center; background: #1a56db; color: #fff; padding: 3px 8px; border-radius: 3px; margin-bottom: 4px; }
        .doc-header .nro { font-size: 10pt; font-weight: 700; }
        .doc-header .meta { font-size: 6.5pt; text-align: right; line-height: 1.4; }
        .sec-title { background: #dbeafe; font-weight: 700; font-size: 6.5pt; text-transform: uppercase; padding: 2px 6px; border-left: 3px solid #1a56db; margin-bottom: 1px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        table.data td, table.data th { border: 1px solid #d1d5db; padding: 2px 5px; font-size: 7pt; vertical-align: top; }
        table.data th { background: #f1f5f9; text-align: left; font-size: 6.5pt; text-transform: uppercase; }
        .lbl { font-size: 5.5pt; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0; }
        .txt-right { text-align: right; }
        .totales { width: 45%; margin-left: auto; margin-top: 2px; }
        .totales .total-row td { background: #f0fdf4; font-weight: 800; color: #059669; }
        .notas { border: 1px solid #d1d5db; padding: 6px; margin-top: 2px; white-space: pre-wrap; }
        .firma-wrap { display: flex; justify-content: space-between; margin-top: 12px; }
        .firma-box { width: 44%; text-align: center; }
        .firma-linea { border-top: 1px solid #000; padding-top: 3px; font-size: 7pt; margin-top: 20px; }
        .nota-final { margin-top: 8px; padding: 5px 10px; background: #fef9c3; border: 1px solid #fde047; border-radius: 3px; font-size: 7.5pt; color: #713f12; text-align: center; }
        .foot { text-align: center; margin-top: 8px; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 6px; }
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
    <div class="header">
        <div class="header-info">
            <div class="empresa">Novitecnologia Cia. Ltda.</div>
            <div><strong>GYE:</strong> 04-6031337 / 0960500158 &nbsp;&nbsp; <strong>UIO:</strong> 02-6001635 / 0960500156</div>
            <div>soporte@novitec.com.ec &nbsp; www.novitec.com.ec</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="SGN - Novitec">
    </div>

    <div class="doc-header">
        <div class="nro">Presupuesto - {{ $orden->nro_orden }}</div>
        <div class="meta">
            Fecha: {{ $fecha }}<br>
            Tecnico: {{ $tecnicoSesion ?: ($orden->tecnico ?: '-') }}
        </div>
    </div>

    <div class="sec-title">Datos de la Orden</div>
    <table class="data">
        <tr>
            <td width="30%"><span class="lbl">Nro. Orden</span>{{ $orden->nro_orden }}</td>
            <td width="40%"><span class="lbl">Cliente</span>{{ $orden->cliente }}</td>
            <td width="30%"><span class="lbl">Tecnico</span>{{ $tecnicoSesion ?: ($orden->tecnico ?: '-') }}</td>
        </tr>
        <tr>
            <td colspan="3"><span class="lbl">Equipo</span>{{ trim(($orden->tipo ?? '') . ' ' . ($orden->marca ?? '') . ' ' . ($orden->modelo ?? '') . ' ' . (($orden->serie ?? '') ? ('S/N ' . $orden->serie) : '')) }}</td>
        </tr>
    </table>

    <div class="sec-title">Detalle del Presupuesto</div>
    <table class="data">
        <tr>
            <th>Servicio / Reparacion</th>
            <th class="txt-right">Sin IVA</th>
            <th class="txt-right">Con IVA 15%</th>
        </tr>
        @forelse($items as $item)
            <tr>
                <td>
                    {{ $item['nombre'] }}
                    @if(!empty($item['desc']))
                        <div style="font-size: 6.3pt; color: #64748b;">{{ $item['desc'] }}</div>
                    @endif
                </td>
                <td class="txt-right">${{ number_format((float) $item['precio'], 2) }}</td>
                <td class="txt-right">${{ number_format((float) $item['precio'] * 1.15, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #64748b;">No hay items para este presupuesto.</td>
            </tr>
        @endforelse
    </table>

    <table class="data totales">
        <tr>
            <td><span class="lbl">Subtotal</span></td>
            <td class="txt-right"><strong>${{ number_format((float) $subtotal, 2) }}</strong></td>
        </tr>
        <tr>
            <td><span class="lbl">IVA 15%</span></td>
            <td class="txt-right">${{ number_format((float) $iva, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td><span class="lbl">TOTAL</span></td>
            <td class="txt-right">${{ number_format((float) $total, 2) }}</td>
        </tr>
    </table>

    @if(!empty($notas))
        <div class="sec-title">Notas / Condiciones</div>
        <div class="notas">{{ $notas }}</div>
    @endif

    <div class="firma-wrap">
        <div class="firma-box"><div class="firma-linea">Tecnico:</div></div>
        <div class="firma-box"><div class="firma-linea">Cliente acepta:</div></div>
    </div>

    <div class="nota-final">
        <strong>NOTA:</strong> Este presupuesto es valido por 15 dias calendario desde la fecha de emision. Los precios incluyen IVA 15%.
    </div>

    <div class="foot">
        Novitecnologia Cia. Ltda. | Sistema de Gestion SGN | Impreso el:
        {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>

@if($autoImprimir)
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
@endif
</body>
</html>
