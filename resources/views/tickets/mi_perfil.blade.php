@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 900px;">
    <!-- Encabezado -->
    <div class="mb-4">
        <a href="{{ route('mistickets.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Volver a Mis Solicitudes
        </a>
        <h2 class="h3 fw-bold text-dark mb-1"><i class="bi bi-person-gear text-primary me-2"></i>Mis Datos Técnicos & Conexión</h2>
        <p class="text-muted small">Mantén actualizada tu información de soporte (AnyDesk, MBA3 y Correo) para que el equipo de soporte técnico y sistemas pueda asistirte rápidamente en cada ticket.</p>
    </div>

    <!-- Ficha Informativa de Tienda -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-shop fs-4"></i>
            </div>
            <div>
                <div class="text-muted small text-uppercase fw-semibold">Punto de Venta / Tienda Asignada:</div>
                <div class="h5 fw-bold text-dark mb-0">
                    {{ $usuario->sucursalCliente ? ($usuario->sucursalCliente->codigo . ' - ' . $usuario->sucursalCliente->nombre) : 'Tienda no asignada' }}
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-2">{{ $usuario->empresa_origen ?? 'NOVICOMPU' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Datos de Soporte -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <form action="{{ route('mistickets.guardar_perfil') }}" method="POST">
            @csrf

            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-laptop me-2 text-primary"></i>Información de Soporte & Acceso</h5>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">Usuario / Cédula</label>
                    <input type="text" class="form-control bg-light" value="{{ $usuario->usuario }}" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">Nombre Completo</label>
                    <input type="text" class="form-control bg-light" value="{{ $usuario->nombre_tecnico }}" readonly>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">
                        <i class="bi bi-envelope-at me-1 text-primary"></i> Correo de Empresa / Institucional
                    </label>
                    <input type="email" name="correo_tec" class="form-control" value="{{ $usuario->correo_tec ?? '' }}" placeholder="ejemplo@novicompu.com / ejemplo@env.com.ec">
                    <div class="form-text small">Recibirás notificaciones cuando tu ticket sea atendido o resuelto.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">
                        <i class="bi bi-whatsapp me-1 text-success"></i> Teléfono / WhatsApp de Contacto
                    </label>
                    <input type="text" name="telefono" class="form-control" value="{{ $usuario->telefono ?? '' }}" placeholder="Ej: 0991234567">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">
                        <i class="bi bi-display me-1 text-danger"></i> ID de AnyDesk (Escritorio Remoto)
                    </label>
                    <input type="text" name="anydesk_id" class="form-control fw-bold" value="{{ $usuario->anydesk_id ?? '' }}" placeholder="Ej: 123 456 789">
                    <div class="form-text small">Para que el equipo de Quito se conecte directamente.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">
                        <i class="bi bi-person-badge me-1 text-info"></i> Usuario de MBA3 (ERP)
                    </label>
                    <input type="text" name="usuario_mba" class="form-control" value="{{ $usuario->usuario_mba ?? '' }}" placeholder="Ej: JPEREZ">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">
                        <i class="bi bi-upc-scan me-1 text-dark"></i> Código de Usuario / Vendedor
                    </label>
                    <input type="text" name="codigo_usuario" class="form-control" value="{{ $usuario->codigo_usuario ?? '' }}" placeholder="Ej: VEND-012">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('mistickets.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Guardar Mis Datos
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
