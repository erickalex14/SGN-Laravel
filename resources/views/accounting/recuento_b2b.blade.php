@extends('layouts.app')

@section('contenido')
<style>
    .b2b-container {
        padding: 28px 24px;
        max-width: 1550px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .b2b-header {
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
    .b2b-title {
        color: #0f172a;
        font-size: 1.4rem;
        font-weight: 800;
        margin: 0;
    }
    .b2b-subtitle {
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
        padding: 16px 20px;
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
    .filter-select {
        background: #f8fafc;
        color: #0f172a;
        border: 1.5px solid #cbd5e1;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        min-width: 240px;
    }

    /* Pestañas Principales Superiores (Pendientes vs Historial) */
    .top-main-tabs {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        border-bottom: 2.5px solid #cbd5e1;
        padding-bottom: 2px;
    }
    .top-main-tab-btn {
        background: #f1f5f9;
        color: #475569;
        border: 1.5px solid #cbd5e1;
        border-bottom: none;
        padding: 12px 24px;
        border-radius: 10px 10px 0 0;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .top-main-tab-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .top-main-tab-btn.active {
        background: #ffffff;
        color: #2563eb;
        border-color: #2563eb #2563eb #ffffff;
        margin-bottom: -2.5px;
        box-shadow: 0 -4px 12px rgba(37, 99, 235, 0.1);
    }
    .top-tab-badge {
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.775rem;
        font-weight: 800;
        background: #e2e8f0;
        color: #334155;
    }
    .top-main-tab-btn.active .top-tab-badge {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Pestañas Principales por Empresa */
    .company-tabs-nav {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 2px;
        overflow-x: auto;
    }
    .company-tab-btn {
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-bottom: none;
        padding: 12px 20px;
        border-radius: 10px 10px 0 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }
    .company-tab-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .company-tab-btn.active {
        background: #ffffff;
        color: #2563eb;
        border-color: #2563eb #2563eb #ffffff;
        margin-bottom: -2px;
        box-shadow: 0 -2px 8px rgba(37, 99, 235, 0.08);
    }
    .company-badge-count {
        font-size: 0.75rem;
        padding: 3px 9px;
        border-radius: 999px;
        font-weight: 800;
        background: #e2e8f0;
        color: #334155;
    }
    .company-tab-btn.active .company-badge-count {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Contenedor Principal de Cada Empresa */
    .company-section-card {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 32px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }
    .company-header-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 20px;
    }
    .company-name-text {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .company-stats-text {
        font-size: 0.9rem;
        font-weight: 600;
        color: #475569;
    }

    /* Subpestañas por Subtipo de Orden dentro de cada Empresa */
    .subtipo-tabs-nav {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        overflow-x: auto;
    }
    .subtipo-tab-btn {
        background: transparent;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .subtipo-tab-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .subtipo-tab-btn.active {
        background: #ffffff;
        color: #2563eb;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }
    .subtipo-badge-count {
        font-size: 0.725rem;
        padding: 2px 8px;
        border-radius: 999px;
        font-weight: 800;
    }
    .subtipo-badge-count.all { background: #cbd5e1; color: #1e293b; }
    .subtipo-badge-count.servicio { background: #dbeafe; color: #1e40af; }
    .subtipo-badge-count.stock { background: #dcfce7; color: #166534; }
    .subtipo-badge-count.autoconsumo { background: #f3e8ff; color: #6b21a8; }
    .subtipo-badge-count.garantia { background: #fef3c7; color: #92400e; }

    /* Apartados por Subtipo dentro de la empresa */
    .subtipo-apartado-card {
        background: #fafafa;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 18px 20px;
        margin-bottom: 20px;
    }
    .subtipo-title-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 10px;
        margin-bottom: 14px;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .subtipo-title-servicio { border-left: 5px solid #2563eb; padding-left: 10px; color: #1e40af; font-weight: 800; font-size: 1rem; }
    .subtipo-title-stock { border-left: 5px solid #10b981; padding-left: 10px; color: #166534; font-weight: 800; font-size: 1rem; }
    .subtipo-title-autoconsumo { border-left: 5px solid #a855f7; padding-left: 10px; color: #6b21a8; font-weight: 800; font-size: 1rem; }
    .subtipo-title-garantia { border-left: 5px solid #f59e0b; padding-left: 10px; color: #92400e; font-weight: 800; font-size: 1rem; }

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
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-details:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .details-row {
        background: #f8fafc !important;
        display: none;
    }

    .btn-primary {
        background: #10b981;
        color: #ffffff;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-primary:hover {
        background: #059669;
    }
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-excel-header {
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
    .btn-excel-header:hover {
        background: #047857;
    }
    .btn-excel-header:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<div class="b2b-container">
    <div class="b2b-header">
        <div>
            <h1 class="b2b-title">Recuento y Facturación B2B</h1>
            <div class="b2b-subtitle">Organizado por Empresa, Sucursal y tipo de orden (Servicio, Stock, Autoconsumo y Garantías Novicompu)</div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" id="btn-excel-header" class="btn-excel-header" disabled onclick="exportarExcelSeleccionadas()">
                <i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX (<span id="count-selected-excel">0</span>)
            </button>
            <button type="button" id="btn-procesar-lote" class="btn-primary" disabled onclick="abrirModalCobroLote()">
                Cobrar Lote Seleccionado (<span id="count-selected">0</span>) — Total: $<span id="sum-selected">0.00</span>
            </button>
        </div>
    </div>

    <!-- Barra de Filtros (Empresa, Sucursal y Buscador General) -->
    <form method="GET" action="{{ route('recuentob2b.index') }}" class="filter-bar">
        <input type="hidden" name="tab" id="filter-input-tab" value="{{ $tabActiva }}">
        
        <div class="filter-group">
            <i class="bi bi-building" style="color: #64748b; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Empresa:</label>
            <select name="empresa" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Todas las Empresas --</option>
                <option value="RB" {{ str_contains(strtoupper($empresaFiltro), 'RB') ? 'selected' : '' }}>RB-HEALTH ECUADOR CIA LTDA</option>
                <option value="NOVI" {{ str_contains(strtoupper($empresaFiltro), 'NOVI') ? 'selected' : '' }}>NOVISOLUTONS CIA. LTDA.</option>
                @foreach($empresasSelect as $emp)
                    @if(!str_contains(strtoupper($emp->nombre), 'RB') && !str_contains(strtoupper($emp->nombre), 'NOVI'))
                        <option value="{{ $emp->nombre }}" {{ $empresaFiltro === $emp->nombre ? 'selected' : '' }}>
                            {{ $emp->nombre }}
                        </option>
                    @endif
                @endforeach
            </select>
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
                <span class="badge badge-servicio" style="font-size: 0.85rem; padding: 6px 12px;">
                    <i class="bi bi-geo-alt me-1"></i>Sucursal: {{ auth()->user()->sucursalPrincipal->ciudad ?? 'Asignada' }}
                </span>
            </div>
        @endif

        <div class="filter-group" style="flex: 1; min-width: 320px;">
            <i class="bi bi-search" style="color: #2563eb; font-size: 1.1rem;"></i>
            <input type="search" name="buscar" id="input-buscar-b2b" class="filter-select" 
                style="width: 100%; border-color: #93c5fd; background: #ffffff;" 
                value="{{ $buscarFiltro ?? '' }}"
                placeholder="Buscar por Nro. Orden, Cédula/RUC, Técnico, Serie o Cliente..." 
                onkeyup="filtrarTablaEnVivo()" onsearch="filtrarTablaEnVivo()">
        </div>

        @if($empresaFiltro !== '' || $sucursalFiltro !== '' || ($buscarFiltro ?? '') !== '')
            <a href="{{ route('recuentob2b.index') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline; font-size: 0.85rem;">
                Limpiar filtros
            </a>
        @endif
    </form>

    <!-- PESTAÑAS PRINCIPALES SUPERIORES: PENDIENTES VS HISTORIAL DE LOTES -->
    <div class="top-main-tabs">
        <button type="button" class="top-main-tab-btn {{ $tabActiva === 'pendientes' ? 'active' : '' }}" id="top-tab-btn-pendientes" onclick="switchTopTab('pendientes')">
            <i class="bi bi-clock-history me-1" style="font-size: 1.1rem;"></i>
            <span>Órdenes Pendientes de Cobro</span>
            <span class="top-tab-badge">{{ $ordenes->count() }}</span>
        </button>
        <button type="button" class="top-main-tab-btn {{ $tabActiva === 'historial' ? 'active' : '' }}" id="top-tab-btn-historial" onclick="switchTopTab('historial')">
            <i class="bi bi-check2-square me-1" style="font-size: 1.1rem; color: #10b981;"></i>
            <span>Historial de Lotes Cobrados</span>
            <span class="top-tab-badge" style="background: #dcfce7; color: #166534;">{{ $lotesProcesados->total() }}</span>
        </button>
    </div>

    <!-- SECCIÓN 1: ÓRDENES PENDIENTES DE COBRO -->
    <div id="main-content-pendientes" style="display: {{ $tabActiva === 'pendientes' ? 'block' : 'none' }};">
        <!-- PESTAÑAS PRINCIPALES POR EMPRESA -->
        <div class="company-tabs-nav">
            <button type="button" class="company-tab-btn active" onclick="switchCompanyTab('todas', this)">
                <i class="bi bi-buildings"></i>
                <span>Todas las Empresas</span>
                <span class="company-badge-count">{{ $ordenes->count() }}</span>
            </button>
            @foreach($ordenesPorEmpresa as $empNombreKey => $grupoEmp)
                @php
                    $slugEmp = \Illuminate\Support\Str::slug($empNombreKey);
                    $cantEmp = $grupoEmp['todas']->count();
                @endphp
                <button type="button" class="company-tab-btn" onclick="switchCompanyTab('{{ $slugEmp }}', this)">
                    <i class="bi bi-building"></i>
                    <span>{{ $empNombreKey }}</span>
                    <span class="company-badge-count">{{ $cantEmp }}</span>
                </button>
            @endforeach
        </div>

        <!-- BLOQUES PRINCIPALES POR CADA EMPRESA -->
        @forelse($ordenesPorEmpresa as $empNombreKey => $grupoEmp)
            @php
                $slugEmp = \Illuminate\Support\Str::slug($empNombreKey);
                $todasEmp = $grupoEmp['todas'];
                $servicioEmp = $grupoEmp['servicio'];
                $stockEmp = $grupoEmp['stock'];
                $autoconsumoEmp = $grupoEmp['autoconsumo'];
                $garantiaEmp = $grupoEmp['garantia'];
                $totalEmpMonto = $todasEmp->sum('valor_total_calculado');
            @endphp

            <div class="company-section-card company-block-container" id="company-block-{{ $slugEmp }}">
                <div class="company-header-title">
                    <div class="company-name-text">
                        <i class="bi bi-building" style="color: #2563eb;"></i>
                        <span>{{ $empNombreKey }}</span>
                    </div>
                    <div class="company-stats-text">
                        Total {{ $todasEmp->count() }} órdenes — Total Estimado: <strong style="color: #059669; font-size: 1.05rem;">${{ number_format($totalEmpMonto, 2) }}</strong>
                    </div>
                </div>

                <!-- SUBPESTAÑAS POR TIPO DE ORDEN DENTRO DE LA EMPRESA -->
                <div class="subtipo-tabs-nav">
                    <button type="button" class="subtipo-tab-btn active" onclick="switchSubtipoTab('{{ $slugEmp }}', 'todos', this)">
                        <i class="bi bi-grid-3x3-gap"></i>
                        <span>Todos los Tipos</span>
                        <span class="subtipo-badge-count all">{{ $todasEmp->count() }}</span>
                    </button>
                    <button type="button" class="subtipo-tab-btn" onclick="switchSubtipoTab('{{ $slugEmp }}', 'servicio', this)">
                        <i class="bi bi-tools"></i>
                        <span>Servicios</span>
                        <span class="subtipo-badge-count servicio">{{ $servicioEmp->count() }}</span>
                    </button>
                    <button type="button" class="subtipo-tab-btn" onclick="switchSubtipoTab('{{ $slugEmp }}', 'stock', this)">
                        <i class="bi bi-box-seam"></i>
                        <span>Stock</span>
                        <span class="subtipo-badge-count stock">{{ $stockEmp->count() }}</span>
                    </button>
                    <button type="button" class="subtipo-tab-btn" onclick="switchSubtipoTab('{{ $slugEmp }}', 'autoconsumo', this)">
                        <i class="bi bi-house-gear"></i>
                        <span>Autoconsumo</span>
                        <span class="subtipo-badge-count autoconsumo">{{ $autoconsumoEmp->count() }}</span>
                    </button>
                    @if($garantiaEmp->count() > 0)
                        <button type="button" class="subtipo-tab-btn" onclick="switchSubtipoTab('{{ $slugEmp }}', 'garantia', this)">
                            <i class="bi bi-shield-check"></i>
                            <span>Garantías</span>
                            <span class="subtipo-badge-count garantia">{{ $garantiaEmp->count() }}</span>
                        </button>
                    @endif
                </div>

                <!-- 1. APARTADO: ÓRDENES DE SERVICIO -->
                @if($servicioEmp->count() > 0)
                    <div class="subtipo-apartado-card subtipo-block-servicio">
                        <div class="subtipo-title-bar">
                            <div class="subtipo-title-servicio">
                                <i class="bi bi-tools me-1"></i>
                                <span>Apartado: Órdenes de Servicio</span>
                            </div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #1e40af;">
                                {{ $servicioEmp->count() }} órdenes — Subtotal: ${{ number_format($servicioEmp->sum('valor_total_calculado'), 2) }}
                            </div>
                        </div>
                        @include('accounting.partials.tabla_recuento_b2b', ['ordenesGrupo' => $servicioEmp, 'tipoGrupo' => $slugEmp . '-servicio'])
                    </div>
                @endif

                <!-- 2. APARTADO: ÓRDENES DE STOCK -->
                @if($stockEmp->count() > 0)
                    <div class="subtipo-apartado-card subtipo-block-stock">
                        <div class="subtipo-title-bar">
                            <div class="subtipo-title-stock">
                                <i class="bi bi-box-seam me-1"></i>
                                <span>Apartado: Órdenes de Stock</span>
                            </div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #166534;">
                                {{ $stockEmp->count() }} órdenes — Subtotal: ${{ number_format($stockEmp->sum('valor_total_calculado'), 2) }}
                            </div>
                        </div>
                        @include('accounting.partials.tabla_recuento_b2b', ['ordenesGrupo' => $stockEmp, 'tipoGrupo' => $slugEmp . '-stock'])
                    </div>
                @endif

                <!-- 3. APARTADO: ÓRDENES DE AUTOCONSUMO -->
                @if($autoconsumoEmp->count() > 0)
                    <div class="subtipo-apartado-card subtipo-block-autoconsumo">
                        <div class="subtipo-title-bar">
                            <div class="subtipo-title-autoconsumo">
                                <i class="bi bi-house-gear me-1"></i>
                                <span>Apartado: Órdenes de Autoconsumo</span>
                            </div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #6b21a8;">
                                {{ $autoconsumoEmp->count() }} órdenes — Subtotal: ${{ number_format($autoconsumoEmp->sum('valor_total_calculado'), 2) }}
                            </div>
                        </div>
                        @include('accounting.partials.tabla_recuento_b2b', ['ordenesGrupo' => $autoconsumoEmp, 'tipoGrupo' => $slugEmp . '-autoconsumo'])
                    </div>
                @endif

                <!-- 4. APARTADO: ÓRDENES DE GARANTÍA -->
                @if($garantiaEmp->count() > 0)
                    <div class="subtipo-apartado-card subtipo-block-garantia">
                        <div class="subtipo-title-bar">
                            <div class="subtipo-title-garantia">
                                <i class="bi bi-shield-check me-1"></i>
                                <span>Apartado: Órdenes de Garantía (Cobro a Novisolutions)</span>
                            </div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #92400e;">
                                {{ $garantiaEmp->count() }} órdenes — Subtotal: ${{ number_format($garantiaEmp->sum('valor_total_calculado'), 2) }}
                            </div>
                        </div>
                        @include('accounting.partials.tabla_recuento_b2b', ['ordenesGrupo' => $garantiaEmp, 'tipoGrupo' => $slugEmp . '-garantia'])
                    </div>
                @endif

                @if($servicioEmp->count() === 0 && $stockEmp->count() === 0 && $autoconsumoEmp->count() === 0 && $garantiaEmp->count() === 0)
                    <div style="text-align: center; color: #94a3b8; padding: 24px;">No hay órdenes finalizadas para esta empresa.</div>
                @endif
            </div>
        @empty
            <div class="company-section-card" style="text-align: center; color: #94a3b8; padding: 36px;">
                No se encontraron órdenes pendientes de cobro B2B para la selección actual.
            </div>
        @endforelse
    </div>

    <!-- SECCIÓN 2: HISTORIAL DE LOTES PROCESADOS Y COBRADOS -->
    <div id="main-content-historial" style="display: {{ $tabActiva === 'historial' ? 'block' : 'none' }};">
        <div class="company-section-card">
            <div class="company-header-title">
                <div class="company-name-text">
                    <i class="bi bi-clock-history" style="color: #2563eb;"></i>
                    <span>Historial de Lotes de Recuento Procesados y Cobrados</span>
                </div>
                <div style="font-size: 0.875rem; color: #64748b; font-weight: 600;">
                    Total Lotes Registrados: <strong style="color: #0f172a;">{{ $lotesProcesados->total() }}</strong>
                </div>
            </div>
            <div class="table-responsive" style="margin-top: 16px;">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nro. Lote</th>
                            <th>Empresa</th>
                            <th>Total Órdenes</th>
                            <th>Subtotal Facturado</th>
                            <th>Pago Neto Banco</th>
                            <th>Retenciones SRI</th>
                            <th>Banco Destino</th>
                            <th>Fecha Registro</th>
                            <th style="text-align: center;">Recibos / Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotesProcesados as $lote)
                            @php
                                $lObj = (object) $lote;
                                $loteIdVal = $lObj->id ?? $lObj->nro_lote ?? '';
                            @endphp
                            <tr>
                                <td><strong>{{ $lObj->nro_lote ?? $lObj->NroLote ?? '' }}</strong></td>
                                <td>{{ $lObj->empresa_nombre ?? $lObj->EmpresaNombre ?? '' }}</td>
                                <td>{{ $lObj->total_ordenes ?? $lObj->TotalOrdenes ?? 0 }} órdenes</td>
                                <td>${{ number_format((float)($lObj->subtotal ?? $lObj->Subtotal ?? 0), 2) }}</td>
                                <td><strong style="color: #2563eb;">${{ number_format((float)($lObj->monto_neto_banco ?? $lObj->MontoNetoBanco ?? 0), 2) }}</strong></td>
                                <td>
                                    Renta: ${{ number_format((float)($lObj->monto_retencion_renta ?? $lObj->MontoRetencionRenta ?? 0), 2) }}<br>
                                    IVA: ${{ number_format((float)($lObj->monto_retencion_iva ?? $lObj->MontoRetencionIva ?? 0), 2) }}
                                </td>
                                <td>{{ $lObj->banco_destino ?? $lObj->BancoDestino ?? 'Banco Pichincha' }}</td>
                                <td>{{ \Carbon\Carbon::parse($lObj->created_at ?? $lObj->CreatedAt ?? now())->format('d/m/Y H:i') }}</td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="{{ route('recuentob2b.recibo_cliente', $loteIdVal) }}" target="_blank" class="btn-details" style="text-decoration: none; display: inline-block; margin-right: 4px; background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Cliente
                                    </a>
                                    <a href="{{ route('recuentob2b.recibo_interno', $loteIdVal) }}" target="_blank" class="btn-details" style="text-decoration: none; display: inline-block; margin-right: 4px; background: #f3e8ff; color: #6b21a8; border-color: #e9d5ff;">
                                        <i class="bi bi-file-earmark-text me-1"></i>Interno
                                    </a>
                                    <form method="POST" action="{{ route('facturas.issue_b2b', $loteIdVal) }}" style="display:inline-block; margin-right:4px;" onsubmit="return confirm('¿Facturar este lote en el ambiente de PRUEBAS del SRI?')">
                                        @csrf
                                        <button type="submit" class="btn-details" style="background:#0f172a; color:white; border-color:#0f172a; cursor:pointer;"><i class="bi bi-file-earmark-check me-1"></i>Facturar</button>
                                    </form>
                                    @if(!empty($lObj->comprobante_path))
                                        <a href="{{ asset($lObj->comprobante_path) }}" target="_blank" class="btn-details" style="text-decoration: none; display: inline-block; background: #fef3c7; color: #92400e; border-color: #fde68a;">
                                            <i class="bi bi-paperclip me-1"></i>Comprobante
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; color: #94a3b8; padding: 24px;">No hay lotes procesados previamente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN DE LOTES COBRADOS -->
            @if($lotesProcesados->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1.5px solid #e2e8f0;">
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
                        Mostrando del {{ $lotesProcesados->firstItem() }} al {{ $lotesProcesados->lastItem() }} de {{ $lotesProcesados->total() }} lotes
                    </div>
                    <div>
                        {{ $lotesProcesados->appends(['tab' => 'historial'])->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    let seleccionadas = [];

    function switchTopTab(tabKey) {
        document.querySelectorAll('.top-main-tab-btn').forEach(b => b.classList.remove('active'));
        const btnTarget = document.getElementById('top-tab-btn-' + tabKey);
        if (btnTarget) btnTarget.classList.add('active');

        const inputTab = document.getElementById('filter-input-tab');
        if (inputTab) inputTab.value = tabKey;

        const contentPendientes = document.getElementById('main-content-pendientes');
        const contentHistorial = document.getElementById('main-content-historial');

        if (tabKey === 'historial') {
            if (contentPendientes) contentPendientes.style.display = 'none';
            if (contentHistorial) contentHistorial.style.display = 'block';
        } else {
            if (contentPendientes) contentPendientes.style.display = 'block';
            if (contentHistorial) contentHistorial.style.display = 'none';
        }
    }

    function switchCompanyTab(slugKey, btn) {
        document.querySelectorAll('.company-tab-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const blocks = document.querySelectorAll('.company-block-container');
        if (slugKey === 'todas') {
            blocks.forEach(b => b.style.display = 'block');
        } else {
            blocks.forEach(b => b.style.display = 'none');
            const target = document.getElementById('company-block-' + slugKey);
            if (target) target.style.display = 'block';
        }
    }

    function switchSubtipoTab(companySlug, subtipoKey, btn) {
        const parentCard = document.getElementById('company-block-' + companySlug);
        if (!parentCard) return;

        parentCard.querySelectorAll('.subtipo-tab-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const subBlocks = parentCard.querySelectorAll('.subtipo-apartado-card');
        if (subtipoKey === 'todos') {
            subBlocks.forEach(b => b.style.display = 'block');
        } else {
            subBlocks.forEach(b => b.style.display = 'none');
            const target = parentCard.querySelector('.subtipo-block-' + subtipoKey);
            if (target) target.style.display = 'block';
        }
    }

    function normalizarDigitosOrden(str) {
        if (!str) return { digitos: '', sinCeros: '' };
        const digitos = String(str).replace(/\D/g, '');
        const sinCeros = digitos.replace(/^0+/, '');
        return { digitos, sinCeros };
    }

    function filtrarTablaEnVivo() {
        const input = document.getElementById('input-buscar-b2b');
        if (!input) return;
        const rawQuery = input.value.toLowerCase().trim();
        const normQuery = normalizarDigitosOrden(rawQuery);

        const rows = document.querySelectorAll('.custom-table tbody tr:not(.details-row)');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const chk = row.querySelector('.chk-orden');
            let dataText = '';
            let nroOrdenRaw = '';
            let nroDigitos = '';
            let nroSinCeros = '';

            if (chk) {
                const d = chk.dataset;
                nroOrdenRaw = (d.nro || '').toLowerCase();
                const norm = normalizarDigitosOrden(d.nro);
                nroDigitos = norm.digitos;
                nroSinCeros = norm.sinCeros;

                dataText = `${d.nro || ''} ${d.clienteNombre || ''} ${d.identificacion || ''} ${d.tecnico || ''} ${d.equipo || ''} ${d.empresa || ''} ${d.subtipo || ''}`.toLowerCase();
            }

            let coincide = false;

            if (rawQuery === '') {
                coincide = true;
            } else {
                if (text.includes(rawQuery) || dataText.includes(rawQuery)) {
                    coincide = true;
                }
                if (!coincide && normQuery.sinCeros !== '') {
                    if (nroSinCeros.includes(normQuery.sinCeros) || 
                        nroDigitos.includes(normQuery.digitos) || 
                        nroOrdenRaw.includes(normQuery.sinCeros)) {
                        coincide = true;
                    }
                }
            }

            if (coincide) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
                if (chk) {
                    chk.checked = false;
                    const detailsRow = document.getElementById('details-row-' + chk.dataset.id);
                    if (detailsRow) detailsRow.style.display = 'none';
                }
            }
        });

        actualizarSeleccion();
    }

    function toggleDetails(ordId) {
        const row = document.getElementById('details-row-' + ordId);
        if (row) {
            row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
        }
    }

    function toggleSelectAllGrupo(master, tipoGrupo) {
        const container = document.getElementById('block-' + tipoGrupo) || document;
        container.querySelectorAll('.chk-orden').forEach(chk => {
            chk.checked = master.checked;
        });
        actualizarSeleccion();
    }

    function actualizarSeleccion() {
        seleccionadas = [];
        let total = 0.0;
        const procesadosMap = new Set();

        document.querySelectorAll('.chk-orden:checked').forEach(chk => {
            const data = chk.dataset;
            const ordId = parseInt(data.id);
            const tipoOrden = data.tipoOrden || 'empresa';
            const uniqueKey = tipoOrden + '-' + ordId;

            if (!procesadosMap.has(uniqueKey)) {
                procesadosMap.add(uniqueKey);
                const itemTotal = parseFloat(data.total) || 0;
                total += itemTotal;
                seleccionadas.push({
                    id: ordId,
                    tipo_orden: tipoOrden,
                    nro_orden: data.nro || '',
                    empresa: data.empresa || '',
                    cliente_nombre: data.clienteNombre || '',
                    identificacion: data.identificacion || '',
                    cliente_telefono: data.clienteTelefono || '',
                    cliente_correo: data.clienteCorreo || '',
                    subtipo: data.subtipo || '',
                    equipo: data.equipo || '',
                    tecnico: data.tecnico || '',
                    sucursal: data.sucursal || '',
                    fecha_ingreso: data.fechaIngreso || '',
                    fecha_entrega: data.fechaEntrega || '',
                    horas: parseFloat(data.horas) || 1.0,
                    tecnicos_count: parseInt(data.tecnicos) || 1,
                    tarifa: parseFloat(data.tarifa) || 0.0,
                    valor_total: itemTotal,
                    estado: data.estado || '',
                    facturacion: data.facturacion || '',
                    descripcion: data.descripcion || '',
                    memo: data.memo || ''
                });
            }
        });

        const countSelected = seleccionadas.length;
        document.getElementById('count-selected').innerText = countSelected;
        document.getElementById('count-selected-excel').innerText = countSelected;
        document.getElementById('sum-selected').innerText = total.toFixed(2);
        
        document.getElementById('btn-procesar-lote').disabled = (countSelected === 0);
        document.getElementById('btn-excel-header').disabled = (countSelected === 0);
    }

    async function exportarExcelSeleccionadas() {
        if (seleccionadas.length === 0) return;

        const btn = document.getElementById('btn-excel-header');
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

            const C = {
                azulO: '1E3A8A', azul: '1E40AF', azulL: 'DBEAFE', azulXL: 'EFF6FF',
                verdeO: '065F46', verde: '166534', verdeL: 'DCFCE7', verdeXL: 'ECFDF5',
                ambar: '854D0E', ambarL: 'FEF9C3', rojo: '9D174D', rojoL: 'FCE7F3',
                gris: 'F8FAFC', grisMed: 'E2E8F0', grisOsc: '64748B', blanco: 'FFFFFF', negro: '0F172A'
            };

            const fl = a => ({ type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + a } });
            const bd = (c = 'E2E8F0') => { const b = { style: 'thin', color: { argb: 'FF' + c } }; return { top: b, left: b, bottom: b, right: b }; };
            const fn = (bold, size, color, extra = {}) => Object.assign({ bold: !!bold, size: size || 10, color: { argb: 'FF' + (color || C.negro) } }, extra);
            const al = (h = 'left', v = 'middle') => ({ horizontal: h, vertical: v });

            const cols = [
                'Nro. Orden', 'Tipo Origen', 'Empresa Facturada', 'Cliente Final (Usuario de la Orden)', 'C.I. / RUC',
                'Teléfono', 'Correo', 'Subtipo', 'Equipo / Marca / Serie', 'Descripción / Falla',
                'Técnico(s) Asignados', 'Cant. Técnicos', 'Sucursal Origen', 'F. Ingreso', 'F. Entrega',
                'Horas Trab.', 'Tarifa Aplicada ($)', 'Valor Cobro Novicompu ($)', 'Valor RB-Health / Otras ($)',
                'Estado Orden', 'Estado Facturación', 'Memo / Observaciones'
            ];
            const nc = cols.length;
            const widths = [15, 14, 28, 30, 15, 14, 22, 16, 26, 30, 24, 12, 16, 14, 14, 12, 18, 22, 22, 16, 18, 30];

            const ws = wb.addWorksheet('Recuento B2B', {
                views: [{ showGridLines: true }],
                pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true, fitToWidth: 1 }
            });
            ws.columns = widths.map(w => ({ width: w }));

            // Título Principal
            ws.mergeCells(1, 1, 1, nc);
            const t1 = ws.getCell('A1');
            t1.value = 'REPORTE ENTERPRISE DE RECUENTO Y FACTURACIÓN B2B — Novitecnología Cía. Ltda.';
            t1.fill = fl(C.azulO); t1.font = fn(true, 13, C.blanco); t1.alignment = al('center');
            ws.getRow(1).height = 28;

            // Subtítulo Metadatos
            ws.mergeCells(2, 1, 2, nc);
            const totalSum = seleccionadas.reduce((a, b) => a + (b.valor_total || 0), 0);
            const t2 = ws.getCell('A2');
            t2.value = `Generado: ${new Date().toLocaleString('es-EC')}   |   Exportado por: {{ auth()->user()->nombre_tecnico ?? auth()->user()->usuario ?? 'Usuario' }}   |   Total Registros: ${seleccionadas.length}   |   Subtotal Lote: $${totalSum.toFixed(2)}`;
            t2.fill = fl(C.azulL); t2.font = fn(false, 9, C.azulO, { italic: true }); t2.alignment = al('center');
            ws.getRow(2).height = 16;

            // Fila KPIs
            const cantGarantia = seleccionadas.filter(o => o.subtipo === 'Garantía').length;
            const montoGarantia = seleccionadas.filter(o => o.subtipo === 'Garantía').reduce((a, b) => a + b.valor_total, 0);
            const cantServicio = seleccionadas.filter(o => o.subtipo === 'Servicios').length;
            const montoServicio = seleccionadas.filter(o => o.subtipo === 'Servicios').reduce((a, b) => a + b.valor_total, 0);

            const kpis = [
                { l: 'TOTAL ÓRDENES', v: seleccionadas.length, p: '100%', bg: C.azulXL, fg: C.azul },
                { l: 'SUBTOTAL FACTURADO', v: '$' + totalSum.toFixed(2), p: '', bg: C.verdeXL, fg: C.verdeO },
                { l: 'GARANTÍAS NOVICOMPU', v: cantGarantia + ' ($' + montoGarantia.toFixed(2) + ')', p: '', bg: C.ambarL, fg: C.ambar },
                { l: 'ÓRDENES SERVICIO', v: cantServicio + ' ($' + montoServicio.toFixed(2) + ')', p: '', bg: C.azulL, fg: C.azul },
            ];

            ws.getRow(3).height = 14; ws.getRow(4).height = 24; ws.getRow(5).height = 14;
            const kStep = Math.floor(nc / 4);
            kpis.forEach((k, i) => {
                const col = i * kStep + 1;
                ['A', 'B', 'C'].forEach((_, ri) => {
                    const cell = ws.getCell(3 + ri, col); cell.fill = fl(k.bg); cell.border = bd();
                });
                const lc = ws.getCell(3, col); lc.value = k.l; lc.font = fn(true, 8, k.fg); lc.alignment = al('center');
                const vc = ws.getCell(4, col); vc.value = k.v; vc.font = fn(true, 14, k.fg); vc.alignment = al('center');
            });

            // Header de Tabla
            const hRowN = 7;
            ws.getRow(6).height = 8;
            ws.getRow(hRowN).height = 22;

            cols.forEach((h, i) => {
                const c = ws.getCell(hRowN, i + 1); c.value = h; c.fill = fl(C.azulO);
                c.font = fn(true, 9, C.blanco); c.alignment = al('center'); c.border = bd('1D4ED8');
            });
            ws.autoFilter = { from: { row: hRowN, column: 1 }, to: { row: hRowN, column: nc } };

            // Filas de Datos
            seleccionadas.forEach((r, idx) => {
                const isRB = (r.empresa || '').toUpperCase().includes('RB');
                const valNovicompu = !isRB ? r.valor_total : 0.00;
                const valOtra = isRB ? r.valor_total : 0.00;

                const vals = [
                    r.nro_orden,
                    (r.tipo_orden || 'empresa').toUpperCase(),
                    r.empresa,
                    r.cliente_nombre || 'N/A',
                    r.identificacion || 'N/A',
                    r.cliente_telefono || 'N/A',
                    r.cliente_correo || 'N/A',
                    r.subtipo || 'Servicios',
                    r.equipo || 'N/A',
                    r.descripcion || '-',
                    r.tecnico || 'N/A',
                    r.tecnicos_count || 1,
                    r.sucursal || 'N/A',
                    r.fecha_ingreso || '-',
                    r.fecha_entrega || '-',
                    r.horas || 1.0,
                    r.tarifa || 0.0,
                    valNovicompu,
                    valOtra,
                    r.estado || 'Finalizada',
                    r.facturacion || 'Pendiente',
                    r.memo || '-'
                ];

                const dr = ws.addRow(vals);
                dr.height = 16;
                const bgBase = idx % 2 === 0 ? C.blanco : C.gris;

                vals.forEach((v, ci) => {
                    const cell = dr.getCell(ci + 1);
                    cell.border = bd();
                    cell.font = fn(false, 9);
                    cell.alignment = al('left', 'middle');
                    cell.fill = fl(bgBase);

                    if (ci === 0) {
                        cell.font = fn(true, 9, C.azul, { name: 'Courier New' });
                        cell.alignment = al('center', 'middle');
                    } else if (ci === 15) {
                        cell.numFormat = '0.0';
                        cell.alignment = al('right', 'middle');
                    } else if (ci === 16 || ci === 17 || ci === 18) {
                        cell.numFormat = '$#,##0.00';
                        cell.alignment = al('right', 'middle');
                        if ((ci === 17 && valNovicompu > 0) || (ci === 18 && valOtra > 0)) {
                            cell.font = fn(true, 9, C.verde);
                        }
                    } else if (ci === 19) {
                        cell.font = fn(true, 8, C.verdeO);
                        cell.alignment = al('center', 'middle');
                    }
                });
            });

            // Escribir Buffer y Descargar XLSX
            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Recuento_B2B_Cobros_${new Date().toISOString().slice(0, 10)}.xlsx`;
            a.click();
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'No se pudo generar el archivo XLSX.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX (<span id="count-selected-excel">${seleccionadas.length}</span>)`;
        }
    }

    function roundToTwo(num) {
        return Math.round((num + Number.EPSILON) * 100) / 100;
    }

    function calcularValoresModal() {
        const subtotalEl = document.getElementById('swal-subtotal-val');
        if (!subtotalEl) return;
        const subtotal = parseFloat(subtotalEl.dataset.val) || 0;

        const montoIvaInput = document.getElementById('swal-iva');
        const montoIva = parseFloat(montoIvaInput ? montoIvaInput.value : 0) || 0;

        const totalConIva = subtotal + montoIva;
        const totalIvaInput = document.getElementById('swal-total-iva');
        if (totalIvaInput) totalIvaInput.value = totalConIva.toFixed(2);

        const retRenta = parseFloat(document.getElementById('swal-ret-renta').value) || 0;
        const retIva = parseFloat(document.getElementById('swal-ret-iva').value) || 0;

        const netoCalculado = totalConIva - retRenta - retIva;
        const netoInput = document.getElementById('swal-neto');
        if (netoInput) netoInput.value = (netoCalculado > 0 ? netoCalculado : 0).toFixed(2);
    }

    function abrirModalCobroLote() {
        if (seleccionadas.length === 0) return;

        const subtotal = parseFloat(document.getElementById('sum-selected').innerText) || 0;
        const empresaNombre = seleccionadas[0].empresa;
        const montoIva = roundToTwo(subtotal * 0.15);
        const totalConIva = roundToTwo(subtotal + montoIva);

        Swal.fire({
            title: 'Procesar Cobro Lote B2B',
            html: `
                <div style="text-align: left; font-size: 0.875rem; color: #0f172a;">
                    <p><strong>Empresa Lote:</strong> ${empresaNombre}</p>
                    <p><strong>Órdenes Seleccionadas:</strong> ${seleccionadas.length}</p>

                    <div id="swal-subtotal-val" data-val="${subtotal.toFixed(2)}" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1.5px solid #cbd5e1; margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem;">
                            <span>Subtotal Factura Lote:</span>
                            <strong style="font-size: 0.95rem;">$${subtotal.toFixed(2)}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-weight: 700; color: #2563eb;">(+) IVA 15% ($):</span>
                            <input type="number" step="0.01" id="swal-iva" class="swal2-input" value="${montoIva.toFixed(2)}" 
                                style="margin: 0; width: 120px; height: 34px; font-size: 0.9rem; font-weight: 700; color: #2563eb; text-align: right;" oninput="calcularValoresModal()">
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid #cbd5e1; padding-top: 6px;">
                            <span style="font-weight: 800; color: #059669;">Total con IVA ($):</span>
                            <input type="number" step="0.01" id="swal-total-iva" class="swal2-input" value="${totalConIva.toFixed(2)}" readonly 
                                style="margin: 0; width: 120px; height: 34px; font-size: 0.95rem; font-weight: 800; color: #059669; background: #ecfdf5; text-align: right;">
                        </div>
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="font-weight: 700; color: #0f172a;">Banco Destino del Pago:</label>
                        <select id="swal-banco" class="swal2-input" style="margin-top: 4px; width: 100%;">
                            <option value="Banco Pichincha Cta Cte">Banco Pichincha Cta Cte</option>
                            <option value="Banco Guayaquil Cta Cte">Banco Guayaquil Cta Cte</option>
                            <option value="Produbanco">Produbanco</option>
                        </select>
                    </div>

                    <div style="margin-top: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-weight: 700; color: #0f172a;">Retención Renta ($):</label>
                            <input type="number" step="0.01" id="swal-ret-renta" class="swal2-input" value="0.00" style="margin-top: 4px;" oninput="calcularValoresModal()">
                        </div>
                        <div>
                            <label style="font-weight: 700; color: #0f172a;">Retención IVA ($):</label>
                            <input type="number" step="0.01" id="swal-ret-iva" class="swal2-input" value="0.00" style="margin-top: 4px;" oninput="calcularValoresModal()">
                        </div>
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="font-weight: 700; color: #0f172a;">Monto Neto Depositado en Banco ($):</label>
                        <input type="number" step="0.01" id="swal-neto" class="swal2-input" value="${totalConIva.toFixed(2)}" style="margin-top: 4px; font-weight: 800; color: #1e3a8a;">
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="font-weight: 700; color: #0f172a;">Nro. Comprobante Retención SRI:</label>
                        <input type="text" id="swal-nro-ret" class="swal2-input" placeholder="Ej: 001-002-000012345" style="margin-top: 4px;">
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="font-weight: 700; color: #0f172a;">Nro. Comprobante / Transf. Bancaria:</label>
                        <input type="text" id="swal-nro-pago" class="swal2-input" placeholder="Ej: TRF-98765432" style="margin-top: 4px;">
                    </div>

                    <div style="margin-top: 14px; background: #f1f5f9; padding: 12px; border-radius: 8px; border: 1.5px dashed #3b82f6;">
                        <label style="font-weight: 700; color: #1d4ed8; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-paperclip" style="font-size: 1.1rem;"></i>Adjuntar Comprobante Pago / Transferencia (PDF o Imagen):
                        </label>
                        <input type="file" id="swal-comprobante-file" accept="application/pdf,image/*" style="margin-top: 8px; font-size: 0.85rem; width: 100%; color: #334155;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Cobro y Facturar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            preConfirm: () => {
                const banco = document.getElementById('swal-banco').value;
                const subtotalVal = parseFloat(document.getElementById('swal-subtotal-val').dataset.val) || 0;
                const ivaVal = parseFloat(document.getElementById('swal-iva').value) || 0;
                const totalIvaVal = parseFloat(document.getElementById('swal-total-iva').value) || 0;
                const neto = parseFloat(document.getElementById('swal-neto').value) || 0;
                const retRenta = parseFloat(document.getElementById('swal-ret-renta').value) || 0;
                const retIva = parseFloat(document.getElementById('swal-ret-iva').value) || 0;
                const nroRet = document.getElementById('swal-nro-ret').value;
                const nroPago = document.getElementById('swal-nro-pago').value;

                if (neto <= 0) {
                    Swal.showValidationMessage('Ingrese un monto neto recibido válido.');
                    return false;
                }

                return {
                    subtotal: subtotalVal,
                    monto_iva: ivaVal,
                    total_con_iva: totalIvaVal,
                    banco_destino: banco,
                    monto_neto_banco: neto,
                    monto_retencion_renta: retRenta,
                    monto_retencion_iva: retIva,
                    nro_retencion: nroRet,
                    nro_comprobante_pago: nroPago
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                procesarCobroBackend(empresaNombre, result.value);
            }
        });
    }

    function procesarCobroBackend(empresaNombre, payload) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('empresa_nombre', empresaNombre);
        formData.append('subtotal', payload.subtotal);
        formData.append('monto_iva', payload.monto_iva);
        formData.append('total_con_iva', payload.total_con_iva);
        formData.append('monto_neto_banco', payload.monto_neto_banco);
        formData.append('monto_retencion_renta', payload.monto_retencion_renta);
        formData.append('monto_retencion_iva', payload.monto_retencion_iva);
        formData.append('nro_retencion', payload.nro_retencion);
        formData.append('nro_comprobante_pago', payload.nro_comprobante_pago);
        formData.append('banco_destino', payload.banco_destino);
        formData.append('ordenes_json', JSON.stringify(seleccionadas));

        const fileInput = document.getElementById('swal-comprobante-file');
        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('comprobante_file', fileInput.files[0]);
        }

        fetch("{{ route('recuentob2b.procesar') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                const loteId = res.lote_id || '';
                Swal.fire({
                    title: 'Cobro B2B Procesado Exitosamente',
                    text: res.mensaje,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Ver Recibo Cliente (PDF)',
                    cancelButtonText: 'Ver Recibo Interno (PDF)',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#0f172a'
                }).then((choice) => {
                    if (choice.isConfirmed && loteId) {
                        window.open("/contabilidad/recuento-b2b/recibo-cliente/" + loteId, '_blank');
                        location.reload();
                    } else if (choice.dismiss === Swal.DismissReason.cancel && loteId) {
                        window.open("/contabilidad/recuento-b2b/recibo-interno/" + loteId, '_blank');
                        location.reload();
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Error', res.error || 'No se pudo procesar el cobro del lote.', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión al procesar.', 'error'));
    }
</script>
@endsection
