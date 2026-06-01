@extends('layouts.app')
@section('titulo', 'Gestión de Listas de Compra')

@push('css_adicional')
<style>
/* CSS unificado manteniendo la fidelidad visual del sistema legacy */
.lc-container { max-width: 1300px; margin: 0 auto; padding: 28px 24px; }
.lc-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
.lc-hdr h2 { margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.lc-tabs { display: flex; gap: 8px; margin-bottom: 24px; }
.lc-tab { padding: 10px 24px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; color: #475569; font-weight: 700; font-size: 13.5px; cursor: pointer; transition: all .2s; }
.lc-tab:hover { background: #e2e8f0; }
.lc-tab.activo { background: #2563eb; color: #fff; border-color: #2563eb; }
.lc-panel { display: none; }
.lc-panel.activo { display: block; }
.lc-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,.02); }
.lc-card-hdr { padding: 16px 20px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-weight: 700; color: #1e293b; display: flex; align-items: center; justify-content: space-between; }
.lc-card-body { padding: 24px; }
.lc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.lc-table th { background: #f8fafc; padding: 12px 16px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; }
.lc-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
.lc-table tr:hover td { background: #f8fafc; }
.btn-submit { background: #10b981; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: opacity .2s; }
.btn-submit:hover { opacity: .9; }
.btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
.badge-lc { font-family: monospace; font-size: 13px; font-weight: 700; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; }
.chk-item { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }
.msg-box { display: none; padding: 14px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
.msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.msg-box.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.btn-pdf { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
.btn-pdf:hover { background: #2563eb; color: #fff; }

/* Estilos adicionales para KPIs y Auditoría */
.kpi-card:hover { transform: translateY(-3px); }
@media print {
    body { background: #fff !important; color: #000 !important; }
    .no-print, .lc-tabs, .lc-hdr, #panel-generar, #panel-historial, .lc-card:has(#audit-search), header, footer, aside, .btn-submit, .btn-pdf, nav, .main-header, .sidebar { display: none !important; }
    #panel-auditoria { display: block !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .lc-card { border: none !important; box-shadow: none !important; }
    .lc-table th { background: #e2e8f0 !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
    .lc-table td { border-bottom: 1px solid #ddd !important; }
    .badge-lc { border: none !important; background: transparent !important; padding: 0 !important; font-weight: bold !important; }
    @page { size: A4 portrait; margin: 15mm; }
}
</style>
@endpush

@section('contenido')
<div class="lc-container">
    <div class="lc-hdr">
        <h2><i class="bi bi-cart-check" style="color:#2563eb;"></i> Consolidación de Compras a Proveedores</h2>
    </div>

    <div class="lc-tabs no-print">
        <button class="lc-tab activo" onclick="lcTab('generar', this)">Generar Lista</button>
        <button class="lc-tab" onclick="lcTab('historial', this)">Historial de Listas</button>
        <button class="lc-tab" onclick="lcTab('auditoria', this)"><i class="bi bi-shield-check me-1"></i> Auditoría / Reportería</button>
    </div>

    <div id="lc-msg" class="msg-box no-print"></div>

    <div class="lc-panel activo" id="panel-generar">
        <div class="lc-card">
            <div class="lc-card-hdr">
                <span><i class="bi bi-list-check me-2"></i> Solicitudes Aprobadas Pendientes de Compra</span>
            </div>
            <form id="form-lista" onsubmit="event.preventDefault(); guardarLista();">
                <div style="overflow-x:auto;">
                    <table class="lc-table">
                        <thead>
                            <tr>
                                <th style="width:40px;"><input type="checkbox" class="chk-item" onchange="marcarTodas(this)"></th>
                                <th>Ticket</th>
                                <th>Orden</th>
                                <th>Repuesto Solicitado</th>
                                <th>Nro Parte</th>
                                <th>Cantidad</th>
                                <th>Link/Proveedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudesPendientes as $sol)
                                <tr>
                                    <td><input type="checkbox" class="chk-item chk-sol" value="{{ $sol->id }}"></td>
                                    <td><span class="badge-lc">{{ $sol->nro_solicitud }}</span></td>
                                    <td>{{ $sol->orden->nro_orden ?? '-' }}</td>
                                    <td><strong>{{ $sol->repuesto_nombre }}</strong></td>
                                    <td>{{ $sol->nro_parte ?: '-' }}</td>
                                    <td><strong style="color:#2563eb;">{{ $sol->cantidad }}</strong></td>
                                    <td>
                                        @if($sol->link_compra)
                                            <a href="{{ $sol->link_compra }}" target="_blank" style="color:#2563eb;">Ver Link</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">No existen requerimientos pendientes de compra.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($solicitudesPendientes->isNotEmpty())
                <div class="lc-card-body" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                    <label style="font-size:13px; font-weight:700; color:#475569; display:block; margin-bottom:8px;">Observaciones Generales de la Lista:</label>
                    <textarea id="observacion" style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px; resize:vertical; min-height:80px; font-family:inherit; font-size:14px; margin-bottom:16px;" placeholder="Agregue indicaciones para el área de compras (opcional)..."></textarea>
                    
                    <button type="submit" id="btn-guardar" class="btn-submit">
                        <i class="bi bi-file-earmark-check"></i> Consolidar Seleccionados en Lista de Compra
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="lc-panel" id="panel-historial">
        <div class="lc-card">
            <div style="overflow-x:auto;">
                <table class="lc-table">
                    <thead>
                        <tr>
                            <th>Nro. Lista</th>
                            <th>Fecha Creación</th>
                            <th>Generado Por</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="lc-historial-tbody">
                        @forelse($listas as $lst)
                            @php
                                $estadoRaw = trim((string) ($lst->estado ?? ''));
                                $estadoUi = $estadoRaw === 'Pendiente' ? 'GENERADA' : strtoupper($estadoRaw);
                            @endphp
                            <tr data-row="lc-historial">
                                <td><span class="badge-lc">{{ $lst->nro_lista }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($lst->fecha_creacion)->format('d/m/Y H:i') }}</td>
                                <td>{{ $lst->creado_por }}</td>
                                <td><span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;">{{ $estadoUi }}</span></td>
                                <td><span style="font-size:12px; color:#64748b;">{{ $lst->observacion ?: '-' }}</span></td>
                                <td style="text-align:right;">
                                    <a href="{{ url('/operaciones/listas-compra/'.$lst->id.'/imprimir') }}" target="_blank" class="btn-pdf">
                                        <i class="bi bi-printer"></i> Imprimir PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">No se han generado listas de compra históricas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="lc-historial-pager" style="padding: 10px 20px 20px;"></div>
        </div>
    </div>

    <div class="lc-panel" id="panel-auditoria">
        @php
            $totalRepuestosComprados = $auditorias->sum('cantidad');
            $totalListas = $listas->count();

            // Calcular técnico con más solicitudes
            $tecnicoMasSolicitudes = '-';
            $maxSolicitudes = 0;
            if ($auditorias->isNotEmpty()) {
                $grupoTecnicos = $auditorias->groupBy(function($s) {
                    return $s->tecnico->nombre_tecnico ?? $s->tecnico_nombre;
                });
                foreach ($grupoTecnicos as $tec => $items) {
                    if ($items->count() > $maxSolicitudes) {
                        $maxSolicitudes = $items->count();
                        $tecnicoMasSolicitudes = $tec;
                    }
                }
            }

            // Calcular repuesto más solicitado
            $repuestoMasSolicitado = '-';
            $maxRepuestos = 0;
            if ($auditorias->isNotEmpty()) {
                $grupoRepuestos = $auditorias->groupBy('repuesto_nombre');
                foreach ($grupoRepuestos as $rep => $items) {
                    if ($items->count() > $maxRepuestos) {
                        $maxRepuestos = $items->count();
                        $repuestoMasSolicitado = $rep;
                    }
                }
            }
        @endphp

        <!-- Dashboard de KPIs de Auditoría de Compras -->
        <div class="lc-kpis" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:24px;">
            <div class="kpi-card" style="background:linear-gradient(135deg, #eff6ff, #dbeafe); border:1.5px solid #bfdbfe; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(37,99,235,.03); transition:transform .2s;">
                <div style="background:#2563eb; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-cart-check-fill"></i></div>
                <div>
                    <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Repuestos Consolidados</span>
                    <span style="font-size:22px; font-weight:800; color:#0f172a; display:block;" id="kpi-cant-filtrada">{{ $totalRepuestosComprados }}</span>
                    <span style="font-size:11px; color:#64748b;">Total unidades en compra</span>
                </div>
            </div>

            <div class="kpi-card" style="background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1.5px solid #bbf7d0; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(16,185,129,.03); transition:transform .2s;">
                <div style="background:#10b981; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-file-earmark-spreadsheet-fill"></i></div>
                <div>
                    <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Listas Generadas</span>
                    <span style="font-size:22px; font-weight:800; color:#0f172a; display:block;">{{ $totalListas }}</span>
                    <span style="font-size:11px; color:#64748b;">Listas de compra en total</span>
                </div>
            </div>

            <div class="kpi-card" style="background:linear-gradient(135deg, #faf5ff, #f3e8ff); border:1.5px solid #e9d5ff; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(147,51,234,.03); transition:transform .2s;">
                <div style="background:#9333ea; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-person-badge-fill"></i></div>
                <div>
                    <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Mayor Demandante</span>
                    <span style="font-size:16px; font-weight:800; color:#0f172a; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;" title="{{ $tecnicoMasSolicitudes }}">{{ $tecnicoMasSolicitudes }}</span>
                    <span style="font-size:11px; color:#64748b;">Con {{ $maxSolicitudes }} requerimientos</span>
                </div>
            </div>

            <div class="kpi-card" style="background:linear-gradient(135deg, #fffbeb, #fef3c7); border:1.5px solid #fde047; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 12px rgba(217,119,6,.03); transition:transform .2s;">
                <div style="background:#d97706; color:#fff; width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;"><i class="bi bi-gear-fill"></i></div>
                <div>
                    <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:4px;">Repuesto Crítico</span>
                    <span style="font-size:16px; font-weight:800; color:#0f172a; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;" title="{{ $repuestoMasSolicitado }}">{{ $repuestoMasSolicitado }}</span>
                    <span style="font-size:11px; color:#64748b;">Con {{ $maxRepuestos }} compras</span>
                </div>
            </div>
        </div>

        <!-- Filtros Avanzados -->
        <div class="lc-card no-print" style="margin-bottom:24px; padding:20px; background:#f8fafc;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h4 style="margin:0; font-size:14px; font-weight:800; color:#1e293b; text-transform:uppercase;"><i class="bi bi-funnel-fill me-1" style="color:#2563eb;"></i> Filtros de Auditoría y Reportería</h4>
                <div style="display:inline-flex; gap:8px;">
                    <button class="btn-submit" style="background:#4f46e5;" onclick="exportarAuditoria('csv')"><i class="bi bi-file-earmark-text-fill"></i> CSV</button>
                    <button class="btn-submit" style="background:#10b981;" onclick="exportarAuditoria('excel')"><i class="bi bi-file-earmark-excel-fill"></i> Excel</button>
                    <button class="btn-submit" style="background:#2563eb;" onclick="window.print()"><i class="bi bi-printer-fill"></i> Imprimir PDF</button>
                </div>
            </div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                <div>
                    <label style="font-size:11.5px; font-weight:700; color:#64748b; display:block; margin-bottom:5px; text-transform:uppercase;">Búsqueda General</label>
                    <input type="text" id="audit-search" oninput="filtrarAuditoria()" class="form-control" style="width:100%; padding:9px 12px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:13px;" placeholder="Repuesto, nro lista, nro orden, cliente...">
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:700; color:#64748b; display:block; margin-bottom:5px; text-transform:uppercase;">Técnico Solicitante</label>
                    <select id="audit-tecnico" onchange="filtrarAuditoria()" class="form-control" style="width:100%; padding:9px 12px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:13px; cursor:pointer;">
                        <option value="">-- Todos --</option>
                        @foreach($tecnicosList as $tec)
                            <option value="{{ strtolower($tec->nombre_tecnico) }}">{{ $tec->nombre_tecnico }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:700; color:#64748b; display:block; margin-bottom:5px; text-transform:uppercase;">Creador de Lista</label>
                    <select id="audit-creador" onchange="filtrarAuditoria()" class="form-control" style="width:100%; padding:9px 12px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:13px; cursor:pointer;">
                        <option value="">-- Todos --</option>
                        @foreach($creadoresList as $creador)
                            <option value="{{ strtolower($creador) }}">{{ $creador }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:700; color:#64748b; display:block; margin-bottom:5px; text-transform:uppercase;">Desde</label>
                    <input type="date" id="audit-fecha-desde" onchange="filtrarAuditoria()" class="form-control" style="width:100%; padding:8px 12px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:13px;">
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:700; color:#64748b; display:block; margin-bottom:5px; text-transform:uppercase;">Hasta</label>
                    <input type="date" id="audit-fecha-hasta" onchange="filtrarAuditoria()" class="form-control" style="width:100%; padding:8px 12px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:13px;">
                </div>
            </div>
        </div>

        <!-- Grilla de Auditoría -->
        <div class="lc-card">
            <div style="overflow-x:auto;">
                <table class="lc-table" id="tabla-auditoria">
                    <thead>
                        <tr>
                            <th>Nro. Lista</th>
                            <th>Ticket Sol.</th>
                            <th>Orden / Cliente</th>
                            <th>Repuesto Solicitado</th>
                            <th>Técnico Solicitante</th>
                            <th>Fecha Lista</th>
                            <th style="text-align:center;">Cant.</th>
                            <th style="text-align:right;" class="no-print">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="lc-auditoria-tbody">
                        @forelse($auditorias as $sol)
                            @php
                                $nroLista = $sol->listaCompra->nro_lista ?? '-';
                                $creador = $sol->listaCompra->creado_por ?? '-';
                                $fechaLista = $sol->listaCompra->fecha_creacion 
                                    ? \Carbon\Carbon::parse($sol->listaCompra->fecha_creacion)->format('d/m/Y H:i') 
                                    : '-';
                                $fechaSort = $sol->listaCompra->fecha_creacion 
                                    ? \Carbon\Carbon::parse($sol->listaCompra->fecha_creacion)->format('Y-m-d') 
                                    : '';
                                $clienteNombre = trim(($sol->orden->cliente->nombres ?? '') . ' ' . ($sol->orden->cliente->apellidos ?? '')) ?: '-';
                                $tecnicoNombre = $sol->tecnico->nombre_tecnico ?? $sol->tecnico_nombre;
                                $searchStr = strtolower(
                                    $sol->repuesto_nombre . ' ' . 
                                    $sol->repuesto_codigo . ' ' . 
                                    $sol->nro_parte . ' ' . 
                                    $nroLista . ' ' . 
                                    $creador . ' ' . 
                                    ($sol->orden->nro_orden ?? '') . ' ' . 
                                    $clienteNombre . ' ' .
                                    $tecnicoNombre
                                );
                            @endphp
                            <tr class="audit-row" data-row="lc-auditoria"
                                data-search="{{ $searchStr }}" 
                                data-tecnico="{{ strtolower($tecnicoNombre) }}" 
                                data-creador="{{ strtolower($creador) }}" 
                                data-fecha="{{ $fechaSort }}"
                                data-cantidad="{{ $sol->cantidad }}">
                                <td>
                                    <span class="badge-lc audit-nro-lista">{{ $nroLista }}</span><br>
                                    <span style="font-size:11px; color:#64748b; margin-top:2px; display:block;">Por: <strong class="audit-creador">{{ $creador }}</strong></span>
                                </td>
                                <td><span class="badge-lc audit-nro-sol" style="background:#eff6ff; color:#2563eb; border-color:#bfdbfe;">{{ $sol->nro_solicitud }}</span></td>
                                <td>
                                    @if($sol->orden)
                                        <a href="{{ route('ordenes.editar', ['id' => $sol->orden_id]) }}" target="_blank" style="color:#2563eb; text-decoration:none; font-weight:700;" class="audit-orden">
                                            {{ $sol->orden->nro_orden }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                    <br>
                                    <span style="font-size:11px; color:#64748b; margin-top:2px; display:block;">Cl: {{ $clienteNombre }}</span>
                                </td>
                                <td>
                                    <strong class="audit-repuesto">{{ $sol->repuesto_nombre }}</strong><br>
                                    <span style="font-size:11px; color:#64748b;">P/N: <span class="audit-nro-parte">{{ $sol->nro_parte ?: 'Sin nro parte' }}</span></span>
                                </td>
                                <td><strong class="audit-tecnico">{{ $tecnicoNombre ?: '-' }}</strong></td>
                                <td>{{ $fechaLista }}</td>
                                <td style="text-align:center;"><strong style="color:#2563eb; font-size:14px;">{{ $sol->cantidad }}</strong></td>
                                <td style="text-align:right;" class="no-print">
                                    @if($sol->lista_compra_id)
                                        <a href="{{ url('/operaciones/listas-compra/'.$sol->lista_compra_id.'/imprimir') }}" target="_blank" class="btn-pdf">
                                            <i class="bi bi-printer"></i> Lista
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="audit-empty-row"><td colspan="8" style="text-align:center; padding:30px; color:#94a3b8;">No se encontraron registros consolidados.</td></tr>
                        @endforelse
                        <tr id="audit-empty-row-filtered" style="display:none;"><td colspan="8" style="text-align:center; padding:30px; color:#94a3b8;">No se encontraron registros coincidentes con los filtros aplicados.</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="lc-auditoria-pager" style="padding: 10px 20px 20px;"></div>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
function lcTab(panel, btn) {
    document.querySelectorAll('.lc-tab').forEach(b => b.classList.remove('activo'));
    document.querySelectorAll('.lc-panel').forEach(p => p.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('panel-' + panel).classList.add('activo');
}

function marcarTodas(source) {
    document.querySelectorAll('.chk-sol').forEach(cb => cb.checked = source.checked);
}

function mostrarMensaje(isError, texto) {
    const box = document.getElementById('lc-msg');
    box.className = 'msg-box ' + (isError ? 'err' : 'ok');
    box.innerHTML = texto;
    box.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function guardarLista() {
    const seleccionados = Array.from(document.querySelectorAll('.chk-sol:checked')).map(cb => cb.value);
    
    if (seleccionados.length === 0) {
        mostrarMensaje(true, 'Debe seleccionar al menos un ticket de repuesto para generar la lista.');
        return;
    }

    if (!confirm(`¿Confirma la consolidación de ${seleccionados.length} solicitudes en una nueva Lista de Compra?`)) return;

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    seleccionados.forEach(id => fd.append('solicitudes_ids[]', id));
    fd.append('observacion', document.getElementById('observacion').value.trim());

    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Consolidando...';

    try {
        const r = await fetch('{{ route("listas_compra.store") }}', { method: 'POST', body: fd });
        const d = await r.json();
        
        if (d.ok) {
            mostrarMensaje(false, `<strong>¡Éxito!</strong> ${d.mensaje}`);
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarMensaje(true, d.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-check"></i> Consolidar Seleccionados en Lista de Compra';
        }
    } catch(e) {
        mostrarMensaje(true, 'Se ha perdido la conexión con el servidor. Intente nuevamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-file-earmark-check"></i> Consolidar Seleccionados en Lista de Compra';
    }
}

function filtrarAuditoria() {
    const searchVal = document.getElementById('audit-search').value.toLowerCase().trim();
    const tecnicoVal = document.getElementById('audit-tecnico').value.toLowerCase();
    const creadorVal = document.getElementById('audit-creador').value.toLowerCase();
    const desdeVal = document.getElementById('audit-fecha-desde').value;
    const hastaVal = document.getElementById('audit-fecha-hasta').value;

    let visibles = 0;
    let totalCant = 0;

    document.querySelectorAll('.audit-row').forEach(row => {
        const searchData = row.getAttribute('data-search');
        const tecnicoData = row.getAttribute('data-tecnico');
        const creadorData = row.getAttribute('data-creador');
        const fechaData = row.getAttribute('data-fecha');
        const cantidad = parseInt(row.getAttribute('data-cantidad') || '0', 10);

        let match = true;

        if (searchVal && !searchData.includes(searchVal)) match = false;
        if (tecnicoVal && tecnicoData !== tecnicoVal) match = false;
        if (creadorVal && creadorData !== creadorVal) match = false;
        
        if (fechaData) {
            if (desdeVal && fechaData < desdeVal) match = false;
            if (hastaVal && fechaData > hastaVal) match = false;
        } else if (desdeVal || hastaVal) {
            match = false;
        }

        if (match) {
            row.style.display = '';
            visibles++;
            totalCant += cantidad;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('kpi-cant-filtrada').innerText = totalCant;

    const emptyRowFiltered = document.getElementById('audit-empty-row-filtered');
    if (visibles === 0) {
        emptyRowFiltered.style.display = '';
    } else {
        emptyRowFiltered.style.display = 'none';
    }
}

function exportarAuditoria(tipo) {
    const rows = Array.from(document.querySelectorAll('.audit-row')).filter(row => row.style.display !== 'none');
    
    if (rows.length === 0) {
        alert('No hay datos filtrados para exportar.');
        return;
    }

    let csvContent = "\uFEFF"; // BOM para asegurar caracteres UTF-8 en Excel
    const headers = ["Nro. Lista", "Creado Por", "Fecha Creacion", "Nro. Solicitud", "Tecnico Solicitante", "Repuesto", "Nro. Parte", "Orden Relacionada", "Cantidad"];
    csvContent += headers.join(",") + "\r\n";

    rows.forEach(row => {
        const nroLista = row.querySelector('.audit-nro-lista').innerText.trim();
        const creadoPor = row.getAttribute('data-creador').trim();
        const fecha = row.getAttribute('data-fecha').trim();
        const nroSol = row.querySelector('.audit-nro-sol').innerText.trim();
        const tecnico = row.querySelector('.audit-tecnico').innerText.trim();
        const repuesto = row.querySelector('.audit-repuesto').innerText.trim().replace(/"/g, '""');
        const nroParte = row.querySelector('.audit-nro-parte').innerText.trim();
        const orden = row.querySelector('.audit-orden') ? row.querySelector('.audit-orden').innerText.trim() : '-';
        const cantidad = row.getAttribute('data-cantidad').trim();

        const rowData = [nroLista, creadoPor, fecha, nroSol, tecnico, repuesto, nroParte, orden, cantidad];
        csvContent += rowData.map(val => `"${val}"`).join(",") + "\r\n";
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    
    link.setAttribute("download", `auditoria_listas_compra_${new Date().toISOString().slice(0,10)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

let _historialPager = null;
let _auditoriaPager = null;
document.addEventListener('DOMContentLoaded', () => {
    _historialPager = new SgnPager({
        containerSelector: '#lc-historial-tbody',
        itemSelector: 'tr[data-row="lc-historial"]',
        pagerContainerSelector: '#lc-historial-pager',
        pageSize: 15
    });
    _auditoriaPager = new SgnPager({
        containerSelector: '#lc-auditoria-tbody',
        itemSelector: 'tr[data-row="lc-auditoria"]',
        pagerContainerSelector: '#lc-auditoria-pager',
        pageSize: 15
    });
});
</script>
@endpush
