@extends('layouts.app')
@section('titulo', 'Editar Orden Corporativa: ' . $orden->nro_orden)

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
    $estadoOrden = trim((string) ($orden->estado ?? ''));
    $chipOrden = match (true) {
        in_array($estadoOrden, ['Pendiente', 'INGRESO'], true) => 'orden-pend',
        in_array($estadoOrden, ['En proceso', 'EN PROCESO', 'REVISION', 'REVISIÓN', 'ESPERA REPUESTO'], true) => 'orden-proc',
        in_array($estadoOrden, ['Finalizada', 'REPARADO'], true) => 'orden-fin',
        in_array($estadoOrden, ['Entregada', 'ENTREGADO'], true) => 'orden-ent',
        default => 'orden-ent',
    };

    $nombreEmpresa = $orden->empresa->nombre ?? '-';
    $rucEmpresa = $orden->empresa->ruc ?? '-';
    
    // Si es tipo servicios, concatenar la lista de técnicos
    if ($orden->subtipo === 'Servicios') {
        $nombreTecnico = $orden->tecnicos->isNotEmpty() 
            ? $orden->tecnicos->pluck('nombre_tecnico')->implode(', ') 
            : ($orden->tecnico->nombre_tecnico ?? '-');
    } else {
        $nombreTecnico = $orden->tecnico->nombre_tecnico ?? '-';
    }

    $usuarioIngreso = $orden->ingresadoPor->usuario ?? ($orden->ingresadoPor->nombre_tecnico ?? '-');
    $fmt = static fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y H:i') : '-';
@endphp

