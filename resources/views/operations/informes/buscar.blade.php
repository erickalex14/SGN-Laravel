@extends('layouts.app')
@section('titulo', 'Buscar Informes Técnicos')

@push('css_adicional')
<style>
/* ═══════════════════════════════════════════
   BÚSQUEDA DE INFORMES TÉCNICOS
═══════════════════════════════════════════ */
.bi-wrap { max-width: 1100px; margin: 0 auto; padding: 26px 20px; }

/* ── Header ─────────────────────────────── */
.bi-header { margin-bottom: 24px; }
.bi-header h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 4px; display:flex; align-items:center; gap:10px; }
.bi-header p  { margin: 0; color: #64748b; font-size: 13px; }

/* ── Panel de búsqueda ──────────────────── */
.bi-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
}

/* Tabs */
.bi-tipos { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
.bi-tipo {
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
.bi-tipo:hover { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }
.bi-tipo.activo { background: #2563eb; border-color: #2563eb; color: #fff; }

/* Search row */
.bi-search-row { display: flex; gap: 10px; align-items: stretch; }
.bi-input-wrap { flex: 1; position: relative; }
.bi-input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px; pointer-events: none; }
.bi-input {
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
.bi-input:focus { outline: none; border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.bi-btn-buscar {
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
.bi-btn-buscar:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
.bi-btn-buscar:disabled { opacity:.55; cursor:not-allowed; transform:none; }
.bi-btn-limpiar {
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 11px 16px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background .15s;
}
.bi-btn-limpiar:hover { background: #e2e8f0; }

/* Filtros avanzados */
.bi-filtros-toggle {
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
.bi-filtros-toggle:hover { color: #2563eb; }
.bi-filtros-toggle i.chevron { transition: transform .2s; }
.bi-filtros-toggle.abierto i.chevron { transform: rotate(180deg); }
.bi-filtros-panel {
    display: none;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.bi-filtros-panel.abierto { display: grid; }
.bi-filtros-panel .campo { display:flex; flex-direction:column; gap:5px; margin:0; }
.bi-filtros-panel .campo label { font-size:11.5px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.04em; }
.bi-filtros-panel .campo select,
.bi-filtros-panel .campo input {
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    padding: 8px 10px; font-size: 13px; color: #0f172a;
    background: #f8fafc; font-family: inherit;
}
.bi-filtros-panel .campo select:focus,
.bi-filtros-panel .campo input:focus { outline: none; border-color: #2563eb; }

/* Mensaje */
.bi-msg { display: none; align-items: center; gap: 10px; margin-top: 14px; padding: 11px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; }
.bi-msg-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.bi-msg-ok  { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }

/* ── Resultados ─────────────────────────── */
.bi-res-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.bi-res-count  { font-size: 13.5px; font-weight: 700; color: #475569; }
.bi-res-count span { color: #2563eb; }

/* Tabla de resultados */
.bi-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.04); }
.bi-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.bi-table thead tr { background: #f8fafc; }
.bi-table th { padding: 12px 14px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1.5px solid #e2e8f0; text-align: left; }
.bi-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
.bi-table tbody tr { cursor: pointer; transition: background .1s; }
.bi-table tbody tr:hover td { background: #f0f9ff; }
.bi-table tbody tr:last-child td { border-bottom: none; }
.bi-nro { font-family: 'Courier New', monospace; font-weight: 800; font-size: 13px; color: #0f172a; }

/* Badges */
.bi-badge { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
.st-operativo   { background:#dcfce7; color:#166534; }
.st-reparado    { background:#fef9c3; color:#854d0e; }
.st-sinrep      { background:#fee2e2; color:#991b1b; }
.st-desguace    { background:#ffe4e6; color:#9f1239; }
.st-espera      { background:#dbeafe; color:#1e40af; }
.st-otro-eq     { background:#f1f5f9; color:#475569; }

.tipo-pill { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.tipo-empresa  { background:#ede9fe; color:#6d28d9; }
.tipo-personal { background:#f1f5f9; color:#475569; }

/* Panel detalle */
.bi-det-wrap { display: none; animation: slideIn .2s ease; }
@keyframes slideIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
.bi-det-nav { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
.bi-btn-volver {
    background: #f1f5f9; color: #334155;
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    padding: 8px 16px; font-size: 13px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; gap: 6px;
    transition: background .15s;
}
.bi-btn-volver:hover { background: #e2e8f0; }
.bi-det-titulo { font-size: 18px; font-weight: 800; color: #0f172a; }

.bi-det-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.bi-det-sec { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.bi-det-sec.full { grid-column: 1 / -1; }
.bi-det-sec-h {
    background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    padding: 11px 16px; font-size: 12.5px; font-weight: 800; color: #475569;
    text-transform: uppercase; letter-spacing:.05em;
    display: flex; align-items: center; gap: 8px;
}
.bi-det-sec-b { padding: 16px; }
.bi-det-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.bi-det-lbl { font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px; }
.bi-det-val { font-size: 13.5px; font-weight: 600; color: #0f172a; }
.bi-text-block {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
    padding: 10px 12px; font-size: 13px; color: #334155;
    white-space: pre-wrap; line-height: 1.6; margin-bottom: 8px;
}
.bi-acciones { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
.bi-accion {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700;
    border: none; cursor: pointer; transition: opacity .15s, transform .1s;
}
.bi-accion:hover { opacity:.88; transform:translateY(-1px); }
.bi-accion.imprimir { background: #2563eb; color: #fff; }
.bi-accion.ot       { background: #0f172a; color: #fff; }
.bi-accion.editar   { background: #059669; color: #fff; }

/* Skeleton */
.bi-skeleton { display: flex; flex-direction: column; gap: 4px; }
.bi-skel-row { height: 48px; background: #f1f5f9; border-radius: 6px; animation: pulse 1.4s ease infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }

/* Responsive */
@media (max-width: 900px) {
    .bi-filtros-panel { grid-template-columns: 1fr 1fr; }
    .bi-det-grid { grid-template-columns: 1fr; }
    .bi-det-sec.full { grid-column: auto; }
    .bi-table th:nth-child(4),
    .bi-table td:nth-child(4),
    .bi-table th:nth-child(5),
    .bi-table td:nth-child(5) { display: none; }
}
@media (max-width: 640px) {
    .bi-tipos { gap: 6px; }
    .bi-tipo  { padding: 7px 11px; font-size: 12px; }
    .bi-search-row { flex-wrap: wrap; }
    .bi-btn-buscar, .bi-btn-limpiar { flex: 1; justify-content: center; }
    .bi-filtros-panel { grid-template-columns: 1fr; }
    .bi-det-inner { grid-template-columns: 1fr; }
    .bi-acciones { flex-direction: column; }
    .bi-accion { justify-content: center; }
}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="bi-wrap">

    {{-- ─── Header ─── --}}
    <div class="bi-header">
        <h2><i class="bi bi-file-earmark-medical" style="color:#2563eb;"></i>Buscar Informes Técnicos</h2>
        <p>Busca informes por número de orden, cliente, técnico, empresa o cédula. Usa los filtros avanzados para acotar por estado del equipo o fechas.</p>
    </div>

    {{-- ─── Panel de búsqueda ─── --}}
    <div class="bi-panel">

        {{-- Tipos de búsqueda --}}
        <div class="bi-tipos" id="bi-tipos">
            <button class="bi-tipo activo" data-tipo="nro_orden">
                <i class="bi bi-hash"></i>Nro. Orden
            </button>
            <button class="bi-tipo" data-tipo="nombre">
                <i class="bi bi-person"></i>Nombre / Cliente
            </button>
            <button class="bi-tipo" data-tipo="tecnico">
                <i class="bi bi-wrench"></i>Técnico
            </button>
            <button class="bi-tipo" data-tipo="empresa">
                <i class="bi bi-building"></i>Empresa
            </button>
            <button class="bi-tipo" data-tipo="cedula">
                <i class="bi bi-card-text"></i>Cédula / RUC
            </button>
            <button class="bi-tipo" data-tipo="serie">
                <i class="bi bi-upc"></i>Serie Equipo
            </button>
        </div>

        {{-- Input de búsqueda --}}
        <div class="bi-search-row">
            <div class="bi-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="bi-q" class="bi-input"
                    placeholder="Ej: UIO-000001"
                    autocomplete="off" autofocus>
            </div>
            <button id="bi-btn-buscar" class="bi-btn-buscar">
                <i class="bi bi-search"></i><span>Buscar</span>
            </button>
            <button id="bi-btn-limpiar" class="bi-btn-limpiar">
                <i class="bi bi-x-lg"></i>Limpiar
            </button>
        </div>

        {{-- Toggle filtros avanzados --}}
        <div class="bi-filtros-toggle" id="bi-filtros-toggle">
            <i class="bi bi-sliders"></i>
            Filtros avanzados
            <i class="bi bi-chevron-down chevron"></i>
        </div>

        {{-- Panel de filtros avanzados --}}
        <div class="bi-filtros-panel" id="bi-filtros-panel">
            <div class="campo">
                <label>Estado del equipo</label>
                <select id="flt-estado">
                    <option value="">— Todos —</option>
                    @foreach($estados as $e)
                        <option value="{{ $e }}">{{ $e }}</option>
                    @endforeach
                </select>
            </div>
            @if($esAdmin)
            <div class="campo">
                <label>Técnico</label>
                <select id="flt-tecnico">
                    <option value="0">— Todos —</option>
                    @foreach($tecnicos as $t)
                        <option value="{{ $t->id }}">{{ $t->nombre_tecnico }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div style="display:none;"><select id="flt-tecnico"><option value="0">—</option></select></div>
            @endif
            <div class="campo">
                <label>Fecha informe desde</label>
                <input type="date" id="flt-desde">
            </div>
            <div class="campo">
                <label>Fecha informe hasta</label>
                <input type="date" id="flt-hasta">
            </div>
        </div>

        {{-- Mensaje --}}
        <div id="bi-msg" class="bi-msg"></div>
    </div>

    {{-- ─── Skeleton ─── --}}
    <div id="bi-loading" style="display:none;">
        <div class="bi-skeleton">
            @for($i = 0; $i < 8; $i++)
            <div class="bi-skel-row"></div>
            @endfor
        </div>
    </div>

    {{-- ─── Resultados en tabla ─── --}}
    <div id="bi-resultados" style="display:none;">
        <div class="bi-res-header">
            <div class="bi-res-count" id="bi-res-count"></div>
        </div>
        <div class="bi-table-wrap">
            <table class="bi-table">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Cliente</th>
                        <th>Técnico</th>
                        <th>Equipo</th>
                        <th>Fecha Informe</th>
                        <th>Estado Equipo</th>
                        <th style="text-align:right;"></th>
                    </tr>
                </thead>
                <tbody id="bi-tbody"></tbody>
            </table>
        </div>
    </div>

    {{-- ─── Panel de detalle ─── --}}
    <div class="bi-det-wrap" id="bi-detalle">
        <div class="bi-det-nav">
            <button class="bi-btn-volver" id="bi-btn-volver">
                <i class="bi bi-arrow-left"></i>Volver a resultados
            </button>
            <div class="bi-det-titulo" id="bi-det-titulo"></div>
        </div>
        <div id="bi-det-content"></div>
    </div>

</div>
</section>
@endsection

@push('js_adicional')
<script>
(function () {
    'use strict';

    /* ── Config ────────────────────────────────────────────────── */
    var URL_BUSCAR  = '{{ route("informes.buscar.listar") }}';
    var URL_IMPRIMIR = '{{ url("/operaciones/informes") }}/';
    var URL_EDITAR   = '{{ url("/operaciones/informes") }}/';
    var URL_OT       = '{{ url("/operaciones/ordenes") }}/';
    var URL_OT_EMP   = '{{ url("/operaciones/ordenes-empresa") }}/';
    var PUEDE_EDITAR = @json((bool) ($puedeEditarInforme ?? false));

    /* ── Estado ────────────────────────────────────────────────── */
    var _tipo       = 'nro_orden';
    var _resultados = [];

    /* ── DOM ───────────────────────────────────────────────────── */
    var elTipos    = document.getElementById('bi-tipos');
    var elQ        = document.getElementById('bi-q');
    var elBtnBuscar= document.getElementById('bi-btn-buscar');
    var elBtnLimp  = document.getElementById('bi-btn-limpiar');
    var elMsg      = document.getElementById('bi-msg');
    var elLoading  = document.getElementById('bi-loading');
    var elRes      = document.getElementById('bi-resultados');
    var elCount    = document.getElementById('bi-res-count');
    var elTbody    = document.getElementById('bi-tbody');
    var elDet      = document.getElementById('bi-detalle');
    var elDetTit   = document.getElementById('bi-det-titulo');
    var elDetCont  = document.getElementById('bi-det-content');
    var elVolver   = document.getElementById('bi-btn-volver');
    var elFiltTog  = document.getElementById('bi-filtros-toggle');
    var elFiltPanel= document.getElementById('bi-filtros-panel');
    var elFltEstado  = document.getElementById('flt-estado');
    var elFltTecnico = document.getElementById('flt-tecnico');
    var elFltDesde   = document.getElementById('flt-desde');
    var elFltHasta   = document.getElementById('flt-hasta');

    /* ── Placeholders ──────────────────────────────────────────── */
    var placeholders = {
        nro_orden: 'Ej: UIO-000001',
        nombre:    'Ej: Juan Pérez',
        tecnico:   'Ej: Carlos',
        empresa:   'Ej: TechCorp',
        cedula:    'Ej: 1712345678',
        serie:     'Ej: SN123456',
    };

    /* ── Helpers ───────────────────────────────────────────────── */
    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function claseEstadoEquipo(v) {
        switch ((v || '').trim()) {
            case 'Operativo':             return 'st-operativo';
            case 'Reparado parcialmente': return 'st-reparado';
            case 'Sin reparación posible':return 'st-sinrep';
            case 'Desguace':              return 'st-desguace';
            case 'En espera de repuesto': return 'st-espera';
            default:                      return 'st-otro-eq';
        }
    }

    function mostrarMsg(txt, error) {
        error = (error !== false);
        elMsg.className  = 'bi-msg ' + (error ? 'bi-msg-err' : 'bi-msg-ok');
        elMsg.innerHTML  = '<i class="bi bi-' + (error ? 'exclamation-circle' : 'check-circle') + '"></i>' + esc(txt);
        elMsg.style.display = 'flex';
    }
    function ocultarMsg() { elMsg.style.display = 'none'; }

    /* ── Render tabla ──────────────────────────────────────────── */
    function renderResultados(items) {
        _resultados = items;
        var total   = items.length;
        elCount.innerHTML = '<span>' + total + '</span> informe' + (total !== 1 ? 's' : '') + ' encontrado' + (total !== 1 ? 's' : '');
        elTbody.innerHTML = '';

        items.forEach(function (inf, idx) {
            var estadoCls  = claseEstadoEquipo(inf.estado_equipo);
            var esEmpresa  = inf.tipo_orden === 'empresa';
            var tr         = document.createElement('tr');
            tr.innerHTML =
                '<td><span class="bi-nro">' + esc(inf.nro_orden || '—') + '</span>' +
                ' <span class="tipo-pill ' + (esEmpresa ? 'tipo-empresa' : 'tipo-personal') + '">' +
                (esEmpresa ? 'Empresa' : 'Personal') + '</span></td>' +
                '<td>' + esc(inf.cliente_nombre || '—') + '</td>' +
                '<td>' + esc(inf.tecnico || '—') + '</td>' +
                '<td>' + esc(inf.equipo_nombre || '—') + '</td>' +
                '<td>' + esc(inf.fecha_informe || '—') + '</td>' +
                '<td><span class="bi-badge ' + estadoCls + '">' + esc(inf.estado_equipo || '—') + '</span></td>' +
                '<td style="text-align:right;"><i class="bi bi-chevron-right" style="color:#cbd5e1;"></i></td>';
            tr.addEventListener('click', function () { renderDetalle(inf); });
            elTbody.appendChild(tr);
        });

        elLoading.style.display = 'none';
        elRes.style.display     = 'block';
        elDet.style.display     = 'none';
    }

    /* ── Render detalle ────────────────────────────────────────── */
    function renderDetalle(inf) {
        var esEmpresa = inf.tipo_orden === 'empresa';
        var estadoCls = claseEstadoEquipo(inf.estado_equipo);

        elDetTit.innerHTML =
            '<span style="font-family:monospace;">' + esc(inf.nro_orden || '') + '</span>' +
            ' — <span class="bi-badge ' + estadoCls + '">' + esc(inf.estado_equipo || '') + '</span>';

        elDetCont.innerHTML =
            '<div class="bi-det-grid">' +

            /* Informe */
            '<div class="bi-det-sec">' +
                '<div class="bi-det-sec-h"><i class="bi bi-file-earmark-medical"></i>Informe Técnico</div>' +
                '<div class="bi-det-sec-b">' +
                    '<div class="bi-det-inner">' +
                        camp('Nro. Orden',   inf.nro_orden   || '—') +
                        camp('Tipo',         esEmpresa ? 'Empresa' : 'Personal') +
                        camp('Fecha Informe',inf.fecha_informe|| '—') +
                        camp('Registrado',   inf.fecha_creacion || '—') +
                    '</div>' +
                    (inf.antecedentes
                        ? '<div class="bi-det-lbl" style="margin-top:10px;margin-bottom:4px;">Antecedentes</div>' +
                          '<div class="bi-text-block">' + esc(inf.antecedentes) + '</div>'
                        : '') +
                    (inf.conclusion
                        ? '<div class="bi-det-lbl" style="margin-bottom:4px;">Conclusión</div>' +
                          '<div class="bi-text-block">' + esc(inf.conclusion) + '</div>'
                        : '') +
                '</div>' +
            '</div>' +

            /* Cliente / Equipo */
            '<div class="bi-det-sec">' +
                '<div class="bi-det-sec-h"><i class="bi bi-person"></i>Cliente & Equipo</div>' +
                '<div class="bi-det-sec-b">' +
                    '<div class="bi-det-inner">' +
                        camp('Cliente',       inf.cliente_nombre || '—') +
                        camp('Identificación',inf.identificacion  || '—') +
                        camp('Técnico',        inf.tecnico         || '—') +
                        camp('Factura/Ticket', inf.nro_factura     || '—') +
                        camp('Equipo',         inf.equipo_nombre   || '—') +
                        camp('Serie',          inf.equipo_serie    || '—') +
                    '</div>' +
                '</div>' +
            '</div>' +

            '</div>' + /* cierra bi-det-grid */

            '<div class="bi-acciones" id="bi-det-acciones"></div>';

        /* Botones */
        var accDiv = document.getElementById('bi-det-acciones');

        // Imprimir informe
        var btnImp = document.createElement('button');
        btnImp.className = 'bi-accion imprimir';
        btnImp.innerHTML = '<i class="bi bi-printer"></i>Imprimir Informe';
        btnImp.onclick   = function () { window.open(URL_IMPRIMIR + inf.id + '/imprimir', '_blank'); };
        accDiv.appendChild(btnImp);

        // Imprimir OT
        var btnOT = document.createElement('button');
        btnOT.className = 'bi-accion ot';
        btnOT.innerHTML = '<i class="bi bi-file-text"></i>Ver Orden de Trabajo';
        btnOT.onclick   = function () {
            var ordenAbs = Math.abs(inf.orden_id);
            var base     = esEmpresa ? URL_OT_EMP : URL_OT;
            window.open(base + ordenAbs + '/imprimir', '_blank');
        };
        accDiv.appendChild(btnOT);

        if (PUEDE_EDITAR) {
            var btnEdit = document.createElement('button');
            btnEdit.className = 'bi-accion editar';
            btnEdit.innerHTML = '<i class="bi bi-pencil-square"></i>Editar Informe';
            btnEdit.onclick   = function () {
                window.location.href = URL_EDITAR + inf.id + '/editar';
            };
            accDiv.appendChild(btnEdit);
        }

        elRes.style.display = 'none';
        elDet.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function camp(lbl, val) {
        return '<div class="bi-det-field">' +
            '<div class="bi-det-lbl">' + esc(lbl) + '</div>' +
            '<div class="bi-det-val">' + esc(String(val)) + '</div>' +
        '</div>';
    }

    /* ── Buscar ────────────────────────────────────────────────── */
    async function buscar() {
        var q = (elQ.value || '').trim();
        var estado    = elFltEstado.value;
        var tecnico   = elFltTecnico ? elFltTecnico.value : '0';
        var desde     = elFltDesde.value;
        var hasta     = elFltHasta.value;

        var hayFiltros = estado || (parseInt(tecnico) > 0) || desde || hasta;
        if (!q && !hayFiltros) { mostrarMsg('Ingresa un valor de búsqueda o selecciona al menos un filtro.'); return; }

        ocultarMsg();
        elBtnBuscar.disabled  = true;
        elBtnBuscar.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Buscando...</span>';
        elRes.style.display   = 'none';
        elDet.style.display   = 'none';
        elLoading.style.display = 'block';

        try {
            var params = new URLSearchParams({ tipo: _tipo, q: q });
            if (estado)            params.append('estado',      estado);
            if (parseInt(tecnico) > 0) params.append('tecnico_id',  tecnico);
            if (desde)             params.append('fecha_desde', desde);
            if (hasta)             params.append('fecha_hasta', hasta);

            var resp = await fetch(URL_BUSCAR + '?' + params.toString(), { cache: 'no-store' });
            var data = await resp.json();

            if (!data.ok) {
                mostrarMsg(data.error || 'No se encontraron resultados.');
                elLoading.style.display = 'none';
                return;
            }

            renderResultados(data.informes || []);
            mostrarMsg(data.total + ' informe(s) encontrado(s).', false);
        } catch (err) {
            mostrarMsg('Error de conexión. Intenta de nuevo.');
            elLoading.style.display = 'none';
        } finally {
            elBtnBuscar.disabled  = false;
            elBtnBuscar.innerHTML = '<i class="bi bi-search"></i><span>Buscar</span>';
        }
    }

    /* ── Limpiar ───────────────────────────────────────────────── */
    function limpiar() {
        elQ.value = '';
        elFltEstado.value  = '';
        if (elFltTecnico) elFltTecnico.value = '0';
        elFltDesde.value   = '';
        elFltHasta.value   = '';
        ocultarMsg();
        elRes.style.display     = 'none';
        elDet.style.display     = 'none';
        elLoading.style.display = 'none';
        _resultados = [];
        elQ.focus();
    }

    /* ── Event listeners ───────────────────────────────────────── */
    elTipos.addEventListener('click', function (e) {
        var btn = e.target.closest('.bi-tipo');
        if (!btn) return;
        document.querySelectorAll('.bi-tipo').forEach(function (b) { b.classList.remove('activo'); });
        btn.classList.add('activo');
        _tipo = btn.dataset.tipo;
        elQ.placeholder = placeholders[_tipo] || '';
        elQ.value = '';
        elQ.focus();
        ocultarMsg();
        elRes.style.display = 'none';
        elDet.style.display = 'none';
    });

    elBtnBuscar.addEventListener('click', buscar);
    elBtnLimp.addEventListener('click', limpiar);
    elVolver.addEventListener('click', function () {
        elDet.style.display = 'none';
        elRes.style.display = _resultados.length ? 'block' : 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    elQ.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); buscar(); } });
    elFiltTog.addEventListener('click', function () {
        var ab = elFiltPanel.classList.toggle('abierto');
        elFiltTog.classList.toggle('abierto', ab);
    });
}());
</script>
@endpush
