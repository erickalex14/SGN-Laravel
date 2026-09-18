@extends('layouts.app')

@section('contenido')
<style>
    .facturacion-container {
        padding: 24px;
        max-width: 1600px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .facturacion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        background: #ffffff;
        padding: 20px 24px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .facturacion-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .facturacion-subtitle {
        color: #64748b;
        font-size: 0.85rem;
        margin-top: 4px;
    }
    .badge-corte {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .top-nav-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 2px;
    }
    .top-nav-btn {
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-bottom: none;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #475569;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .top-nav-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .top-nav-btn.active {
        background: #ffffff;
        color: #2563eb;
        border-color: #2563eb #2563eb #ffffff;
        margin-bottom: -2px;
        box-shadow: 0 -2px 6px rgba(37,99,235,0.08);
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.02);
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        align-items: end;
    }
    .filter-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .filter-control {
        width: 100%;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        font-size: 0.88rem;
        background: #f8fafc;
        color: #0f172a;
        transition: border-color 0.15s;
    }
    .filter-control:focus {
        border-color: #2563eb;
        background: #ffffff;
        outline: none;
    }
    .selection-bar {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        padding: 14px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .selection-info {
        font-size: 0.95rem;
        font-weight: 700;
        color: #166534;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-action-facturar {
        background: #16a34a;
        color: #ffffff;
        font-weight: 700;
        padding: 10px 22px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 0.92rem;
        box-shadow: 0 2px 6px rgba(22, 163, 74, 0.25);
        transition: all 0.2s;
    }
    .btn-action-facturar:hover:not(:disabled) {
        background: #15803d;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(22, 163, 74, 0.35);
    }
    .btn-action-facturar:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }
    .table-container {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.86rem;
    }
    .orders-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .orders-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        vertical-align: middle;
    }
    .orders-table tbody tr:hover {
        background: #f8fafc;
    }
    .orders-table tr.selected-row {
        background: #ecfdf5 !important;
    }
    .badge-tipo {
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-personal {
        background: #e0e7ff;
        color: #3730a3;
    }
    .badge-empresa {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-block;
    }
    .status-finalizada, .status-reparada, .status-cerrado, .status-cerrada {
        background: #dcfce7;
        color: #15803d;
    }
    .status-entregada, .status-lista {
        background: #e0f2fe;
        color: #0369a1;
    }
    .status-nc {
        background: #fee2e2;
        color: #b91c1c;
    }
    .status-facturado {
        background: #d1fae5;
        color: #065f46;
        font-weight: 800;
    }
    .status-pendiente-fac {
        background: #fef9c3;
        color: #854d0e;
    }

    /* Modal Styling */
    .modal-backdrop-custom {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-dialog-custom {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 650px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        animation: modalFadeIn 0.2s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .modal-header-custom {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-radius: 16px 16px 0 0;
    }
    .modal-title-custom {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .modal-close-btn {
        background: transparent;
        border: none;
        font-size: 1.3rem;
        color: #64748b;
        cursor: pointer;
        line-height: 1;
        padding: 4px;
    }
    .modal-close-btn:hover {
        color: #0f172a;
    }
    .modal-body-custom {
        padding: 24px;
    }
    .modal-footer-custom {
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #f8fafc;
        border-radius: 0 0 16px 16px;
    }
    .form-group-modal {
        margin-bottom: 18px;
    }
    .form-group-modal label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .form-group-modal label span.required {
        color: #ef4444;
    }
    .form-control-modal {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        font-size: 0.92rem;
        color: #0f172a;
        background: #ffffff;
        box-sizing: border-box;
    }
    .form-control-modal:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .selected-orders-summary {
        background: #f1f5f9;
        border-radius: 10px;
        padding: 14px;
        max-height: 160px;
        overflow-y: auto;
        margin-bottom: 18px;
        border: 1px solid #e2e8f0;
    }
    .order-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        margin: 3px;
        color: #1e293b;
    }
</style>

<div class="facturacion-container">
    {{-- Encabezado Principal --}}
    <div class="facturacion-header">
        <div>
            <h1 class="facturacion-title">
                <i class="bi bi-collection-check text-success"></i> Facturación por Lotes (Milenium)
                <span class="badge-corte">A partir del 05/09/2026</span>
            </h1>
            <div class="facturacion-subtitle">
                Asigna datos de facturas emitidas en Milenium (Nro. Factura, Nro. Autorización SRI y Monto Cobrado) a órdenes cerradas o finalizadas.
            </div>
        </div>
        <div>
            <a href="{{ route('facturacion_lotes.historial') }}" class="btn btn-outline-secondary btn-sm" style="font-weight: 600; padding: 8px 16px;">
                <i class="bi bi-clock-history"></i> Ver Historial de Facturas
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- Navegación Pestañas --}}
    <div class="top-nav-tabs">
        <a href="{{ route('facturacion_lotes.index') }}" class="top-nav-btn active">
            <i class="bi bi-check2-square"></i> Órdenes Elegibles (≥ 05 Sep)
        </a>
        <a href="{{ route('facturacion_lotes.historial') }}" class="top-nav-btn">
            <i class="bi bi-journal-bookmark"></i> Lotes y Facturas Registradas
        </a>
    </div>

    {{-- Barra de Filtros --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('facturacion_lotes.index') }}">
            <div class="filter-grid">
                <div>
                    <label class="filter-label">Búsqueda Rápida</label>
                    <input type="text" name="buscar" class="filter-control" placeholder="Nro orden, cliente, RUC/cédula, serie..." value="{{ $filtros['buscar'] ?? '' }}">
                </div>
                <div>
                    <label class="filter-label">Tipo de Orden</label>
                    <select name="tipo_orden" class="filter-control">
                        <option value="">-- Todos los tipos --</option>
                        <option value="personal" {{ ($filtros['tipo_orden'] ?? '') === 'personal' ? 'selected' : '' }}>Personal</option>
                        <option value="empresa" {{ ($filtros['tipo_orden'] ?? '') === 'empresa' ? 'selected' : '' }}>Empresa / Corporativo</option>
                    </select>
                </div>
                <div>
                    <label class="filter-label">Estado Facturación</label>
                    <select name="estado_facturacion" class="filter-control">
                        <option value="Pendiente" {{ ($filtros['estado_facturacion'] ?? '') === 'Pendiente' ? 'selected' : '' }}>Pendientes de Facturar</option>
                        <option value="Facturado" {{ ($filtros['estado_facturacion'] ?? '') === 'Facturado' ? 'selected' : '' }}>Ya Facturadas</option>
                        <option value="todos" {{ ($filtros['estado_facturacion'] ?? '') === 'todos' ? 'selected' : '' }}>Todas las Órdenes</option>
                    </select>
                </div>
                <div>
                    <label class="filter-label">Sucursal</label>
                    <select name="sucursal_id" class="filter-control">
                        <option value="">-- Todas las sucursales --</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ (string)($filtros['sucursal_id'] ?? '') === (string)$sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->ciudad }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 8px 18px; border-radius: 8px; flex: 1;">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                    <a href="{{ route('facturacion_lotes.index') }}" class="btn btn-light" style="border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 14px;" title="Limpiar Filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Barra de Selección Interactiva --}}
    <div class="selection-bar">
        <div class="selection-info">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 1.2rem;"></i>
            <span>Órdenes seleccionadas: <strong id="lbl-total-seleccionadas">0</strong></span>
            <span style="color: #64748b; font-weight: normal;">|</span>
            <span style="font-size: 0.85rem; color: #475569;">Total listadas: {{ count($ordenes) }}</span>
        </div>
        <div>
            <button type="button" id="btn-abrir-modal" class="btn-action-facturar" disabled onclick="abrirModalFacturacion()">
                <i class="bi bi-file-earmark-plus"></i> Añadir información de facturación
            </button>
        </div>
    </div>

    {{-- Tabla de Órdenes --}}
    <div class="table-container">
        <table class="orders-table" id="tabla-ordenes">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="check-all" title="Seleccionar todas las visibles" style="cursor: pointer; transform: scale(1.15);">
                    </th>
                    <th>Nro. Orden</th>
                    <th>Tipo</th>
                    <th>Estado Orden</th>
                    <th>Cliente / RUC</th>
                    <th>Equipo / Falla</th>
                    <th>Fecha Ingreso</th>
                    <th>Sucursal</th>
                    <th>Estado Facturación</th>
                    <th>Nro. Factura</th>
                    <th>Nro. Autorización</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes as $ord)
                    @php
                        $badgeEstadoClass = 'status-cerrado';
                        $estLower = strtolower($ord->estado_orden ?? '');
                        if (str_contains($estLower, 'credito')) {
                            $badgeEstadoClass = 'status-nc';
                        } elseif (str_contains($estLower, 'entrega')) {
                            $badgeEstadoClass = 'status-entregada';
                        }
                    @endphp
                    <tr data-tipo="{{ $ord->tipo_orden }}" data-id="{{ $ord->orden_id }}" data-nro="{{ $ord->nro_orden }}">
                        <td style="text-align: center;">
                            <input type="checkbox" class="order-check" 
                                   value="{{ $ord->orden_id }}" 
                                   data-tipo="{{ $ord->tipo_orden }}" 
                                   data-nro="{{ $ord->nro_orden }}"
                                   style="cursor: pointer; transform: scale(1.15);">
                        </td>
                        <td>
                            <strong style="color: #1e40af;">{{ $ord->nro_orden }}</strong>
                        </td>
                        <td>
                            @if($ord->tipo_orden === 'empresa')
                                <span class="badge-tipo badge-empresa"><i class="bi bi-building"></i> Empresa</span>
                            @else
                                <span class="badge-tipo badge-personal"><i class="bi bi-person"></i> Personal</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-status {{ $badgeEstadoClass }}">
                                {{ $ord->estado_orden }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">{{ $ord->cliente ?? 'S/N' }}</div>
                            <div style="font-size: 0.76rem; color: #64748b;">
                                {{ $ord->identificacion ? 'ID: ' . $ord->identificacion : '' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $ord->tipo }} {{ $ord->marca }} {{ $ord->modelo }}</div>
                            <div style="font-size: 0.75rem; color: #64748b; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ord->falla ?? $ord->observacion }}">
                                {{ $ord->falla ?? $ord->observacion ?? 'Sin detalle' }}
                            </div>
                        </td>
                        <td style="white-space: nowrap;">
                            <div>{{ date('d/m/Y', strtotime($ord->fecha_de_ingreso)) }}</div>
                            <div style="font-size: 0.72rem; color: #64748b;">{{ date('H:i', strtotime($ord->fecha_de_ingreso)) }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #475569;">{{ $ord->sucursal ?? 'N/D' }}</span>
                        </td>
                        <td>
                            @if($ord->estado_facturacion === 'Facturado')
                                <span class="badge-status status-facturado"><i class="bi bi-check2-all"></i> Facturado</span>
                            @else
                                <span class="badge-status status-pendiente-fac"><i class="bi bi-hourglass-split"></i> Pendiente</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($ord->nro_factura))
                                <span style="font-family: monospace; font-weight: 700; color: #0f172a; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                    {{ $ord->nro_factura }}
                                </span>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Sin factura</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($ord->nro_autorizacion_factura))
                                <span style="font-family: monospace; font-size: 0.78rem; color: #475569;" title="{{ $ord->nro_autorizacion_factura }}">
                                    {{ Str::limit($ord->nro_autorizacion_factura, 15) }}
                                </span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px; color: #64748b;">
                            <i class="bi bi-inbox" style="font-size: 2.2rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                            <strong>No se encontraron órdenes elegibles.</strong>
                            <p style="font-size: 0.82rem; margin-top: 4px;">Solo se muestran órdenes creadas a partir del 05/09/2026 que estén Finalizadas, Reparadas, Entregadas, Cerradas o en Nota de Crédito.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL INTERACTIVO PARA INGRESAR DATOS DE FACTURA MILENIUM --}}
