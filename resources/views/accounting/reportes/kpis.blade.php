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
</style>

<div class="rep-container">
    <div class="rep-header">
        <div>
            <h1 class="rep-title">Dashboard KPIs & Balances Consolidados</h1>
            <div class="rep-subtitle">Indicadores clave de rendimiento financiero y saldos de cajas sin duplicación bancaria</div>
        </div>
        <div>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir Reporte PDF
            </button>
        </div>
    </div>

    <!-- NAVEGACIÓN SUPERIOR DE SUBPÁGINAS -->
    @include('accounting.reportes.partials.top_subnav')

    <!-- FILTROS -->
    <form method="GET" action="{{ route('contabilidad.reportes.kpis') }}" class="filter-bar">
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
        @endif

        @if($sucursalFiltro !== '' || $fechaInicio !== \Carbon\Carbon::now()->startOfYear()->format('Y-m-d'))
            <a href="{{ route('contabilidad.reportes.kpis') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline; font-size: 0.85rem;">
                Limpiar filtros
            </a>
        @endif
    </form>

    <!-- TARJETAS DE BALANCES KPIS CON LÓGICA ANTI-DUPLICACIÓN -->
    <div class="kpi-grid">
        <div class="kpi-card" style="border-top: 4px solid #059669;">
            <div class="kpi-title"><i class="bi bi-cash-coin" style="color: #059669; font-size: 1.1rem;"></i>Efectivo Pendiente en Ventanilla</div>
            <div class="kpi-value" style="color: #059669;">${{ number_format($balanceCajaGeneralPendiente, 2) }}</div>
            <div class="kpi-subtext">Dinero físico en ventanilla aún no depositado en banco</div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid #2563eb;">
            <div class="kpi-title"><i class="bi bi-bank" style="color: #2563eb; font-size: 1.1rem;"></i>Caja Bancos (Fondos Acreditados)</div>
            <div class="kpi-value" style="color: #2563eb;">${{ number_format($balanceCajaBancos, 2) }}</div>
            <div class="kpi-subtext">Depósitos Ventanilla (${{ number_format($montoCobrosEfectivoDepositado, 2) }}) + Transf. (${{ number_format($montoCobrosBancosDirectos, 2) }}) + Dep. B2B (${{ number_format($netoBancoB2B, 2) }})</div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid #7c3aed;">
            <div class="kpi-title"><i class="bi bi-file-earmark-spreadsheet" style="color: #7c3aed; font-size: 1.1rem;"></i>Facturación B2B (con IVA)</div>
            <div class="kpi-value" style="color: #7c3aed;">${{ number_format($totalConIvaB2B, 2) }}</div>
            <div class="kpi-subtext">{{ $totalB2BCant }} lotes B2B. Ret. Renta: ${{ number_format($retRentaB2B, 2) }} | Ret. IVA: ${{ number_format($retIvaB2B, 2) }}</div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid #d97706;">
            <div class="kpi-title"><i class="bi bi-receipt-cutoff" style="color: #d97706; font-size: 1.1rem;"></i>Cajas Chicas (Fondo Independiente)</div>
            <div class="kpi-value" style="color: #d97706;">${{ number_format($balanceCajaChica, 2) }}</div>
            <div class="kpi-subtext">Gastos ejecutados en compras menores (fondo rotativo separado)</div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="bi bi-graph-up-arrow" style="color: #059669;"></i>
                <span>Total de Recaudación / Ingresos Reales ({{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }})</span>
            </div>
            <div style="font-size: 1.2rem; font-weight: 800; color: #059669;">
                Recaudación Total Ingresos: ${{ number_format($balanceNetoGlobal, 2) }}
            </div>
        </div>
        <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6;">
            El <strong>Total de Recaudación / Ingresos Reales</strong> consolida únicamente el dinero físico en ventanilla pendiente de depósito de Caja General, más la totalidad de los fondos ya acreditados en cuentas de Caja Bancos (que incluyen transferencias directas, depósitos bancarios de ventanilla y recuentos B2B). Esto garantiza <strong>cero duplicidad contable</strong>, ya que el dinero cobrado en efectivo que fue depositado en la cuenta corriente pasa a ser parte de Caja Bancos y no se suma dos veces.
        </p>
    </div>
</div>
@endsection
