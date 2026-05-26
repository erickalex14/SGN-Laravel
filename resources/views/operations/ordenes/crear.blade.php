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
.preord-alert { display: none; margin: 0 0 16px 0; padding: 14px 18px; border-radius: 10px; background: #fffbeb; border: 1.5px solid #fde68a; color: #78350f; }
.preord-title { font-weight: 800; font-size: 13px; color: #92400e; margin-bottom: 5px; }
.rep-stock-wrap { display: none; margin-top: 10px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 9px; padding: 12px 14px; }
.rep-stock-head { font-weight: 700; font-size: 13px; color: #166534; margin-bottom: 8px; }
.rep-resultados { display: none; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.rep-item { padding: 9px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: grid; grid-template-columns: auto 1fr auto; gap: 10px; align-items: center; }
.rep-item:hover { background: #fffbeb; }
.rep-badge { display: none; margin-top: 8px; align-items: center; gap: 8px; background: #dcfce7; border: 1px solid #86efac; border-radius: 7px; padding: 7px 12px; }
.rep-badge-txt { font-size: 13px; color: #166534; font-weight: 700; flex: 1; }
.tec-native-sr { position: absolute !important; left: -9999px !important; width: 1px !important; height: 1px !important; opacity: 0 !important; pointer-events: none !important; }
.tec-dropdown { position: relative; width: 100%; }
.tec-trigger { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; cursor: pointer; background: #fff; user-select: none; transition: border-color .15s; }
.tec-trigger:hover { border-color: #93c5fd; }
.tec-trigger.open { border-color: #2563eb; border-radius: 8px 8px 0 0; }
.tec-trigger-avatar { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 12px; flex-shrink: 0; background: #94a3b8; }
.tec-trigger-info { flex: 1; min-width: 0; }
.tec-trigger-nombre { font-weight: 600; font-size: 13px; color: #0f172a; }
.tec-trigger-stats { font-size: 11px; color: #94a3b8; }
.tec-trigger-arrow { color: #94a3b8; font-size: 13px; transition: transform .2s; }
.tec-trigger.open .tec-trigger-arrow { transform: rotate(180deg); }
.tec-dropdown-list { display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1.5px solid #2563eb; border-top: none; border-radius: 0 0 8px 8px; max-height: 320px; overflow-y: auto; z-index: 200; box-shadow: 0 8px 24px rgba(0,0,0,.15); }
.tec-dropdown-list.open { display: block; }
.tec-item { display: flex; align-items: center; gap: 9px; padding: 8px 12px; cursor: pointer; transition: background .12s; border-bottom: 1px solid #f1f5f9; }
.tec-item:last-child { border-bottom: none; }
.tec-item:hover { background: #f0f7ff; }
.tec-item.selected { background: #eff6ff; }
.tec-item-avatar { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 11px; flex-shrink: 0; }
.tec-item-nombre { flex: 1; font-size: 12.5px; font-weight: 600; color: #0f172a; }
.tec-item-stats { font-size: 11px; color: #94a3b8; white-space: nowrap; }
.tec-item-badge { font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 20px; flex-shrink: 0; margin-left: 6px; }
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
        <div id="preorden-aviso" class="preord-alert">
            <div class="preord-title"><i class="bi bi-exclamation-triangle-fill"></i> Coincidencia con preorden pendiente</div>
            <div id="preorden-aviso-detalle" style="font-size:13px; line-height:1.6;"></div>
            <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="button" onclick="irAPreordenes()"
                        style="background:#f59e0b;color:#fff;border:none;border-radius:8px;padding:7px 16px;font-size:12.5px;font-weight:700;cursor:pointer;">
                    <i class="bi bi-arrow-right-circle"></i> Ir a Preórdenes
                </button>
                <button type="button" onclick="ignorarPreorden()"
                        style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:8px;padding:7px 16px;font-size:12.5px;font-weight:600;cursor:pointer;">
                    Continuar de todos modos
                </button>
            </div>
        </div>

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
                            <option value="propia">INTERNA</option>
                            <option value="externa">EXTERNA</option>
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
                        <label>Técnico Asignado <span class="req">*</span> <span style="font-size:11px;font-weight:400;color:#94a3b8;">ordenado por menor carga</span></label>
                        <select id="ord_tecnico_id" name="ord_tecnico_id" required class="tec-native-sr">
                            <option value="">-- Seleccione un Técnico --</option>
                            @foreach($tecnicos as $tec)
                                @php
                                    $pendientes = (int) ($tec->pendientes ?? 0);
                                    $enProceso = (int) ($tec->en_proceso ?? 0);
                                @endphp
                                <option value="{{ $tec->id }}" data-pend="{{ $pendientes }}" data-proc="{{ $enProceso }}">
                                    {{ $tec->nombre_tecnico }}
                                </option>
                            @endforeach
                        </select>
                        <div class="tec-dropdown" id="tec-dropdown">
                            <div class="tec-trigger" id="tec-trigger" onclick="toggleTecDropdown()">
                                <div class="tec-trigger-avatar" id="tec-trigger-avatar">?</div>
                                <div class="tec-trigger-info">
                                    <div class="tec-trigger-nombre" id="tec-trigger-nombre">-- Seleccionar técnico --</div>
                                    <div class="tec-trigger-stats" id="tec-trigger-stats"></div>
                                </div>
                                <i class="bi bi-chevron-down tec-trigger-arrow"></i>
                            </div>
                            <div class="tec-dropdown-list" id="tec-dropdown-list">
                                @php
                                    $maxCarga = 0;
                                    foreach ($tecnicos as $t) {
                                        $maxCarga = max($maxCarga, (int)($t->pendientes ?? 0) + (int)($t->en_proceso ?? 0));
                                    }
                                    $umbralRojo = max(2, (int) ceil($maxCarga * 0.7));
                                @endphp
                                @foreach($tecnicos as $tec)
                                    @php
                                        $pendientes = (int) ($tec->pendientes ?? 0);
                                        $enProceso = (int) ($tec->en_proceso ?? 0);
                                        $total = $pendientes + $enProceso;
                                        if ($total === 0) {
                                            $color = '#10b981';
                                            $etiqueta = 'Libre';
                                        } elseif ($total <= $umbralRojo) {
                                            $color = '#f59e0b';
                                            $etiqueta = 'Normal';
                                        } else {
                                            $color = '#ef4444';
                                            $etiqueta = 'Cargado';
                                        }
                                    @endphp
                                    <div class="tec-item"
                                         data-tec-id="{{ $tec->id }}"
                                         onclick="seleccionarTecnico(this, {{ $tec->id }}, '{{ addslashes($tec->nombre_tecnico) }}', '{{ $color }}', '{{ $etiqueta }}', {{ $pendientes }}, {{ $enProceso }})">
                                        <div class="tec-item-avatar" style="background:{{ $color }};">{{ strtoupper(substr($tec->nombre_tecnico, 0, 1)) }}</div>
                                        <span class="tec-item-nombre">{{ $tec->nombre_tecnico }}</span>
                                        <span class="tec-item-stats">{{ $pendientes }}P · {{ $enProceso }}EP</span>
                                        <span class="tec-item-badge" style="background:{{ $color }}20;color:{{ $color }};border:1px solid {{ $color }}66;">{{ $etiqueta }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
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
                        <select id="estado_repuesto" name="estado_repuesto" onchange="onEstadoRepuestoChange(this.value)">
                            <option value="No requerido">No requerido</option>
                            <option value="Requerido">Requerido</option>
                            <option value="Con stock">Con stock</option>
                        </select>
                    </div>
                </div>
                <div class="campo" id="bloque-repuesto-stock-aviso" style="display:none;">
                    <div id="panel-repuesto-aviso" style="display:none;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:9px;background:#fffbeb;border:1.5px solid #fde68a;">
                        <i class="bi bi-info-circle-fill" style="color:#d97706;font-size:17px;flex-shrink:0;margin-top:1px;"></i>
                        <div style="font-size:13px;color:#78350f;line-height:1.5;">
                            Guarda la orden y luego gestiona el requerimiento desde <strong>Repuestos / Solicitar Repuesto</strong>.
                        </div>
                    </div>
                    <div id="panel-repuesto-stock" class="rep-stock-wrap">
                        <div class="rep-stock-head"><i class="bi bi-boxes"></i> Seleccionar repuesto con stock</div>
                        <input type="text" id="inp-buscar-repuesto" placeholder="Buscar por codigo o nombre..."
                               style="width:100%;padding:9px 12px;border:1.5px solid #bbf7d0;border-radius:7px;font-size:13px;outline:none;box-sizing:border-box;"
                               oninput="buscarRepuestoStock(this.value)">
                        <div id="repuesto-resultados" class="rep-resultados"></div>
                        <div id="repuesto-seleccionado-badge" class="rep-badge">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                            <span id="repuesto-seleccionado-texto" class="rep-badge-txt"></span>
                            <button type="button" onclick="limpiarRepuestoSeleccionado()" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:15px;padding:0;">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-gear"></i> Repuestos y Producto</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Repuesto Seleccionado</label>
                        <input type="text" id="repuesto_inventario_preview" value="Sin seleccionar" readonly style="background:#f8fafc;">
                        <input type="hidden" id="repuesto_inventario_id" name="repuesto_inventario_id" value="">
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
const _urlVerificarPreorden = '{{ route("preordenes.verificar") }}';
const _urlPreordenes = '{{ route("preordenes.index") }}';
const _urlBuscarRepuestosOrden = '{{ route("ordenes.repuestos.buscar") }}';
let _preordenIgnorada = false;
let _preordenTimer = null;
let _repuestoTimer = null;

function mostrarMensaje(isError, texto) {
    const box = document.getElementById('ord-msg');
    box.className = 'msg-box ' + (isError ? 'err' : 'ok');
    box.innerHTML = texto;
    box.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toggleTecDropdown() {
    const trigger = document.getElementById('tec-trigger');
    const list = document.getElementById('tec-dropdown-list');
    if (!trigger || !list) return;
    const open = list.classList.contains('open');
    trigger.classList.toggle('open', !open);
    list.classList.toggle('open', !open);
}

function seleccionarTecnico(item, tecId, nombre, color, _etiqueta, pend, enproc) {
    const sel = document.getElementById('ord_tecnico_id');
    const av = document.getElementById('tec-trigger-avatar');
    const nm = document.getElementById('tec-trigger-nombre');
    const st = document.getElementById('tec-trigger-stats');
    const trigger = document.getElementById('tec-trigger');
    const list = document.getElementById('tec-dropdown-list');

    if (sel) sel.value = String(tecId);
    if (av) {
        av.style.background = color;
        av.textContent = (nombre || '?').substring(0, 1).toUpperCase();
    }
    if (nm) nm.textContent = nombre || '-- Seleccionar técnico --';
    if (st) st.textContent = `${pend} pend. · ${enproc} en proc.`;

    document.querySelectorAll('#tec-dropdown-list .tec-item').forEach((el) => el.classList.remove('selected'));
    if (item) item.classList.add('selected');
    if (trigger) trigger.classList.remove('open');
    if (list) list.classList.remove('open');
}

function sincronizarTecnicoDesdeSelect() {
    const sel = document.getElementById('ord_tecnico_id');
    if (!sel) return;

    if (!sel.value) {
        const av = document.getElementById('tec-trigger-avatar');
        const nm = document.getElementById('tec-trigger-nombre');
        const st = document.getElementById('tec-trigger-stats');
        document.querySelectorAll('#tec-dropdown-list .tec-item').forEach((el) => el.classList.remove('selected'));
        if (av) {
            av.style.background = '#94a3b8';
            av.textContent = '?';
        }
        if (nm) nm.textContent = '-- Seleccionar técnico --';
        if (st) st.textContent = '';
        return;
    }

    const item = document.querySelector(`#tec-dropdown-list .tec-item[data-tec-id="${sel.value}"]`);
    if (!item) return;
    item.click();
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

function escHtml(str) {
    return (str || '').toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function verificarPreorden() {
    if (_preordenIgnorada) return;

    const ci = (document.getElementById('cli_identificacion').value || '').trim();
    const codigo = (document.getElementById('producto_inventario_codigo').value || '').trim();

    if (!ci && !codigo) {
        ocultarAvisoPreorden();
        return;
    }

    clearTimeout(_preordenTimer);
    _preordenTimer = setTimeout(async () => {
        try {
            const params = [];
            if (ci) params.push('ci=' + encodeURIComponent(ci));
            if (codigo) params.push('codigo=' + encodeURIComponent(codigo));

            const r = await fetch(_urlVerificarPreorden + '?' + params.join('&'), { cache: 'no-store' });
            const d = await r.json();

            if (d.ok && d.preorden) {
                mostrarAvisoPreorden(d.preorden);
            } else {
                ocultarAvisoPreorden();
            }
        } catch {
            ocultarAvisoPreorden();
        }
    }, 600);
}

function mostrarAvisoPreorden(pre) {
    const aviso = document.getElementById('preorden-aviso');
    const detalle = document.getElementById('preorden-aviso-detalle');
    if (!aviso || !detalle) return;

    const fecha = pre.created_at ? String(pre.created_at).substring(0, 10) : '-';
    detalle.innerHTML =
        '<strong>Preorden:</strong> ' + escHtml(pre.nro_preorden || '-') + ' &nbsp;|&nbsp; ' +
        '<strong>Cliente:</strong> ' + escHtml((pre.nombres || '') + ' ' + (pre.apellidos || '')) + ' (' + escHtml(pre.identificacion || '-') + ')<br>' +
        '<strong>Equipo:</strong> ' + escHtml((pre.tipo_producto || '-') + ' ' + (pre.marca_producto || '')) + (pre.desc_producto ? ' — ' + escHtml(pre.desc_producto) : '') + '<br>' +
        '<strong>Código:</strong> ' + escHtml(pre.codigo_producto || '-') + ' &nbsp;|&nbsp; ' +
        '<strong>Registrada:</strong> ' + escHtml(fecha);

    aviso.style.display = 'block';
    aviso.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function ocultarAvisoPreorden() {
    const aviso = document.getElementById('preorden-aviso');
    if (aviso) aviso.style.display = 'none';
}

function ignorarPreorden() {
    _preordenIgnorada = true;
    ocultarAvisoPreorden();
}

function irAPreordenes() {
    window.location.href = _urlPreordenes;
}

function onEstadoRepuestoChange(valor) {
    const wrap = document.getElementById('bloque-repuesto-stock-aviso');
    const panelAviso = document.getElementById('panel-repuesto-aviso');
    const panelStock = document.getElementById('panel-repuesto-stock');

    if (!wrap) return;

    if (valor === 'Requerido') {
        wrap.style.display = '';
        panelAviso.style.display = 'flex';
        panelStock.style.display = 'none';
        limpiarRepuestoSeleccionado();
    } else if (valor === 'Con stock') {
        wrap.style.display = '';
        panelAviso.style.display = 'none';
        panelStock.style.display = 'block';
    } else {
        wrap.style.display = 'none';
        panelAviso.style.display = 'none';
        panelStock.style.display = 'none';
        limpiarRepuestoSeleccionado();
    }
}

function buscarRepuestoStock(q) {
    clearTimeout(_repuestoTimer);
    _repuestoTimer = setTimeout(async () => {
        const lista = document.getElementById('repuesto-resultados');
        if (!lista) return;

        try {
            const url = _urlBuscarRepuestosOrden + '?stock_only=1&q=' + encodeURIComponent(q || '');
            const r = await fetch(url, { cache: 'no-store' });
            const d = await r.json();

            if (!d.ok || !Array.isArray(d.repuestos) || d.repuestos.length === 0) {
                lista.innerHTML = '<div style="padding:14px 16px;color:#94a3b8;font-size:13px;text-align:center;">No se encontraron repuestos.</div>';
                lista.style.display = 'block';
                return;
            }

            renderRepuestosResultado(d.repuestos);
        } catch {
            lista.style.display = 'none';
        }
    }, 280);
}

function renderRepuestosResultado(repuestos) {
    const lista = document.getElementById('repuesto-resultados');
    if (!lista) return;

    lista.innerHTML = '';
    repuestos.forEach((r) => {
        const item = document.createElement('div');
        item.className = 'rep-item';
        item.innerHTML =
            '<code style="font-size:12px;color:#b45309;font-weight:700;white-space:nowrap;">' + escHtml(r.codigo) + '</code>' +
            '<span style="font-size:13px;color:#1e293b;">' + escHtml(r.nombre) + (r.descripcion ? '<span style="color:#94a3b8;font-size:11.5px;"> — ' + escHtml(r.descripcion) + '</span>' : '') + '</span>' +
            '<span style="background:#dcfce7;color:#166534;font-size:10.5px;padding:1px 7px;border-radius:10px;font-weight:700;">Stock: ' + (r.stock || 0) + '</span>';
        item.onclick = () => seleccionarRepuesto(r);
        lista.appendChild(item);
    });
    lista.style.display = 'block';
}

function seleccionarRepuesto(r) {
    const hiddenId = document.getElementById('repuesto_inventario_id');
    const preview = document.getElementById('repuesto_inventario_preview');
    const badge = document.getElementById('repuesto-seleccionado-badge');
    const badgeText = document.getElementById('repuesto-seleccionado-texto');
    const lista = document.getElementById('repuesto-resultados');
    const inp = document.getElementById('inp-buscar-repuesto');

    if (hiddenId) hiddenId.value = r.id;
    if (preview) preview.value = (r.codigo || '-') + ' - ' + (r.nombre || '-');
    if (badgeText) badgeText.textContent = (r.codigo || '-') + ' - ' + (r.nombre || '-');
    if (badge) badge.style.display = 'flex';
    if (lista) lista.style.display = 'none';
    if (inp) {
        inp.value = r.codigo || '';
        inp.style.borderColor = '#f59e0b';
        inp.style.background = '#fffbeb';
    }
}

function limpiarRepuestoSeleccionado() {
    const hiddenId = document.getElementById('repuesto_inventario_id');
    const preview = document.getElementById('repuesto_inventario_preview');
    const badge = document.getElementById('repuesto-seleccionado-badge');
    const lista = document.getElementById('repuesto-resultados');
    const inp = document.getElementById('inp-buscar-repuesto');

    if (hiddenId) hiddenId.value = '';
    if (preview) preview.value = 'Sin seleccionar';
    if (badge) badge.style.display = 'none';
    if (lista) lista.style.display = 'none';
    if (inp) {
        inp.value = '';
        inp.style.borderColor = '';
        inp.style.background = '';
    }
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

    _preordenIgnorada = false;
    verificarPreorden();
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
            _preordenIgnorada = false;
            ocultarAvisoPreorden();
            onEstadoRepuestoChange(document.getElementById('estado_repuesto').value || 'No requerido');
            limpiarRepuestoSeleccionado();
            sincronizarTecnicoDesdeSelect();
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
    onEstadoRepuestoChange(document.getElementById('estado_repuesto').value || 'No requerido');
    sincronizarTecnicoDesdeSelect();

    const inpCi = document.getElementById('cli_identificacion');
    const inpCod = document.getElementById('producto_inventario_codigo');
    if (inpCi) {
        inpCi.addEventListener('input', () => {
            _preordenIgnorada = false;
            verificarPreorden();
        });
    }
    if (inpCod) {
        inpCod.addEventListener('input', () => {
            _preordenIgnorada = false;
            verificarPreorden();
        });
    }

    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('tec-dropdown');
        const trigger = document.getElementById('tec-trigger');
        const list = document.getElementById('tec-dropdown-list');
        if (!dropdown || !trigger || !list) return;
        if (!dropdown.contains(e.target)) {
            trigger.classList.remove('open');
            list.classList.remove('open');
        }
    });
});
</script>
@endpush
