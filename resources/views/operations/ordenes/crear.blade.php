@extends('layouts.app')

@section('titulo', 'Crear Orden de Servicio')



@push('css_adicional')

<style>

/* Visual 1:1 basado en SGN Vanilla / Modulos / Ordenes / Crear */

.modulo {

    padding: 30px;

    background: #f1f5f9;

    min-height: 100%;

}

.orden-container {

    background: white;

    border-radius: 14px;

    padding: 35px;

    box-shadow: 0 4px 24px rgba(0,0,0,0.07);

    max-width: 1000px;

    margin: 0 auto;

}

.form-titulo {

    margin-bottom: 30px;

    padding-bottom: 20px;

    border-bottom: 2px solid #f1f5f9;

}

.form-titulo h2 {

    margin: 0 0 4px 0;

    color: #0f172a;

    font-size: 22px;

    font-weight: 700;

}

.form-titulo p {

    margin: 0;

    color: #94a3b8;

    font-size: 14px;

}

.seccion-form {

    margin-bottom: 28px;

    border: 1px solid #e2e8f0;

    border-radius: 10px;

    overflow: visible;

    background: #fff;

    box-shadow: none;

    position: relative;

}

/* Mantener bordes redondeados en header e hijo final aunque overflow sea visible */

.seccion-hdr { border-radius: 10px 10px 0 0; }

.seccion-form > :last-child { border-radius: 0 0 10px 10px; }

.seccion-hdr {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 14px 20px;

    background: #f8fafc;

    border-bottom: 1px solid #e2e8f0;

    margin: 0;

    font-size: 15px;

    font-weight: 600;

    color: #1e293b;

}

.seccion-hdr i { font-size: 18px; }

.seccion-body { padding: 20px; }

.grid-2,

.grid-3 {

    display: grid;

    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));

    gap: 18px;

}

.campo {

    display: flex;

    flex-direction: column;

    gap: 6px;

    margin-bottom: 0;

}

.campo label {

    font-size: 13px;

    font-weight: 600;

    color: #475569;

}

.campo input,

.campo select,

.campo textarea {

    padding: 10px 12px;

    border: 1px solid #cbd5e1;

    border-radius: 7px;

    font-size: 14px;

    color: #0f172a;

    background: white;

    transition: border-color 0.2s, box-shadow 0.2s;

    outline: none;

    font-family: inherit;

}

.campo input:focus,

.campo select:focus,

.campo textarea:focus {

    border-color: #2563eb;

    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);

}

.campo textarea { resize: vertical; }

