@extends('layouts.app')
@section('titulo', 'Mis Ordenes Asignadas')

@push('css_adicional')
<style>
.mo-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
.mo-hdr { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
.mo-hdr h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; }
.mo-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
.mo-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.mo-table th { background: #f8fafc; padding: 14px 16px; text-align: left; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
.mo-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
.mo-table tr:hover td { background: #f8fafc; }
.ord-badge { font-family: monospace; font-size: 13px; font-weight: 700; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; color: #0f172a; border: 1px solid #cbd5e1; }
.select-estado { padding: 6px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; outline: none; transition: border-color 0.2s; background: #fff; color: #334155; }
.select-estado:focus { border-color: #2563eb; }
.btn-accion { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
.btn-accion:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
.estado-label { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; display: inline-block; margin-bottom: 6px; }
.estado-ingreso { background: #e0f2fe; color: #0369a1; }
.estado-revision { background: #fef9c3; color: #854d0e; }
.estado-reparado { background: #dcfce7; color: #166534; }
.estado-entregado { background: #f1f5f9; color: #475569; }
.estado-default { background: #f3f4f6; color: #374151; }
.mo-empty { padding: 40px; text-align: center; color: #94a3b8; font-size: 15px; }
.rep-wrap { display: flex; flex-direction: column; gap: 6px; }
.rep-sel { width: 100%; padding: 6px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; }
.rep-actions { display: flex; gap: 6px; align-items: center; margin-top: 6px; }
.btn-mini-rep { border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; cursor: pointer; }
.btn-mini-rep.danger { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
.rep-note { font-size: 10.5px; color: #94a3b8; }
</style>
@endpush

@section('contenido')
<div class="mo-container">
    <div class="mo-hdr">
        <h2>Listado de Ordenes Asignadas</h2>
    </div>

    <div class="mo-card">
        <div style="overflow-x:auto;">
            <table class="mo-table" id="tabla-mis-ordenes">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Fecha Ingreso</th>
                        <th>Cliente</th>
                        <th>Equipo</th>
                        <th>Falla Reportada</th>
                        <th style="width:160px;">Estado Actual</th>
                        <th style="width:210px;">Estado Repuesto</th>
                        <th style="width:260px;">Repuesto (Stock)</th>
                        <th style="width:180px; text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $ord)
                        @php
                            $claseEstado = match($ord->estado_orden) {
                                'INGRESO', 'Pendiente' => 'estado-ingreso',
                                'REVISION', 'EN PROCESO', 'En proceso' => 'estado-revision',
                                'REPARADO', 'Finalizada' => 'estado-reparado',
                                'ENTREGADO', 'Entregada' => 'estado-entregado',
                                default => 'estado-default'
                            };
                        @endphp
                        <tr>
                            <td><span class="ord-badge">{{ $ord->nro_orden }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($ord->fecha_de_ingreso)->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong>{{ $ord->cliente->nombres }} {{ $ord->cliente->apellidos }}</strong><br>
                                <span style="font-size:11px; color:#64748b;">ID: {{ $ord->cliente->identificacion }}</span>
                            </td>
                            <td>
                                <strong>{{ $ord->equipo->marca }} {{ $ord->equipo->modelo }}</strong><br>
                                <span style="font-size:11px; color:#64748b;">S/N: {{ $ord->equipo->serie }}</span>
                            </td>
                            <td>
                                <div style="max-height:40px; overflow:hidden; text-overflow:ellipsis; font-size:12px;" title="{{ $ord->equipo->falla }}">
                                    {{ $ord->equipo->falla }}
                                </div>
                            </td>
                            <td>
                                <span class="estado-label {{ $claseEstado }}">{{ $ord->estado_orden }}</span>
                                <select class="select-estado" onchange="cambiarEstado({{ $ord->id }}, this.value, '{{ $ord->nro_orden }}')">
                                    <option value="">Cambiar a...</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Finalizada">Finalizada</option>
                                    <option value="Entregada">Entregada</option>
                                    <option value="Nota de Credito">Nota de Credito</option>
                                </select>
                            </td>
                            <td>
                                @php $estadoRep = $ord->estado_repuesto ?: 'No requerido'; @endphp
                                <div class="rep-wrap">
                                    <span class="estado-label estado-default" id="rep-lbl-{{ $ord->id }}">{{ $estadoRep }}</span>
                                    <select class="rep-sel" id="rep-estado-{{ $ord->id }}" onchange="cambiarEstadoRepuesto({{ $ord->id }}, this.value)">
                                        <option value="No requerido" @selected($estadoRep === 'No requerido')>No requerido</option>
                                        <option value="Requerido" @selected($estadoRep === 'Requerido')>Requerido</option>
                                        <option value="Con stock" @selected($estadoRep === 'Con stock')>Con stock</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="rep-wrap" id="rep-panel-{{ $ord->id }}" style="{{ $estadoRep === 'Con stock' ? '' : 'display:none;' }}">
                                    <select class="rep-sel" id="rep-inv-{{ $ord->id }}">
                                        <option value="">Seleccione repuesto...</option>
                                        @foreach($repuestos as $rep)
                                            <option value="{{ $rep->id }}" @selected((int)$ord->repuesto_inventario_id === (int)$rep->id)>
                                                {{ $rep->codigo }} - {{ $rep->nombre }} (Stock: {{ (int)$rep->stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="rep-actions">
                                        <button type="button" class="btn-mini-rep" onclick="asignarRepuesto({{ $ord->id }})">Asignar</button>
                                        <button type="button" class="btn-mini-rep danger" onclick="revertirRepuesto({{ $ord->id }})">Revertir</button>
                                    </div>
                                    <div class="rep-note">Revertir disponible para administrador.</div>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('ordenes.imprimir', ['id' => $ord->id]) }}" target="_blank" class="btn-accion" style="margin-right:6px;">
                                    Imprimir OT
                                </a>
                                <a href="{{ url('/operaciones/ordenes/editar/'.$ord->id) }}" class="btn-accion">
                                    Detalles / Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="mo-empty">Actualmente no posee ordenes asignadas en el sistema.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
async function cambiarEstado(ordenId, nuevoEstado, nroOrden) {
    if (!nuevoEstado) return;

    if (!confirm(`Confirma la actualizacion de la orden ${nroOrden} a estado: ${nuevoEstado}?`)) {
        location.reload();
        return;
    }

    let ncAsunto = '';
    let ncDetalles = '';

    if (nuevoEstado === 'Nota de Credito') {
        ncAsunto = (prompt('Asunto de la Nota de Credito:') || '').trim();
        if (!ncAsunto) {
            alert('El asunto es obligatorio para Nota de Credito.');
            location.reload();
            return;
        }

        ncDetalles = (prompt('Detalles / justificacion de la Nota de Credito:') || '').trim();
        if (!ncDetalles) {
            alert('Los detalles son obligatorios para Nota de Credito.');
            location.reload();
            return;
        }
    }

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('id', ordenId);
    fd.append('estado', nuevoEstado);

    if (nuevoEstado === 'Nota de Credito') {
        fd.append('nc_asunto', ncAsunto);
        fd.append('nc_detalles', ncDetalles);
    }

    try {
        const r = await fetch('{{ route("mis_ordenes.estado") }}', {
            method: 'POST',
            body: fd
        });
        const d = await r.json();

        if (d.ok) {
            location.reload();
        } else {
            alert('Error en la actualizacion: ' + d.error);
            location.reload();
        }
    } catch (e) {
        alert('Error de comunicacion con el servidor.');
        location.reload();
    }
}

async function cambiarEstadoRepuesto(ordenId, nuevoEstado) {
    if (!nuevoEstado) return;

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('orden_id', ordenId);
    fd.append('estado_repuesto', nuevoEstado);

    try {
        const r = await fetch('{{ route("mis_ordenes.repuesto_estado") }}', { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo actualizar estado de repuesto.');
            location.reload();
            return;
        }

        const lbl = document.getElementById('rep-lbl-' + ordenId);
        const panel = document.getElementById('rep-panel-' + ordenId);
        if (lbl) lbl.textContent = nuevoEstado;
        if (panel) panel.style.display = (nuevoEstado === 'Con stock') ? '' : 'none';
    } catch (e) {
        alert('Error de comunicación con el servidor.');
        location.reload();
    }
}

async function asignarRepuesto(ordenId) {
    const sel = document.getElementById('rep-inv-' + ordenId);
    const repuestoId = sel ? sel.value : '';
    if (!repuestoId) {
        alert('Seleccione un repuesto.');
        return;
    }

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('orden_id', ordenId);
    fd.append('repuesto_inventario_id', repuestoId);

    try {
        const r = await fetch('{{ route("mis_ordenes.repuesto_asignar") }}', { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo asignar repuesto.');
            return;
        }

        const estadoSel = document.getElementById('rep-estado-' + ordenId);
        const lbl = document.getElementById('rep-lbl-' + ordenId);
        if (estadoSel) estadoSel.value = 'Con stock';
        if (lbl) lbl.textContent = 'Con stock';
        alert('Repuesto asignado correctamente.');
    } catch (e) {
        alert('Error de comunicación con el servidor.');
    }
}

async function revertirRepuesto(ordenId) {
    if (!confirm('¿Confirma revertir el repuesto y devolver stock a inventario?')) return;

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('orden_id', ordenId);

    try {
        const r = await fetch('{{ route("mis_ordenes.repuesto_revertir") }}', { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo revertir repuesto.');
            return;
        }
        alert('Repuesto revertido correctamente.');
        location.reload();
    } catch (e) {
        alert('Error de comunicación con el servidor.');
    }
}
</script>
@endpush
