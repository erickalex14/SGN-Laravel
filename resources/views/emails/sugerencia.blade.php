<html>
<body style="font-family:Arial,sans-serif;color:#1e293b;max-width:600px;margin:0 auto;">
    <div style="background:#1e40af;padding:24px 28px;border-radius:10px 10px 0 0;">
        <h2 style="color:white;margin:0;font-size:20px;">Buzon de Sugerencias - SGN</h2>
    </div>
    <div style="background:#f8fafc;padding:28px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px;">
        <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
            <tr>
                <td style="padding:8px 12px;background:#eff6ff;font-weight:700;font-size:13px;color:#1e40af;width:140px;border-radius:6px 0 0 6px;">Remitente</td>
                <td style="padding:8px 12px;background:#fff;border:1px solid #e2e8f0;border-left:none;font-size:14px;">{{ e($nombre_usuario) }}</td>
            </tr>
            <tr>
                <td style="padding:8px 12px;background:#eff6ff;font-weight:700;font-size:13px;color:#1e40af;border-radius:6px 0 0 6px;">Rol</td>
                <td style="padding:8px 12px;background:#fff;border:1px solid #e2e8f0;border-left:none;font-size:14px;">{{ e(ucfirst($rol_usuario)) }}</td>
            </tr>
            <tr>
                <td style="padding:8px 12px;background:#eff6ff;font-weight:700;font-size:13px;color:#1e40af;border-radius:6px 0 0 6px;">Fecha</td>
                <td style="padding:8px 12px;background:#fff;border:1px solid #e2e8f0;border-left:none;font-size:14px;">{{ e($fecha) }}</td>
            </tr>
            <tr>
                <td style="padding:8px 12px;background:#eff6ff;font-weight:700;font-size:13px;color:#1e40af;border-radius:6px 0 0 6px;">Asunto</td>
                <td style="padding:8px 12px;background:#fff;border:1px solid #e2e8f0;border-left:none;font-size:14px;font-weight:600;">{{ e($asunto) }}</td>
            </tr>
        </table>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;">
            <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1e40af;">Detalle del mensaje:</p>
            <p style="margin:0;font-size:14px;line-height:1.7;white-space:pre-wrap;">{{ e($detalle) }}</p>
        </div>
        <p style="margin:20px 0 0;font-size:12px;color:#94a3b8;text-align:center;">
            Mensaje enviado desde SGN.
        </p>
    </div>
</body>
</html>

