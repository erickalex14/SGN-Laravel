@extends('layouts.app')

@section('titulo', 'Mis Datos Personales / Nómina')

@section('contenido')
<div class="container-fluid py-3">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
        <div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">
                <i class="bi bi-person-vcard text-primary me-2"></i>Mis Datos Personales & Nómina
            </h3>
            <p class="text-muted small mb-0">Gestiona tu información de contacto personal, foto de perfil y hoja de vida para uso interno de la empresa.</p>
        </div>
        @if($esMaster)
            <div>
                <a href="{{ route('nomina.admin') }}" class="btn btn-outline-primary fw-bold btn-sm">
                    <i class="bi bi-shield-lock me-1"></i> Ir a Panel de Administración Nómina
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show fw-bold mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Columna Izquierda: Formulario de Datos Personales (Editable) -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Actualizar Datos Personales & Archivos
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('nomina.guardar_mis_datos') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Foto de perfil y avatar -->
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                            <div class="position-relative">
                                @if(!empty($datosNomina->foto_url))
                                    <img src="{{ asset($datosNomina->foto_url) }}" alt="Foto Perfil" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #2563eb;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm fw-bold" style="width: 80px; height: 80px; font-size: 1.8rem;">
                                        {{ strtoupper(substr($usuario->usuario, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold text-dark mb-1">Foto de Perfil (JPG, PNG)</label>
                                <input type="file" name="foto_file" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted d-block mt-1">Formato recomendado: imagen cuadrada (máx 5MB).</small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Nombres Completos</label>
                                <input type="text" name="nombres_completos" class="form-control" value="{{ old('nombres_completos', $datosNomina->nombres_completos ?? $usuario->nombre_tecnico) }}" placeholder="Ej: Erick Chavarrea" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Número de Cédula / DNI</label>
                                <input type="text" name="cedula" class="form-control" value="{{ old('cedula', $datosNomina->cedula ?? $usuario->usuario) }}" placeholder="Ej: 1792487811" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Teléfono Personal de Contacto</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $datosNomina->telefono) }}" placeholder="Ej: 0991234567">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Correo Electrónico Personal</label>
                                <input type="email" name="email_personal" class="form-control" value="{{ old('email_personal', $datosNomina->email_personal) }}" placeholder="ejemplo@correo.com">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary">Contacto de Emergencia</label>
                                <textarea name="contacto_emergencia" class="form-control" rows="2" placeholder="Nombre completo, teléfono y parentesco de tu contacto de emergencia...">{{ old('contacto_emergencia', $datosNomina->contacto_emergencia) }}</textarea>
                            </div>

                            <!-- Hoja de Vida File -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary">Hoja de Vida (Curriculum Vitae)</label>
                                <div class="p-3 rounded-3" style="background: #f1f5f9;">
                                    <input type="file" name="hoja_vida_file" class="form-control form-control-sm mb-2" accept=".pdf,.doc,.docx">
                                    @if(!empty($datosNomina->hoja_vida_url))
                                        <div class="mt-2 d-flex align-items-center gap-2">
                                            <span class="badge bg-success"><i class="bi bi-file-earmark-check me-1"></i> Archivo Subido</span>
                                            <a href="{{ asset($datosNomina->hoja_vida_url) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                                <i class="bi bi-download me-1"></i> Ver / Descargar mi Hoja de Vida
                                            </a>
                                        </div>
                                    @else
                                        <small class="text-muted">Adjunta tu CV en formato PDF o Word (máx 10MB).</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-save me-1"></i> Guardar Mis Datos Personales
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Información Laboral & Financiera de Nómina (Solo Lectura) -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-uppercase fw-bold text-info" style="font-size: 0.75rem; letter-spacing: 0.05em;">Estado Laboral</span>
                        <span class="badge bg-success px-3 py-2 fw-bold" style="font-size: 0.82rem;">
                            <i class="bi bi-shield-check me-1"></i>{{ $datosNomina->estado_afiliacion ?? 'Por Afiliar' }}
                        </span>
                    </div>

                    <h4 class="fw-bold mb-1">{{ $datosNomina->nombres_completos ?? $usuario->nombre_tecnico }}</h4>
                    <p class="text-slate-300 small mb-3">
                        <i class="bi bi-building me-1"></i> Sucursal: {{ $usuario->sucursalPrincipal->ciudad ?? 'Quito' }} &nbsp;|&nbsp; 
                        <i class="bi bi-briefcase me-1"></i> Cargo: {{ $datosNomina->cargo ?: 'No especificado' }}
                    </p>

                    <hr style="border-color: rgba(255,255,255,0.15);">

                    <div class="row g-3 text-center my-2">
                        <div class="col-6">
                            <div class="p-2 rounded bg-white bg-opacity-10">
                                <small class="d-block text-slate-300" style="font-size: 0.75rem;">FECHA INGRESO</small>
                                <strong class="fs-6 text-white">{{ $datosNomina->fecha_ingreso ? $datosNomina->fecha_ingreso->format('d/m/Y') : 'No registrada' }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded bg-white bg-opacity-10">
                                <small class="d-block text-slate-300" style="font-size: 0.75rem;">FECHA SALIDA</small>
                                <strong class="fs-6 text-white">{{ $datosNomina->fecha_salida ? $datosNomina->fecha_salida->format('d/m/Y') : 'N/A (Activo)' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TARJETA DE VACACIONES (LEGAL ECUADOR) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-airplane-engines text-primary me-2"></i>Mis Vacaciones
                    </h5>
                    <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalSolicitarVacaciones">
                        <i class="bi bi-plus-circle me-1"></i>Solicitar Vacaciones
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-2 text-center mb-3">
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: #f1f5f9;">
                                <small class="text-muted d-block" style="font-size: 0.7rem;">ANTIGÜEDAD</small>
                                <strong class="fs-6 text-dark">{{ $datosNomina->calcularAniosAntiguedad() }} Años</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: #f1f5f9;">
                                <small class="text-muted d-block" style="font-size: 0.7rem;">DÍAS / AÑO</small>
                                <strong class="fs-6 text-dark">{{ $datosNomina->calcularDiasVacacionesAnuales() }} Días</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
                                <small class="text-success fw-bold d-block" style="font-size: 0.7rem;">DISPONIBLES</small>
                                <strong class="fs-5 text-success">{{ $datosNomina->calcularDiasPendientes() }} Días</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: #fef2f2; border: 1px solid #fecaca;">
                                <small class="text-danger fw-bold d-block" style="font-size: 0.7rem;">TOMADOS</small>
                                <strong class="fs-5 text-danger">{{ $datosNomina->calcularDiasTomados() }} Días</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded mb-3" style="background: #eff6ff;">
                        <small class="fw-bold text-primary">Estado de Vacaciones:</small>
                        <span class="badge bg-primary px-2 py-1 fw-bold">{{ $datosNomina->obtenerEstadoVacaciones() }}</span>
                    </div>

                    <!-- HISTORIAL DE SOLICITUDES -->
                    <h6 class="fw-bold text-dark mt-3 mb-2" style="font-size: 0.85rem;"><i class="bi bi-clock-history me-1"></i>Mis Solicitudes de Vacaciones</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Fechas</th>
                                    <th>Días</th>
                                    <th>Estado</th>
                                    <th class="text-end">Comprobante</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($solicitudesVacaciones as $sol)
                                    <tr>
                                        <td>
                                            <div>{{ $sol->fecha_inicio ? $sol->fecha_inicio->format('d/m/Y') : '' }}</div>
                                            <small class="text-muted">al {{ $sol->fecha_fin ? $sol->fecha_fin->format('d/m/Y') : '' }}</small>
                                        </td>
                                        <td><strong>{{ $sol->dias_aprobados ?? $sol->dias_solicitados }}</strong>d</td>
                                        <td>
                                            @if($sol->estado === 'Aprobado')
                                                <span class="badge bg-success">Aprobado</span>
                                            @elseif($sol->estado === 'Rechazado')
                                                <span class="badge bg-danger">Rechazado</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('nomina.vacaciones_imprimir', $sol->id) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2" title="Imprimir Comprobante PDF">
                                                <i class="bi bi-printer"></i> PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-2">No has registrado solicitudes de vacaciones.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SOLICITAR VACACIONES -->
<div class="modal fade" id="modalSolicitarVacaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('nomina.vacaciones_solicitar') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" style="font-size: 1rem;">
                        <i class="bi bi-airplane me-2"></i>Nueva Solicitud de Vacaciones
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Saldo disponible: <strong>{{ $datosNomina->calcularDiasPendientes() }} Días</strong>. Las solicitudes están sujetas a aprobación del Admin Master.
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="vac_fecha_inicio" class="form-control" required onchange="calcularDiasVacaciones()">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Fecha de Fin</label>
                            <input type="date" name="fecha_fin" id="vac_fecha_fin" class="form-control" required onchange="calcularDiasVacaciones()">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Días Solicitados</label>
                            <input type="number" name="dias_solicitados" id="vac_dias_solicitados" class="form-control fw-bold text-primary fs-5" min="1" max="60" required readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Observación / Motivo</label>
                            <textarea name="observacion_empleado" class="form-control" rows="2" placeholder="Ingresa detalles u observaciones adicionales de tu solicitud..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-send me-1"></i> Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calcularDiasVacaciones() {
    const ini = document.getElementById('vac_fecha_inicio').value;
    const fin = document.getElementById('vac_fecha_fin').value;
    if (ini && fin) {
        const d1 = new Date(ini);
        const d2 = new Date(fin);
        if (d2 >= d1) {
            const diffTime = Math.abs(d2 - d1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('vac_dias_solicitados').value = diffDays;
        } else {
            document.getElementById('vac_dias_solicitados').value = 1;
        }
    }
}
</script>
@endsection
