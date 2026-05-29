@extends('layouts.app')

@section('titulo', 'Dashboard')

@push('css_adicional')
<style>
.dash { padding: 28px 24px; max-width: 1400px; margin: 0 auto; }
.dash-hdr { margin-bottom: 22px; }
.dash-hdr h1 { margin: 0 0 6px; font-size: 24px; font-weight: 800; color: #0f172a; }
.dash-hdr p { margin: 0; color: #64748b; font-size: 14px; }
.scope-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600; }
.scope-badge.global { background: #ecfdf5; color: #166534; border-color: #bbf7d0; }
.kpi-grid { display: grid; gap: 16px; margin-bottom: 20px; }
.kpi-grid.admin { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.kpi-grid.tech { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.kpi-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 16px; display: flex; align-items: center; gap: 12px; }
.kpi-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.kpi-icon.blue { background: #eff6ff; color: #2563eb; }
.kpi-icon.green { background: #ecfdf5; color: #16a34a; }
.kpi-icon.amber { background: #fffbeb; color: #d97706; }
.kpi-icon.purple { background: #f5f3ff; color: #7c3aed; }
.kpi-icon.rose { background: #fff1f2; color: #e11d48; }
.kpi-val { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1; }
.kpi-lbl { font-size: 12px; color: #64748b; margin-top: 4px; text-transform: uppercase; }
.charts-row { display: grid; gap: 16px; margin-bottom: 16px; }
.charts-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
.charts-row.cols-2 { grid-template-columns: 2fr 1fr; }
.chart-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 16px; }
.chart-title { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
.chart-title i { color: #2563eb; }
.res-wrap { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin-bottom: 16px; }
.res-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.res-val { font-size: 22px; font-weight: 800; }
.res-bar-bg { background: #f1f5f9; border-radius: 999px; height: 8px; overflow: hidden; }
.res-bar-fg { height: 8px; border-radius: 999px; width: 0; transition: width .35s; }
.tec-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.tec-table th { text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; padding: 8px; }
.tec-table td { border-bottom: 1px solid #f8fafc; padding: 10px 8px; color: #1e293b; }
.badge-num { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.badge-total { background: #eff6ff; color: #1d4ed8; }
.badge-ok { background: #ecfdf5; color: #166534; }
.badge-pending { background: #fffbeb; color: #92400e; }
.tec-bar-wrap { width: 100px; height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
.tec-bar { height: 6px; background: #2563eb; border-radius: 4px; }
.dash-error { display: none; margin-bottom: 14px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 10px 12px; font-size: 13px; }
@media (max-width: 1100px) { .kpi-grid.admin { grid-template-columns: repeat(3, minmax(0, 1fr)); } .charts-row.cols-3 { grid-template-columns: 1fr 1fr; } .charts-row.cols-2 { grid-template-columns: 1fr; } }
@media (max-width: 700px) { .kpi-grid.admin, .kpi-grid.tech, .charts-row.cols-3 { grid-template-columns: 1fr; } .dash { padding: 14px 10px; } }
</style>
@endpush

@section('contenido')
@php
    $hora = (int) now('America/Guayaquil')->format('H');
    $saludo = $hora < 12 ? 'Buenos dias' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $nombre = session('nombre') ?? session('usuario') ?? 'Usuario';
@endphp
<div class="dash">
    <div class="dash-hdr">
        <h1>{{ $saludo }}, {{ $nombre }}</h1>
        <p>Panel operativo con indicadores y graficos en tiempo real.</p>
        @if($esSuperadmin)
            <span class="scope-badge global"><i class="bi bi-globe2"></i>Vista global de sucursales</span>
        @else
            <span class="scope-badge"><i class="bi bi-geo-alt"></i>Vista de tu sucursal</span>
        @endif
    </div>

    <div class="dash-error" id="dash-error"></div>

    <section id="sec-tech" style="display:none;">
        <div class="kpi-grid tech">
            <div class="kpi-card"><div class="kpi-icon blue"><i class="bi bi-clipboard-check"></i></div><div><div class="kpi-val" id="t-mis-ordenes">0</div><div class="kpi-lbl">Mis ordenes</div></div></div>
            <div class="kpi-card"><div class="kpi-icon amber"><i class="bi bi-hourglass-split"></i></div><div><div class="kpi-val" id="t-pendientes">0</div><div class="kpi-lbl">Pendientes</div></div></div>
            <div class="kpi-card"><div class="kpi-icon purple"><i class="bi bi-tools"></i></div><div><div class="kpi-val" id="t-en-proceso">0</div><div class="kpi-lbl">En proceso</div></div></div>
            <div class="kpi-card"><div class="kpi-icon green"><i class="bi bi-check2-circle"></i></div><div><div class="kpi-val" id="t-entregadas">0</div><div class="kpi-lbl">Entregadas</div></div></div>
        </div>

        <div class="res-wrap">
            <div class="res-top">
                <span style="font-size:13px;font-weight:600;color:#334155;">Tasa de resolucion</span>
                <span class="res-val" id="t-tasa">0%</span>
            </div>
            <div class="res-bar-bg"><div class="res-bar-fg" id="t-tasa-bar"></div></div>
        </div>

        <div class="charts-row cols-2">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-graph-up"></i>Ordenes ultimos 7 dias</div>
                <canvas id="t-chart-dias" height="160"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-hdd-stack"></i>Tipos de equipo</div>
                <canvas id="t-chart-equipos" height="160"></canvas>
            </div>
        </div>

        <div class="charts-row" style="grid-template-columns:1fr;">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-bar-chart"></i>Historial mensual (6 meses)</div>
                <canvas id="t-chart-mensual" height="90"></canvas>
            </div>
        </div>
    </section>

    <section id="sec-admin" style="display:none;">
        <div class="kpi-grid admin">
            <div class="kpi-card"><div class="kpi-icon blue"><i class="bi bi-clipboard-check"></i></div><div><div class="kpi-val" id="g-total">0</div><div class="kpi-lbl">Ordenes totales</div></div></div>
            <div class="kpi-card"><div class="kpi-icon green"><i class="bi bi-calendar-check"></i></div><div><div class="kpi-val" id="g-hoy">0</div><div class="kpi-lbl">Ingresadas hoy</div></div></div>
            <div class="kpi-card"><div class="kpi-icon amber"><i class="bi bi-tools"></i></div><div><div class="kpi-val" id="g-tecnicos">0</div><div class="kpi-lbl">Tecnicos</div></div></div>
            <div class="kpi-card"><div class="kpi-icon purple"><i class="bi bi-people"></i></div><div><div class="kpi-val" id="g-clientes">0</div><div class="kpi-lbl">Clientes</div></div></div>
            <div class="kpi-card"><div class="kpi-icon rose"><i class="bi bi-shop"></i></div><div><div class="kpi-val" id="g-sucursales">0</div><div class="kpi-lbl">Sucursales</div></div></div>
        </div>

        <div class="charts-row cols-3">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-graph-up"></i>Ordenes ultimos 7 dias</div>
                <canvas id="g-chart-dias" height="160"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-pie-chart"></i>Estado de ordenes</div>
                <canvas id="g-chart-estados" height="160"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-hdd-stack"></i>Tipos de equipo</div>
                <canvas id="g-chart-equipos" height="160"></canvas>
            </div>
        </div>

        <div class="charts-row cols-2">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-person-gear"></i>Rendimiento por tecnico</div>
                <div style="overflow:auto;">
                    <table class="tec-table">
                        <thead>
                            <tr><th>Tecnico</th><th>Total</th><th>Entregadas</th><th>Pendientes</th><th>Progreso</th></tr>
                        </thead>
                        <tbody id="g-tec-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-box-seam"></i>Estado de repuestos</div>
                <canvas id="g-chart-repuestos" height="180"></canvas>
            </div>
        </div>

        <div class="charts-row" id="g-sucursales-row" style="grid-template-columns:1fr; display:none;">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-shop-window"></i>Top sucursales por ordenes</div>
                <canvas id="g-chart-sucursales" height="90"></canvas>
            </div>
        </div>
    </section>
</div>
@endsection

@push('js_adicional')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const DASH_URL = '{{ route("dashboard.metricas") }}';
const charts = {};

function $(id) { return document.getElementById(id); }

function setText(id, value) {
    const el = $(id);
    if (el) el.textContent = value;
}

function showError(message) {
    const box = $('dash-error');
    if (!box) return;
    box.textContent = message;
    box.style.display = 'block';
}

function hideError() {
    const box = $('dash-error');
    if (!box) return;
    box.style.display = 'none';
}

function chart(id, config) {
    if (charts[id]) charts[id].destroy();
    const canvas = $(id);
    if (!canvas) return;
    charts[id] = new Chart(canvas, config);
}

function chartColors() {
    return ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#f43f5e', '#06b6d4', '#64748b'];
}

function renderTech(data) {
    $('sec-tech').style.display = 'block';
    $('sec-admin').style.display = 'none';

    const k = data.kpis || {};
    setText('t-mis-ordenes', k.mis_ordenes ?? 0);
    setText('t-pendientes', k.pendientes ?? 0);
    setText('t-en-proceso', k.en_proceso ?? 0);
    setText('t-entregadas', k.entregadas ?? 0);

    const tasa = Number(k.tasa_resolucion ?? 0);
    const color = tasa >= 75 ? '#10b981' : (tasa >= 50 ? '#f59e0b' : '#ef4444');
    setText('t-tasa', `${tasa}%`);
    $('t-tasa').style.color = color;
    $('t-tasa-bar').style.width = `${Math.max(0, Math.min(100, tasa))}%`;
    $('t-tasa-bar').style.background = color;

    const dias = data.charts?.dias || { labels: [], data: [] };
    chart('t-chart-dias', {
        type: 'line',
        data: { labels: dias.labels, datasets: [{ data: dias.data, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.08)', pointRadius: 4, borderWidth: 2.5, fill: true, tension: .35 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    const equipos = data.charts?.equipos || { labels: [], data: [] };
    chart('t-chart-equipos', {
        type: 'doughnut',
        data: { labels: equipos.labels, datasets: [{ data: equipos.data, backgroundColor: chartColors(), borderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: true, cutout: '60%' }
    });

    const mensual = data.charts?.mensual || { labels: [], data: [] };
    chart('t-chart-mensual', {
        type: 'bar',
        data: { labels: mensual.labels, datasets: [{ data: mensual.data, backgroundColor: '#2563eb', borderRadius: 6 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
}

function renderAdmin(data) {
    $('sec-tech').style.display = 'none';
    $('sec-admin').style.display = 'block';

    const k = data.kpis || {};
    setText('g-total', k.ordenes_totales ?? 0);
    setText('g-hoy', k.ordenes_hoy ?? 0);
    setText('g-tecnicos', k.tecnicos_activos ?? 0);
    setText('g-clientes', k.clientes ?? 0);
    setText('g-sucursales', k.sucursales ?? 0);

    const tecs = Array.isArray(data.tecnicos) ? data.tecnicos : [];
    const maxTotal = tecs.length ? Math.max(...tecs.map(t => Number(t.total || 0))) : 1;
    $('g-tec-body').innerHTML = tecs.length
        ? tecs.map(t => {
            const pct = maxTotal > 0 ? Math.round((Number(t.total || 0) / maxTotal) * 100) : 0;
            return `<tr>
                <td>${escapeHtml(t.nombre || '-')}</td>
                <td><span class="badge-num badge-total">${Number(t.total || 0)}</span></td>
                <td><span class="badge-num badge-ok">${Number(t.entregadas || 0)}</span></td>
                <td><span class="badge-num badge-pending">${Number(t.pendientes || 0)}</span></td>
                <td><div class="tec-bar-wrap"><div class="tec-bar" style="width:${pct}%"></div></div></td>
            </tr>`;
        }).join('')
        : '<tr><td colspan="5" style="color:#94a3b8;text-align:center;padding:14px;">Sin datos de tecnicos.</td></tr>';

    const dias = data.charts?.dias || { labels: [], data: [] };
    chart('g-chart-dias', {
        type: 'line',
        data: { labels: dias.labels, datasets: [{ data: dias.data, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.08)', pointRadius: 4, borderWidth: 2.5, fill: true, tension: .35 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    const estados = data.charts?.estados || { labels: [], data: [], colors: [] };
    chart('g-chart-estados', {
        type: 'doughnut',
        data: { labels: estados.labels, datasets: [{ data: estados.data, backgroundColor: estados.colors?.length ? estados.colors : chartColors(), borderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: true, cutout: '60%' }
    });

    const equipos = data.charts?.equipos || { labels: [], data: [] };
    chart('g-chart-equipos', {
        type: 'bar',
        data: { labels: equipos.labels, datasets: [{ data: equipos.data, backgroundColor: chartColors(), borderRadius: 6 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    const repuestos = data.charts?.repuestos || { labels: [], data: [] };
    chart('g-chart-repuestos', {
        type: 'doughnut',
        data: { labels: repuestos.labels, datasets: [{ data: repuestos.data, backgroundColor: ['#94a3b8', '#10b981', '#ef4444', '#f59e0b', '#3b82f6'], borderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: true, cutout: '55%' }
    });

    const sucursales = data.charts?.sucursales || { labels: [], data: [] };
    const row = $('g-sucursales-row');
    if (Array.isArray(sucursales.labels) && sucursales.labels.length > 0) {
        row.style.display = 'grid';
        chart('g-chart-sucursales', {
            type: 'bar',
            data: { labels: sucursales.labels, datasets: [{ data: sucursales.data, backgroundColor: '#2563eb', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    } else {
        row.style.display = 'none';
        if (charts['g-chart-sucursales']) {
            charts['g-chart-sucursales'].destroy();
            delete charts['g-chart-sucursales'];
        }
    }
}

function escapeHtml(input) {
    return String(input ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

async function cargarDashboard() {
    try {
        const response = await fetch(DASH_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const res = await response.json();
        if (!response.ok || !res.ok || !res.data || !res.data.dashboard) {
            throw new Error(res.error || 'No se pudieron cargar las metricas');
        }

        hideError();
        const data = res.data.dashboard;
        if (data.modo === 'tecnico') {
            renderTech(data);
        } else {
            renderAdmin(data);
        }
    } catch (error) {
        showError(error.message || 'Error al cargar dashboard');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    cargarDashboard();
    setInterval(cargarDashboard, 60000);
});
</script>
@endpush

@include('layouts.asistente_widget')

