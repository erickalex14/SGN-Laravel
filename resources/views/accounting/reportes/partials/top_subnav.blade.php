<style>
    .rep-subnav-container {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        border-bottom: 2.5px solid #cbd5e1;
        padding-bottom: 2px;
        overflow-x: auto;
    }
    .rep-subnav-link {
        background: #f1f5f9;
        color: #475569;
        border: 1.5px solid #cbd5e1;
        border-bottom: none;
        padding: 12px 22px;
        border-radius: 10px 10px 0 0;
        font-size: 0.925rem;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .rep-subnav-link:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .rep-subnav-link.active {
        background: #ffffff;
        color: #2563eb;
        border-color: #2563eb #2563eb #ffffff;
        margin-bottom: -2.5px;
        box-shadow: 0 -4px 12px rgba(37, 99, 235, 0.1);
    }
</style>

<div class="rep-subnav-container">
    <a href="{{ route('contabilidad.reportes.kpis') }}" class="rep-subnav-link {{ request()->routeIs('contabilidad.reportes.kpis') || request()->routeIs('contabilidad.reportes') ? 'active' : '' }}">
        <i class="bi bi-pie-chart-fill me-1" style="color: #2563eb;"></i>Dashboard KPIs & Balances
    </a>
    <a href="{{ route('contabilidad.reportes.caja_general') }}" class="rep-subnav-link {{ request()->routeIs('contabilidad.reportes.caja_general') ? 'active' : '' }}">
        <i class="bi bi-cash-stack me-1" style="color: #059669;"></i>Reporte Caja General & Arqueos
    </a>
    <a href="{{ route('contabilidad.reportes.caja_chica') }}" class="rep-subnav-link {{ request()->routeIs('contabilidad.reportes.caja_chica') ? 'active' : '' }}">
        <i class="bi bi-wallet2 me-1" style="color: #d97706;"></i>Reporte Cajas Chicas por Sucursal
    </a>
    <a href="{{ route('contabilidad.reportes.b2b') }}" class="rep-subnav-link {{ request()->routeIs('contabilidad.reportes.b2b') ? 'active' : '' }}">
        <i class="bi bi-building-check me-1" style="color: #7c3aed;"></i>Reporte Recuento B2B & Bancos
    </a>
</div>
