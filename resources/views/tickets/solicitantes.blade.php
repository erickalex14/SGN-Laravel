@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1400px;">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-4 rounded-4 shadow-sm border">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                    <i class="bi bi-people-fill me-1"></i> Administración de Solicitantes
                </span>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Gestión de Usuarios Generadores de Tickets</h2>
            <p class="text-muted small mb-0">Administra las cuentas de personal de tiendas Novicompu / ENV con su correo institucional, usuario MBA y AnyDesk.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCrearSolicitante">
                <i class="bi bi-person-plus-fill"></i> + Registrar Solicitante
            </button>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('tickets.solicitantes') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-0" placeholder="Buscar por usuario, cédula, nombre, correo o tienda...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="activo" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Solo Activos</option>
                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Solo Inactivos</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 rounded-3">Buscar</button>
                @if(request()->hasAny(['q', 'activo']))
                    <a href="{{ route('tickets.solicitantes') }}" class="btn btn-outline-secondary rounded-3"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Solicitantes -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Usuario / Cédula</th>
                        <th>Nombre & Correo</th>
                        <th>Tienda Asignada</th>
                        <th>Datos Técnicos (AnyDesk / MBA)</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitantes as $sol)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark fs-6">{{ $sol->usuario }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">ID: #{{ $sol->id }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $sol->nombre_tecnico }}</div>
                                <div class="small text-muted">
                                    <i class="bi bi-envelope me-1"></i>{{ $sol->correo_tec ?: 'Sin correo registrado' }}
                                </div>
                            </td>
                            <td>
                                @if($sol->sucursalCliente)
                                    <div class="fw-bold text-dark">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $sol->sucursalCliente->codigo }} - {{ $sol->sucursalCliente->nombre }}
                                    </div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary mt-1">{{ $sol->empresa_origen ?? 'NOVICOMPU' }}</span>
                                @else
                                    <span class="badge bg-warning text-dark">⚠️ Sin Tienda</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1 small">
                                    @if($sol->anydesk_id)
                                        <div><span class="badge bg-danger bg-opacity-10 text-danger fw-semibold">AnyDesk: {{ $sol->anydesk_id }}</span></div>
                                    @endif
                                    @if($sol->usuario_mba || $sol->codigo_usuario)
                                        <div class="text-muted">MBA: <strong>{{ $sol->usuario_mba ?: '-' }}</strong> | Cód: <strong>{{ $sol->codigo_usuario ?: '-' }}</strong></div>
                                    @endif
                                    @if($sol->telefono)
                                        <a href="https://wa.me/593{{ ltrim($sol->telefono, '0') }}" target="_blank" class="text-success text-decoration-none" style="font-size: 0.78rem;">
                                            <i class="bi bi-whatsapp me-1"></i> {{ $sol->telefono }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if((int)$sol->activo === 1)
                                    <span class="badge bg-success text-white rounded-pill px-2 py-1">Activo</span>
                                @else
                                    <span class="badge bg-secondary text-white rounded-pill px-2 py-1">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold" onclick="editarSolicitante({{ json_encode($sol) }})">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                No hay usuarios generadores de tickets registrados aún.
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#modalCrearSolicitante">
                                        <i class="bi bi-person-plus-fill me-1"></i> Registrar el primer solicitante
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($solicitantes->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $solicitantes->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Crear Solicitante -->
<div class="modal fade" id="modalCrearSolicitante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('tickets.solicitantes.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus-fill text-primary me-2"></i>Registrar Solicitante de Tienda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Cédula / Usuario para Iniciar Sesión <span class="text-danger">*</span></label>
                            <input type="text" name="usuario" class="form-control" placeholder="Ej: 1726664749" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Nombre y Apellido Completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_tecnico" class="form-control" placeholder="Ej: Juan Pérez" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Correo de Empresa / Institucional</label>
                            <input type="email" name="correo_tec" class="form-control" placeholder="ejemplo@novicompu.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Contraseña Inicial <span class="text-danger">*</span></label>
                            <input type="password" name="clave" class="form-control" placeholder="Mínimo 4 caracteres" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Cadena / Empresa <span class="text-danger">*</span></label>
                            <select name="empresa_origen" class="form-select" required>
                                <option value="NOVICOMPU" selected>Novicompu</option>
                                <option value="ENV">ENV</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold text-dark">Sucursal / Tienda Asignada <span class="text-danger">*</span></label>
                            <select name="sucursal_cliente_id" class="form-select" required>
                                <option value="">-- Seleccionar tienda --</option>
                                @foreach($tiendasNovicompu as $t)
                                    <option value="{{ $t->id }}">{{ $t->codigo }} - {{ $t->nombre }} ({{ $t->provincia ?? 'Ecuador' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">ID AnyDesk (Opcional)</label>
                            <input type="text" name="anydesk_id" class="form-control" placeholder="Ej: 123 456 789">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Usuario MBA3 (Opcional)</label>
                            <input type="text" name="usuario_mba" class="form-control" placeholder="Ej: JPEREZ">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Teléfono / WhatsApp</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej: 0991234567">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold px-4">Guardar Solicitante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Solicitante -->
<div class="modal fade" id="modalEditarSolicitante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="form-editar-solicitante" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Editar Solicitante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Usuario / Cédula</label>
                            <input type="text" id="edit-usuario" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_tecnico" id="edit-nombre" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Correo de Empresa / Institucional</label>
                            <input type="email" name="correo_tec" id="edit-correo" class="form-control" placeholder="ejemplo@novicompu.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Nueva Contraseña (Opcional)</label>
                            <input type="password" name="clave" class="form-control" placeholder="Dejar en blanco para no cambiar">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Cadena / Empresa <span class="text-danger">*</span></label>
                            <select name="empresa_origen" id="edit-empresa" class="form-select" required>
                                <option value="NOVICOMPU">Novicompu</option>
                                <option value="ENV">ENV</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold text-dark">Sucursal / Tienda Asignada <span class="text-danger">*</span></label>
                            <select name="sucursal_cliente_id" id="edit-tienda" class="form-select" required>
                                <option value="">-- Seleccionar tienda --</option>
                                @foreach($tiendasNovicompu as $t)
                                    <option value="{{ $t->id }}">{{ $t->codigo }} - {{ $t->nombre }} ({{ $t->provincia ?? 'Ecuador' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">ID AnyDesk</label>
                            <input type="text" name="anydesk_id" id="edit-anydesk" class="form-control" placeholder="Ej: 123 456 789">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Usuario MBA3</label>
                            <input type="text" name="usuario_mba" id="edit-mba" class="form-control" placeholder="Ej: JPEREZ">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-dark">Código Vendedor/Usuario</label>
                            <input type="text" name="codigo_usuario" id="edit-codigo" class="form-control" placeholder="Ej: VEND-012">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Teléfono / WhatsApp</label>
                            <input type="text" name="telefono" id="edit-telefono" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Estado</label>
                            <select name="activo" id="edit-activo" class="form-select">
                                <option value="1">Activo (Puede ingresar al SGN)</option>
                                <option value="0">Inactivo (Acceso bloqueado)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold px-4">Actualizar Solicitante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarSolicitante(sol) {
    document.getElementById('edit-usuario').value = sol.usuario;
    document.getElementById('edit-nombre').value = sol.nombre_tecnico;
    document.getElementById('edit-correo').value = sol.correo_tec || '';
    document.getElementById('edit-empresa').value = sol.empresa_origen || 'NOVICOMPU';
    document.getElementById('edit-tienda').value = sol.sucursal_cliente_id || '';
    document.getElementById('edit-anydesk').value = sol.anydesk_id || '';
    document.getElementById('edit-mba').value = sol.usuario_mba || '';
    document.getElementById('edit-codigo').value = sol.codigo_usuario || '';
    document.getElementById('edit-telefono').value = sol.telefono || '';
    document.getElementById('edit-activo').value = sol.activo ? '1' : '0';
    document.getElementById('form-editar-solicitante').action = "{{ url('/tickets/solicitantes') }}/" + sol.id;
    
    new bootstrap.Modal(document.getElementById('modalEditarSolicitante')).show();
}
</script>
@endsection
