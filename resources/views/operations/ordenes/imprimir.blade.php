@php
    $equipo = $orden->equipo;
    $cliente = $orden->cliente;
    $tecnico = $orden->tecnico;
    $sucursal = $orden->sucursal;
    $cas = $orden->cas;
    $usuarioIngreso = $orden->usuarioIngreso;
    $repuesto = $orden->repuestoInventario;

    $series = collect();
    if ($equipo && $equipo->relationLoaded('series')) {
        $series = $equipo->series->pluck('serie')->filter();
    }
    if ($series->isEmpty() && !empty($equipo?->serie)) {
        $series = collect([$equipo->serie]);
    }
    $cantidadSeries = $series->count();
    $motivoIngreso = (string) ($orden->motivo_ingreso ?? '');
    $esGarantia = $motivoIngreso === 'Validacion de Garantia';
    $tipoServicio = $equipo?->tipo_servicio_texto ?: $equipo?->tipoServicio?->nombre;
    $estadoRepuesto = (string) ($orden->estado_repuesto ?: 'No requerido');

    $coloresRepuesto = [
        'No requerido' => ['#f1f5f9', '#475569'],
        'Requerido' => ['#fef9c3', '#92400e'],
        'Con stock' => ['#dcfce7', '#166534'],
        'Sin stock' => ['#fee2e2', '#991b1b'],
        'En espera' => ['#fef9c3', '#92400e'],
        'En espera del repuesto' => ['#fef9c3', '#92400e'],
    ];
    $colorRepuesto = $coloresRepuesto[$estadoRepuesto] ?? ['#f1f5f9', '#475569'];

    $garantiaTipoRaw = (string) ($orden->garantia_tipo ?? '');
    $garantiaTipoLabel = $garantiaTipoRaw === 'propia'
        ? 'Interna'
        : ($garantiaTipoRaw === 'externa' ? 'Externa' : 'No especificada');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden {{ $orden->nro_orden }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 7.6pt; color: #000; background: #fff; }
.no-print { display: inline-flex; }
.wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 3px; margin-bottom: 4px; gap: 10px; }
.header-info { font-size: 7pt; line-height: 1.35; }
.header-info .empresa { font-size: 9pt; font-weight: 700; }
.header img { height: 34px; }
.orden-header { display: flex; justify-content: space-between; align-items: center; background: #1a56db; color: #fff; padding: 3px 8px; border-radius: 3px; margin-bottom: 4px; }
.orden-header .nro { font-size: 10pt; font-weight: 700; }
.orden-header .meta { font-size: 6.5pt; text-align: right; line-height: 1.4; }
.sec-titulo { background: #dbeafe; font-weight: 700; font-size: 6.5pt; text-transform: uppercase; padding: 2px 6px; border-left: 3px solid #1a56db; margin-bottom: 1px; }
table.datos { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
table.datos td { border: 1px solid #d1d5db; padding: 2px 5px; font-size: 7pt; vertical-align: top; }
table.datos td .lbl { font-size: 5.5pt; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0; }
.badge { display: inline-block; padding: 1px 7px; border-radius: 3px; font-size: 7pt; font-weight: 700; }
.btn-print { position: fixed; top: 10px; right: 10px; background: #1a56db; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.condiciones-wrap { border-top: 1.5px solid #000; padding-top: 4px; }
.condiciones-titulo { text-align: center; font-weight: 700; font-size: 7.5pt; text-decoration: underline; margin-bottom: 2px; }
.condiciones { font-size: 5.5pt; text-align: justify; line-height: 1.28; color: #111; }
.condiciones p { margin-bottom: 1px; }
.nota-final { margin-top: 8px; padding: 5px 10px; background: #fef9c3; border: 1px solid #fde047; border-radius: 3px; font-size: 7.5pt; color: #713f12; text-align: center; }
.firmas { display: flex; justify-content: space-between; margin-top: 10px; }
.firma-box { width: 44%; text-align: center; }
.firma-linea { border-top: 1px solid #000; padding-top: 3px; font-size: 7pt; margin-top: 20px; }
.foot { text-align: center; margin-top: 8px; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 6px; }
@media print {
    @page { size: A4 portrait; margin: 10mm; }
    .no-print { display: none !important; }
    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
}
</style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Guardar PDF</button>

<div class="wrap">
    <div class="header">
        <div class="header-info">
            <div class="empresa">Novitecnologia Cia. Ltda.</div>
            <div><strong>GYE:</strong> 04-6031337 / 0960500158 &nbsp;&nbsp; <strong>UIO:</strong> 02-6001635 / 0960500156</div>
            <div>soporte@novitec.com.ec &nbsp; www.novitec.com.ec</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="SGN - Novitec">
    </div>

    <div class="orden-header">
        <div class="nro">Nro. de Orden: {{ $orden->nro_orden }}</div>
        <div class="meta">
            Fecha de ingreso: {{ $orden->fecha_de_ingreso ? \Carbon\Carbon::parse($orden->fecha_de_ingreso)->format('d/m/Y H:i') : '-' }}<br>
            Estado: {{ $orden->estado_orden ?: '-' }}
        </div>
    </div>

    <div class="sec-titulo">Datos del Cliente</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Cliente</span>{{ trim(($cliente?->nombres ?? '') . ' ' . ($cliente?->apellidos ?? '')) ?: '-' }}</td>
            <td width="25%"><span class="lbl">C.I / RUC</span>{{ $cliente?->identificacion ?? '-' }}</td>
            <td width="25%"><span class="lbl">Telefono</span>{{ $cliente?->numero_contacto ?? '-' }}</td>
            <td width="25%"><span class="lbl">Correo</span>{{ $cliente?->correo ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="lbl">Direccion</span>{{ $cliente?->direccion_clientes ?? '-' }}</td>
            <td><span class="lbl">Motivo de Ingreso</span>{{ $motivoIngreso ?: '-' }}</td>
            <td><span class="lbl">Nro. Factura</span>{{ $orden->nro_factura ?: '-' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="lbl">Sucursal del Cliente</span>{{ $nombreSucursalCliente ?? '-' }}</td>
            <td colspan="2"></td>
        </tr>
    </table>

    <div class="sec-titulo">Tecnico Responsable</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tecnico Asignado</span>{{ $tecnico?->nombre_tecnico ?? '-' }}</td>
            <td width="25%"><span class="lbl">Correo</span>{{ $tecnico?->correo_tec ?? '-' }}</td>
            <td width="25%"><span class="lbl">Contacto</span>{{ $sucursal?->nro_base ?? '-' }}</td>
            <td width="25%"><span class="lbl">Ingresado por</span>{{ $usuarioIngreso?->nombre_tecnico ?? $usuarioIngreso?->usuario ?? '-' }}</td>
        </tr>
        <tr>
            <td width="25%"><span class="lbl">Fecha Prometido</span>{{ $orden->fecha_prometido ? \Carbon\Carbon::parse($orden->fecha_prometido)->format('d/m/Y') : '-' }}</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <div class="sec-titulo">Datos del Equipo</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tipo</span>{{ $equipo?->tipo ?: '-' }}</td>
            <td width="25%"><span class="lbl">Marca</span>{{ $equipo?->marca ?: '-' }}</td>
            <td width="25%"><span class="lbl">Codigo / Modelo</span>{{ $equipo?->modelo ?: '-' }}</td>
            <td width="25%"><span class="lbl">Cantidad</span>{{ $cantidadSeries > 0 ? $cantidadSeries : '-' }}</td>
        </tr>
        <tr>
            <td width="25%"><span class="lbl">Fecha Facturacion</span>{{ $equipo?->fecha_facturacion ? \Carbon\Carbon::parse($equipo->fecha_facturacion)->format('d/m/Y') : '-' }}</td>
            @if (!$esGarantia)
                <td width="25%"><span class="lbl">Tipo de Servicio</span>{{ $tipoServicio ?: '-' }}</td>
                <td colspan="2"></td>
            @else
                <td width="25%"><span class="lbl">Cobertura Garantia</span>{{ $garantiaTipoLabel }}</td>
                @if ($garantiaTipoRaw === 'externa')
                    <td colspan="2"><span class="lbl">CAS Asignado</span>{{ $cas?->nombre ?: '-' }}</td>
                @else
                    <td colspan="2"></td>
                @endif
            @endif
        </tr>
        <tr>
            <td colspan="4">
                <span class="lbl">Serie{{ $cantidadSeries > 1 ? 's' : '' }}</span>
                {{ $series->isNotEmpty() ? $series->implode(' | ') : '-' }}
            </td>
        </tr>
        <tr>
            <td colspan="4"><span class="lbl">Problema Reportado</span>{{ $equipo?->falla ?: '-' }}</td>
        </tr>
        <tr>
            <td colspan="4"><span class="lbl">Observaciones</span>{{ $equipo?->observacion ?: '-' }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="lbl">Estado del Repuesto</span>
                <span class="badge" style="background: {{ $colorRepuesto[0] }}; color: {{ $colorRepuesto[1] }};">
                    {{ $estadoRepuesto }}
                </span>
            </td>
            <td colspan="2">
                @if($estadoRepuesto !== 'No requerido' && ($repuesto?->codigo || $repuesto?->nombre))
                    <span class="lbl">Repuesto Asignado</span>
                    {{ trim(($repuesto?->codigo ? $repuesto->codigo . ' - ' : '') . ($repuesto?->nombre ?? '')) ?: '-' }}
                @endif
            </td>
        </tr>
    </table>

    <div class="condiciones-wrap">
        <div class="condiciones-titulo">Condiciones</div>
        <div class="condiciones">
            <p><strong>1. VALIDACION GARANTIA:</strong> Los equipos que ingresen bajo esta condicion deberan ser evaluados obligatoriamente por un tecnico, quien determinara por escrito si estos cumplen con las condiciones establecidas por los fabricantes.</p>
            <p><strong>2. EMISION DE PRESUPUESTO:</strong> Si un equipo no cumple las condiciones de garantia se emitira informe tecnico y presupuesto aproximado; en caso de rechazo del presupuesto aplican cargos de revision segun politicas vigentes.</p>
            <p><strong>3. INTENTO DE REPARACION:</strong> El cliente autoriza el intento de reparacion y conoce que pueden existir riesgos tecnicos inherentes al proceso.</p>
            <p><strong>4. EQUIPOS ABANDONADOS:</strong> Equipos no retirados en el plazo establecido podran generar cargos de bodegaje y custodia, conforme a politicas de servicio.</p>
            <p><strong>5. RESPALDO DE INFORMACION:</strong> El cliente es responsable de respaldar su informacion. Novitecnologia no asume responsabilidad por perdida de datos durante revision o reparacion.</p>
            <p><strong>6. DOCUMENTACION:</strong> Este documento es requerido para el retiro del equipo. La empresa se reserva el derecho de validar la autenticidad del comprobante presentado.</p>
            <p><strong>7. RESOLUCION DE CONTROVERSIAS:</strong> Se aplicara la legislacion ecuatoriana y se priorizara solucion amistosa, mediacion y arbitraje.</p>
        </div>
    </div>

    <div class="nota-final">
        <strong>NOTA:</strong> La perdida o reimpresion del presente documento de orden de trabajo tendra un valor de <strong>$5,00 + IVA</strong>.
    </div>

    <div class="firmas">
        <div class="firma-box"><div class="firma-linea">Recibido por:</div></div>
        <div class="firma-box"><div class="firma-linea">Firma del cliente:</div></div>
    </div>

    <div class="foot">
        Novitecnologia Cia. Ltda. | Sistema de Gestion SGN | Impreso el:
        {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>
</body>
</html>
