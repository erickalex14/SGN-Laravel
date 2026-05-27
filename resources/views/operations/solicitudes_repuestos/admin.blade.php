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
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.6); z-index: 9999; display: none; align-items: center; justify-content: center; }
.modal-overlay.activo { display: flex; }
.modal-box { background: #fff; width: 100%; max-width: 550px; border-radius: 12px; display: flex; flex-direction: column; }
.m-hdr { padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
.m-hdr h3 { margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; }
.btn-cerrar { background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8; }
.m-body { padding: 24px; }
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
                <tbody>
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
                        <tr>
                            <td><span class="badge-sr">{{ $sr->nro_solicitud }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($sr->fecha_solicitud)->format('d/m/Y') }}</td>
                            <td><strong>{{ $sr->orden->nro_orden }}</strong></td>
                            <td>{{ $sr->tecnico_nombre }}</td>
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

            <div class="rep-select-wrap">
                <label for="sr-repuesto-id">Repuesto de inventario para despacho</label>
                <select id="sr-repuesto-id" class="rep-select">
                    <option value="">Sin asignar (se mantiene sin stock)</option>
                    @foreach($repuestos as $rep)
                        <option value="{{ $rep->id }}" data-stock="{{ (int) $rep->stock }}">
                            {{ $rep->codigo }} - {{ $rep->nombre }} (Stock: {{ (int) $rep->stock }})
                        </option>
                    @endforeach
                </select>
                <div class="rep-help" id="sr-repuesto-help">Si no selecciona repuesto, la orden quedara sin stock.</div>
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
function actualizarAyudaRepuesto() {
    const sel = document.getElementById('sr-repuesto-id');
    const help = document.getElementById('sr-repuesto-help');
    if (!sel || !help) return;

    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        help.textContent = 'Si no selecciona repuesto, la orden quedara sin stock.';
        return;
    }

    const stock = opt.getAttribute('data-stock') || '0';
    help.textContent = `Stock disponible del item seleccionado: ${stock}`;
}

function abrirGestion(sr) {
    document.getElementById('sr-id').value = sr.id;
    document.getElementById('sr-nro').textContent = sr.nro_solicitud;
    document.getElementById('sr-orden').textContent = sr.orden ? sr.orden.nro_orden : 'Desconocida';
    document.getElementById('sr-tec').textContent = sr.tecnico_nombre || '-';

    document.getElementById('sr-rep').textContent = sr.repuesto_nombre || ('Repuesto del Catalogo ID: ' + (sr.repuesto_inv_id || '-'));
    document.getElementById('sr-part').textContent = sr.nro_parte || '-';
    document.getElementById('sr-cant').textContent = sr.cantidad || '-';
    document.getElementById('sr-desc').textContent = sr.descripcion || '-';

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

    const repSelect = document.getElementById('sr-repuesto-id');
    repSelect.value = sr.repuesto_inv_id ? String(sr.repuesto_inv_id) : '';
    actualizarAyudaRepuesto();

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

    if (estado === 'RECHAZADA' && !motivo) {
        alert('Indique el motivo del rechazo.');
        return;
    }

    if (estado === 'APROBADA' && !repuestoId) {
        alert('Para aprobar y despachar debe seleccionar un repuesto. Si no hay stock use "Mandar a compras".');
        return;
    }

    if (!confirm(`Confirma pasar el ticket al estado: ${estado}?`)) return;

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('solicitud_id', id);
    fd.append('estado', estado);
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

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('sr-repuesto-id');
    if (sel) sel.addEventListener('change', actualizarAyudaRepuesto);
});
</script>
@endpush
