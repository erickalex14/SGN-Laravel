@php
    $empresa = $orden->empresa;
    $equipo = $orden->equipo;
    $tecnico = $orden->tecnico;
    $sucursal = $orden->sucursal;
    $usuarioIngreso = $orden->ingresadoPor;
    $series = collect();
    if ($equipo) {
        if (!$equipo->relationLoaded('series')) {
            $equipo->load('series');
        }
        $series = $equipo->series->pluck('serie')->filter();
    }
    if ($series->isEmpty() && !empty($equipo?->serie)) {
        $series = collect(explode(',', (string) $equipo->serie))->map(fn($s) => trim($s))->filter();
    }

    // Concatenamos técnicos asignados si existen
    $nombresTecnicos = '-';
    $correosTecnicos = '-';
    if ($orden->relationLoaded('tecnicos') && $orden->tecnicos->isNotEmpty()) {
        $nombresTecnicos = $orden->tecnicos->pluck('nombre_tecnico')->implode(', ');
        $correosTecnicos = $orden->tecnicos->pluck('correo_tec')->filter()->implode(', ');
    } elseif ($tecnico) {
        $nombresTecnicos = $tecnico->nombre_tecnico;
        $correosTecnicos = $tecnico->correo_tec;
    }

    // Cargamos los precios personalizados (para empresas no hay adicionales específicos en esta relación)
    $preciosAdicionales = collect();
    
    // Verificamos si es servicios para aplicar la fórmula
    $esNovisolutionsServicio = $orden->subtipo === 'Servicios';

    if ($esNovisolutionsServicio) {
        $cantTecnicos = $orden->relationLoaded('tecnicos') && $orden->tecnicos->isNotEmpty() ? $orden->tecnicos->count() : 1;
        $horas = (float) ($orden->horas_trabajadas ?? 0);
        $valHora = (float) ($orden->valor_hora ?? 0);
        $precioCalculado = $cantTecnicos * $horas * $valHora;

        $preciosEstandar = collect([
            (object)[
                'servicio' => 'SERVICIO TÉCNICO CORPORATIVO',
                'precio' => $precioCalculado,
                'descripcion' => "CÁLCULO: {$cantTecnicos} técnico(s) * " . number_format($horas, 2) . " hora(s) * $" . number_format($valHora, 2) . "/hr"
            ]
        ]);
    } else {
        $preciosEstandar = collect([
            (object)[
                'servicio' => 'COSTO DE REVISION DEL EQUIPO',
                'precio' => 28.00,
                'descripcion' => 'COSTO DE REVISION TÉCNICA DEL EQUIPO'
            ]
        ]);
    }

    // Cálculos de subtotal y totales
    $subtotalAdicionales = $preciosAdicionales->sum('precio');
    $subtotalEstandar = $preciosEstandar->sum('precio');
    $subtotalTotal = $subtotalAdicionales + $subtotalEstandar;

    // Para órdenes corporativas no se aplica el descuento por defecto de garantía personal
    $aplicaDescuento = false;
    $descuento = 0;
    $baseIva = $subtotalTotal;
    $iva = $baseIva * 0.15;
    $total = $baseIva + $iva;

    $hayPrecios = $preciosAdicionales->isNotEmpty() || $preciosEstandar->isNotEmpty();
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
.condiciones-wrap { border-top: 1.5px solid #000; padding-top: 4px; }
.condiciones-titulo { text-align: center; font-weight: 700; font-size: 7.5pt; text-decoration: underline; margin-bottom: 2px; }
.condiciones { font-size: 5.5pt; text-align: justify; line-height: 1.28; color: #111; }
.condiciones p { margin-bottom: 1px; }
.nota-final { margin-top: 8px; padding: 5px 10px; background: #fef9c3; border: 1px solid #fde047; border-radius: 3px; font-size: 7.5pt; color: #713f12; text-align: center; }
.firmas { display: flex; justify-content: space-between; margin-top: 22px; }
.firma-box { width: 44%; text-align: center; }
.firma-linea { border-top: 1px solid #000; padding-top: 3px; font-size: 7pt; margin-top: 20px; }
.foot { text-align: center; margin-top: 10px; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 6px; }

/* ── Tabla de precios ── */
table.precios-tbl { width: 100%; border-collapse: collapse; margin-bottom: 2px; font-size: 7pt; }
table.precios-tbl th { background: #1a56db; color: #fff; padding: 2px 6px; text-align: left; font-size: 6.5pt; text-transform: uppercase; }
table.precios-tbl th.r { text-align: right; }
table.precios-tbl td { border-bottom: 1px solid #e5e7eb; padding: 2px 6px; vertical-align: top; }
table.precios-tbl td.r { text-align: right; font-weight: 700; color: #059669; }
table.precios-tbl tr.estandar-row td { background: #f0fdf4; }
table.precios-tbl tr.adicional-row td { background: #fff; }
table.precios-tbl tr.sep-row td { background: #f8fafc; font-weight: 700; font-size: 6pt; text-transform: uppercase; color: #64748b; padding: 2px 6px; border-bottom: 1px solid #cbd5e1; }
.totales-box { display: flex; justify-content: flex-end; margin-bottom: 7px; }
.totales-inner { border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden; min-width: 200px; }
.totales-inner table { width: 100%; border-collapse: collapse; font-size: 7pt; }
.totales-inner table td { padding: 2px 8px; }
.totales-inner table td:last-child { text-align: right; font-weight: 700; }
.totales-inner tr.subtotal td { background: #f8fafc; }
.totales-inner tr.iva-row td { background: #fefce8; color: #92400e; }
.totales-inner tr.descuento-row td { background: #fee2e2; color: #991b1b; }
.totales-inner tr.total-row td { background: #1a56db; color: #fff; font-size: 8pt; }
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
            <td>
                @if ($orden->nro_sucursal_cliente)
                    <span class="lbl">Sucursal Cliente</span>{{ $nombreSucursalCliente ?? $orden->nro_sucursal_cliente }}
                    @if ($orden->nro_ticket)
                        <br><span class="lbl">Nro. Ticket</span>{{ $orden->nro_ticket }}
                    @endif
                @else
                    <span class="lbl">Nro. Ticket</span>{{ $orden->nro_ticket ?: '-' }}
                @endif
            </td>
        </tr>
    </table>

    <div class="sec-titulo">Tecnico Responsable</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tecnico Asignado</span>{{ $nombresTecnicos }}</td>
            <td width="25%"><span class="lbl">Correo</span>{{ $correosTecnicos }}</td>
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

    @if ($hayPrecios)
        <!-- ══ TABLA DE VALORES ══ -->
        <div class="sec-titulo">Valores del Servicio</div>
        <table class="precios-tbl">
            <thead>
                <tr>
                    <th style="width:45%">Concepto</th>
                    <th style="width:30%">Detalle</th>
                    <th class="r" style="width:25%">Precio (sin IVA)</th>
                </tr>
            </thead>
            <tbody>
                @if ($preciosAdicionales->isNotEmpty())
                    <tr class="sep-row">
                        <td colspan="3">Valores del tipo de servicio / adicionales</td>
                    </tr>
                    @foreach ($preciosAdicionales as $p)
                        <tr class="adicional-row">
                            <td>{{ $p->servicio }}</td>
                            <td>{{ $p->descripcion }}</td>
                            <td class="r">${{ number_format($p->precio, 2) }}</td>
                        </tr>
                    @endforeach
                @endif

                @if ($preciosEstandar->isNotEmpty())
                    <tr class="sep-row">
                        <td colspan="3">Valores estándar</td>
                    </tr>
                    @foreach ($preciosEstandar as $p)
                        <tr class="estandar-row">
                            <td>{{ $p->servicio }}</td>
                            <td>{{ $p->descripcion }}</td>
                            <td class="r">${{ number_format($p->precio, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totales-box">
            <div class="totales-inner">
                <table>
                    <tr class="subtotal">
                        <td>Subtotal</td>
                        <td>${{ number_format($subtotalTotal, 2) }}</td>
                    </tr>
                    @if ($aplicaDescuento)
                        <tr class="descuento-row">
                            <td><strong>Descuento 100% (Garantía)</strong></td>
                            <td>-${{ number_format($descuento, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="iva-row">
                        <td>IVA 15%</td>
                        <td>${{ number_format($iva, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td><strong>${{ number_format($total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    @endif
    @if (!empty($historialIngresos) && count($historialIngresos) > 0)
        <div class="sec-titulo">Historial de Ingresos Anteriores (Reingresos)</div>
        <table class="datos" style="margin-bottom: 8px;">
            <thead>
                <tr style="background:#f1f5f9; font-weight:bold; font-size:7pt;">
                    <th style="padding:4px 6px; border:1px solid #cbd5e1; text-align:left;">Ingreso #</th>
                    <th style="padding:4px 6px; border:1px solid #cbd5e1; text-align:left;">Nro. Orden</th>
                    <th style="padding:4px 6px; border:1px solid #cbd5e1; text-align:left;">Fecha de Ingreso</th>
                    <th style="padding:4px 6px; border:1px solid #cbd5e1; text-align:left;">Técnico que Ingresó</th>
                    <th style="padding:4px 6px; border:1px solid #cbd5e1; text-align:left;">Técnico Asignado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($historialIngresos as $index => $hist)
                    <tr style="font-size:7.5pt;">
                        <td style="padding:4px 6px; border:1px solid #cbd5e1;">Ingreso #{{ $index + 1 }}</td>
                        <td style="padding:4px 6px; border:1px solid #cbd5e1; font-weight:bold; color:#1a56db;">{{ $hist['nro_orden'] }}</td>
                        <td style="padding:4px 6px; border:1px solid #cbd5e1;">{{ $hist['fecha_ingreso'] ? \Carbon\Carbon::parse($hist['fecha_ingreso'])->format('d/m/Y H:i') : '-' }}</td>
                        <td style="padding:4px 6px; border:1px solid #cbd5e1;">{{ $hist['tecnico_ingreso'] }}</td>
                        <td style="padding:4px 6px; border:1px solid #cbd5e1;">{{ $hist['tecnico_asignado'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="border-top:1.5px solid #000;padding-top:4px;">
        <div class="condiciones-titulo">Condiciones</div>
        <div class="condiciones">
            <p><b>1. VALIDACIÓN GARANTÍA:</b> Los equipos que ingresen bajo esta condición deberán ser evaluados obligatoriamente por un técnico, quien determinará por escrito si éstos cumplen con las condiciones establecidas por los fabricantes y que están disponibles en la documentación y/o manuales suministrados por ellos.</p>
            <p><b>2. EMISIÓN DE PRESUPUESTO:</b> Si un equipo ingresa por validación de garantía, y éste no cumple con las condiciones establecidas por el fabricante, será tratado como "Fuera de Garantía", y se emitirá un informe técnico con las novedades del equipo y un presupuesto aproximado de reparación; el mismo que podrá aceptado o negado por el cliente. En el caso que el cliente niegue el presupuesto o el equipo no se pueda reparar, éste deberá cancelar el valor de revisión, que en todos los casos será de $28+IVA. Si el cliente acepta reparar su equipo y el resultado final es que el equipo está operacional, solo se cobrará el valor presupuestado. En caso que se necesite derivar el equipo a un taller externo para validación de garantía y ésta sea negada, el cliente deberá cancelar los valores por concepto de revisión o reparación que fije dicho taller externo de acuerdo a sus políticas.</p>
            <p><b>3. INTENTO DE REPARACIÓN:</b> El cliente es consciente que, al intentar reparar el equipo, es posible que éste sufra un daño mayor o irreparable y autoriza al Centro de Servicio a proceder con el intento de reparación por lo que, expresamente libera a NOVITECNOLOGIA de cualquier responsabilidad por este concepto.</p>
            <p><b>4. EQUIPOS ABANDONADOS Y DACIÓN EN PAGO:</b> Se considerará como "abandonado" a todo equipo que no haya sido retirado después de 30 días calendario después de finalizada la reparación y/o de haber notificado al cliente la finalización de la revisión o reparación. En caso de haberse cumplido este plazo sin que el cliente haya pagado sus valores adeudados, a dichos valores le serán sumados cargos adicionales por concepto de bodegaje y custodia, por un monto de $1,00 diario. En caso de que el cliente no retire el equipo luego de transcurridos 90 días calendario, se lo considerará como "abandono definitivo" y el cliente concederá la transferencia definitiva de la propiedad del equipo, pudiendo NOVITECNOLOGIA hacer uso como a bien tuviere.</p>
            <p><b>5. RESPALDO DE INFORMACIÓN:</b> El cliente es el único responsable de realizar el debido respaldo de toda la información contenida en su equipo. NOVITECNOLOGIA no asume responsabilidad alguna sobre la conservación, uso o pérdida de ningún tipo de información contenida en el equipo. El cliente acepta y autoriza a NOVITECNOLOGIA a tener acceso al contenido de su dispositivo, en la medida que fuese indispensable para cumplir con el objetivo de la revisión y/o reparación solicitada.</p>
            <p><b>6. DOCUMENTACIÓN:</b> El presente documento es el único válido para el retiro del equipo ingresado a NOVITECNOLOGIA. El cliente podrá, a su exclusiva responsabilidad, delegar a otra persona el retiro de su equipo o dispositivo, para lo cual bastará la presentación del original del presente documento. NOVITECNOLOGIA se reserva el derecho de rechazar la entrega de un equipo en caso de que el documento esté ilegible, adulterado o por no ser el documento original.</p>
            <p><b>7. RESOLUCIÓN DE CONTROVERSIAS:</b> La legislación aplicable a este contrato es la ecuatoriana. Las partes contratantes harán todo lo posible para resolver las controversias que surgieren en forma amistosa, de buena fe, mediante negociaciones directas, agotando todas las instancias incluidas mediación y arbitraje.</p>
            <p>Con la suscripción de este documento, el cliente declara haberlo leído, comprendido y aceptado las cláusulas descritas en todos sus aspectos, lo cual significa que conoce todas las condiciones de la reparación de su dispositivo. En tal sentido, una vez que el cliente ha estampado su firma en el presente, no podrá alegar desconocimiento de las condiciones aquí señaladas.</p>
            <p><b>Políticas de privacidad y uso de datos:</b> El cliente autoriza uso de datos compartidos en este documento, si desea dar de baja envié su solicitud a información@novicompu.com. Para verificar el estado de su orden de trabajo puede comunicarse a los teléfonos: 026001635/026001797/0960500156 (Quito) - 096 050 0158 (mensaje Whatsapp - Guayaquil) o mediante los correos soporte@novitec.com.ec / servicios@novitec.com.ec</p>
        </div>
    </div>

    <div style="margin-top:8px;padding:5px 10px;background:#fef9c3;border:1px solid #fde047;border-radius:3px;font-size:7.5pt;color:#713f12;text-align:center;">
        <b>NOTA:</b> La p&eacute;rdida o reimpresi&oacute;n del presente documento de orden de trabajo tendr&aacute; un valor de <b>$5,00 + IVA</b>.
    </div>

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