<div id="modal-facturacion" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        <form id="form-facturacion-lote" onsubmit="guardarFacturacionLote(event)">
            @csrf
            <div class="modal-header-custom">
                <h5 class="modal-title-custom">
                    <i class="bi bi-receipt-cutoff text-success"></i> Añadir Información de Facturación (Milenium)
                </h5>
                <button type="button" class="modal-close-btn" onclick="cerrarModalFacturacion()">&times;</button>
            </div>
            
            <div class="modal-body-custom">
                {{-- Resumen de órdenes seleccionadas --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px; display: block;">
                        Órdenes que se vincularán a esta factura: (<span id="modal-contador-ordenes">0</span>)
                    </label>
                    <div class="selected-orders-summary" id="modal-chips-ordenes">
                        <!-- Chips dinámicos -->
                    </div>
                </div>

                {{-- Contenedor oculto de inputs de órdenes --}}
                <div id="modal-hidden-inputs"></div>

                <div class="row">
                    <div class="col-md-6 form-group-modal">
                        <label for="nro_factura">Número de Factura Milenium <span class="required">*</span></label>
                        <input type="text" id="nro_factura" name="nro_factura" class="form-control-modal" placeholder="Ej: 026-001-000019326" required autocomplete="off">
                        <small style="color: #64748b; font-size: 0.74rem;">Número físico/electrónico emitido en Milenium.</small>
                    </div>
                    <div class="col-md-6 form-group-modal">
                        <label for="valor_facturado">Valor Facturado Total ($) <span class="required">*</span></label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 10px; font-weight: 700; color: #64748b;">$</span>
                            <input type="number" id="valor_facturado" name="valor_facturado" step="0.01" min="0" class="form-control-modal" style="padding-left: 28px;" placeholder="0.00" required>
                        </div>
                        <small style="color: #64748b; font-size: 0.74rem;">Monto total cobrado por el conjunto de órdenes.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group-modal">
                        <label for="nro_autorizacion">Número de Autorización SRI / Milenium</label>
                        <input type="text" id="nro_autorizacion" name="nro_autorizacion" class="form-control-modal" placeholder="49 dígitos o código de autorización" maxlength="60" autocomplete="off">
                        <small style="color: #64748b; font-size: 0.74rem;">Clave de acceso / autorización SRI (opcional).</small>
                    </div>
                    <div class="col-md-6 form-group-modal">
                        <label for="fecha_factura">Fecha de Factura <span class="required">*</span></label>
                        <input type="date" id="fecha_factura" name="fecha_factura" class="form-control-modal" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="form-group-modal">
                    <label for="observaciones">Observaciones / Notas Adicionales</label>
                    <textarea id="observaciones" name="observaciones" class="form-control-modal" rows="2" placeholder="Detalles de retención, cliente que consolida el pago, etc. (Opcional)"></textarea>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn btn-light" style="font-weight: 600; border: 1.5px solid #cbd5e1;" onclick="cerrarModalFacturacion()">
                    Cancelar
                </button>
                <button type="submit" id="btn-guardar-lote" class="btn btn-success" style="font-weight: 700; padding: 8px 22px;">
                    <i class="bi bi-save-fill me-1"></i> Guardar y Facturar Órdenes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('check-all');
    const orderChecks = document.querySelectorAll('.order-check');
    const btnAbrirModal = document.getElementById('btn-abrir-modal');
    const lblTotal = document.getElementById('lbl-total-seleccionadas');

    function actualizarSeleccion() {
        const seleccionados = document.querySelectorAll('.order-check:checked');
        const count = seleccionados.length;
        lblTotal.textContent = count;
        btnAbrirModal.disabled = count === 0;

        orderChecks.forEach(chk => {
            const tr = chk.closest('tr');
            if (chk.checked) {
                tr.classList.add('selected-row');
            } else {
                tr.classList.remove('selected-row');
            }
        });

        if (orderChecks.length > 0) {
            checkAll.checked = (count === orderChecks.length);
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            orderChecks.forEach(chk => chk.checked = checkAll.checked);
            actualizarSeleccion();
        });
    }

    orderChecks.forEach(chk => {
        chk.addEventListener('change', actualizarSeleccion);
    });
});

