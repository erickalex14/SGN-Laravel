@extends('layouts.app')

@section('contenido')
<div class="container-fluid" style="padding: 20px 30px;">
    <!-- Encabezado Principal -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="h3 mb-1" style="font-weight: 700; color: #1e293b;">
                <i class="bi bi-shield-check text-primary me-2"></i>Bitácora de Auditoría
            </h2>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Historial completo de actividades del sistema y registros de seguridad de todos los usuarios.
            </p>
        </div>
        <div class="col-auto">
            <span class="badge bg-light text-dark border p-2" style="font-size: 13px;">
                <i class="bi bi-activity text-success me-1"></i> Total Registros: <strong>{{ $logs->total() }}</strong>
            </span>
        </div>
    </div>

    <!-- Panel de Filtros -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #fff;">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('bitacora.index') }}" class="row g-3 align-items-end">
                <!-- Filtro por Usuario -->
                <div class="col-md-3">
                    <label for="usuario_id" class="form-label" style="font-weight: 600; font-size: 13px; color: #475569;">Usuario</label>
                    <select name="usuario_id" id="usuario_id" class="form-select form-select-sm" style="border-radius: 8px;">
                        <option value="">-- Todos los Usuarios --</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" {{ request('usuario_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->nombre_tecnico }} ({{ $u->usuario }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Módulo -->
                <div class="col-md-2">
                    <label for="modulo" class="form-label" style="font-weight: 600; font-size: 13px; color: #475569;">Módulo</label>
                    <select name="modulo" id="modulo" class="form-select form-select-sm" style="border-radius: 8px;">
                        <option value="">-- Todos --</option>
                        @foreach($modulos as $key => $val)
                            <option value="{{ $key }}" {{ request('modulo') == $key ? 'selected' : '' }}>
                                {{ $val }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Acción -->
                <div class="col-md-2">
                    <label for="accion" class="form-label" style="font-weight: 600; font-size: 13px; color: #475569;">Acción (Ej: LOGIN)</label>
                    <input type="text" name="accion" id="accion" class="form-control form-control-sm" placeholder="Buscar acción..." value="{{ request('accion') }}" style="border-radius: 8px;">
                </div>

                <!-- Rango de Fechas -->
                <div class="col-md-3">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #475569;">Rango de Fechas (Desde / Hasta)</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                        <span class="input-group-text bg-light">-</span>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                    </div>
                </div>

                <!-- Buscador de texto general -->
                <div class="col-md-2">
                    <label for="buscar" class="form-label" style="font-weight: 600; font-size: 13px; color: #475569;">Buscar texto</label>
                    <input type="text" name="buscar" id="buscar" class="form-control form-control-sm" placeholder="Buscar en detalles..." value="{{ request('buscar') }}" style="border-radius: 8px;">
                </div>

                <!-- Botones de Acción de Filtros -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('bitacora.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 500; background: #2563eb; border-color: #2563eb;">
                        <i class="bi bi-funnel me-1"></i>Filtrar Resultados
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Contenido -->
    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1000px;">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 180px;">Fecha y Hora</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 220px;">Usuario</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 180px;">Acción</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 150px;">Módulo</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 130px;">Ref. ID</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569;">Detalle Corto</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 100px; text-align: center;">Detalles</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: 0;">
                        @forelse($logs as $log)
                            @php
                                // Mapeo estético de badges para las acciones
                                $badgeClass = 'bg-secondary';
                                $accionStr = strtoupper($log->accion);
                                
                                if (str_contains($accionStr, 'CREAR')) {
                                    $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                } elseif (str_contains($accionStr, 'EDITAR') || str_contains($accionStr, 'ACTUALIZAR')) {
                                    $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                } elseif (str_contains($accionStr, 'ELIMINAR') || str_contains($accionStr, 'REVERTIR') || str_contains($accionStr, 'RECHAZAR')) {
                                    $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                } elseif ($accionStr === 'LOGIN') {
                                    $badgeClass = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                } elseif ($accionStr === 'LOGIN_FALLIDO') {
                                    $badgeClass = 'bg-danger text-white';
                                } elseif ($accionStr === 'APROBAR_NC') {
                                    $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                }
                                
                                // Vista preliminar de detalles
                                $detallesPreview = '';
                                if ($log->detalles) {
                                    $decodificado = json_decode($log->detalles, true);
                                    if (is_array($decodificado)) {
                                        $partes = [];
                                        foreach (array_slice($decodificado, 0, 3) as $k => $v) {
                                            $valStr = is_array($v) ? json_encode($v) : (string)$v;
                                            $partes[] = "<strong>{$k}</strong>: {$valStr}";
                                        }
                                        $detallesPreview = implode(', ', $partes);
                                    } else {
                                        $detallesPreview = e(substr($log->detalles, 0, 100));
                                    }
                                }
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 14px 20px; font-size: 13px; color: #64748b;">
                                    <i class="bi bi-clock me-1"></i>{{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #334155;">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2 bg-light border text-secondary" style="width: 30px; height: 30px;">
                                            <i class="bi bi-person" style="font-size: 14px;"></i>
                                        </div>
                                        <div>
                                            <span class="d-block" style="font-weight: 600;">{{ $log->usuario_nombre }}</span>
                                            @if($log->usuario_id)
                                                <small class="text-muted" style="font-size: 11px;">ID: #{{ $log->usuario_id }}</small>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 9px;">Visitante</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 14px 20px;">
                                    <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 11px; border-radius: 6px;">
                                        {{ $log->accion }}
                                    </span>
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #475569;">
                                    <span class="text-capitalize">{{ $log->modulo }}</span>
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #334155; font-weight: 500;">
                                    @if($log->registro_id)
                                        <span class="badge bg-light text-dark border">#{{ $log->registro_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #475569; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {!! $detallesPreview ?: '<span class="text-muted">Sin detalles adicionales</span>' !!}
                                </td>
                                <td style="padding: 14px 20px; text-align: center;">
                                    @if($log->detalles || $log->ip_address || $log->user_agent)
                                        <button type="button" class="btn btn-link btn-sm p-0" onclick="mostrarDetallesLog({{ json_encode($log) }})" style="color: #2563eb; text-decoration: none;">
                                            <i class="bi bi-eye-fill" style="font-size: 16px;"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted" style="font-size: 14px;">
                                    <i class="bi bi-search d-block mb-2 style-3" style="font-size: 24px; color: #94a3b8;"></i>
                                    No se encontraron registros de auditoría que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($logs->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size: 13px;">
                Mostrando registros del <strong>{{ $logs->firstItem() }}</strong> al <strong>{{ $logs->lastItem() }}</strong> de un total de <strong>{{ $logs->total() }}</strong>
            </div>
            <div>
                {!! $logs->links('pagination::bootstrap-4') !!}
            </div>
        </div>
    @endif
</div>

<!-- Modal / Dialogo de Detalle Completo de Auditoría -->
<script>
    function mostrarDetallesLog(log) {
        let detallesHtml = `<div style="text-align: left; font-size: 13px; color: #334155; line-height: 1.6;">`;
        
        // 1. Información Técnica del Usuario y Dispositivo
        detallesHtml += `<div class="mb-3 p-3 bg-light rounded border">
            <h5 style="margin-top: 0; font-size: 14px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px;">
                <i class="bi bi-laptop me-1"></i>Información del Dispositivo
            </h5>
            <div class="row g-2">
                <div class="col-4 text-muted">Dirección IP:</div>
                <div class="col-8"><strong>${log.ip_address || 'Desconocida'}</strong></div>
                <div class="col-4 text-muted">User Agent:</div>
                <div class="col-8" style="font-size: 11px; word-break: break-all;">${log.user_agent || 'N/A'}</div>
            </div>
        </div>`;

        // 2. Carga / Payload de Datos
        if (log.detalles) {
            let parsed = null;
            try {
                parsed = JSON.parse(log.detalles);
            } catch (e) {
                parsed = log.detalles;
            }

            let renderedPayload = '';
            if (typeof parsed === 'object' && parsed !== null) {
                renderedPayload = '<pre class="p-3 bg-dark text-light rounded" style="font-size: 11px; max-height: 250px; overflow-y: auto; text-align: left; font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;">' 
                    + JSON.stringify(parsed, null, 4) 
                    + '</pre>';
            } else {
                renderedPayload = `<div class="p-3 bg-light rounded border">${parsed}</div>`;
            }

            detallesHtml += `<div class="mb-2">
                <h5 style="font-size: 14px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px; margin-bottom: 10px;">
                    <i class="bi bi-database me-1"></i>Carga de Datos (Payload)
                </h5>
                ${renderedPayload}
            </div>`;
        }

        detallesHtml += `</div>`;

        Swal.fire({
            title: `<span style="font-size:18px;font-weight:700;"><i class="bi bi-shield-lock text-primary me-2"></i>Auditoría Registro #${log.id}</span>`,
            html: detallesHtml,
            width: '600px',
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Entendido',
            confirmButtonColor: '#2563eb',
            customClass: {
                popup: 'rounded-3',
                confirmButton: 'px-4 py-2 font-semibold'
            }
        });
    }
</script>
@endsection
