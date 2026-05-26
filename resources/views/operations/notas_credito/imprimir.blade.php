<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Solicitud NC {{ $solicitud->nro_solicitud }}</title>
<style>
body { font-family: Arial, Helvetica, sans-serif; margin: 0; color: #0f172a; background: #f8fafc; }
.wrap { max-width: 920px; margin: 20px auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
.head { padding: 16px 20px; background: #1e3a8a; color: #fff; }
.head h1 { margin: 0; font-size: 20px; }
.head p { margin: 6px 0 0; font-size: 12px; opacity: .9; }
.sec { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; }
.sec h3 { margin: 0 0 10px; font-size: 13px; text-transform: uppercase; color: #334155; }
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; }
.item b { font-size: 12px; color: #64748b; display: block; margin-bottom: 3px; }
.item span { font-size: 14px; }
.txt { white-space: pre-wrap; font-size: 14px; line-height: 1.5; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; padding: 10px 12px; }
.print-btn { position: fixed; top: 12px; right: 12px; border: 0; background: #1d4ed8; color: #fff; padding: 10px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; }
.badge { display: inline-block; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 700; }
.pend { background: #fef3c7; color: #92400e; }
.aprob { background: #dcfce7; color: #166534; }
.rech { background: #fee2e2; color: #991b1b; }
@media print {
    body { background: #fff; }
    .wrap { margin: 0; border: 0; border-radius: 0; }
    .print-btn { display: none; }
}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>
<div class="wrap">
    <div class="head">
        <h1>Solicitud de Nota de Credito</h1>
        <p>Nro: {{ $solicitud->nro_solicitud }} | Fecha: {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud ?? $solicitud->creado_en)->format('d/m/Y H:i') }}</p>
    </div>

    <div class="sec">
        <h3>Datos de Solicitud</h3>
        @php
            $estado = strtoupper((string) $solicitud->estado);
            $badge = $estado === 'APROBADA' ? 'aprob' : ($estado === 'RECHAZADA' ? 'rech' : 'pend');
        @endphp
        <div class="grid">
            <div class="item"><b>Estado</b><span class="badge {{ $badge }}">{{ $solicitud->estado }}</span></div>
            <div class="item"><b>Tecnico</b><span>{{ $solicitud->tecnico_nombre ?: ($solicitud->tecnico->nombre_tecnico ?? '-') }}</span></div>
            <div class="item"><b>Asunto</b><span>{{ $solicitud->asunto }}</span></div>
            <div class="item"><b>Aprobado/Revisado por</b><span>{{ $solicitud->nombre_admin ?: '-' }}</span></div>
        </div>
    </div>

    <div class="sec">
        <h3>Orden Relacionada</h3>
        <div class="grid">
            <div class="item"><b>Nro. Orden</b><span>{{ $solicitud->orden->nro_orden ?? ('#' . $solicitud->orden_id) }}</span></div>
            <div class="item"><b>Estado Orden</b><span>{{ $solicitud->orden->estado_orden ?? '-' }}</span></div>
            <div class="item"><b>Cliente</b><span>{{ trim(($solicitud->orden->cliente->nombres ?? '').' '.($solicitud->orden->cliente->apellidos ?? '')) ?: '-' }}</span></div>
            <div class="item"><b>Equipo</b><span>{{ trim(($solicitud->orden->equipo->marca ?? '').' '.($solicitud->orden->equipo->modelo ?? '')) ?: '-' }}</span></div>
        </div>
    </div>

    <div class="sec">
        <h3>Detalle</h3>
        <div class="txt">{{ $solicitud->detalles ?: '-' }}</div>
    </div>

    @if(!empty($solicitud->motivo_rechazo))
    <div class="sec">
        <h3>Motivo de Rechazo</h3>
        <div class="txt">{{ $solicitud->motivo_rechazo }}</div>
    </div>
    @endif
</div>
</body>
</html>
