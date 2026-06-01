@extends('layouts.app')
@section('titulo', 'Reportes — SGN')

@push('css_adicional')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════
   REPORTES ENTERPRISE — SGN Novitecnología
═══════════════════════════════════════════════════ */

.rep-wrap { max-width: 1420px; margin: 0 auto; padding: 24px 20px; font-family: 'Inter', system-ui, sans-serif; }

/* ── Encabezado ── */
.rep-hero { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
.rep-hero-left h2 { margin: 0 0 3px; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.rep-hero-left p { margin: 0; color: #64748b; font-size: 13px; }
.rep-hero-badge { background: linear-gradient(135deg,#dbeafe,#eff6ff); border: 1px solid #bfdbfe; border-radius: 20px; padding: 4px 14px; font-size: 11px; font-weight: 700; color: #1e40af; display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; }

/* ── Cards ── */
.rep-card { background: #fff; border-radius: 16px; border: 1px solid #e8edf3; box-shadow: 0 2px 16px rgba(0,0,0,.05); margin-bottom: 18px; overflow: hidden; }
.rep-card-head { display: flex; align-items: center; gap: 8px; padding: 13px 20px; background: linear-gradient(135deg,#f0f6ff,#e4effe); border-bottom: 1px solid #c7d8f5; font-size: 12px; font-weight: 800; color: #1a40af; text-transform: uppercase; letter-spacing: .05em; flex-wrap: wrap; }
.rep-card-head .ch-right { margin-left: auto; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

/* ── Filtros ── */
.rep-filtros-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(175px,1fr)); gap: 12px; padding: 18px 20px 0; }
.rep-campo { display: flex; flex-direction: column; gap: 4px; }
.rep-campo label { font-size: 10.5px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em; }
.rep-campo select, .rep-campo input[type=date] {
  border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; font-size: 13px;
  color: #0f172a; background: #f8fafc; font-family: inherit;
  transition: border-color .2s, box-shadow .2s;
}
.rep-campo select:focus, .rep-campo input[type=date]:focus {
  outline: none; border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.12);
}
.filter-active { border-color: #2563eb !important; background: #eff6ff !important; }

/* Pills filtros activos */
.rep-pills { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 20px 0; }
.rep-pill { background: #dbeafe; color: #1e40af; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
.rep-pill button { background: none; border: none; cursor: pointer; color: #1e40af; font-size: 15px; line-height: 1; padding: 0; }

/* ── Botones ── */
.rep-btns-row { display: flex; gap: 8px; padding: 14px 20px 18px; flex-wrap: wrap; align-items: center; }
.rep-btn { display: inline-flex; align-items: center; gap: 6px; border: none; padding: 9px 16px; border-radius: 9px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .15s; white-space: nowrap; }
.rep-btn:hover { transform: translateY(-1px); }
.rep-btn:active { transform: translateY(0); }
.rep-btn:disabled { opacity: .55; cursor: not-allowed; transform: none !important; }
.rep-btn-primary { background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; box-shadow: 0 3px 12px rgba(37,99,235,.35); }
.rep-btn-primary:hover { box-shadow: 0 5px 18px rgba(37,99,235,.45); }
.rep-btn-ghost { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
.rep-btn-ghost:hover { background: #e2e8f0; }
.rep-btn-dark { background: #0f172a; color: #fff; }
.rep-btn-dark:hover { background: #1e293b; }
.rep-btn-green { background: linear-gradient(135deg,#059669,#047857); color: #fff; box-shadow: 0 3px 10px rgba(5,150,105,.3); }
.rep-btn-green:hover { box-shadow: 0 5px 15px rgba(5,150,105,.4); }
.rep-btn-teal { background: linear-gradient(135deg,#0891b2,#0e7490); color: #fff; }
.rep-btn-sm { padding: 6px 12px; font-size: 11.5px; }
.rep-divider-v { width: 1px; height: 28px; background: #e2e8f0; margin: 0 2px; }

/* ── Spinner ── */
.rep-spinner { display: none; align-items: center; justify-content: center; padding: 50px; gap: 14px; color: #2563eb; font-size: 14px; font-weight: 600; }
.rep-spinner.show { display: flex; }
.spin-ring { width: 28px; height: 28px; border: 3px solid #dbeafe; border-top-color: #2563eb; border-radius: 50%; animation: repSpin .7s linear infinite; }
@keyframes repSpin { to { transform: rotate(360deg); } }

/* ── KPIs ── */
.rep-kpis { display: grid; grid-template-columns: repeat(6,1fr); gap: 12px; margin-bottom: 18px; }
.rep-kpi { background: #fff; border: 1px solid #e8edf3; border-radius: 14px; border-top: 3px solid transparent; padding: 16px; text-align: center; transition: box-shadow .2s, transform .2s; cursor: default; }
.rep-kpi:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); transform: translateY(-2px); }
.rep-kpi i { font-size: 22px; display: block; margin-bottom: 6px; }
.rep-kpi-val { font-size: 28px; font-weight: 900; color: #0f172a; line-height: 1; }
.rep-kpi-lbl { font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; }
.rep-kpi-pct { font-size: 12px; font-weight: 700; margin-top: 3px; }
.rep-kpi.c-blue { border-top-color: #2563eb; } .rep-kpi.c-blue i, .rep-kpi.c-blue .rep-kpi-pct { color: #2563eb; }
.rep-kpi.c-amber { border-top-color: #f59e0b; } .rep-kpi.c-amber i, .rep-kpi.c-amber .rep-kpi-pct { color: #f59e0b; }
.rep-kpi.c-indigo { border-top-color: #6366f1; } .rep-kpi.c-indigo i, .rep-kpi.c-indigo .rep-kpi-pct { color: #6366f1; }
.rep-kpi.c-violet { border-top-color: #8b5cf6; } .rep-kpi.c-violet i, .rep-kpi.c-violet .rep-kpi-pct { color: #8b5cf6; }
.rep-kpi.c-green { border-top-color: #10b981; } .rep-kpi.c-green i, .rep-kpi.c-green .rep-kpi-pct { color: #10b981; }
.rep-kpi.c-rose { border-top-color: #f43f5e; } .rep-kpi.c-rose i, .rep-kpi.c-rose .rep-kpi-pct { color: #f43f5e; }

/* ── Gráficos ── */
.rep-charts-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.rep-charts-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.rep-chart-card { background: #fff; border: 1px solid #e8edf3; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.04); }
.rep-chart-title { font-size: 12px; font-weight: 700; color: #1e40af; padding: 12px 16px 0; display: flex; align-items: center; gap: 7px; }
.rep-chart-body { padding: 10px 16px 14px; height: 220px; }
.rep-chart-body canvas { max-height: 100%; }

/* ── Stats row ── */
.rep-stats-row { display: flex; flex-wrap: wrap; gap: 8px; padding: 12px 18px; border-top: 1px solid #f1f5f9; background: #fafbfc; }
.rep-stat-chip { background: #f1f5f9; border-radius: 8px; padding: 5px 11px; font-size: 11.5px; color: #374151; display: flex; align-items: center; gap: 5px; }
.rep-stat-chip b { color: #1e40af; }

/* ── Tabla ── */
.rep-tbl-outer { overflow-x: auto; }
.rep-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
.rep-tbl th { background: #f8fafc; padding: 9px 12px; text-align: left; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; border-bottom: 2px solid #e2e8f0; white-space: nowrap; cursor: pointer; user-select: none; }
.rep-tbl th:hover { background: #f1f5f9; color: #1e40af; }
.rep-tbl th.sort-asc::after { content: ' ▲'; opacity: .7; font-size: 9px; }
.rep-tbl th.sort-desc::after { content: ' ▼'; opacity: .7; font-size: 9px; }
.rep-tbl td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #1e293b; white-space: nowrap; }
.rep-tbl tr:last-child td { border-bottom: none; }
.rep-tbl tr:hover td { background: #f8fbff; }
.rep-nro { font-family: 'Courier New', monospace; font-weight: 800; color: #2563eb; font-size: 13px; }
.vencida-row td { background: #fef2f2 !important; }

/* Estado badges */
.estado-badge { display: inline-block; padding: 2px 9px; border-radius: 10px; font-size: 10.5px; font-weight: 700; }
.tipo-badge { display: inline-block; padding: 1px 7px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.tipo-badge.personal { background: #f0fdf4; color: #166534; }
.tipo-badge.empresa { background: #eff6ff; color: #1e40af; }

/* ── Paginación ── */
.rep-pagination { display: flex; align-items: center; justify-content: space-between; padding: 11px 16px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #64748b; flex-wrap: wrap; gap: 8px; }
.rep-pag-btns { display: flex; gap: 3px; }
.rep-pag-btn { border: 1.5px solid #e2e8f0; background: #fff; color: #475569; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
.rep-pag-btn:hover, .rep-pag-btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
.rep-pag-btn:disabled { opacity: .4; cursor: not-allowed; }

/* ── Buscar ── */
.rep-search-box { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 12px; font-size: 12px; width: 220px; font-family: inherit; transition: border-color .2s; }
.rep-search-box:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

/* ── Empty ── */
.rep-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 52px 24px; color: #94a3b8; text-align: center; gap: 10px; }
.rep-empty i { font-size: 48px; }
.rep-empty h4 { font-size: 16px; font-weight: 700; color: #64748b; margin: 0; }
.rep-empty p { font-size: 13px; margin: 0; }

/* ── Media queries ── */
@media(max-width:1200px) { .rep-charts-3 { grid-template-columns: 1fr 1fr; } }
@media(max-width:960px) { .rep-charts-2, .rep-charts-3 { grid-template-columns: 1fr; } .rep-kpis { grid-template-columns: repeat(3,1fr); } }
@media(max-width:600px) { .rep-kpis { grid-template-columns: repeat(2,1fr); } .rep-filtros-grid { grid-template-columns: 1fr 1fr; } }
</style>
@endpush

@section('contenido')
<div class="rep-wrap">

    {{-- ════ ENCABEZADO ════ --}}
    <div class="rep-hero">
        <div class="rep-hero-left">
            <h2>
                <i class="bi bi-bar-chart-line-fill" style="color:#2563eb;"></i>
                Reportes Operativos
            </h2>
            <p>Estadísticas en tiempo real · KPIs · Gráficos interactivos · Exportación enterprise</p>
            @if($esMaster)
                <span class="rep-hero-badge"><i class="bi bi-globe2"></i> Administrador Master — todas las sucursales</span>
            @else
                <span class="rep-hero-badge"><i class="bi bi-building"></i> Vista de su sucursal</span>
            @endif
        </div>
    </div>

    @if(session('es_superadmin') || !empty(session('permisos')['inv_repuestos']['ver']))
    {{-- ════ BANNER AUDITORÍA DE REPUESTOS ════ --}}
    <div class="rep-card" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; box-shadow: 0 10px 25px rgba(124, 58, 237, 0.25); position: relative; overflow: hidden; margin-bottom: 18px;">
        <div style="position: absolute; right: -50px; bottom: -50px; font-size: 220px; color: rgba(255, 255, 255, 0.05); pointer-events: none; line-height: 1;">
            <i class="bi bi-shield-check"></i>
        </div>
        <div style="padding: 24px 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; position: relative; z-index: 2;">
            <div style="display: flex; align-items: center; gap: 20px; flex: 1; min-width: 280px;">
                <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, 0.25); flex-shrink: 0;">
                    <i class="bi bi-clock-history" style="font-size: 28px; color: #ffffff;"></i>
                </div>
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 5px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 20px; padding: 2px 10px; font-size: 10px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                        <i class="bi bi-stars"></i> Nuevo Módulo
                    </div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                        Historial de Auditoría de Repuestos
                    </h3>
                    <p style="margin: 6px 0 0; color: rgba(255, 255, 255, 0.85); font-size: 13px; line-height: 1.5; max-width: 800px;">
                        Consulte el registro histórico detallado de asignación y consumo de repuestos en bodega. Visualice los costos financieros en tiempo real, filtre por técnicos responsables y exporte en formatos CSV o Excel Enterprise.
                    </p>
                </div>
            </div>
            <a href="{{ route('repuestos.auditoria') }}" class="rep-btn" style="background: #ffffff; color: #4f46e5; border: none; font-weight: 700; text-decoration: none; padding: 12px 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0, 0, 0, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)';">
                <i class="bi bi-journal-text" style="font-size: 16px;"></i> Ir a Auditoría de Stock <i class="bi bi-arrow-right" style="font-size: 14px;"></i>
            </a>
        </div>
    </div>
    @endif

    {{-- ════ FILTROS ════ --}}
    <div class="rep-card">
        <div class="rep-card-head">
            <i class="bi bi-funnel-fill"></i> Filtros de búsqueda
            <span id="badge-filtros" style="background:#dbeafe;color:#1e40af;border-radius:20px;padding:2px 10px;font-size:10.5px;font-weight:700;display:none;margin-left:4px;"></span>
            <div class="ch-right">
                <button class="rep-btn rep-btn-sm rep-btn-ghost" onclick="limpiarFiltros()">
                    <i class="bi bi-x-circle"></i> Limpiar
                </button>
            </div>
        </div>
        <form id="rep-form">
            <div class="rep-filtros-grid">

                <div class="rep-campo">
                    <label>Técnico</label>
                    <select name="tecnico_id" id="f-tecnico">
                        <option value="">Todos</option>
                        @foreach($tecnicos as $t)
                            <option value="{{ $t->id }}">{{ $t->nombre_tecnico }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Sucursal Novitec</label>
                    <select name="sucursal_id" id="f-sucursal">
                        <option value="">Todas</option>
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->ciudad }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>CAS Asignado</label>
                    <select name="cas_id" id="f-cas">
                        <option value="">Todos</option>
                        @foreach($cas as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Estado orden</label>
                    <select name="estado" id="f-estado">
                        <option value="">Todos</option>
                        @foreach($estados as $e)
                            <option value="{{ $e }}">{{ $e }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Tipo orden</label>
                    <select name="tipo_orden" id="f-tipo-orden">
                        <option value="">Todos</option>
                        <option value="personal">Personal</option>
                        <option value="empresa">Empresa</option>
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Marca equipo</label>
                    <select name="marca" id="f-marca">
                        <option value="">Todas</option>
                        @foreach($marcas as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Tipo equipo</label>
                    <select name="tipo_equipo" id="f-tipo">
                        <option value="">Todos</option>
                        @foreach($tiposEquipo as $tp)
                            <option value="{{ $tp }}">{{ $tp }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Motivo ingreso</label>
                    <select name="motivo_ingreso" id="f-motivo">
                        <option value="">Todos</option>
                        @foreach($motivos as $mv)
                            <option value="{{ $mv }}">{{ $mv }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Estado repuesto</label>
                    <select name="estado_repuesto" id="f-repuesto">
                        <option value="">Todos</option>
                        @foreach($estadosRepuesto as $er)
                            <option value="{{ $er }}">{{ $er }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Estado garantía</label>
                    <select name="estado_garantia" id="f-garantia">
                        <option value="">Todos</option>
                        @foreach($estadosGarantia as $eg)
                            <option value="{{ $eg }}">{{ $eg }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rep-campo">
                    <label>Fecha desde</label>
                    <input type="date" name="fecha_inicio" id="f-desde">
                </div>

                <div class="rep-campo">
                    <label>Fecha hasta</label>
                    <input type="date" name="fecha_fin" id="f-hasta">
                </div>

            </div>

            <div id="rep-pills" class="rep-pills"></div>

            <div class="rep-btns-row">
                <button type="submit" class="rep-btn rep-btn-primary" id="btn-generar">
                    <i class="bi bi-search"></i> Generar reporte
                </button>
                <div class="rep-divider-v"></div>
                <button type="button" class="rep-btn rep-btn-dark" id="btn-pdf" disabled>
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF Enterprise
                </button>
                <button type="button" class="rep-btn rep-btn-green" id="btn-xlsx" disabled>
                    <i class="bi bi-file-earmark-excel-fill"></i> XLSX
                </button>
                <button type="button" class="rep-btn rep-btn-teal" id="btn-csv" disabled>
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> CSV
                </button>
            </div>
        </form>
    </div>

    {{-- ════ SPINNER ════ --}}
    <div class="rep-spinner" id="rep-spinner">
        <div class="spin-ring"></div> Procesando datos del reporte…
    </div>

    {{-- ════ RESULTADOS ════ --}}
    <div id="rep-resultados" style="display:none;">

        {{-- KPIs --}}
        <div class="rep-kpis">
            <div class="rep-kpi c-blue">
                <i class="bi bi-clipboard-check"></i>
                <div class="rep-kpi-val" id="k-total">0</div>
                <div class="rep-kpi-lbl">Total órdenes</div>
            </div>
            <div class="rep-kpi c-amber">
                <i class="bi bi-hourglass-split"></i>
                <div class="rep-kpi-val" id="k-pend">0</div>
                <div class="rep-kpi-lbl">Pendientes</div>
                <div class="rep-kpi-pct" id="k-pend-pct"></div>
            </div>
            <div class="rep-kpi c-indigo">
                <i class="bi bi-wrench-adjustable"></i>
                <div class="rep-kpi-val" id="k-proc">0</div>
                <div class="rep-kpi-lbl">En proceso</div>
                <div class="rep-kpi-pct" id="k-proc-pct"></div>
            </div>
            <div class="rep-kpi c-violet">
                <i class="bi bi-check2-circle"></i>
                <div class="rep-kpi-val" id="k-fin">0</div>
                <div class="rep-kpi-lbl">Finalizadas</div>
                <div class="rep-kpi-pct" id="k-fin-pct"></div>
            </div>
            <div class="rep-kpi c-green">
                <i class="bi bi-box-arrow-right"></i>
                <div class="rep-kpi-val" id="k-ent">0</div>
                <div class="rep-kpi-lbl">Entregadas</div>
                <div class="rep-kpi-pct" id="k-ent-pct"></div>
            </div>
            <div class="rep-kpi c-rose">
                <i class="bi bi-receipt-cutoff"></i>
                <div class="rep-kpi-val" id="k-nc">0</div>
                <div class="rep-kpi-lbl">Notas crédito</div>
                <div class="rep-kpi-pct" id="k-nc-pct"></div>
            </div>
        </div>

        {{-- Gráficos fila 1 --}}
        <div class="rep-charts-2">
            <div class="rep-chart-card">
                <div class="rep-chart-title"><i class="bi bi-pie-chart-fill" style="color:#6366f1;"></i> Distribución por estado</div>
                <div class="rep-chart-body"><canvas id="ch-estados"></canvas></div>
            </div>
            <div class="rep-chart-card">
                <div class="rep-chart-title"><i class="bi bi-person-lines-fill" style="color:#2563eb;"></i> Órdenes por técnico (Top 10)</div>
                <div class="rep-chart-body"><canvas id="ch-tecnicos"></canvas></div>
            </div>
        </div>

        {{-- Gráficos fila 2 --}}
        <div class="rep-charts-3">
            <div class="rep-chart-card">
                <div class="rep-chart-title"><i class="bi bi-tag-fill" style="color:#f59e0b;"></i> Top marcas</div>
                <div class="rep-chart-body"><canvas id="ch-marcas"></canvas></div>
            </div>
            <div class="rep-chart-card">
                <div class="rep-chart-title"><i class="bi bi-laptop" style="color:#10b981;"></i> Tipo de equipo</div>
                <div class="rep-chart-body"><canvas id="ch-tipos"></canvas></div>
            </div>
            <div class="rep-chart-card">
                <div class="rep-chart-title"><i class="bi bi-buildings" style="color:#8b5cf6;"></i> Personal vs Empresa</div>
                <div class="rep-chart-body"><canvas id="ch-tipoorden"></canvas></div>
            </div>
        </div>

        {{-- Tabla detalle --}}
        <div class="rep-card" id="rep-tabla-card">
            <div class="rep-card-head">
                <i class="bi bi-table"></i> Detalle — <strong id="rep-count">0</strong>&nbsp;órdenes
                <div class="ch-right">
                    <input type="text" class="rep-search-box" id="rep-buscar" placeholder="🔍 Buscar…" oninput="filtrarTabla(this.value)">
                    <div class="rep-divider-v"></div>
                    <select id="pag-perpage" onchange="_perPage=+this.value;_page=1;renderTabla()" style="border:1.5px solid #e2e8f0;border-radius:6px;padding:4px 8px;font-size:12px;background:#fff;font-family:inherit;">
                        <option value="25">25/pág</option>
                        <option value="50" selected>50/pág</option>
                        <option value="100">100/pág</option>
                        <option value="999999">Todos</option>
                    </select>
                </div>
            </div>

            <div class="rep-stats-row" id="rep-stats-row"></div>

            <div class="rep-tbl-outer">
                <table class="rep-tbl" id="rep-tabla">
                    <thead><tr>
                        <th onclick="sortTabla(0,'nro_orden')">Nro. Orden</th>
                        <th onclick="sortTabla(1,'fecha_de_ingreso')">F. Ingreso</th>
                        <th onclick="sortTabla(2,'cliente_nombre')">Cliente</th>
                        <th onclick="sortTabla(3,'identificacion')">C.I./RUC</th>
                        <th onclick="sortTabla(4,'cliente_telefono')">Teléfono</th>
                        <th onclick="sortTabla(5,'equipo_nombre')">Equipo</th>
                        <th onclick="sortTabla(6,'serie')">Serie</th>
                        <th onclick="sortTabla(7,'marca')">Marca</th>
                        <th onclick="sortTabla(8,'tipo_equipo')">Tipo</th>
                        <th onclick="sortTabla(9,'motivo_ingreso')">Motivo</th>
                        <th onclick="sortTabla(10,'tecnico_nombre')">Técnico</th>
                        <th onclick="sortTabla(11,'sucursal_nombre')">Sucursal</th>
                        <th onclick="sortTabla(19,'cas_nombre')">CAS</th>
                        <th onclick="sortTabla(12,'tipo_orden')">Tipo orden</th>
                        <th onclick="sortTabla(13,'estado_repuesto')">Repuesto</th>
                        <th onclick="sortTabla(14,'estado_garantia')">Garantía</th>
                        <th onclick="sortTabla(15,'estado_orden')">Estado</th>
                        <th onclick="sortTabla(16,'dias_transcurridos')">Días</th>
                        <th onclick="sortTabla(17,'fecha_prometido')">F. Prometido</th>
                        <th onclick="sortTabla(18,'fecha_entrega')">F. Entrega</th>
                    </tr></thead>
                    <tbody id="rep-tbody"></tbody>
                </table>
            </div>

            <div class="rep-pagination" id="rep-pag">
                <span id="pag-info">Mostrando 0 – 0 de 0</span>
                <div class="rep-pag-btns" id="pag-btns"></div>
            </div>
        </div>

        <div id="rep-empty" style="display:none;" class="rep-empty">
            <i class="bi bi-inbox" style="color:#cbd5e1;"></i>
            <h4>Sin resultados</h4>
            <p>No se encontraron órdenes con los filtros aplicados.</p>
        </div>

    </div><!-- /rep-resultados -->
</div><!-- /rep-wrap -->
@endsection

@push('js_adicional')
<script>
(function () {
'use strict';

/* === ESTADO === */
let _all = [], _filtered = [], _charts = {};
let _sortCol = -1, _sortDir = 1, _page = 1, _perPage = 50;
let _chartJsLoaded = false;
const ES_MASTER = @json($esMaster);
const RUTA_FILTRAR = @json(route('reportes.filtrar'));


/* ═══════════ COLORES ESTADO ═══════════ */
const ESTADO_C = {
    'Pendiente'       : { bg:'#fef9c3', fg:'#854d0e', ch:'#f59e0b' },
    'En proceso'      : { bg:'#dbeafe', fg:'#1e40af', ch:'#3b82f6' },
    'Finalizada'      : { bg:'#dcfce7', fg:'#166534', ch:'#22c55e' },
    'Entregada'       : { bg:'#ecfdf5', fg:'#065f46', ch:'#10b981' },
    'Nota de Credito' : { bg:'#fce7f3', fg:'#9d174d', ch:'#ec4899' },
    'Abierta'         : { bg:'#e0e7ff', fg:'#3730a3', ch:'#6366f1' },
};
const PAL = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#14b8a6'];

/* ═══════════ HELPERS ═══════════ */
function esc(v) { return String(v ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function pct(n, t) { return t > 0 ? Math.round(n / t * 100) + '%' : '0%'; }
function countBy(arr, k) { const o = {}; arr.forEach(r => { const v = r[k] || '(Sin dato)'; o[v] = (o[v] || 0) + 1; }); return o; }
function topN(obj, n) { return Object.entries(obj).sort((a, b) => b[1] - a[1]).slice(0, n); }

function normalizeRow(raw) {
    const clienteNombre = raw.cliente_nombre
        || ((raw.cliente?.nombres || '') + ' ' + (raw.cliente?.apellidos || '')).trim()
        || '-';
    return {
        nro_orden         : raw.nro_orden || '',
        fecha_de_ingreso  : raw.fecha_de_ingreso || '',
        fecha_prometido   : raw.fecha_prometido || '',
        fecha_entrega     : raw.fecha_entrega || '',
        tipo_orden        : raw.tipo_orden || 'personal',
        cliente_nombre    : clienteNombre,
        identificacion    : raw.identificacion || raw.cliente?.identificacion || '-',
        cliente_telefono  : raw.cliente_telefono || raw.cliente?.numero_contacto || '-',
        cliente_correo    : raw.cliente_correo || '',
        cliente_direccion : raw.cliente_direccion || '',
        equipo_nombre     : raw.equipo_nombre || [raw.equipo?.marca, raw.equipo?.modelo].filter(Boolean).join(' ') || '-',
        serie             : raw.serie || raw.equipo?.serie || '-',
        marca             : raw.marca || raw.equipo?.marca || '-',
        tipo_equipo       : raw.tipo_equipo || raw.equipo?.tipo || '-',
        motivo_ingreso    : raw.motivo_ingreso || '-',
        estado_repuesto   : raw.estado_repuesto || '-',
        estado_garantia   : raw.estado_garantia || '-',
        estado_orden      : raw.estado_orden || '-',
        tecnico_nombre    : raw.tecnico_nombre || raw.tecnico?.nombre_tecnico || '-',
        sucursal_nombre   : raw.sucursal_nombre || raw.sucursal?.ciudad || '-',
        cas_nombre        : raw.cas_nombre || '-',
        dias_transcurridos: raw.dias_transcurridos ?? '-',
        vencida           : raw.vencida || false,
    };
}

/* ═══════════ PILLS FILTROS ═══════════ */
const FILTROS = [
    { id:'f-tecnico',   label:'Técnico',     sel:true  },
    { id:'f-sucursal',  label:'Sucursal',    sel:true  },
    { id:'f-cas',       label:'CAS',         sel:true  },
    { id:'f-estado',    label:'Estado',      sel:true  },
    { id:'f-tipo-orden',label:'Tipo orden',  sel:true  },
    { id:'f-marca',     label:'Marca',       sel:true  },
    { id:'f-tipo',      label:'Tipo equipo', sel:true  },
    { id:'f-motivo',    label:'Motivo',      sel:true  },
    { id:'f-repuesto',  label:'Repuesto',    sel:true  },
    { id:'f-garantia',  label:'Garantía',    sel:true  },
    { id:'f-desde',     label:'Desde',       sel:false },
    { id:'f-hasta',     label:'Hasta',       sel:false },
];

function actualizarPills() {
    const pillsEl = document.getElementById('rep-pills');
    const badgeEl = document.getElementById('badge-filtros');
    pillsEl.innerHTML = '';
    let cnt = 0;
    FILTROS.forEach(f => {
        if (f.hidden) return;
        const el = document.getElementById(f.id); if (!el) return;
        if (el.value) {
            cnt++;
            el.classList.add('filter-active');
            const lbl = f.sel ? el.options[el.selectedIndex].text : el.value;
            const span = document.createElement('span');
            span.className = 'rep-pill';
            span.innerHTML = `${f.label}: <b>${esc(lbl)}</b> <button onclick="limpiarFiltro('${f.id}')">×</button>`;
            pillsEl.appendChild(span);
        } else { el.classList.remove('filter-active'); }
    });
    badgeEl.textContent = cnt + (cnt === 1 ? ' activo' : ' activos');
    badgeEl.style.display = cnt ? 'inline-block' : 'none';
}

window.limpiarFiltro = function(id) { const el = document.getElementById(id); if (el) el.value = ''; actualizarPills(); };
FILTROS.forEach(f => { const el = document.getElementById(f.id); if (!el) return; el.addEventListener('change', actualizarPills); if (el.type === 'date') el.addEventListener('input', actualizarPills); });

/* === GENERAR REPORTE === */
document.getElementById('rep-form').addEventListener('submit', function(e) {
    e.preventDefault(); generarReporte();
});

function loadChartJs() {
    return new Promise((resolve) => {
        if (window.Chart) { resolve(); return; }
        const urls = [
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js'
        ];
        let i = 0;
        function tryNext() {
            if (i >= urls.length) { resolve(); return; } // resolve anyway, charts wont show but table will
            const s = document.createElement('script'); s.src = urls[i++];
            s.onload = resolve;
            s.onerror = tryNext;
            document.head.appendChild(s);
        }
        tryNext();
    });
}

async function generarReporte() {
    actualizarPills();
    const btn = document.getElementById('btn-generar');
    btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando…';
    document.getElementById('rep-spinner').classList.add('show');
    document.getElementById('rep-resultados').style.display = 'none';
    ['btn-pdf','btn-xlsx','btn-csv'].forEach(id => document.getElementById(id).disabled = true);

    // Build params manually to avoid FormData issues
    const form = document.getElementById('rep-form');
    const inputs = form.querySelectorAll('input, select');
    const params = new URLSearchParams();
    inputs.forEach(el => {
        if (el.name && el.value !== undefined && el.value !== '') {
            params.append(el.name, el.value);
        }
    });

    try {
        const url = RUTA_FILTRAR + (params.toString() ? '?' + params.toString() : '');
        const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        const d = await r.json();
        if (!d.ok) throw new Error(d.error || 'Error desconocido');
        _all = (d.data || []).map(normalizeRow);
        _filtered = _all.slice();
        _sortCol = -1; _sortDir = 1; _page = 1;
        const buscar = document.getElementById('rep-buscar'); if (buscar) buscar.value = '';
        document.getElementById('rep-resultados').style.display = 'block';
        renderKpis();
        renderTabla();
        // Load Chart.js and render charts (non-blocking)
        loadChartJs().then(() => {
            try { renderCharts(); } catch(ce) { console.warn('Charts error:', ce); }
        });
        ['btn-pdf','btn-xlsx','btn-csv'].forEach(id => document.getElementById(id).disabled = false);
    } catch(e) {
        console.error('Error generando reporte:', e);
        alert('Error al generar el reporte: ' + e.message);
    } finally {
        document.getElementById('rep-spinner').classList.remove('show');
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-search"></i> Generar reporte';
    }
}

/* ═══════════ KPIS ═══════════ */
function renderKpis() {
    const total = _all.length;
    const c = { Pendiente:0, 'En proceso':0, Finalizada:0, Entregada:0, 'Nota de Credito':0 };
    _all.forEach(r => { if (c[r.estado_orden] !== undefined) c[r.estado_orden]++; });
    document.getElementById('k-total').textContent = total;
    document.getElementById('k-pend').textContent  = c['Pendiente'];
    document.getElementById('k-proc').textContent  = c['En proceso'];
    document.getElementById('k-fin').textContent   = c['Finalizada'];
    document.getElementById('k-ent').textContent   = c['Entregada'];
    document.getElementById('k-nc').textContent    = c['Nota de Credito'];
    document.getElementById('k-pend-pct').textContent = pct(c['Pendiente'], total);
    document.getElementById('k-proc-pct').textContent = pct(c['En proceso'], total);
    document.getElementById('k-fin-pct').textContent  = pct(c['Finalizada'], total);
    document.getElementById('k-ent-pct').textContent  = Math.round(c['Entregada'] / (total || 1) * 100) + '% entrega';
    document.getElementById('k-nc-pct').textContent   = pct(c['Nota de Credito'], total);
}

/* ═══════════ GRÁFICOS ═══════════ */
function dc(id) { if (_charts[id]) { _charts[id].destroy(); delete _charts[id]; } }
function renderCharts() {
    const rows = _all;
    // 1. Estados
    dc('estados');
    const eC = countBy(rows, 'estado_orden');
    const eL = Object.keys(eC);
    _charts['estados'] = new Chart(document.getElementById('ch-estados'), {
        type: 'doughnut',
        data: { labels: eL, datasets: [{ data: Object.values(eC), backgroundColor: eL.map(l => (ESTADO_C[l]||{ch:'#94a3b8'}).ch), borderWidth: 2, borderColor: '#fff' }] },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'right', labels:{ boxWidth:12, font:{ size:11 } } } } }
    });
    // 2. Técnicos
    dc('tecnicos');
    const tT = topN(countBy(rows, 'tecnico_nombre'), 10);
    _charts['tecnicos'] = new Chart(document.getElementById('ch-tecnicos'), {
        type: 'bar',
        data: { labels: tT.map(x=>x[0]), datasets: [{ label:'Órdenes', data: tT.map(x=>x[1]), backgroundColor:'#3b82f6', borderRadius:5 }] },
        options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ x:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
    });
    // 3. Marcas
    dc('marcas');
    const mT = topN(countBy(rows, 'marca'), 8);
    _charts['marcas'] = new Chart(document.getElementById('ch-marcas'), {
        type: 'bar',
        data: { labels: mT.map(x=>x[0]), datasets: [{ label:'Órdenes', data: mT.map(x=>x[1]), backgroundColor: PAL, borderRadius:5 }] },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
    });
    // 4. Tipo equipo
    dc('tipos');
    const tpT = topN(countBy(rows, 'tipo_equipo'), 8);
    _charts['tipos'] = new Chart(document.getElementById('ch-tipos'), {
        type: 'doughnut',
        data: { labels: tpT.map(x=>x[0]), datasets: [{ data: tpT.map(x=>x[1]), backgroundColor: PAL, borderWidth:2, borderColor:'#fff' }] },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{ size:10 } } } } }
    });
    // 5. Tipo orden
    dc('tipoorden');
    const toC = countBy(rows, 'tipo_orden');
    _charts['tipoorden'] = new Chart(document.getElementById('ch-tipoorden'), {
        type: 'bar',
        data: { labels: Object.keys(toC).map(k => k.charAt(0).toUpperCase()+k.slice(1)), datasets: [{ label:'Órdenes', data: Object.values(toC), backgroundColor:['#3b82f6','#8b5cf6'], borderRadius:8 }] },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
    });
}

/* ═══════════ TABLA ═══════════ */
function renderTabla() {
    const total = _filtered.length;
    document.getElementById('rep-count').textContent = total;
    if (!total) {
        document.getElementById('rep-tabla-card').style.display = 'none';
        document.getElementById('rep-empty').style.display = 'flex';
        renderStatsRow();
        return;
    }
    document.getElementById('rep-empty').style.display = 'none';
    document.getElementById('rep-tabla-card').style.display = 'block';
    const start = (_page - 1) * _perPage, end = start + _perPage;
    const slice = _filtered.slice(start, end);
    document.getElementById('rep-tbody').innerHTML = slice.map(r => {
        const ec = ESTADO_C[r.estado_orden] || { bg:'#f1f5f9', fg:'#475569' };
        const tB = r.tipo_orden === 'empresa'
            ? '<span class="tipo-badge empresa">Empresa</span>'
            : '<span class="tipo-badge personal">Personal</span>';
        const dC = +r.dias_transcurridos > 14 ? '#dc2626' : +r.dias_transcurridos > 7 ? '#d97706' : '#475569';
        const vR = r.vencida ? 'class="vencida-row"' : '';
        const suc = `<td style="font-size:11px;">${esc(r.sucursal_nombre)}</td>`;
        const casCol = `<td style="font-size:11px;">${esc(r.cas_nombre)}</td>`;
        return `<tr ${vR}>
            <td class="rep-nro">${esc(r.nro_orden)}</td>
            <td style="font-size:11px;white-space:nowrap;">${esc(r.fecha_de_ingreso)}</td>
            <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;">${esc(r.cliente_nombre)}</td>
            <td style="font-size:11px;color:#64748b;">${esc(r.identificacion)}</td>
            <td style="font-size:11px;color:#64748b;">${esc(r.cliente_telefono)}</td>
            <td style="font-size:11px;max-width:120px;overflow:hidden;text-overflow:ellipsis;">${esc(r.equipo_nombre)}</td>
            <td style="font-size:10px;max-width:90px;overflow:hidden;text-overflow:ellipsis;">${esc(r.serie)}</td>
            <td>${esc(r.marca)}</td>
            <td style="font-size:11px;">${esc(r.tipo_equipo)}</td>
            <td style="font-size:11px;color:#475569;">${esc(r.motivo_ingreso)}</td>
            <td style="font-size:11px;">${esc(r.tecnico_nombre)}</td>
            ${suc}
            ${casCol}
            <td>${tB}</td>
            <td style="font-size:10.5px;color:#64748b;">${esc(r.estado_repuesto)}</td>
            <td style="font-size:10.5px;color:#64748b;">${esc(r.estado_garantia)}</td>
            <td><span class="estado-badge" style="background:${ec.bg};color:${ec.fg};">${esc(r.estado_orden)}</span></td>
            <td style="text-align:center;font-weight:700;color:${dC};">${r.dias_transcurridos}d</td>
            <td style="font-size:11px;white-space:nowrap;">${esc(r.fecha_prometido || '—')}</td>
            <td style="font-size:11px;white-space:nowrap;">${esc(r.fecha_entrega || '—')}</td>
        </tr>`;
    }).join('');
    renderStatsRow();
    renderPaginacion();
}

function renderStatsRow() {
    const total = _filtered.length; if (!total) { document.getElementById('rep-stats-row').innerHTML = ''; return; }
    const ent = _filtered.filter(x => x.estado_orden === 'Entregada').length;
    const pend = _filtered.filter(x => x.estado_orden === 'Pendiente').length;
    const mT = topN(countBy(_filtered, 'marca'), 1)[0];
    const tT = topN(countBy(_filtered, 'tecnico_nombre'), 1)[0];
    let html = `<div class="rep-stat-chip"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i>Tasa entrega: <b>${pct(ent, total)}</b></div>`;
    html += `<div class="rep-stat-chip"><i class="bi bi-hourglass" style="color:#f59e0b;"></i>Pendientes: <b>${pend} (${pct(pend, total)})</b></div>`;
    if (mT) html += `<div class="rep-stat-chip"><i class="bi bi-tag" style="color:#f59e0b;"></i>Marca líder: <b>${mT[0]} (${mT[1]})</b></div>`;
    if (tT) html += `<div class="rep-stat-chip"><i class="bi bi-person-check" style="color:#3b82f6;"></i>Top técnico: <b>${tT[0]} (${tT[1]})</b></div>`;
    document.getElementById('rep-stats-row').innerHTML = html;
}

function renderPaginacion() {
    const total = _filtered.length, pages = Math.ceil(total / _perPage);
    const s = (_page - 1) * _perPage + 1, e = Math.min(_page * _perPage, total);
    document.getElementById('pag-info').textContent = `Mostrando ${s} – ${e} de ${total}`;
    let html = `<button class="rep-pag-btn" ${_page <= 1 ? 'disabled' : ''} onclick="goPage(${_page-1})">‹</button>`;
    const f = Math.max(1, _page - 2), t = Math.min(pages, _page + 2);
    for (let i = f; i <= t; i++) html += `<button class="rep-pag-btn${_page === i ? ' active' : ''}" onclick="goPage(${i})">${i}</button>`;
    html += `<button class="rep-pag-btn" ${_page >= pages ? 'disabled' : ''} onclick="goPage(${_page+1})">›</button>`;
    document.getElementById('pag-btns').innerHTML = html;
}

window.goPage = function(p) { _page = p; renderTabla(); };
window.filtrarTabla = function(q) {
    q = q.toLowerCase();
    _filtered = q ? _all.filter(r => Object.values(r).join(' ').toLowerCase().includes(q)) : _all.slice();
    _page = 1; renderTabla();
};
window.sortTabla = function(col, key) {
    if (_sortCol === col) _sortDir *= -1; else { _sortCol = col; _sortDir = 1; }
    _filtered.sort((a, b) => String(a[key] || '').localeCompare(String(b[key] || ''), 'es') * _sortDir);
    document.querySelectorAll('.rep-tbl th').forEach((th, i) => {
        th.classList.remove('sort-asc', 'sort-desc');
        if (i === col) th.classList.add(_sortDir === 1 ? 'sort-asc' : 'sort-desc');
    });
    _page = 1; renderTabla();
};

/* ═══════════ LIMPIAR ═══════════ */
window.limpiarFiltros = function() {
    FILTROS.forEach(f => { const el = document.getElementById(f.id); if (el) { el.value = ''; el.classList.remove('filter-active'); } });
    document.getElementById('rep-pills').innerHTML = '';
    document.getElementById('badge-filtros').style.display = 'none';
    document.getElementById('rep-resultados').style.display = 'none';
    _all = []; _filtered = [];
    Object.keys(_charts).forEach(k => { _charts[k].destroy(); delete _charts[k]; });
    ['btn-pdf','btn-xlsx','btn-csv'].forEach(id => document.getElementById(id).disabled = true);
};

/* ═══════════ HELPERS FILTROS TEXTO ═══════════ */
function getFiltrosTxt() {
    const partes = [];
    FILTROS.forEach(f => {
        if (f.hidden) return;
        const el = document.getElementById(f.id); if (!el || !el.value) return;
        partes.push(f.label + ': ' + (f.sel ? el.options[el.selectedIndex].text : el.value));
    });
    return partes;
}

/* ════════════════════════════════════════════════
   PDF ENTERPRISE — igual formato que imprimir.blade.php
════════════════════════════════════════════════ */
document.getElementById('btn-pdf').addEventListener('click', generarPDFEnterprise);

function generarPDFEnterprise() {
    if (!_filtered.length) { alert('No hay datos para exportar.'); return; }
    const total = _filtered.length;
    const cnt = { Pendiente:0, 'En proceso':0, Finalizada:0, Entregada:0, 'Nota de Credito':0 };
    _filtered.forEach(r => { if (cnt[r.estado_orden] !== undefined) cnt[r.estado_orden]++; });
    const tasa = Math.round(cnt['Entregada'] / (total || 1) * 100);
    function pp(n) { return total > 0 ? Math.round(n / total * 100) + '%' : '0%'; }
    const mT5 = topN(countBy(_filtered, 'marca'), 5);
    const tT5 = topN(countBy(_filtered, 'tecnico_nombre'), 5);
    const fTxt = getFiltrosTxt();
    const sH = '<th>Sucursal</th><th>CAS</th>';
    const now = new Date().toLocaleString('es-EC');

    /* Filas tabla */
    const filas = _filtered.map(r => {
        const ec = ESTADO_C[r.estado_orden] || { bg:'#f1f5f9', fg:'#475569' };
        const tB = r.tipo_orden === 'empresa' ? 'Empresa' : 'Personal';
        const tBg = r.tipo_orden === 'empresa' ? '#eff6ff' : '#f0fdf4';
        const tFg = r.tipo_orden === 'empresa' ? '#1e40af' : '#166534';
        const sC = `<td>${r.sucursal_nombre}</td><td>${r.cas_nombre}</td>`;
        const dC = +r.dias_transcurridos > 14 ? '#dc2626' : '#374151';
        return `<tr>
            <td style="font-family:monospace;font-weight:700;color:#1a56db;white-space:nowrap;">${r.nro_orden}</td>
            <td style="white-space:nowrap;">${r.fecha_de_ingreso}</td>
            <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;">${r.cliente_nombre}</td>
            <td style="font-size:6.5pt;color:#64748b;">${r.identificacion}</td>
            <td style="font-size:6.5pt;color:#64748b;">${r.cliente_telefono}</td>
            <td style="font-size:6.5pt;">${r.equipo_nombre}</td>
            <td style="font-size:6pt;">${r.serie}</td>
            <td>${r.marca}</td>
            <td style="font-size:6.5pt;">${r.tipo_equipo}</td>
            <td style="font-size:6.5pt;">${r.motivo_ingreso}</td>
            <td style="font-size:6.5pt;">${r.tecnico_nombre}</td>
            ${sC}
            <td><span style="background:${tBg};color:${tFg};padding:1px 5px;border-radius:3px;font-size:6pt;font-weight:700;">${tB}</span></td>
            <td style="font-size:6.5pt;color:#64748b;">${r.estado_repuesto}</td>
            <td style="font-size:6.5pt;color:#64748b;">${r.estado_garantia || '—'}</td>
            <td><span style="background:${ec.bg};color:${ec.fg};padding:1px 6px;border-radius:8px;font-size:6.5pt;font-weight:700;">${r.estado_orden}</span></td>
            <td style="text-align:center;font-size:6.5pt;font-weight:700;color:${dC};">${r.dias_transcurridos}d</td>
            <td style="font-size:6.5pt;white-space:nowrap;">${r.fecha_prometido || '—'}</td>
            <td style="font-size:6.5pt;white-space:nowrap;">${r.fecha_entrega || '—'}</td>
        </tr>`;
    }).join('');

    /* Bloque resumen estadístico */
    const resumen = `
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
  <div>
    <div style="font-size:8pt;font-weight:700;color:#1e40af;margin-bottom:5px;border-left:3px solid #1e40af;padding-left:6px;">ESTADOS DE ÓRDENES</div>
    <table style="width:100%;border-collapse:collapse;font-size:7pt;">
      <tr style="background:#f8fafc;"><td style="padding:2px 6px;border-bottom:1px solid #e2e8f0;">Total órdenes</td><td style="font-weight:700;">${total}</td><td>100%</td></tr>
      <tr><td style="padding:2px 6px;border-bottom:1px solid #f1f5f9;">Pendientes</td><td style="font-weight:700;color:#854d0e;">${cnt['Pendiente']}</td><td style="color:#854d0e;">${pp(cnt['Pendiente'])}</td></tr>
      <tr style="background:#f8fafc;"><td style="padding:2px 6px;border-bottom:1px solid #e2e8f0;">En proceso</td><td style="font-weight:700;color:#1e40af;">${cnt['En proceso']}</td><td style="color:#1e40af;">${pp(cnt['En proceso'])}</td></tr>
      <tr><td style="padding:2px 6px;border-bottom:1px solid #f1f5f9;">Finalizadas</td><td style="font-weight:700;color:#166534;">${cnt['Finalizada']}</td><td style="color:#166534;">${pp(cnt['Finalizada'])}</td></tr>
      <tr style="background:#f8fafc;"><td style="padding:2px 6px;border-bottom:1px solid #e2e8f0;">Entregadas</td><td style="font-weight:700;color:#065f46;">${cnt['Entregada']}</td><td style="color:#065f46;">${pp(cnt['Entregada'])}</td></tr>
      <tr><td style="padding:2px 6px;border-bottom:1px solid #f1f5f9;">Notas Crédito</td><td style="font-weight:700;color:#9d174d;">${cnt['Nota de Credito']}</td><td style="color:#9d174d;">${pp(cnt['Nota de Credito'])}</td></tr>
      <tr style="background:#ecfdf5;"><td style="padding:2px 6px;font-weight:700;">Tasa de entrega</td><td colspan="2" style="color:#065f46;font-weight:800;">${tasa}%</td></tr>
    </table>
  </div>
  <div>
    <div style="font-size:8pt;font-weight:700;color:#7c3aed;margin-bottom:5px;border-left:3px solid #7c3aed;padding-left:6px;">TOP MARCAS</div>
    <table style="width:100%;border-collapse:collapse;font-size:7pt;">
      ${mT5.map((x,i)=>`<tr style="background:${i%2===0?'#fff':'#f8fafc'};"><td style="padding:2px 6px;border-bottom:1px solid #f1f5f9;">${x[0]}</td><td style="font-weight:700;color:#7c3aed;">${x[1]}</td><td style="color:#64748b;">${pp(x[1])}</td></tr>`).join('')}
    </table>
  </div>
  <div>
    <div style="font-size:8pt;font-weight:700;color:#0f766e;margin-bottom:5px;border-left:3px solid #0f766e;padding-left:6px;">TOP TÉCNICOS</div>
    <table style="width:100%;border-collapse:collapse;font-size:7pt;">
      ${tT5.map((x,i)=>`<tr style="background:${i%2===0?'#fff':'#f8fafc'};"><td style="padding:2px 6px;border-bottom:1px solid #f1f5f9;">${x[0]}</td><td style="font-weight:700;color:#0f766e;">${x[1]}</td><td style="color:#64748b;">${pp(x[1])}</td></tr>`).join('')}
    </table>
  </div>
</div>`;

    /* Tarjetas KPI */
    const kpiCards = [
        { v: total,               l: 'Total', p: '100%',    bc: '#2563eb', bg:'#eff6ff', fg:'#1e40af' },
        { v: cnt['Pendiente'],    l: 'Pendientes', p: pp(cnt['Pendiente']), bc:'#f59e0b', bg:'#fef9c3', fg:'#854d0e' },
        { v: cnt['En proceso'],   l: 'En proceso', p: pp(cnt['En proceso']),bc:'#6366f1', bg:'#e0e7ff', fg:'#3730a3' },
        { v: cnt['Finalizada'],   l: 'Finalizadas',p: pp(cnt['Finalizada']),bc:'#22c55e', bg:'#dcfce7', fg:'#166534' },
        { v: cnt['Entregada'],    l: 'Entregadas', p: tasa+'% tasa',       bc:'#10b981', bg:'#ecfdf5', fg:'#065f46' },
        { v: cnt['Nota de Credito'],l:'N. Crédito',p: pp(cnt['Nota de Credito']),bc:'#ec4899',bg:'#fce7f3',fg:'#9d174d' },
    ].map(k => `<div style="flex:1;border:1px solid #e2e8f0;border-top:3px solid ${k.bc};border-radius:5px;padding:6px 8px;text-align:center;background:${k.bg};">
        <div style="font-size:16pt;font-weight:900;color:${k.fg};line-height:1;">${k.v}</div>
        <div style="font-size:6pt;color:${k.fg};font-weight:700;text-transform:uppercase;margin-top:2px;">${k.l}</div>
        <div style="font-size:7pt;font-weight:700;color:${k.fg};margin-top:1px;">${k.p}</div>
    </div>`).join('');

    const html = `<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<title>Reporte de Órdenes — Novitecnología</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:7.5pt; color:#000; background:#fff; }
@page { size: A4 landscape; margin: 8mm; }
@media print { .no-print { display:none!important; } body { print-color-adjust:exact; -webkit-print-color-adjust:exact; } }
.wrap { width:277mm; margin:0 auto; }
.header { display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #1e40af; padding-bottom:7px; margin-bottom:10px; }
.hdr-left .empresa { font-size:12pt; font-weight:700; color:#1e40af; }
.hdr-left .sub { font-size:7pt; color:#475569; margin-top:2px; }
.hdr-right { text-align:right; font-size:8pt; }
.rep-title { font-weight:700; color:#0f172a; font-size:10pt; }
.kpi-row { display:flex; gap:6px; margin-bottom:10px; }
.filtros-box { background:#f1f5f9; border-left:3px solid #2563eb; padding:4px 10px; border-radius:4px; font-size:7pt; color:#475569; margin-bottom:8px; }
.sec-titulo { font-size:9pt; font-weight:700; color:#0f172a; margin:8px 0 5px; border-left:3px solid #2563eb; padding-left:7px; }
table.dt { width:100%; border-collapse:collapse; }
table.dt th { background:#1e3a8a; color:#fff; font-size:6pt; font-weight:700; text-transform:uppercase; padding:4px 5px; border:1px solid #1d4ed8; text-align:left; white-space:nowrap; }
table.dt td { padding:3px 5px; border:1px solid #e5e7eb; font-size:6.5pt; white-space:nowrap; max-width:130px; overflow:hidden; text-overflow:ellipsis; }
table.dt tr:nth-child(even) td { background:#f8fafc; }
.foot { text-align:center; margin-top:8px; font-size:6.5pt; color:#94a3b8; border-top:1px solid #e5e7eb; padding-top:5px; }
.bp { position:fixed; top:10px; right:10px; background:#1a56db; color:#fff; border:none; padding:9px 20px; border-radius:7px; font-size:12px; cursor:pointer; font-weight:700; z-index:999; box-shadow:0 3px 12px rgba(0,0,0,.2); }
<\/style><\/head><body>
<button class="bp no-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
<div class="wrap">
  <div class="header">
    <div class="hdr-left">
      <div class="empresa">Novitecnología Cía. Ltda.</div>
      <div class="sub"><b>GYE:</b> 04-6031337 / 0960500158 &nbsp;&nbsp; <b>UIO:</b> 02-6001635 / 0960500156 &nbsp;&nbsp; soporte@novitec.com.ec</div>
    </div>
    <div class="hdr-right">
      <div class="rep-title">REPORTE DE ÓRDENES DE SERVICIO</div>
      <div style="color:#64748b;font-size:7pt;">Generado: ${now}</div>
    </div>
  </div>

  <div class="kpi-row">${kpiCards}</div>

  ${fTxt.length ? `<div class="filtros-box"><b>Filtros aplicados:</b> ${fTxt.join(' &nbsp;·&nbsp; ')}</div>` : ''}

  <div class="sec-titulo">Resumen ejecutivo del período</div>
  ${resumen}

  <div class="sec-titulo">Detalle de órdenes <span style="font-size:7.5pt;font-weight:400;color:#64748b;">(${total} registros)</span></div>
  <table class="dt"><thead><tr>
    <th>Nro. Orden</th><th>F. Ingreso</th><th>Cliente</th><th>C.I./RUC</th><th>Teléfono</th>
    <th>Equipo</th><th>Serie</th><th>Marca</th><th>Tipo Eq.</th><th>Motivo</th>
    <th>Técnico</th>${sH}<th>Tipo Ord.</th><th>Repuesto</th><th>Garantía</th>
    <th>Estado</th><th>Días</th><th>F. Prometido</th><th>F. Entrega</th>
  </tr></thead><tbody>${filas}</tbody></table>

  <div class="foot">
    Novitecnología Cía. Ltda. &mdash; Sistema de Gestión SGN &mdash; Impreso el: ${now}
  </div>
</div><\/body><\/html>`;

    const w = window.open('', '_blank', 'width=1200,height=800,scrollbars=yes');
    w.document.write(html); w.document.close();
}

/* ════════════════════════════════════════════════
   CSV ENTERPRISE
════════════════════════════════════════════════ */
document.getElementById('btn-csv').addEventListener('click', exportarCSV);

function exportarCSV() {
    if (!_filtered.length) { alert('No hay datos para exportar.'); return; }
    const BOM = '\uFEFF'; // UTF-8 BOM para Excel
    const headers = [
        'Nro. Orden','Fecha Ingreso','Tipo Orden','Cliente','C.I./RUC','Teléfono','Correo',
        'Equipo','Serie','Marca','Tipo Equipo','Motivo Ingreso',
        'Estado Repuesto','Estado Garantía','Estado Orden',
        'Técnico','Sucursal','CAS','Días Transcurridos','F. Prometido','F. Entrega','Vencida'
    ];
    const rows = _filtered.map(r => [
        r.nro_orden, r.fecha_de_ingreso, r.tipo_orden, r.cliente_nombre, r.identificacion,
        r.cliente_telefono, r.cliente_correo, r.equipo_nombre, r.serie, r.marca,
        r.tipo_equipo, r.motivo_ingreso, r.estado_repuesto, r.estado_garantia,
        r.estado_orden, r.tecnico_nombre, r.sucursal_nombre, r.cas_nombre,
        r.dias_transcurridos, r.fecha_prometido || '', r.fecha_entrega || '',
        r.vencida ? 'Sí' : 'No'
    ].map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','));
    const csv = BOM + [headers.map(h => `"${h}"`).join(','), ...rows].join('\r\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reporte_novitec_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click(); URL.revokeObjectURL(url);
}

/* ════════════════════════════════════════════════
   XLSX ENTERPRISE con ExcelJS
════════════════════════════════════════════════ */
document.getElementById('btn-xlsx').addEventListener('click', () => {
    document.getElementById('btn-xlsx').disabled = true;
    document.getElementById('btn-xlsx').innerHTML = '<i class="bi bi-hourglass-split"></i> Generando…';
    cargarExcelJS().then(() => exportarXLSX()).finally(() => {
        document.getElementById('btn-xlsx').disabled = false;
        document.getElementById('btn-xlsx').innerHTML = '<i class="bi bi-file-earmark-excel-fill"></i> XLSX';
    });
});

function cargarExcelJS() {
    return new Promise((resolve, reject) => {
        if (window.ExcelJS) { resolve(); return; }
        const urls = [
            'https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js',
            'https://unpkg.com/exceljs@4.4.0/dist/exceljs.min.js'
        ];
        let i = 0;
        function tryNext() {
            if (i >= urls.length) { reject(new Error('No se pudo cargar ExcelJS')); return; }
            const s = document.createElement('script'); s.src = urls[i++];
            s.onload = () => window.ExcelJS ? resolve() : tryNext();
            s.onerror = tryNext;
            document.head.appendChild(s);
        }
        tryNext();
    });
}

async function exportarXLSX() {
    if (!_filtered.length) { alert('No hay datos.'); return; }
    const wb = new ExcelJS.Workbook();
    wb.creator = 'SGN - Novitecnologia';
    wb.created = new Date();

    const C = {
        azulO:'1E3A8A', azul:'1E40AF', azulL:'DBEAFE', azulXL:'EFF6FF',
        verdeO:'065F46', verde:'166534', verdeL:'DCFCE7', verdeXL:'ECFDF5',
        ambar:'854D0E', ambarL:'FEF9C3', rojo:'9D174D', rojoL:'FCE7F3',
        indigo:'3730A3', indigoL:'E0E7FF', gris:'F8FAFC', grisMed:'E2E8F0',
        grisOsc:'64748B', blanco:'FFFFFF', negro:'0F172A',
        teal:'0F766E', tealL:'CCFBF1', violet:'7C3AED', violetL:'EDE9FE',
    };
    const EC = {
        'Pendiente':      { bg:C.ambarL, fg:C.ambar  },
        'En proceso':     { bg:C.azulL,  fg:C.azul   },
        'Finalizada':     { bg:C.verdeL, fg:C.verde  },
        'Entregada':      { bg:C.verdeXL,fg:C.verdeO },
        'Nota de Credito':{ bg:C.rojoL,  fg:C.rojo   },
        'Abierta':        { bg:C.indigoL,fg:C.indigo },
    };
    const fl = a => ({ type:'pattern', pattern:'solid', fgColor:{ argb:'FF'+a } });
    const bd = (c='E2E8F0') => { const b = { style:'thin', color:{ argb:'FF'+c } }; return { top:b, left:b, bottom:b, right:b }; };
    const fn = (bold, size, color, extra={}) => Object.assign({ bold:!!bold, size:size||10, color:{ argb:'FF'+(color||C.negro) } }, extra);
    const al = (h='left', v='middle') => ({ horizontal:h, vertical:v });

    const total = _filtered.length;
    const cnt = { Pendiente:0, 'En proceso':0, Finalizada:0, Entregada:0, 'Nota de Credito':0 };
    _filtered.forEach(r => { if (cnt[r.estado_orden] !== undefined) cnt[r.estado_orden]++; });
    const tasa = Math.round(cnt['Entregada'] / (total || 1) * 100);
    const pp = n => (n / (total || 1) * 100).toFixed(1) + '%';
    const mT2 = topN(countBy(_filtered, 'marca'), 10);
    const tT2 = topN(countBy(_filtered, 'tecnico_nombre'), 10);
    const tiT2 = topN(countBy(_filtered, 'tipo_equipo'), 10);

    /* ══ HOJA 1: DETALLE ══ */
    const cols1 = [
        'Nro. Orden','F. Ingreso','F. Prometido','F. Entrega','Días','Vencida',
        'Cliente','C.I./RUC','Teléfono','Correo','Dirección',
        'Equipo','Serie','Marca','Tipo Equipo','Motivo Ingreso',
        'Estado Repuesto','Estado Garantía','Estado Orden',
        'Técnico','Ingresado por',
        'Sucursal',
        'CAS',
        'Tipo Orden'
    ];
    const nc = cols1.length;
    const widths1 = [14,18,14,14,7,8,28,14,14,22,28,18,18,16,16,22,18,14,18,22,20,16,16,12];

    const ws1 = wb.addWorksheet('Órdenes', {
        views: [{ state:'frozen', ySplit:20 }],
        pageSetup: { paperSize:9, orientation:'landscape', fitToPage:true, fitToWidth:1 }
    });
    ws1.columns = widths1.map(w => ({ width:w }));

    // Titulo
    ws1.mergeCells(1, 1, 1, nc);
    const t1 = ws1.getCell('A1'); t1.value = 'REPORTE DE ÓRDENES DE SERVICIO — Novitecnología Cía. Ltda.';
    t1.fill = fl(C.azulO); t1.font = fn(true, 14, C.blanco); t1.alignment = al('center'); ws1.getRow(1).height = 30;

    ws1.mergeCells(2, 1, 2, nc);
    const t2 = ws1.getCell('A2');
    t2.value = `Generado: ${new Date().toLocaleString('es-EC')}   |   Total registros: ${total}   |   Filtros: ${getFiltrosTxt().join(' · ') || 'Ninguno'}`;
    t2.fill = fl(C.azulL); t2.font = fn(false, 10, C.azulO, { italic:true }); t2.alignment = al('center'); ws1.getRow(2).height = 16;

    // KPIs
    const kpis = [
        { l:'TOTAL',      v:total,                p:'100%',            bg:C.azulXL,  fg:C.azul  },
        { l:'PENDIENTES', v:cnt['Pendiente'],      p:pp(cnt['Pendiente']),   bg:C.ambarL,  fg:C.ambar },
        { l:'EN PROCESO', v:cnt['En proceso'],     p:pp(cnt['En proceso']),  bg:C.azulL,   fg:C.azul  },
        { l:'FINALIZADAS',v:cnt['Finalizada'],     p:pp(cnt['Finalizada']),  bg:C.verdeL,  fg:C.verde },
        { l:'ENTREGADAS', v:cnt['Entregada'],      p:pp(cnt['Entregada']),   bg:C.verdeXL, fg:C.verdeO},
        { l:'N. CRÉDITO', v:cnt['Nota de Credito'],p:pp(cnt['Nota de Credito']),bg:C.rojoL, fg:C.rojo },
        { l:'TASA ENTREGA',v:tasa+'%',             p:'',                bg:C.tealL,   fg:C.teal  },
    ];
    [3, 4, 5].forEach(rn => ws1.getRow(rn).height = rn === 4 ? 28 : 14);
    const kStep = Math.floor(nc / 7);
    kpis.forEach((k, i) => {
        const col = i * kStep + 1;
        ['A','B','C'].forEach((_, ri) => {
            const cell = ws1.getCell(3 + ri, col); cell.fill = fl(k.bg); cell.border = bd();
        });
        const lc = ws1.getCell(3, col); lc.value = k.l; lc.font = fn(true, 8, k.fg); lc.alignment = al('center');
        const vc = ws1.getCell(4, col); vc.value = k.v; vc.font = fn(true, 16, k.fg); vc.alignment = al('center');
        const pc = ws1.getCell(5, col); pc.value = k.p; pc.font = fn(false, 9, k.fg); pc.alignment = al('center');
    });

    // Separador resumen
    ws1.mergeCells(6, 1, 6, nc);
    const sep6 = ws1.getCell(6, 1); sep6.value = 'RESUMEN EJECUTIVO';
    sep6.fill = fl(C.azulO); sep6.font = fn(true, 10, C.blanco); sep6.alignment = al('center'); ws1.getRow(6).height = 18;

    // Resumen: estados | marcas | tecnicos
    const resHdr = ['Estado','Cant.','%','','Marca','Cant.','%','','Técnico','Cant.','%'];
    ws1.getRow(7).height = 15;
    resHdr.forEach((h, i) => { if (!h) return; const c = ws1.getCell(7, i+1); c.value = h; c.fill = fl(C.grisMed); c.font = fn(true, 9, C.negro); c.alignment = al('center'); c.border = bd(); });

    const estadosList = [
        ['Total Órdenes', total, '100%', C.azulXL, C.azul],
        ['Pendientes', cnt['Pendiente'], pp(cnt['Pendiente']), C.ambarL, C.ambar],
        ['En proceso', cnt['En proceso'], pp(cnt['En proceso']), C.azulL, C.azul],
        ['Finalizadas', cnt['Finalizada'], pp(cnt['Finalizada']), C.verdeL, C.verde],
        ['Entregadas', cnt['Entregada'], pp(cnt['Entregada']), C.verdeXL, C.verdeO],
        ['Notas de Crédito', cnt['Nota de Credito'], pp(cnt['Nota de Credito']), C.rojoL, C.rojo],
        ['Tasa de entrega', tasa+'%', '', C.tealL, C.teal],
    ];
    const maxR = Math.max(estadosList.length, mT2.length, tT2.length);
    for (let ri = 0; ri < maxR; ri++) {
        const dr = 8 + ri; ws1.getRow(dr).height = 14;
        const bg = ri % 2 === 0 ? C.blanco : C.gris;
        if (ri < estadosList.length) {
            const es = estadosList[ri];
            const c1 = ws1.getCell(dr,1); c1.value = es[0]; c1.fill = fl(es[3]); c1.font = fn(false,9,es[4]); c1.border = bd(); c1.alignment = al('left');
            const c2 = ws1.getCell(dr,2); c2.value = es[1]; c2.fill = fl(es[3]); c2.font = fn(true,9,es[4]); c2.border = bd(); c2.alignment = al('center');
            const c3 = ws1.getCell(dr,3); c3.value = es[2]; c3.fill = fl(es[3]); c3.font = fn(false,9,es[4]); c3.border = bd(); c3.alignment = al('center');
        }
        if (ri < mT2.length) {
            const m = mT2[ri];
            const m1 = ws1.getCell(dr,5); m1.value = m[0]; m1.fill = fl(bg); m1.font = fn(false,9); m1.border = bd(); m1.alignment = al('left');
            const m2 = ws1.getCell(dr,6); m2.value = m[1]; m2.fill = fl(bg); m2.font = fn(true,9,C.violet); m2.border = bd(); m2.alignment = al('center');
            const m3 = ws1.getCell(dr,7); m3.value = pp(m[1]); m3.fill = fl(bg); m3.font = fn(false,9,C.grisOsc); m3.border = bd(); m3.alignment = al('center');
        }
        if (ri < tT2.length) {
            const t = tT2[ri];
            const t1c = ws1.getCell(dr,9); t1c.value = t[0]; t1c.fill = fl(bg); t1c.font = fn(false,9); t1c.border = bd(); t1c.alignment = al('left');
            const t2c = ws1.getCell(dr,10); t2c.value = t[1]; t2c.fill = fl(bg); t2c.font = fn(true,9,C.teal); t2c.border = bd(); t2c.alignment = al('center');
            const t3c = ws1.getCell(dr,11); t3c.value = pp(t[1]); t3c.fill = fl(bg); t3c.font = fn(false,9,C.grisOsc); t3c.border = bd(); t3c.alignment = al('center');
        }
    }

    // Encabezado tabla
    const sepR = 8 + maxR + 1;
    ws1.getRow(sepR - 1).height = 6;
    ws1.mergeCells(sepR, 1, sepR, nc);
    const sepT = ws1.getCell(sepR, 1); sepT.value = `DETALLE DE ÓRDENES  (${total} registros)`;
    sepT.fill = fl(C.azulO); sepT.font = fn(true, 10, C.blanco); sepT.alignment = al('center'); ws1.getRow(sepR).height = 18;

    const hRowN = sepR + 1; ws1.getRow(hRowN).height = 20;
    cols1.forEach((h, i) => {
        const c = ws1.getCell(hRowN, i+1); c.value = h; c.fill = fl('1E3A8A');
        c.font = fn(true, 9, C.blanco); c.alignment = al('center'); c.border = bd('1D4ED8');
    });
    ws1.autoFilter = { from:{ row:hRowN, column:1 }, to:{ row:hRowN, column:nc } };
    ws1.views = [{ state:'frozen', ySplit:hRowN }];

    // Datos
    _filtered.forEach((r, idx) => {
        const vals = [
            r.nro_orden, r.fecha_de_ingreso, r.fecha_prometido || '', r.fecha_entrega || '',
            r.dias_transcurridos ?? '', r.vencida ? 'Sí' : 'No',
            r.cliente_nombre, r.identificacion, r.cliente_telefono, r.cliente_correo, r.cliente_direccion,
            r.equipo_nombre, r.serie, r.marca, r.tipo_equipo, r.motivo_ingreso,
            r.estado_repuesto, r.estado_garantia || '', r.estado_orden,
            r.tecnico_nombre, '',
            r.sucursal_nombre,
            r.cas_nombre,
            r.tipo_orden
        ];
        const dr = ws1.addRow(vals); dr.height = 14;
        const bgBase = idx % 2 === 0 ? C.blanco : C.gris;
        const estadoIdx = 19;
        vals.forEach((v, ci) => {
            const cell = dr.getCell(ci + 1); cell.border = bd(); cell.font = fn(false, 9); cell.alignment = al('left','middle');
            if (ci === 0) { cell.font = fn(true, 9, C.azul, { name:'Courier New' }); cell.fill = fl(bgBase); cell.alignment = al('center','middle'); }
            else if (ci + 1 === estadoIdx) { const ec2 = EC[v] || { bg:C.gris, fg:C.grisOsc }; cell.fill = fl(ec2.bg); cell.font = fn(true, 8, ec2.fg); cell.alignment = al('center','middle'); }
            else { cell.fill = fl(bgBase); }
        });
    });

    /* ══ HOJA 2: ESTADÍSTICAS ══ */
    const ws2 = wb.addWorksheet('Estadísticas', { views:[{ showGridLines:false }] });
    ws2.columns = [{ width:2 },{ width:30 },{ width:13 },{ width:10 },{ width:20 },{ width:3 },{ width:30 },{ width:13 },{ width:10 },{ width:20 }];

    function addSec2(title, headers, data, startR, startC, titleBg, barColor) {
        ws2.getRow(startR).height = 22;
        ws2.mergeCells(startR, startC, startR, startC + 2);
        const stc = ws2.getCell(startR, startC); stc.value = title; stc.fill = fl(titleBg); stc.font = fn(true, 11, C.blanco); stc.alignment = al('left');
        ws2.getRow(startR + 1).height = 15;
        headers.forEach((h, i) => { const hc = ws2.getCell(startR+1, startC+i); hc.value = h; hc.fill = fl(C.grisMed); hc.font = fn(true, 9, C.negro); hc.alignment = al(i===0?'left':'center'); hc.border = bd('CBD5E1'); });
        const maxVal = data.length > 0 ? (parseFloat(data[0][1]) || 1) : 1;
        data.forEach((d, idx) => {
            const dr2 = startR + 2 + idx; ws2.getRow(dr2).height = 14;
            const bg = idx % 2 === 0 ? C.blanco : C.gris;
            d.forEach((v, ci) => { const dc = ws2.getCell(dr2, startC+ci); dc.value = v; dc.fill = fl(bg); dc.border = bd(); dc.font = ci===1 ? fn(true,10,barColor||C.azul) : fn(false,10); dc.alignment = al(ci===0?'left':'center'); });
            const barCell = ws2.getCell(dr2, startC + 3);
            const barW = Math.max(0, Math.min(18, Math.round(((parseFloat(d[1])||0) / maxVal) * 18)));
            barCell.value = '|'.repeat(barW); barCell.font = { size:7, color:{ argb:'FF'+(barColor||C.azul) }, bold:true }; barCell.fill = fl(bg);
        });
        return startR + 2 + data.length;
    }

    ws2.mergeCells(1, 1, 1, 10);
    const tS = ws2.getCell('A1'); tS.value = 'ESTADÍSTICAS — Novitecnología Cía. Ltda.';
    tS.fill = fl(C.azulO); tS.font = fn(true, 15, C.blanco); tS.alignment = al('center'); ws2.getRow(1).height = 32;
    ws2.mergeCells(2, 1, 2, 10);
    const dS = ws2.getCell('A2'); dS.value = `Generado: ${new Date().toLocaleString('es-EC')}   |   ${total} órdenes en el período`;
    dS.fill = fl(C.azulL); dS.font = fn(false, 10, C.azulO, { italic:true }); dS.alignment = al('center'); ws2.getRow(2).height = 16;
    ws2.getRow(3).height = 8;

    [4, 5, 6].forEach(rn => ws2.getRow(rn).height = rn === 5 ? 28 : 14);
    kpis.forEach((k, i) => {
        const col = i + 2;
        const lc = ws2.getCell(4, col); lc.value = k.l; lc.fill = fl(k.bg); lc.font = fn(true, 8, k.fg); lc.alignment = al('center'); lc.border = bd();
        const vc = ws2.getCell(5, col); vc.value = k.v; vc.fill = fl(k.bg); vc.font = fn(true, 18, k.fg); vc.alignment = al('center'); vc.border = bd();
        const pc = ws2.getCell(6, col); pc.value = k.p; pc.fill = fl(k.bg); pc.font = fn(false, 9, k.fg); pc.alignment = al('center'); pc.border = bd();
    });
    ws2.getRow(7).height = 10;

    const estados2 = estadosList.map(e => [e[0], e[1], e[2]]);
    let lE = addSec2('RESUMEN POR ESTADOS', ['Estado','Cantidad','%'], estados2, 8, 2, C.azulO, C.azul); lE++;
    lE = addSec2('TOP MARCAS', ['Marca','Órdenes','%'], mT2.map(x=>[x[0],x[1],pp(x[1])]), lE, 2, C.violet, C.violet); lE++;
    let rE = addSec2('TOP TÉCNICOS', ['Técnico','Órdenes','%'], tT2.map(x=>[x[0],x[1],pp(x[1])]), 8, 7, C.teal, C.teal); rE++;
    rE = addSec2('TOP TIPOS EQUIPO', ['Tipo','Órdenes','%'], tiT2.map(x=>[x[0],x[1],pp(x[1])]), rE, 7, 'F59E0B', 'B45309');
    const bR = Math.max(lE, rE) + 1;
    const toArr = Object.entries(countBy(_filtered, 'tipo_orden')).map(x => [x[0].charAt(0).toUpperCase()+x[0].slice(1), x[1], pp(x[1])]);
    addSec2('TIPO DE ORDEN', ['Tipo','Órdenes','%'], toArr, bR, 2, '0369A1', '0369A1');

    /* Guardar */
    const buffer = await wb.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = `reporte_novitec_${new Date().toISOString().slice(0,10)}.xlsx`;
    document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
}

})();
</script>
@endpush

@include('layouts.asistente_widget')

