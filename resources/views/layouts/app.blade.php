<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGN - @yield('titulo', 'Sistema de Soporte')</title>

    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    @stack('css_adicional') <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="top-bar">
    <div class="logo">
        <img src="{{ asset('images/logosgn1.png') }}" alt="SGN Logo">
    </div>
    <div class="user-info">
        <span>Bienvenido, {{ session('nombre') }}</span>
        <span class="separator">|</span>
        <span>Sucursal: {{ session('sucursal_id') }}</span>
        <span class="separator">|</span>
        <a href="{{ route('auth.logout') }}" class="logout-btn">Cerrar Sesión</a>
    </div>
</div>

<div class="sidebar">
    <ul class="nav-links">
        <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>

        @if(session('es_superadmin') || isset(session('permisos')['ordenes']['ver']))
            <li><a href="{{ route('ordenes.index') }}">Órdenes</a></li>
        @endif

        @if(session('es_superadmin') || isset(session('permisos')['inventario']['ver']))
            <li><a href="{{ route('inventario.index') }}">Inventario</a></li>
        @endif

    </ul>
</div>
<div class="main-content">
    @yield('contenido')
</div>

@stack('js_adicional')
</body>
</html>
