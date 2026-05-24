@extends('layouts.app')
@section('titulo', 'Crear Orden de Servicio')

@push('css_adicional')
<style>
/* CSS Integro de ordenes.css */
.ord-wrap { max-width: 1200px; margin: 0 auto; padding: 20px; }
.ord-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
.ord-hdr h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.seccion-form { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
.seccion-hdr { background: #f8fafc; padding: 14px 20px; border-bottom: 1.5px solid #e2e8f0; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; font-size: 15px; }
.seccion-body { padding: 24px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
.campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.campo label { font-size: 13px; font-weight: 600; color: #475569; }
.campo input, .campo select, .campo textarea { padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; background: #fff; transition: border-color .2s; }
.campo input:focus, .campo select:focus, .campo textarea:focus { outline: none; border-color: #2563eb; }
.req { color: #ef4444; }
.btn-buscar { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 11px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background .2s; }
.btn-buscar:hover { background: #dbeafe; }
.btn-submit { background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 14px 28px; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; transition: opacity .2s; }
.btn-submit:hover { opacity: .9; }
.btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
.msg-box { display: none; padding: 16px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 24px; }
.msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.msg-box.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
</style>
@endpush

@section('contenido')
<div class="ord-wrap">
    <div class="ord-hdr">
        <h2><i class="bi bi-file-earmark-plus" style="color:#2563eb;"></i> Nueva Orden de Ingreso</h2>
    </div>

    <div id="ord-msg" class="msg-box"></div>

    <form id="form-orden" onsubmit="event.preventDefault(); guardarOrden();">
        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-person-badge"></i> Datos del Cliente</div>
            <div class="seccion-body">
                <div class="grid-3">
                    <div class="campo" style="grid-column: span 2;">
                        <label>Cédula / RUC <span class="req">*</span></label>
                        <div style="display:flex; gap:10px;">
                            <input type="text" id="cli_identificacion" style="flex:1;" maxlength="20" required>
                            <button type="button" class="btn-buscar" onclick="buscarClienteAjax()">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="campo">
                        <label>Teléfono de Contacto <span class="req">*</span></label>
                        <input type="text" id="cli_telefono" maxlength="20" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label>Nombres <span class="req">*</span></label>
                        <input type="text" id="cli_nombres" maxlength="100" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Apellidos <span class="req">*</span></label>
                        <input type="text" id="cli_apellidos" maxlength="100" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label>Correo Electrónico</label>
                        <input type="email" id="cli_correo" maxlength="100">
                    </div>
                    <div class="campo">
                        <label>Dirección</label>
                        <input type="text" id="cli_direccion" maxlength="200" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-laptop"></i> Datos del Equipo</div>
            <div class="seccion-body">
                <div class="grid-3">
                    <div class="campo">
                        <label>Tipo de Equipo <span class="req">*</span></label>
                        <input type="text" id="eq_tipo" required placeholder="Ej: LAPTOP" oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Marca <span class="req">*</span></label>
                        <input type="text" id="eq_marca" required placeholder="Ej: DELL" oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Modelo <span class="req">*</span></label>
                        <input type="text" id="eq_modelo" required placeholder="Ej: INSPIRON 15" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label>Número de Serie (S/N) <span class="req">*</span></label>
                        <input type="text" id="eq_serie" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Contraseña / PIN de acceso</label>
                        <input type="text" id="eq_contrasena" placeholder="Si aplica...">
                    </div>
                </div>
                <div class="campo">
                    <label>Falla Reportada por el Cliente <span class="req">*</span></label>
                    <textarea id="eq_falla" rows="3" required></textarea>
                </div>
                <div class="campo">
                    <label>Observaciones del Estado Físico (Rayones, golpes, etc.)</label>
                    <textarea id="eq_observacion" rows="2"></textarea>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-person-workspace"></i> Asignación y Servicio</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Técnico Asignado <span class="req">*</span></label>
                        <select id="ord_tecnico_id" required>
                            <option value="">-- Seleccione un Técnico --</option>
                            @foreach($tecnicos as $tec)
                                <option value="{{ $tec->id }}">{{ $tec->nombre_tecnico }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Tipo de Servicio Sugerido</label>
                        <select id="eq_tipo_servicio">
                            <option value="">-- Seleccione (Opcional) --</option>
                            @foreach($tiposServicio as $ts)
                                <option value="{{ $ts->id }}">{{ $ts->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" id="btn-guardar" class="btn-submit">
            <i class="bi bi-floppy"></i> Generar Orden de Ingreso
        </button>
    </form>
</div>
@endsection

@push('js_adicional')
<script>
function mostrarMensaje(isError, texto) {
    const box = document.getElementById('ord-msg');
    box.className = 'msg-box ' + (isError ? 'err' : 'ok');
    box.innerHTML = texto;
    box.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function buscarClienteAjax() {
    const iden = document.getElementById('cli_identificacion').value.trim();
    if(!iden) { alert('Ingrese una identificación válida para buscar.'); return; }

    try {
        const r = await fetch('{{ url("/operaciones/ordenes/buscar-cliente") }}?identificacion=' + iden);
        const d = await r.json();
        
        if(d.ok && d.cliente) {
            document.getElementById('cli_nombres').value = d.cliente.nombres;
            document.getElementById('cli_apellidos').value = d.cliente.apellidos;
            document.getElementById('cli_telefono').value = d.cliente.numero_contacto;
            document.getElementById('cli_correo').value = d.cliente.correo || '';
            document.getElementById('cli_direccion').value = d.cliente.direccion_clientes || '';
            alert('Cliente encontrado y datos cargados.');
        } else {
            alert('Cliente no encontrado. Por favor, registre los datos manualmente.');
            document.getElementById('cli_nombres').focus();
        }
    } catch(e) { alert('Error al buscar cliente.'); }
}

async function guardarOrden() {
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    
    // Cliente
    fd.append('cli_identificacion', document.getElementById('cli_identificacion').value.trim());
    fd.append('cli_nombres', document.getElementById('cli_nombres').value.trim());
    fd.append('cli_apellidos', document.getElementById('cli_apellidos').value.trim());
    fd.append('cli_telefono', document.getElementById('cli_telefono').value.trim());
    fd.append('cli_correo', document.getElementById('cli_correo').value.trim());
    fd.append('cli_direccion', document.getElementById('cli_direccion').value.trim());

    // Equipo
    fd.append('eq_tipo', document.getElementById('eq_tipo').value.trim());
    fd.append('eq_marca', document.getElementById('eq_marca').value.trim());
    fd.append('eq_modelo', document.getElementById('eq_modelo').value.trim());
    fd.append('eq_serie', document.getElementById('eq_serie').value.trim());
    fd.append('eq_contrasena', document.getElementById('eq_contrasena').value.trim());
    fd.append('eq_falla', document.getElementById('eq_falla').value.trim());
    fd.append('eq_observacion', document.getElementById('eq_observacion').value.trim());
    fd.append('eq_tipo_servicio', document.getElementById('eq_tipo_servicio').value);

    // Orden
    fd.append('ord_tecnico_id', document.getElementById('ord_tecnico_id').value);

    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    try {
        const r = await fetch('{{ route("ordenes.store") }}', { method:'POST', body:fd });
        const d = await r.json();
        
        if(d.ok) {
            mostrarMensaje(false, `<strong>¡Éxito!</strong> ${d.mensaje} <br><br> <a href="/operaciones/ordenes/${d.orden_id}/imprimir" target="_blank" style="color:#166534; text-decoration:underline;">Imprimir Comprobante</a>`);
            document.getElementById('form-orden').reset();
        } else {
            mostrarMensaje(true, d.error);
        }
    } catch(e) {
        mostrarMensaje(true, 'Ocurrió un error crítico de conexión.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy"></i> Generar Orden de Ingreso';
    }
}
</script>
@endpush