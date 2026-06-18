@extends('layouts.app')
@section('titulo', 'Ordenes Asignadas')

@push('css_adicional')
<style>
.oa-container { max-width: 1200px; margin: 0 auto; padding: 28px 24px; }
.oa-head { margin-bottom: 18px; }
.oa-head h2 { margin: 0 0 4px; font-size: 21px; font-weight: 800; color: #0f172a; }
.oa-head p { margin: 0; font-size: 13px; color: #94a3b8; }
.oa-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
.oa-kpi { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 11px 13px; }
.oa-kpi-lbl { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .35px; }
.oa-kpi-val { margin-top: 4px; font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1; }
.oa-global-empty { text-align: center; color: #94a3b8; padding: 40px; font-size: 14px; background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; }

.oa-tecnico-bloque { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 14px; margin-bottom: 14px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.oa-tec-header { display: flex; align-items: center; gap: 12px; padding: 14px 18px; cursor: pointer; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
.oa-tec-header:hover { background: #f1f5f9; }
.oa-tec-avatar { width: 36px; height: 36px; border-radius: 50%; color: #fff; font-size: 15px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.oa-tec-nombre { font-size: 15px; font-weight: 700; color: #0f172a; flex: 1; }
.oa-tec-badges { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.oa-badge-asig { background: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.oa-badge-entr { background: #dcfce7; color: #166534; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.oa-badge-carga { font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 20px; border: 1px solid transparent; }
.oa-chevron { font-size: 11px; color: #94a3b8; transition: transform .2s; }
.oa-chevron.open { transform: rotate(180deg); }

.oa-tec-body { display: none; padding: 14px 16px 18px; }
.oa-tec-body.open { display: block; }
.oa-sub-title { display: flex; align-items: center; gap: 8px; margin: 2px 0 10px; font-size: 13px; font-weight: 700; color: #1e293b; }
.oa-sub-pill { margin-left: auto; background: #e2e8f0; color: #334155; font-size: 11px; padding: 2px 8px; border-radius: 20px; }
.oa-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(275px, 1fr)); gap: 12px; margin-bottom: 14px; }
.oa-empty { color: #94a3b8; font-size: 13px; padding: 14px; text-align: center; border: 1.5px dashed #e2e8f0; border-radius: 10px; }

.oa-card { border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; }
.oa-card-top { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px 8px; border-bottom: 1px solid #f1f5f9; }
.oa-nro { font-family: monospace; font-weight: 800; color: #2563eb; font-size: 13px; }
.oa-status { font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 20px; }
.oa-status.pend { background: #fef9c3; color: #854d0e; }
.oa-status.proc { background: #dbeafe; color: #1e40af; }
.oa-status.fin { background: #dcfce7; color: #166534; }
.oa-status.ent { background: #f1f5f9; color: #475569; }
.oa-status.def { background: #f1f5f9; color: #475569; }
.oa-cliente { padding: 8px 12px 4px; font-size: 13px; font-weight: 700; color: #0f172a; }
.oa-equipo { padding: 0 12px 6px; font-size: 12px; color: #475569; }
.oa-meta-row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; padding: 8px 12px; border-top: 1px solid #f1f5f9; }
.oa-meta { font-size: 11px; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 2px 8px; }
.oa-actions { display: flex; gap: 8px; padding: 9px 12px 12px; border-top: 1px solid #f1f5f9; }
.btn-det { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 5px; border-radius: 7px; padding: 7px 10px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid transparent; }
.btn-det.ver { cursor: pointer; }
.btn-det.ot { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.btn-det.ver { background: #f8fafc; color: #334155; border-color: #e2e8f0; }

.oa-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.48); display: none; align-items: center; justify-content: center; z-index: 9999; }
.oa-modal-overlay.open { display: flex; }
.oa-modal-box { width: 95%; max-width: 680px; max-height: 86vh; overflow-y: auto; border-radius: 16px; background: #fff; box-shadow: 0 20px 60px rgba(0,0,0,.25); padding: 24px 26px; position: relative; }
.oa-modal-close { position: absolute; top: 12px; right: 14px; border: none; background: none; font-size: 23px; color: #94a3b8; cursor: pointer; }
.det-titulo { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 14px; }
.det-nro { font-family: monospace; font-size: 19px; font-weight: 900; color: #2563eb; }
.det-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
.det-campo { display: flex; flex-direction: column; gap: 2px; }
.det-campo label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
.det-campo span { font-size: 13px; color: #0f172a; }
.det-full { grid-column: 1/-1; }

@media (max-width: 980px) { .oa-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .oa-container { padding: 16px 12px; } .det-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="oa-container">
    <div class="oa-head">
        <h2><i class="bi bi-person-check me-2"></i>Ordenes Asignadas</h2>
        <p>Agrupadas por técnico y organizadas según su carga actual.</p>
    </div>

    @php
        $kpiTecnicos = count($porTecnico);
        $kpiEnCurso = 0;
        $kpiEntregadas = 0;
        $kpiPendientes = 0;
        $cargasTecnicos = [];
        foreach ($porTecnico as $p) {
            $tec = $p['tecnico'];
            $kpiEnCurso += (int) ($tec->activas ?? 0);
            $kpiEntregadas += (int) ($tec->entregadas ?? 0);
            $pend = (int) ($tec->pendientes ?? 0);
            $proceso = (int) ($tec->en_proceso ?? 0);
            $kpiPendientes += $pend;
            $cargasTecnicos[] = $pend + $proceso;
        }
        $maxCargaTecnico = count($cargasTecnicos) > 0 ? max($cargasTecnicos) : 0;
    @endphp

    <!-- BARRA DE FILTROS PREMIUM -->
    <div style="background: #fff; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.02);">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 12px; align-items: end;">
            <div class="campo" style="margin: 0; display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.35px;">Buscar</label>
                <div style="position: relative;">
                    <input type="text" id="oa-buscar" oninput="aplicarFiltros()" placeholder="Nro orden, cliente, equipo, marca, serie..." 
                           style="width: 100%; padding: 10px 14px 10px 38px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: border-color 0.15s ease-in-out;">
                    <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                </div>
            </div>
            <div class="campo" style="margin: 0; display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.35px;">Estado Orden</label>
                <select id="oa-filtro-estado" onchange="aplicarFiltros()" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; background-color: #fff; outline: none;">
                    <option value="">-- Todos --</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Finalizada">Finalizada</option>
                    <option value="Entregada">Entregada</option>
                </select>
            </div>
            <div class="campo" style="margin: 0; display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.35px;">Motivo Ingreso</label>
                <select id="oa-filtro-motivo" onchange="aplicarFiltros()" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; background-color: #fff; outline: none;">
                    <option value="">-- Todos --</option>
                    <option value="Servicio Cliente Externo">Servicio Cliente Externo</option>
                    <option value="Validacion de Garantia">Validacion de Garantia</option>
                    <option value="Servicios a Empresas">Servicios a Empresas</option>
                </select>
            </div>
            <div class="campo" style="margin: 0; display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.35px;">Repuestos</label>
                <select id="oa-filtro-repuesto" onchange="aplicarFiltros()" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; background-color: #fff; outline: none;">
                    <option value="">-- Todos --</option>
                    <option value="No requerido">No requerido</option>
                    <option value="Con stock">Con stock</option>
                    <option value="Sin stock">Sin stock</option>
                    <option value="Solicitado">Solicitado</option>
                    <option value="Por solicitar">Por solicitar</option>
                </select>
            </div>
            <button type="button" onclick="limpiarFiltros()" 
                    style="height: 38px; padding: 0 16px; background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center; gap: 6px;"
                    onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
                <i class="bi bi-x-circle"></i> Limpiar
            </button>
        </div>
    </div>

    <div class="oa-kpis">
        <div class="oa-kpi"><div class="oa-kpi-lbl">Técnicos con carga</div><div class="oa-kpi-val">{{ $kpiTecnicos }}</div></div>
        <div class="oa-kpi"><div class="oa-kpi-lbl">Órdenes en curso</div><div class="oa-kpi-val">{{ $kpiEnCurso }}</div></div>
        <div class="oa-kpi"><div class="oa-kpi-lbl">Entregadas</div><div class="oa-kpi-val">{{ $kpiEntregadas }}</div></div>
        <div class="oa-kpi"><div class="oa-kpi-lbl">Pendientes</div><div class="oa-kpi-val">{{ $kpiPendientes }}</div></div>
    </div>

    <div id="oa-busqueda-empty" class="oa-global-empty" style="display: none; padding: 60px 40px;">
        <i class="bi bi-search" style="font-size: 38px; color: #94a3b8; display: block; margin-bottom: 12px;"></i>
        <div style="font-weight: 700; color: #334155; font-size: 15px;">No se encontraron órdenes</div>
        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Intenta ajustar los criterios de búsqueda o limpiar los filtros.</div>
    </div>

    @if(count($porTecnico) === 0)
        <div class="oa-global-empty">No hay ordenes asignadas actualmente.</div>
    @else
        @foreach($porTecnico as $idx => $pack)
            @php
                $tec = $pack['tecnico'];
                $enCursoCount = (int) ($tec->activas ?? 0);
                $entregadasCount = (int) ($tec->entregadas ?? 0);
                $pendientes = (int) ($tec->pendientes ?? 0);
                $enProceso = (int) ($tec->en_proceso ?? 0);
                $totalCarga = $pendientes + $enProceso;
                $ratioCarga = $maxCargaTecnico > 0 ? ($totalCarga / $maxCargaTecnico) : 0;

                if ($totalCarga === 0 || $ratioCarga < 0.40) { $cargaColor = '#10b981'; $cargaLabel = 'Baja'; }
                elseif ($ratioCarga < 0.75) { $cargaColor = '#f59e0b'; $cargaLabel = 'Media'; }
                else { $cargaColor = '#ef4444'; $cargaLabel = 'Alta'; }
            @endphp

            <div class="oa-tecnico-bloque" id="bloque-tec-{{ $tec->id }}">
                <div class="oa-tec-header" onclick="toggleTecnico({{ $tec->id }})">
                    <div class="oa-tec-avatar" style="background: {{ $cargaColor }};">{{ strtoupper(substr((string) $tec->nombre_tecnico, 0, 1)) }}</div>
                    <span class="oa-tec-nombre">{{ $tec->nombre_tecnico }}</span>
                    <div class="oa-tec-badges">
                        <span class="oa-badge-asig">{{ $enCursoCount }} en curso</span>
                        <span class="oa-badge-entr">{{ $entregadasCount }} entregadas</span>
                        <span class="oa-badge-carga" style="background:{{ $cargaColor }}20;color:{{ $cargaColor }};border-color:{{ $cargaColor }}66;">
                            {{ $pendientes }}P · {{ $enProceso }}EP · {{ $cargaLabel }}
                        </span>
                    </div>
                    <span class="oa-chevron" id="chev-{{ $tec->id }}">&#9660;</span>
                </div>

                <div class="oa-tec-body" id="tec-{{ $tec->id }}" style="display: none;">
                    <div class="oa-tabs-header" style="display: flex; gap: 12px; border-bottom: 2.5px solid #e2e8f0; margin-bottom: 16px; padding-bottom: 2px;">
                        <button class="tab-btn-custom active" id="tab-btn-en_curso-{{ $tec->id }}" onclick="switchTab({{ $tec->id }}, 'en_curso')"
                                style="background: none; border: none; border-bottom: 2.5px solid #2563eb; color: #2563eb; padding: 8px 16px; font-size: 13.5px; font-weight: 700; cursor: pointer; transition: all 0.15s; margin-bottom: -4.5px;">
                            Órdenes en Curso (<span class="cnt-badge">{{ $enCursoCount }}</span>)
                        </button>
                        <button class="tab-btn-custom" id="tab-btn-entregadas-{{ $tec->id }}" onclick="switchTab({{ $tec->id }}, 'entregadas')"
                                style="background: none; border: none; border-bottom: 2.5px solid transparent; color: #64748b; padding: 8px 16px; font-size: 13.5px; font-weight: 700; cursor: pointer; transition: all 0.15s; margin-bottom: -4.5px;">
                            Órdenes Entregadas (<span class="cnt-badge">{{ $entregadasCount }}</span>)
                        </button>
                    </div>
                    
                    <div class="oa-cards-grid" id="grid-{{ $tec->id }}">
                        <!-- Cargado dinámicamente -->
                    </div>
                    
                    <div id="pager-{{ $tec->id }}">
                        <!-- Paginación dinámica -->
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
</section>

<div id="oa-modal" class="oa-modal-overlay" onclick="cerrarDetalle(event)">
    <div class="oa-modal-box">
        <button class="oa-modal-close" onclick="cerrarModal()">&times;</button>
        <div id="oa-modal-body"></div>
    </div>
</div>
@endsection

@push('js_adicional')
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
<script>
const PUEDE_EDITAR = @json($esAdminOAdminMaster);
const tecStates = {}; // Mantiene el estado { tab: 'en_curso', page: 1 } de cada técnico expanded

function esc(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

async function cargarOrdenes(tecnicoId, page = 1) {
    if (!tecStates[tecnicoId]) {
        tecStates[tecnicoId] = { tab: 'en_curso', page: 1 };
    }
    tecStates[tecnicoId].page = page;
    const state = tecStates[tecnicoId];
    
    const q = document.getElementById('oa-buscar').value.trim();
    const estado = document.getElementById('oa-filtro-estado').value;
    const motivo = document.getElementById('oa-filtro-motivo').value;
    const repuesto = document.getElementById('oa-filtro-repuesto').value;

    const grid = document.getElementById(`grid-${tecnicoId}`);
    const pager = document.getElementById(`pager-${tecnicoId}`);
    if (!grid) return;

    grid.innerHTML = `<div class="oa-loading" style="text-align: center; padding: 20px; color: #64748b; grid-column: 1/-1;"><i class="bi bi-hourglass-split spin"></i> Cargando órdenes...</div>`;
    if (pager) pager.innerHTML = '';

    try {
        const params = new URLSearchParams({
            tecnico_id: tecnicoId,
            type: state.tab,
            page: page,
            q: q,
            estado: estado,
            motivo: motivo,
            repuesto: repuesto
        });

        const res = await fetch(`{{ url('/operaciones/ordenes/asignadas/ajax') }}?${params.toString()}`);
        const data = await res.json();

        if (!data.ok) {
            grid.innerHTML = `<div class="oa-empty" style="grid-column: 1/-1;">Error al cargar: ${esc(data.error)}</div>`;
            return;
        }

        const items = data.data || [];
        if (items.length === 0) {
            grid.innerHTML = `<div class="oa-empty" style="grid-column: 1/-1;">Sin órdenes en esta sección.</div>`;
            return;
        }

        let html = '';
        items.forEach(o => {
            const estado = (o.estado_orden || '').trim();
            let estadoClass = 'def';
            if (['pendiente', 'ingreso'].includes(estado.toLowerCase())) estadoClass = 'pend';
            else if (['en proceso', 'revision', 'en_proceso'].includes(estado.toLowerCase())) estadoClass = 'proc';
            else if (['finalizada', 'reparado'].includes(estado.toLowerCase())) estadoClass = 'fin';
            else if (['entregada', 'entregado'].includes(estado.toLowerCase())) estadoClass = 'ent';

            const equipo = [o.tipo, o.marca, o.modelo].filter(Boolean).join(' ').trim();
            const urlImprimir = o.tipo_orden === 'empresa' 
                ? `{{ url('/operaciones/ordenes-empresa') }}/${o.orden_id}/imprimir` 
                : `{{ url('/operaciones/ordenes') }}/${o.orden_id}/imprimir`;
            const urlEditar = o.tipo_orden === 'empresa'
                ? `{{ url('/operaciones/ordenes-empresa/editar') }}/${o.orden_id}`
                : `{{ url('/operaciones/ordenes/editar') }}/${o.orden_id}`;

            html += `
                <div class="oa-card" data-orden='${JSON.stringify(o).replace(/'/g, "&#39;")}' ondblclick="abrirDetalleDesdeCard(this)">
                    <div class="oa-card-top">
                        <span class="oa-nro">${esc(o.nro_orden)}</span>
                        <span class="oa-status ${estadoClass}">${esc(o.estado_orden)}</span>
                    </div>
                    <div class="oa-cliente">${esc(o.cliente)}</div>
                    <div class="oa-equipo">${esc(equipo)} · S/N ${esc(o.serie)}</div>
                    <div class="oa-meta-row">
                        <span class="oa-meta"><i class="bi bi-calendar3 me-1"></i>${esc(o.fecha_ingreso_fmt)}</span>
                        <span class="oa-meta">${esc(o.estado_repuesto || 'No requerido')}</span>
                        ${o.motivo_ingreso ? `<span class="oa-meta">${esc(o.motivo_ingreso)}</span>` : ''}
                        ${o.fecha_prometido_fmt ? `
                            <span class="oa-meta" style="color: #b45309; background: #fffbeb; border-color: #fde68a;" title="Fecha Prometida">
                                <i class="bi bi-calendar-check me-1"></i>Prometido: ${esc(o.fecha_prometido_fmt)}
                            </span>
                        ` : ''}
                    </div>
                    <div class="oa-actions">
                        <a class="btn-det ot" target="_blank" href="${urlImprimir}"><i class="bi bi-printer"></i> OT</a>
                        ${PUEDE_EDITAR ? `<a class="btn-det ver" href="${urlEditar}"><i class="bi bi-eye"></i> Ver detalle</a>` : ''}
                    </div>
                </div>
            `;
        });
        grid.innerHTML = html;

        renderPager(pager, data.current_page, data.last_page, data.total, data.per_page, (newPage) => {
            cargarOrdenes(tecnicoId, newPage);
        });

    } catch (e) {
        console.error(e);
        grid.innerHTML = `<div class="oa-empty" style="grid-column: 1/-1;">Error de conexión.</div>`;
    }
}

function renderPager(container, currentPage, lastPage, total, perPage, onPageClick) {
    if (!container) return;
    if (lastPage <= 1) {
        container.innerHTML = '';
        return;
    }

    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(start + perPage - 1, total);

    let buttonsHtml = '';
    
    buttonsHtml += `
        <button class="sgn-pager-btn" ${currentPage === 1 ? 'disabled' : ''} data-page="prev">
            <i class="bi bi-chevron-left"></i>
        </button>
    `;

    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(lastPage, startPage + maxButtons - 1);

    if (endPage - startPage + 1 < maxButtons) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
        buttonsHtml += `<button class="sgn-pager-btn" data-page="1">1</button>`;
        if (startPage > 2) {
            buttonsHtml += `<span style="color:#94a3b8;padding:0 4px;font-size:12px;">...</span>`;
        }
    }

    for (let p = startPage; p <= endPage; p++) {
        buttonsHtml += `
            <button class="sgn-pager-btn ${p === currentPage ? 'activo' : ''}" data-page="${p}">
                ${p}
            </button>
        `;
    }

    if (endPage < lastPage) {
        if (endPage < lastPage - 1) {
            buttonsHtml += `<span style="color:#94a3b8;padding:0 4px;font-size:12px;">...</span>`;
        }
        buttonsHtml += `<button class="sgn-pager-btn" data-page="${lastPage}">${lastPage}</button>`;
    }

    buttonsHtml += `
        <button class="sgn-pager-btn" ${currentPage === lastPage ? 'disabled' : ''} data-page="next">
            <i class="bi bi-chevron-right"></i>
        </button>
    `;

    container.innerHTML = `
        <div class="sgn-pager-wrap" style="margin-top: 10px;">
            <div class="sgn-pager-info">Mostrando <span class="sgn-p-start">${start}</span> a <span class="sgn-p-end">${end}</span> de <span class="sgn-p-total">${total}</span> registros</div>
            <div class="sgn-pager-buttons">${buttonsHtml}</div>
        </div>
    `;

    const btns = container.querySelectorAll('.sgn-pager-btn');
    btns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const pageAttr = btn.getAttribute('data-page');
            let targetPage = currentPage;
            if (pageAttr === 'prev') targetPage = currentPage - 1;
            else if (pageAttr === 'next') targetPage = currentPage + 1;
            else targetPage = parseInt(pageAttr);

            if (targetPage >= 1 && targetPage <= lastPage) {
                onPageClick(targetPage);
            }
        });
    });
}

let oaFilterTimer = null;
function aplicarFiltros() {
    clearTimeout(oaFilterTimer);
    oaFilterTimer = setTimeout(() => {
        Object.keys(tecStates).forEach(tecnicoId => {
            if (tecStates[tecnicoId]) {
                tecStates[tecnicoId].page = 1;
            }
            const body = document.getElementById(`tec-${tecnicoId}`);
            if (body && body.style.display !== 'none') {
                cargarOrdenes(tecnicoId, 1);
            }
        });
    }, 300);
}

function limpiarFiltros() {
    document.getElementById('oa-buscar').value = '';
    document.getElementById('oa-filtro-estado').value = '';
    document.getElementById('oa-filtro-motivo').value = '';
    document.getElementById('oa-filtro-repuesto').value = '';
    aplicarFiltros();
}

function toggleTecnico(tecnicoId) {
    const body = document.getElementById(`tec-${tecnicoId}`);
    const chev = document.getElementById(`chev-${tecnicoId}`);
    if (!body) return;
    
    if (body.style.display === 'none') {
        body.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        const page = tecStates[tecnicoId] ? tecStates[tecnicoId].page : 1;
        cargarOrdenes(tecnicoId, page);
    } else {
        body.style.display = 'none';
        if (chev) chev.classList.remove('open');
    }
}

function switchTab(tecnicoId, tab) {
    if (!tecStates[tecnicoId]) {
        tecStates[tecnicoId] = { tab: 'en_curso', page: 1 };
    }
    
    if (tecStates[tecnicoId].tab === tab) return;
    
    tecStates[tecnicoId].tab = tab;
    tecStates[tecnicoId].page = 1;

    const btnCurso = document.getElementById(`tab-btn-en_curso-${tecnicoId}`);
    const btnEntregadas = document.getElementById(`tab-btn-entregadas-${tecnicoId}`);
    
    if (tab === 'en_curso') {
        if (btnCurso) {
            btnCurso.style.borderBottomColor = '#2563eb';
            btnCurso.style.color = '#2563eb';
        }
        if (btnEntregadas) {
            btnEntregadas.style.borderBottomColor = 'transparent';
            btnEntregadas.style.color = '#64748b';
        }
    } else {
        if (btnCurso) {
            btnCurso.style.borderBottomColor = 'transparent';
            btnCurso.style.color = '#64748b';
        }
        if (btnEntregadas) {
            btnEntregadas.style.borderBottomColor = '#2563eb';
            btnEntregadas.style.color = '#2563eb';
        }
    }
    
    cargarOrdenes(tecnicoId, 1);
}

function abrirDetalleDesdeCard(card) {
    if (!card) return;
    const raw = card.getAttribute('data-orden');
    if (!raw) return;

    let o = null;
    try { o = JSON.parse(raw); } catch { return; }

    const motivo = o.motivo_ingreso || '-';
    const garantia = o.estado_garantia || '-';
    const html = `
        <div class="det-titulo">
            <span class="det-nro">${esc(o.nro_orden)}</span>
            <span class="oa-status def">${esc(o.estado_orden || '-')}</span>
        </div>
        <div class="det-grid">
            <div class="det-campo"><label>Cliente</label><span>${esc(o.cliente || '-')}</span></div>
            <div class="det-campo"><label>Fecha de Ingreso</label><span>${esc(o.fecha_ingreso_fmt || '-')}</span></div>
            <div class="det-campo"><label>Equipo</label><span>${esc((o.tipo || '') + ' ' + (o.marca || '') + ' ' + (o.modelo || ''))}</span></div>
            <div class="det-campo"><label>Serie</label><span>${esc(o.serie || '-')}</span></div>
            <div class="det-campo"><label>Código Producto</label><span>${esc(o.producto_inventario_codigo || '-')}</span></div>
            <div class="det-campo"><label>Motivo de Ingreso</label><span>${esc(motivo)}</span></div>
            <div class="det-campo"><label>Estado Garantía</label><span>${esc(garantia)}</span></div>
            <div class="det-campo"><label>Estado Repuesto</label><span>${esc(o.estado_repuesto || '-')}</span></div>
            <div class="det-campo"><label>Fecha Prometido</label><span>${esc(o.fecha_prometido_fmt || '-')}</span></div>
        </div>
    `;

    const body = document.getElementById('oa-modal-body');
    const modal = document.getElementById('oa-modal');
    if (body) body.innerHTML = html;
    if (modal) modal.classList.add('open');
}

function cerrarModal() {
    const modal = document.getElementById('oa-modal');
    if (modal) modal.classList.remove('open');
}

function cerrarDetalle(e) {
    if (e.target && e.target.id === 'oa-modal') cerrarModal();
}

document.addEventListener('DOMContentLoaded', () => {
    const firstBlock = document.querySelector('.oa-tecnico-bloque');
    if (firstBlock) {
        const header = firstBlock.querySelector('.oa-tec-header');
        if (header) {
            header.click();
        }
    }
});
</script>
@endpush
