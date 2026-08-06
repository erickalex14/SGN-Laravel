@extends('layouts.app')

@section('contenido')
<style>
    .rep-container {
        padding: 28px 24px;
        max-width: 1600px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .rep-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        background: #ffffff;
        padding: 20px 24px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .rep-title {
        color: #0f172a;
        font-size: 1.4rem;
        font-weight: 800;
        margin: 0;
    }
    .rep-subtitle {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 4px;
    }
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        background: #ffffff;
        padding: 18px 22px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filter-select, .filter-input {
        background: #f8fafc;
        color: #0f172a;
        border: 1.5px solid #cbd5e1;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    /* Pestañas Principales */
    .rep-tabs-nav {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        border-bottom: 2.5px solid #cbd5e1;
        padding-bottom: 2px;
        overflow-x: auto;
    }
    .rep-tab-btn {
        background: #f1f5f9;
        color: #475569;
        border: 1.5px solid #cbd5e1;
        border-bottom: none;
        padding: 12px 20px;
        border-radius: 10px 10px 0 0;
        font-size: 0.925rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .rep-tab-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .rep-tab-btn.active {
        background: #ffffff;
        color: #2563eb;
        border-color: #2563eb #2563eb #ffffff;
        margin-bottom: -2.5px;
        box-shadow: 0 -4px 12px rgba(37, 99, 235, 0.1);
    }

    /* Tarjetas KPI de Balances */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .kpi-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .kpi-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 800;
        margin-top: 8px;
        color: #0f172a;
    }
    .kpi-subtext {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 6px;
        font-weight: 500;
    }

    /* Secciones de Auditoría */
    .section-card {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 28px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 14px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 20px;
    }
    .section-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-responsive {
        overflow-x: auto;
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        color: #1e293b;
        font-size: 0.875rem;
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
    }
    .custom-table th {
        background: #f8fafc;
        color: #475569;
        text-align: left;
        padding: 12px 14px;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.775rem;
    }
    .custom-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .custom-table tr:hover td {
        background: #f8fafc;
    }

    .btn-details {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.775rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-details:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .btn-excel {
        background: #059669;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-excel:hover {
        background: #047857;
    }

    .btn-print {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-print:hover {
        background: #1d4ed8;
    }
</style>

<div class="rep-container">
    <div class="rep-header">
        <div>
            <h1 class="rep-title">Reportería y Auditoría de Contabilidad</h1>
            <div class="rep-subtitle">Balances Financieros Consolidados de Caja General, Caja Bancos, Cajas Chicas por Sucursal y Recuento B2B</div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn-excel" onclick="exportarExcelConsolidado()">
                <i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo
            </button>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir Reporte PDF
            </button>
        </div>
    </div>

    <!-- Barra de Filtros General y Avanzada para Caja Chica -->
    <form method="GET" action="{{ route('contabilidad.reportes') }}" class="filter-bar">
        <div class="filter-group">
            <i class="bi bi-calendar-range" style="color: #2563eb; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Desde:</label>
            <input type="date" name="fecha_inicio" class="filter-input" value="{{ $fechaInicio }}" onchange="this.form.submit()">
        </div>

        <div class="filter-group">
            <label style="color: #0f172a; font-weight: 700;">Hasta:</label>
            <input type="date" name="fecha_fin" class="filter-input" value="{{ $fechaFin }}" onchange="this.form.submit()">
        </div>

        @if($esAdminMaster)
            <div class="filter-group">
                <i class="bi bi-geo-alt" style="color: #2563eb; font-size: 1.1rem;"></i>
                <label style="color: #0f172a; font-weight: 700;">Sucursal:</label>
                <select name="sucursal_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Todas las Sucursales --</option>
                    @foreach($sucursalesSelect as $suc)
                        <option value="{{ $suc->id }}" {{ (string)$sucursalFiltro === (string)$suc->id ? 'selected' : '' }}>
                            {{ $suc->ciudad }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="filter-group">
                <span class="badge" style="background: #dbeafe; color: #1e40af; font-size: 0.85rem; padding: 6px 12px; font-weight: 700;">
                    <i class="bi bi-geo-alt me-1"></i>Sucursal: {{ auth()->user()->sucursalPrincipal->ciudad ?? 'Asignada' }}
                </span>
            </div>
        @endif

        <div class="filter-group">
            <i class="bi bi-tags" style="color: #059669; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Tipo Gasto (Caja Chica):</label>
            <select name="tipo_gasto" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Todos los Gastos --</option>
                @foreach($tiposGastoSelect as $tg)
                    <option value="{{ $tg }}" {{ $tipoGastoFiltro === $tg ? 'selected' : '' }}>{{ $tg }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <i class="bi bi-person" style="color: #7c3aed; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Técnico / Custodio:</label>
            <select name="tecnico_id" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Todo el Personal --</option>
                @foreach($tecnicosSelect as $tec)
                    <option value="{{ $tec->nombre_tecnico ?? $tec->usuario }}" {{ $tecnicoFiltro === ($tec->nombre_tecnico ?? $tec->usuario) ? 'selected' : '' }}>
                        {{ $tec->nombre_tecnico ?? $tec->usuario }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($sucursalFiltro !== '' || $tipoGastoFiltro !== '' || $tecnicoFiltro !== '' || $fechaInicio !== \Carbon\Carbon::now()->startOfYear()->format('Y-m-d'))
            <a href="{{ route('contabilidad.reportes') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline; font-size: 0.85rem;">
                Limpiar filtros
            </a>
        @endif
    </form>

    <!-- PESTAÑAS PRINCIPALES DE REPORTE Y AUDITORÍA -->
    <div class="rep-tabs-nav">
        <button type="button" class="rep-tab-btn active" id="tab-btn-resumen" onclick="switchRepTab('resumen')">
            <i class="bi bi-pie-chart me-1"></i>1. Resumen Consolidado & Balances
        </button>
        <button type="button" class="rep-tab-btn" id="tab-btn-cajageneral" onclick="switchRepTab('cajageneral')">
            <i class="bi bi-cash-stack me-1" style="color: #059669;"></i>2. Auditoría Caja General & Arqueos ({{ $arqueos->count() }})
        </button>
        <button type="button" class="rep-tab-btn" id="tab-btn-cajachica" onclick="switchRepTab('cajachica')">
            <i class="bi bi-receipt me-1" style="color: #d97706;"></i>3. Auditoría Cajas Chicas ({{ $gastosCajaChica->count() }})
        </button>
        <button type="button" class="rep-tab-btn" id="tab-btn-b2b" onclick="switchRepTab('b2b')">
            <i class="bi bi-building-check me-1" style="color: #2563eb;"></i>4. Auditoría B2B & Caja Bancos ({{ $lotesB2B->count() }})
        </button>
    </div>

    <!-- PESTAÑA 1: RESUMEN CONSOLIDADO Y BALANCES DE CAJA -->
    <div id="rep-content-resumen">
        <div class="kpi-grid">
            <div class="kpi-card" style="border-top: 4px solid #059669;">
                <div class="kpi-title"><i class="bi bi-cash-coin" style="color: #059669; font-size: 1.1rem;"></i>Balance Caja General (Efectivo)</div>
                <div class="kpi-value" style="color: #059669;">${{ number_format($balanceCajaGeneral, 2) }}</div>
                <div class="kpi-subtext">Cobros en efectivo ventanilla: ${{ number_format($montoCobrosEfectivo, 2) }}</div>
            </div>

            <div class="kpi-card" style="border-top: 4px solid #2563eb;">
                <div class="kpi-title"><i class="bi bi-bank" style="color: #2563eb; font-size: 1.1rem;"></i>Balance Caja Bancos</div>
                <div class="kpi-value" style="color: #2563eb;">${{ number_format($balanceCajaBancos, 2) }}</div>
                <div class="kpi-subtext">Transferencias (${{ number_format($montoCobrosBancos, 2) }}) + Acreditaciones B2B (${{ number_format($netoBancoB2B, 2) }})</div>
            </div>

            <div class="kpi-card" style="border-top: 4px solid #d97706;">
                <div class="kpi-title"><i class="bi bi-receipt-cutoff" style="color: #d97706; font-size: 1.1rem;"></i>Balance Cajas Chicas (Gastos)</div>
                <div class="kpi-value" style="color: #d97706;">${{ number_format($balanceCajaChica, 2) }}</div>
                <div class="kpi-subtext">Total gastado en vales y facturas menores</div>
            </div>

            <div class="kpi-card" style="border-top: 4px solid #7c3aed;">
                <div class="kpi-title"><i class="bi bi-file-earmark-spreadsheet" style="color: #7c3aed; font-size: 1.1rem;"></i>Facturación B2B (con IVA)</div>
                <div class="kpi-value" style="color: #7c3aed;">${{ number_format($totalConIvaB2B, 2) }}</div>
                <div class="kpi-subtext">Retenciones SRI acumuladas: Renta ${{ number_format($retRentaB2B, 2) }} | IVA ${{ number_format($retIvaB2B, 2) }}</div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-graph-up-arrow" style="color: #059669;"></i>
                    <span>Balance Consolidado del Período ({{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }})</span>
                </div>
                <div style="font-size: 1.1rem; font-weight: 800; color: #059669;">
                    Balance Neto Global: ${{ number_format($balanceNetoGlobal, 2) }}
                </div>
            </div>
            <p style="color: #64748b; font-size: 0.9rem;">
                Resumen ejecutivo del flujo financiero acumulado en el período de consulta seleccionado. Se computan los ingresos de ordenes operativas, transferencias bancarias, cobros B2B con IVA y deducciones de caja chica.
            </p>
        </div>
    </div>

    <!-- PESTAÑA 2: AUDITORÍA CAJA GENERAL Y ARQUEOS (CUADRE IDENTICO A CAJA GENERAL) -->
    <div id="rep-content-cajageneral" style="display: none;">
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-clock-history" style="color: #059669;"></i>
                    <span>Historial de Arqueos y Cierres Diarios de Caja General ({{ $arqueos->count() }} arqueos)</span>
                </div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #475569;">
                    Total Físico Arqueado: <strong style="color: #059669;">${{ number_format($totalMontoFisicoArqueos, 2) }}</strong>
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
                            <th style="text-align: center;">Acción / Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arqueos as $arq)
                            @php
                                $codSuc = $arq->codigo_sucursal ?? 'ACC30';
                                $nroArqStr = $codSuc . '-ARQ-' . str_pad($arq->id, 6, '0', STR_PAD_LEFT);
                                $dif = (float)$arq->diferencia;
                            @endphp
                            <tr>
                                <td><strong style="color: #2563eb;">{{ $nroArqStr }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($arq->fecha)->format('d/m/Y H:i') }}</td>
                                <td>${{ number_format((float)($arq->monto_sistema ?? $arq->total_efectivo), 2) }}</td>
                                <td><strong style="color: #0f172a;">${{ number_format((float)($arq->monto_fisico ?? $arq->total_efectivo), 2) }}</strong></td>
                                <td>
                                    <span style="font-weight: 800; color: {{ $dif < 0 ? '#ef4444' : ($dif > 0 ? '#f59e0b' : '#059669') }};">
                                        ${{ number_format($dif, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background: #dcfce7; color: #166534; padding: 4px 10px; font-weight: 700; border: 1px solid #bbf7d0;">
                                        {{ strtoupper($arq->tipo_diferencia ?? 'CUADRE EXACTO') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; font-weight: 700; border: 1px solid #bae6fd;">
                                        {{ strtoupper($arq->estado ?? 'DEPOSITADO') }}
                                    </span>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="{{ route('cajageneral.imprimir_arqueo', $arq->id) }}" target="_blank" class="btn-details" style="background: #2563eb; color: #ffffff; border-color: #1d4ed8; margin-right: 4px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF Arqueo
                                    </a>
                                    @if(!empty($arq->comprobante_deposito_url))
                                        <a href="{{ asset($arq->comprobante_deposito_url) }}" target="_blank" class="btn-details" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                                            <i class="bi bi-paperclip me-1"></i>Ver Comprobante
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">No se registraron arqueos en el período seleccionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-wallet2" style="color: #2563eb;"></i>
                    <span>Detalle de Cobros Registrados en Ventanilla ({{ $cobros->count() }} cobros)</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nro. Orden</th>
                            <th>Fecha Cobro</th>
                            <th>Forma de Pago</th>
                            <th>Destino Cuenta</th>
                            <th>Monto Cobrado ($)</th>
                            <th>Estado Arqueo</th>
                            <th>Comprobante Depósito</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cobros as $cob)
                            @php
                                $compUrl = $cob->comprobante_url ?? $cob->comprobante_deposito_path ?? '';
                            @endphp
                            <tr>
                                <td><strong>{{ $cob->nro_orden ?? 'N/A' }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($cob->fecha_cobro)->format('d/m/Y H:i') }}</td>
                                <td>{{ $cob->metodo_pago ?? 'Efectivo' }}</td>
                                <td>
                                    <span class="badge" style="background: {{ $cob->destino_cuenta === 'Caja General' ? '#dcfce7' : '#dbeafe' }}; color: {{ $cob->destino_cuenta === 'Caja General' ? '#166534' : '#1e40af' }}; font-weight: 700; padding: 4px 8px;">
                                        {{ $cob->destino_cuenta }}
                                    </span>
                                </td>
                                <td><strong style="color: #0f172a;">${{ number_format((float)$cob->monto_cobrado, 2) }}</strong></td>
                                <td>{{ $cob->estado_arqueo ?? 'Pendiente' }}</td>
                                <td>
                                    @if(!empty($compUrl))
                                        <a href="{{ asset($compUrl) }}" target="_blank" class="btn-details">
                                            <i class="bi bi-paperclip me-1"></i>Ver Depósito
                                        </a>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.8rem;">Sin adjunto</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">No hay cobros registrados en el rango de fechas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 3: AUDITORÍA Y REPORTERÍA DE CAJAS CHICAS POR SUCURSAL -->
    <div id="rep-content-cajachica" style="display: none;">
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-boxes" style="color: #d97706;"></i>
                    <span>Resumen por Cajas Chicas Activas ({{ $cajasChicasCabeceras->count() }} Cajas Chicas)</span>
                </div>
            </div>

            <!-- CARDS INDIVIDUALES POR CADA CAJA CHICA POR SUCURSAL -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">
                @forelse($cajasChicasCabeceras as $cc)
                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-left: 5px solid #d97706; border-radius: 10px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <strong style="color: #0f172a; font-size: 1rem;">
                                <i class="bi bi-wallet2 me-1" style="color: #d97706;"></i>{{ $cc->nro_caja_chica }}
                            </strong>
                            <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.75rem; padding: 4px 8px; font-weight: 700; border: 1px solid #fde68a;">
                                {{ $cc->estado }}
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; color: #475569; line-height: 1.6;">
                            <div><strong>Sucursal:</strong> {{ $cc->sucursal_ciudad ?? $cc->codigo_sucursal }}</div>
                            <div><strong>Custodio Responsable:</strong> {{ $cc->custodio_nombre }}</div>
                            <div><strong>Fondo Inicial Asignado:</strong> <strong style="color: #059669;">${{ number_format((float)$cc->fondo_inicial, 2) }}</strong></div>
                            <div><strong>Gastos Ejecutados (Filtro):</strong> <strong style="color: #d97706;">${{ number_format($cc->total_gastos, 2) }}</strong> ({{ $cc->cant_gastos }} vales)</div>
                            <div style="border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 6px;">
                                <strong>Saldo Disponible Estimado:</strong> <strong style="color: #2563eb; font-size: 0.95rem;">${{ number_format($cc->saldo_disponible, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="color: #94a3b8; padding: 16px;">No hay cajas chicas registradas para la sucursal seleccionada.</div>
                @endforelse
            </div>

            <div class="section-header" style="margin-top: 20px;">
                <div class="section-title">
                    <i class="bi bi-receipt" style="color: #d97706;"></i>
                    <span>Auditoría de Vales y Gastos de Caja Chica ({{ $gastosCajaChica->count() }} gastos)</span>
                </div>
                <div style="font-size: 0.95rem; font-weight: 800; color: #d97706;">
                    Total Gastos Filtro: ${{ number_format($totalGastosCajaChica, 2) }}
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nro. Comprobante</th>
                            <th>Fecha</th>
                            <th>Sucursal / Caja</th>
                            <th>Tipo de Gasto / Categoría</th>
                            <th>Proveedor</th>
                            <th>Descripción / Justificación</th>
                            <th>Beneficiario / Técnico</th>
                            <th>Subtotal ($)</th>
                            <th>IVA ($)</th>
                            <th>Total ($)</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gastosCajaChica as $gasto)
                            <tr>
                                <td><strong>{{ $gasto->nro_comprobante ?: 'VALE-' . str_pad($gasto->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($gasto->fecha_comprobante ?? $gasto->created_at)->format('d/m/Y') }}</td>
                                <td><span class="badge" style="background: #f1f5f9; color: #334155;">{{ $gasto->sucursal_ciudad ?? $gasto->codigo_sucursal }}</span></td>
                                <td><strong style="color: #d97706;">{{ $gasto->tipo_gasto }}</strong></td>
                                <td>{{ $gasto->proveedor ?: 'Varios' }}</td>
                                <td>{{ $gasto->descripcion }}</td>
                                <td>{{ $gasto->usuario_beneficiado ?: ($gasto->custodio_nombre ?? 'Solicitante') }}</td>
                                <td>${{ number_format((float)($gasto->subtotal_sin_iva + $gasto->subtotal_con_iva), 2) }}</td>
                                <td>${{ number_format((float)$gasto->iva, 2) }}</td>
                                <td><strong style="color: #0f172a;">${{ number_format((float)$gasto->total, 2) }}</strong></td>
                                <td>
                                    @if(!empty($gasto->comprobante_url))
                                        <a href="{{ asset($gasto->comprobante_url) }}" target="_blank" class="btn-details" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
                                            <i class="bi bi-paperclip me-1"></i>Ver Factura
                                        </a>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.8rem;">Sin adjunto</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align: center; color: #94a3b8; padding: 24px;">No se encontraron registros de gastos de caja chica con los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 4: AUDITORÍA RECUENTO B2B & CAJA BANCOS -->
    <div id="rep-content-b2b" style="display: none;">
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-building-check" style="color: #2563eb;"></i>
                    <span>Auditoría de Lotes de Facturación B2B ({{ $lotesB2B->count() }} lotes)</span>
                </div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #2563eb;">
                    Total Depósitos Bancos: <strong>${{ number_format($netoBancoB2B, 2) }}</strong>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nro. Lote</th>
                            <th>Empresa</th>
                            <th>Total Órdenes</th>
                            <th>Subtotal ($)</th>
                            <th>IVA 15% ($)</th>
                            <th>Total con IVA ($)</th>
                            <th>Ret. Renta ($)</th>
                            <th>Ret. IVA ($)</th>
                            <th>Neto Banco ($)</th>
                            <th>Banco Destino</th>
                            <th>Fecha</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotesB2B as $lote)
                            <tr>
                                <td><strong>{{ $lote->nro_lote }}</strong></td>
                                <td>{{ $lote->empresa_nombre }}</td>
                                <td>{{ $lote->total_ordenes }} órdenes</td>
                                <td>${{ number_format((float)$lote->subtotal, 2) }}</td>
                                <td>${{ number_format((float)$lote->monto_iva, 2) }}</td>
                                <td><strong style="color: #059669;">${{ number_format((float)$lote->total_con_iva, 2) }}</strong></td>
                                <td>${{ number_format((float)$lote->monto_retencion_renta, 2) }}</td>
                                <td>${{ number_format((float)$lote->monto_retencion_iva, 2) }}</td>
                                <td><strong style="color: #2563eb;">${{ number_format((float)$lote->monto_neto_banco, 2) }}</strong></td>
                                <td>{{ $lote->banco_destino ?? 'Banco Pichincha' }}</td>
                                <td>{{ \Carbon\Carbon::parse($lote->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if(!empty($lote->comprobante_path))
                                        <a href="{{ asset($lote->comprobante_path) }}" target="_blank" class="btn-details" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                                            <i class="bi bi-paperclip me-1"></i>Ver Pago
                                        </a>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.8rem;">Sin adjunto</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" style="text-align: center; color: #94a3b8; padding: 24px;">No hay lotes B2B cobrados en el rango de fechas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    function switchRepTab(tabKey) {
        document.querySelectorAll('.rep-tab-btn').forEach(b => b.classList.remove('active'));
        const btnTarget = document.getElementById('tab-btn-' + tabKey);
        if (btnTarget) btnTarget.classList.add('active');

        ['resumen', 'cajageneral', 'cajachica', 'b2b'].forEach(t => {
            const content = document.getElementById('rep-content-' + t);
            if (content) content.style.display = (t === tabKey) ? 'block' : 'none';
        });
    }

    async function exportarExcelConsolidado() {
        const btn = document.querySelector('.btn-excel');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generando XLSX...';

        try {
            if (!window.ExcelJS) {
                await new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js';
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }

            const wb = new ExcelJS.Workbook();
            wb.creator = 'Novitec SGN';
            wb.created = new Date();

            const fl = a => ({ type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + a } });
            const bd = (c = 'E2E8F0') => { const b = { style: 'thin', color: { argb: 'FF' + c } }; return { top: b, left: b, bottom: b, right: b }; };
            const fn = (bold, size, color) => ({ bold: !!bold, size: size || 10, color: { argb: 'FF' + (color || '0F172A') } });

            // 1. Hoja Balances
            const ws1 = wb.addWorksheet('Balances Financieros');
            ws1.columns = [{ width: 35 }, { width: 25 }, { width: 50 }];
            ws1.addRow(['MÓDULO / CAJA', 'MONTO TOTAL ($)', 'DETALLES / OBSERVACIONES']).font = fn(true, 11, 'FFFFFF');
            ws1.getRow(1).fill = fl('1E3A8A');

            ws1.addRow(['Balance Caja General (Efectivo)', {{ $balanceCajaGeneral }}, 'Cobros directos en ventanilla']);
            ws1.addRow(['Balance Caja Bancos (Acreditaciones)', {{ $balanceCajaBancos }}, 'Transferencias directas + Depósitos B2B']);
            ws1.addRow(['Balance Cajas Chicas (Gastos)', {{ $balanceCajaChica }}, 'Vouchers, Vales y Facturas de compras menores']);
            ws1.addRow(['Total Facturación B2B (con IVA)', {{ $totalConIvaB2B }}, 'Lotes facturados a empresas clientes']);
            ws1.addRow(['BALANCE NETO CONSOLIDADO GLOBAL', {{ $balanceNetoGlobal }}, 'Flujo neto acumulado auditado']);

            [2, 3, 4, 5, 6].forEach(r => {
                ws1.getCell(`B${r}`).numFormat = '$#,##0.00';
                ws1.getRow(r).border = bd();
            });
            ws1.getCell('A6').font = fn(true, 11, '059669');
            ws1.getCell('B6').font = fn(true, 11, '059669');

            // Descargar XLSX
            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Auditoria_Contabilidad_Consolidada_${new Date().toISOString().slice(0, 10)}.xlsx`;
            a.click();
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'No se pudo generar la exportación Excel.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo';
        }
    }
</script>
@endsection
