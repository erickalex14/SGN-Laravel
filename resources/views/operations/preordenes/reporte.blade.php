@php
    $valor = function ($obj, string $campo, string $default = '-') {
        $v = $obj->{$campo} ?? null;
        return ($v === null || $v === '') ? $default : (string) $v;
    };

    $nroPreorden = $valor($o, 'nro_preorden', '-');
    $ordenRef = $valor($o, 'orden_ref', '');
    $tieneOrden = $ordenRef !== '';
    $estadoTexto = $tieneOrden ? 'Ingresada al SGN' : 'Pendiente de ingreso';
    $fechaRegistro = $valor($o, 'fecha_registro', '-');

    $sucCliNumero = (int) ($o->sucursal_cliente_numero ?? 0);
    $sucCliNombre = trim((string) ($o->sucursal_cliente_nombre ?? ''));
    $sucCliDisplay = $sucCliNumero > 0
        ? str_pad((string) $sucCliNumero, 3, '0', STR_PAD_LEFT) . ' - ' . $sucCliNombre
        : ($sucCliNombre !== '' ? $sucCliNombre : '-');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pre-Orden {{ $nroPreorden }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 7.6pt; color: #000; background: #fff; }
.wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }
.btn-print { position: fixed; top: 10px; right: 10px; background: #1a56db; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 3px; margin-bottom: 4px; gap: 10px; }
.header-info { font-size: 7pt; line-height: 1.35; }
.header-info .empresa { font-size: 9pt; font-weight: 700; }
.header img { height: 34px; }
.doc-header { display: flex; justify-content: space-between; align-items: center; background: #1a56db; color: #fff; padding: 3px 8px; border-radius: 3px; margin-bottom: 4px; }
.doc-header .nro { font-size: 10pt; font-weight: 700; }
.doc-header .meta { font-size: 6.5pt; text-align: right; line-height: 1.4; }
.pill { display: inline-block; border-radius: 999px; padding: 1px 8px; font-size: 6.2pt; font-weight: 700; margin-left: 4px; }
.pill.warn { background: #fef9c3; color: #92400e; border: 1px solid #fde047; }
.pill.ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.sec-title { background: #dbeafe; font-weight: 700; font-size: 6.5pt; text-transform: uppercase; padding: 2px 6px; border-left: 3px solid #1a56db; margin-bottom: 1px; }
table.data { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
table.data td { border: 1px solid #d1d5db; padding: 2px 5px; font-size: 7pt; vertical-align: top; }
.lbl { font-size: 5.5pt; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0; }
.condiciones-wrap { border-top: 1.5px solid #000; padding-top: 4px; margin-top: 6px; }
.condiciones-titulo { text-align: center; font-weight: 700; font-size: 7.5pt; text-decoration: underline; margin-bottom: 2px; }
.condiciones { font-size: 5.5pt; text-align: justify; line-height: 1.28; color: #111; }
.condiciones p { margin-bottom: 1px; }
.nota-final { margin-top: 8px; padding: 5px 10px; background: #fef9c3; border: 1px solid #fde047; border-radius: 3px; font-size: 7.5pt; color: #713f12; text-align: center; }
.firmas { display: flex; justify-content: space-between; margin: 10px 0 4px; }
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

    <div class="doc-header">
        <div class="nro">
            Pre-Orden: {{ $nroPreorden }}
            <span class="pill warn">PRE-ORDEN</span>
            @if($tieneOrden)
                <span class="pill ok">Orden SGN: {{ $ordenRef }}</span>
            @endif
        </div>
        <div class="meta">
            Fecha de registro: {{ $fechaRegistro }}<br>
            Estado: {{ $estadoTexto }}
        </div>
    </div>

    <div class="sec-title">Datos del Cliente</div>
    <table class="data">
        <tr>
            <td width="25%"><span class="lbl">Cliente</span>{{ $valor($o, 'nombres') }} {{ $valor($o, 'apellidos', '') }}</td>
            <td width="25%"><span class="lbl">C.I / RUC</span>{{ $valor($o, 'identificacion') }}</td>
            <td width="25%"><span class="lbl">Telefono</span>{{ $valor($o, 'telefono') }}</td>
            <td width="25%"><span class="lbl">Correo</span>{{ $valor($o, 'correo') }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="lbl">Sucursal del Cliente</span>{{ $sucCliDisplay }}</td>
            <td><span class="lbl">Motivo de Ingreso</span>Validacion de Garantia</td>
            <td><span class="lbl">Nro. Factura</span>{{ $valor($o, 'nro_factura') }}</td>
        </tr>
    </table>

    <div class="sec-title">Datos del Equipo</div>
    <table class="data">
        <tr>
            <td width="25%"><span class="lbl">Tipo</span>{{ $valor($o, 'tipo_producto') }}</td>
            <td width="25%"><span class="lbl">Marca</span>{{ $valor($o, 'marca_producto') }}</td>
            <td width="25%"><span class="lbl">Codigo</span>{{ $valor($o, 'codigo_producto') }}</td>
            <td width="25%"><span class="lbl">Fecha Facturacion</span>{{ $valor($o, 'fecha_facturacion') }}</td>
        </tr>
        <tr><td colspan="4"><span class="lbl">Serie</span>{{ $valor($o, 'serie') }}</td></tr>
        <tr><td colspan="4"><span class="lbl">Descripcion del Equipo</span>{{ $valor($o, 'desc_producto') }}</td></tr>
        <tr><td colspan="4"><span class="lbl">Problema Reportado por el Cliente</span>{{ $valor($o, 'detalle_equipo') }}</td></tr>
    </table>

    <div class="condiciones-wrap">
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

    <div class="nota-final">
        <strong>NOTA:</strong> Este documento es una pre-orden. La atencion formal inicia cuando entregues fisicamente tu equipo en la tienda Novitecnologia indicada.
        @if($tieneOrden)
            &nbsp;|&nbsp; <strong>Orden de trabajo asignada: {{ $ordenRef }}</strong>
        @endif
    </div>

    <div class="firmas">
        <div class="firma-box"><div class="firma-linea">Recibido por:</div></div>
        <div class="firma-box"><div class="firma-linea">Firma del cliente:</div></div>
    </div>

    <div class="foot">
        Novitecnologia Cia. Ltda. | Sistema de Gestion SGN | Impreso el:
        <span id="hora-impresion"></span>
    </div>
</div>

<script>
var ahora = new Date();
var pad = function(n){ return n < 10 ? '0' + n : n; };
document.getElementById('hora-impresion').textContent =
    pad(ahora.getDate()) + '/' + pad(ahora.getMonth() + 1) + '/' + ahora.getFullYear() +
    ' ' + pad(ahora.getHours()) + ':' + pad(ahora.getMinutes()) + ':' + pad(ahora.getSeconds());
</script>
</body>
</html>
