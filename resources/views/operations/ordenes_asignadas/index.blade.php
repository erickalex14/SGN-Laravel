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
            $kpiEnCurso += count($p['en_curso']);
            $kpiEntregadas += count($p['entregadas']);
            $pend = (int) ($p['tecnico']->pendientes ?? 0);
            $proceso = (int) ($p['tecnico']->en_proceso ?? 0);
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
                $enCurso = $pack['en_curso'];
                $entregadas = $pack['entregadas'];
                $pendientes = (int) ($tec->pendientes ?? 0);
                $enProceso = (int) ($tec->en_proceso ?? 0);
                $totalCarga = $pendientes + $enProceso;
                $ratioCarga = $maxCargaTecnico > 0 ? ($totalCarga / $maxCargaTecnico) : 0;

                if ($totalCarga === 0 || $ratioCarga < 0.40) { $cargaColor = '#10b981'; $cargaLabel = 'Baja'; }
                elseif ($ratioCarga < 0.75) { $cargaColor = '#f59e0b'; $cargaLabel = 'Media'; }
                else { $cargaColor = '#ef4444'; $cargaLabel = 'Alta'; }
            @endphp

            <div class="oa-tecnico-bloque">
                <div class="oa-tec-header" onclick="toggleTecnico('tec-{{ $idx }}', 'chev-{{ $idx }}')">
                    <div class="oa-tec-avatar" style="background: {{ $cargaColor }};">{{ strtoupper(substr((string) $tec->nombre_tecnico, 0, 1)) }}</div>
                    <span class="oa-tec-nombre">{{ $tec->nombre_tecnico }}</span>
                    <div class="oa-tec-badges">
                        <span class="oa-badge-asig">{{ count($enCurso) }} en curso</span>
                        <span class="oa-badge-entr">{{ count($entregadas) }} entregadas</span>
                        <span class="oa-badge-carga" style="background:{{ $cargaColor }}20;color:{{ $cargaColor }};border-color:{{ $cargaColor }}66;">
                            {{ $pendientes }}P · {{ $enProceso }}EP · {{ $cargaLabel }}
                        </span>
                    </div>
                    <span class="oa-chevron" id="chev-{{ $idx }}">&#9660;</span>
                </div>

                <div class="oa-tec-body" id="tec-{{ $idx }}">
                    <div class="oa-sub-title"><i class="bi bi-wrench"></i> Órdenes Asignadas <span class="oa-sub-pill">{{ count($enCurso) }}</span></div>
                    <div class="oa-cards-grid">
                        @forelse($enCurso as $o)
                            @php
                                $estado = trim((string) $o->estado_orden);
                                $estadoClass = match (true) {
                                    in_array($estado, ['Pendiente', 'INGRESO'], true) => 'pend',
                                    in_array($estado, ['En proceso', 'REVISION', 'EN PROCESO'], true) => 'proc',
                                    in_array($estado, ['Finalizada', 'REPARADO'], true) => 'fin',
                                    in_array($estado, ['Entregada', 'ENTREGADO'], true) => 'ent',
                                    default => 'def',
                                };
                            @endphp
                            <div class="oa-card" data-orden='@json($o)'>
                                <div class="oa-card-top">
                                    <span class="oa-nro">{{ $o->nro_orden }}</span>
                                    <span class="oa-status {{ $estadoClass }}">{{ $o->estado_orden }}</span>
                                </div>
                                <div class="oa-cliente">{{ $o->cliente }}</div>
                                <div class="oa-equipo">{{ trim(($o->tipo ?? '').' '.($o->marca ?? '').' '.($o->modelo ?? '')) }} · S/N {{ $o->serie }}</div>
                                <div class="oa-meta-row">
                                    <span class="oa-meta"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($o->fecha_de_ingreso)->format('d/m/Y H:i') }}</span>
                                    <span class="oa-meta">{{ $o->estado_repuesto ?: 'No requerido' }}</span>
                                    @if(!empty($o->motivo_ingreso))<span class="oa-meta">{{ $o->motivo_ingreso }}</span>@endif
                                    @if(!empty($o->fecha_prometido))
                                        <span class="oa-meta" style="color: #b45309; background: #fffbeb; border-color: #fde68a;" title="Fecha Prometida"><i class="bi bi-calendar-check me-1"></i>Prometido: {{ \Carbon\Carbon::parse($o->fecha_prometido)->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                                <div class="oa-actions">
                                    <a class="btn-det ot" target="_blank" href="{{ ($o->tipo_orden ?? 'personal') === 'empresa' ? route('ordenes_empresa.imprimir', ['id' => $o->orden_id]) : route('ordenes.imprimir', ['id' => $o->orden_id]) }}"><i class="bi bi-printer"></i> OT</a>
                                    @if(($o->tipo_orden ?? 'personal') === 'personal')
                                        <a class="btn-det ver" href="{{ url('/operaciones/ordenes/editar/'.$o->orden_id) }}"><i class="bi bi-eye"></i> Ver detalle</a>
                                    @else
                                        <button type="button" class="btn-det ver" onclick="abrirDetalleDesdeCard(this.closest('[data-orden]'))"><i class="bi bi-eye"></i> Ver detalle</button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="oa-empty">Sin ordenes en esta sección.</div>
                        @endforelse
                    </div>

                    <div class="oa-sub-title"><i class="bi bi-check-circle"></i> Órdenes Entregadas <span class="oa-sub-pill">{{ count($entregadas) }}</span></div>
                    <div class="oa-cards-grid">
                        @forelse($entregadas as $o)
                            <div class="oa-card" data-orden='@json($o)'>
                                <div class="oa-card-top">
                                    <span class="oa-nro">{{ $o->nro_orden }}</span>
                                    <span class="oa-status ent">{{ $o->estado_orden }}</span>
                                </div>
                                <div class="oa-cliente">{{ $o->cliente }}</div>
                                <div class="oa-equipo">{{ trim(($o->tipo ?? '').' '.($o->marca ?? '').' '.($o->modelo ?? '')) }} · S/N {{ $o->serie }}</div>
                                <div class="oa-meta-row">
                                    <span class="oa-meta"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($o->fecha_de_ingreso)->format('d/m/Y H:i') }}</span>
                                    <span class="oa-meta">{{ $o->estado_repuesto ?: 'No requerido' }}</span>
                                    @if(!empty($o->fecha_prometido))
                                        <span class="oa-meta" style="color: #b45309; background: #fffbeb; border-color: #fde68a;" title="Fecha Prometida"><i class="bi bi-calendar-check me-1"></i>Prometido: {{ \Carbon\Carbon::parse($o->fecha_prometido)->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                                <div class="oa-actions">
                                    <a class="btn-det ot" target="_blank" href="{{ ($o->tipo_orden ?? 'personal') === 'empresa' ? route('ordenes_empresa.imprimir', ['id' => $o->orden_id]) : route('ordenes.imprimir', ['id' => $o->orden_id]) }}"><i class="bi bi-printer"></i> OT</a>
                                    @if(($o->tipo_orden ?? 'personal') === 'personal')
                                        <a class="btn-det ver" href="{{ url('/operaciones/ordenes/editar/'.$o->orden_id) }}"><i class="bi bi-eye"></i> Ver detalle</a>
                                    @else
                                        <button type="button" class="btn-det ver" onclick="abrirDetalleDesdeCard(this.closest('[data-orden]'))"><i class="bi bi-eye"></i> Ver detalle</button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="oa-empty">Sin ordenes en esta sección.</div>
                        @endforelse
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
<script>
function esc(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function aplicarFiltros() {
    const q = document.getElementById('oa-buscar').value.toLowerCase().trim();
    const estado = document.getElementById('oa-filtro-estado').value.toLowerCase().trim();
    const motivo = document.getElementById('oa-filtro-motivo').value.toLowerCase().trim();
    const repuesto = document.getElementById('oa-filtro-repuesto').value.toLowerCase().trim();

    const bloques = document.querySelectorAll('.oa-tecnico-bloque');
    let totalVisibles = 0;

    bloques.forEach((bloque) => {
        let visibleEnCurso = 0;
        let visibleEntregadas = 0;

        const cards = bloque.querySelectorAll('.oa-card');
        cards.forEach((card) => {
            const raw = card.getAttribute('data-orden') || '{}';
            let o = {};
            try { o = JSON.parse(raw); } catch (_) {}

            const matchQ = !q || 
                (o.nro_orden || '').toLowerCase().includes(q) || 
                (o.cliente || '').toLowerCase().includes(q) || 
                (o.marca || '').toLowerCase().includes(q) || 
                (o.modelo || '').toLowerCase().includes(q) || 
                (o.serie || '').toLowerCase().includes(q) ||
                (o.tipo || '').toLowerCase().includes(q);
                
            const matchEstado = !estado || (o.estado_orden || '').toLowerCase() === estado;
            const matchMotivo = !motivo || (o.motivo_ingreso || '').toLowerCase() === motivo;
            const matchRepuesto = !repuesto || (o.estado_repuesto || 'no requerido').toLowerCase() === repuesto;

            const match = matchQ && matchEstado && matchMotivo && matchRepuesto;
            card.style.display = match ? '' : 'none';
            if (match) {
                if ((o.estado_orden || '').toLowerCase() === 'entregada' || (o.estado_orden || '').toLowerCase() === 'entregado') {
                    visibleEntregadas++;
                } else {
                    visibleEnCurso++;
                }
            }
        });

        // Toggle local empty states inside this technician block
        const enCursoGrid = bloque.querySelector('.oa-cards-grid:first-of-type');
        const entregadasGrid = bloque.querySelector('.oa-cards-grid:last-of-type');

        if (enCursoGrid) {
            let emptyEl = enCursoGrid.querySelector('.oa-empty-filter');
            if (visibleEnCurso === 0 && enCursoGrid.querySelectorAll('.oa-card[style=""]').length === 0) {
                if (!emptyEl) {
                    emptyEl = document.createElement('div');
                    emptyEl.className = 'oa-empty oa-empty-filter';
                    emptyEl.textContent = 'Sin coincidencias en esta sección.';
                    enCursoGrid.appendChild(emptyEl);
                }
                const defaultEmpty = enCursoGrid.querySelector('.oa-empty:not(.oa-empty-filter)');
                if (defaultEmpty) defaultEmpty.style.display = 'none';
            } else {
                if (emptyEl) emptyEl.remove();
                const defaultEmpty = enCursoGrid.querySelector('.oa-empty:not(.oa-empty-filter)');
                if (defaultEmpty) defaultEmpty.style.display = '';
            }
        }

        if (entregadasGrid) {
            let emptyEl = entregadasGrid.querySelector('.oa-empty-filter');
            if (visibleEntregadas === 0 && entregadasGrid.querySelectorAll('.oa-card[style=""]').length === 0) {
                if (!emptyEl) {
                    emptyEl = document.createElement('div');
                    emptyEl.className = 'oa-empty oa-empty-filter';
                    emptyEl.textContent = 'Sin coincidencias en esta sección.';
                    entregadasGrid.appendChild(emptyEl);
                }
                const defaultEmpty = entregadasGrid.querySelector('.oa-empty:not(.oa-empty-filter)');
                if (defaultEmpty) defaultEmpty.style.display = 'none';
            } else {
                if (emptyEl) emptyEl.remove();
                const defaultEmpty = entregadasGrid.querySelector('.oa-empty:not(.oa-empty-filter)');
                if (defaultEmpty) defaultEmpty.style.display = '';
            }
        }

        // Update badges dynamically in the DOM
        const badgeCurso = bloque.querySelector('.oa-badge-asig');
        const badgeEntregadas = bloque.querySelector('.oa-badge-entr');
        if (badgeCurso) badgeCurso.textContent = `${visibleEnCurso} en curso`;
        if (badgeEntregadas) badgeEntregadas.textContent = `${visibleEntregadas} entregadas`;

        const totalBloque = visibleEnCurso + visibleEntregadas;
        bloque.style.display = totalBloque > 0 ? '' : 'none';
        if (totalBloque > 0) totalVisibles++;
    });

    const emptyGlobal = document.getElementById('oa-busqueda-empty');
    if (emptyGlobal) {
        emptyGlobal.style.display = totalVisibles === 0 ? 'block' : 'none';
    }
}

function limpiarFiltros() {
    document.getElementById('oa-buscar').value = '';
    document.getElementById('oa-filtro-estado').value = '';
    document.getElementById('oa-filtro-motivo').value = '';
    document.getElementById('oa-filtro-repuesto').value = '';
    aplicarFiltros();
}

function toggleTecnico(id, chevId) {
    const body = document.getElementById(id);
    const chev = document.getElementById(chevId);
    if (!body) return;
    body.classList.toggle('open');
    if (chev) chev.classList.toggle('open', body.classList.contains('open'));
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
            <div class="det-campo"><label>Fecha de Ingreso</label><span>${esc(o.fecha_de_ingreso || '-')}</span></div>
            <div class="det-campo"><label>Equipo</label><span>${esc((o.tipo || '') + ' ' + (o.marca || '') + ' ' + (o.modelo || ''))}</span></div>
            <div class="det-campo"><label>Serie</label><span>${esc(o.serie || '-')}</span></div>
            <div class="det-campo"><label>Motivo de Ingreso</label><span>${esc(motivo)}</span></div>
            <div class="det-campo"><label>Estado Garantía</label><span>${esc(garantia)}</span></div>
            <div class="det-campo"><label>Estado Repuesto</label><span>${esc(o.estado_repuesto || '-')}</span></div>
            <div class="det-campo"><label>Fecha Prometido</label><span>${esc(o.fecha_prometido || '-')}</span></div>
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
    document.querySelectorAll('.oa-card').forEach((card) => {
        card.addEventListener('dblclick', () => abrirDetalleDesdeCard(card));
    });

    const first = document.querySelector('.oa-tec-body');
    const firstChev = document.querySelector('.oa-chevron');
    if (first) first.classList.add('open');
    if (firstChev) firstChev.classList.add('open');
});
</script>
@endpush
