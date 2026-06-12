<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6;">
    @php
        $esAprobada = $estado === 'Aprobada';
        $esRechazada = $estado === 'Rechazada';
        
        $colorTema = $esAprobada ? '#16a34a' : ($esRechazada ? '#dc2626' : '#d97706');
        $gradiente = $esAprobada 
            ? 'linear-gradient(135deg, #16a34a, #15803d)' 
            : ($esRechazada ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #f59e0b, #d97706)');
        
        $estadoTraducido = match($estado) {
            'Aprobada' => 'APROBADA Y DESPACHADA',
            'Rechazada' => 'RECHAZADA',
            default => 'APROBADA (COMPRA EN PROCESO)',
        };
    @endphp

    <div style="max-width: 600px; margin: 32px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
        <!-- Header -->
        <div style="background: {{ $gradiente }}; padding: 32px 24px; text-align: center; color: #ffffff;">
            <span style="background-color: rgba(255, 255, 255, 0.15); padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Gestión de Solicitud</span>
            <h1 style="margin: 12px 0 0 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">Estado de tu Solicitud</h1>
            <p style="margin: 6px 0 0 0; font-size: 14px; opacity: 0.85;">Tu solicitud de repuesto ha sido procesada</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px 24px;">
            <div style="text-align: center; margin-bottom: 28px;">
                <span style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 6px;">Nro. Solicitud: {{ $nro_solicitud }}</span>
                <span style="font-size: 15px; font-weight: 800; color: #ffffff; background-color: {{ $colorTema }}; padding: 8px 16px; border-radius: 6px; display: inline-block; letter-spacing: 0.5px;">{{ $estadoTraducido }}</span>
            </div>

            <h3 style="margin: 0 0 16px 0; font-size: 15px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Resumen</h3>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                <tr>
                    <td style="padding: 8px 0; font-size: 13.5px; font-weight: 600; color: #64748b; width: 140px; vertical-align: top;">Repuesto:</td>
                    <td style="padding: 8px 0; font-size: 14px; color: #0f172a; vertical-align: top; font-weight: 600;">{{ $repuesto_nombre }}</td>
                </tr>
            </table>

            @if($esRechazada && $motivo_rechazo)
                <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 13.5px; color: #991b1b; font-weight: 700; text-transform: uppercase;">Motivo de Rechazo:</h4>
                    <p style="margin: 0; font-size: 13.5px; color: #b91c1c; font-style: italic;">{{ $motivo_rechazo }}</p>
                </div>
            @endif

            @if($esAprobada)
                <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 13.5px; color: #14532d; font-weight: 700; text-transform: uppercase;">Indicaciones:</h4>
                    <p style="margin: 0; font-size: 13.5px; color: #166534;">El repuesto ha sido descontado de bodega. Ya puedes retirar el componente y proceder con el diagnóstico/reparación en el equipo.</p>
                </div>
            @elseif(!$esRechazada)
                <div style="background-color: #fffbeb; border-left: 4px solid #d97706; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 13.5px; color: #78350f; font-weight: 700; text-transform: uppercase;">Información de Compra:</h4>
                    <p style="margin: 0; font-size: 13.5px; color: #92400e;">Tu solicitud fue aprobada, pero al no haber stock físico disponible en el taller, se ha enviado una solicitud de compra automatizada a la administración central.</p>
                </div>
            @endif

            <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: right;">Fecha Gestión: {{ $fecha }}</p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #f1f5f9;">
            <p style="margin: 0;">Este correo es una notificación automática del sistema Novitec SGN. Por favor, no responda a este mensaje.</p>
            <p style="margin: 6px 0 0 0;">&copy; {{ date('Y') }} Novicompu. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
