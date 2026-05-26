<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGN - Inicio de Sesión</title>
    <link rel="stylesheet" href="{{ asset('estilos.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* Estilos específicos y limpios de la pantalla de login original */
        body.login-body {
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            border: 1.5px solid #e2e8f0;
            width: 100%;
            max-width: 400px;
            padding: 32px;
            box-sizing: border-box;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .login-logo img {
            max-width: 160px;
            height: auto;
        }
        .login-card h2 {
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            text-align: center;
        }
        .login-card p {
            margin: 0 0 24px 0;
            font-size: 13.5px;
            color: #64748b;
            text-align: center;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }
        .form-group label {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 16px;
        }
        .form-group input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #0f172a;
            background-color: #f8fafc;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            border-color: #2563eb;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-login:hover {
            opacity: 0.95;
        }
        .login-msg {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .login-msg.err {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-logo">
            <img src="{{ asset('logosgn1.png') }}" alt="SGN Logo">
        </div>

        <h2>Sistema de Gestión SGN</h2>
        <p>Introduce tus credenciales para acceder al panel</p>

        @if(request()->query('error'))
            <div class="login-msg err">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>
                    @if(request()->query('error') === 'inactivo')
                        El usuario se encuentra inactivo. Contacte al administrador.
                    @else
                        Usuario o contraseña incorrectos. Intente de nuevo.
                    @endif
                </span>
            </div>
        @endif

        <form action="{{ route('auth.validar') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <div class="input-wrapper">
                    <i class="bi bi-person"></i>
                    <input type="text" id="usuario" name="usuario" placeholder="Ej: mchavarrea" required autocomplete="username" autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="clave">Contraseña</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="clave" name="clave" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Ingresar al Sistema
            </button>
        </form>
    </div>

</body>
</html>