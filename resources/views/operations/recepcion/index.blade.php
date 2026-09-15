@extends('layouts.app')
@section('titulo', 'Panel de Recepción')

@push('css_adicional')
<style>
.rec-container { width: 100%; max-width: 100%; margin: 0; padding: 20px 24px; box-sizing: border-box; }
.rec-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 14px; }
.rec-head h2 { margin: 0 0 4px; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
.rec-head p { margin: 0; font-size: 13px; color: #64748b; }
.rec-head-actions { display: flex; align-items: center; gap: 10px; }

.btn-crear-ot { background: #2563eb; color: #fff; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(37,99,235,.25); transition: background .15s, transform .1s; }
.btn-crear-ot:hover { background: #1d4ed8; color: #fff; transform: translateY(-1px); }

/* KPI CARDS */
.rec-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 20px; }
.rec-kpi { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; box-shadow: 0 1px 4px rgba(0,0,0,.03); text-decoration: none; color: inherit; display: block; transition: transform .15s, border-color .15s, box-shadow .15s; }
.rec-kpi:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.rec-kpi.activo { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.2); }
.rec-kpi-lbl { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; display: flex; align-items: center; gap: 5px; }
.rec-kpi-val { margin-top: 6px; font-size: 26px; font-weight: 900; color: #0f172a; line-height: 1; }

.rec-kpi.highlight { border-color: #f59e0b; background: #fffbeb; }
.rec-kpi.highlight .rec-kpi-val { color: #b45309; }
.rec-kpi.success { border-color: #10b981; background: #ecfdf5; }
.rec-kpi.success .rec-kpi-val { color: #047857; }
.rec-kpi.danger { border-color: #f87171; background: #fef2f2; }
.rec-kpi.danger .rec-kpi-val { color: #dc2626; }
.rec-kpi.info { border-color: #38bdf8; background: #f0f9ff; }
.rec-kpi.info .rec-kpi-val { color: #0284c7; }

/* WORKFLOW TABS */
.rec-tabs { display: flex; align-items: center; gap: 8px; margin-bottom: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 0; }
.rec-tab { padding: 10px 18px; font-size: 13.5px; font-weight: 700; color: #64748b; text-decoration: none; border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; transition: all .15s; }
.rec-tab:hover { color: #1e293b; background: rgba(241,245,249,.6); border-top-left-radius: 8px; border-top-right-radius: 8px; }
.rec-tab.active { color: #2563eb; border-bottom-color: #2563eb; background: rgba(37,99,235,.04); border-top-left-radius: 8px; border-top-right-radius: 8px; }
.rec-tab-badge { font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 800; }
.rec-tab.active .rec-tab-badge { background: #2563eb; color: #fff; }
.rec-tab:not(.active) .rec-tab-badge { background: #e2e8f0; color: #475569; }

/* TOOLBAR */
.rec-toolbar { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.02); }
.rec-filters { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.rec-select, .rec-input { padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #0f172a; background: #fff; }
.rec-input { min-width: 260px; }
.rec-select:focus, .rec-input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }

/* TABLE CARD */
.rec-table-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.04); overflow: visible; }
.rec-table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.rec-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; min-width: 1050px; }
.rec-table th { background: #f8fafc; padding: 12px 14px; font-weight: 700; font-size: 11.5px; color: #475569; text-transform: uppercase; letter-spacing: .3px; border-bottom: 1.5px solid #e2e8f0; white-space: nowrap; }
.rec-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.rec-table tr:hover td { background: #f8fafc; }
.rec-table tr.fila-lista-entrega { background: #fffbeb; }
.rec-table tr.fila-lista-entrega:hover td { background: #fef3c7; }
.rec-table tr.fila-finalizada { background: #f0fdf4; }
.rec-table tr.fila-finalizada:hover td { background: #dcfce7; }

/* Sticky column for actions */
.rec-table th.col-acciones,
.rec-table td.col-acciones {
    position: sticky;
    right: 0;
    background: #fff;
    box-shadow: -3px 0 6px rgba(0,0,0,.03);
    z-index: 2;
}
.rec-table th.col-acciones { background: #f8fafc; }
.rec-table tr:hover td.col-acciones { background: #f8fafc; }
.rec-table tr.fila-lista-entrega td.col-acciones { background: #fffbeb; }
.rec-table tr.fila-lista-entrega:hover td.col-acciones { background: #fef3c7; }
.rec-table tr.fila-finalizada td.col-acciones { background: #f0fdf4; }
.rec-table tr.fila-finalizada:hover td.col-acciones { background: #dcfce7; }

/* BADGES */
.badge-estado { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
.badge-Recibido_en_Recepcion, .badge-INGRESO { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 700; }
.badge-Entregado_al_Tecnico, .badge-Recibida { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 700; }
.badge-Pendiente { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
.badge-En_reparacion, .badge-En_proceso { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.badge-Reparada, .badge-Finalizada { background: #dcfce7; color: #166534; font-weight: 800; border: 1.5px solid #86efac; }
.badge-Entregado_en_Recepcion_para_Entrega, .badge-Lista_para_entrega { background: #fef3c7; color: #92400e; border: 1.5px solid #f59e0b; font-weight: 800; }
.badge-Nota_de_Credito { background: #fee2e2; color: #991b1b; border: 1.5px solid #f87171; font-weight: 800; }
.badge-Cerrado, .badge-Entregada { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

/* TIMERS */
.timer-box { font-family: monospace; font-size: 12.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 6px; background: #f1f5f9; color: #334155; white-space: nowrap; }
.timer-box.activo { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.timer-box.pausado { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

/* BUTTONS */
.btn-recibir { background: #0284c7; color: #fff; padding: 6px 12px; border-radius: 7px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all .15s; white-space: nowrap; box-shadow: 0 1px 3px rgba(2,132,199,.3); }
.btn-recibir:hover { background: #0369a1; transform: translateY(-1px); }
.btn-entregar { background: #10b981; color: #fff; padding: 6px 12px; border-radius: 7px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all .15s; white-space: nowrap; box-shadow: 0 1px 3px rgba(16,185,129,.3); }
.btn-entregar:hover { background: #059669; transform: translateY(-1px); }
.btn-ver { background: #f1f5f9; color: #334155; padding: 6px 10px; border-radius: 7px; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e1; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: all .15s; }
.btn-ver:hover { background: #e2e8f0; color: #0f172a; }

.conclusion-text { font-size: 12px; color: #475569; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* MODALS */
.rec-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.65); z-index: 99999; align-items: center; justify-content: center; padding: 16px; backdrop-filter: blur(2px); }
.rec-modal-overlay.open { display: flex; }
.rec-modal { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; box-shadow: 0 20px 40px rgba(0,0,0,.25); overflow: hidden; animation: recModalIn .2s ease; }
@keyframes recModalIn { from { transform: scale(.96); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.rec-modal-hdr { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
.rec-modal-hdr h3 { margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; }
.rec-modal-close { background: none; border: none; font-size: 22px; color: #94a3b8; cursor: pointer; line-height: 1; }
.rec-modal-close:hover { color: #0f172a; }
.rec-modal-body { padding: 20px; }
.rec-modal-ftr { padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; }
</style>
@endpush

@section('contenido')
<div class="rec-container">
    <div class="rec-head">
        <div>
            <h2><i class="bi bi-person-workspace text-primary"></i> Panel de Recepción</h2>
            <p>Monitoreo en tiempo real de órdenes de trabajo, timers de reparación y entrega al cliente.</p>
        </div>
        <div class="rec-head-actions">
            @if($sa && count($sucursales) > 1)
                <form method="GET" action="{{ route('recepcion.index') }}" style="display:inline-block;">
                    <select name="sucursal_id" class="rec-select" onchange="this.form.submit()">
                        <option value="">-- Todas las Sucursales --</option>
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" {{ $sucursalSeleccionada == $s->id ? 'selected' : '' }}>
                                {{ $s->ciudad }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('presupuestos.index') }}" class="btn-crear-ot" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-color: #047857; text-decoration:none;">
                <i class="bi bi-receipt-cutoff"></i> Cotizaciones / Proformas
            </a>
            <a href="{{ route('ordenes.crear') }}" class="btn-crear-ot">
                <i class="bi bi-plus-circle-fill"></i> Nueva Orden de Trabajo
            </a>
        </div>
    </div>

    <!-- KPIs Bar (Click para filtrar) -->
    <div class="rec-kpis">
        <a href="{{ route('recepcion.index', array_merge(request()->except(['estado', 'page']), ['estado' => 'todos'])) }}"
           class="rec-kpi {{ !request('estado') || request('estado') === 'todos' ? 'activo' : '' }}">
            <div class="rec-kpi-lbl">Total Activas</div>
            <div class="rec-kpi-val">{{ $kpis['total_activas'] }}</div>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['estado', 'page']), ['estado' => 'Recibido en Recepcion'])) }}"
           class="rec-kpi {{ request('estado') === 'Recibido en Recepcion' ? 'activo' : '' }}">
            <div class="rec-kpi-lbl"><i class="bi bi-inbox"></i> En Recepción</div>
            <div class="rec-kpi-val">{{ $kpis['recibido_recepcion'] }}</div>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['estado', 'page']), ['estado' => 'en_taller'])) }}"
           class="rec-kpi {{ request('estado') === 'en_taller' ? 'activo' : '' }}">
            <div class="rec-kpi-lbl"><i class="bi bi-tools"></i> En Taller</div>
            <div class="rec-kpi-val">{{ $kpis['en_taller'] }}</div>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['estado', 'page']), ['estado' => 'Reparada'])) }}"
           class="rec-kpi info {{ request('estado') === 'Reparada' ? 'activo' : '' }}">
            <div class="rec-kpi-lbl"><i class="bi bi-check2-all"></i> Reparadas (Por Recibir)</div>
            <div class="rec-kpi-val">{{ $kpis['reparada'] }}</div>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['estado', 'page']), ['estado' => 'Entregado en Recepcion para Entrega'])) }}"
           class="rec-kpi highlight {{ request('estado') === 'Entregado en Recepcion para Entrega' ? 'activo' : '' }}">
            <div class="rec-kpi-lbl"><i class="bi bi-bell-fill"></i> Para Entrega al Cliente</div>
            <div class="rec-kpi-val">{{ $kpis['para_entrega'] }}</div>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['estado', 'page']), ['estado' => 'Nota de Credito'])) }}"
           class="rec-kpi danger {{ request('estado') === 'Nota de Credito' ? 'activo' : '' }}">
            <div class="rec-kpi-lbl"><i class="bi bi-receipt"></i> Notas de Crédito</div>
            <div class="rec-kpi-val">{{ $kpis['notas_credito'] }}</div>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['estado', 'page']), ['estado' => 'Cerrado'])) }}"
           class="rec-kpi success {{ request('estado') === 'Cerrado' ? 'activo' : '' }}">
            <div class="rec-kpi-lbl">
                <i class="bi bi-check-circle-fill"></i>
                @if($flujo === 'antiguo')
                    Cerradas (Histórico)
                @elseif($flujo === 'todos')
                    Total Cerradas
                @else
                    Cerradas Hoy
                @endif
            </div>
            <div class="rec-kpi-val">{{ $kpis['cerradas_hoy'] }}</div>
        </a>
    </div>

    <!-- Pestañas de Navegación de Flujos -->
    <div class="rec-tabs">
        <a href="{{ route('recepcion.index', array_merge(request()->except(['flujo', 'page']), ['flujo' => 'nuevo'])) }}"
           class="rec-tab {{ $flujo === 'nuevo' ? 'active' : '' }}">
            <i class="bi bi-stopwatch"></i>
            <span>Nuevo Flujo (Recepción / Timers)</span>
            <span class="rec-tab-badge">{{ $totalNuevoFlujo }}</span>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['flujo', 'page']), ['flujo' => 'antiguo'])) }}"
           class="rec-tab {{ $flujo === 'antiguo' ? 'active' : '' }}">
            <i class="bi bi-archive-fill"></i>
            <span>Órdenes Flujo Anterior</span>
            <span class="rec-tab-badge">{{ $totalAntiguoFlujo }}</span>
        </a>
        <a href="{{ route('recepcion.index', array_merge(request()->except(['flujo', 'page']), ['flujo' => 'todos'])) }}"
           class="rec-tab {{ $flujo === 'todos' ? 'active' : '' }}">
            <i class="bi bi-layers-fill"></i>
            <span>Todas</span>
            <span class="rec-tab-badge">{{ $totalNuevoFlujo + $totalAntiguoFlujo }}</span>
        </a>
    </div>

    <!-- Toolbar de filtros -->
    <div class="rec-toolbar">
        <form method="GET" action="{{ route('recepcion.index') }}" class="rec-filters">
            @if($sa && $sucursalSeleccionada > 0)
                <input type="hidden" name="sucursal_id" value="{{ $sucursalSeleccionada }}">
            @endif
            @if(request('flujo'))
                <input type="hidden" name="flujo" value="{{ request('flujo') }}">
            @endif
            <select name="estado" class="rec-select" onchange="this.form.submit()">
                <option value="todos" {{ request('estado') === 'todos' ? 'selected' : '' }}>-- Todos los estados --</option>
                <option value="Recibido en Recepcion" {{ request('estado') === 'Recibido en Recepcion' ? 'selected' : '' }}>Recibido en Recepción</option>
                <option value="en_taller" {{ request('estado') === 'en_taller' ? 'selected' : '' }}>En Taller (Técnico / En Reparación)</option>
                <option value="Reparada" {{ request('estado') === 'Reparada' ? 'selected' : '' }}>Reparada (Por recibir en recepción)</option>
                <option value="Entregado en Recepcion para Entrega" {{ request('estado') === 'Entregado en Recepcion para Entrega' ? 'selected' : '' }}>Entregado en Recepción para Entrega</option>
                <option value="Nota de Credito" {{ request('estado') === 'Nota de Credito' ? 'selected' : '' }}>Nota de Crédito</option>
                <option value="Cerrado" {{ request('estado') === 'Cerrado' ? 'selected' : '' }}>Cerrado (Entregada)</option>
            </select>

            <input type="text" name="buscar" class="rec-input" placeholder="Buscar por # orden, cliente, serie..."
                   value="{{ request('buscar') }}">

            <button type="submit" class="btn-ver" style="padding:8px 14px;">
                <i class="bi bi-search"></i> Filtrar
            </button>
            @if(request()->hasAny(['estado', 'buscar']))
                <a href="{{ route('recepcion.index', array_merge($sa && $sucursalSeleccionada > 0 ? ['sucursal_id' => $sucursalSeleccionada] : [], request('flujo') ? ['flujo' => request('flujo')] : [])) }}" class="btn-ver">
                    Limpiar
                </a>
            @endif
        </form>

        <div style="font-size:12.5px; color:#64748b;">
            Mostrando <b>{{ $ordenes->count() }}</b> de <b>{{ $ordenes->total() }}</b> órdenes
        </div>
    </div>

    <!-- Tabla Principal -->
    <div class="rec-table-card">
        <div class="rec-table-responsive">
            <table class="rec-table">
                <thead>
                    <tr>
                        <th style="width: 130px;"># Orden</th>
                        <th style="width: 210px;">Cliente / Contacto</th>
                        <th style="width: 200px;">Equipo</th>
                        <th style="width: 140px;">Técnico</th>
                        <th style="width: 120px;">Estado</th>
                        <th style="width: 130px;">⏱️ Timer Reparación</th>
                        <th>Conclusión / Nota de Crédito</th>
                        <th class="col-acciones" style="width: 170px; text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $ord)
                        @php
                            $estadoCss = str_replace(' ', '_', $ord->estado_orden);
                            $esParaEntrega = in_array($ord->estado_orden, ['Entregado en Recepcion para Entrega', 'Lista para entrega'], true);
                            $esReparada = in_array($ord->estado_orden, ['Reparada', 'Finalizada'], true);
                            $esNotaCredito = $ord->estado_orden === 'Nota de Credito';
                            $ncAprobada = $esNotaCredito && $ord->nc_estado === 'Aprobada';
                        @endphp
                        <tr class="{{ $esParaEntrega ? 'fila-lista-entrega' : ($esReparada ? 'fila-finalizada' : '') }}" id="fila-orden-{{ $ord->id }}">
                            <td>
                                <div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">
                                    <a href="javascript:void(0)" onclick="verDetalle({{ $ord->id }}, '{{ $ord->tipo_orden }}')" style="font-family:monospace; font-weight:800; color:#2563eb; font-size:13.5px; text-decoration:none;">
                                        {{ $ord->nro_orden }}
                                    </a>
                                    @if($ord->tipo_orden === 'empresa')
                                        <span style="font-size:10px; font-weight:800; padding:1px 5px; border-radius:4px; background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe;" title="Orden Corporativa">
                                            {{ str_replace('Empresa · ', '', $ord->motivo_ingreso) }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:11px; color:#94a3b8; margin-top:2px;" title="Ingresado por {{ $ord->usuarioIngreso?->nombre_tecnico ?? ($ord->usuarioIngreso?->usuario ?? 'Recepción') }}">
                                    Por: <b>{{ Str::limit($ord->usuarioIngreso?->nombre_tecnico ?? ($ord->usuarioIngreso?->usuario ?? 'Recepción'), 14) }}</b>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:700; color:#0f172a;" title="{{ $ord->cliente?->nombres }} {{ $ord->cliente?->apellidos }}">
                                    {{ Str::limit(($ord->cliente?->nombres . ' ' . $ord->cliente?->apellidos), 24) }}
                                </div>
                                <div style="font-size:12px; color:#64748b; margin-top:2px; display:flex; align-items:center; gap:6px;">
                                    @if($ord->cliente?->numero_contacto)
                                        <a href="tel:{{ $ord->cliente->numero_contacto }}" style="color:#2563eb; font-weight:700; text-decoration:none;" title="Llamar">
                                            <i class="bi bi-telephone-fill"></i> {{ $ord->cliente->numero_contacto }}
                                        </a>
                                        <a href="https://wa.me/593{{ ltrim(preg_replace('/[^0-9]/', '', $ord->cliente->numero_contacto), '0') }}" target="_blank" style="color:#10b981; text-decoration:none; font-weight:700; display:inline-flex; align-items:center; gap:2px;" title="Chat WhatsApp">
                                            <i class="bi bi-whatsapp"></i> Chat
                                        </a>
                                    @else
                                        <span style="color:#94a3b8;">Sin teléfono</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; color:#334155;" title="{{ $ord->equipo?->tipo }} {{ $ord->equipo?->marca }} {{ $ord->equipo?->modelo }}">
                                    {{ Str::limit(($ord->equipo?->tipo . ' ' . $ord->equipo?->marca . ' ' . $ord->equipo?->modelo), 24) }}
                                </div>
                                <div style="font-size:11px; color:#94a3b8; font-family:monospace;">
                                    S/N: {{ Str::limit($ord->equipo?->serie ?: 'N/A', 16) }}
                                </div>
                            </td>
                            <td>
                                <span style="font-weight:600; color:#1e293b;" title="{{ $ord->tecnico?->nombre_tecnico ?? 'No asignado' }}">
                                    {{ Str::limit($ord->tecnico?->nombre_tecnico ?? 'No asignado', 16) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-estado badge-{{ $estadoCss }}">
                                    {{ $ord->estado_orden }}
                                </span>
                                @if($esNotaCredito && $ord->nc_estado)
                                    <div style="margin-top:3px;">
                                        <span style="font-size:10px; font-weight:700; padding:2px 5px; border-radius:4px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; white-space:nowrap;">
                                            NC: {{ $ord->nc_estado }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($ord->fecha_recibida_tecnico)
                                    <span class="timer-box {{ $ord->timer_activo ? 'activo js-live-timer' : 'pausado' }}"
                                          data-segundos="{{ $ord->timer_segundos }}"
                                          data-activo="{{ $ord->timer_activo ? '1' : '0' }}">
                                        <i class="bi bi-stopwatch"></i>
                                        <span class="timer-val">{{ $ord->timer_formateado }}</span>
                                    </span>
                                @elseif(!$ord->es_nuevo_flujo)
                                    <span style="display:inline-block; font-size:11px; padding:2px 7px; border-radius:6px; background:#f1f5f9; color:#64748b; font-weight:600; border:1px solid #e2e8f0;">
                                        <i class="bi bi-clock-history"></i> Flujo tradicional
                                    </span>
                                @else
                                    <span style="color:#94a3b8; font-size:12px; font-style:italic;">No iniciada</span>
                                @endif
                            </td>
                            <td>
                                @if($esNotaCredito && $ord->nc_estado)
                                    <div style="font-size:12px; color:#b91c1c; font-weight:700;">
                                        <i class="bi bi-receipt"></i> Solicitud NC: {{ $ord->nc_estado }}
                                    </div>
                                    @if($ord->nc_asunto)
                                        <div class="conclusion-text" style="color:#64748b;" title="{{ $ord->nc_asunto }}">
                                            {{ $ord->nc_asunto }}
                                        </div>
                                    @endif
                                @elseif($ord->conclusion_tecnica)
                                    <div class="conclusion-text" title="{{ $ord->conclusion_tecnica }}">
                                        <i class="bi bi-file-earmark-text text-primary"></i> {{ $ord->conclusion_tecnica }}
                                    </div>
                                @else
                                    <span style="color:#94a3b8; font-size:11.5px; font-style:italic;">Pendiente</span>
                                @endif
                            </td>
                            <td class="col-acciones" style="text-align:right; white-space:nowrap;">
                                @if($ord->es_nuevo_flujo && ($esReparada || (in_array($ord->estado_orden, ['Entregado en Recepcion para Entrega', 'Lista para entrega'], true) && !$ord->fecha_lista_entrega)))
                                    <button type="button" class="btn-recibir" onclick="recibirDeTecnico({{ $ord->id }}, '{{ $ord->nro_orden }}', '{{ $ord->tipo_orden }}')" title="Confirmar recepción física de equipo reparado">
                                        <i class="bi bi-inbox-fill"></i> Recibido
                                    </button>
                                @endif
                                @if($esParaEntrega || (!$ord->es_nuevo_flujo && in_array($ord->estado_orden, ['Finalizada', 'Lista para entrega'], true)))
                                    <button type="button" class="btn-entregar" onclick="abrirModalEntrega({{ $ord->id }}, '{{ $ord->nro_orden }}', '{{ addslashes($ord->cliente?->nombres . ' ' . $ord->cliente?->apellidos) }}', false, '{{ $ord->tipo_orden }}')" title="Entregar y cerrar orden">
                                        <i class="bi bi-box-arrow-up-right"></i> Entregar
                                    </button>
                                @endif
                                @if($ncAprobada || ($esNotaCredito && in_array($ord->nc_estado, ['Aprobada', 'aprobada'], true)))
                                    <button type="button" class="btn-entregar" style="background:#9d174d; border-color:#9d174d; box-shadow:0 1px 4px rgba(157,23,77,.35);" onclick="abrirModalEntrega({{ $ord->id }}, '{{ $ord->nro_orden }}', '{{ addslashes($ord->cliente?->nombres . ' ' . $ord->cliente?->apellidos) }}', true, '{{ $ord->tipo_orden }}')" title="Cerrar orden por Nota de Crédito">
                                        <i class="bi bi-x-circle-fill"></i> Cerrar
                                    </button>
                                @endif
                                <button type="button" class="btn-ver" onclick="verDetalle({{ $ord->id }}, '{{ $ord->tipo_orden }}')" title="Ver detalles completos">
                                    <i class="bi bi-eye"></i> Detalle
                                </button>
                                <a href="{{ $ord->tipo_orden === 'empresa' ? url('/operaciones/ordenes-empresa/' . $ord->id . '/imprimir') : url('/operaciones/ordenes/' . $ord->id . '/imprimir') }}" target="_blank" class="btn-ver" title="Imprimir Orden">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:36px; color:#94a3b8;">
                                <i class="bi bi-inbox" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                                No hay órdenes de trabajo que coincidan con el filtro seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ordenes->hasPages())
            <div style="padding:14px 18px; border-top:1px solid #e2e8f0; background:#f8fafc;">
                {{ $ordenes->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Entregar Orden al Cliente / Cerrar NC -->
<div id="modal-entrega" class="rec-modal-overlay">
    <div class="rec-modal">
        <div class="rec-modal-hdr">
            <h3 id="entrega-modal-title"><i class="bi bi-box-arrow-up-right text-success"></i> Entregar Orden de Trabajo</h3>
            <button type="button" class="rec-modal-close" onclick="cerrarModalEntrega()">&times;</button>
        </div>
        <form id="form-entrega" onsubmit="event.preventDefault(); confirmarEntrega();">
            <input type="hidden" id="entrega-orden-id" name="orden_id">
            <input type="hidden" id="entrega-tipo-orden" name="tipo_orden" value="personal">
            <input type="hidden" id="entrega-es-nc" value="0">
            <div class="rec-modal-body">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:16px;">
                    <div style="font-size:12px; color:#64748b;" id="entrega-lbl-subtitulo">Orden a entregar:</div>
                    <div id="entrega-nro-orden" style="font-size:16px; font-weight:800; color:#2563eb; font-family:monospace;"></div>
                    <div id="entrega-cliente-nombre" style="font-size:13.5px; font-weight:700; color:#0f172a; margin-top:2px;"></div>
                    <div id="entrega-badge-nc" style="display:none; margin-top:6px;">
                        <span style="font-size:11px; font-weight:800; background:#fce7f3; color:#9d174d; border:1px solid #f9a8d4; border-radius:12px; padding:3px 10px; display:inline-flex; align-items:center; gap:4px;">
                            <i class="bi bi-receipt"></i> Nota de Crédito Aprobada
                        </span>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:5px;">
                        <span id="entrega-lbl-memo">Memo / Observación de Entrega</span> <span style="color:#ef4444;">*</span>
                    </label>
                    <textarea id="entrega-memo" name="memo_entrega" rows="3" required class="rec-input" style="width:100%; box-sizing:border-box;"
                              placeholder="Indique las condiciones en que se entrega el equipo y conformidad del cliente..."></textarea>
                </div>

                <div style="margin-bottom:10px;">
                    <label style="display:block; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:5px;">
                        <span id="entrega-lbl-foto">Foto de Evidencia de Entrega</span> <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="file" id="entrega-foto" name="foto_evidencia" accept="image/*,application/pdf" capture="environment" class="rec-input" style="width:100%; box-sizing:border-box;">
                    <small id="entrega-hint-foto" style="color:#64748b; font-size:11.5px; display:block; margin-top:4px;">
                        Puede tomar una foto directa con la cámara del dispositivo o subir desde la galería.
                    </small>
                </div>
            </div>
            <div class="rec-modal-ftr">
                <button type="button" class="btn-ver" onclick="cerrarModalEntrega()">Cancelar</button>
                <button type="submit" id="btn-submit-entrega" class="btn-entregar" style="padding:8px 18px; font-size:13px;">
                    <i class="bi bi-check-circle-fill"></i> <span id="btn-submit-entrega-txt">Confirmar Entrega</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Detalle de Orden -->
<div id="modal-detalle" class="rec-modal-overlay">
    <div class="rec-modal" style="max-width:680px;">
        <div class="rec-modal-hdr">
            <h3><i class="bi bi-info-circle text-primary"></i> Detalle de la Orden</h3>
            <button type="button" class="rec-modal-close" onclick="cerrarModalDetalle()">&times;</button>
        </div>
        <div class="rec-modal-body" id="detalle-body" style="max-height:75vh; overflow-y:auto;">
            <div style="text-align:center; padding:30px; color:#94a3b8;">
                <i class="bi bi-hourglass-split" style="font-size:24px;"></i> Cargando...
            </div>
        </div>
        <div class="rec-modal-ftr">
            <button type="button" class="btn-ver" onclick="cerrarModalDetalle()">Cerrar</button>
        </div>
    </div>
</div>
@endsection

@push('scripts_adicionales')
<script>
// Live Timer JS
document.addEventListener('DOMContentLoaded', () => {
    setInterval(() => {
        document.querySelectorAll('.js-live-timer').forEach(el => {
            let segs = parseInt(el.dataset.segundos || '0', 10);
            segs++;
            el.dataset.segundos = segs;
            const span = el.querySelector('.timer-val');
            if (span) {
                span.textContent = formatSecs(segs);
            }
        });
    }, 1000);
});

function formatSecs(s) {
    const d = Math.floor(s / 86400);
    const h = Math.floor((s % 86400) / 3600);
    const m = Math.floor((s % 3600) / 60);
    const seg = s % 60;
    if (d > 0) return `${d}d ${h}h ${m}m`;
    if (h > 0) return `${h}h ${m}m ${seg}s`;
    return `${m}m ${seg}s`;
}

// Recibir de Técnico en Recepción
async function recibirDeTecnico(id, nro, tipoOrden = 'personal') {
    const confirmResult = await Swal.fire({
        title: `¿Recibir equipo #${nro}?`,
        text: '¿Confirmas que el equipo ha sido recibido físicamente en Recepción listo para entrega? Esto detendrá el cronómetro de reparación técnica.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0284c7',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-inbox-fill me-1"></i> Sí, Recibido',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmResult.isConfirmed) return;

    Swal.showLoading();

    try {
        const res = await fetch('{{ route("recepcion.recibir") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ orden_id: id, tipo_orden: tipoOrden })
        });

        const data = await res.json();
        if (data.ok) {
            await Swal.fire({
                icon: 'success',
                title: '¡Equipo Recibido!',
                text: data.mensaje || 'La orden pasó a Lista para Entrega.',
                timer: 1800,
                showConfirmButton: false
            });
            window.location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo recibir',
                text: data.error || 'Ocurrió un error inesperado.'
            });
        }
    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Error de red',
            text: 'No se pudo contactar al servidor.'
        });
    }
}

// Modal Entrega
function abrirModalEntrega(id, nro, cliente, esNc = false, tipoOrden = 'personal') {
    document.getElementById('entrega-orden-id').value = id;
    document.getElementById('entrega-tipo-orden').value = tipoOrden;
    document.getElementById('entrega-es-nc').value = esNc ? '1' : '0';
    document.getElementById('entrega-nro-orden').textContent = nro;
    document.getElementById('entrega-cliente-nombre').textContent = cliente;
    document.getElementById('entrega-memo').value = '';
    document.getElementById('entrega-foto').value = '';

    const titleEl = document.getElementById('entrega-modal-title');
    const badgeNc = document.getElementById('entrega-badge-nc');
    const lblSub = document.getElementById('entrega-lbl-subtitulo');
    const lblMemo = document.getElementById('entrega-lbl-memo');
    const txtMemo = document.getElementById('entrega-memo');
    const lblFoto = document.getElementById('entrega-lbl-foto');
    const hintFoto = document.getElementById('entrega-hint-foto');
    const btnSubmit = document.getElementById('btn-submit-entrega');
    const btnSubmitTxt = document.getElementById('btn-submit-entrega-txt');

    if (esNc) {
        titleEl.innerHTML = '<i class="bi bi-receipt-cutoff" style="color:#9d174d;"></i> Cerrar Orden por Nota de Crédito';
        badgeNc.style.display = 'block';
        lblSub.textContent = 'Orden con NC aprobada a cerrar:';
        lblMemo.textContent = 'Memo / Detalle de Cierre de NC';
        txtMemo.placeholder = 'Indique la comunicación con el cliente y detalles del cierre por Nota de Crédito...';
        lblFoto.textContent = 'Foto o Evidencia de la NC (Comprobante / Documento firmado)';
        hintFoto.textContent = 'Adjunte la foto o archivo PDF del comprobante de Nota de Crédito firmado por el cliente.';
        btnSubmit.style.background = '#9d174d';
        btnSubmit.style.borderColor = '#9d174d';
        btnSubmitTxt.textContent = 'Confirmar Cierre de NC';
    } else {
        titleEl.innerHTML = '<i class="bi bi-box-arrow-up-right text-success"></i> Entregar Orden de Trabajo';
        badgeNc.style.display = 'none';
        lblSub.textContent = 'Orden a entregar:';
        lblMemo.textContent = 'Memo / Observación de Entrega';
        txtMemo.placeholder = 'Indique las condiciones en que se entrega el equipo y conformidad del cliente...';
        lblFoto.textContent = 'Foto de Evidencia de Entrega';
        hintFoto.textContent = 'Puede tomar una foto directa con la cámara del dispositivo o subir desde la galería.';
        btnSubmit.style.background = '#10b981';
        btnSubmit.style.borderColor = '#10b981';
        btnSubmitTxt.textContent = 'Confirmar Entrega';
    }

    document.getElementById('modal-entrega').classList.add('open');
}

function cerrarModalEntrega() {
    document.getElementById('modal-entrega').classList.remove('open');
}

async function confirmarEntrega() {
    const btn = document.getElementById('btn-submit-entrega');
    const form = document.getElementById('form-entrega');
    const esNc = document.getElementById('entrega-es-nc').value === '1';
    const memo = document.getElementById('entrega-memo').value.trim();
    const foto = document.getElementById('entrega-foto').files[0];

    if (!memo) {
        Swal.fire({
            icon: 'warning',
            title: 'Memo obligatorio',
            text: esNc 
                ? 'Por favor ingrese el memo o detalle del cierre de la Nota de Crédito.'
                : 'Por favor ingrese el memo u observación de entrega del equipo.'
        });
        return;
    }
    if (!foto) {
        Swal.fire({
            icon: 'warning',
            title: esNc ? 'Evidencia de NC obligatoria' : 'Foto obligatoria',
            text: esNc 
                ? 'Por favor adjunte la foto o archivo PDF del comprobante de Nota de Crédito firmado.'
                : 'Por favor adjunte la foto o captura de evidencia de entrega firmada/comprobante.'
        });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    const formData = new FormData(form);

    try {
        const res = await fetch('{{ route("recepcion.entregar") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await res.json();
        if (data.ok) {
            cerrarModalEntrega();
            await Swal.fire({
                icon: 'success',
                title: esNc ? '¡Orden Cerrada por NC!' : '¡Orden Entregada!',
                text: data.mensaje || (esNc ? 'La orden con Nota de Crédito ha sido cerrada exitosamente.' : 'La orden ha sido entregada al cliente exitosamente.'),
                timer: 2000,
                showConfirmButton: false
            });
            window.location.reload();
        } else {
            let errorMsg = data.error || data.mensaje || 'No se pudo registrar la acción.';
            if (data.errors && typeof data.errors === 'object') {
                errorMsg = Object.values(data.errors).flat().join('<br>');
            }
            Swal.fire({
                icon: 'error',
                title: esNc ? 'Error al cerrar NC' : 'Error de entrega',
                html: errorMsg
            });
            btn.disabled = false;
            btn.innerHTML = esNc 
                ? '<i class="bi bi-check-circle-fill"></i> Confirmar Cierre de NC' 
                : '<i class="bi bi-check-circle-fill"></i> Confirmar Entrega';
        }
    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'Ocurrió un fallo al comunicarse con el servidor.'
        });
        btn.disabled = false;
        btn.innerHTML = esNc 
            ? '<i class="bi bi-check-circle-fill"></i> Confirmar Cierre de NC' 
            : '<i class="bi bi-check-circle-fill"></i> Confirmar Entrega';
    }
}

// Modal Detalle
async function verDetalle(id, tipoOrden = 'personal') {
    const modal = document.getElementById('modal-detalle');
    const body = document.getElementById('detalle-body');
    modal.classList.add('open');
    body.innerHTML = '<div style="text-align:center; padding:30px; color:#94a3b8;"><i class="bi bi-hourglass-split" style="font-size:24px;"></i> Cargando detalle...</div>';

    try {
        const res = await fetch(`{{ url('/operaciones/recepcion/detalle') }}/${id}?tipo_orden=${tipoOrden}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!data.ok || !data.orden) {
            body.innerHTML = '<div style="color:#ef4444; padding:20px;">No se pudo cargar el detalle.</div>';
            return;
        }

        const o = data.orden;
        body.innerHTML = `
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:16px;">
                <div style="background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Orden de Trabajo</div>
                    <div style="font-size:16px; font-weight:800; color:#2563eb; font-family:monospace;">${o.nro_orden}</div>
                    <div style="font-size:12px; color:#475569; margin-top:2px;">Estado: <b>${o.estado}</b></div>
                    <div style="font-size:12px; color:#475569;">Motivo: ${o.motivo_ingreso}</div>
                    <div style="font-size:11px; color:#94a3b8; margin-top:4px;">Ingresado por: <b>${o.ingresado_por}</b></div>
                </div>

                <div style="background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Técnico Asignado</div>
                    <div style="font-size:14px; font-weight:700; color:#0f172a;">${o.tecnico}</div>
                    <div style="margin-top:6px; font-size:12px; color:#64748b;">⏱️ Tiempo en Reparación:</div>
                    <div style="font-family:monospace; font-weight:800; color:#059669; font-size:13.5px;">${o.timer_formateado || '0m 0s'}</div>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <div style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; border-bottom:1px solid #e2e8f0; padding-bottom:4px; margin-bottom:8px;">Datos del Cliente</div>
                <div style="font-size:13px; color:#0f172a;"><b>Nombre:</b> ${o.cliente}</div>
                <div style="font-size:13px; color:#0f172a;"><b>C.I / RUC:</b> ${o.identificacion}</div>
                <div style="font-size:13px; color:#0f172a;"><b>Teléfono:</b> ${o.telefono}</div>
                <div style="font-size:13px; color:#0f172a;"><b>Correo:</b> ${o.correo || '-'}</div>
            </div>

            <div style="margin-bottom:14px;">
                <div style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; border-bottom:1px solid #e2e8f0; padding-bottom:4px; margin-bottom:8px;">Equipo y Falla Reportada</div>
                <div style="font-size:13px; color:#0f172a;"><b>Equipo:</b> ${o.equipo}</div>
                <div style="font-size:13px; color:#0f172a;"><b>Serie:</b> ${o.serie}</div>
                <div style="font-size:13px; color:#0f172a;"><b>Falla:</b> ${o.falla}</div>
                <div style="font-size:12.5px; color:#64748b; margin-top:2px;"><b>Observación recepción:</b> ${o.observacion || '-'}</div>
            </div>

            <div style="margin-bottom:14px;">
                <div style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; border-bottom:1px solid #e2e8f0; padding-bottom:4px; margin-bottom:8px;">Hitos de Fecha y Tiempo</div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px; font-size:12px;">
                    <div>📅 <b>Ingreso:</b> ${o.fecha_ingreso}</div>
                    <div>⏱️ <b>Recibida por Técnico:</b> ${o.fecha_recibida_tecnico}</div>
                    <div>🔧 <b>Finalización Técnica:</b> ${o.fecha_finalizacion}</div>
                    <div>📦 <b>Devuelta a Recepción:</b> ${o.fecha_lista_entrega}</div>
                    <div>🤝 <b>Entrega al Cliente:</b> ${o.fecha_entrega}</div>
                </div>
            </div>

            ${o.nc_estado ? `
                <div style="background:#fef2f2; border:1.5px solid #fecaca; border-radius:8px; padding:12px; margin-bottom:14px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:12px; font-weight:800; color:#991b1b; text-transform:uppercase;">
                            <i class="bi bi-receipt"></i> Solicitud de Nota de Crédito
                        </span>
                        <span style="background:#fee2e2; color:#991b1b; font-weight:700; font-size:11.5px; padding:2px 8px; border-radius:12px; border:1px solid #fca5a5;">
                            ${o.nc_estado}
                        </span>
                    </div>
                    <div style="font-size:12.5px; color:#1f2937;"><b>Asunto:</b> ${o.nc_asunto || 'N/A'}</div>
                    <div style="font-size:12px; color:#4b5563; margin-top:3px;"><b>Detalles:</b> ${o.nc_detalles || '-'}</div>
                    ${o.nc_respuesta ? `<div style="font-size:12px; color:#15803d; margin-top:4px; background:#f0fdf4; padding:6px; border-radius:4px;"><b>Respuesta Administración:</b> ${o.nc_respuesta}</div>` : ''}
                </div>
            ` : ''}

            ${o.conclusion_tecnica ? `
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; color:#166534; text-transform:uppercase;">Conclusión del Informe Técnico</div>
                    <div style="font-size:13px; color:#14532d; margin-top:4px;">${o.conclusion_tecnica}</div>
                </div>
            ` : ''}

            ${o.factura_adjunta ? `
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px; margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; color:#1e40af; text-transform:uppercase; margin-bottom:6px;">
                        <i class="bi bi-file-earmark-text"></i> Factura Adjunta de Ingreso
                    </div>
                    <a href="${o.factura_adjunta.path}" target="_blank" style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #93c5fd; padding:6px 12px; border-radius:6px; color:#1d4ed8; text-decoration:none; font-size:12.5px; font-weight:700;">
                        <i class="${o.factura_adjunta.path.endsWith('.pdf') ? 'bi bi-file-earmark-pdf-fill text-danger' : 'bi bi-image text-primary'}"></i>
                        Ver Factura (${o.factura_adjunta.nombre})
                    </a>
                </div>
            ` : ''}

            ${o.fotos_ingreso && o.fotos_ingreso.length > 0 ? `
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; color:#334155; text-transform:uppercase; margin-bottom:8px;">
                        <i class="bi bi-camera"></i> Fotos de Ingreso del Equipo (${o.fotos_ingreso.length} Fotos)
                    </div>
                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px;">
                        ${o.fotos_ingreso.map((f, idx) => `
                            <a href="${f.path}" target="_blank" style="display:block; border-radius:6px; overflow:hidden; border:1px solid #cbd5e1; position:relative; aspect-ratio:4/3; background:#000;">
                                <img src="${f.path}" alt="Foto ${idx+1}" style="width:100%; height:100%; object-fit:cover; display:block;">
                                <span style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,.6); color:#fff; font-size:10px; padding:2px 4px; text-align:center;">Foto ${idx+1}</span>
                            </a>
                        `).join('')}
                    </div>
                </div>
            ` : ''}

            ${o.memo_entrega ? `
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-top:10px;">
                    <div style="font-size:11px; font-weight:800; color:#475569; text-transform:uppercase;">Registro de Entrega / Cierre</div>
                    <div style="font-size:12.5px; color:#1e293b; margin-top:4px;"><b>Memo:</b> ${o.memo_entrega}</div>
                    ${o.foto_evidencia_entrega ? `
                        <div style="margin-top:8px;">
                            <a href="${o.foto_evidencia_entrega}" target="_blank" style="color:#2563eb; font-size:12px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
                                <i class="${o.foto_evidencia_entrega.endsWith('.pdf') ? 'bi bi-file-earmark-pdf-fill text-danger' : 'bi bi-image'}"></i> 
                                ${o.foto_evidencia_entrega.endsWith('.pdf') ? 'Ver Documento de Evidencia (PDF)' : 'Ver Foto de Evidencia'}
                            </a>
                        </div>
                    ` : ''}
                </div>
            ` : ''}
        `;
    } catch (e) {
        body.innerHTML = '<div style="color:#ef4444; padding:20px;">Error de conexión al cargar detalle.</div>';
    }
}

function cerrarModalDetalle() {
    document.getElementById('modal-detalle').classList.remove('open');
}
</script>
@endpush
