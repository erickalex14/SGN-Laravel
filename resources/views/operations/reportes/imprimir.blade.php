@php
    $total = $resultados->count();
    $cnt = [
        'Pendiente' => 0,
        'En proceso' => 0,
        'Finalizada' => 0,
        'Entregada' => 0,
        'Nota de Credito' => 0
    ];
    foreach ($resultados as $r) {
        $est = $r['estado_orden'] ?? 'Pendiente';
        if (isset($cnt[$est])) {
            $cnt[$est]++;
        }
    }
    $tasa = $total > 0 ? round(($cnt['Entregada'] / $total) * 100) : 0;

    // Top 5 Marcas
    $topMarcas = $resultados->groupBy('marca')
        ->map(fn($group) => $group->count())
        ->sortDesc()
        ->take(5);

    // Top 5 Tecnicos
    $topTecnicos = $resultados->groupBy('tecnico_nombre')
        ->map(fn($group) => $group->count())
        ->sortDesc()
        ->take(5);

    // Mapeo de estilos CSS de estado para coherencia visual
    $estadoEstilos = [
        'Pendiente'       => ['bg' => '#fef9c3', 'fg' => '#854d0e'],
        'En proceso'      => ['bg' => '#dbeafe', 'fg' => '#1e40af'],
        'Finalizada'      => ['bg' => '#dcfce7', 'fg' => '#166534'],
        'Entregada'       => ['bg' => '#ecfdf5', 'fg' => '#065f46'],
        'Nota de Credito' => ['bg' => '#fce7f3', 'fg' => '#9d174d'],
        'Abierta'         => ['bg' => '#e0e7ff', 'fg' => '#3730a3'],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $titulo }} — Novitecnología</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 7.2pt; color: #1e293b; background: #fff; line-height: 1.25; }
    @page { size: A4 landscape; margin: 8mm; }
    @media print {
        .no-print { display: none !important; }
        body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    }
    
    .wrap { width: 277mm; margin: 0 auto; }
    
    /* Cabecera Premium */
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3.5px solid #1a56db; padding-bottom: 8px; margin-bottom: 12px; }
    .hdr-left .empresa { font-size: 13pt; font-weight: 800; color: #1a56db; }
    .hdr-left .sub { font-size: 7.5pt; color: #64748b; margin-top: 3px; }
    .hdr-right { text-align: right; }
    .rep-title { font-weight: 800; color: #0f172a; font-size: 11pt; text-transform: uppercase; letter-spacing: 0.5px; }
    .hdr-right .meta-date { color: #64748b; font-size: 7.2pt; margin-top: 2px; }
    .header img { height: 35px; object-fit: contain; }

    /* Botón de impresión flotante */
    .bp { position: fixed; top: 15px; right: 15px; background: #1a56db; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-size: 12px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 4px 14px rgba(26, 86, 219, 0.4); display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
    .bp:hover { background: #1e40af; transform: translateY(-1px); }

    /* KPIs Premium Box */
    .kpi-row { display: flex; gap: 8px; margin-bottom: 12px; }
    .kpi-card { flex: 1; border: 1px solid #e2e8f0; border-top: 3.5px solid #64748b; border-radius: 6px; padding: 7px 10px; text-align: center; background: #f8fafc; }
    .kpi-card.c-blue { border-top-color: #1a56db; background: #eff6ff; }
    .kpi-card.c-amber { border-top-color: #d97706; background: #fef9c3; }
    .kpi-card.c-indigo { border-top-color: #4f46e5; background: #eef2ff; }
    .kpi-card.c-green { border-top-color: #16a34a; background: #f0fdf4; }
    .kpi-card.c-emerald { border-top-color: #059669; background: #ecfdf5; }
    .kpi-card.c-rose { border-top-color: #db2777; background: #fdf2f8; }
    
    .kpi-val { font-size: 15pt; font-weight: 900; line-height: 1.1; margin-bottom: 1px; }
    .kpi-card.c-blue .kpi-val { color: #1e40af; }
    .kpi-card.c-amber .kpi-val { color: #854d0e; }
    .kpi-card.c-indigo .kpi-val { color: #3730a3; }
    .kpi-card.c-green .kpi-val { color: #166534; }
    .kpi-card.c-emerald .kpi-val { color: #065f46; }
    .kpi-card.c-rose .kpi-val { color: #9d174d; }

    .kpi-lbl { font-size: 6.2pt; color: #475569; font-weight: 800; text-transform: uppercase; }
    .kpi-pct { font-size: 7.2pt; font-weight: 700; color: #475569; margin-top: 1px; }

    /* Filtros Aplicados */
    .filtros-box { background: #f1f5f9; border-left: 3.5px solid #1a56db; padding: 5px 12px; border-radius: 4px; font-size: 7.2pt; color: #475569; margin-bottom: 12px; }

    /* Grid del Resumen Ejecutivo */
    .resumen-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 15px; }
    .resumen-col { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; background: #fff; }
    .resumen-titulo { font-size: 8pt; font-weight: 800; color: #0f172a; margin-bottom: 6px; border-left: 3px solid #1a56db; padding-left: 6px; text-transform: uppercase; }
    
    /* Tablas de Resumen */
    table.res-tbl { width: 100%; border-collapse: collapse; font-size: 7pt; }
    table.res-tbl td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; }
    table.res-tbl tr:last-child td { border-bottom: none; }
    table.res-tbl td.val { font-weight: 700; text-align: right; width: 45px; }
    table.res-tbl td.pct { color: #64748b; text-align: right; width: 45px; }
    
    /* Sección Titulo */
    .sec-titulo { font-size: 9.5pt; font-weight: 800; color: #0f172a; margin: 15px 0 6px; border-left: 3.5px solid #1a56db; padding-left: 8px; text-transform: uppercase; }

    /* Tabla Principal Calibrada al 100% */
    table.dt { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.dt th { background: #1e3a8a; color: #fff; font-size: 6.8pt; font-weight: 800; text-transform: uppercase; padding: 5px 5px; border: 1px solid #1d4ed8; text-align: left; }
    table.dt td { padding: 5px 5px; border: 1px solid #e2e8f0; font-size: 6.8pt; line-height: 1.3; vertical-align: top; word-break: break-word; }
    table.dt tr:nth-child(even) td { background: #f8fafc; }
    
    .estado-badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 6pt; font-weight: 800; text-align: center; text-transform: uppercase; width: 100%; }
    .tipo-badge { display: inline-block; padding: 1px 4px; border-radius: 4px; font-size: 5.8pt; font-weight: 700; }
    .tipo-badge.empresa { background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; }
    .tipo-badge.personal { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    .link-act { text-decoration: none; font-weight: 700; display: inline-block; }
    .link-act.orden { color: #ef4444; }
    .link-act.informe { color: #2563eb; }

    /* Notas y Aclaraciones */
    .nota-aclaratoria { margin-top: 8px; padding: 6px 12px; background: #fef9c3; border: 1px solid #fde047; border-radius: 5px; font-size: 7.2pt; color: #713f12; }

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
            <div class="rep-title">{{ $titulo }}</div>
            <div class="meta-date">Generado: {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="SGN - Novitec">
    </div>

    <!-- KPIs de Resumen -->
    <div class="kpi-row">
        <div class="kpi-card c-blue">
            <div class="kpi-val">{{ $total }}</div>
            <div class="kpi-lbl">Total órdenes</div>
            <div class="kpi-pct">100%</div>
        </div>
        <div class="kpi-card c-amber">
            <div class="kpi-val">{{ $cnt['Pendiente'] }}</div>
            <div class="kpi-lbl">Pendientes</div>
            <div class="kpi-pct">{{ $total > 0 ? round(($cnt['Pendiente'] / $total) * 100) . '%' : '0%' }}</div>
        </div>
        <div class="kpi-card c-indigo">
            <div class="kpi-val">{{ $cnt['En proceso'] }}</div>
            <div class="kpi-lbl">En proceso</div>
            <div class="kpi-pct">{{ $total > 0 ? round(($cnt['En proceso'] / $total) * 100) . '%' : '0%' }}</div>
        </div>
        <div class="kpi-card c-green">
            <div class="kpi-val">{{ $cnt['Finalizada'] }}</div>
            <div class="kpi-lbl">Finalizadas</div>
            <div class="kpi-pct">{{ $total > 0 ? round(($cnt['Finalizada'] / $total) * 100) . '%' : '0%' }}</div>
        </div>
        <div class="kpi-card c-emerald">
            <div class="kpi-val">{{ $cnt['Entregada'] }}</div>
            <div class="kpi-lbl">Entregadas (Tasa)</div>
            <div class="kpi-pct">{{ $tasa }}% tasa</div>
        </div>
        <div class="kpi-card c-rose">
            <div class="kpi-val">{{ $cnt['Nota de Credito'] }}</div>
            <div class="kpi-lbl">N. Créditos</div>
            <div class="kpi-pct">{{ $total > 0 ? round(($cnt['Nota de Credito'] / $total) * 100) . '%' : '0%' }}</div>
        </div>
    </div>

    <!-- Filtros Aplicados -->
    @if(count($filtrosTxt) > 0)
        <div class="filtros-box">
            <strong>Filtros activos:</strong> {{ implode('  •  ', $filtrosTxt) }}
        </div>
    @endif

    <!-- Resumen Ejecutivo Grid -->
    <div class="resumen-grid">
        <!-- Desglose de estados -->
        <div class="resumen-col">
            <div class="resumen-titulo">Estado de Órdenes</div>
            <table class="res-tbl">
                <tr style="background:#f8fafc;">
                    <td>Total órdenes registradas</td>
                    <td class="val">{{ $total }}</td>
                    <td class="pct">100%</td>
                </tr>
                <tr>
                    <td style="color:#854d0e;">Pendiente de revisión</td>
                    <td class="val" style="color:#854d0e;">{{ $cnt['Pendiente'] }}</td>
                    <td class="pct">{{ $total > 0 ? round(($cnt['Pendiente'] / $total) * 100) . '%' : '0%' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                    <td style="color:#1e40af;">En proceso de reparación</td>
                    <td class="val" style="color:#1e40af;">{{ $cnt['En proceso'] }}</td>
                    <td class="pct">{{ $total > 0 ? round(($cnt['En proceso'] / $total) * 100) . '%' : '0%' }}</td>
                </tr>
                <tr>
                    <td style="color:#166534;">Finalizada (Lista para entrega)</td>
                    <td class="val" style="color:#166534;">{{ $cnt['Finalizada'] }}</td>
                    <td class="pct">{{ $total > 0 ? round(($cnt['Finalizada'] / $total) * 100) . '%' : '0%' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                    <td style="color:#065f46;">Entregada al cliente</td>
                    <td class="val" style="color:#065f46;">{{ $cnt['Entregada'] }}</td>
                    <td class="pct">{{ $total > 0 ? round(($cnt['Entregada'] / $total) * 100) . '%' : '0%' }}</td>
                </tr>
                <tr>
                    <td style="color:#9d174d;">Nota de Crédito emitida</td>
                    <td class="val" style="color:#9d174d;">{{ $cnt['Nota de Credito'] }}</td>
                    <td class="pct">{{ $total > 0 ? round(($cnt['Nota de Credito'] / $total) * 100) . '%' : '0%' }}</td>
                </tr>
            </table>
        </div>

        <!-- Top Marcas -->
        <div class="resumen-col">
            <div class="resumen-titulo">Top Marcas Reportadas</div>
            <table class="res-tbl">
                @php $idxMarca = 0; @endphp
                @forelse($topMarcas as $marca => $cantMarca)
                    <tr style="background: {{ $idxMarca++ % 2 === 0 ? '#fff' : '#f8fafc' }};">
                        <td>{{ $marca ?: '(Sin especificar)' }}</td>
                        <td class="val" style="color:#4f46e5;">{{ $cantMarca }}</td>
                        <td class="pct">{{ $total > 0 ? round(($cantMarca / $total) * 100) . '%' : '0%' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:#94a3b8;">No hay datos de marcas</td></tr>
                @endforelse
            </table>
        </div>

        <!-- Top Tecnicos -->
        <div class="resumen-col">
            <div class="resumen-titulo">Top Técnicos por Carga</div>
            <table class="res-tbl">
                @php $idxTec = 0; @endphp
                @forelse($topTecnicos as $tecNombre => $cantTec)
                    <tr style="background: {{ $idxTec++ % 2 === 0 ? '#fff' : '#f8fafc' }};">
                        <td>{{ $tecNombre ?: '(Sin asignar)' }}</td>
                        <td class="val" style="color:#0f766e;">{{ $cantTec }}</td>
                        <td class="pct">{{ $total > 0 ? round(($cantTec / $total) * 100) . '%' : '0%' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:#94a3b8;">No hay técnicos registrados</td></tr>
                @endforelse
            </table>
        </div>
    </div>

    <!-- Detalle de Órdenes -->
    <div class="sec-titulo">Detalle General de Órdenes de Servicio</div>
    
    <table class="dt">
        <thead>
            <tr>
                <th style="width: 5.5%;">Nro. Orden</th>
                <th style="width: 6.5%;">F. Ingreso</th>
                <th style="width: 11%;">Cliente / Identificación</th>
                <th style="width: 11%;">Equipo / Marca / Serie</th>
                <th style="width: 8%;">Motivo</th>
                <th style="width: 7%;">Técnico</th>
                <th style="width: 4%;">Cant. Téc.</th>
                <th style="width: 8%;">Sucursal / CAS</th>
                <th style="width: 8%;">Repuesto / Garantía</th>
                <th style="width: 6.5%;">Estado</th>
                <th style="width: 3%;">Días</th>
                <th style="width: 6.5%;">Prometido / Entrega</th>
                <th style="width: 4%;">Doc.</th>
                <th style="width: 7%; text-align: right;">Cobro Novicompu</th>
                <th style="width: 7%; text-align: right;">Cobro RB-HEALTH</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $r)
                @php
                    $estEstilo = $estadoEstilos[$r['estado_orden']] ?? ['bg' => '#f1f5f9', 'fg' => '#475569'];
                    $rawId = str_replace('empresa-', '', (string) $r['id']);
                    
                    $pdfOrdenUrl = url($r['tipo_orden'] === 'empresa'
                        ? "/operaciones/ordenes-empresa/{$rawId}/imprimir"
                        : "/operaciones/ordenes/{$rawId}/imprimir"
                    );
                    $pdfInformeUrl = $r['informe_id']
                        ? url("/operaciones/informes/{$r['informe_id']}/imprimir")
                        : null;
                @endphp
                <tr>
                    <td style="font-family: monospace; font-weight: 700; color: #1a56db;">{{ $r['nro_orden'] }}</td>
                    <td style="white-space: nowrap;">{{ $r['fecha_de_ingreso'] }}</td>
                    <td>
                        <strong>{{ $r['cliente_nombre'] }}</strong><br>
                        <span style="color:#64748b; font-size:6.2pt;">C.I./RUC: {{ $r['identificacion'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $r['marca'] }}</strong> {{ str_replace($r['marca'], '', $r['equipo_nombre']) }}<br>
                        <span style="color:#64748b; font-size:6.2pt;">S/N: {{ $r['serie'] }} | Tipo: {{ $r['tipo_equipo'] }}</span>
                    </td>
                    <td>{{ $r['motivo_ingreso'] }}</td>
                    <td>{{ $r['tecnico_nombre'] }}</td>
                    <td style="text-align: center; font-weight: 700;">{{ $r['cantidad_tecnicos'] ?? 1 }}</td>
                    <td>
                        <strong>{{ $r['sucursal_nombre'] }}</strong>
                        @if(!empty($r['cas_nombre']) && $r['cas_nombre'] !== '-')
                            <br><span style="color:#64748b; font-size:6.2pt;">CAS: {{ $r['cas_nombre'] }}</span>
                        @endif
                    </td>
                    <td>
                        Rep: {{ $r['estado_repuesto'] }}
                        @if(!empty($r['estado_garantia']))
                            <br><span style="color:#64748b; font-size:6.2pt;">Gar: {{ $r['estado_garantia'] }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="estado-badge" style="background: {{ $estEstilo['bg'] }}; color: {{ $estEstilo['fg'] }};">
                            {{ $r['estado_orden'] }}
                        </span>
                        <div style="text-align: center; margin-top: 2px;">
                            <span class="tipo-badge {{ $r['tipo_orden'] }}">{{ ucfirst($r['tipo_orden']) }}</span>
                        </div>
                    </td>
                    <td style="text-align: center; font-weight: 700; color: {{ (int)$r['dias_transcurridos'] > 14 ? '#dc2626' : '#475569' }};">
                        {{ $r['dias_transcurridos'] }}d
                    </td>
                    <td style="white-space: nowrap; font-size: 6.2pt;">
                        P: {{ $r['fecha_prometido'] ?: '—' }}<br>
                        E: {{ $r['fecha_entrega'] ?: '—' }}
                    </td>
                    <td style="text-align: center; font-size: 6.2pt;">
                        <a href="{{ $pdfOrdenUrl }}" target="_blank" class="link-act orden">Orden</a><br>
                        @if($pdfInformeUrl)
                            <a href="{{ $pdfInformeUrl }}" target="_blank" class="link-act informe">Informe</a>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 700; font-family: monospace; font-size: 7.5pt; color: {{ (float)($r['valor_novicompu'] ?? 0) > 0 ? '#166534' : '#64748b' }};">
                        ${{ number_format($r['valor_novicompu'] ?? 0, 2) }}
                    </td>
                    <td style="text-align: right; font-weight: 700; font-family: monospace; font-size: 7.5pt; color: {{ (float)($r['valor_otra_empresa'] ?? 0) > 0 ? '#166534' : '#64748b' }};">
                        ${{ number_format($r['valor_otra_empresa'] ?? 0, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" style="text-align: center; color: #94a3b8; padding: 15px;">
                        No se encontraron registros con los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Nota Aclaratoria sobre Descuento a Novicompu -->
    <div class="nota-aclaratoria">
        <strong>* NOTA SOBRE VALOR A COBRAR A NOVICOMPU:</strong> El valor reflejado aplica únicamente para órdenes en estado de <strong>Validación de Garantía</strong>. Corresponde al total acumulado de revisión estándar ($28.00) más repuestos/adicionales, incluyendo el 15% de IVA (total de $32.20 para revisiones estándar básicas), afectado por un <strong>descuento especial del 40% a Novicompu</strong> (es decir, el 60% neto del total con IVA facturable a Novicompu).
    </div>

    <!-- Pie de página -->
    <div class="foot">
        Novitecnología Cía. Ltda. &nbsp;•&nbsp; Sistema de Gestión de Servicio SGN &nbsp;•&nbsp; Impreso el {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>

</body>
</html>
