<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de Compra {{ $lista->nro_lista }}</title>
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
.mono { font-family: monospace; font-weight: 700; color: #1d4ed8; }
.num { text-align: center; font-weight: 700; }
.obs { border: 1px solid #d1d5db; padding: 6px; margin-top: 2px; white-space: pre-wrap; }
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
    @php
        $estadoPdf = match (trim((string) ($lista->estado ?? ''))) {
            'Pendiente', 'Generada', 'Completada', 'Aprobada' => 'APROBADA',
            default => strtoupper(trim((string) ($lista->estado ?? '-'))),
        };
    @endphp
    <div class="header">
        <div class="header-info">
            <div class="empresa">Novitecnologia Cia. Ltda.</div>
            <div><strong>GYE:</strong> 04-6031337 / 0960500158 &nbsp;&nbsp; <strong>UIO:</strong> 02-6001635 / 0960500156</div>
            <div>soporte@novitec.com.ec &nbsp; www.novitec.com.ec</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="SGN - Novitec">
    </div>

    <div class="doc-header">
        <div class="nro">Lista de Compra - {{ $lista->nro_lista }}</div>
        <div class="meta">
            Fecha: {{ $lista->fecha_creacion ? \Carbon\Carbon::parse($lista->fecha_creacion)->format('d/m/Y H:i') : '-' }}<br>
            Generado por: {{ $lista->creado_por ?: '-' }}
        </div>
    </div>

    <div class="sec-title">Resumen</div>
    <table class="data">
        <tr>
            <td width="33%"><span class="lbl">Nro Lista</span>{{ $lista->nro_lista ?: '-' }}</td>
            <td width="33%"><span class="lbl">Estado</span>{{ $estadoPdf }}</td>
            <td width="34%"><span class="lbl">Total Unidades</span>{{ $totalCantidad }}</td>
        </tr>
    </table>

    <div class="sec-title">Detalle de Solicitudes</div>
    <table class="data">
        <tr>
            <th width="4%">#</th>
            <th width="12%">Solicitud</th>
            <th width="34%">Orden / Equipo</th>
            <th width="24%">Repuesto</th>
            <th width="16%">Solicita / Aprueba</th>
            <th width="10%" style="text-align:center;">Cant.</th>
        </tr>
        @forelse($items as $item)
            @php
                $orden = $item->orden;
                $equipo = $orden?->equipo;
                $cliente = $orden?->cliente;
                $clienteNombre = trim(($cliente?->nombres ?? '') . ' ' . ($cliente?->apellidos ?? '')) ?: '-';
                $equipoNombre = trim(($equipo?->tipo ?? '') . ' ' . ($equipo?->marca ?? '') . ' ' . ($equipo?->modelo ?? '')) ?: '-';
                $codigoEquipo = trim((string) ($equipo?->producto_inventario_codigo ?? '')) !== ''
                    ? trim((string) $equipo->producto_inventario_codigo)
                    : (trim((string) ($equipo?->modelo ?? '')) !== '' ? trim((string) $equipo->modelo) : '-');
                $factura = collect([$orden?->nro_factura, $orden?->nro_factura_2])
                    ->filter(fn ($v) => trim((string) $v) !== '')
                    ->implode(' / ');
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><span class="mono">{{ $item->nro_solicitud }}</span></td>
                <td>
                    <span class="lbl">Orden</span><span class="mono">{{ $orden?->nro_orden ?? '-' }}</span><br>
                    <span class="lbl">Cliente</span>{{ $clienteNombre }}<br>
                    <span class="lbl">Equipo</span>{{ $equipoNombre }}<br>
                    <span class="lbl">Codigo equipo</span>{{ $codigoEquipo }}<br>
                    <span class="lbl">Serie</span>{{ $equipo?->serie ?: '-' }}<br>
                    <span class="lbl">Motivo ingreso</span>{{ $orden?->motivo_ingreso ?: '-' }}<br>
                    @if($factura !== '')
                        <span class="lbl">Factura</span>{{ $factura }}
                    @endif
                </td>
                <td>
                    <strong>{{ $item->repuesto_nombre ?: '-' }}</strong><br>
                    <span style="color:#64748b;">{{ $item->nro_parte ?: '-' }}</span>
                </td>
                <td>
                    <span class="lbl">Solicita</span>{{ $item->tecnico_nombre ?: ($item->tecnico?->nombre_tecnico ?? '-') }}<br>
                    <span class="lbl">Aprueba</span>{{ $item->aprobado_por ?: '-' }}<br>
                    <span class="lbl">Tecnico OT</span>{{ $orden?->tecnico?->nombre_tecnico ?: '-' }}
                </td>
                <td class="num">{{ (int) $item->cantidad }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #64748b;">No hay items asociados a esta lista.</td>
            </tr>
        @endforelse
    </table>

    @if(!empty($lista->observacion))
        <div class="sec-title">Observaciones</div>
        <div class="obs">{{ $lista->observacion }}</div>
    @endif

    <div class="foot">
        Novitecnologia Cia. Ltda. | Sistema de Gestion SGN | Impreso el:
        {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>
</body>
</html>
