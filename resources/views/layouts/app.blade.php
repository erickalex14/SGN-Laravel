<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGN - @yield('titulo', 'Sistema de Soporte')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('SGN1.png') }}">
    <link rel="shortcut icon" href="{{ asset('SGN1.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('estilos.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy-layout.css') }}">
    @stack('css_adicional')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
@php
    $p = session('permisos', []);
    $sa = session('es_superadmin');
    $can = function (string $mod, string $acc = 'ver') use ($sa, $p): bool {
        return $sa || (!empty($p[$mod][$acc]));
    };

    $hasOrdenes = $sa
        || !empty($p['ordenes']['crear'])
        || !empty($p['ordenes']['editar'])
        || !empty($p['ordenes']['ver'])
        || !empty($p['ordenes_asignadas']['ver']);

    $hasDocTec = $sa
        || !empty($p['informes']['ver'])
        || !empty($p['notas_credito']['ver'])
        || !empty($p['solicitar_repuesto']['ver']);

    $hasDocAdm = $sa
        || !empty($p['reportes']['ver'])
        || !empty($p['notas_credito']['editar'])
        || !empty($p['repuestos_admin']['ver']);

    $hasInventario = $sa
        || !empty($p['productos']['ver'])
        || !empty($p['marcas']['ver'])
        || !empty($p['repuestos']['ver']);

    $hasControl = $hasInventario || $sa || !empty($p['precios']['ver']);
    $hasServicios = $sa || !empty($p['empresas']['ver']) || !empty($p['cas']['ver']);
    $hasAccesoAdmin = $sa || !empty($p['usuarios']['editar']) || !empty($p['grupos']['ver']);
@endphp

