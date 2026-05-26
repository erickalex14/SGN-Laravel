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
.lista-lineas { display: flex; flex-direction: column; gap: 10px; }
.linea-item { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 10px; align-items: center; }
.btn-mini { background: #f1f5f9; border: 1px solid #cbd5e1; color: #0f172a; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-mini:hover { background: #e2e8f0; }
.hidden { display: none; }
</style>
@endpush

@section('contenido')
<div class="ord-wrap">
    <div class="ord-hdr">
        <h2><i class="bi bi-file-earmark-plus" style="color:#2563eb;"></i> Nueva Orden de Ingreso</h2>
    </div>

    <div id="ord-msg" class="msg-box"></div>

    <form id="form-orden" onsubmit="event.preventDefault(); guardarOrden();">
        @csrf
        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-clipboard-check"></i> Motivo de Ingreso</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Motivo <span class="req">*</span></label>
                        <select id="motivo_ingreso" name="motivo_ingreso" required onchange="actualizarMotivo()">
                            <option value="">-- Seleccione --</option>
                            <option value="Servicio Tecnico">Servicio Tecnico</option>
                            <option value="Servicio Cliente Externo">Servicio Cliente Externo</option>
                            <option value="Validacion de Garantia">Validación de Garantía</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label>Sucursal Cliente</label>
                        <select id="nro_sucursal_cliente" name="nro_sucursal_cliente">
                            <option value="">-- Seleccione --</option>
                            @foreach($sucursalesCliente as $suc)
                                <option value="{{ $suc->numero }}">{{ $suc->numero }} - {{ $suc->nombre }}</option>
                            @endforeach
                            <option value="999">999 - EXTERNO</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-person-badge"></i> Datos del Cliente</div>
            <div class="seccion-body">
                <div class="grid-3">
                    <div class="campo" style="grid-column: span 2;">
                        <label>Cédula / RUC <span class="req">*</span></label>
                        <div style="display:flex; gap:10px;">
                            <input type="text" id="cli_identificacion" name="cli_identificacion" style="flex:1;" maxlength="20" required>
                            <button type="button" class="btn-buscar" onclick="buscarClienteAjax()">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="campo">
                        <label>Teléfono de Contacto <span class="req">*</span></label>
                        <input type="text" id="cli_telefono" name="cli_telefono" maxlength="20" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label>Nombres <span class="req">*</span></label>
                        <input type="text" id="cli_nombres" name="cli_nombres" maxlength="100" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Apellidos <span class="req">*</span></label>
                        <input type="text" id="cli_apellidos" name="cli_apellidos" maxlength="100" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label>Correo Electrónico</label>
                        <input type="email" id="cli_correo" name="cli_correo" maxlength="100">
                    </div>
                    <div class="campo">
                        <label>Dirección</label>
                        <input type="text" id="cli_direccion" name="cli_direccion" maxlength="200" oninput="this.value=this.value.toUpperCase()">
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
                        <select id="eq_tipo" name="eq_tipo" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($tiposDispositivo as $tipo)
                                <option value="{{ $tipo->nombre }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Marca <span class="req">*</span></label>
                        <select id="eq_marca" name="eq_marca" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->nombre }}">{{ $marca->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Modelo <span class="req">*</span></label>
                        <input type="text" id="eq_modelo" name="eq_modelo" required placeholder="Ej: INSPIRON 15" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label>Número de Serie (S/N) <span class="req">*</span></label>
                        <div class="lista-lineas" id="series-container">
                            <div class="linea-item">
                                <input type="text" name="series[]" required oninput="this.value=this.value.toUpperCase()" placeholder="Serie principal">
                                <button type="button" class="btn-mini" onclick="agregarSerie()">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="campo">
                        <label>Contraseña / PIN de acceso</label>
                        <input type="text" id="eq_contrasena" name="eq_contrasena" placeholder="Si aplica...">
                    </div>
                </div>
                <div class="campo">
                    <label>Falla Reportada por el Cliente <span class="req">*</span></label>
                    <textarea id="eq_falla" name="eq_falla" rows="3" required></textarea>
                </div>
                <div class="campo">
                    <label>Observaciones del Estado Físico (Rayones, golpes, etc.)</label>
                    <textarea id="eq_observacion" name="eq_observacion" rows="2"></textarea>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-shield-check"></i> Garantía y Facturación</div>
            <div class="seccion-body">
                <div id="bloque-facturacion" class="grid-3 hidden">
                    <div class="campo">
                        <label>Nro. Factura 1</label>
                        <input type="text" id="nro_factura" name="nro_factura">
                    </div>
                    <div class="campo">
                        <label>Nro. Factura 2</label>
                        <input type="text" id="nro_factura_2" name="nro_factura_2">
                    </div>
                    <div class="campo">
                        <label>Fecha de Facturación</label>
                        <input type="date" id="fecha_facturacion" name="fecha_facturacion">
                    </div>
                </div>
                <div id="bloque-garantia" class="grid-2 hidden">
                    <div class="campo">
                        <label>Tipo de Garantía</label>
                        <select id="garantia_tipo" name="garantia_tipo">
                            <option value="">-- Seleccione --</option>
                            <option value="INTERNA">INTERNA</option>
                            <option value="EXTERNA">EXTERNA</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label>CAS (solo garantía externa)</label>
                        <select id="cas_id" name="cas_id">
                            <option value="">-- Seleccione --</option>
                            @foreach($cas as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->marca }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-person-workspace"></i> Asignación y Servicio</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Técnico Asignado <span class="req">*</span></label>
                        <select id="ord_tecnico_id" name="ord_tecnico_id" required>
                            <option value="">-- Seleccione un Técnico --</option>
                            @foreach($tecnicos as $tec)
                                <option value="{{ $tec->id }}">{{ $tec->nombre_tecnico }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Tipo de Servicio Sugerido</label>
                        <select id="eq_tipo_servicio" name="eq_tipo_servicio">
                            <option value="">-- Seleccione (Opcional) --</option>
                            @foreach($tiposServicio as $ts)
                                <option value="{{ $ts->id }}">{{ $ts->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="bloque-servicio-externo" class="grid-2 hidden">
                    <div class="campo" style="grid-column: span 2;">
                        <label>Tipo de Servicio (Cliente Externo)</label>
                        <input type="text" id="tipo_servicio_texto" name="tipo_servicio_texto" placeholder="Ej: INSTALACION / REVISION" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label>Fecha Prometido <span class="req">*</span></label>
                        <input type="date" id="fecha_prometido" name="fecha_prometido" required>
                    </div>
                    <div class="campo">
                        <label>Estado de Repuesto</label>
                        <select id="estado_repuesto" name="estado_repuesto">
                            <option value="No requerido">No requerido</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Entregado">Entregado</option>
                            <option value="No se encontró">No se encontró</option>
                            <option value="Rechazado">Rechazado</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-gear"></i> Repuestos y Producto</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Repuesto (Inventario)</label>
                        <select id="repuesto_inventario_id" name="repuesto_inventario_id">
                            <option value="">-- Seleccione --</option>
                            @foreach($productosInventario as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->codigo }} - {{ $prod->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Código de Producto Inventario</label>
                        <input type="text" id="producto_inventario_codigo" name="producto_inventario_codigo" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-key"></i> Credenciales del Equipo</div>
            <div class="seccion-body">
                <div class="lista-lineas" id="credenciales-container">
                    <div class="linea-item">
                        <input type="text" name="cred_usuario[]" placeholder="Usuario (opcional)">
                        <input type="text" name="cred_contrasena[]" placeholder="Contraseña / PIN">
                        <input type="hidden" name="cred_es_patron[]" value="0">
                        <button type="button" class="btn-mini" onclick="agregarCredencial()">+</button>
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

function actualizarMotivo() {
    const motivo = document.getElementById('motivo_ingreso').value;
    const bloqueFacturacion = document.getElementById('bloque-facturacion');
    const bloqueGarantia = document.getElementById('bloque-garantia');
    const bloqueServicioExterno = document.getElementById('bloque-servicio-externo');
    const selectSucursal = document.getElementById('nro_sucursal_cliente');
    const tipoServicioSelect = document.getElementById('eq_tipo_servicio');
    const tipoServicioTexto = document.getElementById('tipo_servicio_texto');
    const nroFactura = document.getElementById('nro_factura');
    const fechaFacturacion = document.getElementById('fecha_facturacion');

    const esGarantia = motivo === 'Validacion de Garantia';
    const esExterno = motivo === 'Servicio Cliente Externo';

    bloqueFacturacion.classList.toggle('hidden', !esGarantia);
    bloqueGarantia.classList.toggle('hidden', !esGarantia);
    bloqueServicioExterno.classList.toggle('hidden', !esExterno);

    tipoServicioSelect.disabled = esGarantia || esExterno;
    tipoServicioTexto.required = esExterno;
    nroFactura.required = esGarantia;
    fechaFacturacion.required = esGarantia;

    if (esExterno) {
        selectSucursal.value = '999';
    }
    selectSucursal.disabled = esExterno;
}

function agregarSerie() {
    const container = document.getElementById('series-container');
    const row = document.createElement('div');
    row.className = 'linea-item';
    row.innerHTML = `
        <input type="text" name="series[]" oninput="this.value=this.value.toUpperCase()" placeholder="Serie adicional">
        <button type="button" class="btn-mini" onclick="this.closest('.linea-item').remove()">-</button>
    `;
    container.appendChild(row);
}

function agregarCredencial() {
    const container = document.getElementById('credenciales-container');
    const row = document.createElement('div');
    row.className = 'linea-item';
    row.innerHTML = `
        <input type="text" name="cred_usuario[]" placeholder="Usuario (opcional)">
        <input type="text" name="cred_contrasena[]" placeholder="Contraseña / PIN">
        <input type="hidden" name="cred_es_patron[]" value="0">
        <button type="button" class="btn-mini" onclick="this.closest('.linea-item').remove()">-</button>
    `;
    container.appendChild(row);
}

async function guardarOrden() {
    const form = document.getElementById('form-orden');
    const fd = new FormData(form);

    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    try {
        const r = await fetch('{{ route("ordenes.store") }}', { method:'POST', body:fd });
        const d = await r.json();
        
        if(d.ok) {
            mostrarMensaje(false, `<strong>¡Éxito!</strong> ${d.mensaje} <br><br> <a href="/operaciones/ordenes/${d.orden_id}/imprimir" target="_blank" style="color:#166534; text-decoration:underline;">Imprimir Comprobante</a>`);
            document.getElementById('form-orden').reset();
            actualizarMotivo();
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

document.addEventListener('DOMContentLoaded', () => {
    actualizarMotivo();
});
</script>
@endpush