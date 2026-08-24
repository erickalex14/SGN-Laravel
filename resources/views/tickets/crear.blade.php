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

            <!-- 1. Tipo de Ticket (Selector visual grande) -->
            <div class="mb-4">
                <label class="form-label fw-bold text-dark mb-2">1. ¿Qué tipo de requerimiento deseas realizar? <span class="text-danger">*</span></label>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="card h-100 p-3 border-2 rounded-3 cursor-pointer tipo-card active" id="card-soporte" style="cursor: pointer; transition: all 0.2s; border-color: #2563eb; background: #eff6ff;">
                            <div class="d-flex align-items-start gap-3">
                                <input type="radio" name="tipo_ticket" value="soporte_tecnico" checked class="form-check-input mt-1" onchange="cambiarTipoTicket('soporte_tecnico')">
                                <div>
                                    <div class="fw-bold text-primary fs-6"><i class="bi bi-tools me-1"></i> Soporte Técnico (Hardware / Tienda)</div>
                                    <div class="text-muted small mt-1">Reparación o diagnóstico de equipos, impresoras térmicas, lectores, hardware en tienda o garantías.</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="card h-100 p-3 border-2 rounded-3 cursor-pointer tipo-card" id="card-sistemas" style="cursor: pointer; transition: all 0.2s; border-color: #e2e8f0; background: #ffffff;">
                            <div class="d-flex align-items-start gap-3">
                                <input type="radio" name="tipo_ticket" value="sistemas" class="form-check-input mt-1" onchange="cambiarTipoTicket('sistemas')">
                                <div>
                                    <div class="fw-bold text-dark fs-6" id="label-sistemas-title"><i class="bi bi-hdd-network me-1"></i> Sistemas / Software / TI (Quito)</div>
                                    <div class="text-muted small mt-1">Acceso a MBA3, correo institucional, redes, CCTV, configuración de sistemas o fallas de software.</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 2. Datos de Origen (Tienda y Empresa) -->
            <div class="bg-light p-3 rounded-3 mb-4 border">
                <div class="fw-bold text-dark small mb-3 text-uppercase">
                    <i class="bi bi-geo-alt-fill text-primary me-1"></i> Datos de Ubicación / Tienda Solicitante
                </div>
                @if ($usuario->sucursal_cliente_id && $usuario->sucursalCliente)
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="p-3 bg-white border rounded-3 d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="bi bi-shop fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Tienda Asignada Automáticamente:</div>
                                    <div class="fw-bold text-dark fs-6">
                                        {{ $usuario->sucursalCliente->codigo }} - {{ $usuario->sucursalCliente->nombre }} ({{ $usuario->sucursalCliente->provincia ?? 'Ecuador' }})
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary ms-2">{{ $usuario->empresa_origen ?? 'NOVICOMPU' }}</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="empresa_origen" value="{{ $usuario->empresa_origen ?? 'NOVICOMPU' }}">
                            <input type="hidden" name="sucursal_cliente_id" value="{{ $usuario->sucursal_cliente_id }}">
                            <input type="hidden" name="tienda_nombre" value="{{ $usuario->sucursalCliente->codigo }} - {{ $usuario->sucursalCliente->nombre }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Teléfono / WhatsApp de Contacto</label>
                            <input type="text" name="contacto_telefono" class="form-control" value="{{ $usuario->telefono ?? '' }}" placeholder="Ej: 0991234567">
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Cadena / Empresa <span class="text-danger">*</span></label>
                            <select name="empresa_origen" class="form-select" required>
                                <option value="NOVICOMPU" {{ ($usuario->empresa_origen ?? '') === 'NOVICOMPU' ? 'selected' : '' }}>Novicompu</option>
                                <option value="ENV" {{ ($usuario->empresa_origen ?? '') === 'ENV' ? 'selected' : '' }}>ENV (Envíos & Valores)</option>
                                <option value="OTRO" {{ ($usuario->empresa_origen ?? '') === 'OTRO' ? 'selected' : '' }}>Otra Área / Franquicia</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Sucursal / Tienda de Origen <span class="text-danger">*</span></label>
                            <select name="sucursal_cliente_id" id="select-tienda" class="form-select" onchange="onTiendaChange(this)" required>
                                <option value="">-- Seleccione su tienda / punto --</option>
                                @foreach($tiendasNovicompu as $t)
                                    <option value="{{ $t->id }}" data-nombre="{{ $t->codigo }} - {{ $t->nombre }}" {{ ($usuario->sucursal_cliente_id ?? 0) == $t->id ? 'selected' : '' }}>
                                        {{ $t->codigo }} - {{ $t->nombre }} ({{ $t->provincia ?? 'Ecuador' }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="tienda_nombre" id="input-tienda-nombre">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Teléfono / WhatsApp de Contacto</label>
                            <input type="text" name="contacto_telefono" class="form-control" value="{{ $usuario->telefono ?? '' }}" placeholder="Ej: 0991234567">
                        </div>
                    </div>
                @endif
            </div>

            <!-- 3. Categoría y Prioridad -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-dark">Categoría del Requerimiento <span class="text-danger">*</span></label>
                    <select name="categoria" id="select-categoria" class="form-select" required>
                        <!-- Se llena dinámicamente con JS -->
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-dark">Prioridad / Urgencia <span class="text-danger">*</span></label>
                    <select name="prioridad" class="form-select" required>
                        <option value="baja">🟢 Baja (Consulta o requerimiento no urgente)</option>
                        <option value="media" selected>🔵 Media (Afectación leve o rutina)</option>
                        <option value="alta">🟠 Alta (Afecta la operación de la tienda)</option>
                        <option value="urgente">🔴 Urgente (Punto de venta paralizado)</option>
                    </select>
                </div>
            </div>

            <!-- 4. Título del Requerimiento -->
            <div class="mb-3">
                <label class="form-label fw-semibold text-dark">Título Resumido <span class="text-danger">*</span></label>
                <input type="text" name="titulo" class="form-control" placeholder="Ej: Impresora de recibos no enciende / Error al facturar en MBA3" required>
            </div>

            <!-- 5. Descripción Detallada -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-dark">Descripción Detallada del Problema o Solicitud <span class="text-danger">*</span></label>
                <textarea name="descripcion" rows="5" class="form-control" placeholder="Indica detalladamente qué sucede, pasos para reproducir el error, código de error si aplica, serie del equipo, etc." required></textarea>
            </div>

            <!-- 6. Adjuntos / Evidencias (Imágenes, Capturas, PDFs) -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-dark">Adjuntar Evidencias o Fotos (Opcional)</label>
                <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light" style="border-style: dashed !important;">
                    <i class="bi bi-cloud-arrow-up fs-2 text-primary d-block mb-2"></i>
                    <p class="small text-muted mb-2">Arrastra aquí fotos del equipo, capturas de pantalla o documentos, o haz clic para seleccionar.</p>
                    <input type="file" name="archivos[]" multiple class="form-control d-inline-block w-auto" accept="image/*,.pdf,.doc,.docx,.txt,.zip">
                    <div class="form-text small">Formatos permitidos: JPG, PNG, WEBP, PDF, DOCX, ZIP (Máx. 15MB por archivo)</div>
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

const categoriasSistemas = [
    'Sistema MBA3 / ERP',
    'Cuentas de Correo Institucional',
    'Redes / Internet / Router Tienda',
    'Cámaras / CCTV Tienda',
    'Problemas de Windows / Office / Licencias',
    'Creación / Modificación de Accesos',
    'Desarrollo / Reportes / Bugs',
    'Otro Requerimiento de Sistemas'
];

function cambiarTipoTicket(tipo) {
    const cardSoporte = document.getElementById('card-soporte');
    const cardSistemas = document.getElementById('card-sistemas');
    const selectCat = document.getElementById('select-categoria');

    if (tipo === 'sistemas') {
        cardSoporte.style.borderColor = '#e2e8f0';
        cardSoporte.style.background = '#ffffff';
        cardSistemas.style.borderColor = '#7c3aed';
        cardSistemas.style.background = '#f3e8ff';
        
        selectCat.innerHTML = categoriasSistemas.map(c => `<option value="${c}">${c}</option>`).join('');
    } else {
        cardSoporte.style.borderColor = '#2563eb';
        cardSoporte.style.background = '#eff6ff';
        cardSistemas.style.borderColor = '#e2e8f0';
        cardSistemas.style.background = '#ffffff';
        
        selectCat.innerHTML = categoriasSoporte.map(c => `<option value="${c}">${c}</option>`).join('');
    }
}

function onTiendaChange(sel) {
    const selectedOption = sel.options[sel.selectedIndex];
    const inputNombre = document.getElementById('input-tienda-nombre');
    if (selectedOption && selectedOption.dataset.nombre) {
        inputNombre.value = selectedOption.dataset.nombre;
    } else {
        inputNombre.value = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    cambiarTipoTicket('soporte_tecnico');

    const form = document.getElementById('form-crear-ticket');
    form.addEventListener('submit', function() {
        const btn = document.getElementById('btn-enviar-ticket');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando ticket...';
    });
});
</script>
@endsection
