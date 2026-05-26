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
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 7.5pt; color: #000; background: #fff; }
@media print {
    @page { size: A4 portrait; margin: 10mm; }
    .no-print { display: none !important; }
    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
}
.wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 3px; margin-bottom: 4px; }
.header-info { font-size: 7pt; line-height: 1.4; }
.header-info .empresa { font-size: 9pt; font-weight: bold; }
.orden-header { display: flex; justify-content: space-between; align-items: center; background: #1a56db; color: #fff; padding: 3px 8px; border-radius: 3px; margin-bottom: 4px; }
.orden-header .nro { font-size: 10pt; font-weight: bold; }
.orden-header .meta { font-size: 6.5pt; text-align: right; }
.sec-titulo { background: #dbeafe; font-weight: bold; font-size: 6.5pt; text-transform: uppercase; padding: 2px 6px; border-left: 3px solid #1a56db; margin-bottom: 1px; }
table.datos { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
table.datos td { border: 1px solid #d1d5db; padding: 2px 5px; font-size: 7pt; vertical-align: top; }
table.datos td .lbl { font-size: 5.5pt; color: #6b7280; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 0; }
.condiciones-titulo { text-align: center; font-weight: bold; font-size: 7.5pt; text-decoration: underline; margin-bottom: 2px; }
.condiciones { font-size: 5.5pt; text-align: justify; line-height: 1.3; color: #111; }
.condiciones p { margin-bottom: 1px; }
.badge-preorden { display:inline-block; background:#fef9c3; color:#92400e; border:1px solid #fde047; padding:1px 7px; border-radius:3px; font-size:6.5pt; font-weight:bold; margin-left:6px; }
.badge-orden-ref { display:inline-block; background:#dcfce7; color:#166534; border:1px solid #86efac; padding:1px 7px; border-radius:3px; font-size:6.5pt; font-weight:bold; margin-left:6px; }
.btn-print { position: fixed; top: 10px; right: 10px; background: #1a56db; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: bold; z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.firmas { display: flex; justify-content: space-between; margin: 10px 0 4px; }
.firma-box { width: 44%; text-align: center; }
.firma-linea { border-top: 1px solid #000; padding-top: 3px; font-size: 7pt; margin-top: 20px; }
</style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Guardar PDF</button>
<div class="wrap">
    <div class="header">
        <div class="header-info">
            <div class="empresa">Novitecnologia Cia. Ltda.</div>
            <div><b>Telefonos:</b></div>
            <div><b>GYE:</b> 04-6031337 / 0960500158 &nbsp;&nbsp; <b>UIO:</b> 02-6001635 / 0960500156</div>
            <div>https://www.novitec.com.ec</div>
        </div>
    </div>

    <div class="orden-header">
        <div class="nro">
            Pre-Orden: {{ $nroPreorden }}
            <span class="badge-preorden">PRE-ORDEN</span>
            @if($tieneOrden)
                <span class="badge-orden-ref">Orden SGN: {{ $ordenRef }}</span>
            @endif
        </div>
        <div class="meta">
            Fecha de registro: {{ $fechaRegistro }}<br>
            Estado: {{ $estadoTexto }}
        </div>
    </div>

    <div class="sec-titulo">Datos del Cliente</div>
    <table class="datos">
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

    <div class="sec-titulo">Datos del Equipo</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tipo</span>{{ $valor($o, 'tipo_producto') }}</td>
            <td width="25%"><span class="lbl">Marca</span>{{ $valor($o, 'marca_producto') }}</td>
            <td width="25%"><span class="lbl">Codigo</span>{{ $valor($o, 'codigo_producto') }}</td>
            <td width="25%"><span class="lbl">Fecha Facturacion</span>{{ $valor($o, 'fecha_facturacion') }}</td>
        </tr>
        <tr><td colspan="4"><span class="lbl">Descripcion del Equipo</span>{{ $valor($o, 'desc_producto') }}</td></tr>
        <tr><td colspan="4"><span class="lbl">Problema Reportado por el Cliente</span>{{ $valor($o, 'detalle_equipo') }}</td></tr>
    </table>

    <div style="border-top:1.5px solid #000;padding-top:4px;margin-top:6px;">
        <div class="condiciones-titulo">Condiciones</div>
        <div class="condiciones">
            <p><b>1. VALIDACION GARANTIA:</b> Los equipos que ingresen bajo esta condicion deberan ser evaluados obligatoriamente por un tecnico, quien determinara por escrito si estos cumplen con las condiciones establecidas por los fabricantes y que estan disponibles en la documentacion y/o manuales suministrados por ellos.</p>
            <p><b>2. EMISION DE PRESUPUESTO:</b> Si un equipo ingresa por validacion de garantia, y este no cumple con las condiciones establecidas por el fabricante, sera tratado como "Fuera de Garantia", y se emitira un informe tecnico con las novedades del equipo y un presupuesto aproximado de reparacion; el mismo que podra aceptado o negado por el cliente. En el caso que el cliente niegue el presupuesto o el equipo no se pueda reparar, este debera cancelar el valor de revision, que en todos los casos sera de $28+IVA. Si el cliente acepta reparar su equipo y el resultado final es que el equipo esta operacional, solo se cobrara el valor presupuestado. En caso que se necesite derivar el equipo a un taller externo para validacion de garantia y esta sea negada, el cliente debera cancelar los valores por concepto de revision o reparacion que fije dicho taller externo de acuerdo a sus politicas.</p>
            <p><b>3. INTENTO DE REPARACION:</b> El cliente es consciente que, al intentar reparar el equipo, es posible que este sufra un dano mayor o irreparable y autoriza al Centro de Servicio a proceder con el intento de reparacion por lo que, expresamente libera a NOVITECNOLOGIA de cualquier responsabilidad por este concepto.</p>
            <p><b>4. EQUIPOS ABANDONADOS Y DACION EN PAGO:</b> Se considerara como "abandonado" a todo equipo que no haya sido retirado despues de 30 dias calendario despues de finalizada la reparacion y/o de haber notificado al cliente la finalizacion de la revision o reparacion. En caso de haberse cumplido este plazo sin que el cliente haya pagado sus valores adeudados, a dichos valores le seran sumados cargos adicionales por concepto de bodegaje y custodia, por un monto de $1,00 diario. En caso de que el cliente no retire el equipo luego de transcurridos 90 dias calendario, se lo considerara como "abandono definitivo" y el cliente concedera la transferencia definitiva de la propiedad del equipo, pudiendo NOVITECNOLOGIA hacer uso como a bien tuviere.</p>
            <p><b>5. RESPALDO DE INFORMACION:</b> El cliente es el unico responsable de realizar el debido respaldo de toda la informacion contenida en su equipo. NOVITECNOLOGIA no asume responsabilidad alguna sobre la conservacion, uso o perdida de ningun tipo de informacion contenida en el equipo. El cliente acepta y autoriza a NOVITECNOLOGIA a tener acceso al contenido de su dispositivo, en la medida que fuese indispensable para cumplir con el objetivo de la revision y/o reparacion solicitada.</p>
            <p><b>6. DOCUMENTACION:</b> El presente documento es el unico valido para el retiro del equipo ingresado a NOVITECNOLOGIA. El cliente podra, a su exclusiva responsabilidad, delegar a otra persona el retiro de su equipo o dispositivo, para lo cual bastara la presentacion del original del presente documento. NOVITECNOLOGIA se reserva el derecho de rechazar la entrega de un equipo en caso de que el documento este ilegible, adulterado o por no ser el documento original.</p>
            <p><b>7. RESOLUCION DE CONTROVERSIAS:</b> La legislacion aplicable a este contrato es la ecuatoriana. Las partes contratantes haran todo lo posible para resolver las controversias que surgieren en forma amistosa, de buena fe, mediante negociaciones directas, agotando todas las instancias incluidas mediacion y arbitraje.</p>
            <p>Con la suscripcion de este documento, el cliente declara haberlo leido, comprendido y aceptado las clausulas descritas en todos sus aspectos, lo cual significa que conoce todas las condiciones de la reparacion de su dispositivo. En tal sentido, una vez que el cliente ha estampado su firma en el presente, no podra alegar desconocimiento de las condiciones aqui senaladas.</p>
            <p><b>Politicas de privacidad y uso de datos:</b> El cliente autoriza uso de datos compartidos en este documento, si desea dar de baja envie su solicitud a informacion@novicompu.com. Para verificar el estado de su orden de trabajo puede comunicarse a los telefonos: 026001635/026001797/0960500156 (Quito) - 0960500158 (mensaje Whatsapp - Guayaquil) o mediante los correos soporte@novitec.com.ec / servicios@novitec.com.ec.</p>
        </div>
    </div>

    <div style="margin-top:8px;padding:5px 10px;background:#fef9c3;border:1px solid #fde047;border-radius:3px;font-size:7.5pt;color:#713f12;text-align:center;">
        <b>NOTA:</b> Este documento es una pre-orden. La atencion formal inicia cuando entregues fisicamente tu equipo en la tienda Novitecnologia indicada.
        @if($tieneOrden)
            &nbsp;|&nbsp; <b>Orden de trabajo asignada: {{ $ordenRef }}</b>
        @endif
    </div>

    <div class="firmas">
        <div class="firma-box"><div class="firma-linea">Recibido por:</div></div>
        <div class="firma-box"><div class="firma-linea">Firma del cliente:</div></div>
    </div>

    <div style="text-align:center;margin-top:8px;font-size:7pt;color:#94a3b8;border-top:1px solid #e5e7eb;padding-top:6px;">
        Novitecnologia Cia. Ltda. | Sistema de Gestion Novitecnologia Cia. Ltda.
        &nbsp;|&nbsp; Impreso el: <span id="hora-impresion"></span>
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
