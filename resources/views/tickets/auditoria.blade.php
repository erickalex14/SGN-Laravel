@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4" style="max-width: 1400px;">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-shield-lock-fill me-1"></i> Panel Exclusivo Administradores
                </span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                    <i class="bi bi-clock-history me-1"></i> Auditoría en Tiempo Real
                </span>
            </div>
            <h2 class="h3 fw-bold text-dark mb-0">Auditoría & Reportería de Tickets</h2>
            <p class="text-muted small mb-0">Métricas de rendimiento, tiempos de resolución, satisfacción y trazabilidad de requerimientos.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('tickets.gestion') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-headset"></i> Ir a Mesa de Ayuda
            </a>
            <button type="button" id="btn-exportar-xlsx" onclick="exportarExcelAuditoria()" class="btn btn-success rounded-3 px-4 py-2 fw-bold text-white d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-excel-fill"></i> Exportar Excel Enterprise (XLSX)
            </button>
            <a href="{{ route('tickets.auditoria.exportar', request()->query()) }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 text-dark d-inline-flex align-items-center gap-1.5" title="Descargar versión CSV plano">
                <i class="bi bi-file-earmark-spreadsheet"></i> CSV
            </a>
        </div>
    </div>

    <!-- 1. KPIs y Métricas del Período -->
    <div class="row g-3 mb-4">
        <!-- Total Tickets -->
        <div class="col-6 col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Tickets</div>
                        <div class="h3 fw-bold text-dark mb-0 mt-1">{{ number_format($kpis['total']) }}</div>
                        <div class="text-muted small mt-1">En el período</div>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="bi bi-ticket-perforated-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasa de Resolución -->
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tasa de Resolución</div>
                        <div class="h3 fw-bold text-success mb-0 mt-1">{{ $kpis['tasa_resolucion'] }}%</div>
                        <div class="text-muted small mt-1">
                            <b>{{ $kpis['resueltos'] }}</b> resueltos / <b>{{ $kpis['abiertos'] }}</b> en curso
                        </div>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- En Manos de MBA (48h) -->
        <div class="col-6 col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4" style="border-left-color: #9333ea !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">En Manos MBA (48h)</div>
                        <div class="h3 fw-bold mb-0 mt-1" style="color: #9333ea;">{{ number_format($kpis['en_mba']) }}</div>
                        <div class="text-muted small mt-1">
                            <b>{{ $kpis['total_mba'] }}</b> casos MBA total
                        </div>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="background: #f3e8ff; color: #9333ea; width: 46px; height: 46px;">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tiempo Promedio de Resolución -->
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tiempo Prom. Resolución</div>
                        <div class="h3 fw-bold text-dark mb-0 mt-1">{{ $kpis['tiempo_promedio_resolucion'] }}</div>
                        <div class="text-muted small mt-1">Apertura a solución</div>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="bi bi-stopwatch-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Satisfacción del Solicitante -->
        <div class="col-6 col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Satisfacción</div>
                        <div class="h3 fw-bold text-warning mb-0 mt-1 d-flex align-items-center gap-1">
                            <span>{{ $kpis['calif_promedio'] ?? 'N/A' }}</span>
                            @if($kpis['calif_promedio'])
                                <i class="bi bi-star-fill text-warning fs-6"></i>
                            @endif
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $kpis['total_calificados'] }} calificados
                        </div>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="bi bi-star-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Paneles de Resumen Visual: Distribución & Top Categorías -->
    <div class="row g-3 mb-4">
        <!-- Tipos y Empresas -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="fw-bold text-dark small mb-3 text-uppercase d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-pie-chart-fill text-primary me-1"></i> Distribución de Carga</span>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Soporte Técnico (Hardware/Tiendas):</span>
                        <span class="fw-bold">{{ $kpis['desglose_tipo']['soporte_tecnico'] ?? 0 }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php
                            $porcSop = $kpis['total'] > 0 ? (($kpis['desglose_tipo']['soporte_tecnico'] ?? 0) / $kpis['total']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-primary" style="width: {{ $porcSop }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Sistemas / Software / TI:</span>
                        <span class="fw-bold">{{ $kpis['desglose_tipo']['sistemas'] ?? 0 }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php
                            $porcSys = $kpis['total'] > 0 ? (($kpis['desglose_tipo']['sistemas'] ?? 0) / $kpis['total']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-purple" style="background-color: #8b5cf6; width: {{ $porcSys }}%"></div>
                    </div>
                </div>

                <hr class="my-2">

                <div class="fw-semibold text-dark small mb-2 mt-2">Por Empresa / Cadena:</div>
                <div class="d-flex flex-wrap gap-2">
                    <div class="bg-light p-2 rounded-3 border flex-grow-1 text-center">
                        <div class="text-muted" style="font-size: 11px;">Novicompu</div>
                        <div class="fw-bold fs-6 text-dark">{{ $kpis['desglose_empresa']['NOVICOMPU'] ?? 0 }}</div>
                    </div>
                    <div class="bg-light p-2 rounded-3 border flex-grow-1 text-center">
                        <div class="text-muted" style="font-size: 11px;">ENV</div>
                        <div class="fw-bold fs-6 text-dark">{{ $kpis['desglose_empresa']['ENV'] ?? 0 }}</div>
                    </div>
                    <div class="bg-light p-2 rounded-3 border flex-grow-1 text-center">
                        <div class="text-muted" style="font-size: 11px;">Otros</div>
                        <div class="fw-bold fs-6 text-dark">{{ $kpis['desglose_empresa']['OTRO'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Categorías -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="fw-bold text-dark small mb-3 text-uppercase d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-bar-chart-fill text-primary me-1"></i> Requerimientos Frecuentes</span>
                </div>
                @if($kpis['top_categorias']->isEmpty())
                    <div class="text-center text-muted small py-4">No hay datos en el periodo.</div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($kpis['top_categorias'] as $cat)
                            @php
                                $porc = $kpis['total'] > 0 ? ($cat->total / $kpis['total']) * 100 : 0;
                            @endphp
                            <div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-dark fw-semibold text-truncate" style="max-width: 240px;">{{ $cat->categoria }}</span>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark">{{ $cat->total }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: {{ $porc }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Rendimiento Agentes / Técnicos -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="fw-bold text-dark small mb-3 text-uppercase d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-person-badge-fill text-primary me-1"></i> Desempeño Técnicos</span>
                </div>
                @if($kpis['rendimiento_tecnicos']->isEmpty())
                    <div class="text-center text-muted small py-4">No hay asignaciones registradas.</div>
                @else
                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm table-borderless align-middle small mb-0">
                            <thead>
                                <tr class="text-muted border-bottom" style="font-size: 11px;">
                                    <th>Agente</th>
                                    <th class="text-center">Atendidos</th>
                                    <th class="text-center">Resueltos</th>
                                    <th class="text-center">Calif.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kpis['rendimiento_tecnicos'] as $rt)
                                    <tr class="border-bottom">
                                        <td class="fw-semibold text-dark text-truncate" style="max-width: 130px;">
                                            {{ $rt->asignadoA ? ($rt->asignadoA->nombre_tecnico ?: $rt->asignadoA->usuario) : 'ID ' . $rt->asignado_a_id }}
                                        </td>
                                        <td class="text-center">{{ $rt->total_asignados }}</td>
                                        <td class="text-center text-success fw-bold">{{ $rt->total_resueltos }}</td>
                                        <td class="text-center">
                                            @if($rt->promedio_calificacion)
                                                <span class="badge bg-warning bg-opacity-10 text-dark">{{ number_format($rt->promedio_calificacion, 1) }} <i class="bi bi-star-fill text-warning"></i></span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Barra de Filtros Avanzados -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <form method="GET" action="{{ route('tickets.auditoria') }}" id="form-filtros">
            <div class="row g-3 align-items-end">
                <!-- Rango de Fechas -->
                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-semibold text-dark">Fecha Desde</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ $fechaDesde }}">
                </div>
                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-semibold text-dark">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ $fechaHasta }}">
                </div>

                <!-- Estado -->
                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-semibold text-dark">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">-- Todos los Estados --</option>
                        <option value="abierto" {{ $estado === 'abierto' ? 'selected' : '' }}>Abierto</option>
                        <option value="en_proceso" {{ $estado === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="en_espera" {{ $estado === 'en_espera' ? 'selected' : '' }}>En Espera</option>
                        <option value="en_mba" {{ $estado === 'en_mba' ? 'selected' : '' }}>En Manos de MBA (48h)</option>
                        <option value="resuelto" {{ $estado === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                        <option value="cerrado" {{ $estado === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                        <option value="cancelado" {{ $estado === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <!-- Tipo Ticket -->
                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-semibold text-dark">Tipo de Soporte</label>
                    <select name="tipo_ticket" class="form-select form-select-sm">
                        <option value="">-- Todos los Tipos --</option>
                        <option value="soporte_tecnico" {{ $tipoTicket === 'soporte_tecnico' ? 'selected' : '' }}>Soporte Técnico (Hardware)</option>
                        <option value="sistemas" {{ $tipoTicket === 'sistemas' ? 'selected' : '' }}>Sistemas / Software / TI</option>
                    </select>
                </div>

                <!-- Empresa -->
                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-semibold text-dark">Empresa</label>
                    <select name="empresa_origen" class="form-select form-select-sm">
                        <option value="">-- Todas --</option>
                        <option value="NOVICOMPU" {{ $empresaOrigen === 'NOVICOMPU' ? 'selected' : '' }}>Novicompu</option>
                        <option value="ENV" {{ $empresaOrigen === 'ENV' ? 'selected' : '' }}>ENV</option>
                        <option value="OTRO" {{ $empresaOrigen === 'OTRO' ? 'selected' : '' }}>Otros</option>
                    </select>
                </div>

                <!-- Técnico Asignado -->
                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-semibold text-dark">Técnico Asignado</label>
                    <select name="asignado_a_id" class="form-select form-select-sm">
                        <option value="">-- Todos los Técnicos --</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}" {{ $asignadoAId == $tec->id ? 'selected' : '' }}>
                                {{ $tec->nombre_tecnico ?: $tec->usuario }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tienda / Sucursal -->
                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label small fw-semibold text-dark">Tienda Solicitante</label>
                    <select name="sucursal_cliente_id" class="form-select form-select-sm">
                        <option value="">-- Todas las Tiendas --</option>
                        @foreach($tiendas as $td)
                            <option value="{{ $td->id }}" {{ $sucursalClienteId == $td->id ? 'selected' : '' }}>
                                {{ $td->codigo }} - {{ $td->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Prioridad -->
                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-semibold text-dark">Prioridad</label>
                    <select name="prioridad" class="form-select form-select-sm">
                        <option value="">-- Todas --</option>
                        <option value="baja" {{ $prioridad === 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="media" {{ $prioridad === 'media' ? 'selected' : '' }}>Media</option>
                        <option value="alta" {{ $prioridad === 'alta' ? 'selected' : '' }}>Alta</option>
                        <option value="urgente" {{ $prioridad === 'urgente' ? 'selected' : '' }}>Urgente</option>
                    </select>
                </div>

                <!-- Buscador de texto -->
                <div class="col-12 col-md-5 col-xl-4">
                    <label class="form-label small fw-semibold text-dark">Búsqueda rápida</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Código, N° MBA, título, descripción, solución o solicitante..." value="{{ $q }}">
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="col-12 col-md-12 col-xl-3 d-flex gap-2 justify-content-end">
                    <a href="{{ route('tickets.auditoria') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 rounded-3 fw-bold shadow-sm">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrar
                    </button>
                </div>
            </div>

            <!-- Accesos rápidos de período -->
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top">
                <span class="text-muted small fw-semibold me-1"><i class="bi bi-calendar-event me-1"></i> Períodos Rápidos:</span>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="setPeriodo('hoy')">Hoy</button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="setPeriodo('semana')">Esta Semana</button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="setPeriodo('mes')">Este Mes</button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="setPeriodo('mes_anterior')">Mes Anterior</button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="setPeriodo('anio')">Todo el Año</button>
            </div>
        </form>
    </div>

    <!-- 4. Tabla de Auditoría de Registros -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
            <div class="fw-bold text-dark small text-uppercase">
                <i class="bi bi-list-check text-primary me-1"></i> Registros de Auditoría ({{ $tickets->total() }} encontrados)
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light text-muted fw-semibold" style="font-size: 12px;">
                    <tr>
                        <th class="ps-3">Código / Tipo</th>
                        <th>Categoría</th>
                        <th>Tienda / Solicitante</th>
                        <th>Técnico Asignado</th>
                        <th>Apertura / Resolución</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        @php
                            $badgeEstado = match($t->estado) {
                                'abierto' => 'bg-info text-dark',
                                'en_proceso' => 'bg-warning text-dark',
                                'en_espera' => 'bg-secondary text-white',
                                'en_mba' => 'text-white',
                                'resuelto' => 'bg-success text-white',
                                'cerrado' => 'bg-dark text-white',
                                'cancelado' => 'bg-danger text-white',
                                default => 'bg-light text-dark'
                            };

                            $badgePrioridad = match($t->prioridad) {
                                'urgente' => 'bg-danger text-white',
                                'alta' => 'bg-warning text-dark',
                                'media' => 'bg-primary text-white',
                                'baja' => 'bg-secondary text-white',
                                default => 'bg-light text-dark'
                            };
                        @endphp
                        <tr>
                            <!-- Código & Tipo -->
                            <td class="ps-3">
                                <a href="javascript:void(0)" onclick="verDetalleAudit({{ $t->id }})" class="fw-bold text-primary text-decoration-none font-monospace">
                                    {{ $t->codigo_ticket }}
                                </a>
                                <div>
                                    @if($t->tipo_ticket === 'sistemas')
                                        <span class="badge bg-purple text-white px-2 py-0" style="background-color: #8b5cf6; font-size: 10px;">Sistemas</span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-0" style="font-size: 10px;">Hardware</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Categoría & Título -->
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $t->titulo }}">
                                    {{ $t->titulo }}
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 220px;">
                                    <i class="bi bi-tag me-1"></i>{{ $t->categoria }}
                                </div>
                            </td>

                            <!-- Tienda & Solicitante -->
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 200px;">
                                    {{ $t->tienda_nombre ?: ($t->sucursalCliente ? $t->sucursalCliente->codigo . ' - ' . $t->sucursalCliente->nombre : '—') }}
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 200px;">
                                    <i class="bi bi-person me-1"></i>{{ $t->solicitante ? ($t->solicitante->nombre_tecnico ?: $t->solicitante->usuario) : '—' }}
                                    <span class="badge bg-light text-muted border ms-1">{{ $t->empresa_origen }}</span>
                                </div>
                            </td>

                            <!-- Técnico Asignado -->
                            <td>
                                @if($t->asignadoA)
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="bi bi-person-check-fill text-success me-1"></i>{{ $t->asignadoA->nombre_tecnico ?: $t->asignadoA->usuario }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1">
                                        <i class="bi bi-hourglass me-1"></i>Sin asignar
                                    </span>
                                @endif
                            </td>

                            <!-- Fechas -->
                            <td>
                                <div class="text-dark small">
                                    <i class="bi bi-calendar-plus text-primary me-1"></i>{{ $t->fecha_apertura ? $t->fecha_apertura->format('d/m/Y H:i') : '—' }}
                                </div>
                                @if($t->fecha_resolucion)
                                    <div class="text-success small">
                                        <i class="bi bi-check2-all me-1"></i>{{ $t->fecha_resolucion->format('d/m/Y H:i') }}
                                        @if($t->fecha_apertura)
                                            <span class="text-muted" style="font-size: 11px;">({{ round($t->fecha_apertura->diffInMinutes($t->fecha_resolucion)/60, 1) }}h)</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-muted small" style="font-size: 11px;">Pendiente de solución</div>
                                @endif
                            </td>

                            <!-- Prioridad -->
                            <td>
                                <span class="badge {{ $badgePrioridad }} px-2 py-1 text-uppercase" style="font-size: 11px;">
                                    {{ $t->prioridad }}
                                </span>
                            </td>

                            <!-- Estado -->
                            <td>
                                @if($t->estado === 'en_mba')
                                    <span class="badge text-white px-2 py-1 text-uppercase shadow-sm" style="background: #9333ea; font-size: 11px;">
                                        En MBA (48h)
                                    </span>
                                    @if($t->numero_ticket_mba)
                                        <div class="text-muted small mt-0.5 font-monospace" style="font-size: 10px;">
                                            #{{ $t->numero_ticket_mba }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge {{ $badgeEstado }} px-2 py-1 text-uppercase" style="font-size: 11px;">
                                        {{ str_replace('_', ' ', $t->estado) }}
                                    </span>
                                    @if($t->numero_ticket_mba)
                                        <div class="mt-0.5">
                                            <span class="badge font-monospace" style="background: #f3e8ff; color: #7e22ce; font-size: 9.5px; border: 1px solid #ddd6fe;">MBA: #{{ $t->numero_ticket_mba }}</span>
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <!-- Calificación -->
                            <td>
                                @if($t->calificacion)
                                    <div class="fw-bold text-warning" title="{{ $t->comentario_calificacion }}">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi bi-star{{ $i <= $t->calificacion ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    @if($t->comentario_calificacion)
                                        <div class="text-muted small text-truncate" style="max-width: 140px; font-size: 11px;">
                                            "{{ $t->comentario_calificacion }}"
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('tickets.imprimir', $t->id) }}" target="_blank" class="btn btn-outline-danger" title="Imprimir PDF Oficial del Ticket (Estilo OT)">
                                        <i class="bi bi-printer-fill"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" onclick="verDetalleAudit({{ $t->id }})" title="Ver detalle de auditoría">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-outline-secondary" title="Abrir en Mesa de Ayuda">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                No se encontraron tickets con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal de Auditoría Rápida -->
<div class="modal fade" id="modal-audit-ticket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="modal-audit-codigo">Cargando...</h5>
                    <div class="text-muted small" id="modal-audit-sub">Detalle completo de auditoría</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modal-audit-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted small mt-2">Cargando datos del ticket...</div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <a href="#" id="modal-audit-btn-imprimir" target="_blank" class="btn btn-outline-danger btn-sm rounded-3 fw-bold px-3">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir PDF (OT)
                </a>
                <a href="#" id="modal-audit-link-gestion" class="btn btn-primary btn-sm rounded-3 fw-bold px-3">
                    <i class="bi bi-headset me-1"></i> Abrir en Mesa de Ayuda
                </a>
                <button type="button" class="btn btn-secondary btn-sm rounded-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
function setPeriodo(tipo) {
    const hoy = new Date();
    const inputDesde = document.querySelector('input[name="fecha_desde"]');
    const inputHasta = document.querySelector('input[name="fecha_hasta"]');

    function fmt(d) {
        return d.toISOString().split('T')[0];
    }

    if (tipo === 'hoy') {
        inputDesde.value = fmt(hoy);
        inputHasta.value = fmt(hoy);
    } else if (tipo === 'semana') {
        const primero = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 1));
        const ultimo = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 7));
        inputDesde.value = fmt(primero);
        inputHasta.value = fmt(ultimo);
    } else if (tipo === 'mes') {
        const primero = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const ultimo = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        inputDesde.value = fmt(primero);
        inputHasta.value = fmt(ultimo);
    } else if (tipo === 'mes_anterior') {
        const primero = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        const ultimo = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        inputDesde.value = fmt(primero);
        inputHasta.value = fmt(ultimo);
    } else if (tipo === 'anio') {
        const primero = new Date(hoy.getFullYear(), 0, 1);
        const ultimo = new Date(hoy.getFullYear(), 11, 31);
        inputDesde.value = fmt(primero);
        inputHasta.value = fmt(ultimo);
    }

    document.getElementById('form-filtros').submit();
}

function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function fmtFecha(iso) {
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        return isNaN(d.getTime()) ? String(iso) : d.toLocaleString();
    } catch(e) {
        return String(iso);
    }
}

function verDetalleAudit(id) {
    const modalEl = document.getElementById('modal-audit-ticket');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    modal.show();

    const body = document.getElementById('modal-audit-body');
    body.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="text-muted small mt-2">Cargando datos del ticket...</div>
        </div>
    `;

    fetch(`{{ url('/tickets/auditoria') }}/${id}/detalle`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(async r => {
            if (!r.ok) {
                const txt = await r.text();
                console.error('Fetch error:', r.status, txt);
                throw new Error('Error de servidor al consultar el ticket.');
            }
            return r.json();
        })
        .then(d => {
            if (!d || !d.ok || !d.ticket) {
                body.innerHTML = `<div class="alert alert-danger">${escapeHtml(d?.error || 'No se pudo encontrar la información del ticket.')}</div>`;
                return;
            }

            const t = d.ticket;
            const estadoTexto = (t.estado || '').replace(/_/g, ' ').toUpperCase();
            document.getElementById('modal-audit-codigo').innerText = `${t.codigo_ticket || 'TK'} — ${t.titulo || 'Sin título'}`;
            document.getElementById('modal-audit-sub').innerText = `Categoría: ${t.categoria || '—'} | Estado: ${estadoTexto}`;
            document.getElementById('modal-audit-link-gestion').href = `{{ url('/tickets/gestion') }}/${t.id}`;
            const btnImp = document.getElementById('modal-audit-btn-imprimir');
            if (btnImp) btnImp.href = `{{ url('/tickets') }}/${t.id}/imprimir`;

            const solicitante = d.solicitante_nombre || (t.solicitante ? (t.solicitante.nombre_tecnico || t.solicitante.usuario) : '—');
            const asignado = d.asignado_nombre || (t.asignado_a ? (t.asignado_a.nombre_tecnico || t.asignado_a.usuario) : (t.asignado_a_id ? 'ID ' + t.asignado_a_id : 'Sin asignar'));
            const tienda = d.tienda_nombre || t.tienda_nombre || (t.sucursal_cliente ? `${t.sucursal_cliente.codigo} - ${t.sucursal_cliente.nombre}` : '—');
            const solucion = d.solucion_texto || t.solucion || '';

            let adjuntosHtml = '';
            if (t.adjuntos && t.adjuntos.length > 0) {
                adjuntosHtml = '<div class="mt-3"><div class="fw-bold small text-dark mb-2"><i class="bi bi-paperclip me-1"></i>Archivos y Evidencias Adjuntas:</div><div class="d-flex flex-wrap gap-2">';
                t.adjuntos.forEach(a => {
                    const nombre = a.nombre_archivo || a.nombre_original || 'Archivo adjunto';
                    const isImg = nombre.match(/\.(jpg|jpeg|png|webp|gif)$/i);
                    adjuntosHtml += `
                        <a href="{{ url('/storage') }}/${escapeHtml(a.ruta_archivo)}" target="_blank" class="btn btn-light btn-sm border d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-${isImg ? 'image' : 'file-earmark-text'} text-primary"></i> ${escapeHtml(nombre)}
                        </a>
                    `;
                });
                adjuntosHtml += '</div></div>';
            }

            let solucionHtml = '';
            if (solucion) {
                solucionHtml = `
                    <div class="alert alert-success mt-3 p-3 rounded-3 border-success">
                        <div class="fw-bold small text-success mb-1"><i class="bi bi-check-circle-fill me-1"></i> Solución Registrada:</div>
                        <div class="small text-dark" style="white-space: pre-wrap;">${escapeHtml(solucion)}</div>
                    </div>
                `;
            }

            let mensajesHtml = '';
            if (t.mensajes && t.mensajes.length > 0) {
                mensajesHtml = '<div class="mt-4 pt-3 border-top"><div class="fw-bold small text-dark mb-3"><i class="bi bi-chat-dots-fill text-primary me-1"></i> Historial de Comunicación del Ticket (' + t.mensajes.length + '):</div><div class="d-flex flex-column gap-2" style="max-height: 250px; overflow-y: auto;">';
                t.mensajes.forEach(m => {
                    const autorNombre = m.usuario ? (m.usuario.nombre_tecnico || m.usuario.usuario) : (m.usuario_id === t.solicitante_id ? solicitante : 'Agente');
                    const esSolicitante = m.usuario_id === t.solicitante_id;
                    const fechaFmt = fmtFecha(m.created_at);
                    mensajesHtml += `
                        <div class="p-2 rounded-3 border small ${esSolicitante ? 'bg-light align-self-start' : 'bg-primary bg-opacity-10 border-primary align-self-end'}" style="max-width: 85%;">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <span class="fw-bold ${esSolicitante ? 'text-dark' : 'text-primary'}" style="font-size: 11.5px;">${escapeHtml(autorNombre)}</span>
                                <span class="text-muted" style="font-size: 10px;">${escapeHtml(fechaFmt)}</span>
                            </div>
                            <div class="text-dark" style="white-space: pre-wrap;">${escapeHtml(m.mensaje)}</div>
                        </div>
                    `;
                });
                mensajesHtml += '</div></div>';
            }

            let mbaHtml = '';
            if (t.numero_ticket_mba || t.estado === 'en_mba') {
                mbaHtml = `
                    <div class="alert p-3 rounded-3 mt-3 border d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #faf5ff; border-color: #d8b4fe !important;">
                        <div>
                            <div class="fw-bold" style="color: #7e22ce;"><i class="bi bi-clock-history me-1"></i> Requerimiento Escalado a Soporte Oficial MBA (Máx 48 Horas)</div>
                            <div class="small text-muted mt-0.5">N° Caso / Ticket MBA: <b class="font-monospace text-dark fs-6">#${escapeHtml(t.numero_ticket_mba || 'Pendiente')}</b></div>
                        </div>
                        ${t.fecha_escalado_mba ? `<span class="badge text-white px-2.5 py-1 rounded-pill" style="background: #9333ea; font-size: 11px;">Escalado: ${fmtFecha(t.fecha_escalado_mba)}</span>` : ''}
                    </div>
                `;
            }

            body.innerHTML = `
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small">Tienda Solicitante:</div>
                            <div class="fw-bold text-dark">${escapeHtml(tienda)}</div>
                            <div class="text-muted small mt-1">Solicitante: <b>${escapeHtml(solicitante)}</b> <span class="badge bg-secondary bg-opacity-10 text-secondary">${escapeHtml(t.empresa_origen || 'NOVICOMPU')}</span></div>
                            <div class="text-muted small">Teléfono / WhatsApp: <b>${escapeHtml(t.contacto_telefono || '—')}</b></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small">Atención en Mesa de Ayuda:</div>
                            <div class="fw-bold text-dark">${escapeHtml(asignado)}</div>
                            <div class="text-muted small mt-1">Prioridad: <b class="text-uppercase">${escapeHtml(t.prioridad || 'media')}</b></div>
                            <div class="text-muted small">Fecha Apertura: <b>${fmtFecha(t.fecha_apertura)}</b></div>
                        </div>
                    </div>
                </div>

                ${mbaHtml}

                <div class="mt-3">
                    <div class="fw-bold small text-dark mb-1">Descripción del Problema:</div>
                    <div class="p-3 bg-light rounded-3 border small text-dark" style="white-space: pre-wrap;">${escapeHtml(t.descripcion || '—')}</div>
                </div>

                ${adjuntosHtml}
                ${solucionHtml}

                ${t.calificacion ? `
                    <div class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning rounded-3">
                        <div class="fw-bold text-dark small"><i class="bi bi-star-fill text-warning me-1"></i> Calificación del Solicitante: ${t.calificacion} / 5</div>
                        ${t.comentario_calificacion ? `<div class="text-muted small mt-1">"${escapeHtml(t.comentario_calificacion)}"</div>` : ''}
                    </div>
                ` : ''}

                ${mensajesHtml}
            `;
        })
        .catch(err => {
            console.error('Modal error:', err);
            body.innerHTML = '<div class="alert alert-danger p-3 mb-0">Error al cargar la auditoría del ticket. Verifica la consola o intenta recargar la página.</div>';
        });
}


/* ════════════════════════════════════════════════════
   EXCEL ENTERPRISE REPORTERÍA DE TICKETS (ExcelJS)
   Estilo corporativo Novitecnología idéntico a Reportes Generales
════════════════════════════════════════════════════ */
function cargarExcelJS() {
    return new Promise((resolve, reject) => {
        if (window.ExcelJS) { resolve(); return; }
        const urls = [
            'https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js',
            'https://unpkg.com/exceljs@4.4.0/dist/exceljs.min.js'
        ];
        let i = 0;
        function tryNext() {
            if (i >= urls.length) { reject(new Error('No se pudo cargar la librería ExcelJS.')); return; }
            const s = document.createElement('script');
            s.src = urls[i++];
            s.onload = () => window.ExcelJS ? resolve() : tryNext();
            s.onerror = tryNext;
            document.head.appendChild(s);
        }
        tryNext();
    });
}

async function exportarExcelAuditoria() {
    const btn = document.getElementById('btn-exportar-xlsx');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generando Excel...';

    try {
        await cargarExcelJS();

        // Obtener parámetros actuales de la URL / filtros
        const params = new URLSearchParams(window.location.search);
        const res = await fetch(`{{ route('tickets.auditoria.data_excel') }}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('Error al obtener datos del servidor.');
        const data = await res.json();
        if (!data.ok || !data.tickets || !data.tickets.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin datos',
                text: 'No hay tickets que coincidan con los filtros actuales para exportar.'
            });
            return;
        }

        const tickets = data.tickets;
        const total = tickets.length;

        // Cálculos estadísticos
        let resueltosCount = 0;
        let mbaCount = 0;
        let abiertosCount = 0;
        let enProcesoCount = 0;
        let calificadosCount = 0;
        let sumaCalif = 0;
        let sumaMinutos = 0;
        let countConTiempo = 0;

        const catMap = {};
        const tecMap = {};
        const tiendaMap = {};

        tickets.forEach(t => {
            if (t.estado === 'resuelto' || t.estado === 'cerrado') resueltosCount++;
            if (t.estado === 'en_mba' || (t.numero_ticket_mba && t.numero_ticket_mba !== '—')) mbaCount++;
            if (t.estado === 'abierto') abiertosCount++;
            if (t.estado === 'en_proceso' || t.estado === 'en_atencion') enProcesoCount++;

            if (t.calificacion) {
                calificadosCount++;
                sumaCalif += parseFloat(t.calificacion);
            }
            if (t.tiempo_resolucion_horas) {
                sumaMinutos += (parseFloat(t.tiempo_resolucion_horas) * 60);
                countConTiempo++;
            }

            catMap[t.categoria] = (catMap[t.categoria] || 0) + 1;
            tecMap[t.asignado_nombre] = (tecMap[t.asignado_nombre] || 0) + 1;
            tiendaMap[t.tienda_nombre] = (tiendaMap[t.tienda_nombre] || 0) + 1;
        });

        const tasaResolucion = Math.round((resueltosCount / (total || 1)) * 100);
        const promCalif = calificadosCount > 0 ? (sumaCalif / calificadosCount).toFixed(1) : 'N/A';
        const promHoras = countConTiempo > 0 ? (sumaMinutos / countConTiempo / 60).toFixed(1) + ' hrs' : '—';

        const wb = new ExcelJS.Workbook();
        wb.creator = 'Novitecnología SGN - Mesa de Ayuda';
        wb.created = new Date();

        const C = {
            azulO: '1E3A8A', azul: '1E40AF', azulL: 'DBEAFE', azulXL: 'EFF6FF',
            verdeO: '065F46', verde: '166534', verdeL: 'DCFCE7', verdeXL: 'ECFDF5',
            ambar: '854D0E', ambarL: 'FEF9C3', rojo: '991B1B', rojoL: 'FEE2E2',
            purple: '6B21A8', purpleL: 'F3E8FF', gris: 'F8FAFC', grisMed: 'E2E8F0',
            grisOsc: '64748B', blanco: 'FFFFFF', negro: '0F172A'
        };

        const fl = argb => ({ type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + argb } });
        const bd = (c = 'CBD5E1') => {
            const b = { style: 'thin', color: { argb: 'FF' + c } };
            return { top: b, left: b, bottom: b, right: b };
        };
        const fn = (bold, size, color = C.negro) => ({ bold: !!bold, size: size || 10, color: { argb: 'FF' + color }, name: 'Calibri' });
        const al = (h = 'left', v = 'middle', wrap = false) => ({ horizontal: h, vertical: v, wrapText: !!wrap });

        /* ════════════════════════════════════════════════════
           HOJA 1: RESUMEN EJECUTIVO & KPIS
        ════════════════════════════════════════════════════ */
        const ws1 = wb.addWorksheet('Resumen Ejecutivo', { views: [{ showGridLines: true }] });
        ws1.columns = [{ width: 5 }, { width: 28 }, { width: 18 }, { width: 18 }, { width: 22 }, { width: 25 }, { width: 20 }];

        // Banner de Título
        ws1.mergeCells('B2:G2');
        const rTitle = ws1.getCell('B2');
        rTitle.value = 'NOVITECNOLOGÍA CIA. LTDA. — AUDITORÍA & REPORTERÍA DE TICKETS';
        rTitle.font = fn(true, 13, C.blanco);
        rTitle.fill = fl(C.azulO);
        rTitle.alignment = al('center', 'middle');
        ws1.getRow(2).height = 32;

        ws1.mergeCells('B3:G3');
        const rSub = ws1.getCell('B3');
        rSub.value = `Generado el: ${new Date().toLocaleString()} | Período: ${data.filtros.fecha_inicio} al ${data.filtros.fecha_fin} | Total Registros: ${total}`;
        rSub.font = fn(false, 9.5, C.azulXL);
        rSub.fill = fl(C.azul);
        rSub.alignment = al('center', 'middle');
        ws1.getRow(3).height = 20;

        // Tarjetas de Métricas Ejecutivas (Fila 5 y 6)
        ws1.getRow(5).height = 18;
        ws1.getRow(6).height = 26;

        const kpisCols = [
            { col: 'B', lbl: 'TOTAL TICKETS', val: total, color: C.azul, bg: C.azulL },
            { col: 'C', lbl: 'TASA RESOLUCIÓN', val: `${tasaResolucion}%`, color: C.verde, bg: C.verdeL },
            { col: 'D', lbl: 'EN CURSO / ABIERTOS', val: abiertosCount + enProcesoCount, color: C.ambar, bg: C.ambarL },
            { col: 'E', lbl: 'EN MANOS MBA (48H)', val: mbaCount, color: C.purple, bg: C.purpleL },
            { col: 'F', lbl: 'TIEMPO PROM. RESOLUCIÓN', val: promHoras, color: C.azulO, bg: C.azulXL },
            { col: 'G', lbl: 'SATISFACCIÓN PROMEDIO', val: promCalif !== 'N/A' ? `${promCalif} / 5 ★` : 'N/A', color: C.ambar, bg: C.ambarL }
        ];

        kpisCols.forEach(k => {
            const cellLbl = ws1.getCell(`${k.col}5`);
            cellLbl.value = k.lbl;
            cellLbl.font = fn(true, 8, C.grisOsc);
            cellLbl.fill = fl(C.gris);
            cellLbl.alignment = al('center', 'middle');
            cellLbl.border = bd('E2E8F0');

            const cellVal = ws1.getCell(`${k.col}6`);
            cellVal.value = k.val;
            cellVal.font = fn(true, 14, k.color);
            cellVal.fill = fl(k.bg);
            cellVal.alignment = al('center', 'middle');
            cellVal.border = bd(k.color);
        });

        // Tablas de Top Categorías y Top Técnicos (Fila 8)
        ws1.getCell('B8').value = 'TOP CATEGORÍAS DE INCIDENCIA';
        ws1.getCell('B8').font = fn(true, 10, C.azulO);
        ws1.getCell('E8').value = 'RENDIMIENTO POR TÉCNICO RESOLUTOR';
        ws1.getCell('E8').font = fn(true, 10, C.azulO);

        // Header Cat
        ws1.getCell('B9').value = 'Categoría'; ws1.getCell('B9').fill = fl(C.azulO); ws1.getCell('B9').font = fn(true, 9, C.blanco);
        ws1.getCell('C9').value = 'Cantidad'; ws1.getCell('C9').fill = fl(C.azulO); ws1.getCell('C9').font = fn(true, 9, C.blanco); ws1.getCell('C9').alignment = al('right');
        ws1.getCell('D9').value = '% Participación'; ws1.getCell('D9').fill = fl(C.azulO); ws1.getCell('D9').font = fn(true, 9, C.blanco); ws1.getCell('D9').alignment = al('right');

        // Header Tec
        ws1.getCell('E9').value = 'Técnico Responsable'; ws1.getCell('E9').fill = fl(C.azulO); ws1.getCell('E9').font = fn(true, 9, C.blanco);
        ws1.getCell('F9').value = 'Tickets Atendidos'; ws1.getCell('F9').fill = fl(C.azulO); ws1.getCell('F9').font = fn(true, 9, C.blanco); ws1.getCell('F9').alignment = al('right');
        ws1.getCell('G9').value = '% Carga'; ws1.getCell('G9').fill = fl(C.azulO); ws1.getCell('G9').font = fn(true, 9, C.blanco); ws1.getCell('G9').alignment = al('right');

        const sortObj = obj => Object.entries(obj).sort((a, b) => b[1] - a[1]);
        const topCats = sortObj(catMap).slice(0, 10);
        const topTecs = sortObj(tecMap).slice(0, 10);

        const maxRows = Math.max(topCats.length, topTecs.length);
        for (let i = 0; i < maxRows; i++) {
            const rIdx = 10 + i;
            if (topCats[i]) {
                const c1 = ws1.getCell(`B${rIdx}`); c1.value = topCats[i][0]; c1.font = fn(false, 9); c1.border = bd();
                const c2 = ws1.getCell(`C${rIdx}`); c2.value = topCats[i][1]; c2.font = fn(true, 9); c2.alignment = al('right'); c2.border = bd();
                const c3 = ws1.getCell(`D${rIdx}`); c3.value = ((topCats[i][1] / (total || 1)) * 100).toFixed(1) + '%'; c3.font = fn(false, 9); c3.alignment = al('right'); c3.border = bd();
            }
            if (topTecs[i]) {
                const t1 = ws1.getCell(`E${rIdx}`); t1.value = topTecs[i][0]; t1.font = fn(false, 9); t1.border = bd();
                const t2 = ws1.getCell(`F${rIdx}`); t2.value = topTecs[i][1]; t2.font = fn(true, 9); t2.alignment = al('right'); t2.border = bd();
                const t3 = ws1.getCell(`G${rIdx}`); t3.value = ((topTecs[i][1] / (total || 1)) * 100).toFixed(1) + '%'; t3.font = fn(false, 9); t3.alignment = al('right'); t3.border = bd();
            }
        }

        /* ════════════════════════════════════════════════════
           HOJA 2: DETALLE COMPLETO DE TICKETS
        ════════════════════════════════════════════════════ */
        const ws2 = wb.addWorksheet('Detalle de Tickets', { views: [{ showGridLines: true }] });

        const headers = [
            { header: 'N° Ticket', key: 'codigo_ticket', width: 14 },
            { header: 'Tipo', key: 'tipo_ticket', width: 16 },
            { header: 'Categoría', key: 'categoria', width: 22 },
            { header: 'Prioridad', key: 'prioridad', width: 13 },
            { header: 'Estado', key: 'estado_label', width: 18 },
            { header: 'N° Ticket MBA', key: 'numero_ticket_mba', width: 15 },
            { header: 'F. Escalado MBA', key: 'fecha_escalado_mba', width: 17 },
            { header: 'Empresa', key: 'empresa_origen', width: 14 },
            { header: 'Tienda / Origen', key: 'tienda_nombre', width: 24 },
            { header: 'Solicitante', key: 'solicitante_nombre', width: 22 },
            { header: 'Contacto / Tel.', key: 'contacto_telefono', width: 16 },
            { header: 'AnyDesk ID', key: 'anydesk', width: 14 },
            { header: 'Técnico Asignado', key: 'asignado_nombre', width: 22 },
            { header: 'F. Apertura', key: 'fecha_apertura', width: 17 },
            { header: 'F. 1ra Respuesta', key: 'fecha_primera_respuesta', width: 17 },
            { header: 'F. Resolución', key: 'fecha_resolucion', width: 17 },
            { header: 'Horas SLA', key: 'tiempo_resolucion_horas', width: 12 },
            { header: 'Tiempo Res.', key: 'tiempo_resolucion_formateado', width: 14 },
            { header: 'Calif. (1-5)', key: 'calificacion', width: 12 },
            { header: 'Reseña Solicitante', key: 'comentario_calificacion', width: 28 },
            { header: 'Título Requerimiento', key: 'titulo', width: 30 },
            { header: 'Descripción del Problema', key: 'descripcion', width: 35 },
            { header: 'Solución Técnica Aplicada', key: 'solucion', width: 35 },
            { header: 'PDF Oficial (OT)', key: 'pdf_link', width: 18 }
        ];

        ws2.columns = headers.map(h => ({ header: h.header, key: h.key, width: h.width }));

        // Estilizar Encabezados
        const headerRow = ws2.getRow(1);
        headerRow.height = 26;
        headerRow.eachCell((cell) => {
            cell.fill = fl(C.azulO);
            cell.font = fn(true, 9.5, C.blanco);
            cell.alignment = al('center', 'middle');
            cell.border = bd('1E293B');
        });

        // Insertar datos con estilos y colores condicionales
        tickets.forEach((t, idx) => {
            const row = ws2.addRow({
                codigo_ticket: t.codigo_ticket,
                tipo_ticket: t.tipo_ticket,
                categoria: t.categoria,
                prioridad: t.prioridad,
                estado_label: t.estado_label,
                numero_ticket_mba: t.numero_ticket_mba,
                fecha_escalado_mba: t.fecha_escalado_mba,
                empresa_origen: t.empresa_origen,
                tienda_nombre: t.tienda_nombre,
                solicitante_nombre: t.solicitante_nombre,
                contacto_telefono: t.contacto_telefono,
                anydesk: t.anydesk,
                asignado_nombre: t.asignado_nombre,
                fecha_apertura: t.fecha_apertura,
                fecha_primera_respuesta: t.fecha_primera_respuesta,
                fecha_resolucion: t.fecha_resolucion,
                tiempo_resolucion_horas: t.tiempo_resolucion_horas || '',
                tiempo_resolucion_formateado: t.tiempo_resolucion_formateado,
                calificacion: t.calificacion ? `${t.calificacion} ★` : '',
                comentario_calificacion: t.comentario_calificacion,
                titulo: t.titulo,
                descripcion: t.descripcion,
                solucion: t.solucion,
                pdf_link: { text: 'Abrir PDF Ticket', hyperlink: t.pdf_url, tooltip: 'Ver PDF oficial estilo OT' }
            });

            row.height = 20;
            row.eachCell((cell, colNum) => {
                cell.font = fn(false, 9, C.negro);
                cell.border = bd('E2E8F0');
                cell.alignment = al('left', 'middle');

                // Color condicional por estado
                if (colNum === 5) { // Estado
                    if (t.estado === 'resuelto' || t.estado === 'cerrado') {
                        cell.fill = fl(C.verdeL); cell.font = fn(true, 9, C.verde);
                    } else if (t.estado === 'en_mba') {
                        cell.fill = fl(C.purpleL); cell.font = fn(true, 9, C.purple);
                    } else if (t.estado === 'en_proceso' || t.estado === 'en_atencion') {
                        cell.fill = fl(C.azulL); cell.font = fn(true, 9, C.azul);
                    } else if (t.estado === 'en_espera' || t.estado === 'abierto') {
                        cell.fill = fl(C.ambarL); cell.font = fn(true, 9, C.ambar);
                    } else if (t.estado === 'cancelado') {
                        cell.fill = fl(C.rojoL); cell.font = fn(true, 9, C.rojo);
                    }
                    cell.alignment = al('center', 'middle');
                }

                // Prioridad
                if (colNum === 4) {
                    if (t.prioridad === 'URGENTE') { cell.fill = fl(C.rojoL); cell.font = fn(true, 9, C.rojo); }
                    else if (t.prioridad === 'ALTA') { cell.fill = fl(C.ambarL); cell.font = fn(true, 9, C.ambar); }
                    else if (t.prioridad === 'MEDIA') { cell.fill = fl(C.azulL); cell.font = fn(true, 9, C.azul); }
                    cell.alignment = al('center', 'middle');
                }

                // Hipervínculo PDF
                if (colNum === 24) {
                    cell.font = fn(true, 9, '2563EB', { underline: true });
                    cell.alignment = al('center', 'middle');
                }
            });
        });

        // Activar autofiltro en hoja 2
        ws2.autoFilter = { from: 'A1', to: 'X1' };

        // Descargar archivo Excel
        const buffer = await wb.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Reporte_Auditoria_Tickets_${new Date().toISOString().slice(0, 10)}.xlsx`;
        a.click();
        URL.revokeObjectURL(url);

    } catch (err) {
        console.error('Error generando Excel:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error al exportar',
            text: err.message || 'No se pudo generar el reporte Excel.'
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>
@endpush
