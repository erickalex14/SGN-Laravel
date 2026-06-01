@extends('layouts.app')
@section('titulo', 'Auditoría de Repuestos')

@push('css_adicional')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    /* ═══════════════════════════════════════════════════
       AUDITORÍA DE INVENTARIO — SGN Premium Theme
    ═══════════════════════════════════════════════════ */
    .aud-wrap { max-width: 1420px; margin: 0 auto; padding: 24px 20px; font-family: 'Inter', system-ui, sans-serif; }
    
    .aud-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
    .aud-hdr-text h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .aud-hdr-text p { margin: 0; color: #64748b; font-size: 14px; }
    
    .btn-back { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; padding: 8px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
    .btn-back:hover { background: #e2e8f0; color: #0f172a; }

    /* ── KPIs ── */
    .aud-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .aud-kpi { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; border-top: 4px solid transparent; padding: 18px 16px; text-align: center; box-shadow: 0 1px 6px rgba(0,0,0,.04); transition: box-shadow .2s, transform .2s; }
    .aud-kpi:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); transform: translateY(-2px); }
    .aud-kpi i { font-size: 24px; display: block; margin-bottom: 6px; }
    .aud-kpi-val { font-size: 26px; font-weight: 900; color: #0f172a; line-height: 1.1; word-break: break-all; }
    .aud-kpi-lbl { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-top: 5px; }
    .aud-kpi.c-indigo { border-top-color: #6366f1; } .aud-kpi.c-indigo i { color: #6366f1; }
    .aud-kpi.c-green { border-top-color: #10b981; } .aud-kpi.c-green i { color: #10b981; }
    .aud-kpi.c-amber { border-top-color: #f59e0b; } .aud-kpi.c-amber i { color: #f59e0b; }
    .aud-kpi.c-blue { border-top-color: #3b82f6; } .aud-kpi.c-blue i { color: #3b82f6; }

    /* ── Card & Filtros ── */
    .aud-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 16px rgba(0,0,0,.04); margin-bottom: 24px; overflow: hidden; }
    .aud-card-head { display: flex; align-items: center; gap: 8px; padding: 14px 20px; background: linear-gradient(135deg,#f8fafc,#f1f5f9); border-bottom: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: .05em; }
    .ch-right { margin-left: auto; display: flex; gap: 8px; align-items: center; }

    .aud-filtros-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; padding: 20px 20px 0; }
    .aud-campo { display: flex; flex-direction: column; gap: 6px; }
    .aud-campo label { font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .05em; }
    .aud-campo select, .aud-campo input[type=date] { border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 13.5px; color: #0f172a; background: #fff; transition: border-color .2s, box-shadow .2s; outline:none; }
    .aud-campo select:focus, .aud-campo input[type=date]:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
    
    .aud-btns-row { display: flex; gap: 10px; padding: 16px 20px 20px; flex-wrap: wrap; align-items: center; border-bottom: 1px dashed #e2e8f0; }
    .btn-aud { display: inline-flex; align-items: center; gap: 6px; border: none; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .15s; white-space: nowrap; text-decoration: none; }
    .btn-aud:hover { transform: translateY(-1px); }
    .btn-aud:active { transform: translateY(0); }
    .btn-aud-primary { background: linear-gradient(135deg,#4f46e5,#4338ca); color: #fff; box-shadow: 0 3px 12px rgba(79,70,229,.3); }
    .btn-aud-primary:hover { background: #4338ca; box-shadow: 0 5px 16px rgba(79,70,229,.4); }
    .btn-aud-ghost { background: #f8fafc; color: #475569; border: 1.5px solid #cbd5e1; }
    .btn-aud-ghost:hover { background: #f1f5f9; color: #0f172a; }
    .btn-aud-green { background: linear-gradient(135deg,#10b981,#059669); color: #fff; }
    .btn-aud-dark { background: #0f172a; color: #fff; }
    .btn-aud-dark:hover { background: #1e293b; }
    
    .input-search-box { border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; width: 230px; font-family: inherit; transition: border-color .2s; outline: none; }
    .input-search-box:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); }

    /* ── Tabla ── */
    .aud-tbl-outer { overflow-x: auto; }
    .aud-tbl { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .aud-tbl th { background: #f8fafc; padding: 12px 16px; font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; border-bottom: 2px solid #e2e8f0; white-space: nowrap; cursor: pointer; user-select: none; }
    .aud-tbl th:hover { background: #f1f5f9; color: #4f46e5; }
    .aud-tbl td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
    .aud-tbl tr:last-child td { border-bottom: none; }
    .aud-tbl tr:hover td { background: #f8fbff; }
    
    .aud-code { font-family: monospace; font-weight: 700; color: #b45309; font-size: 13px; }
    .aud-nro-orden { font-family: monospace; font-weight: 800; color: #4f46e5; text-decoration: none; }
    .aud-nro-orden:hover { text-decoration: underline; }
    
    .aud-pagination { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; border-top: 1px solid #f1f5f9; font-size: 12.5px; color: #64748b; flex-wrap: wrap; gap: 8px; }
    .aud-pag-btns { display: flex; gap: 4px; }
    .aud-pag-btn { border: 1.5px solid #cbd5e1; background: #fff; color: #475569; border-radius: 6px; padding: 5px 12px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .aud-pag-btn:hover, .aud-pag-btn.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
    .aud-pag-btn:disabled { opacity: .4; cursor: not-allowed; }

    .aud-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px; color: #94a3b8; text-align: center; gap: 10px; }
    .aud-empty i { font-size: 48px; }
    .aud-empty h4 { font-size: 16px; font-weight: 700; color: #64748b; margin: 0; }
    .aud-empty p { font-size: 13px; margin: 0; }

    /* ── IMPRESIÓN / PRINT CSS ── */
    @media print {
        header, footer, nav, aside, .btn-back, .aud-card-head, .aud-filtros-grid, .aud-btns-row, .aud-pagination, #buscador-container {
            display: none !important;
        }
        body, .aud-wrap {
            background: #fff !important;
            color: #000 !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 11px !important;
        }
        .aud-wrap {
            max-width: 100% !important;
        }
        .aud-hdr {
            border-bottom: 2px solid #000 !important;
            margin-bottom: 15px !important;
            padding-bottom: 8px !important;
        }
        .aud-kpis {
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 10px !important;
            margin-bottom: 15px !important;
        }
        .aud-kpi {
            border: 1px solid #000 !important;
            padding: 10px !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            transform: none !important;
            background: #fcfcfc !important;
        }
        .aud-kpi i {
            display: none !important;
        }
        .aud-kpi-val {
            font-size: 18px !important;
        }
        .aud-card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            border-radius: 8px !important;
        }
        .aud-tbl {
            font-size: 10px !important;
        }
        .aud-tbl th {
            background: #f0f0f0 !important;
            color: #000 !important;
            border-bottom: 1.5px solid #000 !important;
            padding: 6px 8px !important;
        }
        .aud-tbl td {
            padding: 6px 8px !important;
            border-bottom: 1px solid #ccc !important;
        }
        .aud-nro-orden {
            color: #000 !important;
            font-weight: 700 !important;
        }
    }
</style>
@endpush

@section('contenido')
<div class="aud-wrap">

    {{-- Encabezado --}}
    <div class="aud-hdr">
        <div class="aud-hdr-text">
            <h2><i class="bi bi-shield-check" style="color:#4f46e5;"></i> Auditoría de Repuestos</h2>
            <p>Monitoreo detallado de stock restado de bodega, asignaciones y consumo en órdenes de servicio.</p>
        </div>
        <div>
            <a href="{{ route('repuestos.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Catálogo de Repuestos
            </a>
        </div>
    </div>

    {{-- PHP de Cálculo de Métricas KPIs --}}
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

    {{-- KPIs Dashboard --}}
    <div class="aud-kpis">
        <div class="aud-kpi c-indigo">
            <i class="bi bi-boxes"></i>
            <div class="aud-kpi-val">{{ $totalItems }} uds</div>
            <div class="aud-kpi-lbl">Repuestos Utilizados</div>
        </div>
        <div class="aud-kpi c-green">
            <i class="bi bi-currency-dollar"></i>
            <div class="aud-kpi-val">${{ number_format($totalCosto, 2) }}</div>
            <div class="aud-kpi-lbl">Costo Total de Salidas</div>
        </div>
        <div class="aud-kpi c-amber">
            <i class="bi bi-star"></i>
            <div class="aud-kpi-val" style="font-size:15px; font-weight:800; padding:4px 0;" title="{{ $repuestoMasUsado }}">
                {{ strlen($repuestoMasUsado) > 28 ? substr($repuestoMasUsado, 0, 26) . '...' : $repuestoMasUsado }}
            </div>
            <div class="aud-kpi-lbl">Repuesto Más Usado ({{ $repuestoMasUsadoCant }} uds)</div>
        </div>
        <div class="aud-kpi c-blue">
            <i class="bi bi-person-check"></i>
            <div class="aud-kpi-val" style="font-size:15px; font-weight:800; padding:4px 0;" title="{{ $tecnicoLider }}">
                {{ strlen($tecnicoLider) > 28 ? substr($tecnicoLider, 0, 26) . '...' : $tecnicoLider }}
            </div>
            <div class="aud-kpi-lbl">Técnico Más Activo ({{ $tecnicoLiderCant }} uds)</div>
        </div>
    </div>

    {{-- Tarjeta de Filtros --}}
    <div class="aud-card">
        <div class="aud-card-head">
            <i class="bi bi-funnel"></i> Filtros de Auditoría
            <div class="ch-right">
                <a href="{{ route('repuestos.auditoria') }}" class="btn-aud btn-aud-sm btn-aud-ghost" style="padding: 5px 12px; font-size:11.5px;">
                    <i class="bi bi-x-circle"></i> Limpiar Filtros
                </a>
            </div>
        </div>
        <form method="GET" action="{{ route('repuestos.auditoria') }}">
            <div class="aud-filtros-grid">
                <div class="aud-campo">
                    <label>Repuesto Específico</label>
                    <select name="repuesto_id" onchange="this.form.submit()">
                        <option value="">-- Todos los Repuestos --</option>
                        @foreach($repuestosList as $rl)
                            <option value="{{ $rl->id }}" {{ request('repuesto_id') == $rl->id ? 'selected' : '' }}>
                                {{ $rl->codigo }} - {{ $rl->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="aud-campo">
                    <label>Técnico / Responsable</label>
                    <select name="usuario_id" onchange="this.form.submit()">
                        <option value="">-- Todos los Técnicos --</option>
                        @foreach($tecnicosList as $tl)
                            <option value="{{ $tl->id }}" {{ request('usuario_id') == $tl->id ? 'selected' : '' }}>
                                {{ $tl->nombre_tecnico ?: $tl->usuario }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="aud-campo">
                    <label>Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" onchange="this.form.submit()">
                </div>
                <div class="aud-campo">
                    <label>Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" onchange="this.form.submit()">
                </div>
            </div>
            
            <div class="aud-btns-row">
                <button type="submit" class="btn-aud btn-aud-primary">
                    <i class="bi bi-funnel-fill"></i> Aplicar Filtros
                </button>
                <button type="button" class="btn-aud btn-aud-dark" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> Imprimir Reporte (PDF)
                </button>
                <button type="button" class="btn-aud btn-aud-green" onclick="exportarExcel()">
                    <i class="bi bi-file-earmark-excel-fill"></i> Descargar XLSX
                </button>
                <button type="button" class="btn-aud btn-aud-ghost" onclick="exportarCSV()">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Descargar CSV
                </button>
            </div>
        </form>

        {{-- Resultados de la Grilla --}}
        @if($auditorias->isNotEmpty())
            <div class="aud-card-head" style="border-top: 1px solid #e2e8f0; border-bottom: 2px solid #e2e8f0;" id="buscador-container">
                <i class="bi bi-table"></i> Historial de Movimientos de Stock
                <div class="ch-right">
                    <input type="text" class="input-search-box" id="aud-buscador" placeholder="🔍 Buscar en tabla..." oninput="filtrarTablaLocal(this.value)">
                </div>
            </div>
            
            <div class="aud-tbl-outer">
                <table class="aud-tbl" id="aud-tabla">
                    <thead>
                        <tr>
                            <th onclick="sortTablaLocal(0, 'fecha')">Fecha / Hora</th>
                            <th onclick="sortTablaLocal(1, 'codigo')">Código</th>
                            <th onclick="sortTablaLocal(2, 'nombre')">Nombre del Repuesto</th>
                            <th onclick="sortTablaLocal(3, 'tecnico')">Usuario / Técnico</th>
                            <th onclick="sortTablaLocal(4, 'orden')">Orden Relacionada</th>
                            <th onclick="sortTablaLocal(5, 'tipo_orden')">Tipo de Orden</th>
                            <th style="text-align:center;" onclick="sortTablaLocal(6, 'cantidad')">Cant</th>
                            <th style="text-align:right;" onclick="sortTablaLocal(7, 'costo_u')">Costo Unit. ($)</th>
                            <th style="text-align:right;" onclick="sortTablaLocal(8, 'costo_t')">Costo Total ($)</th>
                        </tr>
                    </thead>
                    <tbody id="aud-tbody">
                        @foreach($auditorias as $a)
                            @php
                                $fechaHora = \Carbon\Carbon::parse($a->fecha)->format('d/m/Y H:i');
                                $tecnicoNombre = $a->usuario->nombre_tecnico ?? $a->orden->tecnico->nombre_tecnico ?? 'N/A';
                                $costoUnit = $a->repuesto->costo ?? 0;
                                $costoTotal = $costoUnit * $a->cantidad;
                                $tipoOrden = $a->orden->motivo_ingreso ?? 'N/A';
                                
                                // Estilos dinámicos premium para el tipo de orden
                                $badgeStyle = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                                if ($tipoOrden === 'Servicio Cliente Externo') {
                                    $badgeStyle = 'background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;';
                                } elseif ($tipoOrden === 'Validacion de Garantia' || $tipoOrden === 'Validación de Garantía') {
                                    $badgeStyle = 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
                                } elseif ($tipoOrden === 'Servicios a Empresas') {
                                    $badgeStyle = 'background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff;';
                                }
                            @endphp
                            <tr data-row="auditoria" data-fila="{{ json_encode([
                                'fecha' => $a->fecha,
                                'codigo' => $a->repuesto->codigo ?? '',
                                'nombre' => $a->repuesto->nombre ?? '',
                                'tecnico' => $tecnicoNombre,
                                'orden' => $a->orden->nro_orden ?? '',
                                'tipo_orden' => $tipoOrden,
                                'cantidad' => $a->cantidad,
                                'costo_u' => $costoUnit,
                                'costo_t' => $costoTotal
                            ]) }}">
                                <td style="font-size:12px; white-space:nowrap;">{{ $fechaHora }}</td>
                                <td class="aud-code">{{ $a->repuesto->codigo ?? '-' }}</td>
                                <td style="font-weight:500;">{{ $a->repuesto->nombre ?? '-' }}</td>
                                <td>{{ $tecnicoNombre }}</td>
                                <td>
                                    @if($a->orden)
                                        <a href="{{ route('ordenes.imprimir', ['id' => $a->orden->id]) }}" target="_blank" class="aud-nro-orden" title="Imprimir Comprobante OT">
                                            <i class="bi bi-printer me-1"></i>{{ $a->orden->nro_orden }}
                                        </a>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; {{ $badgeStyle }}">
                                        {{ $tipoOrden }}
                                    </span>
                                </td>
                                <td style="text-align:center; font-weight:700;">{{ $a->cantidad }}</td>
                                <td style="text-align:right; color:#475569;">${{ number_format($costoUnit, 2) }}</td>
                                <td style="text-align:right; font-weight:700; color:#0f172a;">${{ number_format($costoTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="auditoria-pager" style="padding: 10px 20px 20px;"></div>
        @else
            <div class="aud-empty">
                <i class="bi bi-journal-x" style="color:#cbd5e1;"></i>
                <h4>Sin registros de auditoría</h4>
                <p>No se encontraron movimientos de stock en el inventario con los filtros seleccionados.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('js_adicional')
<script>
    let _allRows = [];
    let _filteredRows = [];
    let _sortCol = -1;
    let _sortDir = 1;
    let _audPager = null;

    function escHtml(str) {
        return (str || '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initTabla() {
        const tbody = document.getElementById('aud-tbody');
        if (!tbody) return;

        const trs = tbody.querySelectorAll('tr[data-row="auditoria"]');
        _allRows = Array.from(trs).map(tr => {
            const data = JSON.parse(tr.getAttribute('data-fila') || '{}');
            return {
                element: tr,
                data: data
            };
        });
        _filteredRows = _allRows.slice();
        
        _audPager = new SgnPager({
            containerSelector: '#aud-tbody',
            itemSelector: 'tr[data-row="auditoria"]',
            pagerContainerSelector: '#auditoria-pager',
            pageSize: 15
        });
    }

    window.filtrarTablaLocal = function(q) {
        q = q.toLowerCase().trim();
        if (!q) {
            _filteredRows = _allRows.slice();
        } else {
            _filteredRows = _allRows.filter(r => {
                return Object.values(r.data).join(' ').toLowerCase().includes(q);
            });
        }
        _allRows.forEach(r => {
            if (_filteredRows.includes(r)) {
                r.element.style.display = '';
            } else {
                r.element.style.display = 'none';
            }
        });
    };

    window.sortTablaLocal = function(col, key) {
        if (_sortCol === col) {
            _sortDir *= -1;
        } else {
            _sortCol = col;
            _sortDir = 1;
        }

        _filteredRows.sort((a, b) => {
            let valA = a.data[key];
            let valB = b.data[key];

            if (typeof valA === 'number' && typeof valB === 'number') {
                return (valA - valB) * _sortDir;
            }
            return String(valA).localeCompare(String(valB), 'es') * _sortDir;
        });

        // Reordenar elementos reales en el DOM
        const tbody = document.getElementById('aud-tbody');
        if (tbody) {
            _filteredRows.forEach(r => tbody.appendChild(r.element));
        }

        // Agregar indicadores a las cabeceras
        document.querySelectorAll('.aud-tbl th').forEach((th, i) => {
            th.innerHTML = th.innerHTML.replace(/ [▲▼]/g, '');
            if (i === col) {
                th.innerHTML += _sortDir === 1 ? ' ▲' : ' ▼';
            }
        });

        if (_audPager) {
            _audPager.currentPage = 1;
            _audPager.render();
        }
    };

    // Exportador Nativo a CSV
    window.exportarCSV = function() {
        if (!_filteredRows.length) {
            alert('No hay datos para exportar.');
            return;
        }

        let csv = '\uFEFF'; // UTF-8 BOM
        csv += 'Fecha / Hora,Código,Nombre del Repuesto,Usuario / Técnico,Orden de Servicio,Tipo de Orden,Cantidad,Costo Unit ($),Costo Total ($)\r\n';

        _filteredRows.forEach(r => {
            const d = r.data;
            const fecha = new Date(d.fecha).toLocaleString('es-EC').replace(',', '');
            const fila = [
                `"${fecha}"`,
                `"${d.codigo.replace(/"/g, '""')}"`,
                `"${d.nombre.replace(/"/g, '""')}"`,
                `"${d.tecnico.replace(/"/g, '""')}"`,
                `"${d.orden || 'N/A'}"`,
                `"${(d.tipo_orden || 'N/A').replace(/"/g, '""')}"`,
                d.cantidad,
                d.costo_u.toFixed(2),
                d.costo_t.toFixed(2)
            ];
            csv += fila.join(',') + '\r\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'Auditoria_Stock_Repuestos_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Exportador Nativo a XLSX (Genera un XML Spreadsheet compatible con Excel)
    window.exportarExcel = function() {
        if (!_filteredRows.length) {
            alert('No hay datos para exportar.');
            return;
        }

        const now = new Date().toISOString().slice(0, 10);
        let xml = '<?xml version="1.0" encoding="utf-8"?>\r\n';
        xml += '<?mso-application progid="Excel.Sheet"?>\r\n';
        xml += '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"\r\n';
        xml += ' xmlns:o="urn:schemas-microsoft-com:office:office"\r\n';
        xml += ' xmlns:x="urn:schemas-microsoft-com:office:excel"\r\n';
        xml += ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"\r\n';
        xml += ' xmlns:html="http://www.w3.org/TR/REC-html40">\r\n';
        xml += ' <Styles>\r\n';
        xml += '  <Style ss:ID="Default" ss:Name="Normal">\r\n';
        xml += '   <Alignment ss:Vertical="Bottom"/>\r\n';
        xml += '   <Borders/>\r\n';
        xml += '   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>\r\n';
        xml += '   <Interior/>\r\n';
        xml += '   <NumberFormat/>\r\n';
        xml += '   <Protection/>\r\n';
        xml += '  </Style>\r\n';
        xml += '  <Style ss:ID="Header">\r\n';
        xml += '   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>\r\n';
        xml += '   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>\r\n';
        xml += '   <Interior ss:Color="#4F46E5" ss:Pattern="Solid"/>\r\n';
        xml += '  </Style>\r\n';
        xml += '  <Style ss:ID="Code">\r\n';
        xml += '   <Font ss:FontName="Courier New" x:Family="Modern" ss:Size="10" ss:Color="#B45309" ss:Bold="1"/>\r\n';
        xml += '  </Style>\r\n';
        xml += '  <Style ss:ID="Currency">\r\n';
        xml += '   <NumberFormat ss:Format="$#,##0.00"/>\r\n';
        xml += '  </Style>\r\n';
        xml += ' </Styles>\r\n';
        xml += ' <Worksheet ss:Name="Auditoria Repuestos">\r\n';
        xml += '  <Table>\r\n';
        xml += '   <Column ss:Width="140"/>\r\n';
        xml += '   <Column ss:Width="90"/>\r\n';
        xml += '   <Column ss:Width="220"/>\r\n';
        xml += '   <Column ss:Width="160"/>\r\n';
        xml += '   <Column ss:Width="100"/>\r\n';
        xml += '   <Column ss:Width="160"/>\r\n';
        xml += '   <Column ss:Width="50"/>\r\n';
        xml += '   <Column ss:Width="80"/>\r\n';
        xml += '   <Column ss:Width="90"/>\r\n';
        
        // Headers
        xml += '   <Row ss:Height="24">\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Fecha / Hora</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Código</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Nombre del Repuesto</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Usuario / Técnico</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Orden de Servicio</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tipo de Orden</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Cant</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Costo Unit ($)</Data></Cell>\r\n';
        xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String">Costo Total ($)</Data></Cell>\r\n';
        xml += '   </Row>\r\n';

        _filteredRows.forEach(r => {
            const d = r.data;
            const fecha = new Date(d.fecha).toLocaleString('es-EC');
            xml += '   <Row>\r\n';
            xml += '    <Cell><Data ss:Type="String">' + escHtml(fecha) + '</Data></Cell>\r\n';
            xml += '    <Cell ss:StyleID="Code"><Data ss:Type="String">' + escHtml(d.codigo) + '</Data></Cell>\r\n';
            xml += '    <Cell><Data ss:Type="String">' + escHtml(d.nombre) + '</Data></Cell>\r\n';
            xml += '    <Cell><Data ss:Type="String">' + escHtml(d.tecnico) + '</Data></Cell>\r\n';
            xml += '    <Cell><Data ss:Type="String">' + escHtml(d.orden || 'N/A') + '</Data></Cell>\r\n';
            xml += '    <Cell><Data ss:Type="String">' + escHtml(d.tipo_orden || 'N/A') + '</Data></Cell>\r\n';
            xml += '    <Cell><Data ss:Type="Number">' + d.cantidad + '</Data></Cell>\r\n';
            xml += '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' + d.costo_u + '</Data></Cell>\r\n';
            xml += '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' + d.costo_t + '</Data></Cell>\r\n';
            xml += '   </Row>\r\n';
        });

        xml += '  </Table>\r\n';
        xml += ' </Worksheet>\r\n';
        xml += '</Workbook>\r\n';

        const blob = new Blob([xml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'Auditoria_Stock_Repuestos_' + now + '.xls');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    document.addEventListener('DOMContentLoaded', () => {
        initTabla();
    });
</script>
@endpush
