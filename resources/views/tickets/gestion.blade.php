@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4" style="max-width: 1500px;">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-3 p-md-4 rounded-4 shadow-sm border">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                    <i class="bi bi-headset me-1"></i> Mesa de Ayuda Centralizada · Sede Quito
                </span>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Gestión y Atención de Tickets</h2>
            <p class="text-muted small mb-0">Atención de requerimientos de soporte técnico y sistemas generados desde tiendas y áreas externas.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('tickets.auditoria') }}" class="btn btn-outline-success px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 flex-fill flex-md-grow-0" style="min-height: 40px;">
                <i class="bi bi-bar-chart-line-fill"></i> Auditoría & Reportes
            </a>
            <a href="{{ route('mistickets.create') }}" class="btn btn-outline-primary px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 flex-fill flex-md-grow-0" style="min-height: 40px;">
                <i class="bi bi-plus-lg"></i> Crear Ticket
            </a>
        </div>
    </div>

    <!-- Pestañas Principales (Solo Sistemas TI Habilitado) -->
    <div class="d-flex gap-2 mb-4 p-1 bg-light rounded-4 border overflow-auto" style="max-width: 550px; white-space: nowrap;">
        <a href="{{ route('tickets.gestion', array_merge(request()->except('page'), ['tab' => 'sistemas'])) }}" 
           class="btn flex-fill py-2 px-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 btn-purple text-white shadow-sm"
           style="background: #7c3aed; border-color: #7c3aed;">
            <i class="bi bi-hdd-network"></i> Sistemas TI (Quito)
            <span class="badge bg-white text-dark rounded-pill">{{ $conteoSistemas }}</span>
        </a>
        <button type="button" disabled class="btn flex-fill py-2 px-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 btn-light text-muted opacity-50" title="Módulo de Soporte Hardware temporalmente deshabilitado" style="cursor: not-allowed;">
            <i class="bi bi-tools"></i> Soporte Hardware
            <span class="badge bg-secondary rounded-pill" style="font-size: 0.65rem;">Pausado</span>
        </button>
    </div>

    <!-- Tarjetas de métricas rápidas -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-2.5 p-md-3 bg-white h-100 border-start border-4 border-primary">
                <div class="text-muted small fw-semibold">Abiertos</div>
                <div class="h4 fw-bold text-primary mb-0 mt-1">{{ $stats['abiertos'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-2.5 p-md-3 bg-white h-100 border-start border-4 border-warning">
                <div class="text-muted small fw-semibold">En Proceso</div>
                <div class="h4 fw-bold text-warning mb-0 mt-1">{{ $stats['en_proceso'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-2.5 p-md-3 bg-white h-100 border-start border-4 border-secondary">
                <div class="text-muted small fw-semibold">En Espera</div>
                <div class="h4 fw-bold text-secondary mb-0 mt-1">{{ $stats['en_espera'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-2.5 p-md-3 bg-white h-100 border-start border-4" style="border-left-color: #9333ea !important;">
                <div class="text-muted small fw-semibold">En MBA (48h)</div>
                <div class="h4 fw-bold mb-0 mt-1" style="color: #9333ea;">{{ $stats['en_mba'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-2.5 p-md-3 bg-white h-100 border-start border-4 border-success">
                <div class="text-muted small fw-semibold">Resueltos</div>
                <div class="h4 fw-bold text-success mb-0 mt-1">{{ $stats['resueltos'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-2.5 p-md-3 bg-white h-100 border-start border-4 border-dark">
                <div class="text-muted small fw-semibold">Cerrados</div>
                <div class="h4 fw-bold text-dark mb-0 mt-1">{{ $stats['cerrados'] }}</div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('tickets.gestion') }}" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-0" placeholder="Buscar por código, solicitante o detalle...">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="estado" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="abierto" {{ request('estado') === 'abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="en_proceso" {{ request('estado') === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="en_espera" {{ request('estado') === 'en_espera' ? 'selected' : '' }}>En Espera</option>
                    <option value="en_mba" {{ request('estado') === 'en_mba' ? 'selected' : '' }}>En Manos de MBA (48h)</option>
                    <option value="resuelto" {{ request('estado') === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                    <option value="cerrado" {{ request('estado') === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="empresa" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todas las empresas</option>
                    <option value="NOVICOMPU" {{ request('empresa') === 'NOVICOMPU' ? 'selected' : '' }}>Novicompu</option>
                    <option value="ENV" {{ request('empresa') === 'ENV' ? 'selected' : '' }}>ENV</option>
                    <option value="OTRO" {{ request('empresa') === 'OTRO' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="asignado_a_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todos los técnicos asignados</option>
                    <option value="sin_asignar" {{ request('asignado_a_id') === 'sin_asignar' ? 'selected' : '' }}>Sin Asignar</option>
                    @foreach($tecnicosQuito as $tec)
                        <option value="{{ $tec->id }}" {{ request('asignado_a_id') == $tec->id ? 'selected' : '' }}>{{ $tec->nombre_tecnico ?: $tec->usuario }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-3 rounded-3 fw-semibold flex-fill" style="min-height: 40px;">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                </button>
                <a href="{{ route('tickets.gestion', ['tab' => $tab]) }}" class="btn btn-light border px-3 rounded-3 text-muted d-flex align-items-center justify-content-center" title="Limpiar filtros" style="min-height: 40px;">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- VISTA MÓVIL: Tarjetas para Mesa de Ayuda (Pantallas < 768px) -->
    <div class="d-block d-md-none mb-4">
        <div class="d-flex flex-column gap-3">
            @forelse($tickets as $t)
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 {{ in_array($t->estado, ['resuelto', 'cerrado']) ? 'border-success' : ($t->estado === 'en_mba' ? 'border-purple' : ($t->estado === 'abierto' ? 'border-primary' : 'border-warning')) }}" style="{{ $t->estado === 'en_mba' ? 'border-left-color: #9333ea !important;' : '' }}">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <span class="fw-bold font-monospace text-primary fs-6">{{ $t->codigo_ticket }}</span>
                            <div class="text-muted small" style="font-size: 11px;">
                                <i class="bi bi-clock me-1"></i>{{ $t->fecha_apertura ? $t->fecha_apertura->format('d/m/Y H:i') : $t->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div>
                            @if($t->estado === 'en_mba')
                                <span class="badge text-white rounded-pill px-2.5 py-1" style="background: #9333ea; font-size: 11px;">En MBA (48h)</span>
                            @elseif($t->estado === 'resuelto')
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 11px;"><i class="bi bi-check-circle me-1"></i>Resuelto</span>
                            @elseif($t->estado === 'cerrado')
                                <span class="badge bg-dark text-white rounded-pill px-2.5 py-1" style="font-size: 11px;">Cerrado</span>
                            @elseif($t->estado === 'en_proceso')
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size: 11px;">En Proceso</span>
                            @elseif($t->estado === 'en_espera')
                                <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" style="font-size: 11px;">En Espera</span>
                            @else
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size: 11px;">Abierto</span>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('tickets.show', $t->id) }}" class="fw-bold text-dark text-decoration-none fs-6 d-block mb-1">
                        {{ $t->titulo }}
                    </a>

                    <div class="d-flex flex-wrap align-items-center gap-1.5 mb-2">
                        <span class="badge bg-purple bg-opacity-10 text-purple fw-semibold rounded-pill px-2 py-0.5" style="color: #7c3aed; background-color: #f3e8ff; font-size: 11px;">
                            {{ $t->categoria }}
                        </span>
                        <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                            <i class="bi bi-shop me-1"></i>{{ $t->tienda_nombre ?: ($t->sucursalCliente->nombre ?? 'Tienda') }} ({{ $t->empresa_origen }})
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                            <i class="bi bi-person me-1"></i>{{ $t->solicitante ? ($t->solicitante->nombre_tecnico ?: $t->solicitante->usuario) : ($t->solicitante_nombre ?: 'Solicitante') }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <div class="small">
                            @if($t->asignadoA)
                                <span class="text-dark fw-semibold"><i class="bi bi-person-check-fill text-success me-1"></i>{{ $t->asignadoA->nombre_tecnico ?: $t->asignadoA->usuario }}</span>
                            @else
                                <button type="button" onclick="autoasignarmeTicket({{ $t->id }})" class="btn btn-sm btn-outline-success rounded-pill py-0.5 px-2.5 small fw-bold">
                                    <i class="bi bi-hand-index-thumb me-1"></i> Asignarme
                                </button>
                            @endif
                        </div>
                        <div class="d-flex gap-1.5">
                            <a href="{{ route('tickets.imprimir', $t->id) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-3" title="Imprimir PDF del Ticket (Estilo OT)">
                                <i class="bi bi-printer-fill"></i>
                            </a>
                            @if(in_array($t->estado, ['resuelto', 'cerrado']))
                                <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-sm btn-outline-success rounded-3 fw-bold px-3 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-file-earmark-check-fill"></i> Ver
                                </a>
                            @else
                                <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-sm btn-primary rounded-3 fw-bold px-3 d-inline-flex align-items-center gap-1">
                                    Atender <i class="bi bi-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted bg-white">
                    <i class="bi bi-check2-all fs-1 d-block mb-2 text-success opacity-50"></i>
                    No hay tickets pendientes en esta bandeja.
                </div>
            @endforelse
        </div>
    </div>

    <!-- VISTA ESCRITORIO: Tabla Principal de Tickets (Pantallas >= 768px) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Código / Fecha</th>
                        <th>Tienda & Solicitante</th>
                        <th>Requerimiento / Categoría</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Asignado A</th>
                        <th class="text-end pe-4">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('tickets.show', $t->id) }}" class="fw-bold text-primary text-decoration-none font-monospace">
                                    {{ $t->codigo_ticket }}
                                </a>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $t->fecha_apertura ? $t->fecha_apertura->format('d/m/Y H:i') : $t->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    <i class="bi bi-shop me-1 text-danger"></i>{{ $t->tienda_nombre ?: ($t->sucursalCliente->nombre ?? 'Tienda Externa') }}
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-person me-1"></i>{{ $t->solicitante ? ($t->solicitante->nombre_tecnico ?: $t->solicitante->usuario) : ($t->solicitante_nombre ?: 'Solicitante') }}
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $t->empresa_origen }}</span>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', $t->id) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                    {{ $t->titulo }}
                                </a>
                                <div>
                                    @if($t->tipo_ticket === 'sistemas')
                                        <span class="badge bg-purple bg-opacity-10 text-purple fw-semibold rounded-pill px-2 py-0.5" style="color: #7c3aed; background-color: #f3e8ff; font-size: 0.7rem;">
                                            {{ $t->categoria }}
                                        </span>
                                    @else
                                        <span class="badge bg-blue bg-opacity-10 text-primary fw-semibold rounded-pill px-2 py-0.5" style="background-color: #eff6ff; font-size: 0.7rem;">
                                            {{ $t->categoria }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($t->prioridad === 'urgente')
                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1"><i class="bi bi-fire me-1"></i>Urgente</span>
                                @elseif($t->prioridad === 'alta')
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Alta</span>
                                @elseif($t->prioridad === 'media')
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1" style="color:#0284c7; background:#e0f2fe;">Media</span>
                                @else
                                    <span class="badge bg-light text-muted rounded-pill px-2 py-1">Baja</span>
                                @endif
                            </td>
                            <td>
                                @if($t->estado === 'abierto')
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-1">Abierto</span>
                                @elseif($t->estado === 'en_proceso')
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">En Proceso</span>
                                @elseif($t->estado === 'en_espera')
                                    <span class="badge bg-secondary text-white rounded-pill px-2 py-1">En Espera</span>
                                @elseif($t->estado === 'en_mba')
                                    <span class="badge text-white rounded-pill px-2 py-1 shadow-sm" style="background: #9333ea;">
                                        En Manos MBA (48h)
                                    </span>
                                    @if($t->numero_ticket_mba)
                                        <div class="text-muted small mt-0.5 font-monospace" style="font-size: 10.5px;">
                                            Ticket #{{ $t->numero_ticket_mba }}
                                        </div>
                                    @endif
                                @elseif($t->estado === 'resuelto')
                                    <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="bi bi-check-circle me-1"></i>Resuelto</span>
                                @elseif($t->estado === 'cerrado')
                                    <span class="badge bg-dark text-white rounded-pill px-2 py-1">Cerrado</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1">Cancelado</span>
                                @endif
                            </td>
                            <td>
                                @if($t->asignadoA)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($t->asignadoA->nombre_tecnico ?: $t->asignadoA->usuario, 0, 1)) }}
                                        </div>
                                        <span class="small fw-semibold text-dark">{{ $t->asignadoA->nombre_tecnico ?: $t->asignadoA->usuario }}</span>
                                    </div>
                                @else
                                    <button type="button" onclick="autoasignarmeTicket({{ $t->id }})" class="btn btn-sm btn-outline-success rounded-pill py-0 px-2 small">
                                        <i class="bi bi-hand-index-thumb me-1"></i> Asignarme
                                    </button>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <a href="{{ route('tickets.imprimir', $t->id) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-3" title="Imprimir PDF del Ticket (Estilo OT)">
                                        <i class="bi bi-printer-fill"></i>
                                    </a>
                                    @if(in_array($t->estado, ['resuelto', 'cerrado']))
                                        <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-sm btn-outline-success rounded-3 fw-bold px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
                                            <i class="bi bi-file-earmark-check-fill"></i> Ver Detalle
                                        </a>
                                    @else
                                        <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-sm btn-primary rounded-3 fw-bold px-3">
                                            Atender <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-all fs-1 d-block mb-2 text-success opacity-50"></i>
                                No hay tickets pendientes en esta bandeja.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tickets->hasPages())
        <div class="p-3 d-flex justify-content-center">
            {{ $tickets->links() }}
        </div>
    @endif
</div>

<script>
function autoasignarmeTicket(ticketId) {
    Swal.fire({
        title: '¿Deseas autoasignarte este ticket?',
        text: 'El ticket pasará a estado En Proceso bajo tu responsabilidad.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, asignarme',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("{{ url('/tickets/gestion') }}/" + ticketId + "/asignar", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tecnico_id: {{ $usuario->id }} })
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Asignado!',
                        text: data.mensaje,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ url('/tickets/gestion') }}/" + ticketId;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al asignar',
                        text: data.error || 'No se pudo completar la asignación.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Ocurrió un fallo en la solicitud.'
                });
            });
        }
    });
}
</script>
@endsection
