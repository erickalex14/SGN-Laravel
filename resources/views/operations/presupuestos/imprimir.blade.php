<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto {{ $orden->nro_orden }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 9pt; color: #0f172a; background: #fff; }
        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            .no-print { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
        .print-btn {
            position: fixed; top: 10px; right: 10px;
            background: #1a56db; color: #fff; border: none;
            padding: 10px 20px; border-radius: 6px; font-size: 13px;
            cursor: pointer; font-weight: 700;
        }
        .wrap { max-width: 190mm; margin: 0 auto; padding: 6mm; }
        .header {
            display: flex; justify-content: space-between; align-items: flex-start;
            border-bottom: 1.5px solid #0f172a; padding-bottom: 6px; margin-bottom: 10px;
        }
        .empresa { font-size: 11pt; font-weight: 700; }
        .header-info { font-size: 8.5pt; line-height: 1.6; }
        .badge-pres {
            background: #1a56db; color: #fff; padding: 5px 12px; border-radius: 4px;
            font-size: 13pt; font-weight: 700; text-align: center;
        }
        .sec {
            background: #dbeafe; font-weight: 700; font-size: 7.5pt;
            text-transform: uppercase; padding: 3px 8px; border-left: 3px solid #1a56db;
            margin: 8px 0 3px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; font-size: 8.5pt; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; font-size: 8pt; }
        .txt-right { text-align: right; }
        .lbl {
            font-size: 6.5pt; color: #64748b; font-weight: 700;
            text-transform: uppercase; display: block; margin-bottom: 1px;
        }
        .totales { width: 45%; margin-left: auto; }
        .total-final { background: #f0fdf4; }
        .total-final .valor { font-size: 12pt; font-weight: 800; color: #059669; }
        .iva { color: #f59e0b; font-weight: 700; }
        .firma-wrap { display: flex; justify-content: space-between; margin-top: 20px; }
        .firma-box { width: 44%; text-align: center; }
        .firma-linea { border-top: 1px solid #000; padding-top: 4px; margin-top: 28px; font-size: 8.5pt; }
        .nota-final {
            background: #fef9c3; border: 1px solid #fde047; border-radius: 3px;
            font-size: 7.5pt; color: #713f12; text-align: center; padding: 5px 10px; margin-top: 10px;
        }
        .footer {
            text-align: center; margin-top: 8px; font-size: 7pt;
            color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 6px;
        }
        .empty-state {
            border: 1px dashed #cbd5e1; border-radius: 6px; padding: 12px;
            color: #64748b; text-align: center; font-size: 8.5pt;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Imprimir / Guardar PDF</button>

    <div class="wrap">
        <div class="header">
            <div class="header-info">
                <div class="empresa">Novitecnologia Cia. Ltda.</div>
                <div><b>GYE:</b> 04-6031337 &nbsp; <b>UIO:</b> 02-6001635 &nbsp; <b>MTA:</b> 05-2611080</div>
                <div>soporte@novitec.com.ec &nbsp; www.novitec.com.ec</div>
            </div>
            <div style="text-align:right;">
                <div class="badge-pres">{{ $orden->nro_orden }}</div>
                <div style="font-size:8pt;margin-top:4px;color:#475569;">Presupuesto - {{ $fecha }}</div>
            </div>
        </div>

        <div class="sec">Datos de la Orden</div>
        <table>
            <tr>
                <td width="30%"><span class="lbl">Nro. Orden</span>{{ $orden->nro_orden }}</td>
                <td width="40%"><span class="lbl">Cliente</span>{{ $orden->cliente }}</td>
                <td width="30%"><span class="lbl">Tecnico</span>{{ $tecnicoSesion ?: ($orden->tecnico ?: '-') }}</td>
            </tr>
            <tr>
                <td colspan="3"><span class="lbl">Equipo</span>{{ trim(($orden->tipo ?? '') . ' ' . ($orden->marca ?? '') . ' ' . ($orden->modelo ?? '') . ' ' . (($orden->serie ?? '') ? ('S/N ' . $orden->serie) : '')) }}</td>
            </tr>
        </table>

        <div class="sec">Detalle del Presupuesto</div>
        @if(count($items) > 0)
            <table>
                <tr>
                    <th>Servicio / Reparacion</th>
                    <th class="txt-right">Sin IVA</th>
                    <th class="txt-right">Con IVA 15%</th>
                </tr>
                @foreach($items as $item)
                    <tr>
                        <td>
                            {{ $item['nombre'] }}
                            @if(!empty($item['desc']))
                                <div style="font-size:10px;color:#64748b;">{{ $item['desc'] }}</div>
                            @endif
                        </td>
                        <td class="txt-right">${{ number_format((float) $item['precio'], 2) }}</td>
                        <td class="txt-right" style="color:#059669;font-weight:700;">${{ number_format((float) $item['precio'] * 1.15, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-state">No hay items para este presupuesto. Regresa y agrega al menos uno.</div>
        @endif

        <table class="totales">
            <tr>
                <td><span class="lbl">Subtotal</span></td>
                <td class="txt-right"><b>${{ number_format((float) $subtotal, 2) }}</b></td>
            </tr>
            <tr>
                <td><span class="lbl">IVA 15%</span></td>
                <td class="txt-right"><span class="iva">${{ number_format((float) $iva, 2) }}</span></td>
            </tr>
            <tr class="total-final">
                <td><span class="lbl">TOTAL</span></td>
                <td class="txt-right"><span class="valor">${{ number_format((float) $total, 2) }}</span></td>
            </tr>
        </table>

        @if(!empty($notas))
            <div class="sec">Notas / Condiciones</div>
            <div style="font-size:8.5pt;padding:6px 8px;border:1px solid #e2e8f0;border-radius:4px;">{!! nl2br(e($notas)) !!}</div>
        @endif

        <div class="firma-wrap">
            <div class="firma-box"><div class="firma-linea">Tecnico:</div></div>
            <div class="firma-box"><div class="firma-linea">Cliente acepta:</div></div>
        </div>

        <div class="nota-final"><b>NOTA:</b> Este presupuesto es valido por 15 dias calendario desde la fecha de emision. Los precios incluyen IVA 15%.</div>
        <div class="footer">Novitecnologia Cia. Ltda. - Sistema de Gestion Novitec</div>
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
