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
</style>
@endpush

@section('contenido')
<div class="nc-wrap">
    <div class="nc-hdr">
        <h2><i class="bi bi-file-earmark-check" style="color:#2563eb;"></i> Autorización de Notas de Crédito</h2>
    </div>

    <div class="nc-card">
        <table class="nc-table">
            <thead>
                <tr>
                    <th>Solicitud / Nro</th>
                    <th>Fecha</th>
                    <th>Técnico Solicitante</th>
                    <th>Orden Afectada</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitudes as $nc)
                    @php
                        $estadoNC = strtoupper((string) $nc->estado);
                        $clase = match($estadoNC) { 'PENDIENTE' => 'st-pend', 'APROBADA' => 'st-aprob', 'RECHAZADA' => 'st-rech', default => '' };
                    @endphp
                    <tr>
                        <td>
                            <span class="badge">{{ $nc->nro_solicitud }}</span><br>
                            <span style="font-size:11px;color:#64748b;margin-top:4px;display:block;">{{ $nc->asunto }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($nc->creado_en)->format('d/m/Y') }}</td>
                        <td>{{ $nc->tecnico_nombre }}</td>
                        <td><a href="#" style="color:#2563eb;text-decoration:none;font-weight:600;">{{ $nc->orden->nro_orden }}</a></td>
                        <td><span class="status-badge {{ $clase }}">{{ $nc->estado }}</span></td>
                        <td style="text-align:right;">
                            @if($estadoNC === 'PENDIENTE')
                                <button class="btn-gestion" onclick="abrirGestion({{ json_encode($nc) }})">Gestionar</button>
                            @else
                                <span style="font-size:11px;color:#94a3b8;">Resuelto por: {{ $nc->nombre_admin }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">No hay solicitudes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modal-gestion">
    <div class="modal-box">
        <div class="modal-hdr">
            <h3>Gestión de Solicitud NC</h3>
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
                <label style="font-size:12px; font-weight:700; color:#ef4444; display:block; margin-bottom:6px;">Motivo del Rechazo (Obligatorio):</label>
                <textarea id="motivo_rechazo" class="rechazo-input" placeholder="Especifique la razón del rechazo..."></textarea>
            </div>
        </div>
        <div class="modal-ftr">
            <button class="btn-rechazar" id="btn-show-rechazo" onclick="mostrarCajaRechazo()">Rechazar</button>
            <button class="btn-rechazar" id="btn-confirmar-rechazo" style="display:none;" onclick="procesarNC('RECHAZADA')">Confirmar Rechazo</button>
            <button class="btn-aprobar" onclick="procesarNC('APROBADA')">Aprobar Solicitud</button>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
function abrirGestion(nc) {
    document.getElementById('nc-id').value = nc.id;
    document.getElementById('nc-nro').textContent = nc.nro_solicitud;
    document.getElementById('nc-tec').textContent = nc.tecnico_nombre;
    document.getElementById('nc-asunto').textContent = nc.asunto;
    document.getElementById('nc-detalle').textContent = nc.detalles;
    
    // Reset modal state
    document.getElementById('rechazo-box').style.display = 'none';
    document.getElementById('btn-show-rechazo').style.display = 'inline-block';
    document.getElementById('btn-confirmar-rechazo').style.display = 'none';
    document.getElementById('motivo_rechazo').value = '';

    document.getElementById('modal-gestion').classList.add('activo');
}

function cerrarModal() {
    document.getElementById('modal-gestion').classList.remove('activo');
}

function mostrarCajaRechazo() {
    document.getElementById('rechazo-box').style.display = 'block';
    document.getElementById('btn-show-rechazo').style.display = 'none';
    document.getElementById('btn-confirmar-rechazo').style.display = 'inline-block';
}

async function procesarNC(estado) {
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
</script>
@endpush
