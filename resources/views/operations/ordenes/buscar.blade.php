@extends('layouts.app')
@section('titulo', 'Buscar Órdenes')

@push('css_adicional')
<style>
/* ═══════════════════════════════════════════
   MÓDULO BÚSQUEDA DE ÓRDENES — Rediseño
═══════════════════════════════════════════ */
.bo-wrap { max-width: 1080px; margin: 0 auto; padding: 26px 20px; }

/* ── Header ─────────────────────────────── */
.bo-header { margin-bottom: 24px; }
.bo-header h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 4px; display:flex; align-items:center; gap:10px; }
.bo-header p  { margin: 0; color: #64748b; font-size: 13px; }

/* ── Panel de búsqueda ──────────────────── */
.bo-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
}

/* Tabs de tipo */
.bo-tipos { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
.bo-tipo {
    padding: 8px 16px;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all .15s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.bo-tipo:hover { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }
.bo-tipo.activo { background: #2563eb; border-color: #2563eb; color: #fff; }
.bo-tipo i { font-size: 14px; }

/* Barra de búsqueda */
.bo-search-row {
    display: flex;
    gap: 10px;
    align-items: stretch;
}
.bo-search-input-wrap {
    flex: 1;
    position: relative;
}
.bo-search-input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
    pointer-events: none;
}
.bo-input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    color: #0f172a;
    background: #f8fafc;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
    box-sizing: border-box;
}
.bo-input:focus {
    outline: none;
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.bo-btn-buscar {
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 11px 22px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}
.bo-btn-buscar:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
.bo-btn-buscar:disabled { opacity:.55; cursor:not-allowed; transform:none; }
.bo-btn-limpiar {
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 11px 16px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.bo-btn-limpiar:hover { background: #e2e8f0; }

/* Filtros avanzados */
.bo-filtros-toggle {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 14px;
    color: #64748b;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    width: fit-content;
    user-select: none;
}
.bo-filtros-toggle:hover { color: #2563eb; }
.bo-filtros-toggle i { transition: transform .2s; }
.bo-filtros-toggle.abierto i { transform: rotate(180deg); }

.bo-filtros-panel {
    display: none;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.bo-filtros-panel.abierto { display: grid; }
.bo-filtros-panel .campo { display:flex; flex-direction:column; gap:5px; margin:0; }
.bo-filtros-panel .campo label { font-size:11.5px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.04em; }
.bo-filtros-panel .campo select,
.bo-filtros-panel .campo input {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 13px;
    color: #0f172a;
    background: #f8fafc;
    font-family: inherit;
}
.bo-filtros-panel .campo select:focus,
.bo-filtros-panel .campo input:focus {
    outline: none;
    border-color: #2563eb;
}

/* Mensaje de estado */
.bo-msg {
    display: none;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
    padding: 11px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}
.bo-msg-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.bo-msg-ok  { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }

/* ── Resultados ─────────────────────────── */
.bo-res-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.bo-res-count {
    font-size: 13.5px;
    font-weight: 700;
    color: #475569;
}
.bo-res-count span { color: #2563eb; }

.bo-grid-resultados {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 14px;
}

.bo-card-orden {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, transform .1s;
    position: relative;
}
.bo-card-orden:hover {
    border-color: #2563eb;
    box-shadow: 0 4px 20px rgba(37,99,235,.12);
    transform: translateY(-2px);
}
.bo-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.bo-nro {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    font-family: 'Courier New', monospace;
}
.bo-tipo-pill {
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    text-transform: uppercase;
}
.bo-tipo-pill.empresa { background: #ede9fe; color: #6d28d9; }

.bo-badges { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:12px; }
.bo-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}
.st-pendiente  { background:#fef9c3; color:#854d0e; }
.st-proceso    { background:#dbeafe; color:#1e40af; }
.st-finalizada { background:#dcfce7; color:#166534; }
.st-entregada  { background:#ecfdf5; color:#047857; }
.st-nc         { background:#fce7f3; color:#9d174d; }
.st-otro       { background:#f1f5f9; color:#475569; }

.bo-card-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 12px;
}
.bo-field-lbl { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform:uppercase; letter-spacing:.04em; }
.bo-field-val { font-size: 13px; font-weight: 600; color: #1e293b; margin-top:1px; }

.bo-card-icon {
    position: absolute;
    bottom: 14px;
    right: 14px;
    color: #cbd5e1;
    font-size: 18px;
    transition: color .15s;
}
.bo-card-orden:hover .bo-card-icon { color: #2563eb; }

/* ── Panel de detalle ───────────────────── */
.bo-det-wrap {
    display: none;
    animation: fadeIn .2s ease;
}
@keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

.bo-det-nav {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.bo-btn-volver {
    background: #f1f5f9;
    color: #334155;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background .15s;
}
.bo-btn-volver:hover { background: #e2e8f0; }
.bo-det-titulo { font-size: 18px; font-weight: 800; color: #0f172a; }

.bo-det-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.bo-det-sec {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}
.bo-det-sec.full { grid-column: 1 / -1; }
.bo-det-sec-h {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 11px 16px;
    font-size: 12.5px;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .05em;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bo-det-sec-b { padding: 16px; }
.bo-det-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.bo-det-field { }
.bo-det-lbl { font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px; }
.bo-det-val { font-size: 13.5px; font-weight: 600; color: #0f172a; }
.bo-det-val.mono { font-family: 'Courier New', monospace; }

.bo-text-block {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    color: #334155;
    white-space: pre-wrap;
    line-height: 1.6;
    margin-bottom: 10px;
}

/* Acciones del detalle */
.bo-det-acciones {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 18px;
}
.bo-accion {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: opacity .15s, transform .1s;
}
.bo-accion:hover { opacity:.88; transform:translateY(-1px); }
.bo-accion.ot      { background: #0f172a; color: #fff; }
.bo-accion.informe { background: #2563eb; color: #fff; }
.bo-accion.editar  { background: #059669; color: #fff; }

/* Informe badge */
.inf-presente  { display:inline-flex; align-items:center; gap:4px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.inf-ausente   { display:inline-flex; align-items:center; gap:4px; background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

/* Loading skeleton */
.bo-skeleton {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 14px;
}
.bo-skel-card {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
    animation: pulse 1.4s ease infinite;
}
.bo-skel-line {
    background: #f1f5f9;
    border-radius: 6px;
    margin-bottom: 8px;
}
@keyframes pulse {
    0%,100% { opacity:1 }
    50%      { opacity:.55 }
}

/* ── Responsive ─────────────────────────── */
@media (max-width: 900px) {
    .bo-filtros-panel { grid-template-columns: repeat(2, 1fr); }
    .bo-det-grid { grid-template-columns: 1fr; }
    .bo-det-sec.full { grid-column: auto; }
}
@media (max-width: 640px) {
    .bo-tipos { gap:6px; }
    .bo-tipo  { padding: 7px 11px; font-size: 12px; }
    .bo-search-row { flex-wrap: wrap; }
    .bo-btn-buscar, .bo-btn-limpiar { flex: 1; justify-content: center; }
    .bo-filtros-panel { grid-template-columns: 1fr 1fr; }
    .bo-grid-resultados { grid-template-columns: 1fr; }
    .bo-det-inner { grid-template-columns: 1fr; }
    .bo-det-acciones { flex-direction: column; }
    .bo-accion { justify-content: center; }
}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="bo-wrap">

    {{-- ─── Header ─── --}}
    <div class="bo-header">
        <h2><i class="bi bi-search" style="color:#2563eb;"></i>Buscar Órdenes</h2>
        <p>Busca por número de orden, cliente, técnico, empresa, serie, cédula o factura. Usa los filtros avanzados para acotar resultados.</p>
    </div>

    {{-- ─── Panel de búsqueda ─── --}}
    <div class="bo-panel">

        {{-- Tipos de búsqueda --}}
        <div class="bo-tipos" id="bo-tipos">
            <button class="bo-tipo activo" data-tipo="nro_orden">
                <i class="bi bi-hash"></i>Nro. Orden
            </button>
            <button class="bo-tipo" data-tipo="nombre">
                <i class="bi bi-person"></i>Nombre / Cliente
            </button>
            <button class="bo-tipo" data-tipo="cedula">
                <i class="bi bi-card-text"></i>Cédula / RUC
            </button>
            <button class="bo-tipo" data-tipo="tecnico">
                <i class="bi bi-wrench"></i>Técnico
            </button>
            <button class="bo-tipo" data-tipo="empresa">
                <i class="bi bi-building"></i>Empresa
            </button>
            <button class="bo-tipo" data-tipo="serie">
                <i class="bi bi-upc"></i>Serie
            </button>
            <button class="bo-tipo" data-tipo="factura">
                <i class="bi bi-receipt"></i>Factura / Ticket
            </button>
        </div>

        {{-- Input de búsqueda --}}
        <div class="bo-search-row">
            <div class="bo-search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="bo-q" class="bo-input"
                    placeholder="Ej: UIO-000001"
                    autocomplete="off"
                    autofocus>
            </div>
            <button id="bo-btn-buscar" class="bo-btn-buscar">
                <i class="bi bi-search"></i>
                <span>Buscar</span>
            </button>
            <button id="bo-btn-limpiar" class="bo-btn-limpiar" title="Limpiar">
                <i class="bi bi-x-lg"></i>
                Limpiar
            </button>
        </div>

        {{-- Toggle filtros avanzados --}}
        <div class="bo-filtros-toggle" id="bo-filtros-toggle">
            <i class="bi bi-sliders"></i>
            Filtros avanzados
            <i class="bi bi-chevron-down"></i>
        </div>

        {{-- Panel de filtros avanzados --}}
        <div class="bo-filtros-panel" id="bo-filtros-panel">
            <div class="campo">
                <label>Estado de la orden</label>
                <select id="flt-estado">
                    <option value="">— Todos —</option>
                    @foreach($estados as $e)
                        <option value="{{ $e }}">{{ $e }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label>Técnico</label>
                <select id="flt-tecnico">
                    <option value="0">— Todos —</option>
                    @foreach($tecnicos as $t)
                        <option value="{{ $t->id }}">{{ $t->nombre_tecnico }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label>Fecha desde</label>
                <input type="date" id="flt-desde">
            </div>
            <div class="campo">
                <label>Fecha hasta</label>
                <input type="date" id="flt-hasta">
            </div>
        </div>

        {{-- Mensaje --}}
        <div id="bo-msg" class="bo-msg"></div>
    </div>

    {{-- ─── Esqueleto de carga ─── --}}
    <div id="bo-loading" style="display:none;">
        <div class="bo-skeleton">
            @for($i = 0; $i < 6; $i++)
            <div class="bo-skel-card">
                <div class="bo-skel-line" style="height:16px;width:50%;"></div>
                <div class="bo-skel-line" style="height:12px;width:30%;"></div>
                <div class="bo-skel-line" style="height:12px;width:80%;"></div>
                <div class="bo-skel-line" style="height:12px;width:60%;"></div>
            </div>
            @endfor
        </div>
    </div>

    {{-- ─── Lista de resultados ─── --}}
    <div id="bo-resultados" style="display:none;">
        <div class="bo-res-header">
            <div class="bo-res-count" id="bo-res-count"></div>
        </div>
        <div class="bo-grid-resultados" id="bo-list"></div>
    </div>

    {{-- ─── Panel de detalle ─── --}}
    <div class="bo-det-wrap" id="bo-detalle">
        <div class="bo-det-nav">
            <button class="bo-btn-volver" id="bo-btn-volver">
                <i class="bi bi-arrow-left"></i>Volver a resultados
            </button>
            <div class="bo-det-titulo" id="bo-det-titulo"></div>
        </div>
        <div id="bo-det-content"></div>
    </div>

</div>
</section>
@endsection

@push('js_adicional')
<script>
(function () {
    'use strict';

    @php
        $usuario = auth()->user();
        $rolNombre = mb_strtolower(trim((string) ($usuario?->rol?->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) ($usuario?->grupo?->nombre ?? '')));
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $tienePermisoEditar = session('es_superadmin') === true 
            || !empty(session('permisos', [])['ordenes_editar']['editar']) 
            || !empty(session('permisos', [])['ordenes_editar']['ver']);
        $esAdminOAdminMaster = in_array($rolNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($grupoNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || $tienePermisoEditar;
    @endphp

    /* ── Configuración ─────────────────────────────────────────── */
    var URL_BUSCAR    = '{{ route("ordenes_buscar.listar") }}';
    var URL_OT        = '{{ url("/operaciones/ordenes") }}/';
    var URL_OT_EMP    = '{{ url("/operaciones/ordenes-empresa") }}/';
    var URL_INFORME   = '{{ url("/operaciones/informes") }}/';
    var URL_EDITAR    = '{{ url("/operaciones/ordenes/editar") }}/';
    var PUEDE_EDITAR  = @json($esAdminOAdminMaster);

    /* ── Estado interno ───────────────────────────────────────── */
    var _tipo       = 'nro_orden';
    var _resultados = [];

    /* ── Referencias DOM ──────────────────────────────────────── */
    var elTipos      = document.getElementById('bo-tipos');
    var elQ          = document.getElementById('bo-q');
    var elBtnBuscar  = document.getElementById('bo-btn-buscar');
    var elBtnLimpiar = document.getElementById('bo-btn-limpiar');
    var elMsg        = document.getElementById('bo-msg');
    var elLoading    = document.getElementById('bo-loading');
    var elResultados = document.getElementById('bo-resultados');
    var elResCount   = document.getElementById('bo-res-count');
    var elList       = document.getElementById('bo-list');
    var elDetalle    = document.getElementById('bo-detalle');
    var elDetTitulo  = document.getElementById('bo-det-titulo');
    var elDetContent = document.getElementById('bo-det-content');
    var elBtnVolver  = document.getElementById('bo-btn-volver');
    var elFiltrosToggle = document.getElementById('bo-filtros-toggle');
    var elFiltrosPanel  = document.getElementById('bo-filtros-panel');

    // Filtros avanzados
    var elFltEstado  = document.getElementById('flt-estado');
    var elFltTecnico = document.getElementById('flt-tecnico');
    var elFltDesde   = document.getElementById('flt-desde');
    var elFltHasta   = document.getElementById('flt-hasta');

    /* ── Placeholders por tipo ────────────────────────────────── */
    var placeholders = {
        nro_orden: 'Ej: UIO-000001 o número consecutivo',
        nombre:    'Ej: Juan Pérez',
        cedula:    'Ej: 1712345678',
        tecnico:   'Ej: Carlos',
        empresa:   'Ej: TechCorp',
        serie:     'Ej: SN123456',
        factura:   'Ej: 001-001-000000123',
    };

    /* ── Helpers de mensajes ──────────────────────────────────── */
    function mostrarMsg(texto, error) {
        error = (error !== false);
        elMsg.className  = 'bo-msg ' + (error ? 'bo-msg-err' : 'bo-msg-ok');
        elMsg.innerHTML  = '<i class="bi bi-' + (error ? 'exclamation-circle' : 'check-circle') + '"></i>' + texto;
        elMsg.style.display = 'flex';
    }
    function ocultarMsg() { elMsg.style.display = 'none'; }

    /* ── Estado visual ────────────────────────────────────────── */
    function claseEstado(v) {
        var t = (v || '').toLowerCase().trim();
        if (t === 'pendiente' || t === 'abierta') return 'st-pendiente';
        if (t === 'en proceso')                   return 'st-proceso';
        if (t === 'finalizada')                   return 'st-finalizada';
        if (t === 'entregada')                    return 'st-entregada';
        if (t === 'nota de credito')              return 'st-nc';
        return 'st-otro';
    }

    function badgesOrden(o) {
        var out = '<span class="bo-badge ' + claseEstado(o.estado_orden) + '">' + (o.estado_orden || '—') + '</span>';
        if (o.estado_repuesto && o.estado_repuesto !== 'No requerido') {
            out += ' <span class="bo-badge st-otro">' + o.estado_repuesto + '</span>';
        }
        return out;
    }

    /* ── Render lista ─────────────────────────────────────────── */
    function renderResultados(items) {
        _resultados = items;

        var total = items.length;
        elResCount.innerHTML = '<span>' + total + '</span> orden' + (total !== 1 ? 'es' : '') + ' encontrada' + (total !== 1 ? 's' : '');

        elList.innerHTML = '';
        items.forEach(function (o) {
            var esEmpresa  = o.tipo_orden === 'empresa';
            var nombreEquipo = [o.tipo, o.marca, o.modelo].filter(Boolean).join(' ') || '—';
            var cliente    = o.cliente || [o.nombres, o.apellidos].filter(Boolean).join(' ') || '—';

            var card = document.createElement('div');
            card.className = 'bo-card-orden';
            card.innerHTML =
                '<div class="bo-card-top">' +
                    '<div class="bo-nro">' + (o.nro_orden || '—') + '</div>' +
                    '<span class="bo-tipo-pill ' + (esEmpresa ? 'empresa' : '') + '">' +
                        (esEmpresa ? 'Empresa' : 'Personal') +
                    '</span>' +
                '</div>' +
                '<div class="bo-badges">' + badgesOrden(o) + '</div>' +
                '<div class="bo-card-info">' +
                    '<div>' +
                        '<div class="bo-field-lbl">Cliente</div>' +
                        '<div class="bo-field-val">' + escHtml(cliente) + '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="bo-field-lbl">Técnico</div>' +
                        '<div class="bo-field-val">' + escHtml(o.tecnico || '—') + '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="bo-field-lbl">Equipo</div>' +
                        '<div class="bo-field-val">' + escHtml(nombreEquipo) + '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="bo-field-lbl">Ingreso</div>' +
                        '<div class="bo-field-val">' + escHtml(o.fecha_de_ingreso || '—') + '</div>' +
                    '</div>' +
                '</div>' +
                '<i class="bi bi-chevron-right bo-card-icon"></i>';

            card.addEventListener('click', function () { renderDetalle(o); });
            elList.appendChild(card);
        });

        elLoading.style.display    = 'none';
        elResultados.style.display = 'block';
        elDetalle.style.display    = 'none';
    }

    /* ── Render detalle ───────────────────────────────────────── */
    function renderDetalle(o) {
        var esEmpresa     = o.tipo_orden === 'empresa';
        var clienteNombre = [o.nombres, o.apellidos].filter(Boolean).join(' ') || o.cliente || '—';
        var facturas      = esEmpresa
            ? (o.nro_factura || '—')
            : ([o.nro_factura, o.nro_factura_2].filter(Boolean).join(' / ') || '—');
        var equipoNombre  = [o.tipo, o.marca, o.modelo].filter(Boolean).join(' ') || '—';
        var tieneInforme  = !!o.informe_id;

        elDetTitulo.innerHTML =
            '<span style="font-family:monospace;">' + escHtml(o.nro_orden || '') + '</span>' +
            ' <span style="font-size:13px;font-weight:600;color:#64748b;">' +
            badgesOrden(o) + '</span>';

        elDetContent.innerHTML =
            /* ── Orden ── */
            '<div class="bo-det-grid">' +
            '<div class="bo-det-sec">' +
                '<div class="bo-det-sec-h"><i class="bi bi-file-text"></i>Orden</div>' +
                '<div class="bo-det-sec-b">' +
                    '<div class="bo-det-inner">' +
                        campo('Tipo',        esEmpresa ? 'Empresa' : 'Personal') +
                        campo('Sucursal',    o.sucursal || '—') +
                        campo('Técnico',     o.tecnico  || '—') +
                        campo('Ingreso',     o.fecha_de_ingreso || '—') +
                        campo('Entrega prom.', o.fecha_entrega || '—') +
                        campo(esEmpresa ? 'Nro. Ticket' : 'Nro. Factura', facturas) +
                    '</div>' +
                    (o.motivo_ingreso
                        ? '<div class="bo-det-lbl" style="margin-top:10px;margin-bottom:4px;">Motivo</div>' +
                          '<div class="bo-text-block">' + escHtml(o.motivo_ingreso) + '</div>'
                        : '') +
                '</div>' +
            '</div>' +

            /* ── Cliente ── */
            '<div class="bo-det-sec">' +
                '<div class="bo-det-sec-h"><i class="bi bi-person"></i>Cliente</div>' +
                '<div class="bo-det-sec-b">' +
                    '<div class="bo-det-inner">' +
                        campo('Nombre',         clienteNombre) +
                        campo('Identificación', o.identificacion  || '—') +
                        campo('Teléfono',       o.numero_contacto || '—') +
                        campo('Correo',         o.correo          || '—') +
                    '</div>' +
                '</div>' +
            '</div>' +

            /* ── Equipo ── */
            '<div class="bo-det-sec">' +
                '<div class="bo-det-sec-h"><i class="bi bi-cpu"></i>Equipo</div>' +
                '<div class="bo-det-sec-b">' +
                    '<div class="bo-det-inner">' +
                        campo('Tipo',   o.tipo   || '—') +
                        campo('Marca',  o.marca  || '—') +
                        campo('Modelo', o.modelo || '—') +
                        campo(o.serie && o.serie.indexOf('|') !== -1 ? 'Series' : 'Serie', o.serie || '—') +
                        campo('Código Producto', o.producto_inventario_codigo || '—') +
                    '</div>' +
                    (o.falla
                        ? '<div class="bo-det-lbl" style="margin-top:10px;margin-bottom:4px;">Falla reportada</div>' +
                          '<div class="bo-text-block">' + escHtml(o.falla) + '</div>'
                        : '') +
                    (o.observacion
                        ? '<div class="bo-det-lbl" style="margin-bottom:4px;">Observación</div>' +
                          '<div class="bo-text-block">' + escHtml(o.observacion) + '</div>'
                        : '') +
                '</div>' +
            '</div>' +

            /* ── Informe técnico ── */
            '<div class="bo-det-sec">' +
                '<div class="bo-det-sec-h">' +
                    '<i class="bi bi-file-earmark-medical"></i>Informe Técnico&nbsp;' +
                    (tieneInforme
                        ? '<span class="inf-presente"><i class="bi bi-check-circle"></i>Registrado</span>'
                        : '<span class="inf-ausente"><i class="bi bi-dash-circle"></i>Sin informe</span>') +
                '</div>' +
                '<div class="bo-det-sec-b">' +
                    (tieneInforme
                        ? '<div class="bo-det-inner">' +
                              campo('Fecha informe',  o.fecha_informe || '—') +
                              campo('Estado equipo',  o.estado_equipo || '—') +
                          '</div>' +
                          (o.antecedentes
                              ? '<div class="bo-det-lbl" style="margin-top:10px;margin-bottom:4px;">Antecedentes</div>' +
                                '<div class="bo-text-block">' + escHtml(o.antecedentes) + '</div>'
                              : '') +
                          (o.conclusion
                              ? '<div class="bo-det-lbl" style="margin-bottom:4px;">Conclusión</div>' +
                                '<div class="bo-text-block">' + escHtml(o.conclusion) + '</div>'
                              : '')
                        : '<div style="color:#94a3b8;font-size:13px;">Esta orden aún no tiene informe técnico registrado.</div>') +
                '</div>' +
            '</div>' +

            '</div>' + /* cierra bo-det-grid */
            '<div id="reingresos-history-container"></div>' +
            /* ── Acciones ── */
            '<div class="bo-det-acciones" id="bo-det-acciones"></div>';

        // Fetch reingresos history
        fetch('/operaciones/ordenes/historial-reingresos?orden_id=' + o.orden_id + '&tipo_orden=' + o.tipo_orden)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('reingresos-history-container');
                if (container && data.ok && data.historial && data.historial.length > 0) {
                    let rows = '';
                    data.historial.forEach((h, idx) => {
                        rows += `
                            <tr style="font-size: 13px;">
                                <td style="border: 1px solid #e2e8f0; padding: 10px; font-weight: bold;">Ingreso #${idx + 1}</td>
                                <td style="border: 1px solid #e2e8f0; padding: 10px; font-weight: bold; color: #2563eb;">${h.nro_orden}</td>
                                <td style="border: 1px solid #e2e8f0; padding: 10px;">${h.fecha_ingreso}</td>
                                <td style="border: 1px solid #e2e8f0; padding: 10px;">${h.tecnico_ingreso}</td>
                                <td style="border: 1px solid #e2e8f0; padding: 10px;">${h.tecnico_asignado}</td>
                            </tr>
                        `;
                    });
                    container.innerHTML = `
                        <div class="bo-det-sec" style="margin-top:20px;">
                            <div class="bo-det-sec-h"><i class="bi bi-clock-history"></i> Historial de Ingresos Anteriores (Reingresos)</div>
                            <div class="bo-det-sec-b" style="padding:15px; overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse; text-align:left;">
                                    <thead>
                                        <tr style="background:#f8fafc; font-weight:bold; font-size:12px; color:#475569;">
                                            <th style="border: 1px solid #e2e8f0; padding: 10px;">Ingreso #</th>
                                            <th style="border: 1px solid #e2e8f0; padding: 10px;">Nro. Orden</th>
                                            <th style="border: 1px solid #e2e8f0; padding: 10px;">Fecha</th>
                                            <th style="border: 1px solid #e2e8f0; padding: 10px;">Ingresó</th>
                                            <th style="border: 1px solid #e2e8f0; padding: 10px;">Asignado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${rows}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(err => console.error(err));

        // ── Botones de acción ──────────────────────────
        var acciones = document.getElementById('bo-det-acciones');

        // Imprimir OT
        var btnOT   = document.createElement('button');
        btnOT.className = 'bo-accion ot';
        btnOT.innerHTML = '<i class="bi bi-printer"></i>Imprimir OT';
        btnOT.onclick   = function () {
            var base = esEmpresa ? URL_OT_EMP : URL_OT;
            window.open(base + o.orden_id + '/imprimir', '_blank');
        };
        acciones.appendChild(btnOT);

        // Ver / Imprimir informe
        if (tieneInforme) {
            var btnInf   = document.createElement('button');
            btnInf.className = 'bo-accion informe';
            btnInf.innerHTML = '<i class="bi bi-file-earmark-medical"></i>Ver Informe';
            btnInf.onclick   = function () {
                window.open(URL_INFORME + o.informe_id + '/imprimir', '_blank');
            };
            acciones.appendChild(btnInf);
        }

        // Editar
        if (PUEDE_EDITAR) {
            var btnEdit   = document.createElement('button');
            btnEdit.className = 'bo-accion editar';
            btnEdit.innerHTML = '<i class="bi bi-pencil-square"></i>Editar Orden';
            btnEdit.onclick   = function () {
                if (esEmpresa) {
                    window.location.href = '{{ url("/operaciones/ordenes-empresa/editar") }}/' + o.orden_id;
                } else {
                    window.location.href = URL_EDITAR + o.orden_id;
                }
            };
            acciones.appendChild(btnEdit);
        }

        // Mostrar panel
        elResultados.style.display = 'none';
        elDetalle.style.display    = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /* ── Helpers HTML ─────────────────────────────────────────── */
    function campo(lbl, val) {
        return '<div class="bo-det-field">' +
            '<div class="bo-det-lbl">' + escHtml(lbl) + '</div>' +
            '<div class="bo-det-val">' + escHtml(String(val || '—')) + '</div>' +
        '</div>';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ── Buscar ───────────────────────────────────────────────── */
    async function buscar() {
        var q = (elQ.value || '').trim();
        if (!q) { mostrarMsg('Ingresa un valor para buscar.'); return; }

        ocultarMsg();
        elBtnBuscar.disabled  = true;
        elBtnBuscar.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Buscando...</span>';
        elResultados.style.display = 'none';
        elDetalle.style.display    = 'none';
        elLoading.style.display    = 'block';

        try {
            var params = new URLSearchParams({
                tipo: _tipo,
                q:    q,
            });

            // Filtros avanzados
            var estado    = elFltEstado.value;
            var tecnicoId = elFltTecnico.value;
            var desde     = elFltDesde.value;
            var hasta     = elFltHasta.value;
            if (estado)          params.append('estado',      estado);
            if (tecnicoId > 0)   params.append('tecnico_id',  tecnicoId);
            if (desde)           params.append('fecha_desde', desde);
            if (hasta)           params.append('fecha_hasta', hasta);

            var resp = await fetch(URL_BUSCAR + '?' + params.toString(), { cache: 'no-store' });
            var data = await resp.json();

            if (!data.ok) {
                mostrarMsg(data.error || 'No se encontraron resultados.');
                elLoading.style.display = 'none';
                return;
            }

            renderResultados(data.ordenes || []);
            mostrarMsg(data.total + ' orden(es) encontrada(s).', false);

        } catch (err) {
            mostrarMsg('Error de conexión. Intenta de nuevo.');
            elLoading.style.display = 'none';
        } finally {
            elBtnBuscar.disabled  = false;
            elBtnBuscar.innerHTML = '<i class="bi bi-search"></i><span>Buscar</span>';
        }
    }

    /* ── Limpiar ──────────────────────────────────────────────── */
    function limpiar() {
        elQ.value = '';
        elFltEstado.value  = '';
        elFltTecnico.value = '0';
        elFltDesde.value   = '';
        elFltHasta.value   = '';
        ocultarMsg();
        elResultados.style.display = 'none';
        elDetalle.style.display    = 'none';
        elLoading.style.display    = 'none';
        _resultados = [];
        elQ.focus();
    }

    /* ── Event listeners ──────────────────────────────────────── */
    elTipos.addEventListener('click', function (e) {
        var btn = e.target.closest('.bo-tipo');
        if (!btn) return;
        document.querySelectorAll('.bo-tipo').forEach(function (b) { b.classList.remove('activo'); });
        btn.classList.add('activo');
        _tipo          = btn.dataset.tipo;
        elQ.placeholder = placeholders[_tipo] || '';
        elQ.value       = '';
        elQ.focus();
        ocultarMsg();
        elResultados.style.display = 'none';
        elDetalle.style.display    = 'none';
    });

    elBtnBuscar.addEventListener('click', buscar);

    elBtnLimpiar.addEventListener('click', limpiar);

    elBtnVolver.addEventListener('click', function () {
        elDetalle.style.display    = 'none';
        elResultados.style.display = _resultados.length ? 'block' : 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    elQ.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); buscar(); }
    });

    elFiltrosToggle.addEventListener('click', function () {
        var abierto = elFiltrosPanel.classList.toggle('abierto');
        elFiltrosToggle.classList.toggle('abierto', abierto);
    });

}());
</script>
@endpush
