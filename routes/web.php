<?php

use App\Http\Controllers\Directory\CasController;
use App\Http\Controllers\Directory\EmpresaController;
use App\Http\Controllers\Directory\SucursalController;
use App\Http\Controllers\Directory\SucursalClienteController;
use App\Http\Controllers\Identity\MiCuentaController;
use App\Http\Controllers\Identity\GrupoAccesoController;
use App\Http\Controllers\Identity\UsuarioController;
use App\Http\Controllers\Identity\NotificationController;
use App\Http\Controllers\Identity\SuggestionController;
use App\Http\Controllers\Inventory\MarcaController;
use App\Http\Controllers\Inventory\ProductoController;
use App\Http\Controllers\Inventory\RepuestoController;
use App\Http\Controllers\Operations\CatalogoPrecioController;
use App\Http\Controllers\Operations\OrdenController;
use App\Http\Controllers\Operations\MisOrdenesController;
use App\Http\Controllers\Operations\OrdenesAsignadasController;
use App\Http\Controllers\Operations\EdicionOrdenController;
use App\Http\Controllers\Operations\BuscarOrdenController;
use App\Http\Controllers\Operations\InformeController;
use App\Http\Controllers\Operations\PreordenController;
use App\Http\Controllers\Operations\PresupuestoController;
use App\Http\Controllers\Operations\NotaCreditoController;
use App\Http\Controllers\Operations\SolicitudRepuestoController;
use App\Http\Controllers\Operations\ReporteController;
use App\Http\Controllers\Operations\AsistenteIaController;
use App\Http\Controllers\Inventory\ListaCompraController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Identity\AuthController; // Controlador de autenticación heredado
use App\Http\Controllers\DashboardController;


// ═════════════════════════════════════════════════════════════════
// 1. RUTAS PÚBLICAS / INVITADOS (GUEST)
// ═════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    
    // Raíz del sitio: Renderiza el formulario de inicio de sesión legacy
    Route::get('/', function () {
        return view('auth.login');
    })->name('login'); // El nombre 'login' es obligatorio para que los redireccionamientos de Laravel funcionen

    // Endpoint que procesa el POST del formulario (Reemplaza a validar_login.php)
    Route::post('/validar_login', [AuthController::class, 'login'])->name('auth.validar');
});


