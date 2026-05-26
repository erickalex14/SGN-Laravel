@extends('layouts.app')

@section('titulo', 'Mis Solicitudes NC')

@push('css_adicional')
<style>
    .nc-wrap { max-width: 980px; margin: 0 auto; padding: 24px; }
    .nc-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 20px; }
    .nc-title { margin: 0 0 8px; font-size: 19px; color: #0f172a; font-weight: 800; }
    .nc-sub { margin: 0 0 16px; color: #64748b; font-size: 13px; }
    .nc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .nc-field { display: flex; flex-direction: column; gap: 6px; }
    .nc-field label { font-size: 12px; font-weight: 700; color: #475569; }
    .nc-field input, .nc-field select, .nc-field textarea {
        border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 9px 11px; font-size: 14px;
    }
    .nc-field textarea { min-height: 90px; resize: vertical; }
    .nc-actions { display: flex; justify-content: flex-end; margin-top: 12px; }
    .btn-send { border: none; background: #2563eb; color: #fff; border-radius: 8px; padding: 10px 16px; font-weight: 700; }
    .nc-msg { display: none; margin-top: 10px; padding: 10px 12px; border-radius: 8px; font-size: 13px; }
    .nc-table { width: 100%; border-collapse: collapse; }
    .nc-table th, .nc-table td { border-bottom: 1px solid #f1f5f9; padding: 10px; font-size: 13px; text-align: left; }
    .btn-print { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:6px; padding:6px 10px; font-size:12px; font-weight:700; text-decoration:none; display:inline-block; }
    .btn-print:hover { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
    .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 700; }
    .b-p { background: #fef3c7; color: #92400e; }
    .b-a { background: #dcfce7; color: #166534; }
    .b-r { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('contenido')
<div class="nc-wrap">
    <div class="nc-card">
        <h2 class="nc-title">Solicitar Nota de Credito</h2>
        <p class="nc-sub">Crea una solicitud asociada a una orden de trabajo.</p>
        <div class="nc-grid">
            <div class="nc-field">
                <label>Orden</label>
                <select id="orden_id">
                    <option value="">Seleccione una orden...</option>
                    @foreach($ordenes as $o)
                        @php $tieneNc = (int) ($o->solicitudes_nc_count ?? 0) > 0; @endphp
                        <option value="{{ $o->id }}" @if($tieneNc) disabled @endif>
                            {{ $o->nro_orden }} - {{ $o->estado_orden }} @if($tieneNc) [NC registrada] @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="nc-field">
                <label>Asunto</label>
                <input id="asunto" maxlength="255" placeholder="Asunto de la solicitud">
            </div>
            <div class="nc-field" style="grid-column:1 / span 2;">
                <label>Detalles</label>
                <textarea id="detalles" placeholder="Describe la razon de la solicitud..."></textarea>
            </div>
        </div>
        <div class="nc-actions">
            <button id="btnEnviar" class="btn-send" onclick="enviarSolicitudNC()">Enviar Solicitud</button>
        </div>
        <div id="msgNC" class="nc-msg"></div>
    </div>

    <div class="nc-card">
        <h3 class="nc-title" style="font-size:16px;">Mis solicitudes</h3>
        <table class="nc-table">
            <thead>
                <tr>
                    <th>Nro</th>
                    <th>Orden</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitudes as $s)
                    @php
                        $estado = strtoupper((string)$s->estado);
                        $cls = $estado === 'APROBADA' ? 'b-a' : ($estado === 'RECHAZADA' ? 'b-r' : 'b-p');
                    @endphp
                    <tr>
                        <td>{{ $s->nro_solicitud }}</td>
                        <td>{{ $s->orden->nro_orden ?? ('#' . $s->orden_id) }}</td>
                        <td><span class="badge {{ $cls }}">{{ $s->estado }}</span></td>
                        <td>{{ $s->fecha_solicitud }}</td>
                        <td style="text-align:right;">
                            <a href="{{ route('notas_credito.imprimir', ['id' => $s->id]) }}" target="_blank" class="btn-print">
                                <i class="bi bi-printer"></i> Imprimir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:#94a3b8;">Sin solicitudes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
async function enviarSolicitudNC() {
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('orden_id', document.getElementById('orden_id').value);
    fd.append('asunto', document.getElementById('asunto').value.trim());
    fd.append('detalles', document.getElementById('detalles').value.trim());

    const btn = document.getElementById('btnEnviar');
    const msg = document.getElementById('msgNC');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
        const r = await fetch('{{ route("notas_credito.solicitar") }}', { method: 'POST', body: fd });
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
</script>
@endpush
