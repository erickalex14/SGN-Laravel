@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1000px;">
    <!-- Migas de pan y encabezado -->
    <div class="mb-4">
        <a href="{{ route('mistickets.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Volver a Mis Tickets
        </a>
        <h2 class="h3 fw-bold text-dark mb-1">Crear Nueva Solicitud de Soporte</h2>
        <p class="text-muted small">Completa el formulario para que el equipo correspondiente en Quito atienda tu requerimiento a la brevedad.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <form action="{{ route('mistickets.store') }}" method="POST" enctype="multipart/form-data" id="form-crear-ticket">
            @csrf

            <!-- 1. Tipo de Ticket (Solo Sistemas Habilitado) -->
            <div class="mb-4">
                <label class="form-label fw-bold text-dark mb-2">1. ¿Qué tipo de requerimiento deseas realizar? <span class="text-danger">*</span></label>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="card h-100 p-3 border-2 rounded-3 cursor-pointer tipo-card active" id="card-sistemas" style="cursor: pointer; transition: all 0.2s; border-color: #7c3aed; background: #f3e8ff;">
                            <div class="d-flex align-items-start gap-3">
                                <input type="radio" name="tipo_ticket" value="sistemas" checked class="form-check-input mt-1" onchange="cambiarTipoTicket('sistemas')">
                                <div>
                                    <div class="fw-bold text-dark fs-6" id="label-sistemas-title"><i class="bi bi-hdd-network me-1 text-primary"></i> Sistemas / Software / TI (Quito)</div>
                                    <div class="text-muted small mt-1">Acceso a MBA3, correo institucional, redes, CCTV, configuración de sistemas o fallas de software.</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card h-100 p-3 border-2 rounded-3 opacity-50 bg-light" id="card-soporte" style="border-color: #e2e8f0; cursor: not-allowed;" title="Módulo de Soporte Hardware deshabilitado temporalmente">
                            <div class="d-flex align-items-start gap-3">
                                <input type="radio" name="tipo_ticket" value="soporte_tecnico" disabled class="form-check-input mt-1">
                                <div>
                                    <div class="fw-bold text-muted fs-6 d-flex align-items-center gap-2">
                                        <i class="bi bi-tools"></i> Soporte Técnico (Hardware)
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">Pausado temporalmente</span>
                                    </div>
                                    <div class="text-muted small mt-1">Módulo de hardware físico temporalmente deshabilitado. Selecciona Sistemas TI para todas las solicitudes.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Datos de Origen (Tienda y Empresa) -->
            <div class="bg-light p-3 rounded-3 mb-4 border">
                <div class="fw-bold text-dark small mb-3 text-uppercase">
                    <i class="bi bi-geo-alt-fill text-primary me-1"></i> Datos de Ubicación / Tienda Solicitante
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-dark">Cadena / Empresa <span class="text-danger">*</span></label>
                        <select name="empresa_origen" class="form-select" required>
                            <option value="NOVICOMPU" {{ ($usuario->empresa_origen ?? '') === 'NOVICOMPU' ? 'selected' : '' }}>Novicompu</option>
                            <option value="ENV" {{ ($usuario->empresa_origen ?? '') === 'ENV' ? 'selected' : '' }}>ENV</option>
                            <option value="OTRO" {{ in_array(($usuario->empresa_origen ?? ''), ['OTRO', 'OTROS', 'Otro', 'Otros']) ? 'selected' : '' }}>Otros</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 position-relative" id="contenedor-buscar-tienda">
                        <label class="form-label small fw-semibold text-dark">Sucursal / Tienda de Origen <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="input-buscar-tienda" class="form-control border-start-0 ps-0" placeholder="Escribe para buscar tienda..." autocomplete="off" required>
                            <button type="button" class="btn btn-outline-secondary border-start-0 d-none" id="btn-limpiar-tienda" title="Limpiar tienda seleccionada" onclick="limpiarTiendaSeleccionada()"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <input type="hidden" name="sucursal_cliente_id" id="input-sucursal-id" required>
                        <input type="hidden" name="tienda_nombre" id="input-tienda-nombre">

                        <!-- Dropdown flotante con lista filtrada dinámicamente -->
                        <div id="lista-tiendas-dropdown" class="shadow-lg border rounded-3 bg-white position-absolute w-100 mt-1 d-none" style="z-index: 1050; max-height: 260px; overflow-y: auto; left: 0; top: 100%;">
                            <!-- Opciones filtradas dinámicamente -->
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-dark">Teléfono / WhatsApp de Contacto</label>
                        <input type="text" name="contacto_telefono" class="form-control" value="{{ $usuario->telefono ?? '' }}" placeholder="Ej: 0991234567">
                    </div>
                </div>
            </div>

            <!-- 3. Categoría y Prioridad -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-dark">Categoría del Requerimiento <span class="text-danger">*</span></label>
                    <select name="categoria" id="select-categoria" class="form-select" onchange="verificarAvisoCasoMba()" required>
                        <!-- Se llena dinámicamente con JS -->
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold text-dark mb-0">Prioridad / Urgencia <span class="text-danger">*</span></label>
                        <a href="javascript:void(0)" class="text-primary text-decoration-none small fw-semibold" onclick="toggleGuiaUrgencia()">
                            <i class="bi bi-question-circle-fill"></i> ¿Cómo clasificar la urgencia?
                        </a>
                    </div>
                    <select name="prioridad" id="select-prioridad" class="form-select" onchange="verificarAvisoPrioridad(this)" required>
                        <option value="baja">Baja (Consulta o requerimiento no urgente)</option>
                        <option value="media" selected>Media (Afectación leve o rutina)</option>
                        <option value="alta">Alta (Afecta la operación pero la tienda sigue vendiendo)</option>
                        <option value="urgente">Urgente (Punto de venta 100% paralizado / No se puede facturar)</option>
                    </select>
                    
                    <!-- Aviso dinámico al seleccionar Urgente -->
                    <div id="aviso-prioridad-urgente" class="alert alert-danger p-2 mt-2 mb-0 d-none rounded-3 small border-danger">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0 mt-1"></i>
                            <div>
                                <b class="text-danger">¡Atención con la prioridad Urgente!</b><br>
                                Utiliza <b>Urgente</b> <u>únicamente</u> si la tienda está totalmente paralizada y ningún computador puede facturar ni atender clientes. Si el problema es parcial o tienes alternativas, por favor selecciona <b>Media</b> o <b>Alta</b> para no saturar las emergencias críticas.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guía Oficial para Clasificar la Urgencia del Ticket -->
            <div class="card border-0 rounded-3 p-3 mb-4 border" id="box-guia-urgencias" style="background-color: #f8fafc;">
                <div class="d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleGuiaUrgencia()" style="cursor: pointer;">
                    <div class="fw-bold text-dark small d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2 text-primary fs-5"></i>
                        <span>Guía Oficial: ¿Cómo clasificar la Urgencia de tu Requerimiento?</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" id="btn-toggle-texto-guia" style="font-size: 11px;">
                        <i class="bi bi-chevron-down" id="icono-toggle-guia"></i> <span id="label-toggle-guia">Ocultar guía</span>
                    </button>
                </div>

                <div id="contenido-guia-urgencias" class="mt-3">
                    <div class="row g-2">
                        <!-- Baja -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-2 rounded-3 bg-white border h-100" style="border-left: 4px solid #22c55e !important;">
                                <div class="fw-bold text-success small mb-1">
                                    Baja (Consulta)
                                </div>
                                <div class="text-muted" style="font-size: 11.5px; line-height: 1.35;">
                                    Dudas de uso de sistemas, capacitaciones, solicitudes de reportes o mejoras que <b>no detienen</b> ninguna venta ni operación de la tienda.
                                </div>
                            </div>
                        </div>

                        <!-- Media -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-2 rounded-3 bg-white border h-100" style="border-left: 4px solid #3b82f6 !important;">
                                <div class="fw-bold text-primary small mb-1">
                                    Media (Rutinaria)
                                </div>
                                <div class="text-muted" style="font-size: 11.5px; line-height: 1.35;">
                                    Fallas menores, lentitud leve, creación/modificación de usuarios rutinarios o mantenimientos preventivos que <b>tienen alternativa</b>.
                                </div>
                            </div>
                        </div>

                        <!-- Alta -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-2 rounded-3 bg-white border h-100" style="border-left: 4px solid #f97316 !important;">
                                <div class="fw-bold text-warning small mb-1" style="color: #ea580c !important;">
                                    Alta (Afectación)
                                </div>
                                <div class="text-muted" style="font-size: 11.5px; line-height: 1.35;">
                                    Falla en un equipo de facturación (de varios), lector, cámara CCTV o correo. La tienda <b>sigue facturando</b> pero con dificultad.
                                </div>
                            </div>
                        </div>

                        <!-- Urgente -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-2 rounded-3 bg-white border h-100" style="border-left: 4px solid #ef4444 !important; background-color: #fff5f5 !important;">
                                <div class="fw-bold text-danger small mb-1">
                                    Urgente (Bloqueo Total)
                                </div>
                                <div class="text-muted" style="font-size: 11.5px; line-height: 1.35;">
                                    <b>Punto de venta 100% paralizado</b>. Ningún computador puede facturar o caída total de red que <b>impide vender</b> en toda la tienda.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO 1: CASOS MBA3 (Reemplaza la plantilla Word) -->
            <div id="box-formulario-caso-mba3" class="card border-primary border-opacity-50 shadow-sm rounded-4 p-4 mb-4 bg-white d-none" style="border: 1.5px solid #93c5fd !important; background-color: #f8fbff !important;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-file-earmark-text-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-primary mb-0">Plantilla Oficial de Registro: Caso MBA3</h6>
                            <span class="text-muted" style="font-size: 11.5px;">Completa los campos directamente aquí para su atención inmediata.</span>
                        </div>
                    </div>
                    <span class="badge bg-primary px-3 py-1 rounded-pill fw-bold" style="font-size: 11px;">Formulario Integrado</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold text-dark">Fecha del Registro</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="mba-fecha" value="{{ date('d/m/Y') }}" readonly>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-dark">Tienda / Sucursal Afectada</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="mba-sucursal" value="{{ $usuario->sucursalCliente ? $usuario->sucursalCliente->codigo . ' - ' . $usuario->sucursalCliente->nombre : 'Tienda seleccionada arriba' }}" readonly>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-dark">Usuario que Reporta</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="mba-usuario" value="{{ $usuario->nombre_tecnico ?: $usuario->usuario }}" readonly>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-dark">Ícono de Acceso <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="mba-icono" placeholder="Ej: URDDP-NNNN / Contig0000 / RDP-01">
                        <div class="form-text" style="font-size: 11px;">Nombre o código del ícono de acceso remoto</div>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-semibold text-dark">Ruta de Acceso / Módulo MBA3 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="mba-ruta" list="lista-rutas-mba" placeholder="Ej: (PDV) \ Facturacion / (INV) \ Transferencias">
                        <datalist id="lista-rutas-mba">
                            <option value="(PDV) \ Facturacion">
                            <option value="(INV) \ Transferencias / Ajustes">
                            <option value="(CXC) \ Cobros / Recibos de Caja">
                            <option value="(CON) \ Asientos Contables">
                            <option value="(COM) \ Compras / Proveedores">
                            <option value="(CLI) \ Clientes / Cuentas">
                            <option value="(BAN) \ Bancos / Conciliación">
                        </datalist>
                        <div class="form-text text-primary" style="font-size: 11px;">
                            <i class="bi bi-info-circle me-1"></i>En la parte inferior izquierda de MBA3 se muestra la ruta del módulo actual.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Documentos / Datos Afectados (Opcional)</label>
                    <input type="text" class="form-control form-control-sm" id="mba-documentos" placeholder="Ej: Factura #001-002-00045, Serie de equipo 1234567, Código producto COD-01...">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Descripción Detallada del Problema / Antecedente <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" id="mba-detalle" rows="3" placeholder="Explica detalladamente qué sucede, qué mensaje de error aparece y los pasos para reproducirlo..."></textarea>
                </div>

                <div>
                    <label class="form-label small fw-semibold text-dark">Acción Requerida (¿Qué se debe resolver?) <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" id="mba-accion" rows="2" placeholder="Indica concretamente qué solución necesitas que Sistemas aplique..."></textarea>
                </div>
            </div>

            <!-- FORMULARIO 2: DATOS ESPECÍFICOS PARA CREACIÓN DE USUARIO, VENDEDOR O ÍCONOS (MBA / MILLENIUM) -->
            <div id="box-formulario-datos-especificos" class="card shadow-sm rounded-4 p-4 mb-4 bg-white d-none" style="border: 1.5px solid #c084fc !important; background-color: #faf5ff !important;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2" style="border-color: #e9d5ff !important;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-white rounded-circle p-2 d-flex align-items-center justify-content-center" id="box-esp-icono-bg" style="width: 38px; height: 38px; background: #7c3aed;">
                            <i class="bi bi-person-lines-fill fs-5" id="box-esp-icono"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" id="box-esp-titulo" style="color: #6b21a8;">Datos Requeridos para Solicitud</h6>
                            <span class="text-muted" style="font-size: 11.5px;" id="box-esp-subtitulo">Completa la ficha técnica requerida para que Sistemas procese la creación.</span>
                        </div>
                    </div>
                    <span class="badge px-3 py-1 rounded-pill fw-bold text-white" id="box-esp-badge" style="background: #7c3aed; font-size: 11px;">Ficha Requerida</span>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Cargo en la sucursal -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-dark">Cargo en la Sucursal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="esp-cargo" list="lista-cargos-sucursal" placeholder="Ej: Vendedor / Asesor Comercial / Administrador / Cajero">
                        <datalist id="lista-cargos-sucursal">
                            <option value="Vendedor / Asesor Comercial">
                            <option value="Cajero / Vendedor">
                            <option value="Administrador de Tienda">
                            <option value="Subadministrador de Tienda">
                            <option value="Bodeguero">
                            <option value="Técnico de Servicio">
                        </datalist>
                    </div>

                    <!-- Nombres Completos -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-dark">Nombres Completos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="esp-nombres" placeholder="Ej: JUAN CARLOS PÉREZ LÓPEZ">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Número de Cédula -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-dark">Número de Cédula <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm font-monospace" id="esp-cedula" maxlength="13" placeholder="Ej: 1712345678">
                    </div>

                    <!-- Tel: Corporativo -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-dark">Tel: Corporativo / Celular <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="esp-telefono" value="{{ $usuario->telefono ?? '' }}" placeholder="Ej: 0991234567">
                    </div>

                    <!-- Correo: corporativo de tienda -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-dark">Correo: Corporativo de Tienda <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-sm" id="esp-correo" value="{{ $usuario->correo_tec ?? '' }}" placeholder="Ej: tienda168@novicompu.com">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Sucursal -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-dark">Sucursal (Ejemplo: 168 ENV RECREO 2) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm bg-light" id="esp-sucursal" value="{{ $usuario->sucursalCliente ? $usuario->sucursalCliente->codigo . ' - ' . $usuario->sucursalCliente->nombre : 'Tienda seleccionada arriba' }}" readonly>
                        <div class="form-text" style="font-size: 11px;">Se sincroniza automáticamente con la tienda elegida arriba.</div>
                    </div>

                    <!-- Campo condicional para Ícono Milenium (Grupo de Milenium) -->
                    <div class="col-12 col-md-6 d-none" id="col-esp-grupo-milenium">
                        <label class="form-label small fw-semibold text-dark">Grupo de Milenium <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="esp-grupo-milenium" list="lista-grupos-milenium" placeholder="Ej: Ventas / Administración / Facturación">
                        <datalist id="lista-grupos-milenium">
                            <option value="Ventas">
                            <option value="Administración">
                            <option value="Caja / Facturación">
                            <option value="Bodega / Inventario">
                        </datalist>
                    </div>

                    <!-- Campo condicional para Ícono MBA (Acceso parecido a:) -->
                    <div class="col-12 col-md-6 d-none" id="col-esp-acceso-parecido">
                        <label class="form-label small fw-semibold text-dark">Acceso parecido a: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="esp-acceso-parecido" placeholder="Ej: Usuario de Juan Pérez / Vendedor de Tienda 168">
                        <div class="form-text" style="font-size: 11px;">Indica qué usuario o perfil de referencia tiene permisos similares.</div>
                    </div>
                </div>

                <!-- Observaciones adicionales / Notas complementarias -->
                <div>
                    <label class="form-label small fw-semibold text-dark">Observaciones / Detalles Adicionales (Opcional)</label>
                    <textarea class="form-control form-control-sm" id="esp-observaciones" rows="2" placeholder="Cualquier indicación adicional sobre horarios, accesos especiales, reemplazo de personal, etc."></textarea>
                </div>
            </div>

            <!-- 4. Título del Requerimiento -->
            <div class="mb-3">
                <label class="form-label fw-semibold text-dark">Título Resumido <span class="text-danger">*</span></label>
                <input type="text" name="titulo" id="input-titulo" class="form-control" placeholder="Ej: Impresora de recibos no enciende / Error al facturar en MBA3" required>
            </div>

            <!-- 5. Descripción Detallada (Visible para categorías estándar) -->
            <div class="mb-4" id="contenedor-descripcion-estandar">
                <label class="form-label fw-semibold text-dark">Descripción Detallada del Problema o Solicitud <span class="text-danger">*</span></label>
                <textarea name="descripcion" id="input-descripcion" rows="5" class="form-control" placeholder="Indica detalladamente qué sucede, pasos para reproducir el error, código de error si aplica, serie del equipo, etc." required></textarea>
            </div>

            <!-- 6. Adjuntos / Evidencias (Imágenes, Capturas, PDFs) -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-dark" id="label-adjuntos">Adjuntar Evidencias, Capturas de Pantalla o Fotos (Opcional)</label>
                <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light" style="border-style: dashed !important;">
                    <i class="bi bi-cloud-arrow-up fs-2 text-primary d-block mb-2"></i>
                    <p class="small text-muted mb-2" id="texto-adjuntos">Arrastra aquí capturas de pantalla del error, fotos del equipo o documentos, o haz clic para seleccionar.</p>
                    <input type="file" name="archivos[]" id="input-archivos" multiple class="form-control d-inline-block w-auto" accept="image/*,.pdf,.doc,.docx,.txt,.zip">
                    <div class="form-text small">Formatos permitidos: PDF, JPG, PNG, WEBP, DOCX, ZIP (Máx. 15MB por archivo)</div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Botones de Acción -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('mistickets.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">Cancelar</a>
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm" id="btn-enviar-ticket">
                    <i class="bi bi-send-fill me-1"></i> Registrar y Enviar Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const categoriasSoporte = [
    'Impresoras térmicas / Matriciales',
    'Lectores de código de barras',
    'CPU / Computador de Facturación',
    'Pantallas / Monitores',
    'Equipos de Clientes / Garantías',
    'Mantenimiento Físico Hardware',
    'Otro Soporte de Hardware'
];

const gruposCategoriasSistemas = [
    {
        grupo: 'MBA3 (Sistema ERP)',
        categorias: [
            'Casos MBA3',
            'Creación de usuario MBA',
            'Colocación / Creación icono MBA',
            'Creación vendedor MBA',
            'Dar de baja usuario MBA',
            'Mantenimiento código',
            'Parametrización permisos usuarios'
        ]
    },
    {
        grupo: 'MILLENIUM (Facturación / Sistema)',
        categorias: [
            'Creación icono Millenium',
            'Parametrización permisos usuarios Millenium'
        ]
    },
    {
        grupo: 'CORREOS ELECTRÓNICOS',
        categorias: [
            'Creación nuevos correos',
            'Actualización datos correos'
        ]
    },
    {
        grupo: 'OTROS REQUERIMIENTOS',
        categorias: [
            'Requerimiento general de sistemas',
            'Otro problema de TI'
        ]
    }
];

const tiendasData = [
    @foreach($tiendasNovicompu as $t)
    {
        id: {{ $t->id }},
        codigo: @json($t->codigo),
        nombre: @json($t->nombre),
        provincia: @json($t->provincia ?? 'Ecuador'),
        label: @json($t->codigo . ' - ' . $t->nombre),
        displayText: @json($t->codigo . ' - ' . $t->nombre . ' (' . ($t->provincia ?? 'Ecuador') . ')'),
        searchKey: @json(mb_strtolower($t->codigo . ' ' . $t->nombre . ' ' . ($t->provincia ?? 'Ecuador'))),
        selected: {{ ($usuario->sucursal_cliente_id ?? 0) == $t->id ? 'true' : 'false' }}
    },
    @endforeach
];

let tiendaSeleccionada = null;

function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderizarTiendasDropdown(filtro = '') {
    const dropdown = document.getElementById('lista-tiendas-dropdown');
    if (!dropdown) return;

    const q = filtro.trim().toLowerCase();
    const filtradas = q === '' 
        ? tiendasData 
        : tiendasData.filter(t => t.searchKey.includes(q));

    if (filtradas.length === 0) {
        dropdown.innerHTML = '<div class="p-3 text-center text-muted small"><i class="bi bi-shop-window d-block fs-5 mb-1 opacity-50"></i>No se encontraron tiendas con "<b>' + escapeHtml(filtro) + '</b>"</div>';
        dropdown.classList.remove('d-none');
        return;
    }

    let html = '<div class="list-group list-group-flush small">';
    filtradas.forEach(t => {
        const isSelected = tiendaSeleccionada && tiendaSeleccionada.id === t.id;
        html += `
            <button type="button" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 ${isSelected ? 'active text-white bg-primary' : ''}" 
                    style="cursor: pointer; text-align: left;" onmousedown="seleccionarTienda(${t.id})">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <span class="badge ${isSelected ? 'bg-light text-dark' : 'bg-primary bg-opacity-10 text-primary'} font-monospace px-2 py-1">${escapeHtml(t.codigo)}</span>
                    <div class="text-truncate fw-semibold">${escapeHtml(t.nombre)}</div>
                </div>
                <span class="${isSelected ? 'text-white-50' : 'text-muted'} small ms-2 text-nowrap">${escapeHtml(t.provincia)}</span>
            </button>
        `;
    });
    html += '</div>';
    dropdown.innerHTML = html;
    dropdown.classList.remove('d-none');
}

function seleccionarTienda(id) {
    const t = tiendasData.find(item => item.id === id);
    if (!t) return;

    tiendaSeleccionada = t;
    const inputBuscar = document.getElementById('input-buscar-tienda');
    const inputId = document.getElementById('input-sucursal-id');
    const inputNombre = document.getElementById('input-tienda-nombre');
    const btnLimpiar = document.getElementById('btn-limpiar-tienda');
    const dropdown = document.getElementById('lista-tiendas-dropdown');

    inputBuscar.value = t.displayText;
    inputId.value = t.id;
    inputNombre.value = t.label;

    btnLimpiar.classList.remove('d-none');
    dropdown.classList.add('d-none');
    inputBuscar.setCustomValidity('');

    actualizarTiendaFormularioMba();
}

function limpiarTiendaSeleccionada() {
    tiendaSeleccionada = null;
    const inputBuscar = document.getElementById('input-buscar-tienda');
    const inputId = document.getElementById('input-sucursal-id');
    const inputNombre = document.getElementById('input-tienda-nombre');
    const btnLimpiar = document.getElementById('btn-limpiar-tienda');
    const dropdown = document.getElementById('lista-tiendas-dropdown');

    inputBuscar.value = '';
    inputId.value = '';
    inputNombre.value = '';
    btnLimpiar.classList.add('d-none');
    dropdown.classList.add('d-none');
    inputBuscar.focus();

    actualizarTiendaFormularioMba();
}

function actualizarTiendaFormularioMba() {
    const inputSucursalMba = document.getElementById('mba-sucursal');
    const inputSucursalEsp = document.getElementById('esp-sucursal');
    const val = tiendaSeleccionada ? tiendaSeleccionada.displayText : ((document.getElementById('input-buscar-tienda')?.value) || 'Tienda seleccionada arriba');
    
    if (inputSucursalMba) inputSucursalMba.value = val;
    if (inputSucursalEsp) inputSucursalEsp.value = val;
}

function verificarAvisoCasoMba() {
    const selectCat = document.getElementById('select-categoria');
    const boxFormMba = document.getElementById('box-formulario-caso-mba3');
    const boxFormEsp = document.getElementById('box-formulario-datos-especificos');
    const contenedorDescEstandar = document.getElementById('contenedor-descripcion-estandar');
    const inputTitulo = document.getElementById('input-titulo');
    const inputDesc = document.getElementById('input-descripcion');

    const catVal = selectCat ? selectCat.value : '';

    // Determinar qué formulario específico aplica
    const esCasoMba = catVal === 'Casos MBA3';
    const esCrearVendedor = catVal === 'Creación vendedor MBA' || catVal.toLowerCase().includes('crear un vendedor') || catVal.toLowerCase().includes('vendedor mba');
    const esCrearUsuarioMba = catVal === 'Creación de usuario MBA' || catVal.toLowerCase().includes('crear un usuario mba') || catVal.toLowerCase().includes('usuario mba');
    const esIconoMilenium = catVal === 'Creación icono Millenium' || catVal.toLowerCase().includes('icono milenium') || catVal.toLowerCase().includes('milenium');
    const esIconoMba = catVal === 'Colocación / Creación icono MBA' || catVal.toLowerCase().includes('icono mba');

    // Resetear visibilidad de campos condicionales
    document.getElementById('col-esp-grupo-milenium')?.classList.add('d-none');
    document.getElementById('col-esp-acceso-parecido')?.classList.add('d-none');

    if (esCasoMba) {
        if (boxFormMba) boxFormMba.classList.remove('d-none');
        if (boxFormEsp) boxFormEsp.classList.add('d-none');
        if (contenedorDescEstandar) contenedorDescEstandar.classList.add('d-none');
        if (inputDesc) inputDesc.removeAttribute('required');

        if (inputTitulo && (!inputTitulo.value || inputTitulo.value.startsWith('Datos para') || inputTitulo.value.startsWith('Creación') || inputTitulo.placeholder.includes('Impresora'))) {
            inputTitulo.placeholder = 'Ej: Caso MBA3 - Error al facturar / Falla en módulo de inventario';
        }
        actualizarTiendaFormularioMba();
    } else if (esCrearVendedor || esCrearUsuarioMba || esIconoMilenium || esIconoMba) {
        if (boxFormMba) boxFormMba.classList.add('d-none');
        if (boxFormEsp) boxFormEsp.classList.remove('d-none');
        if (contenedorDescEstandar) contenedorDescEstandar.classList.add('d-none');
        if (inputDesc) inputDesc.removeAttribute('required');

        const boxTitulo = document.getElementById('box-esp-titulo');
        const boxSub = document.getElementById('box-esp-subtitulo');
        const boxBadge = document.getElementById('box-esp-badge');

        if (esCrearVendedor) {
            if (boxTitulo) boxTitulo.innerText = 'Datos Requeridos para Crear un Vendedor en MBA';
            if (boxSub) boxSub.innerText = 'Completa los datos de la sucursal y el asesor comercial para su alta en MBA.';
            if (boxBadge) boxBadge.innerText = 'Creación Vendedor';
            if (inputTitulo && !inputTitulo.value) inputTitulo.placeholder = 'Ej: Creación de Vendedor MBA - ' + (tiendaSeleccionada ? tiendaSeleccionada.label : 'Tienda');
        } else if (esCrearUsuarioMba) {
            if (boxTitulo) boxTitulo.innerText = 'Datos Requeridos para Crear un Usuario MBA';
            if (boxSub) boxSub.innerText = 'Completa los datos para la habilitación de credenciales en el sistema MBA3.';
            if (boxBadge) boxBadge.innerText = 'Creación Usuario MBA';
            if (inputTitulo && !inputTitulo.value) inputTitulo.placeholder = 'Ej: Creación de Usuario MBA - ' + (tiendaSeleccionada ? tiendaSeleccionada.label : 'Tienda');
        } else if (esIconoMilenium) {
            if (boxTitulo) boxTitulo.innerText = 'Datos Requeridos para Crear un Ícono Milenium';
            if (boxSub) boxSub.innerText = 'Indica el grupo de permisos y los datos del colaborador en la sucursal.';
            if (boxBadge) boxBadge.innerText = 'Ícono Milenium';
            document.getElementById('col-esp-grupo-milenium')?.classList.remove('d-none');
            if (inputTitulo && !inputTitulo.value) inputTitulo.placeholder = 'Ej: Creación de Ícono Milenium - ' + (tiendaSeleccionada ? tiendaSeleccionada.label : 'Tienda');
        } else if (esIconoMba) {
            if (boxTitulo) boxTitulo.innerText = 'Datos Requeridos para Crear un Ícono MBA';
            if (boxSub) boxSub.innerText = 'Indica el perfil de referencia con acceso similar para configurar el ícono.';
            if (boxBadge) boxBadge.innerText = 'Ícono MBA';
            document.getElementById('col-esp-acceso-parecido')?.classList.remove('d-none');
            if (inputTitulo && !inputTitulo.value) inputTitulo.placeholder = 'Ej: Creación de Ícono MBA - ' + (tiendaSeleccionada ? tiendaSeleccionada.label : 'Tienda');
        }

        actualizarTiendaFormularioMba();
    } else {
        if (boxFormMba) boxFormMba.classList.add('d-none');
        if (boxFormEsp) boxFormEsp.classList.add('d-none');
        if (contenedorDescEstandar) contenedorDescEstandar.classList.remove('d-none');
        if (inputDesc) inputDesc.setAttribute('required', 'required');

        if (inputTitulo && !inputTitulo.value) {
            inputTitulo.placeholder = 'Ej: Impresora no enciende / Problema en tienda';
        }
    }
}

function verificarAvisoPrioridad(sel) {
    const avisoUrgente = document.getElementById('aviso-prioridad-urgente');
    if (!avisoUrgente) return;
    if (sel.value === 'urgente') {
        avisoUrgente.classList.remove('d-none');
    } else {
        avisoUrgente.classList.add('d-none');
    }
}

function toggleGuiaUrgencia() {
    const contenido = document.getElementById('contenido-guia-urgencias');
    const icono = document.getElementById('icono-toggle-guia');
    const label = document.getElementById('label-toggle-guia');
    if (!contenido) return;

    if (contenido.classList.contains('d-none')) {
        contenido.classList.remove('d-none');
        if (icono) icono.className = 'bi bi-chevron-up';
        if (label) label.innerText = 'Ocultar guía';
    } else {
        contenido.classList.add('d-none');
        if (icono) icono.className = 'bi bi-chevron-down';
        if (label) label.innerText = 'Ver guía';
    }
}

function cambiarTipoTicket(tipo) {
    const cardSoporte = document.getElementById('card-soporte');
    const cardSistemas = document.getElementById('card-sistemas');
    const selectCat = document.getElementById('select-categoria');

    if (tipo === 'sistemas') {
        cardSoporte.style.borderColor = '#e2e8f0';
        cardSoporte.style.background = '#ffffff';
        cardSistemas.style.borderColor = '#7c3aed';
        cardSistemas.style.background = '#f3e8ff';
        
        let optHtml = '';
        gruposCategoriasSistemas.forEach(g => {
            optHtml += `<optgroup label="${escapeHtml(g.grupo)}">`;
            g.categorias.forEach(c => {
                optHtml += `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`;
            });
            optHtml += `</optgroup>`;
        });
        selectCat.innerHTML = optHtml;
    } else {
        cardSoporte.style.borderColor = '#2563eb';
        cardSoporte.style.background = '#eff6ff';
        cardSistemas.style.borderColor = '#e2e8f0';
        cardSistemas.style.background = '#ffffff';
        
        selectCat.innerHTML = categoriasSoporte.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
    }
    verificarAvisoCasoMba();
}

document.addEventListener('DOMContentLoaded', () => {
    cambiarTipoTicket('sistemas');

    const inputBuscar = document.getElementById('input-buscar-tienda');
    const dropdown = document.getElementById('lista-tiendas-dropdown');
    const contenedor = document.getElementById('contenedor-buscar-tienda');

    if (inputBuscar) {
        // Inicializar si tiene selección previa
        const preselected = tiendasData.find(t => t.selected);
        if (preselected) {
            seleccionarTienda(preselected.id);
        }

        inputBuscar.addEventListener('focus', () => {
            const val = inputBuscar.value.trim();
            renderizarTiendasDropdown(val === (tiendaSeleccionada?.displayText || '') ? '' : val);
        });

        inputBuscar.addEventListener('input', () => {
            const val = inputBuscar.value.trim();
            if (tiendaSeleccionada && val !== tiendaSeleccionada.displayText) {
                tiendaSeleccionada = null;
                document.getElementById('input-sucursal-id').value = '';
                document.getElementById('input-tienda-nombre').value = '';
                document.getElementById('btn-limpiar-tienda').classList.add('d-none');
            }
            renderizarTiendasDropdown(val);
        });

        document.addEventListener('click', (e) => {
            if (contenedor && !contenedor.contains(e.target)) {
                dropdown.classList.add('d-none');
            }
        });
    }

    const form = document.getElementById('form-crear-ticket');
    form.addEventListener('submit', function(e) {
        const inputId = document.getElementById('input-sucursal-id');
        const inputBuscar = document.getElementById('input-buscar-tienda');
        if (!inputId || !inputId.value) {
            e.preventDefault();
            inputBuscar.setCustomValidity('Por favor selecciona una sucursal / tienda válida de la lista.');
            inputBuscar.reportValidity();
            inputBuscar.focus();
            renderizarTiendasDropdown('');
            return false;
        }

        const selectCat = document.getElementById('select-categoria');
        const inputDesc = document.getElementById('input-descripcion');
        const catVal = selectCat ? selectCat.value : '';

        const esCasoMba = catVal === 'Casos MBA3';
        const esCrearVendedor = catVal === 'Creación vendedor MBA' || catVal.toLowerCase().includes('crear un vendedor') || catVal.toLowerCase().includes('vendedor mba');
        const esCrearUsuarioMba = catVal === 'Creación de usuario MBA' || catVal.toLowerCase().includes('crear un usuario mba') || catVal.toLowerCase().includes('usuario mba');
        const esIconoMilenium = catVal === 'Creación icono Millenium' || catVal.toLowerCase().includes('icono milenium') || catVal.toLowerCase().includes('milenium');
        const esIconoMba = catVal === 'Colocación / Creación icono MBA' || catVal.toLowerCase().includes('icono mba');

        if (esCasoMba) {
            const icono = (document.getElementById('mba-icono')?.value || '').trim();
            const ruta = (document.getElementById('mba-ruta')?.value || '').trim();
            const detalle = (document.getElementById('mba-detalle')?.value || '').trim();
            const accion = (document.getElementById('mba-accion')?.value || '').trim();
            const fecha = document.getElementById('mba-fecha')?.value || '';
            const sucursal = document.getElementById('mba-sucursal')?.value || '';
            const usuario = document.getElementById('mba-usuario')?.value || '';
            const documentos = (document.getElementById('mba-documentos')?.value || '').trim();

            if (!icono) {
                e.preventDefault();
                alert('Por favor ingresa el Ícono de Acceso para el Caso MBA3 (ej: URDDP-NNNN).');
                document.getElementById('mba-icono')?.focus();
                return false;
            }
            if (!ruta) {
                e.preventDefault();
                alert('Por favor ingresa la Ruta de Acceso / Módulo de MBA3.');
                document.getElementById('mba-ruta')?.focus();
                return false;
            }
            if (!detalle) {
                e.preventDefault();
                alert('Por favor ingresa la descripción detallada del problema en MBA3.');
                document.getElementById('mba-detalle')?.focus();
                return false;
            }
            if (!accion) {
                e.preventDefault();
                alert('Por favor indica qué acción requiere que resuelva Sistemas.');
                document.getElementById('mba-accion')?.focus();
                return false;
            }

            inputDesc.value = 
`========================================
REGISTRO OFICIAL DE CASO MBA3
========================================
• Fecha de Registro: ${fecha}
• Tienda / Sucursal: ${sucursal}
• Usuario que Reporta: ${usuario}
• Ícono de Acceso: ${icono}
• Ruta de Acceso / Módulo: ${ruta}
• Documentos / Datos Afectados: ${documentos || 'No especificado'}
----------------------------------------
DESCRIPCIÓN DETALLADA DEL PROBLEMA:
${detalle}
----------------------------------------
ACCIÓN REQUERIDA (¿Qué se debe resolver?):
${accion}
========================================`;
        } else if (esCrearVendedor || esCrearUsuarioMba || esIconoMilenium || esIconoMba) {
            const cargo = (document.getElementById('esp-cargo')?.value || '').trim();
            const nombres = (document.getElementById('esp-nombres')?.value || '').trim().toUpperCase();
            const cedula = (document.getElementById('esp-cedula')?.value || '').trim();
            const telefono = (document.getElementById('esp-telefono')?.value || '').trim();
            const correo = (document.getElementById('esp-correo')?.value || '').trim();
            const sucursal = (document.getElementById('esp-sucursal')?.value || '').trim();
            const observaciones = (document.getElementById('esp-observaciones')?.value || '').trim();

            if (!cargo) {
                e.preventDefault();
                alert('Por favor ingresa el Cargo en la sucursal.');
                document.getElementById('esp-cargo')?.focus();
                return false;
            }
            if (!nombres) {
                e.preventDefault();
                alert('Por favor ingresa los Nombres Completos.');
                document.getElementById('esp-nombres')?.focus();
                return false;
            }
            if (!cedula) {
                e.preventDefault();
                alert('Por favor ingresa el Número de Cédula.');
                document.getElementById('esp-cedula')?.focus();
                return false;
            }
            if (!telefono) {
                e.preventDefault();
                alert('Por favor ingresa el Tel: Corporativo / Celular.');
                document.getElementById('esp-telefono')?.focus();
                return false;
            }
            if (!correo) {
                e.preventDefault();
                alert('Por favor ingresa el Correo Corporativo de la tienda.');
                document.getElementById('esp-correo')?.focus();
                return false;
            }

            let tituloCaso = 'DATOS REQUERIDOS';
            let camposExtras = '';

            if (esCrearVendedor) {
                tituloCaso = 'DATOS PARA CREAR UN VENDEDOR';
            } else if (esCrearUsuarioMba) {
                tituloCaso = 'DATOS PARA CREAR UN USUARIO MBA';
            } else if (esIconoMilenium) {
                tituloCaso = 'DATOS PARA CREAR UN ICONO MILENIUM';
                const grupoMil = (document.getElementById('esp-grupo-milenium')?.value || '').trim();
                if (!grupoMil) {
                    e.preventDefault();
                    alert('Por favor ingresa el Grupo de Milenium.');
                    document.getElementById('esp-grupo-milenium')?.focus();
                    return false;
                }
                camposExtras = `• Grupo de Milenium: ${grupoMil}\n`;
            } else if (esIconoMba) {
                tituloCaso = 'DATOS PARA CREAR UN ICONO MBA';
                const accesoParecido = (document.getElementById('esp-acceso-parecido')?.value || '').trim();
                if (!accesoParecido) {
                    e.preventDefault();
                    alert('Por favor indica en el campo "Acceso parecido a:" el usuario de referencia.');
                    document.getElementById('esp-acceso-parecido')?.focus();
                    return false;
                }
                camposExtras = `• Acceso parecido a: ${accesoParecido}\n`;
            }

            inputDesc.value = 
`========================================
${tituloCaso}
========================================
• Cargo en la sucursal: ${cargo}
• Nombres Completos: ${nombres}
• Número de Cédula: ${cedula}
• Tel: Corporativo: ${telefono}
• Correo: corporativo de tienda: ${correo}
• Sucursal: ${sucursal}
${camposExtras}${observaciones ? `----------------------------------------\nOBSERVACIONES ADICIONALES:\n${observaciones}\n` : ''}========================================`;
        }

        const btn = document.getElementById('btn-enviar-ticket');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando ticket...';
    });
});
</script>
@endsection
