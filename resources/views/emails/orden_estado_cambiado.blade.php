<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6;">
    <div style="max-width: 600px; margin: 32px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0284c7, #0f172a); padding: 32px 24px; text-align: center; color: #ffffff;">
            <span style="background-color: rgba(255, 255, 255, 0.15); padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Actualización de Orden</span>
            <h1 style="margin: 12px 0 0 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">Estado de Orden Cambiado</h1>
            <p style="margin: 6px 0 0 0; font-size: 14px; opacity: 0.85;">Una orden ha sido actualizada con un nuevo estado</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px 24px;">
            <div style="text-align: center; margin-bottom: 28px;">
                <span style="font-family: monospace; font-size: 24px; font-weight: 800; color: #0284c7; background-color: #f0f9ff; padding: 8px 16px; border-radius: 8px; border: 1.5px dashed #bae6fd; display: inline-block;">{{ $nro_orden }}</span>
            </div>

            <!-- Status Transition UI -->
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 32px; background-color: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9; text-align: center;">
                <div style="display: inline-block; padding: 8px 16px; background-color: #e2e8f0; color: #475569; font-weight: 700; font-size: 14px; border-radius: 8px;">
                    {{ $estado_anterior }}
                </div>
                <div style="display: inline-block; margin: 0 16px; font-size: 18px; color: #94a3b8; font-weight: bold;">
                    &rarr;
                </div>
                @php
                    $newBg = '#e0f2fe';
                    $newColor = '#0369a1';
                    if (in_array(strtolower($estado_nuevo), ['finalizada', 'finalizado', 'reparado', 'reparada'])) {
                        $newBg = '#dcfce7';
                        $newColor = '#15803d';
                    } elseif (in_array(strtolower($estado_nuevo), ['entregada', 'entregado'])) {
                        $newBg = '#fef3c7';
                        $newColor = '#b45309';
                    } elseif (in_array(strtolower($estado_nuevo), ['pendiente', 'ingreso'])) {
                        $newBg = '#eff6ff';
                        $newColor = '#1d4ed8';
                    } elseif (in_array(strtolower($estado_nuevo), ['en proceso', 'revision', 'revisión'])) {
                        $newBg = '#f3e8ff';
                        $newColor = '#6b21a8';
                    } elseif (in_array(strtolower($estado_nuevo), ['nota de credito', 'nota de crédito'])) {
                        $newBg = '#fee2e2';
                        $newColor = '#b91c1c';
                    }
                @endphp
                <div style="display: inline-block; padding: 8px 16px; background-color: {{ $newBg }}; color: {{ $newColor }}; font-weight: 700; font-size: 14px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    {{ $estado_nuevo }}
                </div>
            </div>

            <h3 style="margin: 0 0 16px 0; font-size: 15px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Resumen de la Orden</h3>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; width: 140px; vertical-align: top;">Tipo de Orden:</td>
                    <td style="padding: 8px 0; font-size: 14px; font-weight: 700; color: #0f172a; vertical-align: top;">{{ $tipo_orden }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Sucursal:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top;">{{ $sucursal }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Cliente / Empresa:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top; font-weight: 600;">{{ $nombre_cliente }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Identificación:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top; font-family: monospace;">{{ $identificacion }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Equipo / Dispositivo:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top;">{{ $equipo }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Nro. Serie:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top; font-family: monospace;">{{ $serie }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; vertical-align: top;">Técnico Asignado:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #334155; vertical-align: top; font-weight: 600;">{{ $tecnico }}</td>
                </tr>
            </table>

            <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: right;">Fecha Cambio: {{ $fecha }}</p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #f1f5f9;">
            <p style="margin: 0;">Este correo es una notificación automática del sistema Novitec SGN. Por favor, no responda a este mensaje.</p>
            <p style="margin: 6px 0 0 0;">&copy; {{ date('Y') }} Novicompu. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
