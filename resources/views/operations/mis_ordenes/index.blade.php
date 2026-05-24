@extends('layouts.app')
@section('titulo', 'Mis Órdenes Asignadas')

@push('css_adicional')
<style>
/* Estructura CSS extraida de mis-ordenes.css */
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
</style>
@endpush

@section('contenido')
<div class="mo-container">
    <div class="mo-hdr">
        <h2>Listado de Órdenes Asignadas</h2>
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
                        <th style="width:180px; text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $ord)
                        @php
                            $claseEstado = match($ord->estado_orden) {
                                'INGRESO' => 'estado-ingreso',
                                'REVISIÓN' => 'estado-revision',
                                'REPARADO' => 'estado-reparado',
                                'ENTREGADO' => 'estado-entregado',
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
                                    <option value="INGRESO">INGRESO</option>
                                    <option value="REVISIÓN">REVISIÓN</option>
                                    <option value="REPARADO">REPARADO</option>
                                    <option value="ESPERA REPUESTO">ESPERA REPUESTO</option>
                                    <option value="DEVUELTO SIN REPARAR">DEVUELTO SIN REPARAR</option>
                                </select>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ url('/operaciones/ordenes/editar/'.$ord->id) }}" class="btn-accion">
                                    Detalles / Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="mo-empty">Actualmente no posee órdenes asignadas en el sistema.</div>
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

    if (!confirm(`¿Confirma la actualización de la orden ${nroOrden} a estado: ${nuevoEstado}?`)) {
        location.reload(); 
        return;
    }

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('id', ordenId);
    fd.append('estado', nuevoEstado);

    try {
        const r = await fetch('{{ route("mis_ordenes.estado") }}', {
            method: 'POST',
            body: fd
        });
        const d = await r.json();

        if (d.ok) {
            location.reload();
        } else {
            alert('Error en el proceso de actualización: ' + d.error);
            location.reload();
        }
    } catch (e) {
        alert('Se detectó un error de comunicación con el servidor.');
        location.reload();
    }
}
</script>
@endpush