<div class="eo-wrap">
    <div class="eo-hdr">
        <h2>
            <i class="bi bi-pencil-square" style="color:#2563eb;"></i>
            Gestión y Edición de Orden Corporativa
            <span class="ord-badge">{{ $orden->nro_orden }}</span>
        </h2>
        <div class="eo-actions">
            <a href="{{ route('mis_ordenes.index') }}" class="eo-btn-link back">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <a target="_blank" href="{{ route('ordenes_empresa.imprimir', ['id' => $orden->id]) }}" class="eo-btn-link print">
                <i class="bi bi-printer"></i> Imprimir OT
            </a>
        </div>
    </div>

    <div class="eo-overview">
        <div class="eo-overview-head">
            <strong>Resumen completo de la orden</strong>
            <div class="eo-chips">
                <span class="eo-chip {{ $chipOrden }}">Estado: {{ $estadoOrden ?: '-' }}</span>
                <span class="eo-chip orden-proc">Tipo: Empresa ({{ $orden->subtipo }})</span>
            </div>
        </div>
        <div class="eo-meta-grid">
            <div class="eo-meta-item"><label>Empresa / Cliente</label><span>{{ $nombreEmpresa }}</span></div>
            <div class="eo-meta-item"><label>RUC</label><span>{{ $rucEmpresa }}</span></div>
            <div class="eo-meta-item"><label>Subtipo Orden</label><span>{{ $orden->subtipo }}</span></div>
            <div class="eo-meta-item"><label>Nro. Ticket</label><span>{{ $orden->nro_ticket ?? '-' }}</span></div>

            <div class="eo-meta-item"><label>Sucursal Cliente</label><span>{{ $orden->nro_sucursal_cliente ?? '-' }}</span></div>
            <div class="eo-meta-item"><label>Sucursal Interna</label><span>{{ $orden->sucursal->nombre ?? '-' }}</span></div>
            <div class="eo-meta-item full"><label>Técnico(s) Asignado(s)</label><span>{{ $nombreTecnico }}</span></div>

            @if($orden->subtipo === 'Servicios')
                <div class="eo-meta-item"><label>Tarifa por Hora</label><span>${{ number_format($orden->valor_hora, 2) }}</span></div>
                <div class="eo-meta-item"><label>Horas Trabajadas</label><span>{{ number_format($orden->horas_trabajadas, 2) }} hrs</span></div>
                <div class="eo-meta-item"><label>Total Facturado</label><span><strong>${{ number_format($orden->tecnicos->count() * $orden->horas_trabajadas * $orden->valor_hora, 2) }}</strong></span></div>
                <div class="eo-meta-item"><label>Tipo de Servicio</label><span>{{ $orden->tipo_servicio ?? '-' }}</span></div>
            @else
                <div class="eo-meta-item"><label>Equipo</label><span>{{ trim(($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')) ?: '-' }}</span></div>
                <div class="eo-meta-item"><label>Serie</label><span>{{ $orden->equipo->serie ?? '-' }}</span></div>
                <div class="eo-meta-item"><label>Tipo Servicio Equipo</label><span>{{ $orden->equipo->tipoServicio->nombre ?? '-' }}</span></div>
                <div class="eo-meta-item"></div>
            @endif

            <div class="eo-meta-item full"><label>Falla / Descripción del Servicio</label><span>{{ $orden->descripcion }}</span></div>
            <div class="eo-meta-item full"><label>Observación del Equipo</label><span>{{ $orden->equipo->observacion ?? '-' }}</span></div>

            <div class="eo-meta-item"><label>Fecha Ingreso</label><span>{{ $fmt($orden->fecha_ingreso) }}</span></div>
            <div class="eo-meta-item"><label>Fecha Prometida</label><span>{{ $fmt($orden->fecha_prometido) }}</span></div>
            <div class="eo-meta-item"><label>Ingresado por</label><span>{{ $usuarioIngreso }}</span></div>
            <div class="eo-meta-item"></div>
        </div>
    </div>

    <div id="eo-msg" class="msg-box"></div>

    <form id="form-edicion" onsubmit="event.preventDefault(); guardarActualizacionEmpresa();">
        <input type="hidden" id="orden_id" value="{{ $orden->id }}">
        <input type="hidden" id="equipo_id" value="{{ $orden->equipo_id }}">

        <div class="seccion-form">
            <div class="seccion-hdr"><i class="bi bi-activity"></i> Diagnóstico y Estado</div>
            <div class="seccion-body">
                <div class="grid-2">
                    <div class="campo">
                        <label>Estado Actual de la Orden <span class="req">*</span></label>
                        <select id="estado" name="estado" required>
                            <option value="Pendiente" {{ in_array($orden->estado, ['Pendiente', 'INGRESO'], true) ? 'selected' : '' }}>Pendiente</option>
                            <option value="En proceso" {{ in_array($orden->estado, ['En proceso', 'REVISION', 'REVISIÓN', 'ESPERA REPUESTO', 'EN PROCESO'], true) ? 'selected' : '' }}>En proceso</option>
                            <option value="Finalizada" {{ in_array($orden->estado, ['Finalizada', 'REPARADO'], true) ? 'selected' : '' }}>Finalizada</option>
                            <option value="Entregada" {{ in_array($orden->estado, ['Entregada', 'ENTREGADO'], true) ? 'selected' : '' }}>Entregada</option>
                            <option value="Devuelto sin reparar" {{ in_array($orden->estado, ['Devuelto sin reparar', 'DEVUELTO SIN REPARAR'], true) ? 'selected' : '' }}>Devuelto sin reparar</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label>Fecha Prometida de Entrega <span class="req">*</span></label>
                        <input type="date" id="fecha_prometido" name="fecha_prometido" required value="{{ $orden->fecha_prometido ? \Carbon\Carbon::parse($orden->fecha_prometido)->format('Y-m-d') : '' }}">
                    </div>
                    @if($orden->subtipo === 'Autoconsumo' || $orden->subtipo === 'Stock')
                        <div class="campo" id="bloque-cas-empresa">
                            <label>Asignar CAS <span style="font-size:11px;font-weight:400;color:#64748b;">(Opcional)</span></label>
                            <select id="cas_id_empresa" name="cas_id_empresa">
                                <option value="">-- Seleccione CAS --</option>
                                @foreach($cas as $c)
                                    <option value="{{ $c->id }}" {{ $orden->cas_id == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="campo">
                            <label>Técnico Asignado <span class="req">*</span></label>
                            <select id="tecnico_id" name="tecnico_id" required>
                                @foreach($tecnicos as $t)
                                    <option value="{{ $t->id }}" {{ $orden->tecnico_id == $t->id ? 'selected' : '' }}>
                                        {{ $t->nombre_tecnico }} ({{ $t->pendientes + $t->en_proceso }} OT)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="campo">
                    <label>Falla Reportada / Descripción del Servicio <span class="req">*</span></label>
                    <textarea id="descripcion" name="descripcion" rows="3" required placeholder="Ingrese la falla o descripción del servicio">{{ $orden->descripcion }}</textarea>
                </div>
                <div class="campo">
                    <label>Observaciones Adicionales del Equipo</label>
                    <textarea id="eq_observacion" name="eq_observacion" rows="2" placeholder="Ingrese detalles o estado del equipo al recibirlo">{{ $orden->equipo->observacion ?? '' }}</textarea>
                </div>
            </div>
        </div>

        @if($orden->subtipo === 'Servicios')
            <div class="seccion-form">
                <div class="seccion-hdr"><i class="bi bi-person-gear"></i> Técnicos Asignados <span class="req">*</span> <span style="font-size:11px;font-weight:400;color:#64748b;text-transform:none;">(máximo 5 técnicos)</span></div>
                <div class="seccion-body">
                    @php $idsAsignados = $orden->tecnicos->pluck('id')->toArray(); @endphp
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; max-height: 250px; overflow-y: auto; background: #fff; margin-bottom: 20px;">
                        @foreach($tecnicos as $tec)
                            @php $checked = in_array($tec->id, $idsAsignados) ? 'checked' : ''; @endphp
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer; padding: 4px; color: #1e293b; font-size: 13px;">
                                <input type="checkbox" name="tecnicos_asignados[]" value="{{ $tec->id }}" {{ $checked }} class="chk-tecnico-emp" style="width: 16px; height: 16px; cursor: pointer;">
                                {{ $tec->nombre_tecnico }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="seccion-form" id="bloque-calculo-empresa" style="background: #f0fdf4; border: 1.5px solid #86efac; color: #166534;">
                <div class="seccion-hdr" style="background: #e6fced; border-bottom: 1.5px solid #86efac; color: #166534;"><i class="bi bi-calculator"></i> Desglose de Costo de Servicio Corporativo</div>
                <div class="seccion-body">
                    <div class="grid-2" style="margin-bottom: 14px; gap: 14px;">
                        <div class="campo" style="margin-bottom: 0;">
                            <label style="color: #166534; font-weight: 700; font-size: 12.5px;">Tarifa por Hora ($)</label>
                            <input type="number" step="0.01" name="valor_hora" id="valor_hora" value="{{ number_format($orden->valor_hora, 2, '.', '') }}" style="border-color: #86efac; padding: 8px 12px; font-size: 13.5px; font-weight: 600; border-radius: 8px;">
                        </div>
                        <div class="campo" style="margin-bottom: 0;">
                            <label style="color: #166534; font-weight: 700; font-size: 12.5px;">Horas Trabajadas</label>
                            <input type="number" step="0.25" name="horas_trabajadas" id="horas_trabajadas" value="{{ number_format($orden->horas_trabajadas, 2, '.', '') }}" style="border-color: #86efac; padding: 8px 12px; font-size: 13.5px; font-weight: 600; border-radius: 8px;">
                        </div>
                    </div>
                    <div style="font-size: 14px; font-weight: 800; display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #86efac; padding-top: 12px; flex-wrap: wrap; gap: 10px;">
                        <span>Fórmula: <span id="formula-lbl" style="font-family: monospace; font-size: 13px; color: #15803d;">0 técnicos * 0.00 horas * $0.00/hr</span></span>
                        <span style="font-size: 16px; color: #14532d;">Subtotal Estimado: <strong id="cobro-total-lbl" style="font-size: 19px; color: #166534;">$0.00</strong></span>
                    </div>
                </div>
            </div>
        @endif

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

@if($orden->subtipo === 'Servicios')
function calcularPrecioEmpresa() {
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
        formulaLbl.textContent = `${numTecnicos} técnico(s) * ${horas.toFixed(2)} hora(s) * $${valorHora.toFixed(2)}/hr`;
    }
    if (totalLbl) {
        totalLbl.textContent = `$${total.toFixed(2)}`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const valorHoraInput = document.getElementById('valor_hora');
    const horasInput = document.getElementById('horas_trabajadas');
    
    if (valorHoraInput) valorHoraInput.addEventListener('input', calcularPrecioEmpresa);
    if (horasInput) horasInput.addEventListener('input', calcularPrecioEmpresa);

    document.querySelectorAll('.chk-tecnico-emp').forEach(chk => {
        chk.addEventListener('change', () => {
            const checked = document.querySelectorAll('.chk-tecnico-emp:checked');
            if (checked.length > 5) {
                chk.checked = false;
                alert('Puedes asignar un máximo de 5 técnicos.');
            }
            calcularPrecioEmpresa();
        });
    });

    calcularPrecioEmpresa();
});
@endif

async function guardarActualizacionEmpresa() {
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('orden_id', document.getElementById('orden_id').value);
    fd.append('equipo_id', document.getElementById('equipo_id').value);
    fd.append('estado', document.getElementById('estado').value);
    fd.append('fecha_prometido', document.getElementById('fecha_prometido').value);
    fd.append('descripcion', document.getElementById('descripcion').value.trim());
    fd.append('eq_observacion', document.getElementById('eq_observacion').value.trim());

    @if($orden->subtipo === 'Autoconsumo' || $orden->subtipo === 'Stock')
        fd.append('cas_id_empresa', document.getElementById('cas_id_empresa').value);
        fd.append('tecnico_id', document.getElementById('tecnico_id').value);
    @endif

    @if($orden->subtipo === 'Servicios')
        fd.append('valor_hora', document.getElementById('valor_hora').value);
        fd.append('horas_trabajadas', document.getElementById('horas_trabajadas').value);
        
        const chks = document.querySelectorAll('.chk-tecnico-emp:checked');
        if (chks.length === 0) {
            mostrarMensaje(true, 'Debe asignar al menos 1 técnico.');
            return;
        }
        chks.forEach(chk => {
            fd.append('tecnicos_asignados[]', chk.value);
        });
    @endif

    const btn = document.getElementById('btn-actualizar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando actualización...';

    try {
        const r = await fetch('{{ route("ordenes_empresa.update") }}', { 
            method: 'POST', 
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const d = await r.json();

        if (d.ok) {
            mostrarMensaje(false, `<strong>Éxito:</strong> ${d.mensaje}`);
            setTimeout(() => {
                location.reload();
            }, 1300);
        } else {
            mostrarMensaje(true, d.error || 'No se pudo actualizar la orden corporativa.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualización de Orden';
        }
    } catch (e) {
        console.error(e);
        mostrarMensaje(true, 'Se perdió la conexión con el servidor o hubo un error crítico. Intente nuevamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar Actualización de Orden';
    }
}
</script>
@endpush
