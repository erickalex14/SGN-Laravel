<html>
<body style="font-family:Arial,sans-serif;color:#1e293b;max-width:600px;margin:0 auto;padding:20px;">
    <div style="background:#dc2626;padding:24px 28px;border-radius:10px 10px 0 0;text-align:center;">
        <h2 style="color:white;margin:0;font-size:20px;">Recordatorio: Cierre de Caja</h2>
        <p style="color:#fca5a5;margin:4px 0 0;font-size:13px;">Sucursal: {{ $sucursal }}</p>
    </div>
    <div style="background:#f8fafc;padding:28px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
            <p style="margin:0 0 15px;font-size:15px;line-height:1.7;color:#334155;">
                Estimado administrador,
            </p>
            <p style="margin:0 0 15px;font-size:15px;line-height:1.7;color:#334155;">
                Le recordamos que hoy es el último día del mes de <strong>{{ $mes }} de {{ $anio }}</strong>. Por favor, proceda a realizar la revisión y cierre manual de la <strong>Caja Chica</strong> y <strong>Caja Grande</strong> correspondiente a su sucursal.
            </p>
            <p style="margin:0;font-size:15px;line-height:1.7;color:#334155;">
                Recuerde que el cierre congela los saldos finales, permitiendo transferir los remanentes como saldos iniciales del mes siguiente.
            </p>
        </div>
        <p style="margin:20px 0 0;font-size:12px;color:#94a3b8;text-align:center;line-height:1.4;">
            Este es un correo automático enviado por el sistema SGN. Por favor, no responda directamente a este mensaje.<br>
            <b>Novitec - SGN</b>
        </p>
    </div>
</body>
</html>
