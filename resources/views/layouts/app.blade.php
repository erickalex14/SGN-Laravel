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
    <style>
        /* Regla global de paginamiento antigravity */
        [data-page-hidden="true"] {
            display: none !important;
        }

        /* Estilos premium para sgn-pager */
        .sgn-pager-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,.02);
            flex-wrap: wrap;
            gap: 12px;
        }
        .sgn-pager-info {
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }
        .sgn-pager-buttons {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .sgn-pager-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            outline: none;
        }
        .sgn-pager-btn:hover:not(:disabled) {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
            transform: translateY(-1px);
        }
        .sgn-pager-btn.activo {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37,99,235,.24);
        }
        .sgn-pager-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
        }
        .sgn-pager-size {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }
        .sgn-pager-select {
            padding: 5px 8px;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            font-size: 12.5px;
            background: #ffffff;
            cursor: pointer;
            font-weight: 700;
            color: #475569;
        }
        .sgn-pager-select:focus {
            border-color: #2563eb;
        }
    </style>
    @stack('css_adicional')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
@php
    $p = session('permisos', []);
    $sa = session('es_superadmin');
    $permAlias = [
        'grupos' => 'grupos_acceso',
        'productos' => 'inv_productos',
        'marcas' => 'inv_marcas',
        'repuestos' => 'inv_repuestos',
        'notas_credito_tecnico' => 'solicitar_nc',
        'sucursales_cliente' => 'sucursales_novicompu',
    ];
    $can = function (string $mod, string $acc = 'ver') use ($sa, $p, $permAlias): bool {
        if ($sa) {
            return true;
        }

        $modulos = [$mod];
        if (isset($permAlias[$mod])) {
            $modulos[] = $permAlias[$mod];
        }

        foreach ($modulos as $modulo) {
            if (!empty($p[$modulo][$acc])) {
                return true;
            }
        }

        return false;
    };

    $hasOrdenes = $can('ordenes_crear', 'ver')
        || $can('ordenes_editar', 'ver')
        || $can('ordenes_buscar', 'ver')
        || $can('ordenes_mis', 'ver')
        || $can('ordenes_asignadas', 'ver')
        || $can('preordenes', 'ver');

    $hasDocTec = $can('informes', 'ver')
        || $can('informes', 'crear')
        || $can('presupuestos', 'ver')
        || $can('notas_credito_tecnico', 'ver')
        || $can('solicitar_repuesto', 'ver')
        || auth()->check(); // Técnicos siempre tienen acceso a las rutas de informes

    // Cualquier usuario autenticado puede crear informes y ver los suyos propios
    // (el acceso real ya está filtrado por tecnico_id en el controller)
    $puedeInformesTecnico = auth()->check();

    $hasDocAdm = $can('reportes', 'ver')
        || $can('notas_credito', 'ver')
        || $can('repuestos_admin', 'ver');

    $hasInventario = $can('productos', 'ver')
        || $can('marcas', 'ver')
        || $can('repuestos', 'ver');

    $hasControl = $hasInventario || $can('precios', 'ver');
    $hasServicios = $can('empresas', 'ver') || $can('cas', 'ver');
    $hasAccesoAdmin = $can('usuarios', 'ver') || $can('grupos', 'ver');
    $hasAcceso = $can('mi_cuenta', 'ver') || $hasAccesoAdmin;
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
                    @if ($can('ordenes_crear', 'ver'))
                        <a data-tip="Crear Orden" href="{{ route('ordenes.crear') }}">
                            <i class="bi bi-plus-circle" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Crear Orden</span>
                        </a>
                    @endif
                    @if ($can('ordenes_mis', 'ver'))
                        <a data-tip="Mis Órdenes" href="{{ route('mis_ordenes.index') }}">
                            <i class="bi bi-person-check" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Mis Órdenes</span>
                        </a>
                    @endif
                    @if ($can('ordenes_asignadas', 'ver'))
                        <a data-tip="Órdenes Asignadas" href="{{ route('ordenes_asignadas.index') }}">
                            <i class="bi bi-list-check" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Órdenes Asignadas</span>
                        </a>
                    @endif
                    @if ($can('ordenes_buscar', 'ver'))
                        <a data-tip="Buscar Órdenes" href="{{ route('ordenes_buscar.index') }}">
                            <i class="bi bi-search" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Buscar Órdenes</span>
                        </a>
                    @endif
                    @if ($can('preordenes', 'ver'))
                        <a data-tip="Preórdenes" href="{{ route('preordenes.index') }}">
                            <i class="bi bi-file-earmark-plus" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Preórdenes</span>
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
                                {{-- Crear Informe y Mis Informes: todos los usuarios autenticados --}}
                                @if ($puedeInformesTecnico || $can('informes', 'crear'))
                                    <a data-tip="Crear Informe" href="{{ route('informes.crear') }}">
                                        <i class="bi bi-file-earmark-plus" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Crear Informe</span>
                                    </a>
                                    <a data-tip="Mis Informes" href="{{ route('informes.mis') }}">
                                        <i class="bi bi-journal-text" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Mis Informes</span>
                                    </a>
                                    <a data-tip="Reportes" href="{{ route('reportes.tecnico') }}">
                                        <i class="bi bi-bar-chart-line" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Reportes</span>
                                    </a>
                                @endif
                                {{-- Buscar Informes: Admin (tienen informes,ver) + Superadmin --}}
                                @if ($sa || $can('informes', 'ver'))
                                    <a data-tip="Buscar Informes" href="{{ route('informes.buscar') }}">
                                        <i class="bi bi-file-earmark-medical" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Buscar Informes</span>
                                    </a>
                                @endif
                                @if ($can('presupuestos', 'ver'))
                                    <a data-tip="Proformas" href="{{ route('presupuestos.index') }}">
                                        <i class="bi bi-calculator" style="flex-shrink:0;"></i>
                                        <span class="nav-label" style="margin-left:10px;">Proformas</span>
                                    </a>
                                @endif
                                @if ($can('notas_credito_tecnico', 'ver'))
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
                                @if ($can('notas_credito', 'ver'))
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

        @if ($can('sucursales', 'ver') || $can('sucursales_cliente', 'ver'))
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Sucursales" onclick="navToggle(this)">
                    <i class="bi bi-geo-alt" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Sucursales</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    @if ($can('sucursales', 'ver'))
                    <a data-tip="NONITEC" href="{{ route('sucursales.index') }}">
                        <i class="bi bi-shop" style="flex-shrink:0;"></i>
                        <span class="nav-label" style="margin-left:10px;">Novitecnologia</span>
                    </a>
                    @endif
                    @if ($can('sucursales_cliente', 'ver'))
                        <a data-tip="NOVICOM PU" href="{{ route('sucursales_cliente.index') }}">
                            <i class="bi bi-building" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Novicompu</span>
                        </a>
                    @endif
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

        @if ($hasAcceso)
            <div class="nav-group">
                <a class="nav-toggle" data-tip="Acceso" onclick="navToggle(this)">
                    <i class="bi bi-person-lock" style="flex-shrink:0;"></i>
                    <span class="nav-label" style="margin-left:10px;">Acceso</span>
                    <i class="bi bi-chevron-down nav-arrow ms-auto"></i>
                </a>
                <div class="nav-submenu">
                    @if ($can('mi_cuenta', 'ver'))
                        <a data-tip="Mi Cuenta" href="{{ route('mi_cuenta.index') }}">
                            <i class="bi bi-person-circle" style="flex-shrink:0;"></i>
                            <span class="nav-label" style="margin-left:10px;">Mi Cuenta</span>
                        </a>
                    @endif
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
                            @if ($can('grupos_acceso', 'ver'))
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
                <button onclick="abrirBuzon()" title="Buzon de sugerencias"
                        style="background:none;border:none;cursor:pointer;padding:4px 6px;border-radius:8px;color:#475569;font-size:20px;display:flex;align-items:center;">
                    <i class="bi bi-mailbox2"></i>
                </button>
                <div id="notif-wrapper" style="position:relative;">
                    <button id="notif-btn" onclick="toggleNotifPanel()" title="Notificaciones"
                            style="background:none;border:none;cursor:pointer;position:relative;padding:4px 6px;border-radius:8px;color:#475569;font-size:20px;display:flex;align-items:center;">
                        <i class="bi bi-bell-fill"></i>
                        <span id="notif-badge" style="display:none;position:absolute;top:0;right:0;background:#ef4444;color:white;border-radius:50%;width:18px;height:18px;font-size:10px;font-weight:700;display:none;align-items:center;justify-content:center;line-height:1;">0</span>
                    </button>
                    <div id="notif-panel" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:340px;background:white;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.15);border:1px solid #e2e8f0;z-index:9000;overflow:hidden;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                            <span style="font-weight:700;font-size:14px;color:#0f172a;"><i class="bi bi-bell me-2"></i>Notificaciones</span>
                            <button onclick="marcarTodasLeidas()" style="background:none;border:none;cursor:pointer;font-size:11px;color:#2563eb;font-weight:600;padding:0;">Marcar todas leidas</button>
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

