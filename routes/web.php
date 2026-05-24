<?php

use App\Http\Controllers\Directory\CasController;
use App\Http\Controllers\Directory\EmpresaController;
use App\Http\Controllers\Directory\SucursalClienteController;
use App\Http\Controllers\Identity\GrupoAccesoController;
use App\Http\Controllers\Identity\UsuarioController;
use App\Http\Controllers\Inventory\MarcaController;
use App\Http\Controllers\Inventory\ProductoController;
use App\Http\Controllers\Inventory\RepuestoController;
use App\Http\Controllers\Operations\CatalogoPrecioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Identity\AuthController;
use App\Http\Controllers\DashboardController;
// ... otros controladores

// Grupo de rutas que requieren sesion activa
Route::middleware('auth')->group(function () {

    // Dashboard (Requiere acceso basico)
    // Vista Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Endpoint asincrono de metricas
    Route::get('/dashboard/metricas', [DashboardController::class, 'obtenerMetricas'])->name('dashboard.metricas');

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

    //-------------------------------------------------------
    //------------------PERMISOS GRUPOS----------------------
    //-------------------------------------------------------

    // Vista principal
    Route::middleware(['permiso:grupos,ver'])->group(function () {
        Route::get('/grupos', [GrupoAccesoController::class, 'index'])->name('grupos.index');
        Route::get('/grupos/permisos/{id}', [GrupoAccesoController::class, 'obtenerPermisos'])->name('grupos.permisos.listar');
    });

    // Guardar (Crear/Editar) Grupo
    Route::middleware(['permiso:grupos,crear'])->group(function () {
        Route::post('/grupos/guardar', [GrupoAccesoController::class, 'guardar'])->name('grupos.guardar');
    });

    // Eliminar
    Route::middleware(['permiso:grupos,eliminar'])->group(function () {
        Route::post('/grupos/eliminar', [GrupoAccesoController::class, 'eliminar'])->name('grupos.eliminar');
    });

    // Guardar Permisos (Requiere permiso de editar grupos)
    Route::middleware(['permiso:grupos,editar'])->group(function () {
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
    Route::middleware(['permiso:marcas,ver'])->group(function () {
        Route::get('/inventario/marcas-y-tipos', [MarcaController::class, 'index'])->name('marcas_tipos.index');
    });

    // Rutas operativas de Marcas (Mapeadas al permiso respectivo)
    Route::middleware(['permiso:marcas,crear'])->group(function () {
        Route::post('/inventario/marcas', [MarcaController::class, 'guardarMarca'])->name('marcas.guardar');
    });

    // Rutas operativas de Tipos (Mapeadas al permiso de marcas ya que comparten módulo)
    Route::middleware(['permiso:marcas,crear'])->group(function () {
        Route::post('/inventario/tipos', [MarcaController::class, 'guardarTipo'])->name('tipos_dispositivo.guardar');
    });

    //-------------------------------------------------------
    //--------------------PRODUCTOS--------------------------
    //-------------------------------------------------------

    // Rutas operativas de Productos protegidas por los permisos estipulados
    Route::middleware(['permiso:productos,ver'])->group(function () {
        Route::get('/inventario/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/inventario/productos/listar', [ProductoController::class, 'listar'])->name('productos.listar');
    });

    Route::middleware(['permiso:productos,crear'])->group(function () {
        Route::post('/inventario/productos', [ProductoController::class, 'procesar'])->name('productos.guardar');
    });

    //-------------------------------------------------------
    //--------------------REPUESTOS--------------------------
    //-------------------------------------------------------

    // Vista y listado JSON de repuestos
    Route::middleware(['permiso:repuestos,ver'])->group(function () {
        Route::get('/inventario/repuestos', [RepuestoController::class, 'index'])->name('repuestos.index');
        Route::get('/inventario/repuestos/listar', [RepuestoController::class, 'listar'])->name('repuestos.listar');
    });

    // Guardar / Modificar / Eliminar repuestos
    Route::middleware(['permiso:repuestos,crear'])->group(function () {
        Route::post('/inventario/repuestos', [RepuestoController::class, 'procesar'])->name('repuestos.guardar');
    });

    //-------------------------------------------------------
    //-----------------MODULO PRECIOS------------------------
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

    Route::middleware(['permiso:ordenes,crear'])->group(function () {
        Route::get('/operaciones/ordenes/crear', [OrdenController::class, 'create'])->name('ordenes.crear');
        Route::post('/operaciones/ordenes', [OrdenController::class, 'store'])->name('ordenes.store');
        
        // Endpoint AJAX para autocompletar
        Route::get('/operaciones/ordenes/buscar-cliente', [OrdenController::class, 'buscarCliente']);
    });

    Route::middleware(['permiso:ordenes_asignadas,ver'])->group(function () {
        Route::get('/operaciones/mis-ordenes', [MisOrdenesController::class, 'index'])->name('mis_ordenes.index');
        Route::post('/operaciones/mis-ordenes/estado', [MisOrdenesController::class, 'cambiarEstado'])->name('mis_ordenes.estado');
    });

    // Modulo de Edicion de Ordenes
    Route::middleware(['permiso:ordenes,editar'])->group(function () {
        Route::get('/operaciones/ordenes/editar/{id}', [EdicionOrdenController::class, 'edit'])->name('ordenes.editar');
        Route::post('/operaciones/ordenes/actualizar', [EdicionOrdenController::class, 'update'])->name('ordenes.update');
    });

    // Buscador Global (accesible para quienes pueden ver ordenes)
    Route::middleware(['permiso:ordenes,ver'])->group(function () {
        Route::get('/operaciones/ordenes/buscar-global', [EdicionOrdenController::class, 'buscarGlobal'])->name('ordenes.buscar_global');
    });

    //-------------------------------------------------------
    //-----------------MODULO INFORMES-----------------------
    //-------------------------------------------------------

    Route::middleware(['permiso:informes,ver'])->group(function () {
        Route::get('/operaciones/informes', [InformeController::class, 'index'])->name('informes.index');
    });

    Route::middleware(['permiso:informes,crear'])->group(function () {
        Route::post('/operaciones/informes', [InformeController::class, 'store'])->name('informes.store');
    });

    //-------------------------------------------------------
    //-----------------MODULO NOTAS CREDITO------------------
    //-------------------------------------------------------

    // Panel Tecnico (Crear Solicitudes)
    Route::middleware(['permiso:notas_credito,ver'])->group(function () {
        Route::get('/operaciones/mis-solicitudes-nc', [NotaCreditoController::class, 'indexTecnico'])->name('notas_credito.tecnico');
        Route::post('/operaciones/solicitar-nc', [NotaCreditoController::class, 'solicitar'])->name('notas_credito.solicitar');
    });

    // Panel Admin (Aprobar/Rechazar) -> Requiere permiso de edicion en modulo notas_credito
    Route::middleware(['permiso:notas_credito,editar'])->group(function () {
        Route::get('/operaciones/gestion-nc', [NotaCreditoController::class, 'indexAdmin'])->name('notas_credito.admin');
        Route::post('/operaciones/gestion-nc/procesar', [NotaCreditoController::class, 'gestionar'])->name('notas_credito.gestionar');
    });

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

});
