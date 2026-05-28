@php
    $empresa = $orden->empresa;
    $equipo = $orden->equipo;
    $tecnico = $orden->tecnico;
    $sucursal = $orden->sucursal;
    $usuarioIngreso = $orden->ingresadoPor;
    $series = collect();
    if ($equipo && $equipo->relationLoaded('series')) {
        $series = $equipo->series->pluck('serie')->filter();
    }
    if ($series->isEmpty() && !empty($equipo?->serie)) {
        $series = collect(explode(',', (string) $equipo->serie))->map(fn($s) => trim($s))->filter();
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden Empresa {{ $orden->nro_orden }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 7.8pt; color: #000; background: #fff; }
.wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 3px; margin-bottom: 5px; gap: 10px; }
.header-info { font-size: 7pt; line-height: 1.35; }
.header-info .empresa { font-size: 9pt; font-weight: 700; }
.header img { height: 34px; }
.orden-header { display: flex; justify-content: space-between; align-items: center; background: #1a56db; color: #fff; padding: 4px 8px; border-radius: 3px; margin-bottom: 5px; }
.orden-header .nro { font-size: 10pt; font-weight: 700; }
.orden-header .meta { font-size: 6.8pt; text-align: right; line-height: 1.4; }
.sec-titulo { background: #dbeafe; font-weight: 700; font-size: 6.8pt; text-transform: uppercase; padding: 2px 6px; border-left: 3px solid #1a56db; margin-bottom: 1px; }
table.datos { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
table.datos td { border: 1px solid #d1d5db; padding: 3px 5px; font-size: 7.2pt; vertical-align: top; }
table.datos td .lbl { font-size: 5.8pt; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 1px; }
.badge { display: inline-block; padding: 1px 7px; border-radius: 3px; font-size: 7pt; font-weight: 700; background: #fef9c3; color: #854d0e; }
.btn-print { position: fixed; top: 10px; right: 10px; background: #1a56db; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.firmas { display: flex; justify-content: space-between; margin-top: 22px; }
.firma-box { width: 44%; text-align: center; }
.firma-linea { border-top: 1px solid #000; padding-top: 3px; font-size: 7pt; margin-top: 20px; }
.foot { text-align: center; margin-top: 10px; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 6px; }
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
            Fecha de ingreso: {{ $orden->fecha_ingreso ? \Carbon\Carbon::parse($orden->fecha_ingreso)->format('d/m/Y H:i') : '-' }}<br>
            Estado: {{ $orden->estado ?: '-' }}
        </div>
    </div>

    <div class="sec-titulo">Datos de la Empresa</div>
    <table class="datos">
        <tr>
            <td width="30%"><span class="lbl">Empresa</span>{{ $empresa?->nombre ?? '-' }}</td>
            <td width="20%"><span class="lbl">RUC</span>{{ $empresa?->ruc ?? '-' }}</td>
            <td width="25%"><span class="lbl">Telefono</span>{{ $empresa?->telefono ?? '-' }}</td>
            <td width="25%"><span class="lbl">Correo</span>{{ $empresa?->correo ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="lbl">Direccion</span>{{ $empresa?->direccion_empresa ?? '-' }}</td>
            <td><span class="lbl">Subtipo</span><span class="badge">{{ $orden->subtipo }}</span></td>
            <td><span class="lbl">Nro. Ticket</span>{{ $orden->nro_ticket ?: '-' }}</td>
        </tr>
    </table>

    <div class="sec-titulo">Tecnico Responsable</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tecnico Asignado</span>{{ $tecnico?->nombre_tecnico ?? '-' }}</td>
            <td width="25%"><span class="lbl">Correo</span>{{ $tecnico?->correo_tec ?? '-' }}</td>
            <td width="25%"><span class="lbl">Sucursal</span>{{ $sucursal?->ciudad ?? '-' }}</td>
            <td width="25%"><span class="lbl">Ingresado por</span>{{ $usuarioIngreso?->nombre_tecnico ?? $usuarioIngreso?->usuario ?? '-' }}</td>
        </tr>
        <tr>
            <td><span class="lbl">Fecha Prometido</span>{{ $orden->fecha_prometido ? \Carbon\Carbon::parse($orden->fecha_prometido)->format('d/m/Y') : '-' }}</td>
            <td colspan="3"><span class="lbl">Tipo de Servicio</span>{{ $orden->tipo_servicio ?: ($equipo?->tipoServicio?->nombre ?? '-') }}</td>
        </tr>
    </table>

    <div class="sec-titulo">Detalle del Servicio / Equipo</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tipo</span>{{ $equipo?->tipo ?: '-' }}</td>
            <td width="25%"><span class="lbl">Marca</span>{{ $equipo?->marca ?: '-' }}</td>
            <td width="25%"><span class="lbl">Codigo / Modelo</span>{{ $equipo?->modelo ?: '-' }}</td>
            <td width="25%"><span class="lbl">Series</span>{{ $series->isNotEmpty() ? $series->implode(' | ') : '-' }}</td>
        </tr>
        <tr>
            <td colspan="4"><span class="lbl">Descripcion</span>{{ $orden->descripcion ?: ($equipo?->falla ?: '-') }}</td>
        </tr>
        <tr>
            <td colspan="4"><span class="lbl">Recepcion / Detalles</span>{{ $equipo?->observacion ?: '-' }}</td>
        </tr>
    </table>

    <div class="firmas">
        <div class="firma-box"><div class="firma-linea">Recibido por:</div></div>
        <div class="firma-box"><div class="firma-linea">Firma autorizada:</div></div>
    </div>

    <div class="foot">
        Novitecnologia Cia. Ltda. | Sistema de Gestion SGN | Impreso el:
        {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }}
    </div>
</div>
</body>
</html>