.req { color: #ef4444; }

.btn-buscar { background: #eff6ff; color: #2563eb; border: 1.5px solid #bfdbfe; padding: 10px 14px; border-radius: 7px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background .2s; }

.btn-buscar:hover { background: #dbeafe; }

.botones {

    display: flex;

    gap: 12px;

    justify-content: flex-end;

    margin-top: 10px;

    padding-top: 20px;

    border-top: 1px solid #f1f5f9;

}

.btn-crear {

    background: #2563eb;

    color: white;

    border: none;

    padding: 12px 28px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition: background 0.2s;

    display: inline-flex;

    align-items: center;

    gap: 8px;

}

.btn-crear:hover { background: #1d4ed8; }

.btn-crear:disabled { background: #94a3b8; cursor: not-allowed; }

.btn-limpiar {

    background: #f1f5f9;

    color: #475569;

    border: 1px solid #e2e8f0;

    padding: 12px 28px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition: background 0.2s;

}

.btn-limpiar:hover { background: #e2e8f0; }

.msg-box { display: none; padding: 16px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 24px; }

.msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

.msg-box.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

.lista-lineas { display: flex; flex-direction: column; gap: 10px; }

.linea-item { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 10px; align-items: center; }

.btn-mini { background: #f1f5f9; border: 1px solid #cbd5e1; color: #0f172a; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; }

.btn-mini:hover { background: #e2e8f0; }

.hidden { display: none !important; }

.preord-alert { display: none; margin: 0 0 16px 0; padding: 14px 18px; border-radius: 10px; background: #fffbeb; border: 1.5px solid #fde68a; color: #78350f; }

.preord-title { font-weight: 800; font-size: 13px; color: #92400e; margin-bottom: 5px; }

.rep-stock-wrap { display: none; margin-top: 10px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 9px; padding: 12px 14px; }

.rep-stock-head { font-weight: 700; font-size: 13px; color: #166534; margin-bottom: 8px; }

.rep-resultados { display: none; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,.08); }

.rep-item { padding: 9px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: grid; grid-template-columns: auto 1fr auto; gap: 10px; align-items: center; }

.rep-item:hover { background: #fffbeb; }

.rep-badge { display: none; margin-top: 8px; align-items: center; gap: 8px; background: #dcfce7; border: 1px solid #86efac; border-radius: 7px; padding: 7px 12px; }

.rep-badge-txt { font-size: 13px; color: #166534; font-weight: 700; flex: 1; }

.prod-badge { display:none; font-size:11.5px; font-weight:600; padding:3px 9px; border-radius:20px; margin-top:4px; width:fit-content; }

.prod-spinner { display:none; position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:13px; color:#2563eb; }

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.motivo-lock-msg { margin-top: 16px; font-size: 12.5px; color: #475569; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 9px 12px; }

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

.tec-dropdown-list { display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1.5px solid #2563eb; border-top: none; border-radius: 0 0 8px 8px; z-index: 9999; box-shadow: 0 8px 24px rgba(0,0,0,.15); overflow: hidden; }

.tec-dropdown-list.open { display: block; }

.tec-search-wrap { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; position: sticky; top: 0; z-index: 1; }

.tec-search-inp { width: 100%; padding: 7px 10px 7px 30px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 12.5px; outline: none; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 16 16'%3E%3Cpath fill='%2394a3b8' d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.156a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E") no-repeat 9px center; box-sizing: border-box; transition: border-color .15s; }

.tec-search-inp:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.1); }

.tec-search-empty { padding: 14px 12px; font-size: 12.5px; color: #94a3b8; text-align: center; display: none; }

.tec-items-scroll { max-height: 260px; overflow-y: auto; }

.tec-item { display: flex; align-items: center; gap: 9px; padding: 8px 12px; cursor: pointer; transition: background .12s; border-bottom: 1px solid #f1f5f9; }

.tec-item:last-child { border-bottom: none; }

.tec-item:hover { background: #f0f7ff; }

.tec-item.selected { background: #eff6ff; }

.tec-item-avatar { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 11px; flex-shrink: 0; }

.tec-item-nombre { flex: 1; font-size: 12.5px; font-weight: 600; color: #0f172a; }

.tec-item-stats { font-size: 11px; color: #94a3b8; white-space: nowrap; }

.tec-item-badge { font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 20px; flex-shrink: 0; margin-left: 6px; }

.tec-yo-badge { font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 20px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; margin-left: 4px; flex-shrink: 0; }

.tec-trigger-yo { font-size: 10px; color: #2563eb; font-weight: 700; margin-left: 4px; }

@media (max-width: 768px) {

    .modulo { padding: 16px; }

    .orden-container { padding: 22px; }

}

</style>

@endpush



@section('contenido')

<section class="modulo activo">

<div class="orden-container">

    <div class="form-titulo">

        <h2><i class="bi bi-clipboard-plus me-2"></i>Nueva Orden de Servicio</h2>

        <p>Complete todos los campos requeridos</p>

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

                    <i class="bi bi-arrow-right-circle"></i> Ir a Preordenes

                </button>

                <button type="button" onclick="ignorarPreorden()"

                        style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:8px;padding:7px 16px;font-size:12.5px;font-weight:600;cursor:pointer;">

                    Continuar de todos modos

                </button>

            </div>

        </div>



        <div class="seccion-form motivo-base">

            <div class="seccion-hdr"><i class="bi bi-clipboard-check"></i> Motivo de Ingreso</div>

            <div class="seccion-body">

                <div class="campo">

                    <label>Motivo <span class="req">*</span></label>

                    <select id="motivo_ingreso" name="motivo_ingreso" required onchange="actualizarMotivo()">

                        <option value="">-- Seleccione --</option>

                        <option value="Servicio Cliente Externo">Servicio Cliente Externo</option>

                        <option value="Validacion de Garantia">Validacion de Garantia</option>

                        <option value="Servicios a Empresas">Servicios a Empresas</option>

                    </select>

                </div>

                <div id="motivo-lock-msg" class="motivo-lock-msg" style="margin-top: 12px;">

                    Seleccione el motivo de ingreso para habilitar el resto del formulario.

                </div>

            </div>

        </div>



        <div class="seccion-form bloque-empresa-flow hidden">

            <div class="seccion-hdr"><i class="bi bi-building"></i> Empresa</div>

            <div class="seccion-body">

                <div class="grid-2">

                    <div class="campo">

                        <label>Seleccionar Empresa <span class="req">*</span></label>

                        <select name="empresa_id" id="empresa_id" onchange="onEmpresaChange(this.value)">

                            <option value="">-- Seleccione --</option>

                            @foreach($empresas as $empresa)

                                <option value="{{ $empresa->id }}" data-nombre="{{ $empresa->nombre }}">{{ $empresa->nombre }} - {{ $empresa->ruc }}</option>

                            @endforeach

                        </select>

                    </div>

                    <div class="campo">

                        <label>Tipo <span class="req">*</span></label>

                        <div style="display:flex;gap:24px;margin-top:10px;">

                            <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">

                                <input type="radio" name="subtipo_empresa" value="Autoconsumo" onchange="onSubtipoEmpresaChange(this.value)">

                                Autoconsumo

                            </label>

                            <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">

                                <input type="radio" name="subtipo_empresa" value="Servicios" onchange="onSubtipoEmpresaChange(this.value)">

                                Servicios

                            </label>

                            <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">

                                <input type="radio" name="subtipo_empresa" value="Stock" onchange="onSubtipoEmpresaChange(this.value)">

                                Stock

                            </label>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <div id="bloque-form-servicios-empresa" class="seccion-form bloque-empresa-detalle hidden">

            <div class="seccion-hdr"><i class="bi bi-tools"></i> Datos del Servicio</div>

            <div class="seccion-body">

                <div class="grid-2">

                    <div class="campo">

                        <label>Tipo de Servicio <span class="req">*</span></label>

                        <select name="emp_tipo_servicio" id="emp_tipo_servicio">

                            <option value="">-- Seleccione --</option>

                            @foreach($tiposServicio as $ts)

                                <option value="{{ $ts->nombre }}">{{ $ts->nombre }}</option>

                            @endforeach

                        </select>

                    </div>

                    <div class="campo">

                        <label>Nro. Ticket <span class="req">*</span></label>

                        <input type="text" name="emp_nro_ticket" id="emp_nro_ticket" autocomplete="off"
                               placeholder="Ingrese el numero de ticket" oninput="this.value=this.value.toUpperCase()" onblur="validarInputDuplicado(this, 'factura')" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}">

                    </div>

                </div>

                <div class="campo">

                    <label>Descripcion <span class="req">*</span></label>

                    <textarea name="emp_descripcion" id="emp_descripcion" rows="4" placeholder="Describa el servicio a brindar..."></textarea>

                </div>

            </div>

        </div>



        <div id="bloque-equipo-empresa" class="seccion-form bloque-empresa-detalle hidden">

            <div class="seccion-hdr"><i class="bi bi-hdd"></i> Datos del Equipo</div>

            <div class="seccion-body">

                <div class="grid-3">

                    <div class="campo">

                        <label>Codigo <span class="req">*</span></label>

                        <input type="text" name="emp_modelo" id="emp_modelo" autocomplete="off" style="text-transform:uppercase;"

                               oninput="this.value=this.value.toUpperCase(); buscarProductoEmpresa(this.value);">

                        <span id="emp-prod-badge" class="prod-badge"></span>

                    </div>

                    <div class="campo">

                        <label>Tipo de Equipo <span class="req">*</span></label>

                        <select name="emp_tipo_equipo" id="emp_tipo_equipo">

                            <option value="">-- Seleccione --</option>

                            @foreach($tiposDispositivo as $tipo)

                                <option value="{{ $tipo->nombre }}">{{ $tipo->nombre }}</option>

                            @endforeach

                        </select>

                    </div>

                    <div class="campo">

                        <label>Tipo de Servicio <span class="req">*</span></label>

                        <select name="emp_tipo_servicio_id" id="emp_tipo_servicio_id">

                            <option value="">-- Seleccione --</option>

                            @foreach($tiposServicio as $ts)

                                <option value="{{ $ts->id }}">{{ $ts->nombre }}</option>

                            @endforeach

                        </select>

                    </div>

                    <div class="campo">

                        <label>Marca <span class="req">*</span></label>

                        <select name="emp_marca" id="emp_marca">

                            <option value="">-- Seleccione --</option>

                            @foreach($marcas as $marca)

                                <option value="{{ $marca->nombre }}">{{ $marca->nombre }}</option>

                            @endforeach

                        </select>

                    </div>



                    <div class="campo" style="grid-column: span 2;">

                        <label>Serie</label>

                        <div class="lista-lineas" id="series-empresa-container">

                            <div class="linea-item">

                                <input type="text" name="emp_series[]" id="emp_serie" oninput="this.value=this.value.toUpperCase()" onblur="validarInputDuplicado(this, 'serie')" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}" placeholder="Serie principal">

                                <button type="button" class="btn-mini" onclick="agregarSerieEmpresa()">+</button>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="campo">

                    <label>Problema Reportado <span class="req">*</span></label>

                    <textarea name="emp_falla" id="emp_falla" rows="4"></textarea>

                </div>

                <div class="campo">

                    <label>Recepcion / detalles <span class="req">*</span></label>

                    <textarea name="emp_observacion" id="emp_observacion" rows="3"></textarea>

                </div>

            </div>

        </div>



        <div id="bloque-asignacion-empresa" class="seccion-form bloque-empresa-detalle hidden">

            <div class="seccion-hdr"><i class="bi bi-person-gear"></i> Tecnico Responsable</div>

            <div class="seccion-body">

                <div class="grid-2">

                    <div class="campo">

                        <label>Tecnico Asignado <span class="req">*</span> <span style="font-size:11px;font-weight:400;color:#94a3b8;">ordenado por menor carga</span></label>

                        {{-- Select nativo oculto (sincronizado) --}}

                        <select id="ord_tecnico_id_empresa" name="ord_tecnico_id" class="tec-native-sr">

                            <option value="">-- Seleccione un Tecnico --</option>

                            @foreach($tecnicos as $tec)

                                @php

                                    $pendientes = (int) ($tec->pendientes ?? 0);

                                    $enProceso  = (int) ($tec->en_proceso ?? 0);

                                @endphp

                                <option value="{{ $tec->id }}" data-pend="{{ $pendientes }}" data-proc="{{ $enProceso }}">

                                    {{ $tec->nombre_tecnico }}

                                </option>

                            @endforeach

                        </select>

                        {{-- Dropdown custom empresa --}}

                        <div class="tec-dropdown" id="tec-dropdown-emp">

                            <div class="tec-trigger" id="tec-trigger-emp" onclick="toggleTecDropdownEmp()">

                                <div class="tec-trigger-avatar" id="tec-trigger-avatar-emp">?</div>

                                <div class="tec-trigger-info">

                                    <div class="tec-trigger-nombre" id="tec-trigger-nombre-emp">-- Seleccionar tecnico --</div>

                                    <div class="tec-trigger-stats" id="tec-trigger-stats-emp"></div>

                                </div>

                                <i class="bi bi-chevron-down tec-trigger-arrow"></i>

                            </div>

                            <div class="tec-dropdown-list" id="tec-dropdown-list-emp">

                                <div class="tec-search-wrap">

                                    <input type="text" class="tec-search-inp" placeholder="Buscar tecnico..." oninput="filtrarTecnicos(this, 'tec-dropdown-list-emp')" autocomplete="off">

                                </div>

                                <div class="tec-search-empty" id="tec-empty-emp">Sin coincidencias</div>

                                <div class="tec-items-scroll" id="tec-items-emp">

                                @php

                                    $tecnicoSesionId = (int) session('tecnico_id', 0);

                                    $maxCargaEmp = 0;

                                    foreach ($tecnicos as $t) {

                                        $maxCargaEmp = max($maxCargaEmp, (int)($t->pendientes ?? 0) + (int)($t->en_proceso ?? 0));

                                    }

                                    $umbralRojoEmp = max(2, (int) ceil($maxCargaEmp * 0.7));

                                @endphp

                                @foreach($tecnicos as $tec)

                                    @php

                                        $pendientes = (int) ($tec->pendientes ?? 0);

                                        $enProceso  = (int) ($tec->en_proceso ?? 0);

                                        $total = $pendientes + $enProceso;

                                        $esTuMismo = ($tec->id === $tecnicoSesionId);

                                        if ($total === 0)       { $color = '#10b981'; $etiqueta = 'Libre'; }

                                        elseif ($total <= $umbralRojoEmp) { $color = '#f59e0b'; $etiqueta = 'Normal'; }

                                        else                    { $color = '#ef4444'; $etiqueta = 'Cargado'; }

                                    @endphp

                                    <div class="tec-item {{ $esTuMismo ? 'tec-item-yo' : '' }}"

                                         data-tec-id="{{ $tec->id }}"

                                         onclick="seleccionarTecnicoEmp(this, {{ $tec->id }}, '{{ addslashes($tec->nombre_tecnico) }}', '{{ $color }}', '{{ $etiqueta }}', {{ $pendientes }}, {{ $enProceso }}, {{ $esTuMismo ? 'true' : 'false' }})">

                                        <div class="tec-item-avatar" style="background:{{ $esTuMismo ? '#2563eb' : $color }};">{{ strtoupper(substr($tec->nombre_tecnico, 0, 1)) }}</div>

                                        <span class="tec-item-nombre">{{ $tec->nombre_tecnico }}</span>

                                        @if($esTuMismo)<span class="tec-yo-badge">Tu</span>@endif

                                        <span class="tec-item-stats">{{ $pendientes }}P ? {{ $enProceso }}EP</span>

                                        <span class="tec-item-badge" style="background:{{ $color }}20;color:{{ $color }};border:1px solid {{ $color }}66;">{{ $etiqueta }}</span>

                                    </div>

                                @endforeach

                                </div>{{-- /tec-items-scroll --}}

                            </div>

                        </div>

                    </div>



                    {{-- Checklist de tecnicos para NOVISOLUTIONS --}}

                    <div class="campo hidden" id="bloque-multi-tecnicos" style="margin-top: 10px; display: flex; flex-direction: column; gap: 4px;">

                        <label style="font-size: 13px; font-weight: 600; color: #475569;">Tecnicos Asignados <span class="req">*</span> <span style="font-size:11px;font-weight:400;color:#94a3b8;text-transform:none;">(maximo 5 tecnicos)</span></label>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 8px; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; max-height: 250px; overflow-y: auto; background: #fff;">

                            @foreach($tecnicos as $tec)

                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer; padding: 4px; color: #1e293b; font-size: 13px;">

                                    <input type="checkbox" name="tecnicos_asignados[]" value="{{ $tec->id }}" data-nombre="{{ $tec->nombre_tecnico }}" class="chk-tecnico-emp" style="width: 16px; height: 16px; cursor: pointer;">

                                    {{ $tec->nombre_tecnico }}

                                </label>

                            @endforeach

                        </div>

                        <div class="campo" style="margin-top: 10px;">

                            <label style="font-size: 13px; font-weight: 600; color: #475569;">Tecnico Encargado <span class="req">*</span></label>

                            <select id="tecnico_encargado" name="tecnico_encargado" disabled>

                                <option value="">-- Seleccione al encargado --</option>

                            </select>

                            <small style="display:block;margin-top:4px;color:#64748b;">Debe ser uno de los tecnicos asignados y sera quien realice el reporte.</small>

                        </div>

                    </div>

                    <div class="campo">

                        <label>Fecha Prometido <span class="req">*</span></label>

                        <input type="date" name="emp_fecha_prometido" id="emp_fecha_prometido">

                    </div>

                    <div class="campo hidden" id="bloque-cas-empresa">

                        <label>Asignar CAS <span style="font-size:11px;font-weight:400;color:#94a3b8;">(Opcional)</span></label>

                        <select id="cas_id_empresa" name="cas_id_empresa">

                            <option value="">-- Seleccione CAS --</option>

                            @foreach($cas as $c)

                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>

                            @endforeach

                        </select>

                    </div>

                    <div class="campo hidden" id="campo-sucursal-cliente-empresa">

                        <label>Sucursal Cliente</label>

                        <select id="nro_sucursal_cliente_empresa" name="nro_sucursal_cliente">

                            <option value="">-- Seleccione --</option>

                            @foreach($sucursalesCliente as $suc)

                                <option value="{{ $suc->codigo }}">{{ $suc->codigo }} - {{ $suc->nombre }}</option>

                            @endforeach

                            <option value="999">999 - SERVICIO EXTERNO</option>

                        </select>

                    </div>

                </div>



                {{-- Bloque de calculo para NOVISOLUTIONS --}}

                <div id="bloque-calculo-novisolutions" class="hidden" style="margin-top: 20px; padding: 16px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; color: #166534; box-shadow: 0 2px 8px rgba(16,185,129,0.05);">

                    <h4 style="margin: 0 0 14px; font-weight: 800; font-size: 13.5px; display: flex; align-items: center; gap: 6px; color: #166534;"><i class="bi bi-calculator"></i> Desglose de Costo de Servicio Corporativo</h4>

                    <div class="grid-2" style="margin-bottom: 14px; gap: 14px;">

                        <div class="campo" style="margin-bottom: 0;">

                            <label style="color: #166534; font-weight: 700; font-size: 12.5px;">Tarifa por Hora ($)</label>

                            <input type="number" step="0.01" name="valor_hora" id="valor_hora" value="50.00" style="border-color: #86efac; padding: 8px 12px; font-size: 13.5px; font-weight: 600; border-radius: 8px;">

                        </div>

                        <div class="campo" style="margin-bottom: 0;">

                            <label style="color: #166534; font-weight: 700; font-size: 12.5px;">Horas Trabajadas</label>

                            <input type="number" step="0.25" name="horas_trabajadas" id="horas_trabajadas" value="1.00" style="border-color: #86efac; padding: 8px 12px; font-size: 13.5px; font-weight: 600; border-radius: 8px;">

                        </div>

                    </div>

                    <div style="font-size: 14px; font-weight: 800; display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #86efac; padding-top: 12px; flex-wrap: wrap; gap: 10px;">

                        <span>Formula: <span id="formula-lbl" style="font-family: monospace; font-size: 13px; color: #15803d;">0 tecnicos * 1.00 horas * $50.00/hr</span></span>

                        <span style="font-size: 16px; color: #14532d;">Subtotal Estimado: <strong id="cobro-total-lbl" style="font-size: 19px; color: #166534;">$0.00</strong></span>

                    </div>

                </div>

            </div>

        </div>



        <!-- SECCIÓN 1: DATOS DEL CLIENTE -->

        <div class="seccion-form bloque-motivo bloque-personal hidden">

            <div class="seccion-hdr"><i class="bi bi-person-badge"></i> Datos del Cliente</div>

            <div class="seccion-body">
                <input type="hidden" id="cli_tipo" name="cli_tipo" value="natural">

                <div class="grid-3" style="margin-bottom: 18px;">

                    <div class="campo">

                        <label>C.I / RUC <span class="req">*</span></label>

                        <input type="text" id="cli_identificacion" name="cli_identificacion" maxlength="20" required placeholder="Ingrese C.I / RUC">

                        <span id="cli-buscar-status" style="font-size: 11px; display: none; margin-top: 2px; font-weight: 600;"></span>

                    </div>

                    <div class="campo">

                        <label>Nombre <span class="req">*</span></label>

                        <input type="text" id="cli_nombres" name="cli_nombres" maxlength="100" required oninput="this.value=this.value.toUpperCase()">

                    </div>

                    <div class="campo">

                        <label>Apellido <span class="req">*</span></label>

                        <input type="text" id="cli_apellidos" name="cli_apellidos" maxlength="100" required oninput="this.value=this.value.toUpperCase()">

                    </div>

                </div>

                <div class="grid-3" style="margin-bottom: 18px;">

                    <div class="campo">

                        <label>Teléfono <span class="req">*</span></label>

                        <input type="text" id="cli_telefono" name="cli_telefono" maxlength="20" required>

                    </div>

                    <div class="campo">

                        <label>Correo <span class="req">*</span></label>

                        <input type="email" id="cli_correo" name="cli_correo" maxlength="100">

                    </div>

                    <div class="campo">

                        <label>Direccion <span class="req">*</span></label>

                        <input type="text" id="cli_direccion" name="cli_direccion" maxlength="200" oninput="this.value=this.value.toUpperCase()">

                    </div>

                </div>

            </div>

        </div>



        <!-- SECCION DE GARANTÍA Y FACTURACION (SE MUESTRA SOLO EN VALIDACION DE GARANTÍA) -->

        <div id="bloque-garantia-facturacion" class="seccion-form bloque-motivo bloque-personal hidden">

            <div class="seccion-hdr"><i class="bi bi-shield-check"></i> Garantia y Facturacion</div>

            <div class="seccion-body">

                <div id="bloque-facturacion" class="grid-3 hidden" style="margin-bottom: 18px;">

                    <div class="campo">

                        <label>Nro. Factura 1 <span class="req">*</span></label>

                        <div style="display: flex; gap: 8px; align-items: center;">

                            <input type="text" id="nro_factura" name="nro_factura" oninput="onInputFactura(this)" onblur="validarInputDuplicado(this, 'factura')" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}" placeholder="000-000-000000000" style="flex: 1;">

                            <button type="button" id="btn-agregar-factura-2" class="btn-mini" style="height: 40px; width: 40px; display: flex; align-items: center; justify-content: center; font-size: 18px;" onclick="mostrarFactura2()">+</button>

                        </div>

                    </div>

                    <div class="campo hidden" id="wrapper-factura-2">

                        <label>Nro. Factura 2</label>

                        <div style="display: flex; gap: 8px; align-items: center;">

                            <input type="text" id="nro_factura_2" name="nro_factura_2" oninput="formatearFactura(this)" onblur="validarInputDuplicado(this, 'factura')" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}" placeholder="000-000-000000000" style="flex: 1;">

                            <button type="button" class="btn-mini" style="height: 40px; width: 40px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #dc2626; border-color: #fca5a5; background: #fee2e2;" onclick="ocultarFactura2()">-</button>

                        </div>

                    </div>

                    <div class="campo">

                        <label>Fecha de Facturacion <span class="req">*</span></label>

                        <input type="date" id="fecha_facturacion" name="fecha_facturacion" max="{{ \Carbon\Carbon::now('America/Guayaquil')->format('Y-m-d') }}">

                    </div>

                    <div class="campo">

                        <label>Sucursal Cliente <span class="req">*</span></label>

                        <select id="nro_sucursal_cliente" name="nro_sucursal_cliente">

                            <option value="">-- Seleccione --</option>

                            @foreach($sucursalesCliente as $suc)

                                <option value="{{ $suc->codigo }}">{{ $suc->codigo }} - {{ $suc->nombre }}</option>

                            @endforeach

                            <option value="999">999 - SERVICIO EXTERNO</option>

                        </select>

                    </div>

                    <div class="campo">

                        <label>Tipo de Garantia <span class="req">*</span></label>

                        <select id="garantia_tipo" name="garantia_tipo" onchange="actualizarGarantiaTipo()">

                            <option value="">-- Seleccione --</option>

                            <option value="interna">Interna</option>

                            <option value="externa">Externa</option>

                        </select>

                    </div>

                    <div class="campo hidden" id="bloque-cas">

                        <label>Asignar CAS <span class="req">*</span></label>

                        <select id="cas_id" name="cas_id">

                            <option value="">-- Seleccione CAS --</option>

                            @foreach($cas as $c)

                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>

                            @endforeach

                        </select>

                    </div>

                </div>

            </div>

        </div>



        <!-- SECCIÓN 2: TÉCNICO RESPONSABLE -->

        <div class="seccion-form bloque-motivo bloque-personal hidden">

            <div class="seccion-hdr"><i class="bi bi-person-badge-fill"></i> Técnico Responsable</div>

            <div class="seccion-body">

                <div class="campo">

                    <label>Técnico Asignado <span class="req">*</span> <span style="font-size:11px;font-weight:400;color:#94a3b8;">ordenados por menor carga</span></label>

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

                            <div class="tec-search-wrap">

                                <input type="text" class="tec-search-inp" placeholder="Buscar técnico..." oninput="filtrarTecnicos(this, 'tec-dropdown-list')" autocomplete="off">

                            </div>

                            <div class="tec-search-empty" id="tec-empty-per">Sin coincidencias</div>

                            <div class="tec-items-scroll" id="tec-items-per">

                            @php

                                $tecnicoSesionId = (int) session('tecnico_id', 0);

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

                                    $esTuMismo = ($tec->id === $tecnicoSesionId);

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

                                <div class="tec-item {{ $esTuMismo ? 'tec-item-yo' : '' }}"

                                     data-tec-id="{{ $tec->id }}"

                                     onclick="seleccionarTecnico(this, {{ $tec->id }}, '{{ addslashes($tec->nombre_tecnico) }}', '{{ $esTuMismo ? '#2563eb' : $color }}', '{{ $etiqueta }}', {{ $pendientes }}, {{ $enProceso }}, {{ $esTuMismo ? 'true' : 'false' }})">

                                    <div class="tec-item-avatar" style="background:{{ $esTuMismo ? '#2563eb' : $color }};">{{ strtoupper(substr($tec->nombre_tecnico, 0, 1)) }}</div>

                                    <span class="tec-item-nombre">{{ $tec->nombre_tecnico }}</span>

                                    @if($esTuMismo)<span class="tec-yo-badge">TÃƒº</span>@endif

                                    <span class="tec-item-stats">{{ $pendientes }}P · {{ $enProceso }}EP</span>

                                    <span class="tec-item-badge" style="background:{{ $color }}20;color:{{ $color }};border:1px solid {{ $color }}66;">{{ $etiqueta }}</span>

                                </div>

                            @endforeach

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- SECCIÓN 3: DATOS DEL EQUIPO -->

        <div class="seccion-form bloque-motivo bloque-personal hidden">

            <div class="seccion-hdr"><i class="bi bi-laptop"></i> Datos del Equipo</div>

            <div class="seccion-body">

                <!-- Modelo oculto requerido por base de datos / validación -->

                <input type="hidden" id="eq_modelo" name="eq_modelo" value="">

                <div id="producto-nuevo-aviso" style="display:none; margin-bottom:16px; padding:14px 16px; border:1px solid #fcd34d; background:#fffbeb; color:#92400e; border-radius:12px; font-size:13px; line-height:1.55;">
                    Este codigo no esta registrado en nuestra base de datos, ingresa por favor la descripcion con el formato correcto para guardarlo en la base de datos cuando crees la orden.
                </div>



                <div class="grid-3" style="margin-bottom: 18px;">

                    <div class="campo">

                        <label>Código <span class="req">*</span></label>

                        <div style="position:relative;">

                            <input type="text" id="producto_inventario_codigo" name="producto_inventario_codigo" required

                                   autocomplete="off" style="width:100%;text-transform:uppercase;"

                                   oninput="this.value=this.value.toUpperCase(); manejarInputCodigoProducto();">

                            <span id="prod-spinner" class="prod-spinner">

                                <i class="bi bi-arrow-repeat" style="animation:spin .7s linear infinite;display:inline-block;"></i>

                            </span>

                        </div>

                        <span id="prod-badge" class="prod-badge"></span>

                    </div>

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

                        <label>Tipo de Servicio <span class="req">*</span></label>

                        <div id="wrapper-tipo-servicio">

                            <!-- Input para Cliente Externo -->

                            <input type="text" id="tipo_servicio_texto" name="tipo_servicio_texto" placeholder="INGRESE EL TIPO DE SERVICIO" oninput="this.value=this.value.toUpperCase()" style="width:100%;">



                            <!-- Select para otros motivos (Garantía, etc.) -->

                            <select id="eq_tipo_servicio" name="eq_tipo_servicio" style="width:100%;">

                                <option value="">-- Seleccione (Opcional) --</option>

                                @foreach($tiposServicio as $ts)

                                    <option value="{{ $ts->id }}">{{ $ts->nombre }}</option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                </div>                <div class="grid-3" style="margin-bottom: 18px;">
                    <div class="campo">
                        <label>Marca <span class="req">*</span></label>
                        <select id="eq_marca" name="eq_marca" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->nombre }}">{{ $marca->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo" id="campo-producto-nuevo-descripcion" style="display:none;">
                        <label>Descripcion del equipo <span class="req">*</span></label>
                        <input type="text" id="producto_nuevo_descripcion" placeholder="INGRESE LA DESCRIPCION CORRECTA" oninput="this.value=this.value.toUpperCase(); sincronizarDescripcionProductoNuevo();" style="width:100%;">
                    </div>
                </div>



                <div class="campo" style="margin-bottom: 18px;">

                    <label>Serie</label>

                    <div class="lista-lineas" id="series-container">

                        <div class="linea-item" style="display: flex; gap: 10px; align-items: center; width: 100%;">

                            <input type="text" name="series[]" required oninput="this.value=this.value.toUpperCase()" onblur="validarInputDuplicado(this, 'serie')" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}" placeholder="Número de serie" style="flex: 1;">

                            <button type="button" class="btn-mini" onclick="agregarSerie()" style="height: 38px; padding: 0 14px;">+</button>

                        </div>

                    </div>

                </div>



                <div class="grid-3" style="margin-bottom: 18px;">

                    <div class="campo">

                        <label>Estado Repuesto</label>

                        <select id="estado_repuesto" name="estado_repuesto" onchange="onEstadoRepuestoChange(this.value)">

                            <option value="No requerido">No requerido</option>

                            <option value="Requerido">Requerido</option>

                            <option value="Con stock">Con stock</option>

                        </select>

                    </div>

                </div>



                <!-- Panel de stock anidado -->

                <div class="campo" id="bloque-repuesto-stock-aviso" style="display:none; margin-bottom: 18px;">

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

                        <div id="repuestos-seleccionados-container" style="display:none; margin-top:12px;">

                            <div style="font-size:12px; font-weight:700; color:#15803d; margin-bottom:6px; display:flex; align-items:center; gap:6px;">

                                <i class="bi bi-check2-all"></i> Repuestos Seleccionados:

                            </div>

                            <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden;">

                                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:12.5px;">

                                    <thead>

                                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#64748b; font-weight:700;">

                                            <th style="padding:8px 10px;">Código</th>

                                            <th style="padding:8px 10px;">Nombre</th>

                                            <th style="padding:8px 10px; width:50px; text-align:center;">Acción</th>

                                        </tr>

                                    </thead>

                                    <tbody id="tabla-repuestos-cuerpo">

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Repuestos Seleccionados list -->

                <div class="campo" style="margin-bottom: 18px; display: none;" id="bloque-repuestos-seleccionados-visual">

                    <label>Repuestos Seleccionados en Stock</label>

                    <div id="repuestos-ocultos-inputs"></div>

                    <input type="hidden" id="repuesto_inventario_id" name="repuesto_inventario_id" value="">

                    <div id="repuestos-lista-visual-resumen" style="font-size:13px; color:#475569; padding:8px 10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:7px; font-style:italic;">

                        Ningún repuesto de stock seleccionado.

                    </div>

                </div>



                <!-- Credenciales del Equipo Divider / Header -->

                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px; margin-bottom: 16px; border-top: 1px solid #f1f5f9; padding-top: 18px;">

                    <span style="font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase;">

                        Credenciales del Equipo

                    </span>

                    <button type="button" class="btn-crear" style="background: #2563eb; color: #fff; font-size: 12.5px; font-weight: 700; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'" onclick="agregarCredencial()">

                        <i class="bi bi-plus-circle"></i> Agregar credencial

                    </button>

                </div>



                <div id="credenciales-container" style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px;">

                    <!-- Tarjetas dinámicas se agregarán aquí -->

                </div>



                <div class="campo" style="margin-bottom: 18px;">

                    <label>Problema Reportado <span class="req">*</span></label>

                    <textarea id="eq_falla" name="eq_falla" rows="3" required placeholder="Describa el problema reportado por el cliente..."></textarea>

                </div>



                <div class="campo" style="margin-bottom: 18px;">

                    <label>Recepción / detalles <span class="req">*</span></label>

                    <textarea id="eq_observacion" name="eq_observacion" rows="3" required placeholder="Recepción y detalles al recibir el equipo..."></textarea>

                </div>



                <div class="grid-3">

                    <div class="campo">

                        <label>Fecha Prometido <span class="req">*</span></label>

                        <input type="date" id="fecha_prometido" name="fecha_prometido" required min="{{ \Carbon\Carbon::now('America/Guayaquil')->format('Y-m-d') }}">

                    </div>

                </div>

            </div>

        </div>



        <div id="acciones-orden" class="botones hidden" style="display: flex; gap: 12px; justify-content: flex-end; width: 100%;">

            <button type="submit" id="btn-guardar" class="btn-crear">

                <i class="bi bi-file-earmark-check"></i> Crear Orden

            </button>

            <button type="button" class="btn-limpiar" onclick="document.getElementById('form-orden').reset(); actualizarMotivo(); limpiarEstadoProducto(); limpiarBadgeProducto();">

                <i class="bi bi-x-circle"></i> Limpiar

            </button>

        </div>

    </form>

</div>

</section>

@endsection



@push('js_adicional')

<script>

const _urlVerificarPreorden = '{{ route("preordenes.verificar") }}';

const _urlPreordenes = '{{ route("preordenes.index") }}';

const _urlBuscarRepuestosOrden = '{{ route("ordenes.repuestos.buscar") }}';

const _urlBuscarProductoOrden = '{{ route("ordenes.productos.buscar") }}';

let _preordenIgnorada = false;

let _preordenTimer = null;

let _repuestoTimer = null;

let _productoTimer = null;

let _productoEstado = { status: 'idle', codigo: '' };



function mostrarMensaje(isError, texto) {

    const box = document.getElementById('ord-msg');

    box.className = 'msg-box ' + (isError ? 'err' : 'ok');

    box.innerHTML = texto;

    box.style.display = 'block';

    window.scrollTo({ top: 0, behavior: 'smooth' });

}



function sincronizarModelo() {
    const modelInp = document.getElementById('eq_modelo');
    if (!modelInp) return;

    if (_productoEstado.status === 'nuevo') {
        const descripcion = (document.getElementById('producto_nuevo_descripcion')?.value || '').trim().toUpperCase();
        modelInp.value = descripcion;
        return;
    }

    if (_productoEstado.status !== 'existente') {
        modelInp.value = '';
    }
}



function toggleTecDropdown() {

    const trigger = document.getElementById('tec-trigger');

    const list    = document.getElementById('tec-dropdown-list');

    if (!trigger || !list) return;

    // Cerrar el otro dropdown si estÃƒ¡ abierto

    _cerrarDropdownEmp();

    const open = list.classList.contains('open');

    trigger.classList.toggle('open', !open);

    list.classList.toggle('open', !open);

    if (!open) {

        // Al abrir: limpiar buscador y enfocar

        const inp = list.querySelector('.tec-search-inp');

        if (inp) { inp.value = ''; filtrarTecnicos(inp, 'tec-dropdown-list'); setTimeout(() => inp.focus(), 50); }

    }

}



function toggleTecDropdownEmp() {

    const trigger = document.getElementById('tec-trigger-emp');

    const list    = document.getElementById('tec-dropdown-list-emp');

    if (!trigger || !list) return;

    // Cerrar el otro dropdown si estÃƒ¡ abierto

    _cerrarDropdownPer();

    const open = list.classList.contains('open');

    trigger.classList.toggle('open', !open);

    list.classList.toggle('open', !open);

    if (!open) {

        const inp = list.querySelector('.tec-search-inp');

        if (inp) { inp.value = ''; filtrarTecnicos(inp, 'tec-dropdown-list-emp'); setTimeout(() => inp.focus(), 50); }

    }

}



function _cerrarDropdownPer() {

    document.getElementById('tec-dropdown-list')?.classList.remove('open');

    document.getElementById('tec-trigger')?.classList.remove('open');

}

function _cerrarDropdownEmp() {

    document.getElementById('tec-dropdown-list-emp')?.classList.remove('open');

    document.getElementById('tec-trigger-emp')?.classList.remove('open');

}



// Filtrado en tiempo real por nombre

function filtrarTecnicos(inp, listId) {

    const q = (inp.value || '').toLowerCase().trim();

    const list = document.getElementById(listId);

    if (!list) return;

    const items = list.querySelectorAll('.tec-items-scroll .tec-item');

    let visibles = 0;

    items.forEach(item => {

        const nombre = (item.querySelector('.tec-item-nombre')?.textContent || '').toLowerCase();

        const mostrar = q === '' || nombre.includes(q);

        item.style.display = mostrar ? '' : 'none';

        if (mostrar) visibles++;

    });

    // Mostrar/ocultar mensaje de sin resultados

    const emptyEl = list.querySelector('.tec-search-empty');

    if (emptyEl) emptyEl.style.display = visibles === 0 ? 'block' : 'none';

}



// Cerrar dropdowns al hacer click fuera

document.addEventListener('click', function(e) {

    if (!e.target.closest('#tec-dropdown') && !e.target.closest('#tec-dropdown-emp')) {

        _cerrarDropdownPer();

        _cerrarDropdownEmp();

    }

});





function seleccionarTecnico(item, tecId, nombre, color, _etiqueta, pend, enproc, esTu = false) {

    const sel = document.getElementById('ord_tecnico_id');

    const av  = document.getElementById('tec-trigger-avatar');

    const nm  = document.getElementById('tec-trigger-nombre');

    const st  = document.getElementById('tec-trigger-stats');

    const trigger = document.getElementById('tec-trigger');

    const list    = document.getElementById('tec-dropdown-list');



    if (sel) sel.value = String(tecId);

    if (av) {

        av.style.background = color;

        av.textContent = (nombre || '?').substring(0, 1).toUpperCase();

    }

    if (nm) {

        nm.innerHTML = escHtml(nombre || '-- Seleccionar técnico --')

            + (esTu ? ' <span class="tec-trigger-yo">Tecnico</span>' : '');

    }

    if (st) st.textContent = `${pend} pend. · ${enproc} en proc.`;



    document.querySelectorAll('#tec-dropdown-list .tec-item').forEach((el) => el.classList.remove('selected'));

    if (item) item.classList.add('selected');

    if (trigger) trigger.classList.remove('open');

    if (list) list.classList.remove('open');

}



function seleccionarTecnicoEmp(item, tecId, nombre, color, _etiqueta, pend, enproc, esTu = false) {

    const sel = document.getElementById('ord_tecnico_id_empresa');

    const av  = document.getElementById('tec-trigger-avatar-emp');

    const nm  = document.getElementById('tec-trigger-nombre-emp');

    const st  = document.getElementById('tec-trigger-stats-emp');

    const trigger = document.getElementById('tec-trigger-emp');

    const list    = document.getElementById('tec-dropdown-list-emp');



    if (sel) sel.value = String(tecId);

    if (av) {

        av.style.background = color;

        av.textContent = (nombre || '?').substring(0, 1).toUpperCase();

    }

    if (nm) {

        nm.innerHTML = escHtml(nombre || '-- Seleccionar técnico --')

            + (esTu ? ' <span class="tec-trigger-yo">Tecnico</span>' : '');

    }

    if (st) st.textContent = `${pend} pend. · ${enproc} en proc.`;



    document.querySelectorAll('#tec-dropdown-list-emp .tec-item').forEach((el) => el.classList.remove('selected'));

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

        if (av) { av.style.background = '#94a3b8'; av.textContent = '?'; }

        if (nm) nm.textContent = '-- Seleccionar técnico --';

        if (st) st.textContent = '';

        return;

    }



    const item = document.querySelector(`#tec-dropdown-list .tec-item[data-tec-id="${sel.value}"]`);

    if (!item) return;

    item.click();

}



let _rucModalShowedFor = '';

async function buscarClienteAjax() {

    const iden = (document.getElementById('cli_identificacion')?.value || '').trim();

    if(!iden) { return; }



    const statusEl = document.getElementById('cli-buscar-status');

    if (statusEl) {

        statusEl.textContent = '⏳ Buscando cliente...';

        statusEl.style.color = '#2563eb';

        statusEl.style.display = 'inline-block';

    }



    try {

        const r = await fetch('{{ route("ordenes.buscar_cliente") }}?identificacion=' + iden);

        const d = await r.json();



        if(d.ok && d.cliente) {

            document.getElementById('cli_nombres').value = d.cliente.nombres;

            document.getElementById('cli_apellidos').value = d.cliente.apellidos;

            document.getElementById('cli_telefono').value = d.cliente.numero_contacto;

            document.getElementById('cli_correo').value = d.cliente.correo || '';

            document.getElementById('cli_direccion').value = d.cliente.direccion_clientes || '';

            if (d.cliente.apellidos === '.') {
                setClienteTipoVista('empresa', d.cliente.nombres);
            } else {
                setClienteTipoVista('natural');
            }
            _rucModalShowedFor = d.cliente.identificacion;

            if (statusEl) {

                statusEl.innerHTML = '<i class="bi bi-check-circle-fill"></i> Cliente encontrado';

                statusEl.style.color = '#166534';

            }

        } else {
            verificarRucTipo(iden);

            if (statusEl) {

                statusEl.innerHTML = '<i class="bi bi-info-circle-fill"></i> Cliente nuevo (registro manual)';

                statusEl.style.color = '#d97706';

            }

        }

    } catch(e) {

        if (statusEl) {

            statusEl.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Error al buscar';

            statusEl.style.color = '#ef4444';

        }

    }

}

function verificarRucTipo(identificacion) {
    const iden = (identificacion || '').trim();
    if (iden.length !== 13) {
        setClienteTipoVista('natural');
        return;
    }
    if (_rucModalShowedFor === iden) {
        return;
    }
    _rucModalShowedFor = iden;

    Swal.fire({
        title: 'Tipo de Contribuyente (RUC)',
        text: 'El número ingresado es un RUC. ¿Corresponde a una persona natural o a una empresa?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Empresa / Persona Jurídica',
        cancelButtonText: 'Persona Natural',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#4b5563',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            pedirNombreEmpresa();
        } else {
            setClienteTipoVista('natural');
        }
    });
}

function pedirNombreEmpresa() {
    Swal.fire({
        title: 'Nombre de la Empresa',
        input: 'text',
        inputLabel: 'Razón Social / Nombre Comercial',
        placeholder: 'Ingrese el nombre de la empresa...',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#2563eb',
        allowOutsideClick: false,
        allowEscapeKey: false,
        inputValidator: (value) => {
            const val = (value || '').trim();
            if (!val) {
                return 'El nombre de la empresa es requerido.';
            }
            const regex = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,\-#&()\/]+$/;
            if (!regex.test(val)) {
                return 'El nombre de la empresa contiene caracteres no válidos. Permitidos: letras, números, espacios, y signos (. , - # & ( ) /)';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const nombreEmpresa = result.value.trim().toUpperCase();
            setClienteTipoVista('empresa', nombreEmpresa);
        }
    });
}

function setClienteTipoVista(tipo, nombreEmpresa = '') {
    const hiddenTipo = document.getElementById('cli_tipo');
    const inpNombres = document.getElementById('cli_nombres');
    const inpApellidos = document.getElementById('cli_apellidos');
    
    if (!hiddenTipo || !inpNombres || !inpApellidos) return;
    
    const labelNombres = inpNombres.closest('.campo').querySelector('label');
    const wrapperApellidos = inpApellidos.closest('.campo');

    if (tipo === 'empresa') {
        hiddenTipo.value = 'empresa';
        inpNombres.value = nombreEmpresa;
        inpApellidos.value = '.';
        inpApellidos.required = false;
        
        labelNombres.innerHTML = 'Razón Social / Nombre de la Empresa <span class="req">*</span>';
        wrapperApellidos.style.display = 'none';
    } else {
        hiddenTipo.value = 'natural';
        if (inpApellidos.value === '.') {
            inpApellidos.value = '';
        }
        inpApellidos.required = true;
        
        labelNombres.innerHTML = 'Nombre <span class="req">*</span>';
        wrapperApellidos.style.display = 'block';
    }
}



function escHtml(str) {

    return (str || '').toString()

        .replace(/&/g, '&amp;')

        .replace(/</g, '&lt;')

        .replace(/>/g, '&gt;')

        .replace(/\"/g, '&quot;')

        .replace(/'/g, '&#39;');

}



function obtenerSeriePreorden() {

    const inputs = document.querySelectorAll('#series-container input[name="series[]"]');

    for (const input of inputs) {

        const valor = (input.value || '').trim();

        if (valor) return valor.toUpperCase();

    }

    return '';

}



function verificarPreorden() {

    if (_preordenIgnorada) return;



    const motivo = document.getElementById('motivo_ingreso').value;

    if (motivo === 'Servicios a Empresas') {

        ocultarAvisoPreorden();

        return;

    }



    const ci = (document.getElementById('cli_identificacion').value || '').trim();

    const codigo = (document.getElementById('producto_inventario_codigo').value || '').trim();

    const serie = obtenerSeriePreorden();



    if (!ci && !codigo && !serie) {

        ocultarAvisoPreorden();

        return;

    }



    clearTimeout(_preordenTimer);

    _preordenTimer = setTimeout(async () => {

        try {

            const params = [];

            if (ci) params.push('ci=' + encodeURIComponent(ci));

            if (codigo) params.push('codigo=' + encodeURIComponent(codigo));

            if (serie) params.push('serie=' + encodeURIComponent(serie));



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

        '<strong>Equipo:</strong> ' + escHtml((pre.tipo_producto || '-') + ' ' + (pre.marca_producto || '')) + (pre.desc_producto ? ' ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â ' + escHtml(pre.desc_producto) : '') + '<br>' +

        '<strong>Codigo:</strong> ' + escHtml(pre.codigo_producto || '-') + ' &nbsp;|&nbsp; ' +

        '<strong>Serie:</strong> ' + escHtml(pre.serie || '-') + '<br>' +

        '<strong>Factura:</strong> ' + escHtml(pre.nro_factura || '-') + ' &nbsp;|&nbsp; ' +

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



function seleccionarOpcionPorTexto(selectId, texto) {

    const select = document.getElementById(selectId);

    if (!select || !texto) return;



    const objetivo = String(texto).trim().toUpperCase();

    Array.from(select.options).some((opt) => {

        if (String(opt.value).trim().toUpperCase() === objetivo || String(opt.textContent).trim().toUpperCase() === objetivo) {

            select.value = opt.value;

            return true;

        }

        return false;

    });

}



function mostrarBadgeProducto(tipo, texto) {

    const badge = document.getElementById('prod-badge');

    if (!badge) return;



    badge.textContent = texto;

    if (tipo === 'ok') {

        badge.style.background = '#dcfce7';

        badge.style.color = '#166534';

        badge.style.border = '1px solid #86efac';

    } else {

        badge.style.background = '#fef9c3';

        badge.style.color = '#92400e';

        badge.style.border = '1px solid #fde68a';

    }

    badge.style.display = 'inline-block';

}



function limpiarBadgeProducto() {

    const badge = document.getElementById('prod-badge');

    if (badge) {

        badge.style.display = 'none';

        badge.textContent = '';

    }

}



function obtenerDescripcionProductoNuevo() {
    return (document.getElementById('producto_nuevo_descripcion')?.value || '').trim().toUpperCase();
}

function sincronizarDescripcionProductoNuevo() {
    const modelInp = document.getElementById('eq_modelo');
    if (modelInp) {
        modelInp.value = obtenerDescripcionProductoNuevo();
    }
}

function mostrarEstadoProductoNuevo(codigo, mantenerDescripcion = false) {
    const aviso = document.getElementById('producto-nuevo-aviso');
    const campo = document.getElementById('campo-producto-nuevo-descripcion');
    const input = document.getElementById('producto_nuevo_descripcion');
    const modelInp = document.getElementById('eq_modelo');

    _productoEstado = { status: 'nuevo', codigo: codigo };
    if (aviso) aviso.style.display = 'block';
    if (campo) campo.style.display = 'block';

    if (!mantenerDescripcion && input) {
        input.value = '';
    }

    if (modelInp) {
        modelInp.value = input ? input.value.trim().toUpperCase() : '';
    }
}

function ocultarEstadoProductoNuevo() {
    const aviso = document.getElementById('producto-nuevo-aviso');
    const campo = document.getElementById('campo-producto-nuevo-descripcion');
    const input = document.getElementById('producto_nuevo_descripcion');

    if (aviso) aviso.style.display = 'none';
    if (campo) campo.style.display = 'none';
    if (input) input.value = '';
}

function limpiarEstadoProducto(codigo = '') {
    _productoEstado = { status: codigo ? 'pending' : 'idle', codigo: codigo };
    ocultarEstadoProductoNuevo();
    const modelInp = document.getElementById('eq_modelo');
    if (modelInp) {
        modelInp.value = '';
    }
}

function manejarInputCodigoProducto() {
    const codigo = (document.getElementById('producto_inventario_codigo')?.value || '').trim().toUpperCase();
    _preordenIgnorada = false;
    verificarPreorden();

    if (codigo === '') {
        limpiarEstadoProducto();
        limpiarBadgeProducto();
        return;
    }

    if (_productoEstado.codigo !== codigo) {
        limpiarEstadoProducto(codigo);
        limpiarBadgeProducto();
    }
}

async function buscarProductoPorCodigo(codigo) {
    clearTimeout(_productoTimer);
    const badge = document.getElementById('prod-badge');
    const spinner = document.getElementById('prod-spinner');
    const q = (codigo || '').trim().toUpperCase();

    _preordenIgnorada = false;
    verificarPreorden();

    if (q === '') {
        limpiarEstadoProducto();
        limpiarBadgeProducto();
        return { status: 'idle', codigo: '' };
    }

    document.getElementById('producto_inventario_codigo').value = q;
    const descripcionActual = obtenerDescripcionProductoNuevo();
    _productoEstado = { status: 'pending', codigo: q };

    if (spinner) spinner.style.display = 'inline-block';
    if (badge) badge.style.display = 'none';

    try {
        const r = await fetch(_urlBuscarProductoOrden + '?codigo=' + encodeURIComponent(q), { cache: 'no-store' });
        const d = await r.json();

        if (!d.ok || !d.producto) {
            mostrarEstadoProductoNuevo(q, descripcionActual.length >= 3);
            if (descripcionActual.length >= 3) {
                document.getElementById('producto_nuevo_descripcion').value = descripcionActual;
            }
            sincronizarDescripcionProductoNuevo();
            mostrarBadgeProducto('warn', 'Codigo no registrado. Ingresa la descripcion para guardarlo al crear la orden.');
            return { status: 'nuevo', codigo: q };
        }

        const p = d.producto;
        _productoEstado = { status: 'existente', codigo: q };
        ocultarEstadoProductoNuevo();
        seleccionarOpcionPorTexto('eq_tipo', p.tipo_nombre || '');
        seleccionarOpcionPorTexto('eq_marca', p.marca || '');
        document.getElementById('eq_modelo').value = p.descripcion ? String(p.descripcion).trim().toUpperCase() : '';
        mostrarBadgeProducto('ok', 'Producto encontrado: ' + (p.descripcion || p.codigo));
        return { status: 'existente', codigo: q, producto: p };
    } catch {
        limpiarBadgeProducto();
        return { status: 'error', codigo: q };
    } finally {
        if (spinner) spinner.style.display = 'none';
    }
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

            '<span style="font-size:13px;color:#1e293b;">' + escHtml(r.nombre) + (r.descripcion ? '<span style="color:#94a3b8;font-size:11.5px;"> ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â ' + escHtml(r.descripcion) + '</span>' : '') + '</span>' +

            '<span style="background:#dcfce7;color:#166534;font-size:10.5px;padding:1px 7px;border-radius:10px;font-weight:700;">Stock: ' + (r.stock || 0) + '</span>';

        item.onclick = () => seleccionarRepuesto(r);

        lista.appendChild(item);

    });

    lista.style.display = 'block';

}



let _repuestosSeleccionados = [];



function actualizarTablaRepuestos() {

    const cuerpo = document.getElementById('tabla-repuestos-cuerpo');

    const container = document.getElementById('repuestos-seleccionados-container');

    const inputsDiv = document.getElementById('repuestos-ocultos-inputs');

    const visualResumen = document.getElementById('repuestos-lista-visual-resumen');

    const hiddenLegacy = document.getElementById('repuesto_inventario_id');



    if (!cuerpo) return;



    cuerpo.innerHTML = '';

    inputsDiv.innerHTML = '';



    if (_repuestosSeleccionados.length === 0) {

        if (container) container.style.display = 'none';

        if (visualResumen) {

            visualResumen.textContent = 'NingÃƒºn repuesto de stock seleccionado.';

            visualResumen.style.fontStyle = 'italic';

        }

        if (hiddenLegacy) hiddenLegacy.value = '';

        return;

    }



    if (container) container.style.display = 'block';



    let listadoNombres = [];

    _repuestosSeleccionados.forEach((r) => {

        // Render rows

        const tr = document.createElement('tr');

        tr.style.borderBottom = '1px solid #e2e8f0';

        tr.innerHTML = `

            <td style="padding:8px 10px; font-family:monospace; font-weight:700; color:#b45309;">${escHtml(r.codigo)}</td>

            <td style="padding:8px 10px; color:#1e293b;">${escHtml(r.nombre)}</td>

            <td style="padding:8px 10px; text-align:center;">

                <button type="button" style="background:none; border:none; color:#dc2626; font-size:15px; cursor:pointer;" onclick="eliminarRepuestoDeSeleccion(${r.id})">

                    <i class="bi bi-trash"></i>

                </button>

            </td>

        `;

        cuerpo.appendChild(tr);



        // Render hidden inputs

        const hiddenInp = document.createElement('input');

        hiddenInp.type = 'hidden';

        hiddenInp.name = 'repuestos_seleccionados[]';

        hiddenInp.value = r.id;

        inputsDiv.appendChild(hiddenInp);



        listadoNombres.push((r.codigo || '-') + ' - ' + (r.nombre || '-'));

    });



    if (visualResumen) {

        visualResumen.textContent = listadoNombres.join(' | ');

        visualResumen.style.fontStyle = 'normal';

    }



    // legacy fallback

    if (hiddenLegacy) {

        hiddenLegacy.value = _repuestosSeleccionados[0].id;

    }

}



function eliminarRepuestoDeSeleccion(id) {

    _repuestosSeleccionados = _repuestosSeleccionados.filter(x => Number(x.id) !== Number(id));

    actualizarTablaRepuestos();

}



function seleccionarRepuesto(r) {

    const lista = document.getElementById('repuesto-resultados');

    const inp = document.getElementById('inp-buscar-repuesto');



    const existe = _repuestosSeleccionados.some(x => Number(x.id) === Number(r.id));

    if (!existe) {

        _repuestosSeleccionados.push(r);

    } else {

        alert('Este repuesto ya fue seleccionado.');

    }



    actualizarTablaRepuestos();



    if (lista) lista.style.display = 'none';

    if (inp) {

        inp.value = '';

        inp.style.borderColor = '#15803d';

        inp.style.background = '#f0fdf4';

    }

}



function limpiarRepuestoSeleccionado() {

    _repuestosSeleccionados = [];

    actualizarTablaRepuestos();



    const lista = document.getElementById('repuesto-resultados');

    const inp = document.getElementById('inp-buscar-repuesto');

    if (lista) lista.style.display = 'none';

    if (inp) {

        inp.value = '';

        inp.style.borderColor = '';

        inp.style.background = '';

    }

}



function actualizarMotivo() {

    const motivoEl = document.getElementById('motivo_ingreso');

    const motivo = motivoEl ? motivoEl.value : '';



    const bloqueFacturacion = document.getElementById('bloque-facturacion');

    const bloqueGarantia = document.getElementById('bloque-garantia');

    const bloqueServicioExterno = document.getElementById('bloque-servicio-externo');

    const selectSucursal = document.getElementById('nro_sucursal_cliente');

    const tipoServicioSelect = document.getElementById('eq_tipo_servicio');

    const tipoServicioTexto = document.getElementById('tipo_servicio_texto');

    const nroFactura = document.getElementById('nro_factura');

    const fechaFacturacion = document.getElementById('fecha_facturacion');

    const garantiaTipo = document.getElementById('garantia_tipo');

    const casGarantia = document.getElementById('cas_id');

    const bloquesDependientes = document.querySelectorAll('.bloque-motivo');

    const acciones = document.getElementById('acciones-orden');

    const lockMsg = document.getElementById('motivo-lock-msg');



    const esGarantia = motivo === 'Validacion de Garantia';

    const esExterno = motivo === 'Servicio Cliente Externo';

    const esEmpresa = motivo === 'Servicios a Empresas';

    const motivoSeleccionado = motivo !== '';



    bloquesDependientes.forEach((el) => el.classList.toggle('hidden', !motivoSeleccionado || esEmpresa));

    document.querySelectorAll('.bloque-empresa-flow').forEach((el) => el.classList.toggle('hidden', !esEmpresa));

    if (acciones) acciones.classList.toggle('hidden', !motivoSeleccionado);

    if (lockMsg) lockMsg.classList.toggle('hidden', motivoSeleccionado);



    if (bloqueFacturacion) bloqueFacturacion.classList.toggle('hidden', !esGarantia || esEmpresa);

    if (bloqueGarantia) bloqueGarantia.classList.toggle('hidden', !esGarantia || esEmpresa);

    if (bloqueServicioExterno) bloqueServicioExterno.classList.toggle('hidden', !esExterno || esEmpresa);



    document.querySelectorAll('.bloque-personal input, .bloque-personal select, .bloque-personal textarea').forEach((el) => {

        el.disabled = esEmpresa;

    });

    document.querySelectorAll('.bloque-empresa-flow input, .bloque-empresa-flow select, .bloque-empresa-detalle input, .bloque-empresa-detalle select, .bloque-empresa-detalle textarea').forEach((el) => {

        el.disabled = !esEmpresa;

    });



    if (tipoServicioSelect) {

        tipoServicioSelect.disabled = esEmpresa || esExterno;

        tipoServicioSelect.required = !esEmpresa && esGarantia;

        tipoServicioSelect.style.display = esExterno ? 'none' : '';

        if (esExterno) tipoServicioSelect.value = '';

    }

    if (tipoServicioTexto) {

        tipoServicioTexto.disabled = esEmpresa || !esExterno;

        tipoServicioTexto.required = !esEmpresa && esExterno;

        tipoServicioTexto.style.display = esExterno ? '' : 'none';

        if (!esExterno) tipoServicioTexto.value = '';

    }

    if (nroFactura) nroFactura.required = !esEmpresa && esGarantia;

    if (fechaFacturacion) fechaFacturacion.required = !esEmpresa && esGarantia;

    if (selectSucursal) {

        selectSucursal.required = !esEmpresa && esGarantia;

    }

    if (garantiaTipo) {

        garantiaTipo.required = !esEmpresa && esGarantia;

        if (!esGarantia) garantiaTipo.value = '';

    }



    if (esExterno) {

        if (selectSucursal) selectSucursal.value = '999';

    }

    if (selectSucursal) selectSucursal.disabled = esEmpresa || esExterno;



    if (!esEmpresa) {

        document.querySelectorAll('input[name="subtipo_empresa"]').forEach((r) => r.checked = false);

        document.querySelectorAll('.bloque-empresa-detalle').forEach((el) => el.classList.add('hidden'));

        limpiarRequiredEmpresa();

    }



    if (!esGarantia) {

        ocultarFactura2();

        if (casGarantia) casGarantia.value = '';

    }



    actualizarGarantiaTipo();



    _preordenIgnorada = false;

    verificarPreorden();

}



function actualizarGarantiaTipo() {

    const motivo = document.getElementById('motivo_ingreso')?.value || '';

    const garantiaTipo = document.getElementById('garantia_tipo');

    const bloqueCas = document.getElementById('bloque-cas');

    const cas = document.getElementById('cas_id');

    const esGarantiaExterna = motivo === 'Validacion de Garantia' && (garantiaTipo?.value || '') === 'externa';



    if (bloqueCas) bloqueCas.classList.toggle('hidden', !esGarantiaExterna);

    if (cas) {

        cas.disabled = !esGarantiaExterna;

        cas.required = esGarantiaExterna;

        if (!esGarantiaExterna) cas.value = '';

    }

}



function limpiarRequiredEmpresa() {

    ['empresa_id','emp_tipo_servicio','emp_nro_ticket','emp_descripcion','emp_modelo','emp_tipo_equipo','emp_tipo_servicio_id','emp_marca','emp_serie','emp_falla','emp_observacion','ord_tecnico_id_empresa','emp_fecha_prometido','nro_sucursal_cliente_empresa']

        .forEach((id) => {

            const el = document.getElementById(id);

            if (el) el.required = false;

        });

}



function onEmpresaChange(val) {

    const activo = Boolean(val);

    document.querySelectorAll('input[name="subtipo_empresa"]').forEach((r) => {

        r.disabled = !activo;

        if (!activo) r.checked = false;

    });

    if (!activo) {

        document.querySelectorAll('.bloque-empresa-detalle').forEach((el) => el.classList.add('hidden'));

        limpiarRequiredEmpresa();

    }

    verificarNovisolutions();

}



function onSubtipoEmpresaChange(valor) {

    const esServicios = valor === 'Servicios';

    const esAutoconsumo = valor === 'Autoconsumo';

    const esStock = valor === 'Stock';

    const requiereEquipo = esAutoconsumo || esStock;



    document.getElementById('bloque-form-servicios-empresa').classList.toggle('hidden', !esServicios);

    document.getElementById('bloque-equipo-empresa').classList.toggle('hidden', !requiereEquipo);

    document.getElementById('bloque-asignacion-empresa').classList.toggle('hidden', !(esServicios || requiereEquipo));



    const campoSucursal = document.getElementById('campo-sucursal-cliente-empresa');

    if (campoSucursal) {

        campoSucursal.classList.toggle('hidden', !(esStock || esServicios || esAutoconsumo));

        const selectSucursal = document.getElementById('nro_sucursal_cliente_empresa');

        if (selectSucursal) {

            selectSucursal.disabled = !(esStock || esServicios || esAutoconsumo);

        }

    }



    limpiarRequiredEmpresa();

    document.getElementById('empresa_id').required = true;

    document.getElementById('ord_tecnico_id_empresa').required = requiereEquipo;

    document.getElementById('emp_fecha_prometido').required = esServicios || requiereEquipo;



    if (esServicios) {

        document.getElementById('emp_tipo_servicio').required = true;



        const inputTicket = document.getElementById('emp_nro_ticket');

        if (inputTicket && (!inputTicket.value || inputTicket.value.trim() === '')) {

            const rand = Math.random().toString(36).substring(2, 6).toUpperCase();

            const seq = Date.now().toString().slice(-4);

            inputTicket.value = `TK-${rand}-${seq}`;

        }



        document.getElementById('emp_nro_ticket').required = true;

        document.getElementById('emp_descripcion').required = true;

    }



    if (requiereEquipo) {

        document.getElementById('emp_modelo').required = true;

        document.getElementById('emp_tipo_equipo').required = true;

        document.getElementById('emp_tipo_servicio_id').required = true;

        document.getElementById('emp_marca').required = true;

        document.getElementById('emp_serie').required = false;

        document.getElementById('emp_falla').required = true;

        document.getElementById('emp_observacion').required = true;

        document.getElementById('emp_nro_ticket').value = '';



        // nro_sucursal_cliente_empresa is optional, not required

    }



    verificarNovisolutions();

}



function agregarSerieEmpresa() {

    const container = document.getElementById('series-empresa-container');

    const row = document.createElement('div');

    row.className = 'linea-item';

    row.innerHTML = `

        <input type="text" name="emp_series[]" oninput="this.value=this.value.toUpperCase()" onblur="validarInputDuplicado(this, 'serie')" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}" placeholder="Serie adicional">

        <button type="button" class="btn-mini" onclick="this.closest('.linea-item').remove()">-</button>

    `;

    container.appendChild(row);

}



let ultimoPrefijoPrompt = '';



function autoseleccionarSucursalPorFactura(valor) {

    const motivo = document.getElementById('motivo_ingreso')?.value || '';

    if (motivo !== 'Validacion de Garantia') {

        return;

    }



    const select = document.getElementById('nro_sucursal_cliente');

    if (!select) {

        return;

    }



    const digitos = String(valor || '').replace(/\D/g, '');

    if (digitos.length < 3) {

        ultimoPrefijoPrompt = '';

        return;

    }



    const prefix = digitos.substring(0, 3);

    if (prefix === ultimoPrefijoPrompt) {

        return;

    }



    const matches = Array.from(select.options).filter(opt => {

        if (!opt.value) return false;

        const optDigits = opt.value.replace(/\D/g, '').padStart(3, '0');

        return optDigits === prefix;

    });



    if (matches.length === 1) {

        ultimoPrefijoPrompt = prefix;

        select.value = matches[0].value;

        select.dispatchEvent(new Event('change', { bubbles: true }));

    } else if (matches.length > 1) {

        ultimoPrefijoPrompt = prefix;



        const inputOptions = {};

        matches.forEach(opt => {

            inputOptions[opt.value] = opt.text;

        });



        Swal.fire({

            title: 'Seleccionar Sucursal',

            text: `Se encontraron mÃƒºltiples sucursales con el nÃƒºmero ${prefix}. Por favor seleccione una:`,

            input: 'select',

            inputOptions: inputOptions,

            inputPlaceholder: '-- Seleccione la sucursal --',

            showCancelButton: true,

            confirmButtonColor: '#2563eb',

            cancelButtonColor: '#64748b',

            confirmButtonText: 'Confirmar',

            cancelButtonText: 'Cancelar',

            inputValidator: (value) => {

                if (!value) {

                    return 'Debe seleccionar una sucursal';

                }

            }

        }).then((result) => {

            if (result.isConfirmed && result.value) {

                select.value = result.value;

                select.dispatchEvent(new Event('change', { bubbles: true }));

            }

        });

    }

}



function formatearFactura(input) {

    let value = input.value.replace(/\D/g, ''); // Remover todo lo que no sea digito

    if (value.length > 15) {

        value = value.substring(0, 15);

    }



    let formatted = '';

    if (value.length > 0) {

        formatted += value.substring(0, 3);

    }

    if (value.length > 3) {

        formatted += '-' + value.substring(3, 6);

    }

    if (value.length > 6) {

        formatted += '-' + value.substring(6, 15);

    }

    input.value = formatted;

}



function onInputFactura(input) {

    formatearFactura(input);

    autoseleccionarSucursalPorFactura(input.value);

}



function mostrarFactura2() {

    const wrap = document.getElementById('wrapper-factura-2');

    const btn = document.getElementById('btn-agregar-factura-2');

    if (wrap) wrap.classList.remove('hidden');

    if (btn) btn.classList.add('hidden');

}



function ocultarFactura2() {

    const wrap = document.getElementById('wrapper-factura-2');

    const btn = document.getElementById('btn-agregar-factura-2');

    const input = document.getElementById('nro_factura_2');

    if (wrap) wrap.classList.add('hidden');

    if (btn) btn.classList.remove('hidden');

    if (input) input.value = '';

}



function buscarProductoEmpresa(codigo) {

    clearTimeout(_productoTimer);

    const badge = document.getElementById('emp-prod-badge');

    const q = (codigo || '').trim();



    if (q.length < 3) {

        if (badge) {

            badge.style.display = 'none';

            badge.textContent = '';

        }

        return;

    }



    _productoTimer = setTimeout(async () => {

        try {

            const r = await fetch(_urlBuscarProductoOrden + '?codigo=' + encodeURIComponent(q), { cache: 'no-store' });

            const d = await r.json();



            if (!d.ok || !d.producto) {

                if (badge) {

                    badge.textContent = 'Codigo no encontrado. Complete los datos y se registrara como producto nuevo.';

                    badge.style.background = '#fef9c3';

                    badge.style.color = '#92400e';

                    badge.style.border = '1px solid #fde68a';

                    badge.style.display = 'inline-block';

                }

                return;

            }



            const p = d.producto;

            seleccionarOpcionPorTexto('emp_tipo_equipo', p.tipo_nombre || '');

            seleccionarOpcionPorTexto('emp_marca', p.marca || '');

            if (badge) {

                badge.textContent = 'Producto encontrado: ' + (p.descripcion || p.codigo);

                badge.style.background = '#dcfce7';

                badge.style.color = '#166534';

                badge.style.border = '1px solid #86efac';

                badge.style.display = 'inline-block';

            }

        } catch {

            if (badge) badge.style.display = 'none';

        }

    }, 450);

}



function agregarSerie() {

    const container = document.getElementById('series-container');

    const row = document.createElement('div');

    row.className = 'linea-item';

    row.innerHTML = `

        <input type="text" name="series[]" oninput="this.value=this.value.toUpperCase()" onblur="validarInputDuplicado(this, 'serie')" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}" placeholder="Serie adicional">

        <button type="button" class="btn-mini" onclick="this.closest('.linea-item').remove()">-</button>

    `;

    container.appendChild(row);

}



function agregarCredencial() {

    const container = document.getElementById('credenciales-container');

    if (!container) return;



    const cardId = 'cred-card-' + Date.now() + Math.random().toString(36).substr(2, 9);



    const card = document.createElement('div');

    card.className = 'cred-card-item';

    card.id = cardId;

    card.style.cssText = 'background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 14px; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s;';



    card.innerHTML = `

        <!-- Inputs principales para enviar al servidor en un solo arreglo -->

        <input type="hidden" name="cred_es_patron[]" class="cred-es-patron-inp" value="0">

        <input type="hidden" name="cred_contrasena[]" class="cred-hidden-pwd-actual" value="">



        <!-- Usuario (opcional) -->

        <div class="campo" style="display: flex; flex-direction: column; gap: 4px;">

            <label style="font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em;">Usuario (opcional)</label>

            <input type="text" name="cred_usuario[]" placeholder="ej: admin" style="border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; font-size: 13px; color: #0f172a; background: #fff; font-family: inherit; outline: none; transition: border-color .2s;" onfocus="this.style.borderColor='#2563eb';" onblur="this.style.borderColor='#cbd5e1';">

        </div>



        <!-- Tipo de credencial -->

        <div class="campo" style="display: flex; flex-direction: column; gap: 6px;">

            <label style="font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em;">Tipo de credencial *</label>

            <div style="display: flex; gap: 20px; align-items: center;">

                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; cursor: pointer; color: #1e293b; user-select: none;">

                    <input type="radio" name="tipo_tipo_${cardId}" value="texto" checked onchange="toggleCredCardType('${cardId}', 'texto')" style="cursor: pointer; width: 16px; height: 16px;">

                    Texto / PIN

                </label>

                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; cursor: pointer; color: #1e293b; user-select: none;">

                    <input type="radio" name="tipo_tipo_${cardId}" value="patron" onchange="toggleCredCardType('${cardId}', 'patron')" style="cursor: pointer; width: 16px; height: 16px;">

                    Patrón de dibujo

                </label>

            </div>

        </div>



        <!-- Bloque Texto / PIN -->

        <div class="campo input-texto-wrap" id="texto-wrap-${cardId}" style="display: flex; flex-direction: column; gap: 4px;">

            <label style="font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em;">Contraseña / PIN *</label>

            <div style="display: flex; gap: 10px; align-items: center; width: 100%;">

                <input type="text" class="pwd-text-visual" placeholder="PIN o contraseña" style="flex: 1; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; font-size: 13px; color: #0f172a; background: #fff; outline: none; transition: border-color .2s;" oninput="sincronizarPwdActual('${cardId}', this.value)" onfocus="this.style.borderColor='#2563eb';" onblur="this.style.borderColor='#cbd5e1';">

                <button type="button" onclick="eliminarCredencialCard('${cardId}')" style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #dc2626; font-size: 16px; transition: all 0.15s;" onmouseover="this.style.background='#fca5a5'; this.style.color='#fff';" onmouseout="this.style.background='#fee2e2'; this.style.color='#dc2626';" title="Eliminar credencial">

                    <i class="bi bi-trash"></i>

                </button>

            </div>

        </div>



        <!-- Bloque Patrón de dibujo -->

        <div class="campo input-patron-wrap" id="patron-wrap-${cardId}" style="display: none; flex-direction: column; gap: 8px;">

            <label style="font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em;">Dibuja el patrón</label>



            <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-start;">

                <div style="position: relative; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">

                    <canvas class="patron-canvas" id="canvas-${cardId}" width="180" height="180" style="display: block; cursor: crosshair; touch-action: none; background: #fff; border-radius: 8px;"></canvas>

                </div>



                <div style="display: flex; align-items: center; gap: 15px; width: 100%;">

                    <button type="button" class="btn-limpiar-patron" onclick="clearPatronLock('${cardId}')" style="background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s;" onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">

                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar

                    </button>

                    <span class="status-patron" id="status-${cardId}" style="font-size: 12.5px; font-weight: 700; color: #64748b; display: flex; align-items: center; gap: 4px;">

                        <i class="bi bi-info-circle"></i> Sin dibujar

                    </span>

                </div>

            </div>



            <!-- Botón Eliminar abajo estilo mockup 2 (barra roja con tacho centrado) -->

            <div style="margin-top: 6px; border-top: 1px dashed #cbd5e1; padding-top: 12px; width: 100%;">

                <button type="button" onclick="eliminarCredencialCard('${cardId}')" style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; width: 100%; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #dc2626; font-size: 16px; transition: all 0.15s;" onmouseover="this.style.background='#fca5a5'; this.style.color='#fff';" onmouseout="this.style.background='#fee2e2'; this.style.color='#dc2626';" title="Eliminar credencial">

                    <i class="bi bi-trash"></i>

                </button>

            </div>

        </div>

    `;



    container.appendChild(card);



    // Inicializar el canvas de patrón para esta tarjeta

    initPatternLock(cardId);

}



function toggleCredCardType(cardId, type) {

    const textoWrap = document.getElementById('texto-wrap-' + cardId);

    const patronWrap = document.getElementById('patron-wrap-' + cardId);

    const isPatronInp = document.querySelector('#' + cardId + ' .cred-es-patron-inp');

    const hiddenPwdInp = document.querySelector('#' + cardId + ' .cred-hidden-pwd-actual');



    if (type === 'patron') {

        if (textoWrap) textoWrap.style.display = 'none';

        if (patronWrap) patronWrap.style.display = 'flex';

        if (isPatronInp) isPatronInp.value = '1';



        // Reset or initialize to the canvas pattern lock state

        if (window.patternLocks && window.patternLocks[cardId]) {

            window.patternLocks[cardId].clear();

        }

    } else {

        if (textoWrap) textoWrap.style.display = 'flex';

        if (patronWrap) patronWrap.style.display = 'none';

        if (isPatronInp) isPatronInp.value = '0';



        // Sincronizar con el valor visual de texto

        const textVal = document.querySelector('#' + cardId + ' .pwd-text-visual')?.value || '';

        if (hiddenPwdInp) hiddenPwdInp.value = textVal;

    }

}



function sincronizarPwdActual(cardId, val) {

    const isPatron = document.querySelector('#' + cardId + ' .cred-es-patron-inp')?.value === '1';

    if (!isPatron) {

        const hiddenPwdInp = document.querySelector('#' + cardId + ' .cred-hidden-pwd-actual');

        if (hiddenPwdInp) hiddenPwdInp.value = val;

    }

}



function clearPatronLock(cardId) {

    if (window.patternLocks && window.patternLocks[cardId]) {

        window.patternLocks[cardId].clear();

    }

}



function eliminarCredencialCard(cardId) {

    const card = document.getElementById(cardId);

    if (card) {

        card.remove();

    }

    // Clean up locks

    if (window.patternLocks && window.patternLocks[cardId]) {

        delete window.patternLocks[cardId];

    }

}



function drawPattern(canvasId, selectedDots, currentPos) {

    const canvas = document.getElementById(canvasId);

    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    ctx.clearRect(0, 0, canvas.width, canvas.height);



    // Coordenadas fijas para la grilla de 3x3

    const dots = [

        {x: 30, y: 30}, {x: 90, y: 30}, {x: 150, y: 30},

        {x: 30, y: 90}, {x: 90, y: 90}, {x: 150, y: 90},

        {x: 30, y: 150}, {x: 90, y: 150}, {x: 150, y: 150}

    ];



    // 1. Dibujar líneas conectadas

    if (selectedDots.length > 0) {

        ctx.beginPath();

        ctx.strokeStyle = '#2563eb'; // Elegante azul premium

        ctx.lineWidth = 4;

        ctx.lineCap = 'round';

        ctx.lineJoin = 'round';

        ctx.moveTo(dots[selectedDots[0]].x, dots[selectedDots[0]].y);

        for (let i = 1; i < selectedDots.length; i++) {

            ctx.lineTo(dots[selectedDots[i]].x, dots[selectedDots[i]].y);

        }

        if (currentPos) {

            ctx.lineTo(currentPos.x, currentPos.y);

        }

        ctx.stroke();

    }



    // 2. Dibujar puntos de la grilla

    dots.forEach((dot, idx) => {

        const isSelected = selectedDots.includes(idx);

        ctx.beginPath();

        if (isSelected) {

            // Anillo exterior translÃƒºcido

            ctx.arc(dot.x, dot.y, 16, 0, Math.PI * 2);

            ctx.fillStyle = 'rgba(37, 99, 235, 0.15)';

            ctx.fill();



            // Punto interior activo

            ctx.beginPath();

            ctx.arc(dot.x, dot.y, 8, 0, Math.PI * 2);

            ctx.fillStyle = '#2563eb';

            ctx.fill();

        } else {

            // Punto inactivo normal

            ctx.arc(dot.x, dot.y, 6, 0, Math.PI * 2);

            ctx.fillStyle = '#94a3b8';

            ctx.fill();

        }

    });

}



function initPatternLock(cardId) {

    const canvas = document.getElementById('canvas-' + cardId);

    if (!canvas) return;



    let selectedDots = [];

    let isDrawing = false;



    const dots = [

        {x: 30, y: 30}, {x: 90, y: 30}, {x: 150, y: 30},

        {x: 30, y: 90}, {x: 90, y: 90}, {x: 150, y: 90},

        {x: 30, y: 150}, {x: 90, y: 150}, {x: 150, y: 150}

    ];



    // Renderizado del estado inicial vacío

    drawPattern('canvas-' + cardId, [], null);



    function getCanvasCoords(canvas, event) {

        const rect = canvas.getBoundingClientRect();

        let clientX, clientY;

        if (event.touches && event.touches.length > 0) {

            clientX = event.touches[0].clientX;

            clientY = event.touches[0].clientY;

        } else {

            clientX = event.clientX;

            clientY = event.clientY;

        }

        return {

            x: clientX - rect.left,

            y: clientY - rect.top

        };

    }



    function getClosestDot(pos) {

        const hitRadius = 22;

        for (let i = 0; i < dots.length; i++) {

            const dx = pos.x - dots[i].x;

            const dy = pos.y - dots[i].y;

            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < hitRadius) {

                return i;

            }

        }

        return -1;

    }



    function handleStart(e) {

        e.preventDefault();

        const pos = getCanvasCoords(canvas, e);

        const dotIdx = getClosestDot(pos);

        if (dotIdx !== -1) {

            isDrawing = true;

            selectedDots = [dotIdx];

            drawPattern('canvas-' + cardId, selectedDots, pos);

            if (navigator.vibrate) navigator.vibrate(10);

        }

    }



    function handleMove(e) {

        if (!isDrawing) return;

        e.preventDefault();

        const pos = getCanvasCoords(canvas, e);

        const dotIdx = getClosestDot(pos);

        if (dotIdx !== -1 && !selectedDots.includes(dotIdx)) {

            selectedDots.push(dotIdx);

            if (navigator.vibrate) navigator.vibrate(10);

        }

        drawPattern('canvas-' + cardId, selectedDots, pos);

    }



    function handleEnd(e) {

        if (!isDrawing) return;

        isDrawing = false;



        if (selectedDots.length > 1) {

            drawPattern('canvas-' + cardId, selectedDots, null);

            const base64 = canvas.toDataURL('image/png');

            document.querySelector('#' + cardId + ' .cred-hidden-pwd-actual').value = base64;



            const statusEl = document.getElementById('status-' + cardId);

            if (statusEl) {

                statusEl.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#10b981;"></i> Patrón dibujado';

                statusEl.style.color = '#10b981';

            }

        } else {

            clearPatronLock(cardId);

        }

    }



    canvas.addEventListener('mousedown', handleStart);

    canvas.addEventListener('mousemove', handleMove);



    // MouseUp se añade al window para robustez en arrastres fuera del canvas

    window.addEventListener('mouseup', handleEnd);



    canvas.addEventListener('touchstart', handleStart);

    canvas.addEventListener('touchmove', handleMove);

    canvas.addEventListener('touchend', handleEnd);



    window.patternLocks = window.patternLocks || {};

    window.patternLocks[cardId] = {

        clear: function() {

            selectedDots = [];

            isDrawing = false;

            drawPattern('canvas-' + cardId, [], null);

            document.querySelector('#' + cardId + ' .cred-hidden-pwd-actual').value = '';

            const statusEl = document.getElementById('status-' + cardId);

            if (statusEl) {

                statusEl.innerHTML = '<i class="bi bi-check-circle"></i> Sin dibujar';

                statusEl.style.color = '#64748b';

            }

        }

    };

}



async function guardarOrden() {
    const modelInp = document.getElementById('eq_modelo');
    const cod = (document.getElementById('producto_inventario_codigo')?.value || '').trim().toUpperCase();
    const motivo = document.getElementById('motivo_ingreso')?.value || '';
    const isEmpresa = motivo === 'Servicios a Empresas';

    if (!isEmpresa && cod !== '') {
        const resultadoProducto = await buscarProductoPorCodigo(cod);
        if (resultadoProducto.status === 'nuevo') {
            const descripcionNueva = obtenerDescripcionProductoNuevo();
            if (descripcionNueva.length < 3) {
                mostrarMensaje(true, 'Este codigo no esta registrado. Ingresa una descripcion valida del equipo antes de crear la orden.');
                document.getElementById('producto_nuevo_descripcion')?.focus();
                return;
            }
            if (modelInp) {
                modelInp.value = descripcionNueva;
            }
        }
    }

    if (modelInp && !modelInp.value) {
        modelInp.value = cod || '';
    }

    const form = document.getElementById('form-orden');
    const fd = new FormData(form);

    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    // ── VALIDACIÓN DE ÓRDENES DUPLICADAS ──
    try {
        let series = [];
        let facturas = [];
        
        if (isEmpresa) {
            const empSeriesEls = document.querySelectorAll('input[name="emp_series[]"]');
            empSeriesEls.forEach(el => {
                const val = el.value.trim();
                if (val) series.push(val);
            });
            const ticketVal = document.getElementById('emp_nro_ticket')?.value.trim();
            if (ticketVal) facturas.push(ticketVal);
        } else {
            const personalSeriesEls = document.querySelectorAll('input[name="series[]"]');
            personalSeriesEls.forEach(el => {
                const val = el.value.trim();
                if (val) series.push(val);
            });
            
            const fac1 = document.getElementById('nro_factura')?.value.trim();
            if (fac1) facturas.push(fac1);
            const fac2 = document.getElementById('nro_factura_2')?.value.trim();
            if (fac2) facturas.push(fac2);
        }
        
        // Ignorar sn, s/n y ya aprobados
        series = series.filter(s => {
            const lower = s.toLowerCase();
            return lower !== '' && lower !== 'sn' && lower !== 's/n' && !_duplicadosAprobados.has(lower);
        });
        facturas = facturas.filter(f => {
            const lower = f.toLowerCase();
            return f !== '' && !_duplicadosAprobados.has(lower);
        });
        
        if (series.length > 0 || facturas.length > 0) {
            const responseCheck = await fetch('{{ route("ordenes.verificar_duplicado") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ series, facturas })
            });
            
            if (responseCheck.ok) {
                const dupCheck = await responseCheck.json();
                if (dupCheck.duplicated) {
                    const count = dupCheck.coincidencias.length;
                    const ordinalIngreso = count + 1;
                    
                    let tableRows = '';
                    dupCheck.coincidencias.forEach(c => {
                        tableRows += `
                            <tr>
                                <td style="border:1px solid #cbd5e1; padding:6px; font-weight:700;">${c.nro_orden}</td>
                                <td style="border:1px solid #cbd5e1; padding:6px;">${c.fecha_ingreso || '-'}</td>
                                <td style="border:1px solid #cbd5e1; padding:6px;">${c.tecnico_ingreso || '-'}</td>
                                <td style="border:1px solid #cbd5e1; padding:6px;">${c.tecnico_asignado || '-'}</td>
                            </tr>
                        `;
                    });
                    
                    const tableHtml = `
                        <div style="margin-top:14px; text-align:left; font-size:12px; font-family:inherit;">
                            <p style="margin-bottom:8px; font-weight:700; color:#dc2626;">Se encontraron ${count} orden(es) previa(s) con la misma Serie o Factura/Ticket:</p>
                            <table style="width:100%; border-collapse:collapse; text-align:left; margin-bottom:12px;">
                                <thead>
                                    <tr style="background:#f1f5f9;">
                                        <th style="border:1px solid #cbd5e1; padding:6px;">Orden</th>
                                        <th style="border:1px solid #cbd5e1; padding:6px;">Fecha</th>
                                        <th style="border:1px solid #cbd5e1; padding:6px;">Ingresó</th>
                                        <th style="border:1px solid #cbd5e1; padding:6px;">Asignado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tableRows}
                                </tbody>
                            </table>
                            <p>¿Desea ingresar esta orden como el <strong>Ingreso #${ordinalIngreso}</strong> para este equipo/serie?</p>
                        </div>
                    `;
                    
                    const result = await Swal.fire({
                        title: '¡Serie o Factura Duplicada!',
                        html: tableHtml,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: `Sí, registrar como Ingreso #${ordinalIngreso}`,
                        cancelButtonText: 'No, cancelar',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#dc2626',
                        width: '600px',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                    
                    if (!result.isConfirmed) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-file-earmark-check"></i> Crear Orden';
                        return;
                    }
                }
            }
        }
    } catch (e) {
        console.error('Error al verificar duplicados:', e);
    }

    try {
        const r = await fetch('{{ route("ordenes.store") }}', {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const contentType = r.headers.get('content-type') || '';
        if (!r.ok) {
            let errorText = '';
            try {
                errorText = await r.text();
            } catch (_) {}

            console.error('Response error details:', r.status, errorText);

            if (r.status === 419) {
                mostrarMensaje(true, 'La sesión ha expirado (Error 419). Por favor, recarga la página e intenta de nuevo.');
                return;
            }

            if (contentType.includes('application/json')) {
                try {
                    const errorJson = JSON.parse(errorText);
                    mostrarMensaje(true, errorJson.error || `Error del servidor (${r.status}): ${r.statusText}`);
                    return;
                } catch (_) {}
            }

            const titleMatch = errorText.match(/<title>(.*?)<\/title>/i);
            const title = titleMatch ? titleMatch[1] : '';
            mostrarMensaje(true, `Error del servidor (${r.status})${title ? ': ' + title : ''}. Por favor, contacte a soporte.`);
            return;
        }

        if (!contentType.includes('application/json')) {
            const responseText = await r.text();
            console.error('Expected JSON but received:', responseText.substring(0, 500));
            mostrarMensaje(true, 'El servidor retornó una respuesta inesperada (no JSON).');
            return;
        }

        const d = await r.json();

        if(d.ok) {
            const urlImprimirOrden = @json(route('ordenes.imprimir', ['id' => '__ID__']));
            const urlImprimirEmpresa = @json(route('ordenes_empresa.imprimir', ['id' => '__ID__']));
            const linkImprimir = d.tipo_orden === 'empresa'
                ? `<br><br> <a href="${urlImprimirEmpresa.replace('__ID__', encodeURIComponent(d.orden_id))}" target="_blank" style="color:#166534; text-decoration:underline;">Imprimir Comprobante</a>`
                : `<br><br> <a href="${urlImprimirOrden.replace('__ID__', encodeURIComponent(d.orden_id))}" target="_blank" style="color:#166534; text-decoration:underline;">Imprimir Comprobante</a>`;
            mostrarMensaje(false, `<strong>Exito!</strong> ${d.mensaje}${linkImprimir}`);
            document.getElementById('form-orden').reset();
            actualizarMotivo();
            _preordenIgnorada = false;
            ocultarAvisoPreorden();
            onEstadoRepuestoChange(document.getElementById('estado_repuesto').value || 'No requerido');
            limpiarRepuestoSeleccionado();
            limpiarEstadoProducto();
            limpiarBadgeProducto();
            sincronizarTecnicoDesdeSelect();
        } else {
            mostrarMensaje(true, d.error);
        }
    } catch(e) {
        console.error('Connection/JavaScript critical error:', e);
        mostrarMensaje(true, 'Ocurrió un error crítico de conexión o de red: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-file-earmark-check"></i> Crear Orden';
    }
}



document.addEventListener('DOMContentLoaded', () => {

    actualizarMotivo();

    onEstadoRepuestoChange(document.getElementById('estado_repuesto').value || 'No requerido');

    sincronizarTecnicoDesdeSelect();



    // Configurar validaciones dinÃƒ¡micas

    setupDynamicValidation(document.getElementById('cli_identificacion'), EcuadorianValidator.validarIdentificacion, (v) => {

        if (v.length === 0) return 'La identificación es requerida.';

        if (/[^a-zA-Z0-9]/.test(v)) return 'La identificación sólo debe contener letras y nÃƒºmeros.';

        return 'Debe ser una cédula (10 dígitos), RUC (13 dígitos) de Ecuador, o un pasaporte vÃƒ¡lido (5 a 20 caracteres alfanuméricos).';

    });



    setupDynamicValidation(document.getElementById('cli_telefono'), EcuadorianValidator.validarTelefono, (v) => {

        if (v.length === 0) return 'El teléfono es requerido.';

        if (/[^\d]/.test(v)) return 'El teléfono sólo debe contener nÃƒºmeros.';

        return 'El teléfono debe ser un celular de 10 dígitos (ej: 0987654321) o convencional de 9 dígitos (ej: 022345678) de Ecuador.';

    });



    setupDynamicValidation(document.getElementById('cli_correo'), EcuadorianValidator.validarEmail, (v) => {

        return 'El correo electrónico no tiene un formato vÃƒ¡lido.';

    });



    const inpCi = document.getElementById('cli_identificacion');

    const inpCod = document.getElementById('producto_inventario_codigo');

    const seriesContainer = document.getElementById('series-container');

    if (inpCi) {

        inpCi.addEventListener('input', () => {

            _preordenIgnorada = false;

            verificarPreorden();



            const val = inpCi.value.trim();

            if (val === '') {

                document.getElementById('cli_nombres').value = '';

                document.getElementById('cli_apellidos').value = '';

                document.getElementById('cli_telefono').value = '';

                document.getElementById('cli_correo').value = '';

                document.getElementById('cli_direccion').value = '';

                const statusEl = document.getElementById('cli-buscar-status');

                if (statusEl) {

                    statusEl.style.display = 'none';

                    statusEl.textContent = '';

                }

            } else if (val.length === 10 || val.length === 13) {

                buscarClienteAjax();

            }

        });

        inpCi.addEventListener('blur', buscarClienteAjax);

        inpCi.addEventListener('keydown', (e) => {

            if (e.key === 'Enter') {

                e.preventDefault();

                buscarClienteAjax();

            }

        });

    }

    if (inpCod) {
        inpCod.addEventListener('input', manejarInputCodigoProducto);
        inpCod.addEventListener('blur', () => buscarProductoPorCodigo(inpCod.value));
        inpCod.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProductoPorCodigo(inpCod.value);
            }
        });
    }

    if (seriesContainer) {

        seriesContainer.addEventListener('input', (event) => {

            if (!event.target || event.target.name !== 'series[]') return;

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



    // Event listeners para NOVISOLUTIONS

    const valorHoraInput = document.getElementById('valor_hora');

    const horasInput = document.getElementById('horas_trabajadas');

    if (valorHoraInput) valorHoraInput.addEventListener('input', calcularPrecioNovisolutions);

    if (horasInput) horasInput.addEventListener('input', calcularPrecioNovisolutions);



    document.querySelectorAll('.chk-tecnico-emp').forEach(chk => {

        chk.addEventListener('change', () => {

            const checked = document.querySelectorAll('.chk-tecnico-emp:checked');

            if (checked.length > 5) {

                chk.checked = false;

                alert('Puedes asignar un maximo de 5 tecnicos.');

            }

            actualizarTecnicoEncargadoEmpresa();

            calcularPrecioNovisolutions();

        });

    });

    actualizarTecnicoEncargadoEmpresa();

});



function verificarNovisolutions() {

    const selectEmpresa = document.getElementById('empresa_id');

    if (!selectEmpresa) return;



    const subtipoRadio = document.querySelector('input[name="subtipo_empresa"]:checked');

    const subtipo = subtipoRadio ? subtipoRadio.value : '';

    const esServicioEmpresa = subtipo === 'Servicios';

    const requiereEquipo = subtipo === 'Autoconsumo' || subtipo === 'Stock';



    const bloqueMultiTecnicos = document.getElementById('bloque-multi-tecnicos');

    const defaultTecnicoDropdown = document.getElementById('tec-dropdown-emp')?.parentElement;



    if (bloqueMultiTecnicos && defaultTecnicoDropdown) {

        if (esServicioEmpresa) {

            bloqueMultiTecnicos.classList.remove('hidden');

            defaultTecnicoDropdown.classList.add('hidden');

            document.getElementById('ord_tecnico_id_empresa').disabled = true;

            document.getElementById('ord_tecnico_id_empresa').required = false;

            document.querySelectorAll('.chk-tecnico-emp').forEach(chk => chk.disabled = false);

            actualizarTecnicoEncargadoEmpresa();

        } else {

            bloqueMultiTecnicos.classList.add('hidden');

            defaultTecnicoDropdown.classList.remove('hidden');

            document.getElementById('ord_tecnico_id_empresa').disabled = false;

            document.querySelectorAll('.chk-tecnico-emp').forEach(chk => {

                chk.disabled = true;

                chk.checked = false;

            });

            const encargado = document.getElementById('tecnico_encargado');

            if (encargado) {

                encargado.innerHTML = '<option value="">-- Seleccione al encargado --</option>';

                encargado.value = '';

                encargado.disabled = true;

            }

        }

    }



    const bloqueCasEmpresa = document.getElementById('bloque-cas-empresa');

    if (bloqueCasEmpresa) {

        if (requiereEquipo) {

            bloqueCasEmpresa.classList.remove('hidden');

            document.getElementById('cas_id_empresa').disabled = false;

        } else {

            bloqueCasEmpresa.classList.add('hidden');

            document.getElementById('cas_id_empresa').disabled = true;

            document.getElementById('cas_id_empresa').value = '';

        }

    }



    const bloqueCalculo = document.getElementById('bloque-calculo-novisolutions');

    if (bloqueCalculo) {

        if (esServicioEmpresa) {

            bloqueCalculo.classList.remove('hidden');

            document.getElementById('valor_hora').disabled = false;

            document.getElementById('horas_trabajadas').disabled = false;

        } else {

            bloqueCalculo.classList.add('hidden');

            document.getElementById('valor_hora').disabled = true;

            document.getElementById('horas_trabajadas').disabled = true;

        }

    }



    calcularPrecioNovisolutions();

}



function actualizarTecnicoEncargadoEmpresa() {

    const select = document.getElementById('tecnico_encargado');

    if (!select) return;



    const seleccionados = Array.from(document.querySelectorAll('.chk-tecnico-emp:checked'));

    const valorActual = select.value;



    select.innerHTML = '<option value="">-- Seleccione al encargado --</option>';



    seleccionados.forEach((chk) => {

        const option = document.createElement('option');

        option.value = chk.value;

        option.textContent = chk.dataset.nombre || chk.closest('label')?.textContent?.trim() || `Tecnico ${chk.value}`;

        select.appendChild(option);

    });



    select.disabled = seleccionados.length === 0;



    if (seleccionados.length === 0) {

        select.value = '';

        return;

    }



    const existeActual = seleccionados.some((chk) => chk.value === valorActual);

    select.value = existeActual ? valorActual : seleccionados[0].value;

}



function calcularPrecioNovisolutions() {

    const chks = document.querySelectorAll('.chk-tecnico-emp:checked');

    const numTecnicos = chks.length;



    const valorHoraInput = document.getElementById('valor_hora');

    const horasInput = document.getElementById('horas_trabajadas');

    if (!valorHoraInput || !horasInput) return;



    const valorHora = parseFloat(valorHoraInput.value) || 0;

    const horas = parseFloat(horasInput.value) || 0;

    const total = numTecnicos * horas * valorHora;



    const formulaLbl = document.getElementById('formula-lbl');

    const totalLbl = document.getElementById('cobro-total-lbl');



    if (formulaLbl) {

        formulaLbl.textContent = `${numTecnicos} tecnico(s) * ${horas.toFixed(2)} hora(s) * $${valorHora.toFixed(2)}/hr`;

    }

    if (totalLbl) {

        totalLbl.textContent = `$${total.toFixed(2)}`;

    }

}

let _duplicadosAprobados = new Set();

async function validarInputDuplicado(inputElement, tipo) {
    const val = (inputElement.value || '').trim();
    if (!val) return;

    const lowerVal = val.toLowerCase();
    if (tipo === 'serie' && (lowerVal === 'sn' || lowerVal === 's/n')) {
        return;
    }

    if (_duplicadosAprobados.has(lowerVal)) {
        return;
    }

    try {
        const payload = {};
        if (tipo === 'serie') {
            payload.series = [val];
            payload.facturas = [];
        } else {
            payload.series = [];
            payload.facturas = [val];
        }

        const responseCheck = await fetch('{{ route("ordenes.verificar_duplicado") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        if (responseCheck.ok) {
            const dupCheck = await responseCheck.json();
            if (dupCheck.duplicated) {
                const count = dupCheck.coincidencias.length;
                const ordinalIngreso = count + 1;
                
                let tableRows = '';
                dupCheck.coincidencias.forEach(c => {
                    tableRows += `
                        <tr>
                            <td style="border:1px solid #cbd5e1; padding:6px; font-weight:700;">${c.nro_orden}</td>
                            <td style="border:1px solid #cbd5e1; padding:6px;">${c.fecha_ingreso || '-'}</td>
                            <td style="border:1px solid #cbd5e1; padding:6px;">${c.tecnico_ingreso || '-'}</td>
                            <td style="border:1px solid #cbd5e1; padding:6px;">${c.tecnico_asignado || '-'}</td>
                        </tr>
                    `;
                });
                
                const tableHtml = `
                    <div style="margin-top:14px; text-align:left; font-size:12px; font-family:inherit;">
                        <p style="margin-bottom:8px; font-weight:700; color:#dc2626;">Se encontraron ${count} orden(es) previa(s) con la misma ${tipo === 'serie' ? 'Serie' : 'Factura/Ticket'}:</p>
                        <table style="width:100%; border-collapse:collapse; text-align:left; margin-bottom:12px;">
                            <thead>
                                <tr style="background:#f1f5f9;">
                                    <th style="border:1px solid #cbd5e1; padding:6px;">Orden</th>
                                    <th style="border:1px solid #cbd5e1; padding:6px;">Fecha</th>
                                    <th style="border:1px solid #cbd5e1; padding:6px;">Ingresó</th>
                                    <th style="border:1px solid #cbd5e1; padding:6px;">Asignado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                        <p>¿Desea ingresar esta orden como el <strong>Ingreso #${ordinalIngreso}</strong> para este equipo/serie?</p>
                    </div>
                `;
                
                const result = await Swal.fire({
                    title: `¡${tipo === 'serie' ? 'Serie' : 'Factura/Ticket'} Duplicada!`,
                    html: tableHtml,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: `Sí, registrar como Ingreso #${ordinalIngreso}`,
                    cancelButtonText: 'No, cancelar',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#dc2626',
                    width: '600px',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
                
                if (result.isConfirmed) {
                    _duplicadosAprobados.add(lowerVal);
                } else {
                    inputElement.value = '';
                    inputElement.focus();
                }
            }
        }
    } catch (e) {
        console.error('Error al verificar duplicado:', e);
    }
}

</script>

@endpush



















