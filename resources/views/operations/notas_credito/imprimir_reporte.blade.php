<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte de Notas de Credito</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 8pt; color: #000; background: #fff; }
.wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }
.btn-print { position: fixed; top: 10px; right: 10px; background: #1a56db; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 4px; margin-bottom: 6px; gap: 10px; }
.header-info { font-size: 7pt; line-height: 1.35; }
.header-info .empresa { font-size: 9.5pt; font-weight: 700; }
.header img { height: 32px; }
.report-title { font-size: 11pt; font-weight: 800; text-transform: uppercase; color: #0f172a; margin-top: 4px; margin-bottom: 2px; }
.filters-summary { font-size: 7.5pt; color: #475569; margin-bottom: 10px; line-height: 1.4; background: #f8fafc; border: 1px dashed #cbd5e1; padding: 6px 10px; border-radius: 4px; }
table.report-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
table.report-table th { background: #1a56db; color: #fff; font-size: 7pt; text-transform: uppercase; font-weight: 700; padding: 4px 6px; text-align: left; border: 1.5px solid #1a56db; }
table.report-table td { padding: 4px 6px; border: 1px solid #cbd5e1; font-size: 7pt; vertical-align: middle; }
table.report-table tr:nth-child(even) td { background: #f8fafc; }
.badge { font-family: monospace; font-size: 7pt; font-weight: 700; background: #f1f5f9; padding: 1px 4px; border-radius: 3px; color: #0f172a; border: 1px solid #cbd5e1; }
.status-badge { padding: 1px 5px; border-radius: 3px; font-size: 6.5pt; font-weight: 700; text-transform: uppercase; border: 1px solid transparent; display: inline-block; }
.st-pend { background: #fef9c3; color: #854d0e; border-color: #fde047; }
.st-aprob { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.st-rech { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
.summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px; page-break-inside: avoid; }
.summary-box { border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px; text-align: center; }
.summary-box .val { font-size: 13pt; font-weight: 800; color: #0f172a; }
.summary-box .lbl { font-size: 6.5pt; font-weight: 700; color: #475569; text-transform: uppercase; }
.foot { text-align: center; margin-top: 15px; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 6px; page-break-inside: avoid; }
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

    <div class="report-title">Reporte de Auditoría - Notas de Crédito</div>

    @php
        $q = request('q');
        $estado = request('estado');
        $sucursalId = (int) request('sucursal_id');
        $tecnico = request('tecnico');
        $desde = request('desde');
        $hasta = request('hasta');

        $filtros = [];
        if ($q) $filtros[] = "Búsqueda: \"{$q}\"";
        if ($estado) $filtros[] = "Estado: " . ucfirst(strtolower($estado));
        if ($sucursalId) {
            $suc = \App\Models\Directory\Sucursal::find($sucursalId);
            $filtros[] = "Sucursal: " . ($suc->ciudad ?? $suc->nombre ?? $sucursalId);
        }
        if ($tecnico) $filtros[] = "Técnico: \"{$tecnico}\"";
        if ($desde || $hasta) {
            $desdeF = $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : 'Inicio';
            $hastaF = $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : 'Fin';
            $filtros[] = "Rango de Fechas: {$desdeF} - {$hastaF}";
        }
        $filtrosTxt = empty($filtros) ? 'Todos los registros' : implode(' | ', $filtros);

        // Resumen
        $total = $solicitudes->count();
        $aprobadas = $solicitudes->filter(fn($s) => strtoupper($s->estado) === 'APROBADA')->count();
        $rechazadas = $solicitudes->filter(fn($s) => strtoupper($s->estado) === 'RECHAZADA')->count();
        $pendientes = $solicitudes->filter(fn($s) => strtoupper($s->estado) === 'PENDIENTE')->count();
    @endphp

    <div class="filters-summary">
        <strong>Filtros aplicados:</strong> {{ $filtrosTxt }}
    </div>

    <div class="summary-grid">
        <div class="summary-box" style="background:#eff6ff; border-color:#bfdbfe;">
            <div class="val" style="color:#1e40af;">{{ $total }}</div>
            <div class="lbl">Total Trámites</div>
        </div>
        <div class="summary-box" style="background:#f0fdf4; border-color:#bbf7d0;">
            <div class="val" style="color:#166534;">{{ $aprobadas }}</div>
            <div class="lbl">Autorizadas</div>
        </div>
        <div class="summary-box" style="background:#fee2e2; border-color:#fecaca;">
            <div class="val" style="color:#991b1b;">{{ $rechazadas }}</div>
            <div class="lbl">Rechazadas</div>
        </div>
        <div class="summary-box" style="background:#fffbeb; border-color:#fef3c7;">
            <div class="val" style="color:#854d0e;">{{ $pendientes }}</div>
            <div class="lbl">Pendientes</div>
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th width="12%">Solicitud</th>
                <th width="11%">Fecha</th>
                <th width="20%">Técnico</th>
                <th width="25%">Asunto</th>
                <th width="18%">Orden / Factura</th>
                <th width="14%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($solicitudes as $s)
                @php
                    $estadoNC = strtoupper((string) $s->estado);
                    $clase = match($estadoNC) { 'PENDIENTE' => 'st-pend', 'APROBADA' => 'st-aprob', 'RECHAZADA' => 'st-rech', default => '' };
                @endphp
                <tr>
                    <td><span class="badge">{{ $s->nro_solicitud }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($s->creado_en)->format('d/m/Y') }}</td>
                    <td><strong>{{ $s->tecnico_nombre ?: ($s->tecnico->nombre_tecnico ?? '-') }}</strong></td>
                    <td>{{ $s->asunto }}</td>
                    <td>
                        {{ $s->orden->nro_orden ?? '-' }}
                        @if(!empty($s->orden->nro_factura))
                            <div style="font-size:6pt; color:#475569; margin-top:1px;">Factura: {{ $s->orden->nro_factura }}</div>
                        @endif
                    </td>
                    <td><span class="status-badge {{ $clase }}">{{ $s->estado }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#64748b;">No se encontraron registros de notas de crédito.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">
        Novitecnologia Cia. Ltda. | SGN Auditoría de Notas de Crédito | Generado el:
        {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>

<script>
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 300);
    }
</script>
</body>
</html>
