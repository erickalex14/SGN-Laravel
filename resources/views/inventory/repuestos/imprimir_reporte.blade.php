@php
    $totalItems = $auditorias->sum('cantidad');
    $totalCosto = $auditorias->sum(fn($a) => ($a->repuesto->costo ?? 0) * $a->cantidad);
    
    // Repuesto más usado
    $repuestoMasUsado = 'Ninguno';
    $repuestoMasUsadoCant = 0;
    $agrupadoRep = $auditorias->groupBy('repuesto_id');
    if ($agrupadoRep->isNotEmpty()) {
        $maxRep = $agrupadoRep->map->sum('cantidad')->sortDesc();
        $maxId = $maxRep->keys()->first();
        $repMas = $auditorias->firstWhere('repuesto_id', $maxId);
        if ($repMas && $repMas->repuesto) {
            $repuestoMasUsado = $repMas->repuesto->nombre;
            $repuestoMasUsadoCant = $maxRep->first();
        }
    }

    // Técnico con más consumo
    $tecnicoLider = 'Ninguno';
    $tecnicoLiderCant = 0;
    $agrupadoTec = $auditorias->groupBy(fn($a) => $a->usuario_id ?: ($a->orden->tecnico_id ?? 0));
    if ($agrupadoTec->isNotEmpty()) {
        $maxTec = $agrupadoTec->map->sum('cantidad')->sortDesc();
        $tecId = $maxTec->keys()->first();
        $tecMas = $auditorias->first(fn($a) => ($a->usuario_id ?: ($a->orden->tecnico_id ?? 0)) == $tecId);
        if ($tecMas) {
            $tecnicoLider = $tecMas->usuario->nombre_tecnico ?? $tecMas->orden->tecnico->nombre_tecnico ?? 'N/A';
            $tecnicoLiderCant = $maxTec->first();
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Auditoría de Repuestos — Novitecnología</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 7.2pt; color: #1e293b; background: #fff; line-height: 1.25; }
        @page { size: A4 landscape; margin: 8mm; }
        @media print {
            .no-print { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
        
        .wrap { width: 277mm; margin: 0 auto; }
        
        /* Cabecera Premium */
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3.5px solid #4f46e5; padding-bottom: 8px; margin-bottom: 12px; }
        .hdr-left .empresa { font-size: 13pt; font-weight: 800; color: #4f46e5; }
        .hdr-left .sub { font-size: 7.5pt; color: #64748b; margin-top: 3px; }
        .hdr-right { text-align: right; }
        .rep-title { font-weight: 800; color: #0f172a; font-size: 11pt; text-transform: uppercase; letter-spacing: 0.5px; }
        .hdr-right .meta-date { color: #64748b; font-size: 7.2pt; margin-top: 2px; }
        .header img { height: 35px; object-fit: contain; }

        /* Botón de impresión flotante */
        .bp { position: fixed; top: 15px; right: 15px; background: #4f46e5; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-size: 12px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4); display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .bp:hover { background: #4338ca; transform: translateY(-1px); }

        /* KPIs Premium Box */
        .kpi-row { display: flex; gap: 8px; margin-bottom: 12px; }
        .kpi-card { flex: 1; border: 1px solid #e2e8f0; border-top: 3.5px solid #64748b; border-radius: 6px; padding: 7px 10px; text-align: center; background: #f8fafc; }
        .kpi-card.c-indigo { border-top-color: #6366f1; background: #eef2ff; }
        .kpi-card.c-green { border-top-color: #10b981; background: #f0fdf4; }
        .kpi-card.c-amber { border-top-color: #f59e0b; background: #fffbeb; }
        .kpi-card.c-blue { border-top-color: #3b82f6; background: #eff6ff; }
        
        .kpi-val { font-size: 14pt; font-weight: 900; line-height: 1.1; margin-bottom: 1px; }
        .kpi-card.c-indigo .kpi-val { color: #4f46e5; }
        .kpi-card.c-green .kpi-val { color: #15803d; }
        .kpi-card.c-amber .kpi-val { color: #b45309; }
        .kpi-card.c-blue .kpi-val { color: #1d4ed8; }

        .kpi-lbl { font-size: 6.2pt; color: #475569; font-weight: 800; text-transform: uppercase; }
        .kpi-lbl-sub { font-size: 5.8pt; color: #64748b; margin-top: 2px; }

        /* Filtros Aplicados */
        .filtros-box { background: #f1f5f9; border-left: 3.5px solid #4f46e5; padding: 5px 12px; border-radius: 4px; font-size: 7.2pt; color: #475569; margin-bottom: 12px; }
        
        /* Sección Titulo */
        .sec-titulo { font-size: 9.5pt; font-weight: 800; color: #0f172a; margin: 10px 0 6px; border-left: 3.5px solid #4f46e5; padding-left: 8px; text-transform: uppercase; }

        /* Tabla Principal Calibrada al 100% */
        table.dt { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.dt th { background: #4f46e5; color: #fff; font-size: 6.8pt; font-weight: 800; text-transform: uppercase; padding: 5px 6px; border: 1px solid #4338ca; text-align: left; }
        table.dt td { padding: 5px 6px; border: 1px solid #e2e8f0; font-size: 6.8pt; line-height: 1.3; vertical-align: middle; word-break: break-word; }
        table.dt tr:nth-child(even) td { background: #f8fafc; }
        
        .aud-code { font-family: monospace; font-weight: 700; color: #b45309; }
        .aud-nro-orden { font-family: monospace; font-weight: 800; color: #4f46e5; text-decoration: none; }
        
        .badge-style { display: inline-block; padding: 1px 6px; border-radius: 6px; font-size: 5.8pt; font-weight: 700; text-align: center; }

        /* Pie de página */
        .foot { text-align: center; margin-top: 15px; font-size: 6.8pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>

<button class="bp no-print" onclick="window.print()">
    <span>🖨️</span> Imprimir / Guardar PDF
</button>

<div class="wrap">
    <!-- Encabezado Corporativo -->
    <div class="header">
        <div class="hdr-left">
            <div class="empresa">Novitecnología Cía. Ltda.</div>
            <div class="sub">
                <strong>GYE:</strong> 04-6031337 / 0960500158 &nbsp;•&nbsp; 
                <strong>UIO:</strong> 02-6001635 / 0960500156 &nbsp;•&nbsp; 
                soporte@novitec.com.ec
            </div>
        </div>
        <div class="hdr-right">
            <div class="rep-title">Auditoría de Consumo de Repuestos</div>
            <div class="meta-date">Generado: {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="SGN - Novitec">
    </div>

    <!-- KPIs de Resumen -->
    <div class="kpi-row">
        <div class="kpi-card c-indigo">
            <div class="kpi-val">{{ $totalItems }} uds</div>
            <div class="kpi-lbl">Total Utilizados</div>
        </div>
        <div class="kpi-card c-green">
            <div class="kpi-val">${{ number_format($totalCosto, 2) }}</div>
            <div class="kpi-lbl">Costo total salidas</div>
        </div>
        <div class="kpi-card c-amber">
            <div class="kpi-val" style="font-size:10pt;" title="{{ $repuestoMasUsado }}">
                {{ strlen($repuestoMasUsado) > 28 ? substr($repuestoMasUsado, 0, 26) . '...' : $repuestoMasUsado }}
            </div>
            <div class="kpi-lbl">Repuesto más usado</div>
            <div class="kpi-lbl-sub">Consumo: {{ $repuestoMasUsadoCant }} uds</div>
        </div>
        <div class="kpi-card c-blue">
            <div class="kpi-val" style="font-size:10pt;" title="{{ $tecnicoLider }}">
                {{ strlen($tecnicoLider) > 28 ? substr($tecnicoLider, 0, 26) . '...' : $tecnicoLider }}
            </div>
            <div class="kpi-lbl">Técnico más activo</div>
            <div class="kpi-lbl-sub">Consumo: {{ $tecnicoLiderCant }} uds</div>
        </div>
    </div>

    <!-- Filtros Aplicados -->
    @if(count($filtrosTxt) > 0)
        <div class="filtros-box">
            <strong>Filtros activos:</strong> {{ implode('  •  ', $filtrosTxt) }}
        </div>
    @endif

    <!-- Detalle de Movimientos -->
    <div class="sec-titulo">Historial de Movimientos de Stock en Bodega</div>
    
    <table class="dt">
        <thead>
            <tr>
                <th style="width: 11%;">Fecha / Hora</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 24%;">Nombre del Repuesto</th>
                <th style="width: 16%;">Usuario / Técnico</th>
                <th style="width: 10%;">Orden</th>
                <th style="width: 13%;">Tipo de Orden</th>
                <th style="width: 4%; text-align: center;">Cant</th>
                <th style="width: 6%; text-align: right;">Costo Unit.</th>
                <th style="width: 6%; text-align: right;">Costo Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($auditorias as $a)
                @php
                    $fechaHora = \Carbon\Carbon::parse($a->fecha)->format('d/m/Y H:i');
                    $tecnicoNombre = $a->usuario->nombre_tecnico ?? $a->orden->tecnico->nombre_tecnico ?? 'N/A';
                    $costoUnit = $a->repuesto->costo ?? 0;
                    $costoTotal = $costoUnit * $a->cantidad;
                    $tipoOrden = $a->orden->motivo_ingreso ?? 'N/A';
                    
                    $badgeStyle = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                    if ($tipoOrden === 'Servicio Cliente Externo') {
                        $badgeStyle = 'background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;';
                    } elseif ($tipoOrden === 'Validacion de Garantia' || $tipoOrden === 'Validación de Garantía') {
                        $badgeStyle = 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
                    } elseif ($tipoOrden === 'Servicios a Empresas') {
                        $badgeStyle = 'background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff;';
                    }
                @endphp
                <tr>
                    <td>{{ $fechaHora }}</td>
                    <td class="aud-code">{{ $a->repuesto->codigo ?? '-' }}</td>
                    <td><strong>{{ $a->repuesto->nombre ?? '-' }}</strong></td>
                    <td>{{ $tecnicoNombre }}</td>
                    <td style="font-family: monospace; font-weight: 700;">
                        {{ $a->orden->nro_orden ?? 'N/A' }}
                    </td>
                    <td>
                        <span class="badge-style" style="{{ $badgeStyle }}">
                            {{ $tipoOrden }}
                        </span>
                    </td>
                    <td style="text-align: center; font-weight: 700;">{{ $a->cantidad }}</td>
                    <td style="text-align: right; font-family: monospace;">${{ number_format($costoUnit, 2) }}</td>
                    <td style="text-align: right; font-weight: 700; font-family: monospace;">${{ number_format($costoTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #94a3b8; padding: 15px;">
                        No se encontraron registros de auditoría.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pie de página -->
    <div class="foot">
        Novitecnología Cía. Ltda. &nbsp;•&nbsp; Sistema de Gestión de Servicio SGN &nbsp;•&nbsp; Impreso el {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>

<script>
    window.onload = function() {
        // Ejecutar la impresión automática al cargar la página
        setTimeout(() => {
            window.print();
        }, 300);
    }
</script>
</body>
</html>
