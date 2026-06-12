<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6;">
    @php
        $esAprobada = $estado === 'Aprobada';
        $colorTema = $esAprobada ? '#16a34a' : '#dc2626';
        $gradiente = $esAprobada 
            ? 'linear-gradient(135deg, #16a34a, #15803d)' 
            : 'linear-gradient(135deg, #ef4444, #dc2626)';
    @endphp

    <div style="max-width: 600px; margin: 32px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
        <!-- Header -->
        <div style="background: {{ $gradiente }}; padding: 32px 24px; text-align: center; color: #ffffff;">
            <span style="background-color: rgba(255, 255, 255, 0.15); padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Gestión de Nota de Crédito</span>
            <h1 style="margin: 12px 0 0 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">Estado de tu Solicitud</h1>
            <p style="margin: 6px 0 0 0; font-size: 14px; opacity: 0.85;">Tu solicitud de Nota de Crédito ha sido resuelta</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px 24px;">
            <div style="text-align: center; margin-bottom: 28px;">
                <span style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 6px;">Nro. Solicitud: {{ $nro_solicitud }}</span>
                <span style="font-size: 15px; font-weight: 800; color: #ffffff; background-color: {{ $colorTema }}; padding: 8px 16px; border-radius: 6px; display: inline-block; letter-spacing: 0.5px;">SOLICITUD {{ strtoupper($estado) }}</span>
            </div>

            @if(!$esAprobada && $motivo_rechazo)
                <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 13.5px; color: #991b1b; font-weight: 700; text-transform: uppercase;">Motivo de Rechazo:</h4>
                    <p style="margin: 0; font-size: 13.5px; color: #b91c1c; font-style: italic;">{{ $motivo_rechazo }}</p>
                </div>
            @endif

            @if($esAprobada)
                <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 13.5px; color: #14532d; font-weight: 700; text-transform: uppercase;">Indicaciones:</h4>
                    <p style="margin: 0; font-size: 13.5px; color: #166534;">La Nota de Crédito ha sido aprobada por la administración. Ya se puede imprimir y entregar el comprobante del trámite según el protocolo de la sucursal.</p>
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
