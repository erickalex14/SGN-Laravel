@extends('layouts.app')
@section('titulo', 'Editar Orden: ' . $orden->nro_orden)

@push('css_adicional')
<style>
/* CSS Integro de editar.css */
.eo-wrap { max-width: 1200px; margin: 0 auto; padding: 20px; }
.eo-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
.eo-hdr h2 { margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.ord-badge { background: #1e293b; color: #fff; padding: 4px 10px; border-radius: 6px; font-family: monospace; font-size: 16px; font-weight: 700; letter-spacing: 1px; }
.info-panel { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.info-item { display: flex; flex-direction: column; gap: 4px; }
.info-item label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.info-item span { font-size: 14px; font-weight: 600; color: #1e293b; }
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
</style>
@endpush

@section('contenido')
<div class="eo-wrap">
    <div class="eo-hdr">
        <h2>
            <i class="bi bi-pencil-square" style="color:#2563eb;"></i> 
            Gestión y Edición de Orden
            <span class="ord-badge">{{ $orden->nro_orden }}</span>
        </h2>
    </div>

    <div class="info-panel">
        <div class="info-item">
            <label>Cliente</label>
            <span>{{ $orden->cliente->nombres }} {{ $orden->cliente->apellidos }}</span>
        </div>
        <div class="info-item">
            <label>Identificación</label>
            <span>{{ $orden->cliente->identificacion }}</span>
        </div>
        <div class="info-item">
            <label>Equipo / Modelo</label>
            <span>{{ $orden->equipo->marca }} {{ $orden->equipo->modelo }}</span>
        </div>
        <div class="info-item">
            <label>Serie</label>
            <span>{{ $orden->equipo->serie }}</span>
        </div>
        <div class="info-item">
            <label>Fecha Ingreso</label>
            <span>{{ \Carbon\Carbon::parse($orden->fecha_de_ingreso)->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <div id="eo-msg" class="msg-box"></div>

    <form id="form-edicion" onsubmit="event.preventDefault(); guardarActualizacion();">
        <input type="hidden" id="orden_id" value="{{ $orden->id }}">
        <input type="hidden" id="equipo_id" value="{{ $orden->equipo_id }}">

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-activity"></i> Diagnóstico y Estado</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Estado Actual de la Orden <span class="req">*</span></label>
                        <select id="estado_orden" required>
                            <option value="Pendiente" {{ in_array($orden->estado_orden, ['Pendiente', 'INGRESO']) ? 'selected' : '' }}>Pendiente</option>
                            <option value="En proceso" {{ in_array($orden->estado_orden, ['En proceso', 'REVISIÓN', 'REVISION', 'ESPERA REPUESTO']) ? 'selected' : '' }}>En proceso</option>
                            <option value="Finalizada" {{ in_array($orden->estado_orden, ['Finalizada', 'REPARADO']) ? 'selected' : '' }}>Finalizada</option>
                            <option value="Entregada" {{ in_array($orden->estado_orden, ['Entregada', 'ENTREGADO']) ? 'selected' : '' }}>Entregada</option>
                            <option value="Nota de Credito" {{ $orden->estado_orden === 'Nota de Credito' ? 'selected' : '' }}>Nota de Credito</option>
                            <option value="Devuelto sin reparar" {{ in_array($orden->estado_orden, ['Devuelto sin reparar', 'DEVUELTO SIN REPARAR']) ? 'selected' : '' }}>Devuelto sin reparar</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label>Fecha Prometida de Entrega</label>
                        <input type="date" id="fecha_prometido" value="{{ $orden->fecha_prometido ? \Carbon\Carbon::parse($orden->fecha_prometido)->format('Y-m-d') : '' }}">
                    </div>
                </div>

                <div class="campo">
                    <label>Falla Reportada / Diagnóstico Técnico <span class="req">*</span></label>
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
                                <option value="{{ $ts->id }}" {{ $orden->equipo->tipo_servicio_id == $ts->id ? 'selected' : '' }}>
                                    {{ $ts->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Catálogo de Precio Estándar Sugerido</label>
                        <select id="valor_estandar_id">
                            <option value="">-- Sin Precio Asociado --</option>
                            @foreach($precios as $p)
                                <option value="{{ $p->id }}" {{ $orden->valor_estandar_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->servicio }} - ${{ number_format($p->precio, 2) }}
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
                            <option value="{{ $prod->id }}" {{ $orden->repuesto_inventario_id == $prod->id ? 'selected' : '' }}>
                                [{{ $prod->codigo }}] {{ $prod->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" id="btn-actualizar" class="btn-submit">
            <i class="bi bi-floppy"></i> Guardar Actualización de Orden
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
    
    // IDs
    fd.append('orden_id', document.getElementById('orden_id').value);
    fd.append('equipo_id', document.getElementById('equipo_id').value);
    
    // Estado y Fechas
    fd.append('estado_orden', document.getElementById('estado_orden').value);
    fd.append('fecha_prometido', document.getElementById('fecha_prometido').value);
    
    // Diagnostico
    fd.append('eq_falla', document.getElementById('eq_falla').value.trim());
    fd.append('eq_observacion', document.getElementById('eq_observacion').value.trim());
    
    // Servicios e Inventario
    fd.append('tipo_servicio_id', document.getElementById('tipo_servicio_id').value);
    fd.append('valor_estandar_id', document.getElementById('valor_estandar_id').value);
    fd.append('repuesto_inventario_id', document.getElementById('repuesto_inventario_id').value);

    const btn = document.getElementById('btn-actualizar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando actualización...';

    try {
        const r = await fetch('{{ route("ordenes.update") }}', { method:'POST', body:fd });
        const d = await r.json();
        
        if(d.ok) {
            mostrarMensaje(false, `<strong>¡Éxito!</strong> ${d.mensaje}`);
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarMensaje(true, d.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualización de Orden';
        }
    } catch(e) {
        mostrarMensaje(true, 'Se ha perdido la conexión con el servidor. Intente nuevamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualización de Orden';
    }
}
</script>
@endpush