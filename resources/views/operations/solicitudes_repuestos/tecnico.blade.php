@extends('layouts.app')

@section('titulo', 'Solicitar Repuesto')

@push('css_adicional')
<style>
    .sr-wrap { max-width: 1000px; margin: 0 auto; padding: 24px; }
    .sr-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; margin-bottom:20px; }
    .sr-title { margin:0 0 8px; font-size:19px; color:#0f172a; font-weight:800; }
    .sr-sub { margin:0 0 16px; color:#64748b; font-size:13px; }
    .sr-grid { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
    .sr-field { display:flex; flex-direction:column; gap:6px; }
    .sr-field label { font-size:12px; font-weight:700; color:#475569; }
    .sr-field input, .sr-field select, .sr-field textarea {
        border:1.5px solid #e2e8f0; border-radius:8px; padding:9px 11px; font-size:14px;
    }
    .sr-field textarea { min-height:90px; resize:vertical; }
    .sr-actions { display:flex; justify-content:flex-end; margin-top:12px; }
    .btn-send { border:none; background:#2563eb; color:#fff; border-radius:8px; padding:10px 16px; font-weight:700; }
    .sr-msg { display:none; margin-top:10px; padding:10px 12px; border-radius:8px; font-size:13px; }
    .sr-table { width:100%; border-collapse:collapse; }
    .sr-table th, .sr-table td { border-bottom:1px solid #f1f5f9; padding:10px; font-size:13px; text-align:left; }
    .btn-print { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:6px; padding:6px 10px; font-size:12px; font-weight:700; text-decoration:none; display:inline-block; }
    .btn-print:hover { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
    .badge { display:inline-block; border-radius:999px; padding:3px 10px; font-size:11px; font-weight:700; }
    .b-p { background:#fef3c7; color:#92400e; }
    .b-a { background:#dcfce7; color:#166534; }
    .b-r { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('contenido')
<div class="sr-wrap">
    <div class="sr-card">
        <h2 class="sr-title">Solicitar Repuesto</h2>
        <p class="sr-sub">Envia un ticket de bodega para una orden asignada.</p>
        <div class="sr-grid">
            <div class="sr-field">
                <label>Orden</label>
                <select id="orden_id">
                    <option value="">Seleccione una orden...</option>
                    @foreach($ordenes as $o)
                        <option value="{{ $o->id }}">{{ $o->nro_orden }} - {{ $o->estado_orden }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sr-field">
                <label>Cantidad</label>
                <input id="cantidad" type="number" min="1" value="1">
            </div>
            <div class="sr-field">
                <label>Nombre repuesto</label>
                <input id="repuesto_nombre" maxlength="255" placeholder="Ej: Pantalla A123">
            </div>
            <div class="sr-field">
                <label>Nro de parte</label>
                <input id="nro_parte" maxlength="100">
            </div>
            <div class="sr-field" style="grid-column:1 / span 2;">
                <label>Link de compra</label>
                <input id="link_compra" type="url" placeholder="https://...">
            </div>
            <div class="sr-field" style="grid-column:1 / span 2;">
                <label>Descripcion</label>
                <textarea id="descripcion" placeholder="Detalles tecnicos del repuesto..."></textarea>
            </div>
        </div>
        <div class="sr-actions">
            <button id="btnEnviarSR" class="btn-send" onclick="enviarSolicitudSR()">Enviar Solicitud</button>
        </div>
        <div id="msgSR" class="sr-msg"></div>
    </div>

    <div class="sr-card">
        <h3 class="sr-title" style="font-size:16px;">Mis tickets de repuesto</h3>
        <table class="sr-table">
            <thead>
                <tr>
                    <th>Nro</th>
                    <th>Orden</th>
                    <th>Repuesto</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="sr-tbody">
                @forelse($solicitudes as $s)
                    @php
                        $estado = strtoupper((string)$s->estado);
                        $esCompra = $estado === 'COMPRA' || ($estado === 'APROBADA' && empty($s->repuesto_id));
                        $cls = $estado === 'RECHAZADA' ? 'b-r' : ($esCompra ? 'b-p' : ($estado === 'APROBADA' ? 'b-a' : 'b-p'));
                        $estadoLabel = $esCompra ? 'COMPRA' : ($s->estado ?: '-');
                    @endphp
                    <tr data-row="sr">
                        <td>{{ $s->nro_solicitud }}</td>
                        <td>{{ $s->orden->nro_orden ?? ('#' . $s->orden_id) }}</td>
                        <td>{{ $s->repuesto_nombre }}</td>
                        <td><span class="badge {{ $cls }}">{{ $estadoLabel }}</span></td>
                        <td>{{ $s->fecha_solicitud }}</td>
                        <td style="text-align:right;">
                            <a href="{{ route('solicitudes_repuestos.imprimir', ['id' => $s->id]) }}" target="_blank" class="btn-print">
                                <i class="bi bi-printer"></i> Imprimir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:#94a3b8;">Sin solicitudes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div id="sr-pager" style="margin-top:15px;"></div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
async function enviarSolicitudSR() {
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('orden_id', document.getElementById('orden_id').value);
    fd.append('cantidad', document.getElementById('cantidad').value);
    fd.append('repuesto_nombre', document.getElementById('repuesto_nombre').value.trim());
    fd.append('nro_parte', document.getElementById('nro_parte').value.trim());
    fd.append('link_compra', document.getElementById('link_compra').value.trim());
    fd.append('descripcion', document.getElementById('descripcion').value.trim());

    const btn = document.getElementById('btnEnviarSR');
    const msg = document.getElementById('msgSR');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
        const r = await fetch('{{ route("solicitudes_repuestos.solicitar") }}', { method: 'POST', body: fd });
        const d = await r.json();
        msg.style.display = 'block';
        if (d.ok) {
            msg.style.background = '#f0fdf4';
            msg.style.color = '#166534';
            msg.textContent = d.mensaje || 'Solicitud enviada.';
            setTimeout(() => location.reload(), 900);
        } else {
            msg.style.background = '#fef2f2';
            msg.style.color = '#991b1b';
            msg.textContent = d.error || 'No se pudo enviar.';
        }
    } catch (e) {
        msg.style.display = 'block';
        msg.style.background = '#fef2f2';
        msg.style.color = '#991b1b';
        msg.textContent = 'Error de conexion.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enviar Solicitud';
    }
}

let _srPager = null;
document.addEventListener('DOMContentLoaded', () => {
    _srPager = new SgnPager({
        containerSelector: '#sr-tbody',
        itemSelector: 'tr[data-row="sr"]',
        pagerContainerSelector: '#sr-pager',
        pageSize: 10
    });
});
</script>
@endpush
