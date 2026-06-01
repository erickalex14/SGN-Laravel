@extends('layouts.app')
@section('titulo', 'Gestión de Notas de Crédito')

@push('css_adicional')
<style>
/* CSS heredado del proyecto original */
.nc-wrap { max-width: 1200px; margin: 0 auto; padding: 28px 24px; }
.nc-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
.nc-hdr h2 { margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.nc-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
.nc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.nc-table th { background: #f8fafc; padding: 14px 16px; text-align: left; font-weight: 700; color: #475569; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
.nc-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
.nc-table tr:hover td { background: #f8fafc; }
.badge { font-family: monospace; font-size: 13px; font-weight: 700; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; color: #0f172a; border: 1px solid #cbd5e1; }
.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.st-pend { background: #fef9c3; color: #854d0e; }
.st-aprob { background: #dcfce7; color: #166534; }
.st-rech { background: #fee2e2; color: #991b1b; }
.btn-gestion { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-gestion:hover { background: #2563eb; color: #fff; }
.btn-print { background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:700; text-decoration:none; display:inline-block; }
.btn-print:hover { background:#0f172a; color:#fff; border-color:#0f172a; }
.btn-exportar { background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; transition: opacity .2s; }
.btn-exportar:hover { opacity: .9; }
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.6); z-index: 9999; display: none; align-items: center; justify-content: center; }
.modal-overlay.activo { display: flex; }
.modal-box { background: #fff; width: 100%; max-width: 500px; border-radius: 12px; display: flex; flex-direction: column; }
.modal-hdr { padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
.modal-hdr h3 { margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; }
.btn-cerrar { background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8; }
.modal-body { padding: 20px; }
.info-row { margin-bottom: 12px; font-size: 13.5px; }
.info-row strong { color: #475569; display: inline-block; width: 80px; }
.modal-ftr { padding: 16px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px; }
.btn-rechazar { background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-aprobar { background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
#rechazo-box { display: none; margin-top: 16px; }
textarea.rechazo-input { width: 100%; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; resize: vertical; min-height: 80px; font-family: inherit; font-size: 13px; }

/* Estilos de Analítica y Print */
.kpi-card:hover { transform: translateY(-3px); }
@media print {
    body { background: #fff !important; color: #000 !important; }
    .no-print, .nc-card:has(#filtro-q), header, footer, aside, .btn-print, .btn-gestion, .btn-exportar, nav, .main-header, .sidebar { display: none !important; }
    .nc-wrap { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .nc-card { border: none !important; box-shadow: none !important; }
    .nc-table th { background: #e2e8f0 !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
    .nc-table td { border-bottom: 1px solid #ddd !important; }
    .badge { border: none !important; background: transparent !important; padding: 0 !important; font-weight: bold !important; }
    @page { size: A4 portrait; margin: 15mm; }
}
</style>
@endpush

@section('contenido')
@php
    $totalNc = $solicitudes->count();
    $aprobadasNc = $solicitudes->filter(fn($s) => strtoupper($s->estado) === 'APROBADA')->count();
    $pendientesNc = $solicitudes->filter(fn($s) => strtoupper($s->estado) === 'PENDIENTE')->count();

    // Técnico con más solicitudes
    $tecLider = '-';
    $maxSolicitudes = 0;
    if ($solicitudes->isNotEmpty()) {
        $grupoTecnicos = $solicitudes->groupBy(function($s) {
            return $s->tecnico->nombre_tecnico ?? $s->tecnico_nombre;
        });
        foreach ($grupoTecnicos as $tec => $items) {
            if ($items->count() > $maxSolicitudes) {
                $maxSolicitudes = $items->count();
                $tecLider = $tec;
            }
        }
    }
@endphp

<div class="nc-wrap">
    <div class="nc-hdr no-print">
        <h2><i class="bi bi-file-earmark-check" style="color:#2563eb;"></i> Autorización de Notas de Crédito</h2>
    </div>

    <!-- Dashboard de KPIs de Analítica de NC -->
    <div class="nc-kpis" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:24px;">
        <div class="kpi-card" style="background:linear-gradient(135deg, #eff6ff, #dbeafe); border:1.5px solid #bfdbfe; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(37,99,235,.03); transition:transform .2s;">
            <div style="background:#2563eb; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-journal-text"></i></div>
            <div>
                <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Total Solicitudes</span>
                <span style="font-size:22px; font-weight:800; color:#0f172a; display:block;" id="kpi-total-nc">{{ $totalNc }}</span>
                <span style="font-size:11px; color:#64748b;">Trámites registrados</span>
            </div>
        </div>

        <div class="kpi-card" style="background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1.5px solid #bbf7d0; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(16,185,129,.03); transition:transform .2s;">
            <div style="background:#10b981; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-check2-circle"></i></div>
            <div>
                <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Autorizadas (Aprobadas)</span>
                <span style="font-size:22px; font-weight:800; color:#0f172a; display:block;">{{ $aprobadasNc }}</span>
                <span style="font-size:11px; color:#64748b;">NC aplicadas con éxito</span>
            </div>
        </div>

        <div class="kpi-card" style="background:linear-gradient(135deg, #fffbeb, #fef3c7); border:1.5px solid #fde047; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(217,119,6,.03); transition:transform .2s;">
            <div style="background:#d97706; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Pendientes</span>
                <span style="font-size:22px; font-weight:800; color:#0f172a; display:block;">{{ $pendientesNc }}</span>
                <span style="font-size:11px; color:#64748b;">En espera de revisión</span>
            </div>
        </div>

        <div class="kpi-card" style="background:linear-gradient(135deg, #faf5ff, #f3e8ff); border:1.5px solid #e9d5ff; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(147,51,234,.03); transition:transform .2s;">
            <div style="background:#9333ea; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-person-badge-fill"></i></div>
            <div>
                <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Mayor Demandante</span>
                <span style="font-size:15px; font-weight:800; color:#0f172a; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px;" title="{{ $tecLider }}">{{ $tecLider }}</span>
                <span style="font-size:11px; color:#64748b;">Con {{ $maxSolicitudes }} solicitudes</span>
            </div>
        </div>
    </div>

    {{-- Panel de Filtros Premium --}}
    <div class="nc-card no-print" style="margin-bottom: 18px; padding: 20px; background:#f8fafc;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="font-size: 11.5px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .05em; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-funnel"></i> Filtros de Auditoría y Búsqueda
            </div>
            <div style="display:inline-flex; gap:8px;">
                <button class="btn-exportar" style="background:#4f46e5;" onclick="exportarAuditoriaNC('csv')"><i class="bi bi-file-earmark-text-fill"></i> CSV</button>
                <button class="btn-exportar" style="background:#10b981;" onclick="exportarAuditoriaNC('excel')"><i class="bi bi-file-earmark-excel-fill"></i> Excel</button>
                <button class="btn-exportar" style="background:#2563eb;" onclick="window.print()"><i class="bi bi-printer-fill"></i> Imprimir PDF</button>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase;">Buscador General</label>
                <input type="text" id="filtro-q" class="filtro-inp" placeholder="Nro, asunto, orden..." style="border:1.5px solid #cbd5e1; border-radius:8px; padding:8px 12px; font-size:13px;" oninput="aplicarFiltrosLocal()">
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase;">Estado</label>
                <select id="filtro-estado" style="border:1.5px solid #cbd5e1; border-radius:8px; padding:8px 12px; font-size:13px; background:#fff; cursor:pointer;" onchange="aplicarFiltrosLocal()">
                    <option value="">-- Todos --</option>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="APROBADA">Aprobada</option>
                    <option value="RECHAZADA">Rechazada</option>
                </select>
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase;">Técnico</label>
                <input type="text" id="filtro-tecnico" placeholder="Nombre del técnico..." style="border:1.5px solid #cbd5e1; border-radius:8px; padding:8px 12px; font-size:13px;" oninput="aplicarFiltrosLocal()">
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase;">Fecha de Solicitud</label>
                <input type="date" id="filtro-fecha" style="border:1.5px solid #cbd5e1; border-radius:8px; padding:8px 12px; font-size:13px;" onchange="aplicarFiltrosLocal()">
            </div>
        </div>
    </div>

    <div class="nc-card">
        <table class="nc-table" id="tabla-nc-admin">
            <thead>
                <tr>
                    <th>Solicitud / Nro</th>
                    <th>Fecha</th>
                    <th>Técnico Solicitante</th>
                    <th>Orden Afectada</th>
                    <th>Informe Técnico</th>
                    <th>Estado</th>
                    <th style="text-align:right;" class="no-print">Acción</th>
                </tr>
            </thead>
            <tbody id="nca-tbody">
                @forelse($solicitudes as $nc)
                    @php
                        $estadoNC = strtoupper((string) $nc->estado);
                        $clase = match($estadoNC) { 'PENDIENTE' => 'st-pend', 'APROBADA' => 'st-aprob', 'RECHAZADA' => 'st-rech', default => '' };
                        $tecnicoNombreReal = $nc->tecnico->nombre_tecnico ?? $nc->tecnico_nombre;
                    @endphp
                    <tr class="nc-row" data-solicitud="{{ json_encode([
                        'id' => $nc->id,
                        'nro_solicitud' => $nc->nro_solicitud,
                        'asunto' => $nc->asunto,
                        'tecnico' => $tecnicoNombreReal,
                        'orden' => $nc->orden->nro_orden ?? '',
                        'estado' => $nc->estado,
                        'fecha' => \Carbon\Carbon::parse($nc->creado_en)->format('Y-m-d'),
                        'motivo_rechazo' => $nc->motivo_rechazo
                    ]) }}">
                        <td>
                            <span class="badge audit-nro-sol">{{ $nc->nro_solicitud }}</span><br>
                            <span style="font-size:11px;color:#64748b;margin-top:4px;display:block;" class="audit-asunto">{{ $nc->asunto }}</span>
                        </td>
                        <td class="audit-fecha">{{ \Carbon\Carbon::parse($nc->creado_en)->format('d/m/Y') }}</td>
                        <td><strong class="audit-tecnico">{{ $tecnicoNombreReal }}</strong></td>
                        <td>
                            @if($nc->orden)
                                <a href="{{ route('ordenes.editar', ['id' => $nc->orden_id]) }}" target="_blank" style="color:#2563eb;text-decoration:none;font-weight:600;" class="audit-orden">
                                    <i class="bi bi-eye me-1"></i>{{ $nc->orden->nro_orden }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($nc->orden && $nc->orden->informes && $nc->orden->informes->isNotEmpty())
                                @php $inf = $nc->orden->informes->first(); @endphp
                                <a href="{{ route('informes.imprimir', ['id' => $inf->id]) }}" target="_blank" class="btn-print" style="background:#f5f3ff; color:#7c3aed; border-color:#ddd6fe; display:inline-flex; align-items:center; gap:4px; padding:4px 8px; font-weight:700;">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Ver Informe
                                </a>
                            @else
                                <span style="color:#94a3b8; font-style:italic;">No registrado</span>
                            @endif
                        </td>
                        <td><span class="status-badge {{ $clase }} audit-estado">{{ $nc->estado }}</span></td>
                        <td style="text-align:right;" class="no-print">
                            <div style="display:inline-flex; gap:6px; justify-content:flex-end;">
                                <button class="btn-print" style="background:#f8fafc; color:#475569; border-color:#cbd5e1; font-weight:700;" onclick="abrirGestion({{ json_encode($nc) }}, true)">Detalles</button>
                                <a href="{{ route('ordenes.imprimir', ['id' => $nc->orden_id]) }}" target="_blank" class="btn-print">Imprimir</a>
                                @if($estadoNC === 'PENDIENTE')
                                    <button class="btn-gestion" onclick="abrirGestion({{ json_encode($nc) }}, false)">Gestionar</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="nc-empty-row"><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">No hay solicitudes registradas.</td></tr>
                @endforelse
                <tr id="nc-empty-row-filtered" style="display:none;"><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">No se encontraron solicitudes con los filtros aplicados.</td></tr>
            </tbody>
        </table>
        <div id="nca-pager" style="margin: 0 16px 16px;" class="no-print"></div>
    </div>
</div>

<div class="modal-overlay" id="modal-gestion">
    <div class="modal-box">
        <div class="modal-hdr">
            <h3 id="modal-titulo-nc">Gestión de Solicitud NC</h3>
            <button class="btn-cerrar" onclick="cerrarModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="nc-id">
            <div class="info-row"><strong>Nro:</strong> <span id="nc-nro"></span></div>
            <div class="info-row"><strong>Técnico:</strong> <span id="nc-tec"></span></div>
            <div class="info-row"><strong>Asunto:</strong> <span id="nc-asunto"></span></div>
            <div style="margin-top:16px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#334155;">
                <strong style="display:block; margin-bottom:6px; color:#0f172a;">Detalle de la Solicitud:</strong>
                <span id="nc-detalle" style="white-space: pre-wrap;"></span>
            </div>

            <div id="rechazo-box">
                <label id="rechazo-label" style="font-size:12px; font-weight:700; color:#ef4444; display:block; margin-bottom:6px;">Motivo del Reclamo/Rechazo (Obligatorio):</label>
                <textarea id="motivo_rechazo" class="rechazo-input" placeholder="Especifique la razón..."></textarea>
            </div>
        </div>
        <div class="modal-ftr">
            <button class="btn-rechazar" id="btn-show-rechazo" onclick="mostrarCajaRechazo()">Rechazar</button>
            <button class="btn-rechazar" id="btn-confirmar-rechazo" style="display:none;" onclick="procesarNC('RECHAZADA')">Confirmar Rechazo</button>
            <button class="btn-aprobar" id="btn-confirmar-aprobar" onclick="procesarNC('APROBADA')">Aprobar Solicitud</button>
            <button class="btn-print" id="btn-cerrar-modal" style="background:#f1f5f9; color:#475569; border-color:#e2e8f0;" onclick="cerrarModal()">Cerrar</button>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
let _allSolicitudes = [];

function initFiltros() {
    const rows = document.querySelectorAll('.nc-row');
    _allSolicitudes = Array.from(rows).map(tr => {
        return {
            element: tr,
            data: JSON.parse(tr.getAttribute('data-solicitud') || '{}')
        };
    });
}

window.aplicarFiltrosLocal = function() {
    const q = document.getElementById('filtro-q').value.toLowerCase().trim();
    const estado = document.getElementById('filtro-estado').value;
    const tecnico = document.getElementById('filtro-tecnico').value.toLowerCase().trim();
    const fecha = document.getElementById('filtro-fecha').value;
    
    let count = 0;
    _allSolicitudes.forEach(s => {
        const d = s.data;
        
        const matchQ = !q || (
            d.nro_solicitud.toLowerCase().includes(q) ||
            d.asunto.toLowerCase().includes(q) ||
            d.orden.toLowerCase().includes(q)
        );
        
        const matchEstado = !estado || d.estado.toUpperCase() === estado;
        const matchTecnico = !tecnico || d.tecnico.toLowerCase().includes(tecnico);
        const matchFecha = !fecha || d.fecha === fecha;
        
        if (matchQ && matchEstado && matchTecnico && matchFecha) {
            s.element.style.display = '';
            count++;
        } else {
            s.element.style.display = 'none';
        }
    });
    
    // Actualizar dinámicamente el KPI del total filtrado
    document.getElementById('kpi-total-nc').innerText = count;

    const emptyRowFiltered = document.getElementById('nc-empty-row-filtered');
    if (emptyRowFiltered) {
        emptyRowFiltered.style.display = count === 0 ? '' : 'none';
    }
}

window.abrirGestion = function(nc, esReadOnly = false) {
    document.getElementById('nc-id').value = nc.id;
    document.getElementById('nc-nro').textContent = nc.nro_solicitud;
    
    // Resolver el nombre del técnico desde la relación del JSON, o fallback al string original
    const nombreTecnico = nc.tecnico?.nombre_tecnico || nc.tecnico_nombre || 'No registrado';
    document.getElementById('nc-tec').textContent = nombreTecnico;
    document.getElementById('nc-asunto').textContent = nc.asunto;
    document.getElementById('nc-detalle').textContent = nc.detalles;
    
    const rBox = document.getElementById('rechazo-box');
    const confirmRechBtn = document.getElementById('btn-confirmar-rechazo');
    const showRechBtn = document.getElementById('btn-show-rechazo');
    const aprobarBtn = document.getElementById('btn-confirmar-aprobar');
    const cerrarBtn = document.getElementById('btn-cerrar-modal');
    const titulo = document.getElementById('modal-titulo-nc');
    
    if (esReadOnly) {
        titulo.textContent = 'Detalles de la Solicitud NC';
        showRechBtn.style.display = 'none';
        confirmRechBtn.style.display = 'none';
        aprobarBtn.style.display = 'none';
        cerrarBtn.style.display = 'inline-block';
        
        if (nc.estado.toUpperCase() === 'RECHAZADA') {
            rBox.style.display = 'block';
            document.getElementById('rechazo-label').textContent = 'Motivo del Rechazo:';
            document.getElementById('rechazo-label').style.color = '#dc2626';
            document.getElementById('motivo_rechazo').value = nc.motivo_rechazo || 'No especificado.';
            document.getElementById('motivo_rechazo').readOnly = true;
        } else {
            rBox.style.display = 'none';
        }
    } else {
        titulo.textContent = 'Gestión de Solicitud NC';
        rBox.style.display = 'none';
        showRechBtn.style.display = 'inline-block';
        confirmRechBtn.style.display = 'none';
        aprobarBtn.style.display = 'inline-block';
        cerrarBtn.style.display = 'none';
        document.getElementById('motivo_rechazo').value = '';
        document.getElementById('motivo_rechazo').readOnly = false;
        document.getElementById('rechazo-label').textContent = 'Motivo del Reclamo/Rechazo (Obligatorio):';
        document.getElementById('rechazo-label').style.color = '#ef4444';
    }

    document.getElementById('modal-gestion').classList.add('activo');
}

window.cerrarModal = function() {
    document.getElementById('modal-gestion').classList.remove('activo');
}

window.mostrarCajaRechazo = function() {
    document.getElementById('rechazo-box').style.display = 'block';
    document.getElementById('btn-show-rechazo').style.display = 'none';
    document.getElementById('btn-confirmar-rechazo').style.display = 'inline-block';
}

window.procesarNC = async function(estado) {
    const id = document.getElementById('nc-id').value;
    const motivo = document.getElementById('motivo_rechazo').value.trim();

    if (estado === 'RECHAZADA' && !motivo) {
        alert('Debe ingresar un motivo para rechazar la solicitud.');
        return;
    }

    if (!confirm(`¿Confirma que desea MARCAR como ${estado} esta solicitud?`)) return;

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('solicitud_id', id);
    fd.append('estado', estado);
    if(estado === 'RECHAZADA') fd.append('motivo_rechazo', motivo);

    try {
        const r = await fetch('{{ route("notas_credito.gestionar") }}', { method: 'POST', body: fd });
        const d = await r.json();
        
        if (d.ok) {
            alert(d.mensaje);
            location.reload();
        } else {
            alert(d.error);
        }
    } catch(e) { alert('Error de conexión.'); }
}

window.exportarAuditoriaNC = function(tipo) {
    const rows = Array.from(document.querySelectorAll('.nc-row')).filter(row => row.style.display !== 'none');
    
    if (rows.length === 0) {
        alert('No hay datos filtrados para exportar.');
        return;
    }

    let csvContent = "\uFEFF"; // BOM para caracteres UTF-8 en Excel
    const headers = ["Nro. Solicitud", "Asunto", "Fecha", "Tecnico Solicitante", "Orden Relacionada", "Estado"];
    csvContent += headers.join(",") + "\r\n";

    rows.forEach(row => {
        const nroSol = row.querySelector('.audit-nro-sol').innerText.trim();
        const asunto = row.querySelector('.audit-asunto').innerText.trim().replace(/"/g, '""');
        const fecha = row.querySelector('.audit-fecha').innerText.trim();
        const tecnico = row.querySelector('.audit-tecnico').innerText.trim();
        const orden = row.querySelector('.audit-orden') ? row.querySelector('.audit-orden').innerText.trim() : '-';
        const estado = row.querySelector('.audit-estado').innerText.trim();

        const rowData = [nroSol, asunto, fecha, tecnico, orden, estado];
        csvContent += rowData.map(val => `"${val}"`).join(",") + "\r\n";
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    
    link.setAttribute("download", `auditoria_notas_credito_${new Date().toISOString().slice(0,10)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

let _ncaPager = null;
document.addEventListener('DOMContentLoaded', () => {
    initFiltros();
    
    _ncaPager = new SgnPager({
        containerSelector: '#nca-tbody',
        itemSelector: 'tr.nc-row',
        pagerContainerSelector: '#nca-pager',
        pageSize: 15
    });
});
</script>
@endpush
