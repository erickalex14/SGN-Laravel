<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Rol de Pagos</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
@endverbatim
<style>
body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #cbd5e1; padding: 8px 12px; vertical-align: middle; }
.header-title { background: #1e3a8a; color: #ffffff; font-size: 14pt; font-weight: bold; text-align: center; height: 40px; }
.header-sub { background: #eff6ff; color: #1e40af; font-size: 10pt; font-weight: bold; text-align: center; height: 25px; }
.th-col { background: #1e293b; color: #ffffff; font-weight: bold; font-size: 10pt; text-align: center; height: 30px; }
.num { text-align: right; mso-number-format: "\$#,##0.00"; font-weight: bold; }
.total-row { background: #0f766e; color: #ffffff; font-weight: bold; font-size: 11pt; height: 32px; }
.total-row td { border: 1px solid #047857; }
</style>
</head>
<body>
<table>
    <tr>
        <td colspan="21" class="header-title">NOVITECNOLOGIA CIA. LTDA. — ROL DE PAGOS MENSUAL DE NÓMINA</td>
    </tr>
    <tr>
        <td colspan="21" class="header-sub">Fecha de Generación: {{ $fechaEmision }} &nbsp;|&nbsp; Entorno: Control de Nómina Interna</td>
    </tr>
    <tr><td colspan="21" style="height:10px; border:none;"></td></tr>
    <thead>
        <tr>
            <th class="th-col">N°</th>
            <th class="th-col">Usuario</th>
            <th class="th-col">Cédula / DNI</th>
            <th class="th-col">Nombres Completos</th>
            <th class="th-col">Cargo / Puesto</th>
            <th class="th-col">Sucursal</th>
            <th class="th-col">Estado Afiliación</th>
            <th class="th-col">Fecha Ingreso</th>
            <th class="th-col">Fecha Salida</th>
            <th class="th-col">Antigüedad (Años)</th>
            <th class="th-col">Días Vac/Año</th>
            <th class="th-col">Días Tomados</th>
            <th class="th-col">Días Pendientes</th>
            <th class="th-col">Estado Vacaciones</th>
            <th class="th-col">Teléfono Personal</th>
            <th class="th-col">Email Personal</th>
            <th class="th-col">Contacto Emergencia</th>
            <th class="th-col">Sueldo Base ($)</th>
            <th class="th-col">Bonificaciones ($)</th>
            <th class="th-col">Sanciones ($)</th>
            <th class="th-col">Total a Recibir ($)</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $count = 1;
            $sumSueldo = 0;
            $sumBonos = 0;
            $sumSanciones = 0;
            $sumNeto = 0;
        @endphp
        @foreach($usuarios as $u)
            @php 
                $dn = $u->datosNomina; 
                $sueldo = (float)($dn->sueldo_base ?? 0);
                $bonos = (float)($dn->bonificaciones ?? 0);
                $sancion = (float)($dn->sanciones ?? 0);
                $neto = (float)($dn->total_a_recibir ?? 0);

                $sumSueldo += $sueldo;
                $sumBonos += $bonos;
                $sumSanciones += $sancion;
                $sumNeto += $neto;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $count++ }}</td>
                <td>{{ $u->usuario }}</td>
                <td style="mso-number-format: '\@';">{{ $dn->cedula ?? $u->usuario }}</td>
                <td><strong>{{ $dn->nombres_completos ?? $u->nombre_tecnico ?? $u->usuario }}</strong></td>
                <td>{{ $dn->cargo ?: 'No especificado' }}</td>
                <td style="text-align: center;">{{ $u->sucursalPrincipal->ciudad ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $dn->estado_afiliacion ?? 'Por Afiliar' }}</td>
                <td style="text-align: center;">{{ $dn->fecha_ingreso ? $dn->fecha_ingreso->format('d/m/Y') : 'N/A' }}</td>
                <td style="text-align: center;">{{ $dn->fecha_salida ? $dn->fecha_salida->format('d/m/Y') : 'Activo' }}</td>
                <td style="text-align: center;"><strong>{{ $dn ? $dn->calcularAniosAntiguedad() : 0 }}</strong></td>
                <td style="text-align: center;">{{ $dn ? $dn->calcularDiasVacacionesAnuales() : 0 }}</td>
                <td style="text-align: center; color: #b91c1c;">{{ $dn ? $dn->calcularDiasTomados() : 0 }}</td>
                <td style="text-align: center; color: #047857; font-weight: bold;">{{ $dn ? $dn->calcularDiasPendientes() : 0 }}</td>
                <td style="text-align: center;">{{ $dn ? $dn->obtenerEstadoVacaciones() : 'N/A' }}</td>
                <td style="mso-number-format: '\@';">{{ $dn->telefono ?? '' }}</td>
                <td>{{ $dn->email_personal ?? '' }}</td>
                <td>{{ $dn->contacto_emergencia ?? '' }}</td>
                <td class="num">${{ number_format($sueldo, 2) }}</td>
                <td class="num" style="color: #15803d;">+${{ number_format($bonos, 2) }}</td>
                <td class="num" style="color: #b91c1c;">-${{ number_format($sancion, 2) }}</td>
                <td class="num" style="color: #047857;">${{ number_format($neto, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="17" style="text-align: right; padding-right: 15px;">TOTALES GENERALES NÓMINA:</td>
            <td class="num">${{ number_format($sumSueldo, 2) }}</td>
            <td class="num">+${{ number_format($sumBonos, 2) }}</td>
            <td class="num">-${{ number_format($sumSanciones, 2) }}</td>
            <td class="num">${{ number_format($sumNeto, 2) }}</td>
        </tr>
    </tfoot>
</table>
</body>
</html>
