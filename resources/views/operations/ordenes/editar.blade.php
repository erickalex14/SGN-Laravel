@extends('layouts.app')
@section('titulo', 'Editar Orden: ' . $orden->nro_orden)

@push('css_adicional')
<style>
.eo-wrap { max-width: 1220px; margin: 0 auto; padding: 20px; }
.eo-hdr { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; flex-wrap: wrap; }
.eo-hdr h2 { margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.ord-badge { background: #1e293b; color: #fff; padding: 4px 10px; border-radius: 6px; font-family: monospace; font-size: 16px; font-weight: 700; letter-spacing: .8px; }
.eo-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.eo-btn-link { display: inline-flex; align-items: center; gap: 6px; text-decoration: none; border-radius: 8px; padding: 10px 13px; font-size: 13px; font-weight: 700; border: 1px solid transparent; }
.eo-btn-link.back { background: #f8fafc; color: #334155; border-color: #e2e8f0; }
.eo-btn-link.print { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }

.eo-overview { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,.03); overflow: hidden; }
.eo-overview-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; flex-wrap: wrap; }
.eo-overview-head strong { font-size: 14px; color: #0f172a; }
.eo-chips { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.eo-chip { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; border: 1px solid transparent; }
.eo-chip.orden-pend { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
.eo-chip.orden-proc { background: #dbeafe; color: #1e40af; border-color: #bfdbfe; }
.eo-chip.orden-fin { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.eo-chip.orden-ent { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
.eo-chip.rep-ok { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.eo-chip.rep-req { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.eo-chip.gar-ok { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.eo-chip.gar-wait { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.eo-chip.gar-no { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.eo-meta-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; padding: 14px 16px 16px; }
.eo-meta-item { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; padding: 10px; min-height: 68px; }
.eo-meta-item.full { grid-column: span 2; }
.eo-meta-item label { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .35px; color: #64748b; font-weight: 700; margin-bottom: 4px; }
.eo-meta-item span { font-size: 13px; color: #0f172a; font-weight: 600; word-break: break-word; }

.seccion-form { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
.seccion-hdr { background: #f1f5f9; padding: 14px 20px; border-bottom: 1.5px solid #e2e8f0; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; font-size: 15px; }
.seccion-body { padding: 24px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.campo label { font-size: 13px; font-weight: 600; color: #475569; }
.campo input, .campo select, .campo textarea { padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; background: #fff; transition: border-color .2s; }
.campo input:focus, .campo select:focus, .campo textarea:focus { outline: none; border-color: #2563eb; }
.req { color: #ef4444; }

.btn-submit { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 14px 28px; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; transition: opacity .2s; }
.btn-submit:hover { opacity: .9; }
.btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
.msg-box { display: none; padding: 16px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 24px; }
.msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.msg-box.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

@media (max-width: 980px) {
  .eo-meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 720px) {
  .grid-2 { grid-template-columns: 1fr; }
  .eo-meta-grid { grid-template-columns: 1fr; }
  .eo-meta-item.full { grid-column: auto; }
}
</style>
@endpush

@section('contenido')
@php
    $estadoOrden = trim((string) ($orden->estado_orden ?? ''));
    $estadoRep = trim((string) ($orden->estado_repuesto ?? ''));
    $estadoGar = trim((string) ($orden->estado_garantia ?? ''));

    $chipOrden = match (true) {
        in_array($estadoOrden, ['Pendiente', 'INGRESO'], true) => 'orden-pend',
        in_array($estadoOrden, ['En proceso', 'EN PROCESO', 'REVISION', 'REVISIÓN', 'ESPERA REPUESTO'], true) => 'orden-proc',
        in_array($estadoOrden, ['Finalizada', 'REPARADO'], true) => 'orden-fin',
        in_array($estadoOrden, ['Entregada', 'ENTREGADO'], true) => 'orden-ent',
        default => 'orden-ent',
    };

    $chipRep = in_array($estadoRep, ['Requerido', 'Solicitado', 'Pendiente'], true) ? 'rep-req' : 'rep-ok';
    $chipGar = in_array($estadoGar, ['Aceptada', 'Aprobada'], true)
        ? 'gar-ok'
        : (in_array($estadoGar, ['Negada', 'Rechazada'], true) ? 'gar-no' : 'gar-wait');

    $nombreCliente = trim(((string) ($orden->cliente->nombres ?? '')) . ' ' . ((string) ($orden->cliente->apellidos ?? '')));
    $nombreTecnico = $orden->tecnico->nombre_tecnico ?? '-';
    $usuarioIngreso = $orden->usuarioIngreso->usuario ?? ($orden->usuarioIngreso->nombre_tecnico ?? '-');
    $usuarioMod = $orden->usuarioModificacion->usuario ?? ($orden->usuarioModificacion->nombre_tecnico ?? '-');
    $casNombre = $orden->cas->nombre ?? '-';

    $fmt = static fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y H:i') : '-';
@endphp

<div class="eo-wrap">
    <div class="eo-hdr">
        <h2>
            <i class="bi bi-pencil-square" style="color:#2563eb;"></i>
            Gestion y Edicion de Orden
            <span class="ord-badge">{{ $orden->nro_orden }}</span>
        </h2>
        <div class="eo-actions">
            <a href="{{ route('mis_ordenes.index') }}" class="eo-btn-link back">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <a target="_blank" href="{{ route('ordenes.imprimir', ['id' => $orden->id]) }}" class="eo-btn-link print">
                <i class="bi bi-printer"></i> Imprimir OT
            </a>
        </div>
    </div>

    <div class="eo-overview">
        <div class="eo-overview-head">
            <strong>Resumen completo de la orden</strong>
            <div class="eo-chips">
                <span class="eo-chip {{ $chipOrden }}">Estado: {{ $estadoOrden ?: '-' }}</span>
                <span class="eo-chip {{ $chipRep }}">Repuesto: {{ $estadoRep ?: '-' }}</span>
                <span class="eo-chip {{ $chipGar }}">Garantia: {{ $estadoGar ?: '-' }}</span>
            </div>
        </div>
        <div class="eo-meta-grid">
            <div class="eo-meta-item"><label>Cliente</label><span>{{ $nombreCliente ?: '-' }}</span></div>
            <div class="eo-meta-item"><label>Identificacion</label><span>{{ $orden->cliente->identificacion ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Contacto</label><span>{{ $orden->cliente->numero_contacto ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Correo</label><span>{{ $orden->cliente->correo ?? '-' }}</span></div>

            <div class="eo-meta-item full"><label>Direccion</label><span>{{ $orden->cliente->direccion_clientes ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Sucursal Cliente</label><span>{{ $orden->nro_sucursal_cliente ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Sucursal Interna</label><span>{{ $orden->sucursal->nombre ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Tecnico</label><span>{{ $nombreTecnico }}</span></div>

            <div class="eo-meta-item"><label>Motivo de Ingreso</label><span>{{ $orden->motivo_ingreso ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Garantia Tipo</label><span>{{ $orden->garantia_tipo ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Factura 1</label><span>{{ $orden->nro_factura ?: '-' }}</span></div>
            <div class="eo-meta-item"><label>Factura 2</label><span>{{ $orden->nro_factura_2 ?: '-' }}</span></div>

            <div class="eo-meta-item"><label>Equipo</label><span>{{ trim(($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')) ?: '-' }}</span></div>
            <div class="eo-meta-item"><label>Serie</label><span>{{ $orden->equipo->serie ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Contrasena Equipo</label><span>{{ $orden->equipo->contrasena_equipo ?: '-' }}</span></div>
            <div class="eo-meta-item"><label>Repuesto Inventario</label><span>{{ $orden->repuestoInventario->descripcion ?? '-' }}</span></div>

            <div class="eo-meta-item full"><label>Falla Reportada</label><span>{{ $orden->equipo->falla ?? '-' }}</span></div>
            <div class="eo-meta-item full"><label>Observacion Equipo</label><span>{{ $orden->equipo->observacion ?? '-' }}</span></div>

            <div class="eo-meta-item"><label>CAS</label><span>{{ $casNombre }}</span></div>
            <div class="eo-meta-item"><label>Caso CAS</label><span>{{ $orden->cas_numero_caso ?: '-' }}</span></div>
            <div class="eo-meta-item"><label>CAS Envio</label><span>{{ $fmt($orden->cas_fecha_envio) }}</span></div>
            <div class="eo-meta-item"><label>CAS Retorno</label><span>{{ $fmt($orden->cas_fecha_retorno) }}</span></div>

            <div class="eo-meta-item"><label>Fecha Ingreso</label><span>{{ $fmt($orden->fecha_de_ingreso) }}</span></div>
            <div class="eo-meta-item"><label>Fecha Prometida</label><span>{{ $fmt($orden->fecha_prometido) }}</span></div>
            <div class="eo-meta-item"><label>Fecha Finalizacion</label><span>{{ $fmt($orden->fecha_finalizacion) }}</span></div>
            <div class="eo-meta-item"><label>Fecha Entrega</label><span>{{ $fmt($orden->fecha_entrega) }}</span></div>

            <div class="eo-meta-item"><label>Ingresado por</label><span>{{ $usuarioIngreso }}</span></div>
            <div class="eo-meta-item"><label>Ultima Modificacion</label><span>{{ $usuarioMod }}</span></div>
            <div class="eo-meta-item full"><label>Observacion Orden</label><span>{{ $orden->observacion ?: '-' }}</span></div>
        </div>
    </div>

    <div id="eo-msg" class="msg-box"></div>

    <form id="form-edicion" onsubmit="event.preventDefault(); guardarActualizacion();">
        <input type="hidden" id="orden_id" value="{{ $orden->id }}">
        <input type="hidden" id="equipo_id" value="{{ $orden->equipo_id }}">

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-activity"></i> Diagnostico y Estado</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Estado Actual de la Orden <span class="req">*</span></label>
                        <select id="estado_orden" required>
                            <option value="Pendiente" {{ in_array($orden->estado_orden, ['Pendiente', 'INGRESO'], true) ? 'selected' : '' }}>Pendiente</option>
                            <option value="En proceso" {{ in_array($orden->estado_orden, ['En proceso', 'REVISION', 'REVISIÓN', 'ESPERA REPUESTO', 'EN PROCESO'], true) ? 'selected' : '' }}>En proceso</option>
                            <option value="Finalizada" {{ in_array($orden->estado_orden, ['Finalizada', 'REPARADO'], true) ? 'selected' : '' }}>Finalizada</option>
                            <option value="Entregada" {{ in_array($orden->estado_orden, ['Entregada', 'ENTREGADO'], true) ? 'selected' : '' }}>Entregada</option>
                            <option value="Nota de Credito" {{ $orden->estado_orden === 'Nota de Credito' ? 'selected' : '' }}>Nota de Credito</option>
                            <option value="Devuelto sin reparar" {{ in_array($orden->estado_orden, ['Devuelto sin reparar', 'DEVUELTO SIN REPARAR'], true) ? 'selected' : '' }}>Devuelto sin reparar</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label>Fecha Prometida de Entrega</label>
                        <input type="date" id="fecha_prometido" value="{{ $orden->fecha_prometido ? \Carbon\Carbon::parse($orden->fecha_prometido)->format('Y-m-d') : '' }}">
                    </div>
                    @if($orden->motivo_ingreso === 'Validacion de Garantia')
                        <div class="campo" id="bloque-cas">
                            <label>Asignar CAS <span style="font-size:11px;font-weight:400;color:#64748b;">(Opcional)</span></label>
                            <select id="cas_id" name="cas_id">
                                <option value="">-- Seleccione CAS --</option>
                                @foreach($cas as $c)
                                    <option value="{{ $c->id }}" {{ $orden->cas_id == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="campo">
                    <label>Falla Reportada / Diagnostico Tecnico <span class="req">*</span></label>
                    <textarea id="eq_falla" rows="3" required>{{ $orden->equipo->falla }}</textarea>
                </div>
                <div class="campo">
                    <label>Observaciones Adicionales del Equipo</label>
                    <textarea id="eq_observacion" rows="2">{{ $orden->equipo->observacion }}</textarea>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-currency-dollar"></i> Servicios y Repuestos Aplicados</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Tipo de Servicio Aplicado</label>
                        <select id="tipo_servicio_id">
                            <option value="">-- No Especificado --</option>
                            @foreach($tiposServicio as $ts)
                                <option value="{{ $ts->id }}" {{ (int) $orden->equipo->tipo_servicio_id === (int) $ts->id ? 'selected' : '' }}>
                                    {{ $ts->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Catalogo de Precio Estandar Sugerido</label>
                        <select id="valor_estandar_id">
                            <option value="">-- Sin Precio Asociado --</option>
                            @foreach($precios as $p)
                                <option value="{{ $p->id }}" {{ (int) $orden->valor_estandar_id === (int) $p->id ? 'selected' : '' }}>
                                    {{ $p->servicio }} - ${{ number_format((float) $p->precio, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="campo">
                    <label>Repuesto de Inventario Asociado</label>
                    <select id="repuesto_inventario_id">
                        <option value="">-- Sin Repuesto Asociado --</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}" {{ (int) $orden->repuesto_inventario_id === (int) $prod->id ? 'selected' : '' }}>
                                [{{ $prod->codigo }}] {{ $prod->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" id="btn-actualizar" class="btn-submit">
            <i class="bi bi-floppy"></i> Guardar Actualizacion de Orden
        </button>
    </form>
</div>
@endsection

@push('js_adicional')
<script>
function mostrarMensaje(isError, texto) {
    const box = document.getElementById('eo-msg');
    box.className = 'msg-box ' + (isError ? 'err' : 'ok');
    box.innerHTML = texto;
    box.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function guardarActualizacion() {
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');

    fd.append('orden_id', document.getElementById('orden_id').value);
    fd.append('equipo_id', document.getElementById('equipo_id').value);

    fd.append('estado_orden', document.getElementById('estado_orden').value);
    fd.append('fecha_prometido', document.getElementById('fecha_prometido').value);

    fd.append('eq_falla', document.getElementById('eq_falla').value.trim());
    fd.append('eq_observacion', document.getElementById('eq_observacion').value.trim());

    fd.append('tipo_servicio_id', document.getElementById('tipo_servicio_id').value);
    fd.append('valor_estandar_id', document.getElementById('valor_estandar_id').value);
    fd.append('repuesto_inventario_id', document.getElementById('repuesto_inventario_id').value);

    @if($orden->motivo_ingreso === 'Validacion de Garantia')
        fd.append('cas_id', document.getElementById('cas_id').value);
    @endif

    const btn = document.getElementById('btn-actualizar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando actualizacion...';

    try {
        const r = await fetch('{{ route("ordenes.update") }}', { method: 'POST', body: fd });
        const d = await r.json();

        if (d.ok) {
            mostrarMensaje(false, `<strong>Exito:</strong> ${d.mensaje}`);
            setTimeout(() => location.reload(), 1300);
        } else {
            mostrarMensaje(true, d.error || 'No se pudo actualizar la orden.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualizacion de Orden';
        }
    } catch (e) {
        mostrarMensaje(true, 'Se perdio la conexion con el servidor. Intente nuevamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualizacion de Orden';
    }
}
</script>
@endpush