<div id="modal-buzon" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;">
        <button onclick="cerrarBuzon()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">x</button>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="background:#eff6ff;border-radius:10px;width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-mailbox2" style="font-size:22px;color:#2563eb;"></i>
            </div>
            <div>
                <h5 style="margin:0;font-size:17px;font-weight:700;color:#0f172a;">Buzon de Sugerencias</h5>
                <p style="margin:0;font-size:12px;color:#94a3b8;">Tu mensaje llegara al administrador</p>
            </div>
        </div>
        <div style="margin-bottom:14px;">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:5px;">Asunto <span style="color:#ef4444;">*</span></label>
            <input id="buz-asunto" type="text" maxlength="100" placeholder="De que trata tu mensaje?"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;outline:none;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:20px;">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:5px;">Detalle <span style="color:#ef4444;">*</span></label>
            <textarea id="buz-detalle" rows="5" maxlength="1000" placeholder="Describe tu sugerencia..."
                      style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;outline:none;resize:vertical;box-sizing:border-box;font-family:inherit;"></textarea>
        </div>
        <div id="buz-msg" style="display:none;margin-bottom:12px;padding:10px 14px;border-radius:8px;font-size:13px;"></div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="cerrarBuzon()" style="background:#f1f5f9;color:#475569;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">Cancelar</button>
            <button id="btn-enviar-buz" onclick="enviarSugerencia()" style="background:#2563eb;color:white;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-send"></i> Enviar
            </button>
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

    var _csrf = '{{ csrf_token() }}';
    var _urlBuscarGlobal = '{{ route("ordenes.buscar_global") }}';
    var _urlNotificaciones = '{{ route("notificaciones.index") }}';
    var _urlMarcarNotif = '{{ route("notificaciones.marcar") }}';
    var _urlSugerencias = '{{ route("sugerencias.enviar") }}';

    var _gsTimer = null;
    var _gsItems = [];
    var _gsSel = -1;

    var _notifAbierto = false;
    var _notifTimer = null;

    function escHtml(txt) {
        return (txt || '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function gsBadge(estado) {
        var e = (estado || '').toLowerCase();
        if (e === 'pendiente') return 'gs-badge gs-badge-pend';
        if (e === 'en proceso') return 'gs-badge gs-badge-proc';
        if (e === 'finalizada') return 'gs-badge gs-badge-list';
        if (e === 'entregada') return 'gs-badge gs-badge-ent';
        return 'gs-badge gs-badge-def';
    }

    function gsBuscar(q) {
        clearTimeout(_gsTimer);
        var dd = document.getElementById('gs-dropdown');
        var spin = document.getElementById('gs-spinner');
        if (!dd) return;

        if (!q || q.length < 2) {
            dd.classList.remove('open');
            dd.innerHTML = '';
            _gsItems = [];
            _gsSel = -1;
            if (spin) spin.style.display = 'none';
            return;
        }

        _gsTimer = setTimeout(function() {
            if (spin) spin.style.display = 'block';
            fetch(_urlBuscarGlobal + '?q=' + encodeURIComponent(q), { cache: 'no-store' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (spin) spin.style.display = 'none';
                    if (!data.ok) {
                        dd.innerHTML = '<div class="gs-empty">' + escHtml(data.error || 'No se pudo buscar.') + '</div>';
                        dd.classList.add('open');
                        _gsItems = [];
                        return;
                    }

                    var ordenes = data.ordenes || [];
                    _gsItems = ordenes;
                    _gsSel = -1;

                    if (!ordenes.length) {
                        dd.innerHTML = '<div class="gs-empty"><i class="bi bi-search me-2"></i>Sin resultados</div>';
                        dd.classList.add('open');
                        return;
                    }

                    var html = '<div class="gs-section"><div class="gs-section-lbl">Ordenes</div>';
                    ordenes.forEach(function(o, idx) {
                        var id = o.orden_id || o.id || 0;
                        html += '<div class="gs-item" data-idx="' + idx + '" onclick="gsAbrir(' + id + ')">';
                        html += '<span class="gs-nro">' + escHtml(o.nro_orden || '-') + '</span>';
                        html += '<span class="gs-cliente">' + escHtml(o.cliente || '-') + '</span>';
                        html += '<span class="' + gsBadge(o.estado_orden) + '">' + escHtml(o.estado_orden || '-') + '</span>';
                        html += '</div>';
                    });
                    html += '</div>';
                    dd.innerHTML = html;
                    dd.classList.add('open');
                })
                .catch(function() {
                    if (spin) spin.style.display = 'none';
                    dd.innerHTML = '<div class="gs-empty">Error de conexion.</div>';
                    dd.classList.add('open');
                    _gsItems = [];
                });
        }, 220);
    }

    function gsAbrir(id) {
        if (!id) return;
        window.location.href = '/operaciones/ordenes/editar/' + id;
    }

    function gsKeyDown(e) {
        var dd = document.getElementById('gs-dropdown');
        if (!dd || !dd.classList.contains('open')) return;

        if (e.key === 'Escape') {
            dd.classList.remove('open');
            return;
        }
        if (!_gsItems.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            _gsSel = (_gsSel + 1) % _gsItems.length;
            gsPintarSeleccion();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            _gsSel = (_gsSel - 1 + _gsItems.length) % _gsItems.length;
            gsPintarSeleccion();
        } else if (e.key === 'Enter' && _gsSel >= 0) {
            e.preventDefault();
            var sel = _gsItems[_gsSel];
            gsAbrir(sel.orden_id || sel.id || 0);
        }
    }

    function gsPintarSeleccion() {
        document.querySelectorAll('#gs-dropdown .gs-item').forEach(function(el) {
            el.style.background = '';
        });
        if (_gsSel >= 0) {
            var item = document.querySelector('#gs-dropdown .gs-item[data-idx="' + _gsSel + '"]');
            if (item) item.style.background = '#f1f5f9';
        }
    }

    function toggleNotifPanel() {
        var panel = document.getElementById('notif-panel');
        if (!panel) return;
        _notifAbierto = !_notifAbierto;
        panel.style.display = _notifAbierto ? 'block' : 'none';
        if (_notifAbierto) cargarNotificaciones();
    }

    function cargarNotificaciones() {
        fetch(_urlNotificaciones, { cache: 'no-store' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok) return;
                actualizarBadge(data.no_leidas || 0);
                renderNotificaciones(data.notificaciones || []);
            })
            .catch(function() {});
    }

    function actualizarBadge(n) {
        var badge = document.getElementById('notif-badge');
        if (!badge) return;
        if (n > 0) {
            badge.textContent = n > 9 ? '9+' : n;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderNotificaciones(notifs) {
        var lista = document.getElementById('notif-lista');
        if (!lista) return;
        if (!notifs.length) {
            lista.innerHTML = '<div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;"><i class="bi bi-bell-slash" style="font-size:28px;display:block;margin-bottom:8px;"></i>Sin notificaciones</div>';
            return;
        }

        var iconos = {
            nc_solicitud: '<i class="bi bi-bell-fill" style="color:#2563eb;"></i>',
            nc_aprobada: '<i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>',
            nc_rechazada: '<i class="bi bi-x-circle-fill" style="color:#dc2626;"></i>'
        };

        var html = '';
        notifs.forEach(function(n) {
            var noLeida = (n.leida == 0 || n.leida === '0' || n.leida === false);
            var bg = noLeida ? '#eff6ff' : '#ffffff';
            var fw = noLeida ? '600' : '400';
            var icono = iconos[n.tipo] || '<i class="bi bi-bell"></i>';
            var dot = noLeida ? '<span style="width:8px;height:8px;border-radius:50%;background:#2563eb;display:inline-block;margin-top:5px;"></span>' : '';

            html += '<div onclick="marcarNotifLeida(' + n.id + ')" style="display:flex;gap:10px;align-items:flex-start;padding:12px 16px;border-bottom:1px solid #f1f5f9;cursor:pointer;background:' + bg + ';">';
            html += '<span style="font-size:18px;flex-shrink:0;">' + icono + '</span>';
            html += '<div style="flex:1;min-width:0;">';
            html += '<p style="margin:0;font-size:13px;font-weight:' + fw + ';color:#0f172a;line-height:1.4;">' + escHtml(n.mensaje) + '</p>';
            html += '<p style="margin:3px 0 0;font-size:11px;color:#94a3b8;">' + escHtml(n.fecha || '') + '</p>';
            html += '</div>' + dot + '</div>';
        });
        lista.innerHTML = html;
    }

    function marcarNotifLeida(id) {
        var body = new URLSearchParams({ _token: _csrf, accion: 'una', id: id });
        fetch(_urlMarcarNotif, { method: 'POST', body: body })
            .then(function() { cargarNotificaciones(); })
            .catch(function() {});
    }

    function marcarTodasLeidas() {
        var body = new URLSearchParams({ _token: _csrf, accion: 'todas' });
        fetch(_urlMarcarNotif, { method: 'POST', body: body })
            .then(function() { cargarNotificaciones(); })
            .catch(function() {});
    }

    function abrirBuzon() {
        document.getElementById('buz-asunto').value = '';
        document.getElementById('buz-detalle').value = '';
        var msg = document.getElementById('buz-msg');
        msg.style.display = 'none';
        document.getElementById('modal-buzon').style.display = 'flex';
    }

    function cerrarBuzon() {
        document.getElementById('modal-buzon').style.display = 'none';
    }

    async function enviarSugerencia() {
        var asunto = document.getElementById('buz-asunto').value.trim();
        var detalle = document.getElementById('buz-detalle').value.trim();
        var msg = document.getElementById('buz-msg');
        var btn = document.getElementById('btn-enviar-buz');

        if (!asunto || !detalle) {
            msg.style.display = 'block';
            msg.style.background = '#fef2f2';
            msg.style.color = '#991b1b';
            msg.style.border = '1px solid #fca5a5';
            msg.textContent = 'Por favor completa asunto y detalle.';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';

        try {
            var fd = new FormData();
            fd.append('_token', _csrf);
            fd.append('asunto', asunto);
            fd.append('detalle', detalle);

            var r = await fetch(_urlSugerencias, { method: 'POST', body: fd });
            var data = await r.json();

            msg.style.display = 'block';
            if (data.ok) {
                msg.style.background = '#f0fdf4';
                msg.style.color = '#166534';
                msg.style.border = '1px solid #86efac';
                msg.textContent = 'Mensaje enviado correctamente.';
                setTimeout(cerrarBuzon, 1800);
            } else {
                msg.style.background = '#fef2f2';
                msg.style.color = '#991b1b';
                msg.style.border = '1px solid #fca5a5';
                msg.textContent = 'Error al enviar: ' + (data.error || 'intenta de nuevo.');
            }
        } catch (e) {
            msg.style.display = 'block';
            msg.style.background = '#fef2f2';
            msg.style.color = '#991b1b';
            msg.style.border = '1px solid #fca5a5';
            msg.textContent = 'Error de conexion. Intenta de nuevo.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i> Enviar';
        }
    }

    document.addEventListener('click', function(e) {
        var wrap = document.getElementById('gs-wrap');
        var dd = document.getElementById('gs-dropdown');
        if (wrap && dd && !wrap.contains(e.target)) dd.classList.remove('open');

        var wrapper = document.getElementById('notif-wrapper');
        if (_notifAbierto && wrapper && !wrapper.contains(e.target)) {
            document.getElementById('notif-panel').style.display = 'none';
            _notifAbierto = false;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        if (_sidebarCollapsed) {
            var sidebar = document.getElementById('sidebar');
            var icon = document.getElementById('collapse-icon');
            if (sidebar) sidebar.classList.add('collapsed');
            if (icon) icon.className = 'bi bi-chevron-double-right';
        }

        var mbuz = document.getElementById('modal-buzon');
        if (mbuz) {
            mbuz.addEventListener('click', function(e) {
                if (e.target === this) cerrarBuzon();
            });
        }

        cargarNotificaciones();
        _notifTimer = setInterval(function() {
            if (!_notifAbierto) cargarNotificaciones();
        }, 30000);
    });

    // Reusable client-side pagination component for SGN
    class SgnPager {
        constructor(options) {
            this.containerSelector = options.containerSelector;
            this.itemSelector = options.itemSelector;
            this.pagerContainerSelector = options.pagerContainerSelector;
            this.pageSize = parseInt(options.pageSize) || 15;
            this.currentPage = 1;
            this.onPageChange = options.onPageChange;
            this.observer = null;
            this.init();
        }

        init() {
            this.setupPagerMarkup();
            this.bindEvents();
            this.render();
            this.setupObserver();
        }

        setupPagerMarkup() {
            const container = document.querySelector(this.pagerContainerSelector);
            if (!container) return;

            container.innerHTML = `
                <div class="sgn-pager-wrap">
                    <div class="sgn-pager-info">Mostrando <span class="sgn-p-start">0</span> a <span class="sgn-p-end">0</span> de <span class="sgn-p-total">0</span> registros</div>
                    <div class="sgn-pager-buttons"></div>
                    <div class="sgn-pager-size">
                        <span>Por página:</span>
                        <select class="sgn-pager-select">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            `;

            const select = container.querySelector('.sgn-pager-select');
            if (select) {
                select.value = this.pageSize;
            }
        }

        bindEvents() {
            const container = document.querySelector(this.pagerContainerSelector);
            if (!container) return;

            const select = container.querySelector('.sgn-pager-select');
            if (select) {
                select.addEventListener('change', (e) => {
                    this.pageSize = parseInt(e.target.value) || 15;
                    this.currentPage = 1;
                    this.render();
                });
            }

            const buttonsContainer = container.querySelector('.sgn-pager-buttons');
            if (buttonsContainer) {
                buttonsContainer.addEventListener('click', (e) => {
                    const btn = e.target.closest('.sgn-pager-btn');
                    if (!btn || btn.disabled) return;

                    const page = btn.getAttribute('data-page');
                    if (page === 'prev') {
                        this.currentPage--;
                    } else if (page === 'next') {
                        this.currentPage++;
                    } else {
                        this.currentPage = parseInt(page) || 1;
                    }
                    this.render();
                });
            }
        }

        setupObserver() {
            const container = document.querySelector(this.containerSelector);
            if (!container) return;

            this.observer = new MutationObserver(() => {
                this.observer.disconnect();
                const newVisibleCount = this.getVisibleItemsCount();
                if (this.lastVisibleCount !== newVisibleCount) {
                    this.currentPage = 1;
                    this.render();
                } else {
                    this.reObserve();
                }
            });

            this.reObserve();
        }

        reObserve() {
            const container = document.querySelector(this.containerSelector);
            if (!container || !this.observer) return;

            this.observer.observe(container, {
                attributes: true,
                attributeFilter: ['style', 'class'],
                subtree: true
            });
        }

        getVisibleItemsCount() {
            return this.getFilteredItems().length;
        }

        getFilteredItems() {
            const container = document.querySelector(this.containerSelector);
            if (!container) return [];

            const allItems = Array.from(container.querySelectorAll(this.itemSelector));
            return allItems.filter(item => {
                return item.style.display !== 'none' && !item.classList.contains('sgn-hidden-by-search');
            });
        }

        render() {
            const filteredItems = this.getFilteredItems();
            this.lastVisibleCount = filteredItems.length;

            const totalItems = filteredItems.length;
            const totalPages = Math.max(1, Math.ceil(totalItems / this.pageSize));

            if (this.currentPage > totalPages) {
                this.currentPage = totalPages;
            }
            if (this.currentPage < 1) {
                this.currentPage = 1;
            }

            const start = (this.currentPage - 1) * this.pageSize;
            const end = Math.min(start + this.pageSize, totalItems);

            const container = document.querySelector(this.containerSelector);
            if (container) {
                const allItems = container.querySelectorAll(this.itemSelector);
                allItems.forEach(item => {
                    item.setAttribute('data-page-hidden', 'true');
                });
            }

            for (let i = start; i < end; i++) {
                if (filteredItems[i]) {
                    filteredItems[i].setAttribute('data-page-hidden', 'false');
                }
            }

            const pager = document.querySelector(this.pagerContainerSelector);
            if (pager) {
                const wrap = pager.querySelector('.sgn-pager-wrap');
                if (wrap) {
                    if (totalItems === 0) {
                        wrap.querySelector('.sgn-p-start').textContent = '0';
                        wrap.querySelector('.sgn-p-end').textContent = '0';
                        wrap.querySelector('.sgn-p-total').textContent = '0';
                    } else {
                        wrap.querySelector('.sgn-p-start').textContent = start + 1;
                        wrap.querySelector('.sgn-p-end').textContent = end;
                        wrap.querySelector('.sgn-p-total').textContent = totalItems;
                    }

                    const btnContainer = wrap.querySelector('.sgn-pager-buttons');
                    if (btnContainer) {
                        let btnHtml = `
                            <button class="sgn-pager-btn" data-page="prev" ${this.currentPage === 1 ? 'disabled' : ''}>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        `;

                        const maxButtons = 5;
                        let startPage = Math.max(1, this.currentPage - Math.floor(maxButtons / 2));
                        let endPage = Math.min(totalPages, startPage + maxButtons - 1);

                        if (endPage - startPage + 1 < maxButtons) {
                            startPage = Math.max(1, endPage - maxButtons + 1);
                        }

                        if (startPage > 1) {
                            btnHtml += `<button class="sgn-pager-btn" data-page="1">1</button>`;
                            if (startPage > 2) {
                                btnHtml += `<span style="color:#94a3b8;padding:0 4px;">...</span>`;
                            }
                        }

                        for (let p = startPage; p <= endPage; p++) {
                            btnHtml += `
                                <button class="sgn-pager-btn ${p === this.currentPage ? 'activo' : ''}" data-page="${p}">
                                    ${p}
                                </button>
                            `;
                        }

                        if (endPage < totalPages) {
                            if (endPage < totalPages - 1) {
                                btnHtml += `<span style="color:#94a3b8;padding:0 4px;">...</span>`;
                            }
                            btnHtml += `<button class="sgn-pager-btn" data-page="${totalPages}">${totalPages}</button>`;
                        }

                        btnHtml += `
                            <button class="sgn-pager-btn" data-page="next" ${this.currentPage === totalPages ? 'disabled' : ''}>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        `;

                        btnContainer.innerHTML = btnHtml;
                    }
                }
            }

            if (this.onPageChange) {
                this.onPageChange(this.currentPage);
            }
            this.reObserve();
        }

        destroy() {
            if (this.observer) {
                this.observer.disconnect();
            }
        }
    }
</script>

@stack('js_adicional')
</body>
</html>
