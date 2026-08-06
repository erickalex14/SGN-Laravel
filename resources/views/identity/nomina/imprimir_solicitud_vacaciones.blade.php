<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Vacaciones #{{ sprintf('%05d', $solicitud->id) }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; margin: 0; padding: 15px; }
        .container { max-width: 800px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 25px; border-radius: 6px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .logo-title { font-size: 18px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; }
        .sub-title { font-size: 12px; color: #64748b; margin-top: 2px; }
        .badge-status { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: bold; font-size: 12px; text-transform: uppercase; }
        .status-aprobado { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-pendiente { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
        .status-rechazado { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        
        .section-title { background: #f1f5f9; padding: 8px 12px; font-weight: bold; font-size: 12px; color: #0f172a; text-transform: uppercase; border-left: 4px solid #2563eb; margin-top: 20px; margin-bottom: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td, th { padding: 8px 10px; border: 1px solid #e2e8f0; vertical-align: top; }
        th { background: #f8fafc; font-weight: bold; text-align: left; color: #475569; width: 30%; }
        
        .grid-2 { display: flex; gap: 15px; }
        .col { flex: 1; }
        
        .signatures { margin-top: 60px; display: flex; justify-content: space-between; gap: 40px; }
        .sig-box { flex: 1; text-align: center; border-top: 1.5px solid #0f172a; padding-top: 8px; }
        .sig-title { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #334155; }

        @media print {
            body { padding: 0; }
            .container { border: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="background: #2563eb; color: #ffffff; border: none; padding: 8px 18px; font-weight: bold; border-radius: 4px; cursor: pointer;">
            Imprimir / Guardar en PDF
        </button>
    </div>

    <div class="container">
        <!-- Encabezado corporativo -->
        <div class="header">
            <div>
                <div class="logo-title">NOVITECNOLOGIA CIA. LTDA.</div>
                <div class="sub-title">Departamento de Recursos Humanos & Nómina Interna</div>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: bold; font-size: 14px; color: #0f172a;">SOLICITUD DE VACACIONES</div>
                <div style="font-size: 11px; color: #64748b;">N° {{ sprintf('%05d', $solicitud->id) }}</div>
                <div style="margin-top: 5px;">
                    <span class="badge-status status-{{ strtolower($solicitud->estado) }}">
                        {{ $solicitud->estado }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Información del Empleado -->
        <div class="section-title">1. Información del Colaborador</div>
        <table>
            <tr>
                <th>Nombres Completos:</th>
                <td><strong>{{ $datosNomina->nombres_completos ?? $empleado->nombre_tecnico ?? $empleado->usuario }}</strong></td>
                <th>Cédula / DNI:</th>
                <td>{{ $datosNomina->cedula ?? $empleado->usuario }}</td>
            </tr>
            <tr>
                <th>Cargo / Puesto:</th>
                <td>{{ $datosNomina->cargo ?: 'No especificado' }}</td>
                <th>Sucursal:</th>
                <td>{{ $empleado->sucursalPrincipal->ciudad ?? 'Quito' }}</td>
            </tr>
            <tr>
                <th>Fecha de Ingreso:</th>
                <td>{{ $datosNomina->fecha_ingreso ? $datosNomina->fecha_ingreso->format('d/m/Y') : 'No registrada' }}</td>
                <th>Antigüedad Servida:</th>
                <td><strong>{{ $datosNomina ? $datosNomina->calcularAniosAntiguedad() : 0 }} Años Cumplidos</strong></td>
            </tr>
        </table>

        <!-- Resumen de Días de Vacaciones -->
        <div class="section-title">2. Resumen de Saldo de Vacaciones</div>
        <table>
            <tr>
                <th>Días Anuales por Antigüedad:</th>
                <td><strong>{{ $datosNomina ? $datosNomina->calcularDiasVacacionesAnuales() : 0 }} Días / Año</strong> (15 base + días adicionales)</td>
            </tr>
            <tr>
                <th>Total Días Acumulados:</th>
                <td>{{ $datosNomina ? $datosNomina->calcularDiasTotalesAcumulados() : 0 }} Días</td>
            </tr>
            <tr>
                <th>Días Tomados Anteriormente:</th>
                <td>{{ $datosNomina ? $datosNomina->calcularDiasTomados() : 0 }} Días</td>
            </tr>
            <tr>
                <th>Días Disponibles / Pendientes:</th>
                <td><strong style="color: #059669; font-size: 14px;">{{ $datosNomina ? $datosNomina->calcularDiasPendientes() : 0 }} Días Disponibles</strong></td>
            </tr>
        </table>

        <!-- Detalle de la Solicitud -->
        <div class="section-title">3. Detalle de la Solicitud y Aprobación</div>
        <table>
            <tr>
                <th>Días Solicitados:</th>
                <td><strong style="font-size: 14px; color: #2563eb;">{{ $solicitud->dias_solicitados }} Día(s)</strong></td>
                <th>Días Aprobados:</th>
                <td><strong style="font-size: 14px; color: #166534;">{{ $solicitud->dias_aprobados ?? $solicitud->dias_solicitados }} Día(s)</strong></td>
            </tr>
            <tr>
                <th>Fecha Inicio Solicitada:</th>
                <td>{{ $solicitud->fecha_inicio ? $solicitud->fecha_inicio->format('d/m/Y') : 'N/A' }}</td>
                <th>Fecha Fin Solicitada:</th>
                <td>{{ $solicitud->fecha_fin ? $solicitud->fecha_fin->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @if($solicitud->estado === 'Aprobado')
            <tr>
                <th>Fecha Inicio Aprobada:</th>
                <td><strong>{{ $solicitud->fecha_inicio_aprobada ? $solicitud->fecha_inicio_aprobada->format('d/m/Y') : ($solicitud->fecha_inicio ? $solicitud->fecha_inicio->format('d/m/Y') : 'N/A') }}</strong></td>
                <th>Fecha Fin Aprobada:</th>
                <td><strong>{{ $solicitud->fecha_fin_aprobada ? $solicitud->fecha_fin_aprobada->format('d/m/Y') : ($solicitud->fecha_fin ? $solicitud->fecha_fin->format('d/m/Y') : 'N/A') }}</strong></td>
            </tr>
            @endif
            <tr>
                <th>Motivo / Observación Empleado:</th>
                <td colspan="3">{{ $solicitud->observacion_empleado ?: 'Ninguna' }}</td>
            </tr>
            @if(!empty($solicitud->observacion_admin))
            <tr>
                <th>Observación Administración:</th>
                <td colspan="3" style="color: #1e40af; background: #eff6ff;">{{ $solicitud->observacion_admin }}</td>
            </tr>
            @endif
        </table>

        <!-- Secciones de Firmas -->
        <div class="signatures">
            <div class="sig-box">
                <div style="height: 50px;"></div>
                <div class="sig-title">Firma del Colaborador</div>
                <div style="font-size: 11px; color: #64748b;">{{ $datosNomina->nombres_completos ?? $empleado->nombre_tecnico ?? $empleado->usuario }}</div>
                <div style="font-size: 10px; color: #94a3b8;">C.I. {{ $datosNomina->cedula ?? $empleado->usuario }}</div>
            </div>
            <div class="sig-box">
                <div style="height: 50px;"></div>
                <div class="sig-title">Aprobado por Gerencia / Admin Master</div>
                <div style="font-size: 11px; color: #64748b;">{{ $aprobador ? ($aprobador->nombre_tecnico ?? $aprobador->usuario) : 'Administración Master' }}</div>
                <div style="font-size: 10px; color: #94a3b8;">NOVITECNOLOGIA CIA. LTDA.</div>
            </div>
        </div>
    </div>
</body>
</html>
