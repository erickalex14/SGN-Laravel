@extends('layouts.app')
@section('titulo', 'Gestion de Solicitudes a Bodega')

@push('css_adicional')
<style>
.sr-wrap { max-width: 1300px; margin: 0 auto; padding: 28px 24px; }
.sr-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
.sr-hdr h2 { margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.sr-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
.sr-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sr-table th { background: #f8fafc; padding: 14px 16px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; }
.sr-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
.sr-table tr:hover td { background: #f8fafc; }
.badge-sr { font-family: monospace; font-size: 13px; font-weight: 700; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; }
.st-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.st-pend { background: #fef9c3; color: #854d0e; }
.st-aprob { background: #dcfce7; color: #166534; }
.st-rech { background: #fee2e2; color: #991b1b; }
.st-comp { background: #e0f2fe; color: #0369a1; }
.btn-accion { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; }
.btn-accion:hover { background: #2563eb; color: #fff; }
.btn-print { background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:700; text-decoration:none; display:inline-block; margin-right:6px; }
.btn-print:hover { background:#0f172a; color:#fff; border-color:#0f172a; }
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.6); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; }
.modal-overlay.activo { display: flex; }
.modal-box { background: #fff; width: 100%; max-width: 550px; max-height: 90vh; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
.m-hdr { padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
.m-hdr h3 { margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; }
.btn-cerrar { background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8; }
.m-body { padding: 24px; overflow-y: auto; flex: 1; }
.info-row { margin-bottom: 10px; font-size: 13.5px; }
.info-row strong { color: #475569; display: inline-block; width: 120px; }
.m-ftr { padding: 16px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px; }
.btn-rech { background: #ef4444; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-compra { background: #0284c7; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-aprob { background: #10b981; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.rechazo-box { display: none; margin-top: 16px; }
.rechazo-box textarea { width: 100%; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; resize: vertical; min-height: 80px; font-family: inherit; font-size: 13px; }
.rep-select-wrap { margin-top: 14px; }
.rep-select-wrap label { color: #0f172a; font-weight: 700; font-size: 12px; margin-bottom: 6px; display: block; }
.rep-select { width: 100%; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #1e293b; }
.rep-help { margin-top: 6px; font-size: 11px; color: #64748b; }
</style>
@endpush

@section('contenido')
<div class="sr-wrap">
    <div class="sr-hdr">
        <h2><i class="bi bi-box-seam" style="color:#2563eb;"></i> Solicitudes de Repuestos (Bodega)</h2>
    </div>

    <div class="sr-card">
        <div style="overflow-x:auto;">
            <table class="sr-table">
                <thead>
                    <tr>
                        <th>Nro. Ticket</th>
                        <th>Fecha</th>
                        <th>Orden</th>
                        <th>Tecnico</th>
                        <th>Repuesto Solicitado</th>
                        <th>Cant.</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Accion</th>
                    </tr>
                </thead>
            <tbody id="sra-tbody">
                @forelse($solicitudes as $sr)
                    @php
                        $estadoSR = strtoupper((string) $sr->estado);
                        $esCompra = $estadoSR === 'COMPRA' || ($estadoSR === 'APROBADA' && empty($sr->repuesto_id));
                        $clase = match(true) {
                            $estadoSR === 'PENDIENTE' => 'st-pend',
                            $estadoSR === 'RECHAZADA' => 'st-rech',
                            $esCompra => 'st-comp',
                            $estadoSR === 'APROBADA' => 'st-aprob',
                            default => '',
                        };
                        $estadoLabel = $esCompra ? 'COMPRA' : ($sr->estado ?: '-');
                    @endphp
                    <tr data-row="sra">
                        <td><span class="badge-sr">{{ $sr->nro_solicitud }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($sr->fecha_solicitud)->format('d/m/Y') }}</td>
                        <td><strong>{{ $sr->orden->nro_orden }}</strong></td>
                        <td>{{ $sr->tecnico->nombre_tecnico ?? $sr->tecnico_nombre }}</td>
                        <td>
                            <strong>{{ $sr->repuesto_nombre ?? ($sr->repuestoAsignado ? $sr->repuestoAsignado->nombre : 'Indefinido') }}</strong><br>
                            <span style="font-size:11px;color:#64748b;">{{ $sr->nro_parte ? 'P/N: '.$sr->nro_parte : '' }}</span>
                        </td>
                        <td><strong>{{ $sr->cantidad }}</strong></td>
                        <td><span class="st-badge {{ $clase }}">{{ $estadoLabel }}</span></td>
                        <td style="text-align:right;">
                            <a href="{{ route('solicitudes_repuestos.imprimir', ['id' => $sr->id]) }}" target="_blank" class="btn-print">Imprimir</a>
                            @if($estadoSR === 'PENDIENTE')
                                <button class="btn-accion" onclick='abrirGestion(@json($sr))'>Atender</button>
                            @else
                                <span style="font-size:11px;color:#94a3b8;"><i class="bi bi-check2-all"></i> Resuelto</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:#94a3b8;">No hay solicitudes pendientes en bodega.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div id="sra-pager" style="margin: 0 16px 16px;"></div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-gestion">
    <div class="modal-box">
        <div class="m-hdr">
            <h3>Atender Ticket a Bodega</h3>
            <button class="btn-cerrar" onclick="document.getElementById('modal-gestion').classList.remove('activo')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="m-body">
            <input type="hidden" id="sr-id">
            <div class="info-row"><strong>Ticket:</strong> <span id="sr-nro"></span></div>
            <div class="info-row"><strong>Orden:</strong> <span id="sr-orden"></span></div>
            <div class="info-row"><strong>Tecnico:</strong> <span id="sr-tec"></span></div>

            <div style="margin-top:16px; padding:16px; background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:8px;">
                <div class="info-row"><strong>Repuesto:</strong> <span id="sr-rep"></span></div>
                <div class="info-row"><strong>Nro. Parte:</strong> <span id="sr-part"></span></div>
                <div class="info-row"><strong>Cantidad:</strong> <strong style="color:#2563eb;" id="sr-cant"></strong></div>
                <div class="info-row"><strong>Link/URL:</strong> <a id="sr-link" href="#" target="_blank" style="color:#2563eb;"></a></div>
                <div class="info-row" style="margin-top:8px;"><strong>Notas:</strong> <span id="sr-desc"></span></div>
            </div>

            <div class="rep-select-wrap" style="position: relative;">
                <label for="sr-repuesto-busqueda">Buscar Repuesto de Inventario para Despacho</label>
                <div style="display: flex; gap: 8px; margin-bottom: 6px;">
                    <input type="text" id="sr-repuesto-busqueda" class="rep-select" placeholder="Escriba código, nombre o descripción..." style="flex: 1;" autocomplete="off">
                    <button type="button" class="btn-accion" id="btn-buscar-repuesto" style="margin: 0; padding: 0 16px;" onclick="buscarRepuestosAjax()">Buscar</button>
                </div>
                
                <!-- Contenedor de resultados de búsqueda -->
                <div id="sr-busqueda-resultados" style="display: none; position: absolute; left: 0; right: 0; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10000; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 2px;">
                    <!-- Los resultados se inyectarán aquí -->
                </div>

                <!-- Input oculto para almacenar el id seleccionado -->
                <input type="hidden" id="sr-repuesto-id" value="">
                
                <!-- Visualización del repuesto seleccionado actualmente -->
                <div id="sr-seleccionado-info" style="display: none; margin-top: 8px; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 6px; flex: 1;">
                        <strong style="color: #166534; font-size: 13px;">Seleccionado:</strong>
                        <span id="sr-seleccionado-nombre" style="font-size: 13px; color: #1e293b; font-weight: 600;"></span>
                    </div>
                    <button type="button" style="background: none; border: none; color: #ef4444; font-weight: 700; cursor: pointer; font-size: 16px; line-height: 1;" onclick="deseleccionarRepuesto()">&times;</button>
                </div>

                <div class="rep-help" id="sr-repuesto-help" style="margin-top: 6px;">Escriba y busque para asignar un repuesto de inventario.</div>
            </div>

            <div style="margin-top: 14px; margin-bottom: 14px;">
                <label for="sr-repuesto-cantidad" style="font-weight: 700; font-size: 12px; color: #475569; display: block; margin-bottom: 6px;">Cantidad a Despachar / Comprar</label>
                <input type="number" id="sr-repuesto-cantidad" class="rep-select" min="1" style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px;" placeholder="Ingrese cantidad a despachar...">
            </div>

            <div class="rechazo-box" id="box-rechazo">
                <label style="color:#ef4444; font-weight:700; font-size:12px; margin-bottom:6px; display:block;">Motivo de rechazo (obligatorio)</label>
                <textarea id="motivo_rechazo" placeholder="Ej: No se encuentra en stock y no fue aprobado por gerencia..."></textarea>
            </div>
        </div>
        <div class="m-ftr">
            <button class="btn-rech" id="btn-show-rechazo" onclick="mostrarRechazo()">Rechazar</button>
            <button class="btn-rech" id="btn-conf-rechazo" style="display:none;" onclick="procesar('RECHAZADA')">Confirmar rechazo</button>
            <button class="btn-compra" onclick="procesar('COMPRA')">Mandar a compras</button>
            <button class="btn-aprob" onclick="procesar('APROBADA')">Aprobar y despachar</button>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
function seleccionarRepuesto(rep) {
    document.getElementById('sr-repuesto-id').value = rep.id;
    document.getElementById('sr-seleccionado-nombre').textContent = `${rep.codigo} - ${rep.nombre} (Stock: ${rep.stock})`;
    document.getElementById('sr-seleccionado-info').style.display = 'flex';
    document.getElementById('sr-busqueda-resultados').style.display = 'none';
    document.getElementById('sr-repuesto-busqueda').value = '';
    
    const help = document.getElementById('sr-repuesto-help');
    if (help) {
        help.textContent = `Stock disponible del item seleccionado: ${rep.stock}`;
    }
}

function deseleccionarRepuesto() {
    document.getElementById('sr-repuesto-id').value = '';
    document.getElementById('sr-seleccionado-info').style.display = 'none';
    
    const help = document.getElementById('sr-repuesto-help');
    if (help) {
        help.textContent = 'Escriba y busque para asignar un repuesto de inventario.';
    }
}

async function buscarRepuestosAjax(mostrarAlert = true) {
    const q = document.getElementById('sr-repuesto-busqueda').value.trim();
    const resContainer = document.getElementById('sr-busqueda-resultados');
    
    if (q.length < 2) {
        if (mostrarAlert) {
            alert('Por favor, ingrese al menos 2 caracteres para buscar.');
        }
        resContainer.style.display = 'none';
        return;
    }
    
    resContainer.style.display = 'block';
    resContainer.innerHTML = '<div style="padding: 10px; color: #64748b; font-size: 13px;">Buscando...</div>';
    
    try {
        const response = await fetch(`{{ route('solicitudes_repuestos.admin.buscar_repuesto') }}?q=${encodeURIComponent(q)}&stock_only=false`);
        const data = await response.json();
        
        if (data.ok && data.repuestos && data.repuestos.length > 0) {
            resContainer.innerHTML = '';
            data.repuestos.forEach(rep => {
                const item = document.createElement('div');
                item.style.padding = '8px 12px';
                item.style.cursor = 'pointer';
                item.style.borderBottom = '1px solid #f1f5f9';
                item.style.fontSize = '13px';
                item.innerHTML = `<strong>${rep.codigo}</strong> - ${rep.nombre} <span style="color: ${rep.stock > 0 ? '#166534' : '#ef4444'}; font-weight: bold;">(Stock: ${rep.stock})</span>`;
                item.onmouseover = () => item.style.background = '#f8fafc';
                item.onmouseout = () => item.style.background = '#fff';
                item.onclick = () => seleccionarRepuesto(rep);
                resContainer.appendChild(item);
            });
        } else {
            resContainer.innerHTML = '<div style="padding: 10px; color: #94a3b8; font-size: 13px;">No se encontraron repuestos.</div>';
        }
    } catch (e) {
        resContainer.innerHTML = '<div style="padding: 10px; color: #ef4444; font-size: 13px;">Error al buscar repuesto.</div>';
    }
}

function abrirGestion(sr) {
    document.getElementById('sr-id').value = sr.id;
    document.getElementById('sr-nro').textContent = sr.nro_solicitud;
    document.getElementById('sr-orden').textContent = sr.orden ? sr.orden.nro_orden : 'Desconocida';
    
    // Mostrar nombre real del tecnico (de la relacion eager loaded si existe)
    document.getElementById('sr-tec').textContent = (sr.tecnico ? sr.tecnico.nombre_tecnico : sr.tecnico_nombre) || '-';

    document.getElementById('sr-rep').textContent = sr.repuesto_nombre || ('Repuesto del Catalogo ID: ' + (sr.repuesto_inv_id || '-'));
    document.getElementById('sr-part').textContent = sr.nro_parte || '-';
    document.getElementById('sr-cant').textContent = sr.cantidad || '-';
    document.getElementById('sr-desc').textContent = sr.descripcion || '-';
    document.getElementById('sr-repuesto-cantidad').value = sr.cantidad || 1;

    const lnk = document.getElementById('sr-link');
    if (sr.link_compra) {
        lnk.href = sr.link_compra;
        lnk.textContent = 'Ver proveedor';
    } else {
        lnk.removeAttribute('href');
        lnk.textContent = '-';
    }

    document.getElementById('box-rechazo').style.display = 'none';
    document.getElementById('btn-show-rechazo').style.display = 'inline-block';
    document.getElementById('btn-conf-rechazo').style.display = 'none';
    document.getElementById('motivo_rechazo').value = '';

    // Limpiar input y contenedor de busqueda
    document.getElementById('sr-repuesto-busqueda').value = '';
    document.getElementById('sr-busqueda-resultados').style.display = 'none';

    // Si ya existe un repuesto asignado o sugerido en el catalogo, preseleccionarlo
    if (sr.repuesto_asignado) {
        seleccionarRepuesto(sr.repuesto_asignado);
    } else if (sr.repuesto_catalogo) {
        seleccionarRepuesto(sr.repuesto_catalogo);
    } else if (sr.repuesto_inv_id) {
        // Fallback si no cargo la relacion por alguna razon pero tiene el id
        seleccionarRepuesto({
            id: sr.repuesto_inv_id,
            codigo: sr.repuesto_codigo || 'N/A',
            nombre: sr.repuesto_nombre || 'Sugerido',
            stock: 0
        });
    } else {
        deseleccionarRepuesto();
    }

    document.getElementById('modal-gestion').classList.add('activo');
}

function mostrarRechazo() {
    document.getElementById('box-rechazo').style.display = 'block';
    document.getElementById('btn-show-rechazo').style.display = 'none';
    document.getElementById('btn-conf-rechazo').style.display = 'inline-block';
}

async function procesar(estado) {
    const id = document.getElementById('sr-id').value;
    const motivo = document.getElementById('motivo_rechazo').value.trim();
    const repuestoId = document.getElementById('sr-repuesto-id').value;
    const cantidad = document.getElementById('sr-repuesto-cantidad').value;

    if (estado === 'RECHAZADA' && !motivo) {
        alert('Indique el motivo del rechazo.');
        return;
    }

    if (estado === 'APROBADA' && !repuestoId) {
        alert('Para aprobar y despachar debe seleccionar un repuesto. Si no hay stock use "Mandar a compras".');
        return;
    }

    if (estado !== 'RECHAZADA' && (!cantidad || parseInt(cantidad) <= 0)) {
        alert('Por favor ingrese una cantidad válida (mayor a 0).');
        return;
    }

    if (!confirm(`Confirma pasar el ticket al estado: ${estado}?`)) return;

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('solicitud_id', id);
    fd.append('estado', estado);
    fd.append('cantidad', cantidad);
    if (estado === 'APROBADA' && repuestoId) fd.append('repuesto_id', repuestoId);
    if (estado === 'RECHAZADA') fd.append('motivo_rechazo', motivo);

    try {
        const r = await fetch('{{ route("solicitudes_repuestos.gestionar") }}', { method: 'POST', body: fd });
        const d = await r.json();
        if (d.ok) {
            alert(d.mensaje);
            location.reload();
        } else {
            alert(d.error || 'No se pudo procesar la solicitud.');
        }
    } catch (e) {
        alert('Error critico en el servidor.');
    }
}

let _sraPager = null;
document.addEventListener('DOMContentLoaded', () => {
    // Escuchar el Enter en la barra de busqueda de repuestos
    const txtBusqueda = document.getElementById('sr-repuesto-busqueda');
    let debounceTimer;

    if (txtBusqueda) {
        txtBusqueda.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarRepuestosAjax(true);
            }
        });

        // Autocompletado en tiempo real mientras escribe (con debounce de 300ms)
        txtBusqueda.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const q = e.target.value.trim();
            if (q.length >= 2) {
                debounceTimer = setTimeout(() => {
                    buscarRepuestosAjax(false);
                }, 300);
            } else {
                document.getElementById('sr-busqueda-resultados').style.display = 'none';
            }
        });
    }

    // Cerrar sugerencias al hacer click fuera
    document.addEventListener('click', (e) => {
        const container = document.getElementById('sr-busqueda-resultados');
        const input = document.getElementById('sr-repuesto-busqueda');
        const btn = document.getElementById('btn-buscar-repuesto');
        if (container && e.target !== container && e.target !== input && e.target !== btn && !container.contains(e.target)) {
            container.style.display = 'none';
        }
    });

    _sraPager = new SgnPager({
        containerSelector: '#sra-tbody',
        itemSelector: 'tr[data-row="sra"]',
        pagerContainerSelector: '#sra-pager',
        pageSize: 15
    });
});
</script>
@endpush
