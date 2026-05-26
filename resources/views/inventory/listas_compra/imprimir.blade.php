<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de Compra {{ $lista->nro_lista }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 12px; background: #fff; color: #0f172a; }
.page { width: 100%; max-width: 190mm; margin: 0 auto; padding: 8mm; }
.print-btn { position: fixed; top: 12px; right: 12px; border: 0; background: #7c3aed; color: #fff; padding: 10px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; }
.header { display: flex; justify-content: space-between; border-bottom: 2px solid #7c3aed; padding-bottom: 8px; margin-bottom: 12px; }
.title { font-size: 20px; font-weight: 800; color: #7c3aed; }
.meta { text-align: right; line-height: 1.5; }
.info { background: #f5f3ff; border: 1px solid #ede9fe; border-radius: 6px; padding: 8px; margin-bottom: 10px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
.lbl { display: block; color: #7c3aed; text-transform: uppercase; font-size: 10px; font-weight: 700; margin-bottom: 2px; }
table { width: 100%; border-collapse: collapse; }
th { background: #7c3aed; color: #fff; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; }
td { border-bottom: 1px solid #e2e8f0; padding: 7px 8px; vertical-align: top; }
tbody tr:nth-child(even) { background: #faf5ff; }
.mono { font-family: monospace; font-weight: 700; color: #6d28d9; }
.num { text-align: center; font-weight: 800; color: #6d28d9; }
.obs { margin-top: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 8px; }
.obs .lbl { color: #92400e; }
.foot { margin-top: 12px; font-size: 10px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
@media print {
    @page { size: A4 portrait; margin: 10mm; }
    .print-btn { display: none; }
    .page { padding: 0; }
}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>
<div class="page">
    <div class="header">
        <div>
            <div class="title">Lista de Compra</div>
            <div>Novitecnologia Cia. Ltda.</div>
        </div>
        <div class="meta">
            <div><strong>{{ $lista->nro_lista }}</strong></div>
            <div>Fecha: {{ $lista->fecha_creacion ? \Carbon\Carbon::parse($lista->fecha_creacion)->format('d/m/Y H:i') : '-' }}</div>
            <div>Generado por: {{ $lista->creado_por ?: '-' }}</div>
        </div>
    </div>

    <div class="info">
        <div><span class="lbl">Nro Lista</span>{{ $lista->nro_lista ?: '-' }}</div>
        <div><span class="lbl">Estado</span>{{ $lista->estado ?: '-' }}</div>
        <div><span class="lbl">Total Unidades</span>{{ $totalCantidad }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Solicitud</th>
                <th>Orden / Cliente</th>
                <th>Repuesto</th>
                <th>Tecnico</th>
                <th style="text-align:center;">Cant.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="mono">{{ $item->nro_solicitud }}</span></td>
                    <td>
                        <span class="mono">{{ $item->orden?->nro_orden ?? '-' }}</span><br>
                        <span>{{ trim(($item->orden?->cliente?->nombres ?? '') . ' ' . ($item->orden?->cliente?->apellidos ?? '')) ?: '-' }}</span><br>
                        <span style="color:#64748b;font-size:11px;">
                            {{ trim(($item->orden?->equipo?->tipo ?? '') . ' ' . ($item->orden?->equipo?->marca ?? '') . ' ' . ($item->orden?->equipo?->modelo ?? '')) ?: '-' }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $item->repuesto_nombre ?: '-' }}</strong><br>
                        <span style="color:#64748b;font-size:11px;">{{ $item->nro_parte ?: '-' }}</span>
                    </td>
                    <td>{{ $item->tecnico_nombre ?: '-' }}</td>
                    <td class="num">{{ (int) $item->cantidad }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#64748b;">No hay items asociados a esta lista.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($lista->observacion))
        <div class="obs">
            <span class="lbl">Observaciones</span>
            {{ $lista->observacion }}
        </div>
    @endif

    <div class="foot">
        Impreso: {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }} | {{ $lista->nro_lista }}
    </div>
</div>
</body>
</html>