function abrirModalFacturacion() {
    const seleccionados = document.querySelectorAll('.order-check:checked');
    if (seleccionados.length === 0) {
        alert('Por favor, seleccione al menos una orden para facturar.');
        return;
    }

    const containerChips = document.getElementById('modal-chips-ordenes');
    const containerHidden = document.getElementById('modal-hidden-inputs');
    const contador = document.getElementById('modal-contador-ordenes');

    containerChips.innerHTML = '';
    containerHidden.innerHTML = '';
    contador.textContent = seleccionados.length;

    seleccionados.forEach((chk, idx) => {
        const tipo = chk.dataset.tipo;
        const ordenId = chk.value;
        const nroOrden = chk.dataset.nro;

        // Chip visual
        const chip = document.createElement('span');
        chip.className = 'order-chip';
        chip.innerHTML = `<i class="bi bi-${tipo === 'empresa' ? 'building' : 'person'} text-primary"></i> ${nroOrden}`;
        containerChips.appendChild(chip);

        // Inputs ocultos para submit
        containerHidden.innerHTML += `
            <input type="hidden" name="ordenes[${idx}][tipo_orden]" value="${tipo}">
            <input type="hidden" name="ordenes[${idx}][orden_id]" value="${ordenId}">
            <input type="hidden" name="ordenes[${idx}][nro_orden]" value="${nroOrden}">
        `;
    });

    document.getElementById('modal-facturacion').style.display = 'flex';
}

function cerrarModalFacturacion() {
    document.getElementById('modal-facturacion').style.display = 'none';
}

// Cerrar al hacer clic fuera
window.addEventListener('click', function(e) {
    const modal = document.getElementById('modal-facturacion');
    if (e.target === modal) {
        cerrarModalFacturacion();
    }
});

async function guardarFacturacionLote(event) {
    event.preventDefault();
    const form = event.target;
    const btnGuardar = document.getElementById('btn-guardar-lote');

    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';

    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("facturacion_lotes.guardar") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (response.ok && data.ok) {
            alert(data.message || 'Factura por lote guardada exitosamente.');
            window.location.reload();
        } else {
            let errorMsg = data.message || 'Ocurrió un error al procesar el lote.';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('\n');
            }
            alert(errorMsg);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save-fill me-1"></i> Guardar y Facturar Órdenes';
        }
    } catch (err) {
        alert('Error de conexión con el servidor: ' + err.message);
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save-fill me-1"></i> Guardar y Facturar Órdenes';
    }
}
</script>
@endsection
