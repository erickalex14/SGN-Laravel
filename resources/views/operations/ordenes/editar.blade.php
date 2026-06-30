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
    $eqSeries = $orden->equipo ? $orden->equipo->series()->orderBy('orden')->get() : collect();
    $cantidadSeries = $eqSeries->count() ?: 1;
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
            <div class="eo-meta-item"><label>Cantidad</label><span id="overview-cantidad">{{ $cantidadSeries }}</span></div>
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
        <input type="hidden" id="cli_tipo" value="{{ $orden->cliente->apellidos === '.' ? 'empresa' : 'natural' }}">

        <!-- Datos del Cliente -->
        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-person"></i> Datos del Cliente</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>C.I / RUC <span class="req">*</span></label>
                        <input type="text" id="cli_identificacion" value="{{ $orden->cliente->identificacion }}" required>
                    </div>
                    <div class="campo">
                        <label>Nombre <span class="req">*</span></label>
                        <input type="text" id="cli_nombres" value="{{ $orden->cliente->nombres }}" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Apellido <span class="req">*</span></label>
                        <input type="text" id="cli_apellidos" value="{{ $orden->cliente->apellidos }}" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Teléfono <span class="req">*</span></label>
                        <input type="text" id="cli_telefono" value="{{ $orden->cliente->numero_contacto }}" required>
                    </div>
                    <div class="campo">
                        <label>Correo Electrónico</label>
                        <input type="email" id="cli_correo" value="{{ $orden->cliente->correo }}">
                    </div>
                    <div class="campo">
                        <label>Dirección</label>
                        <input type="text" id="cli_direccion" value="{{ $orden->cliente->direccion_clientes }}" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos y Series del Equipo -->
        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-laptop"></i> Datos y Series del Equipo</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Tipo de Equipo <span class="req">*</span></label>
                        <select id="eq_tipo" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($tiposDispositivo as $tipo)
                                <option value="{{ $tipo->nombre }}" {{ ($orden->equipo->tipo ?? '') === $tipo->nombre ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Marca <span class="req">*</span></label>
                        <select id="eq_marca" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->nombre }}" {{ ($orden->equipo->marca ?? '') === $marca->nombre ? 'selected' : '' }}>
                                    {{ $marca->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label>Modelo</label>
                        <input type="text" id="eq_modelo" value="{{ $orden->equipo->modelo ?? '' }}" oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Contraseña / PIN del Equipo</label>
                        <input type="text" id="eq_contrasena" value="{{ $orden->equipo->contrasena_equipo ?? '' }}">
                    </div>
                    <div class="campo">
                        <label>Cantidad</label>
                        <input type="text" id="eq_cantidad" value="{{ $cantidadSeries }}" readonly style="background:#f1f5f9; cursor:not-allowed; font-weight:700;">
                    </div>
                </div>

                <div class="campo" style="margin-top:16px;">
                    <label>Series del Equipo <span class="req">*</span></label>
                    <div id="series-container" style="display:flex; flex-direction:column; gap:8px;">
                        @php
                            // ya cargado al inicio
                        @endphp
                        @if($eqSeries->isEmpty())
                            <div class="linea-item" style="display:flex; gap:10px;">
                                <input type="text" name="series[]" value="{{ $orden->equipo->serie ?? '' }}" oninput="this.value=this.value.toUpperCase()" placeholder="Serie Principal" style="flex:1;">
                            </div>
                        @else
                            @foreach($eqSeries as $index => $es)
                                <div class="linea-item" style="display:flex; gap:10px;">
                                    <input type="text" name="series[]" value="{{ $es->serie }}" oninput="this.value=this.value.toUpperCase()" placeholder="{{ $index === 0 ? 'Serie Principal' : 'Serie Adicional' }}" style="flex:1;">
                                    @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-danger btn-mini" style="background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; border-radius:8px; width:36px; height:36px;" onclick="this.closest('.linea-item').remove(); actualizarCantidad();">-</button>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm" style="margin-top:10px; background:#f1f5f9; border:1.5px solid #cbd5e1; color:#475569; font-weight:700; border-radius:8px; padding:6px 12px; cursor:pointer; width:auto; display:inline-block;" onclick="agregarSerie()">+ Agregar Serie Adicional</button>
                </div>
            </div>
        </div>

        <!-- Datos de Factura (Garantía) -->
        <div class="seccion-form" id="bloque-facturacion" style="display: {{ $orden->motivo_ingreso === 'Validacion de Garantia' ? 'block' : 'none' }};">
            <div class="seccion-hdr"><i class="bi bi-receipt"></i> Datos de Facturación (Garantía)</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Nro. Factura <span class="req">*</span></label>
                        <input type="text" id="nro_factura" value="{{ $orden->nro_factura ?? '' }}" oninput="onInputFactura(this)" placeholder="000-000-000000000">
                    </div>
                    <div class="campo">
                        <label>Nro. Factura 2 (Opcional)</label>
                        <input type="text" id="nro_factura_2" value="{{ $orden->nro_factura_2 ?? '' }}" oninput="formatearFactura(this)" placeholder="000-000-000000000">
                    </div>
                    <div class="campo">
                        <label>Fecha de Facturación <span class="req">*</span></label>
                        <input type="date" id="fecha_facturacion" value="{{ ($orden->fecha_facturacion ?? $orden->equipo->fecha_facturacion ?? '') ? \Carbon\Carbon::parse($orden->fecha_facturacion ?? $orden->equipo->fecha_facturacion)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="campo">
                        <label>Sucursal del Cliente (Novicompu) <span class="req">*</span></label>
                        <select id="nro_sucursal_cliente">
                            <option value="">-- Seleccionar Sucursal --</option>
                            @foreach($sucursalesCliente as $suc)
                                <option value="{{ $suc->codigo }}" {{ $orden->nro_sucursal_cliente == $suc->codigo ? 'selected' : '' }}>
                                    {{ $suc->codigo }} - {{ $suc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-activity"></i> Diagnóstico y Estado</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Motivo de Ingreso <span class="req">*</span></label>
                        @if(in_array($orden->motivo_ingreso, ['Servicio Cliente Externo', 'Validacion de Garantia'], true))
                            <select id="motivo_ingreso" required onchange="toggleBloquesMotivo()">
                                <option value="Servicio Cliente Externo" {{ $orden->motivo_ingreso === 'Servicio Cliente Externo' ? 'selected' : '' }}>Servicio Cliente Externo</option>
                                <option value="Validacion de Garantia" {{ $orden->motivo_ingreso === 'Validacion de Garantia' ? 'selected' : '' }}>Validacion de Garantia</option>
                            </select>
                        @else
                            <select id="motivo_ingreso" required disabled onchange="toggleBloquesMotivo()" style="background:#f1f5f9; cursor:not-allowed;">
                                <option value="{{ $orden->motivo_ingreso }}" selected>{{ $orden->motivo_ingreso }}</option>
                            </select>
                        @endif
                    </div>
                    <div class="campo" id="bloque-garantia" style="display: {{ $orden->motivo_ingreso === 'Validacion de Garantia' ? 'block' : 'none' }};">
                        <label>Tipo de Garantía <span class="req">*</span></label>
                        <select id="garantia_tipo" onchange="toggleBloquesMotivo()">
                            <option value="">-- Seleccione --</option>
                            <option value="propia" {{ $orden->garantia_tipo === 'propia' || $orden->garantia_tipo === 'interna' ? 'selected' : '' }}>Interna</option>
                            <option value="externa" {{ $orden->garantia_tipo === 'externa' ? 'selected' : '' }}>Externa</option>
                        </select>
                    </div>
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
                    <div class="campo" id="bloque-transferencia" style="display: {{ $orden->motivo_ingreso === 'Validacion de Garantia' ? 'block' : 'none' }};">
                        <label>Plataforma de Transferencia de Inventario</label>
                        <select id="transferencia_plataforma">
                            <option value="">-- Seleccione --</option>
                            <option value="MBA3" {{ $orden->transferencia_plataforma === 'MBA3' ? 'selected' : '' }}>MBA3</option>
                            <option value="Milenium" {{ $orden->transferencia_plataforma === 'Milenium' ? 'selected' : '' }}>Milenium</option>
                            <option value="Otros" {{ $orden->transferencia_plataforma === 'Otros' ? 'selected' : '' }}>Otros</option>
                        </select>
                        <div style="margin-top:10px;">
                            <label>Número de Transferencia de Inventario</label>
                            <input type="text" id="transferencia_numero" value="{{ $orden->transferencia_numero }}" placeholder="Ingrese número de transferencia de inventario...">
                        </div>
                    </div>
                    <div class="campo">
                        <label>Fecha Prometida de Entrega</label>
                        <input type="date" id="fecha_prometido" value="{{ $orden->fecha_prometido ? \Carbon\Carbon::parse($orden->fecha_prometido)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="campo">
                        <label>Técnico Asignado <span class="req">*</span></label>
                        <select id="tecnico_id" required>
                            @foreach($tecnicos as $t)
                                <option value="{{ $t->id }}" {{ $orden->tecnico_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->nombre_tecnico }} ({{ $t->pendientes + $t->en_proceso }} OT)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo" id="bloque-cas" style="display: {{ ($orden->motivo_ingreso === 'Validacion de Garantia' && $orden->garantia_tipo === 'externa') ? 'block' : 'none' }};">
                        <label>Asignar CAS <span style="font-size:11px;font-weight:400;color:#64748b;">(Opcional)</span></label>
                        <select id="cas_id" name="cas_id">
                            <option value="">-- Seleccione CAS --</option>
                            @foreach($cas as $c)
                                <option value="{{ $c->id }}" {{ $orden->cas_id == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="campo">
                    <label>Falla Reportada / Diagnóstico Técnico <span class="req">*</span></label>
                    <textarea id="eq_falla" rows="3" required>{{ $orden->equipo->falla ?? '' }}</textarea>
                </div>
                <div class="campo">
                    <label>Observaciones Adicionales del Equipo</label>
                    <textarea id="eq_observacion" rows="2">{{ $orden->equipo->observacion ?? '' }}</textarea>
                </div>
                <div class="campo">
                    <label>Observación de la Orden (Interna/General)</label>
                    <textarea id="observacion_orden" rows="2">{{ $orden->observacion ?? '' }}</textarea>
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
                                <option value="{{ $ts->id }}" {{ (int) ($orden->equipo->tipo_servicio_id ?? 0) === (int) $ts->id ? 'selected' : '' }}>
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
            <i class="bi bi-floppy"></i> Guardar Actualización de Orden
        </button>
    </form>
</div>
@endsection

@push('js_adicional')
<script>
function mostrarMensaje(isError, texto) {
    Swal.fire({
        icon: isError ? 'error' : 'success',
        title: isError ? 'Error' : '¡Éxito!',
        html: texto,
        confirmButtonColor: '#2563eb',
        background: '#ffffff',
        color: '#1e293b'
    });
}

function toggleBloquesMotivo() {
    const motivo = document.getElementById('motivo_ingreso').value;
    const bloqueFacturacion = document.getElementById('bloque-facturacion');
    const bloqueGarantia = document.getElementById('bloque-garantia');
    const bloqueCas = document.getElementById('bloque-cas');
    const bloqueTransferencia = document.getElementById('bloque-transferencia');
    
    if (motivo === 'Validacion de Garantia') {
        if (bloqueFacturacion) bloqueFacturacion.style.display = 'block';
        if (bloqueGarantia) bloqueGarantia.style.display = 'block';
        if (bloqueTransferencia) bloqueTransferencia.style.display = 'block';
        
        const garantiaTipo = document.getElementById('garantia_tipo').value;
        if (garantiaTipo === 'externa') {
            if (bloqueCas) bloqueCas.style.display = 'block';
        } else {
            if (bloqueCas) bloqueCas.style.display = 'none';
        }
    } else {
        if (bloqueFacturacion) bloqueFacturacion.style.display = 'none';
        if (bloqueGarantia) bloqueGarantia.style.display = 'none';
        if (bloqueCas) bloqueCas.style.display = 'none';
        if (bloqueTransferencia) bloqueTransferencia.style.display = 'none';
    }
}

async function guardarActualizacion() {
    const motivo = document.getElementById('motivo_ingreso').value;
    if (motivo === 'Validacion de Garantia') {
        const nroFactura = document.getElementById('nro_factura').value.trim();
        const fechaFact = document.getElementById('fecha_facturacion').value;
        const sucursal = document.getElementById('nro_sucursal_cliente').value;
        const garTipo = document.getElementById('garantia_tipo').value;
        
        if (!nroFactura) {
            Swal.fire({ icon: 'warning', title: 'Campo Requerido', text: 'El número de factura es requerido para garantía.', confirmButtonColor: '#2563eb' });
            return;
        }
        if (!fechaFact) {
            Swal.fire({ icon: 'warning', title: 'Campo Requerido', text: 'La fecha de facturación es requerida para garantía.', confirmButtonColor: '#2563eb' });
            return;
        }
        if (!sucursal) {
            Swal.fire({ icon: 'warning', title: 'Campo Requerido', text: 'La sucursal del cliente es requerida para garantía.', confirmButtonColor: '#2563eb' });
            return;
        }
        if (!garTipo) {
            Swal.fire({ icon: 'warning', title: 'Campo Requerido', text: 'El tipo de garantía es requerido.', confirmButtonColor: '#2563eb' });
            return;
        }
    }

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');

    fd.append('orden_id', document.getElementById('orden_id').value);
    fd.append('equipo_id', document.getElementById('equipo_id').value);

    fd.append('estado_orden', document.getElementById('estado_orden').value);
    fd.append('fecha_prometido', document.getElementById('fecha_prometido').value);
    fd.append('transferencia_plataforma', document.getElementById('transferencia_plataforma') ? document.getElementById('transferencia_plataforma').value : '');
    fd.append('transferencia_numero', document.getElementById('transferencia_numero') ? document.getElementById('transferencia_numero').value.trim() : '');
    fd.append('tecnico_id', document.getElementById('tecnico_id').value);

    fd.append('eq_falla', document.getElementById('eq_falla').value.trim());
    fd.append('eq_observacion', document.getElementById('eq_observacion').value.trim());

    fd.append('tipo_servicio_id', document.getElementById('tipo_servicio_id').value);
    fd.append('valor_estandar_id', document.getElementById('valor_estandar_id').value);
    fd.append('repuesto_inventario_id', document.getElementById('repuesto_inventario_id').value);

    // Nuevos campos de equipo
    fd.append('eq_tipo', document.getElementById('eq_tipo').value);
    fd.append('eq_marca', document.getElementById('eq_marca').value);
    fd.append('eq_modelo', document.getElementById('eq_modelo').value.trim());
    fd.append('eq_contrasena', document.getElementById('eq_contrasena').value.trim());

    // Nuevos campos de orden
    fd.append('motivo_ingreso', document.getElementById('motivo_ingreso').value);
    fd.append('garantia_tipo', document.getElementById('garantia_tipo').value);
    fd.append('observacion_orden', document.getElementById('observacion_orden').value.trim());

    // Facturación / CAS
    fd.append('cas_id', document.getElementById('cas_id') ? document.getElementById('cas_id').value : '');
    fd.append('nro_factura', document.getElementById('nro_factura').value.trim());
    fd.append('nro_factura_2', document.getElementById('nro_factura_2').value.trim());
    fd.append('fecha_facturacion', document.getElementById('fecha_facturacion').value);
    fd.append('nro_sucursal_cliente', document.getElementById('nro_sucursal_cliente').value);

    // Datos del cliente
    fd.append('cli_tipo', document.getElementById('cli_tipo').value);
    fd.append('cli_identificacion', document.getElementById('cli_identificacion').value.trim());
    fd.append('cli_nombres', document.getElementById('cli_nombres').value.trim());
    fd.append('cli_apellidos', document.getElementById('cli_apellidos').value.trim());
    fd.append('cli_telefono', document.getElementById('cli_telefono').value.trim());
    fd.append('cli_correo', document.getElementById('cli_correo').value.trim());
    fd.append('cli_direccion', document.getElementById('cli_direccion').value.trim());

    // Series
    const seriesInputs = document.querySelectorAll('input[name="series[]"]');
    seriesInputs.forEach(input => {
        fd.append('series[]', input.value.trim());
    });

    const btn = document.getElementById('btn-actualizar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando actualización...';

    try {
        const r = await fetch('{{ route("ordenes.update") }}', { method: 'POST', body: fd });
        const d = await r.json();

        if (d.ok) {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: d.mensaje,
                showConfirmButton: false,
                timer: 1500,
                background: '#ffffff',
                color: '#1e293b'
            });
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar',
                text: d.error || 'No se pudo actualizar la orden.',
                confirmButtonColor: '#2563eb',
                background: '#ffffff',
                color: '#1e293b'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualización de Orden';
        }
    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Error de Red',
            text: 'Se perdió la conexión con el servidor. Intente nuevamente.',
            confirmButtonColor: '#2563eb',
            background: '#ffffff',
            color: '#1e293b'
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualización de Orden';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar bloques según motivo
    toggleBloquesMotivo();

    // Configurar validaciones dinámicas
    setupDynamicValidation(document.getElementById('cli_identificacion'), EcuadorianValidator.validarIdentificacion, (v) => {
        if (v.length === 0) return 'La identificación es requerida.';
        if (/[^a-zA-Z0-9]/.test(v)) return 'La identificación sólo debe contener letras y números.';
        return 'Debe ser una cédula (10 dígitos), RUC (13 dígitos) de Ecuador, o un pasaporte válido (5 a 20 caracteres alfanuméricos).';
    });

    setupDynamicValidation(document.getElementById('cli_telefono'), EcuadorianValidator.validarTelefono, (v) => {
        if (v.length === 0) return 'El teléfono es requerido.';
        if (/[^\d]/.test(v)) return 'El teléfono sólo debe contener números.';
        return 'El teléfono debe ser un celular de 10 dígitos (ej: 0987654321) o convencional de 9 dígitos (ej: 022345678) de Ecuador.';
    });

    setupDynamicValidation(document.getElementById('cli_correo'), EcuadorianValidator.validarEmail, (v) => {
        return 'El correo electrónico no tiene un formato válido.';
    });

    // Inicializar vista del cliente según apellidos actuales al cargar
    const currentApellidos = document.getElementById('cli_apellidos')?.value || '';
    if (currentApellidos === '.') {
        setClienteTipoVista('empresa', document.getElementById('cli_nombres').value);
    } else {
        setClienteTipoVista('natural');
    }

    const inpCi = document.getElementById('cli_identificacion');
    if (inpCi) {
        inpCi.addEventListener('input', () => {
            const val = inpCi.value.trim();
            if (val.length === 13) {
                verificarRucTipo(val);
            } else if (val.length < 13) {
                setClienteTipoVista('natural');
            }
        });
        inpCi.addEventListener('blur', () => {
            const val = inpCi.value.trim();
            if (val.length === 13) {
                verificarRucTipo(val);
            }
        });
    }
});

function actualizarCantidad() {
    const count = document.querySelectorAll('input[name="series[]"]').length || 1;
    const inputCant = document.getElementById('eq_cantidad');
    if (inputCant) {
        inputCant.value = count;
    }
    const overviewCant = document.getElementById('overview-cantidad');
    if (overviewCant) {
        overviewCant.textContent = count;
    }
}

function agregarSerie() {
    const container = document.getElementById('series-container');
    const row = document.createElement('div');
    row.className = 'linea-item';
    row.style.cssText = 'display:flex; gap:10px; margin-top:8px;';
    row.innerHTML = `
        <input type="text" name="series[]" oninput="this.value=this.value.toUpperCase()" placeholder="Serie adicional" style="flex:1;">
        <button type="button" class="btn btn-sm btn-danger btn-mini" style="background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; border-radius:8px; width:36px; height:36px;" onclick="this.closest('.linea-item').remove(); actualizarCantidad();">-</button>
    `;
    container.appendChild(row);
    actualizarCantidad();
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
            text: `Se encontraron múltiples sucursales con el número ${prefix}. Por favor seleccione una:`,
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

let _rucModalShowedFor = '';

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
</script>
@endpush
