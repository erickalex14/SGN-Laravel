@php
    $equipo = $orden->equipo;
    $cliente = $orden->cliente;
    $tecnico = $orden->tecnico;
    $sucursal = $orden->sucursal;
    $cas = $orden->cas;
    $usuarioIngreso = $orden->usuarioIngreso;

    $series = collect();
    if ($equipo && $equipo->relationLoaded('series')) {
        $series = $equipo->series->pluck('serie')->filter();
    }
    if ($series->isEmpty() && !empty($equipo?->serie)) {
        $series = collect([$equipo->serie]);
    }

    $tipoServicio = $equipo?->tipo_servicio_texto ?: $equipo?->tipoServicio?->nombre;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden {{ $orden->nro_orden }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #fff; color: #0f172a; font-size: 12px; }
.page { width: 100%; max-width: 190mm; margin: 0 auto; padding: 8mm; }
.print-btn { position: fixed; top: 12px; right: 12px; border: 0; background: #1d4ed8; color: #fff; padding: 10px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; }
.header { border-bottom: 1px solid #0f172a; padding-bottom: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; }
.title { font-size: 20px; font-weight: 800; }
.meta { text-align: right; line-height: 1.5; }
.sec { margin-bottom: 10px; }
.sec h3 { font-size: 12px; text-transform: uppercase; background: #dbeafe; border-left: 3px solid #1d4ed8; padding: 4px 8px; margin-bottom: 4px; }
table { width: 100%; border-collapse: collapse; }
td { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: top; }
.lbl { display: block; font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; font-weight: 700; }
.foot { margin-top: 14px; border-top: 1px solid #cbd5e1; padding-top: 8px; font-size: 10px; color: #64748b; text-align: center; }
@media print {
    @page { size: A4 portrait; margin: 10mm; }
    .print-btn { display: none; }
    .page { padding: 0; }
}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>
<div class="page">
    <div class="header">
        <div>
            <div class="title">Orden de Servicio</div>
            <div>Novitecnologia Cia. Ltda.</div>
        </div>
        <div class="meta">
            <div><strong>{{ $orden->nro_orden }}</strong></div>
            <div>Ingreso: {{ \Carbon\Carbon::parse($orden->fecha_de_ingreso)->format('d/m/Y H:i') }}</div>
            <div>Estado: {{ $orden->estado_orden }}</div>
        </div>
    </div>

    <div class="sec">
        <h3>Datos del Cliente</h3>
        <table>
            <tr>
                <td width="25%"><span class="lbl">Cliente</span>{{ trim(($cliente?->nombres ?? '') . ' ' . ($cliente?->apellidos ?? '')) ?: '-' }}</td>
                <td width="25%"><span class="lbl">Identificacion</span>{{ $cliente?->identificacion ?? '-' }}</td>
                <td width="25%"><span class="lbl">Telefono</span>{{ $cliente?->numero_contacto ?? '-' }}</td>
                <td width="25%"><span class="lbl">Correo</span>{{ $cliente?->correo ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="lbl">Direccion</span>{{ $cliente?->direccion_clientes ?? '-' }}</td>
                <td><span class="lbl">Motivo de Ingreso</span>{{ $orden->motivo_ingreso ?? '-' }}</td>
                <td><span class="lbl">Sucursal Cliente</span>{{ $orden->nro_sucursal_cliente ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="sec">
        <h3>Tecnico Responsable</h3>
        <table>
            <tr>
                <td width="25%"><span class="lbl">Tecnico</span>{{ $tecnico->nombre_tecnico ?? '-' }}</td>
                <td width="25%"><span class="lbl">Sucursal</span>{{ $sucursal?->ciudad ?: ($sucursal?->nombre ?? '-') }}</td>
                <td width="25%"><span class="lbl">Ingresado por</span>{{ $usuarioIngreso->nombre_tecnico ?? '-' }}</td>
                <td width="25%"><span class="lbl">Fecha Prometida</span>{{ $orden->fecha_prometido ? \Carbon\Carbon::parse($orden->fecha_prometido)->format('d/m/Y') : '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="sec">
        <h3>Datos del Equipo</h3>
        <table>
            <tr>
                <td width="25%"><span class="lbl">Tipo</span>{{ $equipo?->tipo ?: '-' }}</td>
                <td width="25%"><span class="lbl">Marca</span>{{ $equipo?->marca ?: '-' }}</td>
                <td width="25%"><span class="lbl">Modelo</span>{{ $equipo?->modelo ?: '-' }}</td>
                <td width="25%"><span class="lbl">Fecha Facturacion</span>{{ $equipo?->fecha_facturacion ? \Carbon\Carbon::parse($equipo->fecha_facturacion)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td colspan="4"><span class="lbl">Series</span>{{ $series->isNotEmpty() ? $series->implode(' | ') : '-' }}</td>
            </tr>
            <tr>
                <td colspan="4"><span class="lbl">Falla Reportada</span>{{ $equipo?->falla ?: '-' }}</td>
            </tr>
            <tr>
                <td colspan="4"><span class="lbl">Observacion</span>{{ $equipo?->observacion ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="lbl">Tipo de Servicio</span>{{ $tipoServicio ?: '-' }}</td>
                <td><span class="lbl">Estado Repuesto</span>{{ $orden->estado_repuesto ?: '-' }}</td>
                <td><span class="lbl">Garantia</span>{{ $orden->garantia_tipo ?: '-' }}</td>
                <td><span class="lbl">CAS</span>{{ $cas?->nombre ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="foot">
        Impreso: {{ now('America/Guayaquil')->format('d/m/Y H:i:s') }} | Orden {{ $orden->nro_orden }}
    </div>
</div>
</body>
</html>
