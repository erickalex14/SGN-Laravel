@extends('layouts.app')
@section('titulo', 'Mis Informes')

@push('css_adicional')
<style>
/* ═══════════════════════════════════════════
   MIS INFORMES TÉCNICOS
═══════════════════════════════════════════ */
.mi-wrap { max-width: 1060px; margin: 0 auto; padding: 26px 20px; }

/* Header */
.mi-hdr { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.mi-hdr-left h2 { font-size:21px; font-weight:800; color:#0f172a; margin:0 0 4px; display:flex; align-items:center; gap:10px; }
.mi-hdr-left p  { color:#64748b; font-size:13px; margin:0; }
.mi-btn-nuevo {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff;
    border:none; border-radius:10px; padding:10px 20px;
    font-size:13.5px; font-weight:700; text-decoration:none;
    cursor:pointer; transition:opacity .2s, transform .1s;
    box-shadow:0 3px 10px rgba(37,99,235,.3);
}
.mi-btn-nuevo:hover { opacity:.9; transform:translateY(-1px); color:#fff; }

/* Barra de filtros */
.mi-filtros {
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    padding:14px 18px; margin-bottom:18px;
    display:flex; align-items:center; gap:10px; flex-wrap:wrap;
    box-shadow:0 1px 6px rgba(0,0,0,.04);
}
.mi-filtros label { font-size:12.5px; font-weight:700; color:#64748b; white-space:nowrap; }
.mi-filtros input, .mi-filtros select {
    border:1.5px solid #e2e8f0; border-radius:8px;
    padding:7px 10px; font-size:13px; color:#0f172a;
    background:#f8fafc; font-family:inherit;
    transition:border-color .2s;
}
.mi-filtros input:focus, .mi-filtros select:focus { outline:none; border-color:#2563eb; }
.mi-filtros input { flex:1; min-width:160px; }
.mi-count { margin-left:auto; font-size:12.5px; color:#94a3b8; white-space:nowrap; }

/* Tabla */
.mi-table-wrap { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.04); }
.mi-table { width:100%; border-collapse:collapse; font-size:13px; }
.mi-table thead tr { background:#f8fafc; }
.mi-table th { padding:12px 14px; font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.05em; border-bottom:1.5px solid #e2e8f0; text-align:left; white-space:nowrap; }
.mi-table td { padding:12px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; vertical-align:middle; }
.mi-table tbody tr:last-child td { border-bottom:none; }
.mi-table tbody tr:hover td { background:#f8fafc; }

/* Nro orden */
.mi-nro { font-family:'Courier New',monospace; font-weight:800; font-size:13px; color:#0f172a; }

/* Tipo pill */
.tipo-pill { font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; }
.tipo-emp  { background:#ede9fe; color:#6d28d9; }
.tipo-pers { background:#f1f5f9; color:#475569; }

/* Estado badge */
.est-badge { font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:20px; white-space:nowrap; }
.est-operativo  { background:#dcfce7; color:#166534; }
.est-reparado   { background:#fef9c3; color:#854d0e; }
.est-sinrep     { background:#fee2e2; color:#991b1b; }
.est-desguace   { background:#ffe4e6; color:#9f1239; }
.est-espera     { background:#dbeafe; color:#1e40af; }
.est-otro       { background:#f1f5f9; color:#475569; }

/* Acciones */
.mi-acciones { display:flex; gap:6px; justify-content:flex-end; }
.mi-accion {
    display:inline-flex; align-items:center; gap:5px;
    padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700;
    border:none; cursor:pointer; transition:opacity .15s, transform .1s;
    text-decoration:none;
}
.mi-accion:hover { opacity:.85; transform:translateY(-1px); }
.mi-accion.editar   { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.mi-accion.imprimir { background:#0f172a; color:#fff; }

/* Vacío */
.mi-empty {
    padding:60px 20px; text-align:center;
    color:#94a3b8; font-size:14px;
}
.mi-empty i { font-size:48px; display:block; margin-bottom:12px; opacity:.4; }
.mi-empty p { margin:0 0 16px; }

/* Responsive */
@media (max-width:900px) {
    .mi-table th:nth-child(4), .mi-table td:nth-child(4),
    .mi-table th:nth-child(6), .mi-table td:nth-child(6) { display:none; }
}
@media (max-width:640px) {
    .mi-table th:nth-child(3), .mi-table td:nth-child(3) { display:none; }
    .mi-acciones { flex-direction:column; }
}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="mi-wrap">

    {{-- Header --}}
    <div class="mi-hdr">
        <div class="mi-hdr-left">
            <h2><i class="bi bi-journal-text" style="color:#2563eb;"></i>Mis Informes</h2>
            <p>Lista de todos tus informes técnicos generados. Edítalos o imprímelos desde aquí.</p>
        </div>
        <a href="{{ route('informes.crear') }}" class="mi-btn-nuevo">
            <i class="bi bi-file-earmark-plus"></i>Crear Informe
        </a>
    </div>

    @if($informes->isEmpty())
        {{-- Estado vacío --}}
        <div class="mi-table-wrap">
            <div class="mi-empty">
                <i class="bi bi-journal-x"></i>
                <p>Aún no has creado ningún informe técnico.</p>
                <a href="{{ route('informes.crear') }}" class="mi-btn-nuevo" style="margin:0 auto;width:fit-content;">
                    <i class="bi bi-file-earmark-plus"></i>Crear mi primer informe
                </a>
            </div>
        </div>
    @else
        {{-- Filtros --}}
        <div class="mi-filtros">
            <label><i class="bi bi-funnel"></i></label>
            <input type="text" id="flt-texto" placeholder="Buscar por orden, cliente o equipo..." oninput="filtrar()">
            <label>Estado:</label>
            <select id="flt-estado" onchange="filtrar()">
                <option value="">— Todos —</option>
                <option value="Operativo">Operativo</option>
                <option value="Reparado parcialmente">Reparado parcialmente</option>
                <option value="Sin reparación posible">Sin reparación posible</option>
                <option value="Desguace">Desguace</option>
                <option value="En espera de repuesto">En espera de repuesto</option>
            </select>
            <span class="mi-count" id="mi-count">{{ $informes->count() }} informe(s)</span>
        </div>

        {{-- Tabla --}}
        <div class="mi-table-wrap">
            <table class="mi-table" id="mi-tabla">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Cliente</th>
                        <th>Equipo</th>
                        <th>Fecha Informe</th>
                        <th>Estado Equipo</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="mi-tbody">
                    @foreach($informes as $inf)
                    <tr data-row="informe"
                        data-nro="{{ strtolower($inf->nro_orden ?? '') }}"
                        data-cliente="{{ strtolower($inf->cliente_nombre ?? '') }}"
                        data-equipo="{{ strtolower($inf->equipo_nombre ?? '') }}"
                        data-estado="{{ $inf->estado_equipo ?? '' }}">
                        <td>
                            <span class="mi-nro">{{ $inf->nro_orden ?? '—' }}</span>
                            <span class="tipo-pill {{ $inf->tipo_orden === 'empresa' ? 'tipo-emp' : 'tipo-pers' }}">
                                {{ $inf->tipo_orden === 'empresa' ? 'Empresa' : 'Personal' }}
                            </span>
                        </td>
                        <td>{{ $inf->cliente_nombre ?? '—' }}</td>
                        <td>{{ $inf->equipo_nombre  ?? '—' }}</td>
                        <td>{{ $inf->fecha_informe  ?? '—' }}</td>
                        <td>
                            @php
                                $cls = match($inf->estado_equipo ?? '') {
                                    'Operativo'              => 'est-operativo',
                                    'Reparado parcialmente'  => 'est-reparado',
                                    'Sin reparación posible' => 'est-sinrep',
                                    'Desguace'               => 'est-desguace',
                                    'En espera de repuesto'  => 'est-espera',
                                    default                  => 'est-otro',
                                };
                            @endphp
                            <span class="est-badge {{ $cls }}">{{ $inf->estado_equipo ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="mi-acciones">
                                <a href="{{ route('informes.crear') }}?orden_id={{ $inf->orden_id }}"
                                   class="mi-accion editar"
                                   title="Editar informe">
                                    <i class="bi bi-pencil-square"></i>Editar
                                </a>
                                <a href="{{ route('informes.imprimir', $inf->id) }}"
                                   target="_blank"
                                   class="mi-accion imprimir"
                                   title="Imprimir informe">
                                    <i class="bi bi-printer"></i>Imprimir
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="informes-pager" style="padding: 10px 20px 20px;"></div>
    @endif

</div>
</section>
@endsection

@push('js_adicional')
<script>
let _informesPager = null;
document.addEventListener('DOMContentLoaded', () => {
    _informesPager = new SgnPager({
        containerSelector: '#mi-tbody',
        itemSelector: 'tr[data-row="informe"]',
        pagerContainerSelector: '#informes-pager',
        pageSize: 15
    });
});

function filtrar() {
    var texto  = (document.getElementById('flt-texto').value  || '').toLowerCase().trim();
    var estado = (document.getElementById('flt-estado').value || '').trim();
    var filas  = document.querySelectorAll('#mi-tbody tr[data-row="informe"]');
    var visibles = 0;

    filas.forEach(function (tr) {
        var nro     = tr.dataset.nro     || '';
        var cliente = tr.dataset.cliente || '';
        var equipo  = tr.dataset.equipo  || '';
        var est     = tr.dataset.estado  || '';

        var matchTexto  = !texto  || nro.includes(texto) || cliente.includes(texto) || equipo.includes(texto);
        var matchEstado = !estado || est === estado;

        var visible = matchTexto && matchEstado;
        tr.style.display = visible ? '' : 'none';
        if (visible) visibles++;
    });

    var cnt = document.getElementById('mi-count');
    if (cnt) cnt.textContent = visibles + ' informe(s)';
}
</script>
@endpush
