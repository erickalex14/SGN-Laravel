@extends('layouts.app')

@section('titulo', 'Gestión de Nómina (Admin Master)')

@section('contenido')
<div class="container-fluid py-3">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
        <div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">
                <i class="bi bi-bank text-primary me-2"></i>Gestión Completa de Nómina
            </h3>
            <p class="text-muted small mb-0">Panel exclusivo de Administración Master para control de sueldos, bonificaciones, sanciones y roles de pago.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nomina.exportar_excel') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success fw-bold">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar Rol de Pagos (Excel)
            </a>
            <a href="{{ route('nomina.mis_datos') }}" class="btn btn-outline-secondary fw-bold">
                <i class="bi bi-person me-1"></i> Mis Datos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show fw-bold mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-primary">
                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Total Empleados</small>
                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalEmpleados) }}</div>
            </div>
        </div>
        <div class="col-xl-2.5 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-info">
                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Sueldo Base Total</small>
                <div class="fs-4 fw-bold text-info mt-1">${{ number_format($totalSueldoBase, 2) }}</div>
            </div>
        </div>
        <div class="col-xl-2.5 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success">
                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Total Bonificaciones</small>
                <div class="fs-4 fw-bold text-success mt-1">+${{ number_format($totalBonificaciones, 2) }}</div>
            </div>
        </div>
        <div class="col-xl-2.5 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-danger">
                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Total Sanciones</small>
                <div class="fs-4 fw-bold text-danger mt-1">-${{ number_format($totalSanciones, 2) }}</div>
            </div>
        </div>
        <div class="col-xl-2.5 col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
                <small class="text-uppercase fw-bold text-white-50" style="font-size: 0.7rem;">Total Presupuesto Neto</small>
                <div class="fs-4 fw-bold mt-1">${{ number_format($totalNeto, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- FILTROS Y BUSQUEDA -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('nomina.admin') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="buscar" class="form-control border-start-0" placeholder="Buscar por nombre, usuario o cédula..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="sucursal_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todas las Sucursales --</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>
                                Sucursal {{ $suc->ciudad }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-grow-1">Filtrar</button>
                    @if(request()->anyFilled(['buscar', 'sucursal_id']))
                        <a href="{{ route('nomina.admin') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- SECCIÓN DE SOLICITUDES DE VACACIONES PENDIENTES / PROCESADAS -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-airplane-engines text-primary me-2"></i>Gestión de Solicitudes de Vacaciones
            </h5>
            @php $cantPendientes = $solicitudesVacaciones->where('estado', 'Pendiente')->count(); @endphp
            @if($cantPendientes > 0)
                <span class="badge bg-warning text-dark px-3 py-2 fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $cantPendientes }} Solicitud(es) Pendiente(s)
                </span>
            @else
                <span class="badge bg-success px-3 py-2 fw-bold">Al Día (Sin pendientes)</span>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                    <thead class="table-light text-uppercase" style="font-size: 0.73rem;">
                        <tr>
                            <th style="padding: 10px 14px;">Empleado</th>
                            <th>Fechas Solicitadas</th>
                            <th>Días Solicitados</th>
                            <th>Días Disponibles</th>
                            <th>Motivo Empleado</th>
                            <th>Estado</th>
                            <th class="text-center" style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudesVacaciones as $sol)
                            @php $dn = $sol->datosNomina; @endphp
                            <tr>
                                <td style="padding: 10px 14px;">
                                    <strong>{{ $dn->nombres_completos ?? $sol->usuario->nombre_tecnico ?? $sol->usuario->usuario }}</strong>
                                    <small class="d-block text-muted">@ {{ $sol->usuario->usuario }}</small>
                                </td>
                                <td>
                                    <div>{{ $sol->fecha_inicio ? $sol->fecha_inicio->format('d/m/Y') : '' }} al {{ $sol->fecha_fin ? $sol->fecha_fin->format('d/m/Y') : '' }}</div>
                                    @if($sol->estado === 'Aprobado' && $sol->fecha_inicio_aprobada)
                                        <small class="text-success fw-bold">Aprobado: {{ $sol->fecha_inicio_aprobada->format('d/m/Y') }} al {{ $sol->fecha_fin_aprobada->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td><strong class="fs-6 text-primary">{{ $sol->dias_solicitados }}</strong> días</td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">
                                        {{ $dn ? $dn->calcularDiasPendientes() : 0 }} días disp.
                                    </span>
                                </td>
                                <td>{{ $sol->observacion_empleado ?: 'Sin observación' }}</td>
                                <td>
                                    @if($sol->estado === 'Aprobado')
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Aprobado</span>
                                    @elseif($sol->estado === 'Rechazado')
                                        <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Rechazado</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock me-1"></i>Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if($sol->estado === 'Pendiente')
                                            <button type="button" class="btn btn-xs btn-success fw-bold" onclick="abrirModalAprobarVacaciones({{ $sol->id }}, {{ json_encode($sol) }})">
                                                <i class="bi bi-check-lg"></i> Aprobar
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="rechazarVacaciones({{ $sol->id }})">
                                                <i class="bi bi-x-lg"></i> Rechazar
                                            </button>
                                        @endif
                                        <a href="{{ route('nomina.vacaciones_imprimir', $sol->id) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Imprimir Comprobante PDF">
                                            <i class="bi bi-printer"></i> PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No existen solicitudes de vacaciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TABLA PRINCIPAL DE NOMINA -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead style="background: #1e293b; color: #ffffff; text-transform: uppercase; font-size: 0.75rem;">
                    <tr>
                        <th style="padding: 12px 14px;">Empleado</th>
                        <th>Cédula</th>
                        <th>Sucursal / Rol</th>
                        <th>Contacto & Emergencia</th>
                        <th>Estado Afiliación</th>
                        <th>Vacaciones (Antigüedad / Saldo)</th>
                        <th class="text-end">Sueldo Base</th>
                        <th class="text-end">Bonos</th>
                        <th class="text-end">Sanciones</th>
                        <th class="text-end">Total Neto</th>
                        <th class="text-center" style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                        @php $dn = $u->datosNomina; @endphp
                        <tr>
                            <td style="padding: 10px 14px;">
                                <div class="d-flex align-items-center gap-2">
                                    @if(!empty($dn->foto_url))
                                        <img src="{{ asset($dn->foto_url) }}" alt="Foto" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #3b82f6;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                            {{ strtoupper(substr($u->usuario, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong class="d-block text-dark">{{ $dn->nombres_completos ?? $u->nombre_tecnico ?? $u->usuario }}</strong>
                                        <small class="text-muted">@ {{ $u->usuario }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>{{ $dn->cedula ?? $u->usuario }}</strong></td>
                            <td>
                                <div><i class="bi bi-building me-1 text-secondary"></i>{{ $u->sucursalPrincipal->ciudad ?? 'N/A' }}</div>
                                <span class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;">{{ $dn->cargo ?: ($u->rol->rol ?? $u->grupo->nombre ?? 'Empleado') }}</span>
                            </td>
                            <td>
                                @if(!empty($dn->telefono))
                                    <div><i class="bi bi-telephone me-1 text-primary"></i>{{ $dn->telefono }}</div>
                                @endif
                                @if(!empty($dn->email_personal))
                                    <div class="small text-muted"><i class="bi bi-envelope me-1"></i>{{ $dn->email_personal }}</div>
                                @endif
                                @if(!empty($dn->contacto_emergencia))
                                    <div class="small text-danger mt-1 fw-bold" title="Contacto de Emergencia">
                                        <i class="bi bi-shield-exclamation me-1"></i>{{ $dn->contacto_emergencia }}
                                    </div>
                                @elseif(empty($dn->telefono) && empty($dn->email_personal))
                                    <span class="text-muted small">Sin registrar</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark px-2 py-1 fw-bold">
                                    {{ $dn->estado_afiliacion ?? 'Por Afiliar' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $dn->calcularAniosAntiguedad() }} Años Antig.</div>
                                <div class="small text-success fw-bold">{{ $dn->calcularDiasPendientes() }}d disp / {{ $dn->calcularDiasVacacionesAnuales() }}d año</div>
                                <span class="badge bg-light text-primary border" style="font-size: 0.68rem;">{{ $dn->obtenerEstadoVacaciones() }}</span>
                            </td>
                            <td class="text-end fw-bold">${{ number_format((float)($dn->sueldo_base ?? 0), 2) }}</td>
                            <td class="text-end fw-bold text-success">+${{ number_format((float)($dn->bonificaciones ?? 0), 2) }}</td>
                            <td class="text-end fw-bold text-danger">-${{ number_format((float)($dn->sanciones ?? 0), 2) }}</td>
                            <td class="text-end">
                                <strong class="text-success fs-6">${{ number_format((float)($dn->total_a_recibir ?? 0), 2) }}</strong>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="abrirModalEditarNomina({{ $u->id }}, {{ json_encode($dn) }}, '{{ $u->usuario }}')">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </button>
                                @if(!empty($dn->hoja_vida_url))
                                    <a href="{{ asset($dn->hoja_vida_url) }}" target="_blank" class="btn btn-sm btn-outline-info mt-1" title="Ver Hoja de Vida">
                                        <i class="bi bi-file-earmark-pdf"></i> CV
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No se encontraron registros de nómina con los criterios especificados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function abrirModalEditarNomina(usuarioId, dn, nombreUsuario) {
    Swal.fire({
        title: '<span style="font-size: 1.1rem; font-weight: 800;"><i class="bi bi-person-gear text-primary me-2"></i>Editar Nómina: ' + (dn.nombres_completos || nombreUsuario) + '</span>',
        html: `
            <form id="swal-form-nomina" style="text-align: left; font-size: 0.85rem;" class="mt-2">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-bold small text-secondary mb-1">Sueldo Base ($)</label>
                        <input type="number" step="0.01" id="swal-sueldo" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.9rem; font-weight:700;" value="${dn.sueldo_base || '0.00'}">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small text-success mb-1">+ Bonificaciones ($)</label>
                        <input type="number" step="0.01" id="swal-bonos" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.9rem; font-weight:700;" value="${dn.bonificaciones || '0.00'}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-danger mb-1">- Sanciones ($)</label>
                        <input type="number" step="0.01" id="swal-sanciones" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.9rem; font-weight:700;" value="${dn.sanciones || '0.00'}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-primary mb-1">Estado Afiliación</label>
                        <select id="swal-afiliacion" class="swal2-select m-0 w-100" style="height: 38px; font-size: 0.85rem;">
                            <option value="Afiliado (IESS)" ${(dn.estado_afiliacion === 'Afiliado (IESS)') ? 'selected' : ''}>Afiliado (IESS)</option>
                            <option value="Período de Prueba" ${(dn.estado_afiliacion === 'Período de Prueba') ? 'selected' : ''}>Período de Prueba</option>
                            <option value="Por Afiliar" ${(dn.estado_afiliacion === 'Por Afiliar') ? 'selected' : ''}>Por Afiliar</option>
                            <option value="No Afiliado" ${(dn.estado_afiliacion === 'No Afiliado') ? 'selected' : ''}>No Afiliado</option>
                            <option value="Pasante" ${(dn.estado_afiliacion === 'Pasante') ? 'selected' : ''}>Pasante</option>
                        </select>
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Cargo / Puesto Nómina</label>
                        <input type="text" id="swal-cargo" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" placeholder="Ej: Desarrollador de Software" value="${dn.cargo || ''}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Fecha Ingreso</label>
                        <input type="date" id="swal-ingreso" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${dn.fecha_ingreso ? dn.fecha_ingreso.substring(0,10) : ''}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Fecha Salida</label>
                        <input type="date" id="swal-salida" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${dn.fecha_salida ? dn.fecha_salida.substring(0,10) : ''}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Cédula</label>
                        <input type="text" id="swal-cedula" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${dn.cedula || ''}">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Nombres Completos</label>
                        <input type="text" id="swal-nombres" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${dn.nombres_completos || ''}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Teléfono Personal</label>
                        <input type="text" id="swal-telefono" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${dn.telefono || ''}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Email Personal</label>
                        <input type="email" id="swal-email" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${dn.email_personal || ''}">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label fw-bold small text-danger mb-1"><i class="bi bi-shield-exclamation me-1"></i>Contacto de Emergencia</label>
                        <textarea id="swal-emergencia" class="swal2-textarea m-0 w-100" style="height: 55px; font-size: 0.85rem;" placeholder="Nombre completo, teléfono y parentesco de emergencia...">${dn.contacto_emergencia || ''}</textarea>
                    </div>
                </div>
            </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-save me-1"></i> Guardar Cambios',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        preConfirm: () => {
            return {
                sueldo_base: document.getElementById('swal-sueldo').value,
                bonificaciones: document.getElementById('swal-bonos').value,
                sanciones: document.getElementById('swal-sanciones').value,
                estado_afiliacion: document.getElementById('swal-afiliacion').value,
                cargo: document.getElementById('swal-cargo').value,
                fecha_ingreso: document.getElementById('swal-ingreso').value,
                fecha_salida: document.getElementById('swal-salida').value,
                nombres_completos: document.getElementById('swal-nombres').value,
                cedula: document.getElementById('swal-cedula').value,
                telefono: document.getElementById('swal-telefono').value,
                email_personal: document.getElementById('swal-email').value,
                contacto_emergencia: document.getElementById('swal-emergencia').value,
            };
        }
    }).then((res) => {
        if (res.isConfirmed && res.value) {
            enviarActualizacionNominaAdmin(usuarioId, res.value);
        }
    });
}

function enviarActualizacionNominaAdmin(usuarioId, payload) {
    Swal.fire({
        title: 'Guardando datos...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const formData = new FormData();
    Object.keys(payload).forEach(k => {
        if (payload[k] !== null && payload[k] !== undefined) {
            formData.append(k, payload[k]);
        }
    });

    fetch("{{ url('/nomina/admin/guardar') }}/" + usuarioId, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(r => r.json().then(data => ({ status: r.status, body: data })))
    .then(res => {
        if (res.body && res.body.ok) {
            Swal.fire('¡Éxito!', res.body.mensaje || 'Actualizado con éxito', 'success').then(() => location.reload());
        } else {
            let msg = res.body?.error || res.body?.mensaje || 'No se pudo actualizar.';
            if (res.body?.errors) {
                msg = Object.values(res.body.errors).flat().join('<br>');
            }
            Swal.fire('Error', msg, 'error');
        }
    })
    .catch(err => Swal.fire('Error', 'Fallo de conexión: ' + err.message, 'error'));
}

function abrirModalAprobarVacaciones(id, sol) {
    const fIni = sol.fecha_inicio ? sol.fecha_inicio.substring(0,10) : '';
    const fFin = sol.fecha_fin ? sol.fecha_fin.substring(0,10) : '';
    const dSolicitados = sol.dias_solicitados || 1;

    Swal.fire({
        title: '<span style="font-size: 1.1rem; font-weight: 800;"><i class="bi bi-check-circle text-success me-2"></i>Aprobar Vacaciones</span>',
        html: `
            <form id="swal-form-vac-aprob" style="text-align: left; font-size: 0.85rem;" class="mt-2">
                <div class="alert alert-info border-0 py-2 px-3 small mb-3">
                    Revisión de solicitud para el colaborador. Puedes aprobar los días solicitados o ajustar fechas/días asignados.
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-primary mb-1">Días Aprobados</label>
                        <input type="number" id="swal-vac-dias" class="swal2-input m-0 w-100" style="height: 38px; font-size: 1rem; font-weight:700;" min="1" max="60" value="${dSolicitados}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Fecha Inicio Aprobada</label>
                        <input type="date" id="swal-vac-ini" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${fIni}">
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Fecha Fin Aprobada</label>
                        <input type="date" id="swal-vac-fin" class="swal2-input m-0 w-100" style="height: 38px; font-size: 0.85rem;" value="${fFin}">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Observación del Administrador</label>
                        <textarea id="swal-vac-obs" class="swal2-textarea m-0 w-100" style="height: 55px; font-size: 0.85rem;" placeholder="Comentarios de aprobación u observaciones de la administración..."></textarea>
                    </div>
                </div>
            </form>
        `,
        width: '520px',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Confirmar Aprobación',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#166534',
        preConfirm: () => {
            return {
                dias_aprobados: document.getElementById('swal-vac-dias').value,
                fecha_inicio_aprobada: document.getElementById('swal-vac-ini').value,
                fecha_fin_aprobada: document.getElementById('swal-vac-fin').value,
                observacion_admin: document.getElementById('swal-vac-obs').value,
            };
        }
    }).then((res) => {
        if (res.isConfirmed && res.value) {
            enviarAprobacionVacaciones(id, res.value);
        }
    });
}

function enviarAprobacionVacaciones(id, payload) {
    Swal.fire({ title: 'Aprobando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

    const formData = new FormData();
    Object.keys(payload).forEach(k => { if (payload[k]) formData.append(k, payload[k]); });

    fetch("{{ url('/nomina/vacaciones/aprobar') }}/" + id, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(r => r.json().then(data => ({ status: r.status, body: data })))
    .then(res => {
        if (res.body && res.body.ok) {
            Swal.fire('¡Aprobado!', res.body.mensaje || 'Vacaciones aprobadas con éxito.', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', res.body?.error || 'No se pudo aprobar la solicitud.', 'error');
        }
    })
    .catch(err => Swal.fire('Error', 'Fallo de conexión: ' + err.message, 'error'));
}

function rechazarVacaciones(id) {
    Swal.fire({
        title: '¿Rechazar solicitud?',
        text: 'Ingresa el motivo de rechazo para el colaborador:',
        input: 'textarea',
        inputPlaceholder: 'Motivo del rechazo...',
        showCancelButton: true,
        confirmButtonText: 'Sí, Rechazar',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancelar',
        preConfirm: (text) => {
            if (!text) {
                Swal.showValidationMessage('Debes ingresar un motivo de rechazo.');
            }
            return text;
        }
    }).then((res) => {
        if (res.isConfirmed && res.value) {
            Swal.fire({ title: 'Rechazando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            const formData = new FormData();
            formData.append('observacion_admin', res.value);

            fetch("{{ url('/nomina/vacaciones/rechazar') }}/" + id, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
            .then(r => r.json().then(data => ({ status: r.status, body: data })))
            .then(res => {
                if (res.body && res.body.ok) {
                    Swal.fire('Rechazado', res.body.mensaje || 'Solicitud rechazada.', 'info').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.body?.error || 'No se pudo rechazar.', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Fallo de conexión: ' + err.message, 'error'));
        }
    });
}
</script>
@endsection