<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>
<div class="wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('SGN.png') }}" alt="SGN" class="sidebar-logo-full">
            <button class="btn-collapse" id="btn-collapse" onclick="colapsarSidebar()" title="Colapsar menú">
                <i class="bi bi-chevron-double-left" id="collapse-icon"></i>
            </button>
        </div>

        <a data-tip="Dashboard" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2" style="flex-shrink:0;"></i>
            <span class="nav-label" style="margin-left:10px;">Dashboard</span>
        </a>

        @if ($hasOrdenes)
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Órdenes" onclick="navToggle(this)">
                    <i class="bi bi-clipboard-plus" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Órdenes</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    @if ($can('ordenes', 'crear'))
                        <a data-tip="Crear Orden" href="{{ route('ordenes.crear') }}">
                            <i class="bi bi-plus-circle" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Crear Orden</span>
                        </a>
                    @endif
                    @if ($can('ordenes_asignadas', 'ver'))
                        <a data-tip="Mis Órdenes" href="{{ route('mis_ordenes.index') }}">
                            <i class="bi bi-person-check" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Mis Órdenes</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasDocTec || $hasDocAdm)
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Documentación" onclick="navToggle(this)">
                    <i class="bi bi-file-earmark-text" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Documentación</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    @if ($hasDocTec)
                        <div class="nav-subgroup">
                            <div class="nav-subtoggle" onclick="navSubToggle(this)">
                                <i class="bi bi-file-earmark-medical" style="font-size:11px;"></i>
                                <span>Docs. Técnicos</span>
                                <i class="bi bi-chevron-down nav-sub-arrow"></i>
                            </div>
                            <div class="nav-submenu-2">
                                @if ($can('informes', 'ver'))
                                    <a data-tip="Informes" href="{{ route('informes.index') }}">
                                        <i class="bi bi-file-earmark-medical" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Informes</span>
                                    </a>
                                @endif
                                @if ($can('notas_credito', 'ver'))
                                    <a data-tip="Solicitar NC" href="{{ route('notas_credito.tecnico') }}">
                                        <i class="bi bi-receipt-cutoff" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Solicitar NC</span>
                                    </a>
                                @endif
                                @if ($can('solicitar_repuesto', 'ver'))
                                    <a data-tip="Solicitar Repuesto" href="{{ route('solicitudes_repuestos.tecnico') }}">
                                        <i class="bi bi-wrench-adjustable" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Solicitar Repuesto</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($hasDocAdm)
                        <div class="nav-subgroup">
                            <div class="nav-subtoggle" onclick="navSubToggle(this)">
                                <i class="bi bi-file-earmark-ruled" style="font-size:11px;"></i>
                                <span>Docs. Admin.</span>
                                <i class="bi bi-chevron-down nav-sub-arrow"></i>
                            </div>
                            <div class="nav-submenu-2">
                                @if ($can('reportes', 'ver'))
                                    <a data-tip="Reportes" href="{{ route('reportes.index') }}">
                                        <i class="bi bi-bar-chart-line" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Reportes</span>
                                    </a>
                                @endif
                                @if ($can('notas_credito', 'editar'))
                                    <a data-tip="Notas de Crédito" href="{{ route('notas_credito.admin') }}">
                                        <i class="bi bi-receipt" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Notas de Crédito</span>
                                    </a>
                                @endif
                                @if ($can('repuestos_admin', 'ver'))
                                    <a data-tip="Repuestos" href="{{ route('solicitudes_repuestos.admin') }}">
                                        <i class="bi bi-tools" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Repuestos</span>
                                    </a>
                                    <a data-tip="Listas Compra" href="{{ route('listas_compra.index') }}">
                                        <i class="bi bi-card-checklist" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Listas Compra</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasControl)
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Control" onclick="navToggle(this)">
                    <i class="bi bi-box-seam" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Control</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    @if ($hasInventario)
                        <div class="nav-subgroup">
                            <div class="nav-subtoggle" onclick="navSubToggle(this)">
                                <i class="bi bi-box-seam" style="font-size:11px;"></i>
                                <span>Inventario</span>
                                <i class="bi bi-chevron-down nav-sub-arrow"></i>
                            </div>
                            <div class="nav-submenu-2">
                                @if ($can('productos', 'ver'))
                                    <a data-tip="Productos" href="{{ route('productos.index') }}">
                                        <i class="bi bi-box-seam" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Productos</span>
                                    </a>
                                @endif
                                @if ($can('marcas', 'ver'))
                                    <a data-tip="Marcas" href="{{ route('marcas_tipos.index') }}">
                                        <i class="bi bi-tags" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Marcas</span>
                                    </a>
                                @endif
                                @if ($can('repuestos', 'ver'))
                                    <a data-tip="Repuestos" href="{{ route('repuestos.index') }}">
                                        <i class="bi bi-tools" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Repuestos</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($can('precios', 'ver'))
                        <div class="nav-subgroup">
                            <div class="nav-subtoggle" onclick="navSubToggle(this)">
                                <i class="bi bi-tag" style="font-size:11px;"></i>
                                <span>Precios</span>
                                <i class="bi bi-chevron-down nav-sub-arrow"></i>
                            </div>
                            <div class="nav-submenu-2">
                                <a data-tip="Precios" href="{{ route('precios.index') }}">
                                    <i class="bi bi-tag" style="flex-shrink:0;"></i>
                                    <span class="nav-label" style="margin-left:10px;">Precios</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($can('sucursales', 'ver'))
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Sucursales" onclick="navToggle(this)">
                    <i class="bi bi-geo-alt" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Sucursales</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    <a data-tip="NOVICOM PU" href="{{ route('sucursales_cliente.index') }}">
                        <i class="bi bi-building" style="flex-shrink:0;"></i>
                        <span class="nav-label" style="margin-left:10px;">NOVICOM PU</span>
                    </a>
                </div>
            </div>
        @endif

        @if ($hasServicios)
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Prestación de Servicios" onclick="navToggle(this)">
                    <i class="bi bi-briefcase" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Prestación Servicios</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    @if ($can('empresas', 'ver'))
                        <a data-tip="Empresas" href="{{ route('empresas.index') }}">
                            <i class="bi bi-building" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Empresas</span>
                        </a>
                    @endif
                    @if ($can('cas', 'ver'))
                        <a data-tip="CAS" href="{{ route('cas.index') }}">
                            <i class="bi bi-headset" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">CAS</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasAccesoAdmin)
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Acceso" onclick="navToggle(this)">
                    <i class="bi bi-person-lock" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Acceso</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    <div class="nav-subgroup">
                        <div class="nav-subtoggle" onclick="navSubToggle(this)">
                            <i class="bi bi-shield-lock" style="font-size:11px;"></i>
                            <span>Administrador</span>
                            <i class="bi bi-chevron-down nav-sub-arrow"></i>
                        </div>
                        <div class="nav-submenu-2">
                            @if ($can('usuarios', 'editar'))
                                <a data-tip="Usuarios" href="{{ route('usuarios.modificar') }}">
                                    <i class="bi bi-people" style="flex-shrink:0;"></i>
                                    <span class="nav-label" style="margin-left:10px;">Usuarios</span>
                                </a>
                            @endif
                            @if ($can('usuarios', 'crear'))
                                <a data-tip="Crear Usuario" href="{{ route('usuarios.crear') }}">
                                    <i class="bi bi-person-plus" style="flex-shrink:0;"></i>
                                    <span class="nav-label" style="margin-left:10px;">Crear Usuario</span>
                                </a>
                            @endif
                            @if ($can('grupos', 'ver'))
                                <a data-tip="Grupos de Acceso" href="{{ route('grupos.index') }}">
                                    <i class="bi bi-shield-lock" style="flex-shrink:0;"></i>
                                    <span class="nav-label" style="margin-left:10px;">Grupos de Acceso</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </aside>

    <div class="main-content">
        <div class="topbar">
            <button class="btn-hamburger" id="btn-hamburger" onclick="toggleSidebar()" aria-label="Menú">
                <i class="bi bi-list"></i>
            </button>
            <img src="{{ asset('SGNI.png') }}" alt="SGN" class="topbar-logo">
            <div class="gs-wrap" id="gs-wrap">
                <i class="bi bi-search gs-ico"></i>
                <input class="gs-input" id="gs-input" type="text"
                       placeholder="Buscar por orden, cliente, C.I., serie, factura…"
                       autocomplete="off"
                       oninput="gsBuscar(this.value)"
                       onkeydown="gsKeyDown(event)">
                <i class="bi bi-arrow-repeat spin gs-spinner" id="gs-spinner"></i>
                <div class="gs-dropdown" id="gs-dropdown"></div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
                <span class="topbar-username">
                    <i class="bi bi-person-circle me-1"></i>
                    {{ session('nombre') ?? session('usuario') ?? 'Usuario' }}
                </span>
                <div id="notif-wrapper" style="position:relative;">
                    <button id="notif-btn" onclick="toggleNotifPanel()" title="Notificaciones"
                            style="background:none;border:none;cursor:pointer;position:relative;padding:4px 6px;border-radius:8px;color:#475569;font-size:20px;display:flex;align-items:center;">
                        <i class="bi bi-bell-fill"></i>
                        <span id="notif-badge" style="display:none;position:absolute;top:0;right:0;background:#ef4444;color:white;border-radius:50%;width:18px;height:18px;font-size:10px;font-weight:700;display:none;align-items:center;justify-content:center;line-height:1;">0</span>
                    </button>
                    <div id="notif-panel" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:340px;background:white;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.15);border:1px solid #e2e8f0;z-index:9000;overflow:hidden;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                            <span style="font-weight:700;font-size:14px;color:#0f172a;"><i class="bi bi-bell me-2"></i>Notificaciones</span>
                        </div>
                        <div id="notif-lista" style="max-height:360px;overflow-y:auto;">
                            <div style="padding:20px;text-align:center;color:#94a3b8;font-size:13px;">Sin notificaciones</div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('auth.logout') }}" class="btn btn-sm btn-logout">Cerrar sesión</a>
            </div>
        </div>

        <div class="content-area" id="contenido">
            @yield('contenido')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function navSubToggle(el) {
        var sub2 = el.nextElementSibling;
        var isOpen = el.classList.contains('open');
        el.classList.toggle('open', !isOpen);
        if (sub2) sub2.classList.toggle('open', !isOpen);
    }

    function navToggle(toggleEl) {
        var submenu = toggleEl.nextElementSibling;
        var isOpen = toggleEl.classList.contains('open');

        document.querySelectorAll('.nav-toggle.open').forEach(function(el) {
            if (el !== toggleEl) {
                el.classList.remove('open');
                var sub = el.nextElementSibling;
                if (sub && sub.classList.contains('nav-submenu')) {
                    sub.classList.remove('open');
                }
            }
        });

        toggleEl.classList.toggle('open', !isOpen);
        if (submenu) submenu.classList.toggle('open', !isOpen);
    }

    function toggleSidebar() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        if (!sidebar || !overlay) return;
        var isOpen = sidebar.classList.toggle('open');
        overlay.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    var _sidebarCollapsed = (localStorage.getItem('sgn_sidebar_collapsed') === '1');
    function colapsarSidebar() {
        var sidebar = document.getElementById('sidebar');
        var icon = document.getElementById('collapse-icon');
        _sidebarCollapsed = !_sidebarCollapsed;
        if (sidebar) sidebar.classList.toggle('collapsed', _sidebarCollapsed);
        if (icon) {
            icon.className = _sidebarCollapsed
                ? 'bi bi-chevron-double-right'
                : 'bi bi-chevron-double-left';
        }
        localStorage.setItem('sgn_sidebar_collapsed', _sidebarCollapsed ? '1' : '0');
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (_sidebarCollapsed) {
            var sidebar = document.getElementById('sidebar');
            var icon = document.getElementById('collapse-icon');
            if (sidebar) sidebar.classList.add('collapsed');
            if (icon) icon.className = 'bi bi-chevron-double-right';
        }
    });

    function gsBuscar(q) {
        var dd = document.getElementById('gs-dropdown');
        if (!dd) return;
        if (!q || q.length < 2) {
            dd.classList.remove('open');
            dd.innerHTML = '';
            return;
        }
        dd.innerHTML = '<div class="gs-empty"><i class="bi bi-search me-2"></i>Búsqueda global en migración.</div>';
        dd.classList.add('open');
    }

    function gsKeyDown(e) {
        if (e.key === 'Escape') {
            var dd = document.getElementById('gs-dropdown');
            if (dd) dd.classList.remove('open');
        }
    }

    document.addEventListener('click', function(e) {
        var wrap = document.getElementById('gs-wrap');
        var dd = document.getElementById('gs-dropdown');
        if (wrap && dd && !wrap.contains(e.target)) dd.classList.remove('open');
    });

    var _notifAbierto = false;
    function toggleNotifPanel() {
        var panel = document.getElementById('notif-panel');
        if (!panel) return;
        _notifAbierto = !_notifAbierto;
        panel.style.display = _notifAbierto ? 'block' : 'none';
    }

    document.addEventListener('click', function(e) {
        var wrapper = document.getElementById('notif-wrapper');
        if (!_notifAbierto) return;
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('notif-panel').style.display = 'none';
            _notifAbierto = false;
        }
    });
</script>

@stack('js_adicional')
</body>
</html>
