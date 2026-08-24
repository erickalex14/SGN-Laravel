@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1500px;">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-4 rounded-4 shadow-sm border">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                    <i class="bi bi-headset me-1"></i> Mesa de Ayuda Centralizada · Sede Quito
                </span>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Gestión y Atención de Tickets</h2>
            <p class="text-muted small mb-0">Atención de requerimientos de soporte técnico y sistemas generados desde tiendas y áreas externas.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('mistickets.create') }}" class="btn btn-outline-primary px-3 py-2 rounded-3 fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Crear Ticket
            </a>
        </div>
    </div>

    <!-- Pestañas Principales (Soporte Técnico vs Sistemas TI) -->
    <div class="d-flex gap-2 mb-4 p-1 bg-light rounded-4 border" style="max-width: 550px;">
        <a href="{{ route('tickets.gestion', array_merge(request()->except('page'), ['tab' => 'soporte_tecnico'])) }}" 
           class="btn flex-fill py-2 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $tab === 'soporte_tecnico' ? 'btn-primary shadow-sm' : 'btn-light text-muted' }}">
            <i class="bi bi-tools"></i> Soporte Técnico
            <span class="badge {{ $tab === 'soporte_tecnico' ? 'bg-white text-primary' : 'bg-secondary' }} rounded-pill">{{ $conteoSoporte }}</span>
        </a>
        <a href="{{ route('tickets.gestion', array_merge(request()->except('page'), ['tab' => 'sistemas'])) }}" 
           class="btn flex-fill py-2 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $tab === 'sistemas' ? 'btn-purple text-white shadow-sm' : 'btn-light text-muted' }}"
           style="{{ $tab === 'sistemas' ? 'background: #7c3aed; border-color: #7c3aed;' : '' }}">
            <i class="bi bi-hdd-network"></i> Sistemas TI (Quito)
            <span class="badge {{ $tab === 'sistemas' ? 'bg-white text-dark' : 'bg-secondary' }} rounded-pill">{{ $conteoSistemas }}</span>
        </a>
    </div>

    <!-- Tarjetas de métricas rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
                <div class="text-muted small fw-semibold">Abiertos (Nuevos)</div>
                <div class="h4 fw-bold text-primary mb-0">{{ $stats['abiertos'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                <div class="text-muted small fw-semibold">En Proceso</div>
                <div class="h4 fw-bold text-warning mb-0">{{ $stats['en_proceso'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-secondary">
                <div class="text-muted small fw-semibold">En Espera</div>
                <div class="h4 fw-bold text-secondary mb-0">{{ $stats['en_espera'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                <div class="text-muted small fw-semibold">Resueltos (Por Confirmar)</div>
                <div class="h4 fw-bold text-success mb-0">{{ $stats['resueltos'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-dark">
                <div class="text-muted small fw-semibold">Cerrados / Histórico</div>
                <div class="h4 fw-bold text-dark mb-0">{{ $stats['cerrados'] }}</div>
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
            <div class="col-6 col-md-2">
                <select name="asignado_a_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todos los asignados</option>
                    <option value="sin_asignar" {{ request('asignado_a_id') === 'sin_asignar' ? 'selected' : '' }}>⚠️ Sin Asignar</option>
                    @foreach($tecnicosQuito as $tec)
                        <option value="{{ $tec->id }}" {{ request('asignado_a_id') == $tec->id ? 'selected' : '' }}>{{ $tec->nombre_tecnico ?: $tec->usuario }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="prioridad" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todas las prioridades</option>
                    <option value="urgente" {{ request('prioridad') === 'urgente' ? 'selected' : '' }}>🔴 Urgente</option>
                    <option value="alta" {{ request('prioridad') === 'alta' ? 'selected' : '' }}>🟠 Alta</option>
                    <option value="media" {{ request('prioridad') === 'media' ? 'selected' : '' }}>🔵 Media</option>
                    <option value="baja" {{ request('prioridad') === 'baja' ? 'selected' : '' }}>🟢 Baja</option>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100 rounded-3"><i class="bi bi-filter"></i></button>
                @if(request()->hasAny(['q', 'estado', 'empresa', 'asignado_a_id', 'prioridad']))
                    <a href="{{ route('tickets.gestion', ['tab' => $tab]) }}" class="btn btn-outline-secondary rounded-3" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Tickets -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
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
                        <tr class="{{ $t->prioridad === 'urgente' && $t->estado === 'abierto' ? 'table-danger table-opacity-10' : '' }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark fs-6">{{ $t->codigo_ticket }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $t->fecha_apertura ? $t->fecha_apertura->format('d/m/Y H:i') : $t->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $t->tienda_nombre ?: ($t->sucursalCliente->nombre ?? 'Tienda Externa') }}
                                </div>
                                <div class="small text-muted">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary me-1">{{ $t->empresa_origen }}</span>
                                    {{ $t->solicitante->nombre_tecnico ?: $t->solicitante->usuario }}
                                    @if($t->contacto_telefono)
                                        <a href="https://wa.me/593{{ ltrim($t->contacto_telefono, '0') }}" target="_blank" class="text-success text-decoration-none ms-1" title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', $t->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                    {{ $t->titulo }}
                                </a>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark border">{{ $t->categoria }}</span>
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
                                @elseif($t->estado === 'resuelto')
                                    <span class="badge bg-success text-white rounded-pill px-2 py-1">✓ Resuelto</span>
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
                                @if(in_array($t->estado, ['resuelto', 'cerrado']))
                                    <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-sm btn-outline-success rounded-3 fw-bold px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
                                        <i class="bi bi-file-earmark-check-fill"></i> Ver Detalle
                                    </a>
                                @else
                                    <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-sm btn-primary rounded-3 fw-bold px-3">
                                        Atender <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                @endif
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
        @if($tickets->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function autoasignarmeTicket(ticketId) {
    Swal.fire({
        title: '¿Deseas autoasignarte este ticket?',
        text: 'El ticket pasará a estado En Proceso bajo tu responsabilidad.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, asignarme',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669'
    }).then((r) => {
        if (r.isConfirmed) {
            Swal.showLoading();
            fetch(`/tickets/gestion/${ticketId}/asignar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tecnico_id: {{ $usuario->id }} })
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    Swal.fire('Asignado', res.mensaje, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            });
        }
    });
}
</script>
@endsection
