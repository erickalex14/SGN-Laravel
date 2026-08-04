@extends('layouts.app')

@section('contenido')
<style>
    .cg-container {
        padding: 28px 24px;
        max-width: 1450px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .cg-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        background: #ffffff;
        padding: 20px 24px;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .cg-title {
        color: #0f172a;
        font-size: 1.4rem;
        font-weight: 800;
        margin: 0;
    }
    .cg-subtitle {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 4px;
    }
    .cg-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
    .metric-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
        background: #2563eb;
    }
    .metric-card.success::before { background: #10b981; }
    .metric-card.warning::before { background: #f59e0b; }
    .metric-card.info::before { background: #0284c7; }
    .metric-label {
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .metric-value {
        color: #0f172a;
        font-size: 1.7rem;
        font-weight: 800;
        margin-top: 6px;
    }
    .cg-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        white-space: nowrap;
    }
    .btn-header-green {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #10b981;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2);
    }
    .btn-header-green:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-header-blue {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
    }
    .btn-header-blue:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    .btn-action {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-action:hover {
        background: #1d4ed8;
    }
    .btn-success-action {
        background: #10b981;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-success-action:hover {
        background: #059669;
    }

    /* PESTAÑAS DE NAVEGACIÓN UX/UI */
    .cg-tabs-container {
        display: flex;
        gap: 6px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        margin-bottom: 24px;
        overflow-x: auto;
    }
    .cg-tab-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 11px 20px;
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .cg-tab-btn:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.6);
    }
    .cg-tab-btn.active {
        background: #ffffff;
        color: #2563eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }
    .tab-count-badge {
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 800;
        background: #e2e8f0;
        color: #475569;
    }
    .cg-tab-btn.active .tab-count-badge {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .cg-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 28px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .cg-card-title {
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1.5px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        color: #1e293b;
        font-size: 0.875rem;
    }
    .custom-table th {
        background: #f8fafc;
        color: #475569;
        text-align: left;
        padding: 12px 16px;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.78rem;
        letter-spacing: 0.03em;
    }
    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .custom-table tr:hover td {
        background: #f8fafc;
    }
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-efectivo { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .badge-bancos { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
    .badge-exacto { background: #dcfce7; color: #15803d; }
    .badge-faltante { background: #fee2e2; color: #b91c1c; }
    .badge-sobrante { background: #fef3c7; color: #b45309; }
    .badge-pendiente { background: #fef3c7; color: #b45309; }
    .badge-depositado { background: #e0f2fe; color: #0369a1; }
    .search-results-box {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        max-height: 200px;
        overflow-y: auto;
        margin-top: 6px;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .search-item {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .search-item:hover { background: #f1f5f9; }
    .search-item:last-child { border-bottom: none; }
</style>

<div class="cg-container">
    <!-- HEADER GENERAL -->
    <div class="cg-header">
        <div style="flex: 1; margin-right: 20px;">
            <h1 class="cg-title">Caja General & Flujo de Efectivo Diaria B2C</h1>
            <p class="cg-subtitle">Manejo de Cobros a Clientes Externos, Recaudación de Efectivo, Vueltos y Arqueo Ciego — Sucursal {{ $sucursalNombre }} ({{ $codigoSucursal }})</p>
        </div>
        <div class="cg-header-actions">
            <button class="btn-header-green" onclick="abrirModalIngresoCobro()">
                <i class="bi bi-plus-circle"></i>
                <span>Registrar Cobro</span>
            </button>
            <button class="btn-header-blue" onclick="abrirModalArqueo()">
                <i class="bi bi-safe2"></i>
                <span>Arqueo Ciego Diario</span>
            </button>
        </div>
    </div>

    <!-- TARJETAS METRICAS DIARIAS -->
    <div class="cg-metrics">
        <div class="metric-card success">
            <div class="metric-label">Efectivo Pendiente (Últimas 72 Horas)</div>
            <div class="metric-value" id="metric-efectivo-val" style="color: #10b981;">${{ number_format($totalEfectivoCalculado, 2) }}</div>
        </div>
        <div class="metric-card info">
            <div class="metric-label">Cobros Bancarios (Últimas 72h)</div>
            <div class="metric-value" style="color: #0284c7;">${{ number_format($totalBancosCalculado, 2) }}</div>
        </div>
        <div class="metric-card success">
            <div class="metric-label">Total Cobros Registrados (72h)</div>
            <div class="metric-value">{{ count($cobrosEfectivo) + count($cobrosBancos) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Estado de Cierre Hoy</div>
            <div class="metric-value" style="font-size: 1.2rem; margin-top: 12px;">
                @php
                    $primerArq = count($arqueos) > 0 ? (object) $arqueos[0] : null;
                    $fechaArq = $primerArq ? ($primerArq->fecha ?? $primerArq->Fecha ?? null) : null;
                @endphp
                @if($fechaArq && \Carbon\Carbon::parse($fechaArq)->isToday())
                    <span class="badge badge-exacto">Arqueado Hoy</span>
                @else
                    <span class="badge badge-pendiente">Pendiente Arqueo</span>
                @endif
            </div>
        </div>
    </div>

    <!-- PESTAÑAS DE NAVEGACIÓN UX/UI -->
    <div class="cg-tabs-container">
        <button class="cg-tab-btn active" id="btn-tab-efectivo" onclick="switchCajaTab('efectivo', this)">
            <i class="bi bi-cash-stack"></i>
            <span>Cobros en Efectivo (Pendientes)</span>
            <span class="tab-count-badge">{{ count($cobrosEfectivo) }}</span>
        </button>

        <button class="cg-tab-btn" id="btn-tab-arqueos" onclick="switchCajaTab('arqueos', this)">
            <i class="bi bi-safe"></i>
            <span>Historial de Arqueos</span>
            <span class="tab-count-badge">{{ count($arqueos) }}</span>
        </button>

        <button class="cg-tab-btn" id="btn-tab-arqueados" onclick="switchCajaTab('arqueados', this)">
            <i class="bi bi-check-circle"></i>
            <span>Cobros Arqueados & Depositados</span>
            <span class="tab-count-badge">{{ count($cobrosArqueados) }}</span>
        </button>

        <button class="cg-tab-btn" id="btn-tab-bancos" onclick="switchCajaTab('bancos', this)">
            <i class="bi bi-credit-card-2-front"></i>
            <span>Cobros Bancarios (Tarjetas / Transf.)</span>
            <span class="tab-count-badge">{{ count($cobrosBancos) }}</span>
        </button>
    </div>

    <!-- CONTENIDO DE PESTAÑA 1: COBROS EN EFECTIVO PENDIENTES DE ARQUEO -->
    <div id="pane-efectivo" class="cg-tab-pane">
        <div class="cg-card">
            <div class="cg-card-title">
                <div>
                    <span>Cobros de Cliente Externo — Efectivo Pendientes (Últimas 72 Horas)</span>
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 400; margin-top: 2px;">
                        Seleccione los cobros pendientes que ingresarán al nuevo Arqueo o Depósito a Bancos.
                    </div>
                </div>
                <div style="background: #f1f5f9; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; color: #0f172a;">
                    Seleccionado: <span id="selected-cobros-total-label" style="color: #10b981;">${{ number_format($totalEfectivoCalculado, 2) }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="check-all-cobros" checked onclick="toggleCheckAllCobros(this)" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th>Nro. Orden</th>
                            <th>Cliente</th>
                            <th>Equipo / Serie</th>
                            <th>Método Pago</th>
                            <th class="text-end">Monto Cobrado</th>
                            <th class="text-end">Monto Recibido</th>
                            <th class="text-end text-warning">Vuelto Dado</th>
                            <th class="text-end text-success">Neto Caja</th>
                            <th>Estado</th>
                            <th>Registrado Por</th>
                            <th>Fecha / Hora</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cobrosEfectivo as $cbr)
                            @php
                                $cObj = (object) $cbr;
                                $recibido = (float)($cObj->monto_recibido ?? $cObj->monto_cobrado ?? 0);
                                $vuelto = (float)($cObj->vuelto_dado ?? 0);
                                $neto = (float)($cObj->monto_neto_caja ?? ($cObj->monto_cobrado ?? 0));
                                $estadoArq = $cObj->estado_arqueo ?? 'Pendiente';
                            @endphp
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" class="cbx-cobro" value="{{ $cObj->id }}" data-neto="{{ $neto }}" checked onchange="actualizarSeleccionCobrosUI()" style="cursor: pointer; width: 16px; height: 16px;">
                                </td>
                                <td><strong>{{ $cObj->nro_orden }}</strong></td>
                                <td>{{ $cObj->cliente_nombre }}</td>
                                <td>{{ $cObj->equipo_info ?? 'N/A' }}</td>
                                <td><span class="badge badge-efectivo">{{ $cObj->metodo_pago }}</span></td>
                                <td class="text-end"><strong>${{ number_format((float)$cObj->monto_cobrado, 2) }}</strong></td>
                                <td class="text-end">${{ number_format($recibido, 2) }}</td>
                                <td class="text-end font-monospace text-warning"><strong>${{ number_format($vuelto, 2) }}</strong></td>
                                <td class="text-end font-monospace text-success"><strong>${{ number_format($neto, 2) }}</strong></td>
                                <td><span class="badge badge-pendiente">Pendiente</span></td>
                                <td>{{ $cObj->usuario_registro_nombre ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($cObj->fecha_cobro)->format('d/m/Y H:i') }}</td>
                                 <td style="white-space: nowrap;">
                                    <a href="{{ route('cajageneral.imprimir_recibo', ['id' => $cObj->id, 'tipo' => 'cliente']) }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #10b981; color: #ffffff; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700;">
                                        <i class="bi bi-person-badge me-1"></i>Recibo Cliente
                                    </a>
                                    <a href="{{ route('cajageneral.imprimir_recibo', ['id' => $cObj->id, 'tipo' => 'interno']) }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #2563eb; color: #ffffff; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px;">
                                        <i class="bi bi-file-earmark-text me-1"></i>Recibo Interno
                                    </a>

                                    @if(!empty($cObj->comprobante_url))
                                        @php $compUrl = str_starts_with($cObj->comprobante_url, 'http') ? $cObj->comprobante_url : asset($cObj->comprobante_url); @endphp
                                        <a href="{{ $compUrl }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px;" title="Ver Comprobante PDF / Imagen">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Ver Comprobante
                                        </a>
                                        <button type="button" onclick="abrirModalAdjuntarComprobanteCobro({{ $cObj->id }})" style="display: inline-block; padding: 4px 6px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.72rem; font-weight: 700; cursor: pointer; margin-left: 2px;" title="Cambiar / Re-subir Comprobante">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @else
                                        <button type="button" onclick="abrirModalAdjuntarComprobanteCobro({{ $cObj->id }})" style="display: inline-block; padding: 4px 8px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px; cursor: pointer;">
                                            <i class="bi bi-upload me-1"></i>Adjuntar Comprobante
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" style="text-align: center; color: #94a3b8; padding: 28px;">No hay cobros en efectivo pendientes de arqueo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CONTENIDO DE PESTAÑA 2: HISTORIAL DE ARQUEOS Y CIERRES DIARIOS -->
    <div id="pane-arqueos" class="cg-tab-pane" style="display: none;">
        <div class="cg-card">
            <div class="cg-card-title">
                <div>
                    <span>Historial de Arqueos y Cierres Diarios</span>
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 400; margin-top: 2px;">
                        Listado de arqueos ciegos realizados, resultados de cuadre y depósitos asociados.
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nro. Arqueo</th>
                            <th>Fecha</th>
                            <th>Monto Sistema (Neto Efectivo)</th>
                            <th>Monto Físico Contado</th>
                            <th>Diferencia</th>
                            <th>Resultado Arqueo</th>
                            <th>Estado Depósito</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arqueos as $arq)
                            @php
                                $arqObj = (object) $arq;
                                $diff = (float)($arqObj->diferencia ?? $arqObj->Diferencia ?? 0);
                                $tipoDiff = $arqObj->tipo_diferencia ?? $arqObj->TipoDiferencia ?? 'Cuadre Exacto';
                                $estado = $arqObj->estado ?? $arqObj->Estado ?? 'Pendiente Deposito';
                                $arqId = $arqObj->id ?? $arqObj->Id ?? 0;
                                $codSuc = $arqObj->codigo_sucursal ?? $arqObj->CodigoSucursal ?? $codigoSucursal;
                                $nroArqueo = $codSuc . '-ARQ-' . str_pad($arqId, 6, '0', STR_PAD_LEFT);
                            @endphp
                            <tr>
                                <td><strong style="color: #2563eb;">{{ $nroArqueo }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($arqObj->fecha ?? $arqObj->Fecha ?? now())->format('d/m/Y H:i') }}</td>
                                <td>${{ number_format((float)($arqObj->monto_sistema ?? $arqObj->MontoSistema ?? 0), 2) }}</td>
                                <td>${{ number_format((float)($arqObj->monto_fisico ?? $arqObj->MontoFisico ?? 0), 2) }}</td>
                                <td style="color: {{ $diff < 0 ? '#ef4444' : ($diff > 0 ? '#d97706' : '#10b981') }}; font-weight: 700;">
                                    ${{ number_format($diff, 2) }}
                                </td>
                                <td>
                                    @if($diff < 0)
                                        <span class="badge badge-faltante">Faltante</span>
                                    @elseif($diff > 0)
                                        <span class="badge badge-sobrante">Sobrante</span>
                                    @else
                                        <span class="badge badge-exacto">Cuadre Exacto</span>
                                    @endif
                                </td>
                                <td>
                                    @if($estado === 'Depositado')
                                        <span class="badge badge-depositado">Depositado</span>
                                    @else
                                        <span class="badge badge-pendiente">Pendiente Depósito</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('cajageneral.imprimir_arqueo', $arqId) }}" target="_blank" style="display: inline-block; padding: 6px 12px; background: #2563eb; color: #ffffff; border-radius: 6px; font-size: 0.75rem; text-decoration: none; font-weight: 700;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF Arqueo
                                    </a>

                                    @if($estado !== 'Depositado')
                                        <button class="btn-action" style="padding: 6px 12px; font-size: 0.75rem; background: #0284c7; color: #ffffff; border: none; border-radius: 6px; margin-left: 4px; cursor: pointer;" onclick="abrirModalDeposito({{ $arqId }})">
                                            <i class="bi bi-upload me-1"></i>Adjuntar Depósito
                                        </button>
                                    @endif

                                    @if(!empty($arqObj->comprobante_deposito_url ?? $arqObj->ComprobanteDepositoUrl ?? null))
                                        <a href="{{ $arqObj->comprobante_deposito_url ?? $arqObj->ComprobanteDepositoUrl }}" target="_blank" style="display: inline-block; margin-left: 4px; padding: 6px 12px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-size: 0.75rem; text-decoration: none; font-weight: 700;">
                                            <i class="bi bi-paperclip me-1"></i>Ver Comprobante
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: #94a3b8; padding: 28px;">No hay registros de arqueos anteriores.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CONTENIDO DE PESTAÑA 3: COBROS ARQUEADOS Y DEPOSITADOS -->
    <div id="pane-arqueados" class="cg-tab-pane" style="display: none;">
        <div class="cg-card">
            <div class="cg-card-title">
                <div>
                    <span>Historial de Cobros Arqueados y Depositados</span>
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 400; margin-top: 2px;">
                        Registro de cobros en efectivo ya incluidos en un Arqueo o Depósito Bancario.
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nro. Arqueo</th>
                            <th>Nro. Orden</th>
                            <th>Cliente</th>
                            <th>Equipo / Serie</th>
                            <th>Método Pago</th>
                            <th class="text-end">Monto Cobrado</th>
                            <th class="text-end text-warning">Vuelto Dado</th>
                            <th class="text-end text-success">Neto Caja</th>
                            <th>Estado Arqueo</th>
                            <th>Registrado Por</th>
                            <th>Fecha / Hora</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cobrosArqueados as $cbr)
                            @php
                                $cObj = (object) $cbr;
                                $recibido = (float)($cObj->monto_recibido ?? $cObj->monto_cobrado ?? 0);
                                $vuelto = (float)($cObj->vuelto_dado ?? 0);
                                $neto = (float)($cObj->monto_neto_caja ?? ($cObj->monto_cobrado ?? 0));
                                $estadoArq = $cObj->estado_arqueo ?? 'Arqueado';
                                $arqId = $cObj->arqueo_id ?? 0;
                            @endphp
                            <tr>
                                <td>
                                    @if($arqId > 0)
                                        <a href="{{ route('cajageneral.imprimir_arqueo', $arqId) }}" target="_blank" style="font-weight: 700; color: #2563eb; text-decoration: none;">
                                            {{ $cObj->nro_arqueo }}
                                        </a>
                                    @else
                                        <span style="color: #64748b;">{{ $cObj->nro_arqueo }}</span>
                                    @endif
                                </td>
                                <td><strong>{{ $cObj->nro_orden }}</strong></td>
                                <td>{{ $cObj->cliente_nombre }}</td>
                                <td>{{ $cObj->equipo_info ?? 'N/A' }}</td>
                                <td><span class="badge badge-efectivo">{{ $cObj->metodo_pago }}</span></td>
                                <td class="text-end"><strong>${{ number_format((float)$cObj->monto_cobrado, 2) }}</strong></td>
                                <td class="text-end font-monospace text-warning"><strong>${{ number_format($vuelto, 2) }}</strong></td>
                                <td class="text-end font-monospace text-success"><strong>${{ number_format($neto, 2) }}</strong></td>
                                <td>
                                    @if($estadoArq === 'Depositado')
                                        <span class="badge badge-depositado">Depositado</span>
                                    @else
                                        <span class="badge badge-exacto">Arqueado</span>
                                    @endif
                                </td>
                                <td>{{ $cObj->usuario_registro_nombre ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($cObj->fecha_cobro)->format('d/m/Y H:i') }}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('cajageneral.imprimir_recibo', ['id' => $cObj->id, 'tipo' => 'cliente']) }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #10b981; color: #ffffff; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700;">
                                        <i class="bi bi-person-badge me-1"></i>Recibo Cliente
                                    </a>
                                    <a href="{{ route('cajageneral.imprimir_recibo', ['id' => $cObj->id, 'tipo' => 'interno']) }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #2563eb; color: #ffffff; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px;">
                                        <i class="bi bi-file-earmark-text me-1"></i>Recibo Interno
                                    </a>

                                    @if(!empty($cObj->comprobante_url))
                                        @php $compUrl = str_starts_with($cObj->comprobante_url, 'http') ? $cObj->comprobante_url : asset($cObj->comprobante_url); @endphp
                                        <a href="{{ $compUrl }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px;" title="Ver Comprobante PDF / Imagen">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Ver Comprobante
                                        </a>
                                        <button type="button" onclick="abrirModalAdjuntarComprobanteCobro({{ $cObj->id }})" style="display: inline-block; padding: 4px 6px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.72rem; font-weight: 700; cursor: pointer; margin-left: 2px;" title="Cambiar / Re-subir Comprobante">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @else
                                        <button type="button" onclick="abrirModalAdjuntarComprobanteCobro({{ $cObj->id }})" style="display: inline-block; padding: 4px 8px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px; cursor: pointer;">
                                            <i class="bi bi-upload me-1"></i>Adjuntar Comprobante
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" style="text-align: center; color: #94a3b8; padding: 28px;">No hay cobros arqueados o depositados registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CONTENIDO DE PESTAÑA 4: COBROS BANCARIOS (TARJETAS / TRANSFERENCIAS) -->
    <div id="pane-bancos" class="cg-tab-pane" style="display: none;">
        <div class="cg-card">
            <div class="cg-card-title">
                <div>
                    <span>Cobros de Cliente Externo — Tarjetas y Transferencias (Ingresan a Bancos / Últimas 72 Horas)</span>
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 400; margin-top: 2px;">
                        Cobros mediante Datafast, Kushki, Transferencias Bancarias y Depósitos Directos.
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nro. Orden</th>
                            <th>Cliente</th>
                            <th>Equipo / Serie</th>
                            <th>Método de Pago</th>
                            <th>Destino Cuenta</th>
                            <th class="text-end">Monto Cobrado</th>
                            <th>Registrado Por</th>
                            <th>Fecha / Hora</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cobrosBancos as $cbr)
                            @php $cObj = (object) $cbr; @endphp
                            <tr>
                                <td><strong>{{ $cObj->nro_orden }}</strong></td>
                                <td>{{ $cObj->cliente_nombre }}</td>
                                <td>{{ $cObj->equipo_info ?? 'N/A' }}</td>
                                <td><span class="badge badge-bancos">{{ $cObj->metodo_pago }}</span></td>
                                <td><strong>Bancos</strong></td>
                                <td class="text-end"><strong style="color: #0284c7;">${{ number_format((float)$cObj->monto_cobrado, 2) }}</strong></td>
                                <td>{{ $cObj->usuario_nombre }}</td>
                                <td>{{ \Carbon\Carbon::parse($cObj->fecha_cobro)->format('d/m/Y H:i') }}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('cajageneral.imprimir_recibo', ['id' => $cObj->id, 'tipo' => 'cliente']) }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #10b981; color: #ffffff; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700;">
                                        <i class="bi bi-person-badge me-1"></i>Recibo Cliente
                                    </a>
                                    <a href="{{ route('cajageneral.imprimir_recibo', ['id' => $cObj->id, 'tipo' => 'interno']) }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #2563eb; color: #ffffff; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px;">
                                        <i class="bi bi-file-earmark-text me-1"></i>Recibo Interno
                                    </a>

                                    @if(!empty($cObj->comprobante_url))
                                        @php $compUrl = str_starts_with($cObj->comprobante_url, 'http') ? $cObj->comprobante_url : asset($cObj->comprobante_url); @endphp
                                        <a href="{{ $compUrl }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px;" title="Ver Comprobante PDF / Imagen">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Ver Comprobante
                                        </a>
                                        <button type="button" onclick="abrirModalAdjuntarComprobanteCobro({{ $cObj->id }})" style="display: inline-block; padding: 4px 6px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.72rem; font-weight: 700; cursor: pointer; margin-left: 2px;" title="Cambiar / Re-subir Comprobante">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @else
                                        <button type="button" onclick="abrirModalAdjuntarComprobanteCobro({{ $cObj->id }})" style="display: inline-block; padding: 4px 8px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 6px; font-size: 0.72rem; text-decoration: none; font-weight: 700; margin-left: 4px; cursor: pointer;">
                                            <i class="bi bi-upload me-1"></i>Adjuntar Comprobante
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; color: #94a3b8; padding: 28px;">No hay cobros bancarios o por tarjeta registrados en las últimas 72 horas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let ordenSeleccionada = null;

    function switchCajaTab(tabName, btnEl) {
        document.querySelectorAll('.cg-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.cg-tab-pane').forEach(pane => pane.style.display = 'none');

        if (btnEl) btnEl.classList.add('active');
        const targetPane = document.getElementById('pane-' + tabName);
        if (targetPane) targetPane.style.display = 'block';
    }

    const CUENTAS_BANCARIAS_OPCIONES = [
        { value: 'Efectivo', label: 'Efectivo (Caja General - Recepción)' },
        { value: 'Transferencia - Banco Pichincha (Cta. Cte. 2100072390)', label: 'Transferencia - Banco Pichincha (Cta. Cte. 2100072390)' },
        { value: 'Transferencia - Banco Bolivariano (Cta. Cte. 5005094867)', label: 'Transferencia - Banco Bolivariano (Cta. Cte. 5005094867)' },
        { value: 'Transferencia - Banco Guayaquil (Cta. Cte. 0043504851)', label: 'Transferencia - Banco Guayaquil (Cta. Cte. 0043504851)' },
        { value: 'Depósito - Banco Pichincha (Cta. Cte. 2100072390)', label: 'Depósito - Banco Pichincha (Cta. Cte. 2100072390)' },
        { value: 'Depósito - Banco Bolivariano (Cta. Cte. 5005094867)', label: 'Depósito - Banco Bolivariano (Cta. Cte. 5005094867)' },
        { value: 'Depósito - Banco Guayaquil (Cta. Cte. 0043504851)', label: 'Depósito - Banco Guayaquil (Cta. Cte. 0043504851)' },
        { value: 'Tarjeta Datafast / Kushki', label: 'Tarjeta Datafast / Kushki (Bancos)' }
    ];

    let filasPagosModal = [];

    function generarSelectMetodoHTML(idAttr, selectedVal) {
        let opts = '';
        CUENTAS_BANCARIAS_OPCIONES.forEach(o => {
            const isSel = (o.value === selectedVal) ? 'selected' : '';
            opts += `<option value="${o.value}" ${isSel}>${o.label}</option>`;
        });
        return `<select id="${idAttr}" class="swal2-input style-pago-select" onchange="recalcularDesglosePagosUI()" style="margin: 0; width: 100%; font-size: 0.82rem; height: 38px; padding: 4px 8px;">${opts}</select>`;
    }

    let tipoCobroModal = 'orden';

    function cambiarTipoCobroModal(tipo) {
        tipoCobroModal = tipo;
        const btnOrden = document.getElementById('btn-tipo-orden');
        const btnDirecto = document.getElementById('btn-tipo-directo');
        const boxOrden = document.getElementById('container-tipo-orden');
        const boxDirecto = document.getElementById('container-tipo-directo');

        if (tipo === 'venta_directa') {
            if (btnOrden) { btnOrden.style.background = 'transparent'; btnOrden.style.color = '#64748b'; }
            if (btnDirecto) { btnDirecto.style.background = '#10b981'; btnDirecto.style.color = '#ffffff'; }
            if (boxOrden) boxOrden.style.display = 'none';
            if (boxDirecto) boxDirecto.style.display = 'block';
        } else {
            if (btnOrden) { btnOrden.style.background = '#2563eb'; btnOrden.style.color = '#ffffff'; }
            if (btnDirecto) { btnDirecto.style.background = 'transparent'; btnDirecto.style.color = '#64748b'; }
            if (boxOrden) boxOrden.style.display = 'block';
            if (boxDirecto) boxDirecto.style.display = 'none';
        }
    }

    function abrirModalIngresoCobro() {
        ordenSeleccionada = null;
        tipoCobroModal = 'orden';
        filasPagosModal = [
            { id: 1, metodo: 'Efectivo', monto: 0.00, ref: '' }
        ];

        Swal.fire({
            title: 'Registrar Cobro en Caja General',
            width: '750px',
            html: `
                <div style="text-align: left; font-size: 0.875rem; color: #0f172a;">
                    <!-- Selector de Tipo de Cobro -->
                    <div style="display: flex; gap: 10px; margin-bottom: 16px; background: #f1f5f9; padding: 5px; border-radius: 10px; border: 1.5px solid #cbd5e1;">
                        <button type="button" id="btn-tipo-orden" onclick="cambiarTipoCobroModal('orden')" style="flex: 1; padding: 9px 12px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; background: #2563eb; color: #ffffff; transition: all 0.2s;">
                            <i class="bi bi-file-earmark-text me-1"></i> Por Orden de Servicio
                        </button>
                        <button type="button" id="btn-tipo-directo" onclick="cambiarTipoCobroModal('venta_directa')" style="flex: 1; padding: 9px 12px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; background: transparent; color: #64748b; transition: all 0.2s;">
                            <i class="bi bi-cart-check me-1"></i> Venta Directa / Mostrador / Varios
                        </button>
                    </div>

                    <!-- CONTAINER 1: POR ORDEN DE SERVICIO -->
                    <div id="container-tipo-orden">
                        <div style="margin-bottom: 12px;">
                            <label style="font-weight: 700; color: #0f172a;">1. Buscar / Digitar Número de Orden de Trabajo:</label>
                            <div style="display: flex; gap: 8px; margin-top: 4px;">
                                <input type="text" id="swal-search-orden" class="swal2-input" placeholder="Ej. OT-1002 o Nombre de cliente..." style="margin: 0; flex: 1;">
                                <button type="button" class="btn-action" onclick="buscarOrdenAjax()">Buscar</button>
                            </div>
                            <div id="swal-results-box" class="search-results-box"></div>
                        </div>

                        <div id="swal-orden-info" style="display: none; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 14px;">
                            <div><strong>Orden:</strong> <span id="info-nro-orden"></span></div>
                            <div><strong>Cliente:</strong> <span id="info-cliente"></span></div>
                            <div><strong>Equipo:</strong> <span id="info-equipo"></span></div>
                        </div>

                        <div id="swal-orden-warning-previo" style="display: none; background: #fff7ed; color: #c2410c; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #fdba74; margin-bottom: 14px; font-size: 0.83rem;"></div>
                    </div>

                    <!-- CONTAINER 2: VENTA DIRECTA / MOSTRADOR / VARIOS -->
                    <div id="container-tipo-directo" style="display: none; background: #f8fafc; padding: 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; margin-bottom: 14px;">
                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 10px; font-size: 0.88rem; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px;">
                            <i class="bi bi-shop me-1" style="color: #10b981;"></i> Datos del Producto / Venta Directa (Sin Orden de Servicio)
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                            <div>
                                <label style="font-weight: 700; color: #334155; font-size: 0.78rem;">Cliente / Comprador:</label>
                                <input type="text" id="swal-vd-cliente" class="swal2-input" placeholder="Nombre del cliente..." value="Consumidor Final" style="margin-top: 4px; width: 100%; font-size: 0.85rem; height: 38px;">
                            </div>
                            <div>
                                <label style="font-weight: 700; color: #334155; font-size: 0.78rem;">Código de Producto / Repuesto (Opcional):</label>
                                <input type="text" id="swal-vd-codigo" class="swal2-input" placeholder="Ej. 1PSM1680..." style="margin-top: 4px; width: 100%; font-size: 0.85rem; height: 38px;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-weight: 700; color: #334155; font-size: 0.78rem;">Descripción del Producto / Ítem (Cargador, Laptop, etc.):</label>
                                <input type="text" id="swal-vd-descripcion" class="swal2-input" placeholder="Ej. Cargador Laptop 65W / Laptop Mostrador..." style="margin-top: 4px; width: 100%; font-size: 0.85rem; height: 38px;">
                            </div>
                            <div>
                                <label style="font-weight: 700; color: #334155; font-size: 0.78rem;">Serie del Equipo (Si aplica):</label>
                                <input type="text" id="swal-vd-serie" class="swal2-input" placeholder="SN: N/A..." style="margin-top: 4px; width: 100%; font-size: 0.85rem; height: 38px;">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Monto Total Cobrado -->
                    <div style="margin-bottom: 14px;">
                        <label style="font-weight: 700; color: #0f172a;">2. Monto Total a Cobrar ($):</label>
                        <input type="number" step="0.01" id="swal-monto-cobrado" class="swal2-input" placeholder="0.00" style="margin-top: 4px; width: 100%; font-size: 1.1rem; font-weight: 800; color: #2563eb;" oninput="onMontoTotalCobradoChange()">
                    </div>

                    <!-- 3. Desglose de Métodos de Pago / Cuentas Bancarias -->
                    <div style="margin-bottom: 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <label style="font-weight: 700; color: #0f172a; margin: 0;">3. Formas de Pago / Cuentas de Destino:</label>
                            <button type="button" onclick="agregarFilaPagoCobro()" style="background: #2563eb; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer;">
                                + Agregar Método de Pago (Pago Mixto)
                            </button>
                        </div>

                        <div id="swal-pagos-rows-container"></div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px solid #cbd5e1; font-size: 0.85rem;">
                            <span>Suma Desglose: <strong id="lbl-suma-desglose" style="color: #0f172a;">$0.00</strong></span>
                            <span>Diferencia por Asignar: <strong id="lbl-diferencia-desglose" style="color: #10b981;">$0.00</strong></span>
                        </div>
                    </div>

                    <!-- Detalle de Efectivo (Solo si se selecciona Efectivo) -->
                    <div id="container-efectivo-desglose" style="display: none; background: #ffffff; padding: 12px; border-radius: 8px; border: 1.5px solid #86efac; margin-bottom: 14px;">
                        <div style="font-weight: 700; color: #15803d; margin-bottom: 8px; font-size: 0.8rem; text-transform: uppercase;">
                            Control de Efectivo (Ingresa a Caja General en Recepción)
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-weight: 700; color: #2563eb; font-size: 0.78rem;">Monto Recibido ($):</label>
                                <input type="number" step="0.01" id="swal-monto-recibido" class="swal2-input" placeholder="0.00" style="margin-top: 4px; width: 100%; font-size: 0.85rem;" oninput="actualizarCalculosCobroForm()">
                            </div>
                            <div>
                                <label style="font-weight: 700; color: #d97706; font-size: 0.78rem;">Vuelto Dado ($):</label>
                                <input type="text" id="swal-vuelto-dado" class="swal2-input" value="0.00" readonly style="margin-top: 4px; width: 100%; font-weight: 700; color: #d97706; background: #fff; font-size: 0.85rem;">
                            </div>
                            <div>
                                <label style="font-weight: 700; color: #16a34a; font-size: 0.78rem;">Sobrante / Propina ($):</label>
                                <input type="number" step="0.01" id="swal-sobrante" class="swal2-input" value="0.00" style="margin-top: 4px; width: 100%; font-weight: 700; font-size: 0.85rem;" oninput="actualizarCalculosCobroForm()">
                            </div>
                        </div>
                        <div style="margin-top: 8px; text-align: right;">
                            <span style="font-size: 0.85rem; color: #64748b;">Neto Efectivo Caja: <strong id="swal-neto-label" style="color: #16a34a; font-size: 1.05rem;">$0.00</strong></span>
                        </div>
                    </div>

                    <div>
                        <label style="font-weight: 700; color: #0f172a;">4. Observaciones General / Notas (Opcional):</label>
                        <textarea id="swal-cobro-obs" class="swal2-textarea" placeholder="Observaciones o notas adicionales..." style="margin-top: 4px; height: 50px; width: 100%;"></textarea>
                    </div>
                </div>
            `,
            didOpen: () => {
                renderFilasPagoCobroUI();
            },
            showCancelButton: true,
            confirmButtonText: 'Guardar Cobro',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            preConfirm: () => {
                const montoTotalCobrado = parseFloat(document.getElementById('swal-monto-cobrado').value || 0);
                const obsGeneral = document.getElementById('swal-cobro-obs').value;

                let nroOrden = '';
                let ordenId = null;
                let clienteNombre = 'Consumidor Final';
                let equipoInfo = '';
                let codigoProducto = null;
                let serieProducto = null;

                if (tipoCobroModal === 'venta_directa') {
                    clienteNombre = document.getElementById('swal-vd-cliente').value.trim() || 'Consumidor Final';
                    codigoProducto = document.getElementById('swal-vd-codigo').value.trim();
                    serieProducto = document.getElementById('swal-vd-serie').value.trim();
                    const descProd = document.getElementById('swal-vd-descripcion').value.trim() || 'Venta Directa Mostrador';

                    nroOrden = 'VENTA-' + Math.floor(Date.now() / 1000);
                    equipoInfo = (codigoProducto ? '[' + codigoProducto + '] ' : '') + descProd + (serieProducto ? ' (SN: ' + serieProducto + ')' : '');
                } else {
                    const searchNro = document.getElementById('swal-search-orden').value.trim();
                    nroOrden = ordenSeleccionada ? ordenSeleccionada.nro_orden : searchNro;
                    ordenId = ordenSeleccionada ? ordenSeleccionada.id : null;
                    clienteNombre = ordenSeleccionada ? ordenSeleccionada.cliente : 'Cliente Externo';
                    equipoInfo = ordenSeleccionada ? ordenSeleccionada.equipo : '';

                    if (!nroOrden) {
                        Swal.showValidationMessage('Debe digitar o seleccionar una orden de trabajo.');
                        return false;
                    }
                }

                if (isNaN(montoTotalCobrado) || montoTotalCobrado <= 0) {
                    Swal.showValidationMessage('Debe ingresar un monto total a cobrar válido mayor a $0.00.');
                    return false;
                }

                // Recopilar filas de pago
                let pagosPayload = [];
                let sumaDesglose = 0;
                let hayEfectivo = false;

                const container = document.getElementById('swal-pagos-rows-container');
                const rows = container.querySelectorAll('.pago-row-item');

                for (let r of rows) {
                    const selMetodo = r.querySelector('.row-metodo-select').value;
                    const valMonto = parseFloat(r.querySelector('.row-monto-input').value || 0);
                    const valRef = r.querySelector('.row-ref-input').value.trim();

                    if (valMonto <= 0) {
                        Swal.showValidationMessage('Cada método de pago desglosado debe tener un monto mayor a $0.00.');
                        return false;
                    }

                    sumaDesglose += valMonto;
                    let pObj = {
                        metodo_pago: selMetodo,
                        monto_cobrado: valMonto,
                        observaciones: valRef ? (valRef + (obsGeneral ? ' | ' + obsGeneral : '')) : obsGeneral
                    };

                    if (selMetodo.includes('Efectivo')) {
                        hayEfectivo = true;
                        const montoRecibido = parseFloat(document.getElementById('swal-monto-recibido').value || 0);
                        const vueltoDado = parseFloat(document.getElementById('swal-vuelto-dado').value || 0);
                        const sobrante = parseFloat(document.getElementById('swal-sobrante').value || 0);

                        pObj.monto_recibido = montoRecibido > 0 ? montoRecibido : valMonto;
                        pObj.vuelto_dado = vueltoDado;
                        pObj.sobrante = sobrante;
                    } else {
                        pObj.monto_recibido = valMonto;
                        pObj.vuelto_dado = 0.00;
                        pObj.sobrante = 0.00;
                    }

                    pagosPayload.push(pObj);
                }

                const diff = Math.abs(montoTotalCobrado - sumaDesglose);
                if (diff > 0.01) {
                    Swal.showValidationMessage(`La suma del desglose ($${sumaDesglose.toFixed(2)}) debe coincidir exactamente con el total a cobrar ($${montoTotalCobrado.toFixed(2)}).`);
                    return false;
                }

                return {
                    tipo_cobro: tipoCobroModal,
                    orden_id: ordenId,
                    nro_orden: nroOrden,
                    codigo_producto: codigoProducto,
                    serie_producto: serieProducto,
                    cliente_nombre: clienteNombre,
                    equipo_info: equipoInfo,
                    monto_cobrado: montoTotalCobrado,
                    observaciones: obsGeneral,
                    pagos: pagosPayload
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarCobro(result.value);
            }
        });
    }

    function renderFilasPagoCobroUI() {
        const container = document.getElementById('swal-pagos-rows-container');
        if (!container) return;

        let html = '';
        filasPagosModal.forEach((f, idx) => {
            const btnRemove = (filasPagosModal.length > 1)
                ? `<button type="button" onclick="eliminarFilaPagoCobro(${idx})" style="background: #ef4444; color: #fff; border: none; border-radius: 6px; padding: 4px 8px; font-weight: 700; cursor: pointer;">X</button>`
                : '';

            const showFile = !f.metodo.includes('Efectivo');
            const fileBoxStyle = showFile ? 'display: flex;' : 'display: none;';

            html += `
                <div class="pago-row-item" style="border: 1px solid #e2e8f0; background: #ffffff; padding: 10px; border-radius: 8px; margin-bottom: 8px;">
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 8px; align-items: center;">
                        <div>
                            ${generarSelectMetodoHTML('metodo-row-' + idx, f.metodo)}
                        </div>
                        <div>
                            <input type="number" step="0.01" class="swal2-input row-monto-input" placeholder="Monto $" value="${f.monto > 0 ? f.monto.toFixed(2) : ''}" style="margin: 0; width: 100%; font-size: 0.85rem; height: 38px; font-weight: 700;" oninput="recalcularDesglosePagosUI()">
                        </div>
                        <div>
                            <input type="text" class="swal2-input row-ref-input" placeholder="Nro boucher/ref" value="${f.ref}" style="margin: 0; width: 100%; font-size: 0.8rem; height: 38px;">
                        </div>
                        <div>
                            ${btnRemove}
                        </div>
                    </div>

                    <div class="row-file-container" style="${fileBoxStyle} margin-top: 8px; padding-top: 6px; border-top: 1px dashed #cbd5e1; align-items: center; justify-content: space-between; font-size: 0.78rem;">
                        <span style="font-weight: 700; color: #0284c7; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="bi bi-paperclip"></i> Adjuntar Comprobante (JPG, PNG, PDF):
                        </span>
                        <input type="file" class="row-file-input" accept="image/*,.pdf" style="font-size: 0.75rem; max-width: 270px; padding: 2px;">
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Re-bind selects
        const rows = container.querySelectorAll('.pago-row-item');
        rows.forEach((r, idx) => {
            const sel = r.querySelector('select');
            if (sel) {
                sel.classList.add('row-metodo-select');
                sel.onchange = () => {
                    const isEf = sel.value.includes('Efectivo');
                    filasPagosModal[idx].metodo = sel.value;
                    const fileBox = r.querySelector('.row-file-container');
                    if (fileBox) fileBox.style.display = isEf ? 'none' : 'flex';
                    recalcularDesglosePagosUI();
                };
            }
        });

        recalcularDesglosePagosUI();
    }

    function onMontoTotalCobradoChange() {
        const total = parseFloat(document.getElementById('swal-monto-cobrado').value || 0);
        if (filasPagosModal.length === 1) {
            filasPagosModal[0].monto = total;
            const inputMonto = document.querySelector('.row-monto-input');
            if (inputMonto) inputMonto.value = total > 0 ? total.toFixed(2) : '';
        }
        recalcularDesglosePagosUI();
    }

    function agregarFilaPagoCobro() {
        const total = parseFloat(document.getElementById('swal-monto-cobrado').value || 0);
        let sumaActual = 0;
        document.querySelectorAll('.row-monto-input').forEach(inp => {
            sumaActual += parseFloat(inp.value || 0);
        });

        let saldoRestante = Math.max(0, total - sumaActual);

        filasPagosModal.push({
            id: Date.now(),
            metodo: 'Transferencia - Banco Pichincha (Cta. Cte. 2100072390)',
            monto: saldoRestante,
            ref: ''
        });

        renderFilasPagoCobroUI();
    }

    function eliminarFilaPagoCobro(index) {
        if (filasPagosModal.length <= 1) return;
        filasPagosModal.splice(index, 1);
        renderFilasPagoCobroUI();
    }

    function recalcularDesglosePagosUI() {
        const totalCobrado = parseFloat(document.getElementById('swal-monto-cobrado').value || 0);
        let suma = 0;
        let hayEfectivo = false;
        let efectivoMonto = 0;

        const container = document.getElementById('swal-pagos-rows-container');
        if (container) {
            const rows = container.querySelectorAll('.pago-row-item');
            rows.forEach(r => {
                const met = r.querySelector('.row-metodo-select').value;
                const m = parseFloat(r.querySelector('.row-monto-input').value || 0);
                suma += m;
                if (met.includes('Efectivo')) {
                    hayEfectivo = true;
                    efectivoMonto += m;
                }
            });
        }

        const diff = totalCobrado - suma;
        const lblSuma = document.getElementById('lbl-suma-desglose');
        const lblDiff = document.getElementById('lbl-diferencia-desglose');

        if (lblSuma) lblSuma.innerText = '$' + suma.toFixed(2);
        if (lblDiff) {
            lblDiff.innerText = '$' + Math.abs(diff).toFixed(2) + (diff < 0 ? ' (Exceso)' : '');
            lblDiff.style.color = (Math.abs(diff) <= 0.01) ? '#10b981' : '#ef4444';
        }

        // Toggle efectivo section
        const desgloseEfectivoBox = document.getElementById('container-efectivo-desglose');
        if (desgloseEfectivoBox) {
            if (hayEfectivo) {
                desgloseEfectivoBox.style.display = 'block';
                const inpRecibido = document.getElementById('swal-monto-recibido');
                if (inpRecibido && (!inpRecibido.value || parseFloat(inpRecibido.value) === 0)) {
                    inpRecibido.value = efectivoMonto > 0 ? efectivoMonto.toFixed(2) : '';
                }
                actualizarCalculosCobroForm(efectivoMonto);
            } else {
                desgloseEfectivoBox.style.display = 'none';
            }
        }
    }

    function actualizarCalculosCobroForm(efectivoMontoParam) {
        let efectivoMonto = 0;
        if (typeof efectivoMontoParam === 'number' && efectivoMontoParam > 0) {
            efectivoMonto = efectivoMontoParam;
        } else {
            const container = document.getElementById('swal-pagos-rows-container');
            if (container) {
                const rows = container.querySelectorAll('.pago-row-item');
                rows.forEach(r => {
                    const sel = r.querySelector('.row-metodo-select');
                    const met = sel ? sel.value : 'Efectivo';
                    const m = parseFloat(r.querySelector('.row-monto-input')?.value || 0);
                    if (met.includes('Efectivo')) {
                        efectivoMonto += m;
                    }
                });
            }
        }

        let recibido = parseFloat(document.getElementById('swal-monto-recibido')?.value || 0);
        const sobrante = parseFloat(document.getElementById('swal-sobrante')?.value || 0);
        const faltante = parseFloat(document.getElementById('swal-faltante')?.value || 0);

        let vuelto = 0.00;
        if (recibido > efectivoMonto && efectivoMonto > 0) {
            vuelto = recibido - efectivoMonto;
        }
        const inpVuelto = document.getElementById('swal-vuelto-dado');
        if (inpVuelto) inpVuelto.value = vuelto.toFixed(2);

        let neto = (recibido - vuelto) + sobrante - faltante;
        if (neto <= 0) {
            neto = efectivoMonto + sobrante - faltante;
        }

        const lblNeto = document.getElementById('swal-neto-label');
        if (lblNeto) lblNeto.innerText = '$' + neto.toFixed(2);
    }

    function buscarProductoVentaDirectaAjax() {
        const q = document.getElementById('swal-vd-codigo').value.trim();
        const box = document.getElementById('swal-prod-results-box');
        if (!q || q.length < 1) {
            box.style.display = 'none';
            return;
        }

        fetch("{{ route('cajageneral.buscar_producto') }}?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(res => {
                if (res.ok && res.productos && res.productos.length > 0) {
                    let html = '';
                    res.productos.forEach(p => {
                        html += `
                            <div class="search-item" onclick='seleccionarProductoVentaDirecta(${JSON.stringify(p)})'>
                                <strong style="color:#b45309;">${p.codigo}</strong> — ${p.nombre}<br>
                                <span style="font-size: 0.78rem; color: #64748b;">${p.tipo} ${p.costo > 0 ? '| Costo Ref: $' + p.costo.toFixed(2) : ''}</span>
                            </div>
                        `;
                    });
                    box.innerHTML = html;
                    box.style.display = 'block';
                } else {
                    box.innerHTML = '<div style="padding: 10px; color: #94a3b8;">No se encontraron productos con ese criterio.</div>';
                    box.style.display = 'block';
                }
            })
            .catch(() => {
                box.style.display = 'none';
            });
    }

    function seleccionarProductoVentaDirecta(p) {
        document.getElementById('swal-vd-codigo').value = p.codigo;
        document.getElementById('swal-vd-descripcion').value = p.nombre;
        document.getElementById('swal-prod-results-box').style.display = 'none';

        if (p.costo && p.costo > 0) {
            document.getElementById('swal-monto-cobrado').value = p.costo.toFixed(2);
            onMontoTotalCobradoChange();
        }
    }

    function buscarOrdenAjax() {
        const q = document.getElementById('swal-search-orden').value.trim();
        const box = document.getElementById('swal-results-box');
        if (!q || q.length < 1) {
            box.style.display = 'none';
            return;
        }

        fetch("{{ route('cajageneral.buscar_orden') }}?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(res => {
                if (res.ok && res.ordenes && res.ordenes.length > 0) {
                    let html = '';
                    res.ordenes.forEach(o => {
                        const badgePrevio = o.tiene_cobros_previos
                            ? `<span style="background: #ffedd5; color: #c2410c; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 0.72rem; margin-left: 6px;">Cobrado Previo: $${o.total_cobrado_previo.toFixed(2)} (${o.cobros_previos_count})</span>`
                            : '';

                        html += `
                            <div class="search-item" onclick='seleccionarOrden(${JSON.stringify(o)})'>
                                <strong>${o.nro_orden}</strong> — ${o.cliente} ${badgePrevio}<br>
                                <span style="font-size: 0.78rem; color: #64748b;">${o.equipo} | Sugerido: $${o.total_sugerido.toFixed(2)}</span>
                            </div>
                        `;
                    });
                    box.innerHTML = html;
                    box.style.display = 'block';
                } else {
                    box.innerHTML = '<div style="padding: 10px; color: #94a3b8;">No se encontraron órdenes externas con ese criterio.</div>';
                    box.style.display = 'block';
                }
            })
            .catch(() => {
                box.style.display = 'none';
            });
    }

    function seleccionarOrden(ord) {
        ordenSeleccionada = ord;
        document.getElementById('swal-search-orden').value = ord.nro_orden;
        document.getElementById('swal-results-box').style.display = 'none';

        document.getElementById('info-nro-orden').innerText = ord.nro_orden;
        document.getElementById('info-cliente').innerText = ord.cliente;
        document.getElementById('info-equipo').innerText = ord.equipo;
        document.getElementById('swal-orden-info').style.display = 'block';

        const warnBox = document.getElementById('swal-orden-warning-previo');
        if (warnBox) {
            if (ord.tiene_cobros_previos) {
                warnBox.style.display = 'block';
                warnBox.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>ADVERTENCIA DE COBRO PREVIO / SEGUNDO COBRO:</strong><br>
                    Esta orden ya registra <strong>${ord.cobros_previos_count} cobro(s) anterior(es)</strong> por un total acumulado de <strong>$${ord.total_cobrado_previo.toFixed(2)}</strong>. Este registro ingresará como un nuevo cobro adicional.
                `;
            } else {
                warnBox.style.display = 'none';
            }
        }

        if (ord.total_sugerido && ord.total_sugerido > 0) {
            document.getElementById('swal-monto-cobrado').value = ord.total_sugerido.toFixed(2);
            onMontoTotalCobradoChange();
        }
    }

    function enviarCobro(payload) {
        Swal.fire({
            title: 'Procesando cobro...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('nro_orden', payload.nro_orden);
        if (payload.orden_id) formData.append('orden_id', payload.orden_id);
        formData.append('cliente_nombre', payload.cliente_nombre);
        formData.append('equipo_info', payload.equipo_info);
        formData.append('monto_cobrado', payload.monto_cobrado);
        if (payload.observaciones) formData.append('observaciones', payload.observaciones);

        const container = document.getElementById('swal-pagos-rows-container');
        const rows = container ? container.querySelectorAll('.pago-row-item') : [];

        payload.pagos.forEach((p, idx) => {
            formData.append(`pagos[${idx}][metodo_pago]`, p.metodo_pago);
            formData.append(`pagos[${idx}][monto_cobrado]`, p.monto_cobrado);
            formData.append(`pagos[${idx}][monto_recibido]`, p.monto_recibido);
            formData.append(`pagos[${idx}][vuelto_dado]`, p.vuelto_dado);
            formData.append(`pagos[${idx}][sobrante]`, p.sobrante);
            if (p.observaciones) formData.append(`pagos[${idx}][observaciones]`, p.observaciones);

            if (rows[idx]) {
                const fileInput = rows[idx].querySelector('.row-file-input');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append(`comprobante_file_${idx}`, fileInput.files[0]);
                }
            }
        });

        fetch("{{ route('cajageneral.guardar_cobro') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                const primerId = (res.cobro_ids && res.cobro_ids.length > 0) ? res.cobro_ids[0] : null;
                Swal.fire({
                    title: '¡Cobro Registrado con Éxito!',
                    text: res.mensaje,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-printer me-1"></i> Imprimir Recibo PDF',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#2563eb'
                }).then((r) => {
                    if (r.isConfirmed && primerId) {
                        window.open("{{ url('/contabilidad/caja-general/recibo') }}/" + primerId, '_blank');
                    }
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.error || res.mensaje || 'No se pudo guardar el cobro.', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión al guardar el cobro: ' + err.message, 'error'));
    }

    function getSelectedCobrosDetalle() {
        const checkboxes = document.querySelectorAll('.cbx-cobro:checked');
        let ids = [];
        let items = [];
        let total = 0;
        checkboxes.forEach(cb => {
            const id = parseInt(cb.value);
            const row = cb.closest('tr');
            const nroOrden = row ? (row.children[1]?.innerText?.trim() || '') : '';
            const cliente = row ? (row.children[2]?.innerText?.trim() || '') : '';
            const equipo = row ? (row.children[3]?.innerText?.trim() || '') : '';
            const neto = parseFloat(cb.dataset.neto || 0);

            ids.push(id);
            items.push({ id, nroOrden, cliente, equipo, neto });
            total += neto;
        });
        return { ids: ids, items: items, total: total };
    }

    function actualizarSeleccionCobrosUI() {
        const data = getSelectedCobrosDetalle();
        const label = document.getElementById('selected-cobros-total-label');
        if (label) {
            label.innerText = '$' + data.total.toFixed(2) + ' (' + data.ids.length + ' seleccionados)';
        }
    }

    function toggleCheckAllCobros(master) {
        const checkboxes = document.querySelectorAll('.cbx-cobro');
        checkboxes.forEach(cb => cb.checked = master.checked);
        actualizarSeleccionCobrosUI();
    }

    function abrirModalArqueo() {
        const selectionData = getSelectedCobrosDetalle();
        const montoSistema = selectionData.total;
        const cobroIds = selectionData.ids;
        const items = selectionData.items;

        if (cobroIds.length === 0) {
            Swal.fire('Atención', 'Debe seleccionar al menos un cobro en la tabla para realizar el arqueo.', 'warning');
            return;
        }

        let itemsHtml = '';
        items.forEach(it => {
            const eqText = (it.equipo && it.equipo !== 'N/A' && it.equipo.trim() !== '') ? `<span style="color: #64748b; font-size: 0.75rem;"> (${it.equipo})</span>` : '';
            itemsHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 6px 10px; font-weight: 700; color: #2563eb;">${it.nroOrden}</td>
                    <td style="padding: 6px 10px; color: #334155;">${it.cliente}${eqText}</td>
                    <td style="padding: 6px 10px; text-align: right; font-weight: 700; color: #16a34a;">$${it.neto.toFixed(2)}</td>
                </tr>
            `;
        });

        Swal.fire({
            title: 'Arqueo Ciego de Caja General',
            width: '640px',
            padding: '20px',
            html: `
                <div style="text-align: left; font-size: 0.875rem; color: #0f172a; box-sizing: border-box; width: 100%;">
                    <p style="margin: 0 0 10px 0;"><strong>Sucursal:</strong> {{ $sucursalNombre }} ({{ $codigoSucursal }})</p>

                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 12px; border-radius: 10px; margin-bottom: 16px; box-sizing: border-box; width: 100%;">
                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 6px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em;">
                            Órdenes Seleccionadas para el Arqueo (${cobroIds.length}):
                        </div>
                        <div style="max-height: 120px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 8px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                <thead>
                                    <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                                        <th style="padding: 6px 10px; font-weight: 700;">Orden</th>
                                        <th style="padding: 6px 10px; font-weight: 700;">Cliente / Equipo</th>
                                        <th style="padding: 6px 10px; text-align: right; font-weight: 700;">Neto ($)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: right; font-size: 1rem; color: #0f172a;">
                            <strong>Monto Sistema Calculado:</strong> <span style="color: #10b981; font-weight: 800; font-size: 1.1rem; margin-left: 4px;">$${montoSistema.toFixed(2)}</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px; box-sizing: border-box; width: 100%;">
                        <label style="font-weight: 700; color: #0f172a; display: block;">Monto Físico Contado en Caja ($):</label>
                        <input type="number" step="0.01" id="swal-monto-fisico" placeholder="0.00" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; font-weight: 700; outline: none; transition: border 0.2s;">
                    </div>

                    <div style="margin-bottom: 14px; box-sizing: border-box; width: 100%;">
                        <label style="font-weight: 700; color: #0f172a; display: block;">Observaciones / Justificación (Opcional):</label>
                        <textarea id="swal-obs" placeholder="Notas sobre el cierre o diferencia..." style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; outline: none; height: 60px; resize: vertical;"></textarea>
                    </div>

                    <div style="background: #f0f9ff; padding: 14px; border-radius: 10px; border: 1.5px solid #bae6fd; box-sizing: border-box; width: 100%;">
                        <div style="font-weight: 700; color: #0284c7; margin-bottom: 8px; font-size: 0.875rem;">
                            Depósito Bancario Inmediato (Opcional)
                        </div>
                        <div style="margin-bottom: 10px; box-sizing: border-box; width: 100%;">
                            <label style="font-weight: 600; font-size: 0.8rem; color: #334155; display: block;">Nro. Comprobante / Papeleta Depósito:</label>
                            <input type="text" id="swal-nro-dep-arqueo" placeholder="Ej. DEP-987654" style="width: 100%; box-sizing: border-box; margin-top: 4px; padding: 8px 12px; border: 1px solid #93c5fd; border-radius: 6px; font-size: 0.875rem; outline: none; background: #ffffff;">
                        </div>
                        <div style="box-sizing: border-box; width: 100%;">
                            <label style="font-weight: 600; font-size: 0.8rem; color: #334155; display: block;">Subir Comprobante (PDF o Imagen JPG/PNG):</label>
                            <input type="file" id="swal-file-dep-arqueo" accept=".pdf,.png,.jpg,.jpeg,.webp" style="width: 100%; box-sizing: border-box; margin-top: 4px; padding: 6px 10px; border: 1.5px dashed #0284c7; border-radius: 6px; font-size: 0.82rem; background: #ffffff; color: #0f172a; cursor: pointer;">
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Arqueo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            preConfirm: () => {
                const montoFisico = document.getElementById('swal-monto-fisico').value;
                const obs = document.getElementById('swal-obs').value;
                const nroDep = document.getElementById('swal-nro-dep-arqueo').value;
                const fileEl = document.getElementById('swal-file-dep-arqueo');
                const fileDep = (fileEl && fileEl.files.length > 0) ? fileEl.files[0] : null;

                if (!montoFisico || isNaN(montoFisico)) {
                    Swal.showValidationMessage('Debe ingresar un monto físico válido.');
                    return false;
                }
                return {
                    montoFisico: parseFloat(montoFisico),
                    obs: obs,
                    nroDep: nroDep ? nroDep.trim() : '',
                    fileDep: fileDep
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarArqueo(
                    montoSistema,
                    result.value.montoFisico,
                    result.value.obs,
                    cobroIds,
                    result.value.nroDep,
                    result.value.fileDep
                );
            }
        });
    }

    function enviarArqueo(montoSistema, montoFisico, observaciones, cobroIds, nroDeposito, fileDeposito) {
        let formData = new FormData();
        formData.append('sucursal_id', {{ $sucursalId }});
        formData.append('codigo_sucursal', "{{ $codigoSucursal }}");
        formData.append('monto_sistema', montoSistema);
        formData.append('monto_fisico', montoFisico);
        formData.append('observaciones', observaciones || '');
        if (nroDeposito) formData.append('nro_comprobante_deposito', nroDeposito);
        if (fileDeposito) formData.append('comprobante_file', fileDeposito);
        formData.append('cobro_ids', JSON.stringify(cobroIds || []));

        fetch("{{ route('cajageneral.guardar_arqueo') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                Swal.fire({
                    title: 'Arqueo Registrado',
                    text: res.mensaje,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Imprimir PDF Arqueo',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#2563eb'
                }).then((r) => {
                    if (r.isConfirmed && res.arqueo_id) {
                        window.open("{{ url('/contabilidad/caja-general/arqueo') }}/" + res.arqueo_id + "/imprimir", '_blank');
                    }
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.error || 'No se pudo guardar el arqueo', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión', 'error'));
    }

    function abrirModalDeposito(arqueoId) {
        Swal.fire({
            title: 'Registrar Depósito Bancario',
            width: '500px',
            padding: '20px',
            html: `
                <div style="text-align: left; font-size: 0.875rem; color: #0f172a; box-sizing: border-box; width: 100%;">
                    <div style="margin-top: 8px; box-sizing: border-box; width: 100%;">
                        <label style="font-weight: 700; color: #0f172a; display: block;">Nro. Comprobante de Depósito / Papeleta:</label>
                        <input type="text" id="swal-nro-dep" placeholder="Ej: DEP-987654" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none;">
                    </div>
                    <div style="margin-top: 14px; box-sizing: border-box; width: 100%;">
                        <label style="font-weight: 700; color: #0f172a; display: block;">Subir Comprobante (PDF o Imagen JPG/PNG):</label>
                        <input type="file" id="swal-file-dep" accept=".pdf,.png,.jpg,.jpeg,.webp" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 8px 10px; border: 1.5px dashed #0284c7; border-radius: 8px; font-size: 0.82rem; background: #ffffff; color: #0f172a; cursor: pointer;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar Depósito',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            preConfirm: () => {
                const nroDep = document.getElementById('swal-nro-dep').value;
                const fileEl = document.getElementById('swal-file-dep');
                const fileDep = (fileEl && fileEl.files.length > 0) ? fileEl.files[0] : null;

                if ((!nroDep || nroDep.trim() === '') && !fileDep) {
                    Swal.showValidationMessage('Ingrese el número de comprobante o adjunte un archivo.');
                    return false;
                }
                return { nroDep: nroDep ? nroDep.trim() : '', fileDep: fileDep };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarDeposito(arqueoId, result.value.nroDep, result.value.fileDep);
            }
        });
    }

    function enviarDeposito(arqueoId, nroDeposito, fileDeposito) {
        let formData = new FormData();
        formData.append('arqueo_id', arqueoId);
        if (nroDeposito) formData.append('nro_comprobante_deposito', nroDeposito);
        if (fileDeposito) formData.append('comprobante_file', fileDeposito);

        fetch("{{ route('cajageneral.subir_deposito') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                Swal.fire('Depósito Registrado', res.mensaje, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.error || 'No se pudo guardar el depósito', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión', 'error'));
    }

    function abrirModalAdjuntarComprobanteCobro(cobroId) {
        Swal.fire({
            title: 'Adjuntar / Subir Comprobante de Pago',
            width: '500px',
            padding: '20px',
            html: `
                <div style="text-align: left; font-size: 0.875rem; color: #0f172a; box-sizing: border-box; width: 100%;">
                    <div style="margin-bottom: 10px; background: #f0f9ff; padding: 10px 12px; border-radius: 8px; border: 1px solid #bae6fd; font-size: 0.82rem; color: #0369a1;">
                        <i class="bi bi-info-circle-fill me-1"></i> Selecciona el archivo PDF o imagen (JPG/PNG) del comprobante de transferencia o depósito bancario.
                    </div>
                    <div style="margin-top: 10px; box-sizing: border-box; width: 100%;">
                        <label style="font-weight: 700; color: #0f172a; display: block;">Archivo Comprobante (PDF, JPG, PNG):</label>
                        <input type="file" id="swal-file-cobro-comp" accept=".pdf,.png,.jpg,.jpeg,.webp" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 8px 10px; border: 1.5px dashed #0284c7; border-radius: 8px; font-size: 0.85rem; background: #ffffff; color: #0f172a; cursor: pointer;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-upload me-1"></i> Subir Comprobante',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            preConfirm: () => {
                const fileEl = document.getElementById('swal-file-cobro-comp');
                const fileComp = (fileEl && fileEl.files.length > 0) ? fileEl.files[0] : null;
                if (!fileComp) {
                    Swal.showValidationMessage('Debes seleccionar un archivo PDF o imagen.');
                    return false;
                }
                return { fileComp: fileComp };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                enviarSubirComprobanteCobro(cobroId, result.value.fileComp);
            }
        });
    }

    function enviarSubirComprobanteCobro(cobroId, fileComp) {
        Swal.fire({ title: 'Subiendo comprobante...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        let formData = new FormData();
        formData.append('comprobante_file', fileComp);

        fetch("{{ url('/contabilidad/caja-general/cobro') }}/" + cobroId + "/subir-comprobante", {
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
                Swal.fire('¡Éxito!', res.body.mensaje || 'Comprobante guardado correctamente.', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.body?.error || 'No se pudo subir el comprobante.', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión: ' + err.message, 'error'));
    }
</script>
@endsection
