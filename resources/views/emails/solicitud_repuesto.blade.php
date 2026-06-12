<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6;">
    <div style="max-width: 600px; margin: 32px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 32px 24px; text-align: center; color: #ffffff;">
            <span style="background-color: rgba(255, 255, 255, 0.15); padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Solicitud de Repuesto</span>
            <h1 style="margin: 12px 0 0 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">Nuevo Repuesto Requerido</h1>
            <p style="margin: 6px 0 0 0; font-size: 14px; opacity: 0.85;">Se ha ingresado una nueva solicitud de repuesto en taller</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px 24px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 24px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px;">
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block;">Nro. Solicitud</span>
                    <span style="font-family: monospace; font-size: 16px; font-weight: 700; color: #1e3a8a;">{{ $nro_solicitud }}</span>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block;">Orden Asociada</span>
                    <span style="font-family: monospace; font-size: 16px; font-weight: 700; color: #0f172a;">{{ $nro_orden }}</span>
                </div>
            </div>

            <h3 style="margin: 0 0 16px 0; font-size: 15px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Detalle del Repuesto</h3>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; width: 140px; vertical-align: top;">Nombre Repuesto:</td>
                    <td style="padding: 8px 0; font-size: 14px; font-weight: 700; color: #0f172a; vertical-align: top;">{{ $repuesto_nombre }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Cantidad:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top; font-weight: 700;">{{ $cantidad }} unidad(es)</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Número de Parte:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top; font-family: monospace;">{{ $nro_parte }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Técnico Solicitante:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top; font-weight: 600;">{{ $tecnico }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Sucursal:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top;">{{ $sucursal }}</td>
                </tr>
                @if($link_compra && $link_compra !== 'No especificado')
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Enlace de Compra:</td>
                    <td style="padding: 8px 0; font-size: 13px; vertical-align: top;">
                        <a href="{{ $link_compra }}" target="_blank" style="color: #2563eb; font-weight: 600; text-decoration: underline;">Ver Enlace de Referencia</a>
                    </td>
                </tr>
                @endif
            </table>

            <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
                <h4 style="margin: 0 0 6px 0; font-size: 13.5px; color: #14532d; font-weight: 700; text-transform: uppercase;">Notas / Justificación del Técnico:</h4>
                <p style="margin: 0; font-size: 13.5px; color: #166534; font-style: italic;">{{ $descripcion }}</p>
            </div>

            <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: right;">Fecha Solicitud: {{ $fecha }}</p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #f1f5f9;">
            <p style="margin: 0;">Este correo es una notificación automática del sistema Novitec SGN. Por favor, no responda a este mensaje.</p>
            <p style="margin: 6px 0 0 0;">&copy; {{ date('Y') }} Novicompu. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
