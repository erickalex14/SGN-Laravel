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
</style>
@endpush

@section('contenido')
<div class="lc-container">
    <div class="lc-hdr">
        <h2><i class="bi bi-cart-check" style="color:#2563eb;"></i> Consolidación de Compras a Proveedores</h2>
    </div>

    <div class="lc-tabs">
        <button class="lc-tab activo" onclick="lcTab('generar', this)">Generar Lista</button>
        <button class="lc-tab" onclick="lcTab('historial', this)">Historial de Listas</button>
    </div>

    <div id="lc-msg" class="msg-box"></div>

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
                                    <td>{{ $sol->orden->nro_orden }}</td>
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
                    <tbody>
                        @forelse($listas as $lst)
                            @php
                                $estadoRaw = trim((string) ($lst->estado ?? ''));
                                $estadoUi = $estadoRaw === 'Pendiente' ? 'GENERADA' : strtoupper($estadoRaw);
                            @endphp
                            <tr>
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
</script>
@endpush
