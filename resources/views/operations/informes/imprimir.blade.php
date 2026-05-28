@php
    $orden = $informe->orden;
    $ordenEmpresa = $informe->ordenEmpresa ?? null;
    $cliente = $orden?->cliente;
    $empresa = $ordenEmpresa?->empresa;
    $equipo = $orden?->equipo ?? $ordenEmpresa?->equipo;
    $tecnico = $informe->tecnico;

    $nroOrden = (string) ($orden?->nro_orden ?? $ordenEmpresa?->nro_orden ?? '-');
    $clienteNombre = trim((string) (($cliente?->nombres ?? '') . ' ' . ($cliente?->apellidos ?? '')));
    if ($clienteNombre === '') {
        $clienteNombre = (string) ($empresa?->nombre ?? '-');
    }
    $identificacion = (string) ($cliente?->identificacion ?? $empresa?->ruc ?? '—');
    $telefono = (string) ($cliente?->numero_contacto ?? $empresa?->telefono ?? '—');
    $correo = (string) ($cliente?->correo ?? $empresa?->correo ?? '—');
    $direccion = (string) ($cliente?->direccion_clientes ?? $empresa?->direccion_empresa ?? '');
    $nroFactura = trim((string) ($orden?->nro_factura ?? $ordenEmpresa?->nro_ticket ?? ''));
    $nroFactura2 = trim((string) ($orden?->nro_factura_2 ?? ''));
    $estadoOrden = (string) ($orden?->estado_orden ?? $ordenEmpresa?->estado ?? '');
    $estadoOrden = str_replace(['Credito', 'credito'], ['Crédito', 'crédito'], $estadoOrden);
    $estadoEquipo = (string) ($informe->estado_equipo ?? '');

    $colorEstado = '#64748b';
    if ($estadoEquipo === 'Operativo') {
        $colorEstado = '#10b981';
    } elseif ($estadoEquipo === 'Reparado parcialmente') {
        $colorEstado = '#f59e0b';
    } elseif ($estadoEquipo === 'Sin reparación posible' || $estadoEquipo === 'Desguace') {
        $colorEstado = '#ef4444';
    } elseif ($estadoEquipo === 'En espera de repuesto') {
        $colorEstado = '#3b82f6';
    }

    $repuestos = collect();
    if ($orden && $orden->relationLoaded('ordenRepuestos')) {
        $repuestos = $orden->ordenRepuestos->map(function ($r) {
            return (object) [
                'codigo' => (string) ($r->repuesto?->codigo ?? ''),
                'nombre' => (string) ($r->repuesto?->nombre ?? ''),
                'nro_parte' => (string) ($r->repuesto?->nro_parte ?? ''),
            ];
        })->filter(fn ($r) => $r->nombre !== '')->values();
    }

    $fechaInforme = (string) ($informe->fecha_informe ?? '');
    $fFmt = $fechaInforme;
    if ($fechaInforme !== '') {
        try {
            $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            $fecha = \Carbon\Carbon::parse($fechaInforme);
            $fFmt = $fecha->format('d') . ' de ' . ($meses[(int) $fecha->format('n')] ?? $fecha->format('m')) . ' de ' . $fecha->format('Y');
        } catch (\Throwable $e) {
            $fFmt = $fechaInforme;
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Informe {{ $nroOrden }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;font-size:9pt;color:#000;background:#fff}
@media print{@page{size:A4 portrait;margin:10mm}.no-print{display:none!important}body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}
.wrap{width:100%;max-width:190mm;margin:auto;padding:6mm}
.header{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1.5px solid #000;padding-bottom:6px;margin-bottom:8px}
.header-info{font-size:8.5pt;line-height:1.6}.header-info .empresa{font-size:11pt;font-weight:bold}.header img{height:42px}
.orden-header{display:flex;justify-content:space-between;align-items:center;background:#1a56db;color:#fff;padding:5px 10px;border-radius:3px;margin-bottom:8px}
.orden-header .nro{font-size:13pt;font-weight:bold}.orden-header .meta{font-size:8pt;text-align:right;line-height:1.7}
.sec-titulo{background:#dbeafe;font-weight:bold;font-size:7.5pt;text-transform:uppercase;padding:3px 8px;border-left:3px solid #1a56db;margin-bottom:1px;margin-top:6px}
table.datos{width:100%;border-collapse:collapse;margin-bottom:7px}table.datos td{border:1px solid #d1d5db;padding:4px 7px;font-size:8.5pt;vertical-align:top}
table.datos td .lbl{font-size:6.5pt;color:#6b7280;font-weight:bold;text-transform:uppercase;display:block;margin-bottom:1px}
.texto-campo{border:1px solid #d1d5db;padding:5px 8px;font-size:8.5pt;margin-bottom:7px;min-height:28px;white-space:pre-wrap;line-height:1.55}
.estado-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:8pt;font-weight:700;color:#fff}
.firmas{display:flex;justify-content:space-between;margin:10px 0}.firma-box{width:44%;text-align:center}
.firma-linea{border-top:1px solid #000;padding-top:4px;font-size:8.5pt;margin-top:28px}
.btn-print{position:fixed;top:10px;right:10px;background:#1a56db;color:white;border:none;padding:10px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:bold;z-index:999;box-shadow:0 2px 8px rgba(0,0,0,.2)}
</style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">&#128424; Imprimir / Guardar PDF</button>
<div class="wrap">
    <div class="header">
        <div class="header-info">
            <div class="empresa">Novitecnología Cía. Ltda.</div>
            <div><b>Teléfonos:</b></div>
            <div><b>GYE:</b> 04-031337 / 0960500158 &nbsp;&nbsp; <b>UIO:</b> 02-600135 / 0960500156</div>
            <div>https://www.novitec.com.ec</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="Novitec">
    </div>

    <div class="orden-header">
        <div class="nro">{{ $nroOrden }} - INFORME TÉCNICO</div>
        <div class="meta">Fecha: {{ $fFmt }}<br>Técnico: {{ $tecnico?->nombre_tecnico ?? '-' }}</div>
    </div>

    <div class="sec-titulo">Datos del Cliente</div>
    <table class="datos">
        <tr>
            <td width="50%"><span class="lbl">Cliente</span>{{ $clienteNombre }}</td>
            <td width="50%"><span class="lbl">Identificación / RUC</span>{{ $identificacion !== '' ? $identificacion : '—' }}</td>
        </tr>
        <tr>
            <td width="50%"><span class="lbl">Teléfono</span>{{ $telefono !== '' ? $telefono : '—' }}</td>
            <td width="50%"><span class="lbl">Correo</span>{{ $correo !== '' ? $correo : '—' }}</td>
        </tr>
        @if($direccion !== '')
            <tr>
                <td colspan="2"><span class="lbl">Dirección</span>{{ $direccion }}</td>
            </tr>
        @endif
    </table>

    <div class="sec-titulo">Datos de la Orden</div>
    <table class="datos">
        <tr>
            <td width="50%"><span class="lbl">Nro. de Orden</span>{{ $nroOrden }}</td>
            <td width="50%"><span class="lbl">{{ $ordenEmpresa ? 'Nro. Ticket' : 'Nro. Factura' }}</span>{{ $nroFactura !== '' ? $nroFactura : ($nroFactura2 !== '' ? '' : '—') }}{{ $nroFactura2 !== '' ? (' / ' . $nroFactura2) : '' }}</td>
        </tr>
        <tr>
            <td><span class="lbl">Estado de la Orden</span>{{ $estadoOrden }}</td>
            <td><span class="lbl">Estado Final del Equipo</span><span class="estado-badge" style="background:{{ $colorEstado }};">{{ $estadoEquipo }}</span></td>
        </tr>
        @if($repuestos->isNotEmpty())
            <tr>
                <td colspan="2">
                    <span class="lbl">Repuestos Utilizados</span>
                    @foreach($repuestos as $r)
                        <div style="margin-bottom:3px;">
                            @if($r->codigo !== '')<strong>{{ $r->codigo }}</strong> &mdash; @endif{{ $r->nombre }}@if($r->nro_parte !== '') <span style="color:#64748b;font-size:9pt;">(Nro. Parte: {{ $r->nro_parte }})</span>@endif
                        </div>
                    @endforeach
                </td>
            </tr>
        @endif
    </table>

    <div class="sec-titulo">Datos del Equipo</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tipo</span>{{ $equipo?->tipo ?? '—' }}</td>
            <td width="25%"><span class="lbl">Marca</span>{{ $equipo?->marca ?? '—' }}</td>
            <td width="25%"><span class="lbl">Código / Modelo</span>{{ $equipo?->modelo ?? '—' }}</td>
            <td width="25%"><span class="lbl">Serie</span>{{ $equipo?->serie ?? '—' }}</td>
        </tr>
    </table>

    <div class="sec-titulo">Antecedentes</div>
    <div class="texto-campo">{{ $informe->antecedentes ?? '' }}</div>

    <div class="sec-titulo">Proceso</div>
    <div class="texto-campo">{{ $informe->proceso ?? '' }}</div>

    @if(!empty($informe->conclusion))
        <div class="sec-titulo">Conclusión</div>
        <div class="texto-campo">{{ $informe->conclusion }}</div>
    @endif

    @if(!empty($informe->recomendaciones))
        <div class="sec-titulo">Recomendaciones</div>
        <div class="texto-campo">{{ $informe->recomendaciones }}</div>
    @endif

    @if($informe->fotos->isNotEmpty())
        <div class="sec-titulo">Evidencia Fotográfica</div>
        <table style="width:100%;border-collapse:collapse;">
            @foreach($informe->fotos->chunk(2) as $fila)
                <tr>
                    @foreach($fila as $foto)
                        @php
                            $rutaFoto = (string) ($foto->foto_data ?? '');
                            $src = str_starts_with($rutaFoto, 'data:') ? $rutaFoto : asset('storage/' . ltrim($rutaFoto, '/'));
                        @endphp
                        <td style="padding:6px;text-align:center;">
                            <img src="{{ $src }}" style="max-width:220px;max-height:180px;border-radius:4px;border:1px solid #ddd;" alt="Foto">
                            @if(!empty($foto->caption))
                                <div style="font-size:8pt;color:#555;margin-top:4px;">{{ $foto->caption }}</div>
                            @endif
                        </td>
                    @endforeach
                    @if($fila->count() === 1)
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endif

    <div class="firmas">
        <div class="firma-box"><div class="firma-linea">Técnico responsable</div></div>
        <div class="firma-box"><div class="firma-linea">Recibido conforme</div></div>
    </div>

    <div style="text-align:center;margin-top:10px;font-size:7pt;color:#94a3b8;border-top:1px solid #e5e7eb;padding-top:6px;">
        Novitecnología Cía. Ltda. - Sistema de Gestion Novitec
    </div>
</div>
</body>
</html>