// Grupo de rutas que requieren sesion activa
Route::middleware('auth')->group(function () {

    // Cerrar sesion
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Asistente de Inteligencia Artificial Conversacional
    Route::post('/operaciones/asistente-ia/preguntar', [AsistenteIaController::class, 'preguntar'])->name('asistente.preguntar');

    // Rutas de acceso rapido usadas por el layout
    Route::middleware(['permiso:ordenes_crear,ver'])->group(function () {
        Route::get('/operaciones/ordenes', function () {
            return redirect()->route('ordenes.crear');
        })->name('ordenes.index');
    });

    Route::middleware(['permiso:inv_productos,ver'])->group(function () {
        Route::get('/inventario', function () {
            return redirect()->route('productos.index');
        })->name('inventario.index');
    });

    // Dashboard (Requiere acceso basico)
    // Vista Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Endpoint asincrono de metricas
    Route::get('/dashboard/metricas', [DashboardController::class, 'obtenerMetricas'])->name('dashboard.metricas');

    // Utilidades globales del layout legacy
    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/marcar', [NotificationController::class, 'markAsRead'])->name('notificaciones.marcar');
    Route::post('/sugerencias/enviar', [SuggestionController::class, 'send'])->name('sugerencias.enviar');

    //-------------------------------------------------------
    //------------------EMPRESAS-----------------------------
    //-------------------------------------------------------

    Route::middleware(['permiso:empresas,ver'])->group(function () {
        Route::get('/empresas', [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('/empresas/listar', [EmpresaController::class, 'listar'])->name('empresas.listar');
    });

    Route::middleware(['permiso:empresas,crear'])->group(function () {
        Route::post('/empresas/guardar', [EmpresaController::class, 'guardar'])->name('empresas.guardar');
    });

    //-------------------------------------------------------
    //----------------------CAS------------------------------
    //-------------------------------------------------------

    // Vista principal protegida por el permiso general del modulo (require_modulo('cas', 'ver'))
    Route::middleware(['permiso:cas,ver'])->group(function () {
        Route::get('/cas', [CasController::class, 'index'])->name('cas.index');
    });

    // Operaciones de guardado protegidas por el permiso estricto (gate_permiso('cas', 'crear'))
    Route::middleware(['permiso:cas,crear'])->group(function () {
        Route::post('/cas/guardar', [CasController::class, 'guardar'])->name('cas.guardar');
    });

    // Endpoint interno para JSON (utilizado en el modulo de Órdenes o reportes)
    // No requiere validación profunda más allá de estar autenticado
    Route::get('/cas/activos', [CasController::class, 'listarActivos'])->name('cas.listar_activos');

    //-------------------------------------------------------
    //--------------SUCURSALES/CLIENTE-SUCURSAL--------------
    //-------------------------------------------------------

    // Vista principal protegida por (require_modulo('sucursales', 'ver'))
    Route::middleware(['permiso:sucursales,ver'])->group(function () {
        Route::get('/sucursales-cliente', [SucursalClienteController::class, 'index'])->name('sucursales_cliente.index');
    });

    // Crear protegido por gate_permiso('sucursales', 'crear')
    Route::middleware(['permiso:sucursales,crear'])->group(function () {
        Route::post('/sucursales-cliente/crear', [SucursalClienteController::class, 'crear'])->name('sucursales_cliente.crear');
    });

    // Modificar (editar/toggle) protegido por gate_permiso('sucursales', 'editar')
    Route::middleware(['permiso:sucursales,editar'])->group(function () {
        Route::post('/sucursales-cliente/actualizar', [SucursalClienteController::class, 'actualizar'])->name('sucursales_cliente.actualizar');
        Route::post('/sucursales-cliente/toggle', [SucursalClienteController::class, 'toggle'])->name('sucursales_cliente.toggle');
    });

    // Sucursales NONITEC (tabla legacy: sucursales)
    Route::middleware(['permiso:sucursales,ver'])->group(function () {
        Route::get('/sucursales-nonitec', [SucursalController::class, 'index'])->name('sucursales.index');
    });

    Route::middleware(['permiso:sucursales,crear'])->group(function () {
        Route::post('/sucursales-nonitec/crear', [SucursalController::class, 'crear'])->name('sucursales.crear');
    });

    Route::middleware(['permiso:sucursales,editar'])->group(function () {
        Route::post('/sucursales-nonitec/actualizar', [SucursalController::class, 'actualizar'])->name('sucursales.actualizar');
    });

    //-------------------------------------------------------
    //------------------PERMISOS GRUPOS----------------------
    //-------------------------------------------------------

    // Vista principal
    Route::middleware(['permiso:grupos_acceso,ver'])->group(function () {
        Route::get('/grupos', [GrupoAccesoController::class, 'index'])->name('grupos.index');
        Route::get('/grupos/permisos/{id}', [GrupoAccesoController::class, 'obtenerPermisos'])->name('grupos.permisos.listar');
    });

    // Guardar (Crear/Editar) Grupo
    Route::middleware(['permiso:grupos_acceso,crear'])->group(function () {
        Route::post('/grupos/guardar', [GrupoAccesoController::class, 'guardar'])->name('grupos.guardar');
    });

    // Eliminar
    Route::middleware(['permiso:grupos_acceso,eliminar'])->group(function () {
        Route::post('/grupos/eliminar', [GrupoAccesoController::class, 'eliminar'])->name('grupos.eliminar');
    });

    // Guardar Permisos (Requiere permiso de editar grupos)
    Route::middleware(['permiso:grupos_acceso,editar'])->group(function () {
        Route::post('/grupos/permisos', [GrupoAccesoController::class, 'guardarPermisos'])->name('grupos.permisos.guardar');
    });


    //-------------------------------------------------------
    //---------------------USUARIOS--------------------------
    //-------------------------------------------------------

    // Crear Usuario
    Route::middleware(['permiso:usuarios,crear'])->group(function () {
        Route::get('/usuarios/crear', [UsuarioController::class, 'index'])->name('usuarios.crear');
        Route::post('/usuarios/guardar', [UsuarioController::class, 'storeOrUpdate'])->name('usuarios.guardar');
    });

    // Modificar Usuario
    Route::middleware(['permiso:usuarios,editar'])->group(function () {
        Route::get('/usuarios/modificar', [UsuarioController::class, 'editList'])->name('usuarios.modificar');
        // El metodo storeOrUpdate gestiona tanto creacion como edicion en base a la existencia del 'id'
        Route::post('/usuarios/actualizar', [UsuarioController::class, 'storeOrUpdate'])->name('usuarios.actualizar');
        Route::post('/usuarios/toggle', [UsuarioController::class, 'toggle'])->name('usuarios.toggle');

        // Endpoints AJAX puros
        Route::get('/usuarios/{id}/permisos', [UsuarioController::class, 'getPermisos']);
        Route::get('/usuarios/{id}/sucursales', [UsuarioController::class, 'getSucursales']);
    });


    //-------------------------------------------------------
    //---------------------=MARCAS---------------------------
    //-------------------------------------------------------

    // Vista conjunta protegida por permiso de modulo
    Route::middleware(['permiso:inv_marcas,ver'])->group(function () {
        Route::get('/inventario/marcas-y-tipos', [MarcaController::class, 'index'])->name('marcas_tipos.index');
    });

    // Rutas operativas de Marcas (Mapeadas al permiso respectivo)
    Route::middleware(['permiso:inv_marcas,crear'])->group(function () {
        Route::post('/inventario/marcas', [MarcaController::class, 'guardarMarca'])->name('marcas.guardar');
    });

    // Rutas operativas de Tipos (Mapeadas al permiso de marcas ya que comparten módulo)
    Route::middleware(['permiso:inv_marcas,crear'])->group(function () {
        Route::post('/inventario/tipos', [MarcaController::class, 'guardarTipo'])->name('tipos_dispositivo.guardar');
    });

    //-------------------------------------------------------
    //--------------------PRODUCTOS--------------------------
    //-------------------------------------------------------

    // Rutas operativas de Productos protegidas por los permisos estipulados
    Route::middleware(['permiso:inv_productos,ver'])->group(function () {
        Route::get('/inventario/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/inventario/productos/listar', [ProductoController::class, 'listar'])->name('productos.listar');
    });

    Route::middleware(['permiso:inv_productos,crear'])->group(function () {
        Route::post('/inventario/productos', [ProductoController::class, 'procesar'])->name('productos.guardar');
    });

    //-------------------------------------------------------
    //--------------------REPUESTOS--------------------------
    //-------------------------------------------------------

    // Vista y listado JSON de repuestos
    Route::middleware(['permiso:inv_repuestos,ver'])->group(function () {
        Route::get('/inventario/repuestos', [RepuestoController::class, 'index'])->name('repuestos.index');
        Route::get('/inventario/repuestos/listar', [RepuestoController::class, 'listar'])->name('repuestos.listar');
    });

    // Guardar / Modificar / Eliminar repuestos
    Route::middleware(['permiso:inv_repuestos,crear'])->group(function () {
        Route::post('/inventario/repuestos', [RepuestoController::class, 'procesar'])->name('repuestos.guardar');
    });

    //-------------------------------------------------------
    //-------------------MODULO PRECIOS----------------------
    //-------------------------------------------------------

    // Vista combinada del modulo
    Route::middleware(['permiso:precios,ver'])->group(function () {
        Route::get('/operaciones/precios-y-servicios', [CatalogoPrecioController::class, 'index'])->name('precios.index');
    });

    // Rutas operativas de Precios y Tipos de Servicio
    Route::middleware(['permiso:precios,crear'])->group(function () {
        Route::post('/operaciones/precios', [CatalogoPrecioController::class, 'procesarPrecio'])->name('precios.guardar');
        Route::post('/operaciones/tipos-servicio', [CatalogoPrecioController::class, 'procesarTipo'])->name('tipos_servicio.guardar');
    });

    //-------------------------------------------------------
    //-----------------MODULO ORDENES------------------------
    //-------------------------------------------------------

    Route::middleware(['permiso:ordenes_crear,ver'])->group(function () {
        Route::get('/operaciones/ordenes/crear', [OrdenController::class, 'create'])->name('ordenes.crear');
        Route::post('/operaciones/ordenes', [OrdenController::class, 'store'])->name('ordenes.store');
        
        // Endpoint AJAX para autocompletar
        Route::get('/operaciones/ordenes/buscar-cliente', [OrdenController::class, 'buscarCliente']);
        Route::get('/operaciones/ordenes/buscar-producto', [OrdenController::class, 'buscarProducto'])->name('ordenes.productos.buscar');
        Route::get('/operaciones/ordenes/repuestos/buscar', [RepuestoController::class, 'buscarParaOrden'])->name('ordenes.repuestos.buscar');
        Route::get('/operaciones/preordenes/verificar', [PreordenController::class, 'verificar'])->name('preordenes.verificar');
    });

    Route::middleware(['permiso:ordenes_mis,ver'])->group(function () {
        Route::get('/operaciones/mis-ordenes', [MisOrdenesController::class, 'index'])->name('mis_ordenes.index');
        Route::get('/operaciones/mis-ordenes/repuestos/buscar', [RepuestoController::class, 'buscarParaOrden'])->name('mis_ordenes.repuestos.buscar');
        Route::post('/operaciones/mis-ordenes/estado', [MisOrdenesController::class, 'cambiarEstado'])->name('mis_ordenes.estado');
        Route::post('/operaciones/mis-ordenes/repuesto/estado', [MisOrdenesController::class, 'cambiarEstadoRepuesto'])->name('mis_ordenes.repuesto_estado');
        Route::post('/operaciones/mis-ordenes/garantia/estado', [MisOrdenesController::class, 'cambiarEstadoGarantia'])->name('mis_ordenes.garantia_estado');
        Route::post('/operaciones/mis-ordenes/repuesto/asignar', [MisOrdenesController::class, 'asignarRepuesto'])->name('mis_ordenes.repuesto_asignar');
        Route::post('/operaciones/mis-ordenes/repuesto/revertir', [MisOrdenesController::class, 'revertirRepuesto'])->name('mis_ordenes.repuesto_revertir');
    });

    Route::middleware(['permiso:ordenes_asignadas,ver'])->group(function () {
        Route::get('/operaciones/ordenes/asignadas', [OrdenesAsignadasController::class, 'index'])->name('ordenes_asignadas.index');
    });

    Route::middleware(['permiso:ordenes_buscar,ver'])->group(function () {
        Route::get('/operaciones/ordenes/buscar', [BuscarOrdenController::class, 'index'])->name('ordenes_buscar.index');
        Route::get('/operaciones/ordenes/buscar/listar', [BuscarOrdenController::class, 'listar'])->name('ordenes_buscar.listar');
    });

    // Modulo de Edicion de Ordenes
    Route::middleware(['permiso:ordenes_editar,ver'])->group(function () {
        Route::get('/operaciones/ordenes/editar/{id}', [EdicionOrdenController::class, 'edit'])->name('ordenes.editar');
        Route::post('/operaciones/ordenes/actualizar', [EdicionOrdenController::class, 'update'])->name('ordenes.update');
    });

    // Buscador Global: accesible a usuarios autenticados, filtrando por alcance interno
    Route::get('/operaciones/ordenes/buscar-global', [EdicionOrdenController::class, 'buscarGlobal'])->name('ordenes.buscar_global');
    // Reimpresion de comprobante OT (legacy: disponible para usuario autenticado)
    Route::get('/operaciones/ordenes/{id}/imprimir', [OrdenController::class, 'imprimir'])->name('ordenes.imprimir');
    Route::get('/operaciones/ordenes-empresa/{id}/imprimir', [OrdenController::class, 'imprimirEmpresa'])->name('ordenes_empresa.imprimir');

    //-------------------------------------------------------
    //-----------------MODULO INFORMES-----------------------
    //-------------------------------------------------------

    Route::middleware(['permiso:informes,ver'])->group(function () {
        // Raíz → redirect inteligente según rol (ver controller)
        Route::get('/operaciones/informes', [InformeController::class, 'index'])->name('informes.index');

        // Admin: buscar todos los informes
        Route::get('/operaciones/informes/buscar', [InformeController::class, 'indexBuscar'])->name('informes.buscar');
        Route::get('/operaciones/informes/buscar/listar', [InformeController::class, 'buscarInformes'])->name('informes.buscar.listar');

        // Compartidos (autenticado + permiso ver)
        Route::get('/operaciones/informes/ver', [InformeController::class, 'verPorOrden'])->name('informes.ver');
        Route::get('/operaciones/informes/{id}/imprimir', [InformeController::class, 'imprimir'])->name('informes.imprimir');
    });

    // Técnicos + Superadmin: crear y ver sus propios informes
    Route::middleware(['permiso:informes,crear'])->group(function () {
        Route::get('/operaciones/informes/crear', [InformeController::class, 'indexCrear'])->name('informes.crear');
        Route::get('/operaciones/informes/crear/buscar-orden', [InformeController::class, 'buscarOrdenesAjax'])->name('informes.crear.buscar');
        Route::get('/operaciones/mis-informes', [InformeController::class, 'misInformes'])->name('informes.mis');
        Route::post('/operaciones/informes', [InformeController::class, 'store'])->name('informes.store');
        Route::post('/operaciones/informes/generar-con-ia', [InformeController::class, 'generarConIa'])->name('informes.generar.ia');
    });

    //-------------------------------------------------------
    //-----------------MODULO PREORDENES---------------------
    //-------------------------------------------------------
    Route::middleware(['permiso:preordenes,ver'])->group(function () {
        Route::get('/operaciones/preordenes', [PreordenController::class, 'index'])->name('preordenes.index');
        Route::post('/operaciones/preordenes/reporte', [PreordenController::class, 'reporte'])->name('preordenes.reporte');
        Route::post('/operaciones/preordenes/ingresar', [PreordenController::class, 'ingresar'])->name('preordenes.ingresar');
    });

    //-------------------------------------------------------
    //-----------------MODULO PRESUPUESTOS-------------------
    //-------------------------------------------------------
    Route::middleware(['permiso:presupuestos,ver'])->group(function () {
        Route::get('/operaciones/presupuestos', [PresupuestoController::class, 'index'])->name('presupuestos.index');
        Route::get('/operaciones/presupuestos/{id}/imprimir', [PresupuestoController::class, 'imprimir'])->name('presupuestos.imprimir');
    });

    //-------------------------------------------------------
    //-----------------MODULO NOTAS CREDITO------------------
    //-------------------------------------------------------

    // Panel Tecnico (Crear Solicitudes)
    Route::middleware(['permiso:solicitar_nc,ver'])->group(function () {
        Route::get('/operaciones/mis-solicitudes-nc', [NotaCreditoController::class, 'indexTecnico'])->name('notas_credito.tecnico');
        Route::post('/operaciones/solicitar-nc', [NotaCreditoController::class, 'solicitar'])->name('notas_credito.solicitar');
    });

    // Panel Admin listado
    Route::middleware(['permiso:notas_credito,ver'])->group(function () {
        Route::get('/operaciones/gestion-nc', [NotaCreditoController::class, 'indexAdmin'])->name('notas_credito.admin');
    });

    // Panel Admin (Aprobar/Rechazar)
    Route::middleware(['permiso:notas_credito,editar'])->group(function () {
        Route::post('/operaciones/gestion-nc/procesar', [NotaCreditoController::class, 'gestionar'])->name('notas_credito.gestionar');
    });
    // Reimpresion de solicitudes NC (acceso controlado dentro del controlador)
    Route::get('/operaciones/notas-credito/{id}/imprimir', [NotaCreditoController::class, 'imprimir'])->name('notas_credito.imprimir');

    //-------------------------------------------------------
    //-------------MODULO SOLICITUDES REPUESTOS--------------
    //-------------------------------------------------------

    // Panel Tecnico
    Route::middleware(['permiso:solicitar_repuesto,ver'])->group(function () {
        Route::get('/operaciones/mis-solicitudes-bodega', [SolicitudRepuestoController::class, 'indexTecnico'])->name('solicitudes_repuestos.tecnico');
        Route::post('/operaciones/solicitar-repuesto', [SolicitudRepuestoController::class, 'solicitar'])->name('solicitudes_repuestos.solicitar');
    });

    // Panel Admin/Bodega
    Route::middleware(['permiso:repuestos_admin,ver'])->group(function () {
        Route::get('/operaciones/bodega-solicitudes', [SolicitudRepuestoController::class, 'indexAdmin'])->name('solicitudes_repuestos.admin');
        Route::post('/operaciones/bodega-solicitudes/procesar', [SolicitudRepuestoController::class, 'gestionar'])->name('solicitudes_repuestos.gestionar');
    });
    // Reimpresion de tickets de repuestos (acceso controlado dentro del controlador)
    Route::get('/operaciones/solicitudes-repuestos/{id}/imprimir', [SolicitudRepuestoController::class, 'imprimir'])->name('solicitudes_repuestos.imprimir');

    //-------------------------------------------------------
    //-------------MODULO REPORTES----------------------------
    //-------------------------------------------------------

    Route::middleware(['permiso:reportes,ver'])->group(function () {
        Route::get('/operaciones/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/operaciones/reportes/filtrar', [ReporteController::class, 'filtrar'])->name('reportes.filtrar');
    });

    //-------------------------------------------------------
    //-------------MODULO LISTAS COMPRA----------------------
    //-------------------------------------------------------

// Requiere permiso del modulo repuestos_admin (la gestion de bodega original)
    Route::middleware(['permiso:repuestos_admin,ver'])->group(function () {
        Route::get('/operaciones/listas-compra', [ListaCompraController::class, 'index'])->name('listas_compra.index');
        Route::get('/operaciones/listas-compra/{id}/imprimir', [ListaCompraController::class, 'imprimir'])->name('listas_compra.imprimir');
    });

    Route::middleware(['permiso:repuestos_admin,crear'])->group(function () {
        Route::post('/operaciones/listas-compra/generar', [ListaCompraController::class, 'store'])->name('listas_compra.store');
    });

    //-------------------------------------------------------
    //--------------------MI CUENTA--------------------------
    //-------------------------------------------------------
    Route::middleware(['permiso:mi_cuenta,ver'])->group(function () {
        Route::get('/mi-cuenta', [MiCuentaController::class, 'index'])->name('mi_cuenta.index');
        Route::get('/configuracion', [MiCuentaController::class, 'index'])->name('configuracion.index');
        Route::post('/mi-cuenta/guardar', [MiCuentaController::class, 'guardar'])->name('mi_cuenta.guardar');
    });

});
