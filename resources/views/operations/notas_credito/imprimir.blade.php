<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Solicitud NC {{ $solicitud->nro_solicitud }}</title>
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
.lbl { font-size: 5.5pt; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0; }
.pill { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 6.5pt; font-weight: 700; }
.txt-box { border: 1px solid #d1d5db; padding: 6px; margin-bottom: 4px; white-space: pre-wrap; line-height: 1.45; }
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
        <div class="nro">Solicitud Nota de Credito - {{ $solicitud->nro_solicitud }}</div>
        <div class="meta">
            Fecha: {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud ?? $solicitud->creado_en)->format('d/m/Y H:i') }}<br>
            Tecnico: {{ $solicitud->tecnico_nombre ?: ($solicitud->tecnico->nombre_tecnico ?? '-') }}
        </div>
    </div>

    @php
        $estado = strtoupper((string) $solicitud->estado);
        $estilo = $estado === 'APROBADA'
            ? ['#dcfce7', '#166534']
            : ($estado === 'RECHAZADA' ? ['#fee2e2', '#991b1b'] : ['#fef3c7', '#92400e']);
    @endphp

    <div class="sec-title">Datos de Solicitud</div>
    <table class="data">
        <tr>
            <td width="25%"><span class="lbl">Estado</span><span class="pill" style="background: {{ $estilo[0] }}; color: {{ $estilo[1] }};">{{ $solicitud->estado }}</span></td>
            <td width="25%"><span class="lbl">Tecnico</span>{{ $solicitud->tecnico_nombre ?: ($solicitud->tecnico->nombre_tecnico ?? '-') }}</td>
            <td width="25%"><span class="lbl">Asunto</span>{{ $solicitud->asunto ?: '-' }}</td>
            <td width="25%"><span class="lbl">Aprobado/Revisado por</span>{{ $solicitud->nombre_admin ?: '-' }}</td>
        </tr>
    </table>

    <div class="sec-title">Orden Relacionada</div>
    <table class="data">
        <tr>
            <td width="25%"><span class="lbl">Nro. Orden</span>{{ $solicitud->orden->nro_orden ?? ('#' . $solicitud->orden_id) }}</td>
            <td width="25%"><span class="lbl">Estado Orden</span>{{ $solicitud->orden->estado_orden ?? '-' }}</td>
            <td width="25%"><span class="lbl">Cliente</span>{{ trim(($solicitud->orden->cliente->nombres ?? '') . ' ' . ($solicitud->orden->cliente->apellidos ?? '')) ?: '-' }}</td>
            <td width="25%"><span class="lbl">Equipo</span>{{ trim(($solicitud->orden->equipo->marca ?? '') . ' ' . ($solicitud->orden->equipo->modelo ?? '')) ?: '-' }}</td>
        </tr>
    </table>

    <div class="sec-title">Detalle</div>
    <div class="txt-box">{{ $solicitud->detalles ?: '-' }}</div>

    @if(!empty($solicitud->motivo_rechazo))
        <div class="sec-title">Motivo de Rechazo</div>
        <div class="txt-box">{{ $solicitud->motivo_rechazo }}</div>
    @endif

    <div class="foot">
        Novitecnologia Cia. Ltda. | Sistema de Gestion SGN | Impreso el:
        {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>
</body>
</html>
