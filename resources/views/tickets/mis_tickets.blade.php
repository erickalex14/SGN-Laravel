@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1400px;">
    <!-- Encabezado con bienvenida -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-4 rounded-4 shadow-sm border">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                    <i class="bi bi-ticket-perforated me-1"></i> Portal de Requerimientos y Soporte
                </span>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Mis Solicitudes y Tickets</h2>
            <p class="text-muted small mb-0">Crea solicitudes de soporte técnico o requerimientos de sistemas y haz seguimiento en tiempo real.</p>
        </div>
        <div>
            <a href="{{ route('mistickets.create') }}" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i> Crear Nuevo Ticket
            </a>
        </div>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Total Creados</div>
                        <div class="h3 fw-bold text-dark mb-0">{{ $stats['total'] }}</div>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-collection fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">En Atención / Espera</div>
                        <div class="h3 fw-bold text-warning mb-0">{{ $stats['abiertos'] }}</div>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Resueltos</div>
                        <div class="h3 fw-bold text-success mb-0">{{ $stats['resueltos'] }}</div>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-secondary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Cerrados</div>
                        <div class="h3 fw-bold text-secondary mb-0">{{ $stats['cerrados'] }}</div>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-archive fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('mistickets.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-0" placeholder="Buscar por código, título o detalle...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="tipo" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    <option value="soporte_tecnico" {{ request('tipo') === 'soporte_tecnico' ? 'selected' : '' }}>🛠️ Soporte Técnico</option>
                    <option value="sistemas" {{ request('tipo') === 'sistemas' ? 'selected' : '' }}>⚡ Sistemas TI (Quito)</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="estado" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="abierto" {{ request('estado') === 'abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="en_proceso" {{ request('estado') === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="en_espera" {{ request('estado') === 'en_espera' ? 'selected' : '' }}>En Espera</option>
                    <option value="resuelto" {{ request('estado') === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                    <option value="cerrado" {{ request('estado') === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 rounded-3">Filtrar</button>
                @if(request()->hasAny(['q', 'tipo', 'estado']))
                    <a href="{{ route('mistickets.index') }}" class="btn btn-outline-secondary rounded-3" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Tickets -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Código / Fecha</th>
                        <th>Tipo / Categoría</th>
                        <th>Título & Tienda Origen</th>
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
                                <span class="fw-bold text-dark">{{ $t->codigo_ticket }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $t->fecha_apertura ? $t->fecha_apertura->format('d/m/Y H:i') : $t->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                @if($t->tipo_ticket === 'sistemas')
                                    <span class="badge bg-purple bg-opacity-10 text-purple fw-semibold rounded-pill px-2 py-1" style="color: #7c3aed; background-color: #f3e8ff;">
                                        ⚡ Sistemas TI
                                    </span>
                                @else
                                    <span class="badge bg-blue bg-opacity-10 text-primary fw-semibold rounded-pill px-2 py-1" style="background-color: #eff6ff;">
                                        🛠️ Soporte Técnico
                                    </span>
                                @endif
                                <div class="text-muted small mt-1">{{ $t->categoria }}</div>
                            </td>
                            <td>
                                <a href="{{ route('mistickets.show', $t->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                    {{ $t->titulo }}
                                </a>
                                <div class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $t->tienda_nombre ?: ($t->sucursalCliente->nombre ?? 'Tienda Externa') }} 
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $t->empresa_origen }}</span>
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
                                    <span class="text-muted small fst-italic">Por asignar (Quito)</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('mistickets.show', $t->id) }}" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">
                                    Ver Detalle <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                No tienes tickets registrados en este momento.
                                <div class="mt-3">
                                    <a href="{{ route('mistickets.create') }}" class="btn btn-sm btn-primary rounded-3">
                                        <i class="bi bi-plus-lg me-1"></i> Crear mi primer ticket
                                    </a>
                                </div>
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
@endsection
