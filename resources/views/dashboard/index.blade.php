@extends('layouts.app')

@section('titulo', 'Dashboard')

@push('css_adicional')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.dash { padding: 32px 28px; max-width: 1440px; margin: 0 auto; font-family: 'Plus Jakarta Sans', sans-serif; }
.dash-hdr {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 26px 32px;
    margin-bottom: 28px;
    position: relative;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.02), 0 2px 4px -1px rgba(15, 23, 42, 0.01);
}
.dash-hdr h1 { margin: 0 0 6px; font-size: 30px; font-weight: 900; color: #0f172a; font-family: 'Outfit', sans-serif; letter-spacing: -0.03em; }
.dash-hdr p { margin: 0; color: #64748b; font-size: 15px; font-weight: 500; }
.scope-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 30px; padding: 6px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
.scope-badge.global { background: #ecfdf5; color: #15803d; border-color: #bbf7d0; }
.kpi-grid { display: grid; gap: 18px; margin-bottom: 24px; }
.kpi-grid.admin { grid-template-columns: repeat(6, minmax(0, 1fr)); }
.kpi-grid.tech { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.kpi-card { background: #ffffff; border-radius: 18px; border: 1.5px solid #e2e8f0; padding: 24px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.01), 0 2px 4px -2px rgba(15, 23, 42, 0.02); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
.kpi-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: transparent; transition: background 0.3s; }
.kpi-card:hover { transform: translateY(-5px); box-shadow: 0 16px 28px -8px rgba(15, 23, 42, 0.08); border-color: #cbd5e1; }
.kpi-card:hover::before { background: #2563eb; }
.kpi-card.cas-card::before { background: #0891b2; }
.kpi-card.cas-card:hover::before { background: #06b6d4; }
.kpi-card.cas-card:hover { border-color: #0891b2; }
.kpi-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.kpi-card:hover .kpi-icon { transform: scale(1.1) rotate(3deg); }
.kpi-icon.blue { background: rgba(59, 130, 246, 0.08); color: #2563eb; }
.kpi-icon.green { background: rgba(16, 185, 129, 0.08); color: #10b981; }
.kpi-icon.amber { background: rgba(245, 158, 11, 0.08); color: #d97706; }
.kpi-icon.purple { background: rgba(139, 92, 246, 0.08); color: #7c3aed; }
.kpi-icon.rose { background: rgba(244, 63, 94, 0.08); color: #e11d48; }
.kpi-icon.cyan { background: rgba(6, 182, 212, 0.08); color: #0891b2; }
.kpi-val { font-size: 28px; font-weight: 900; color: #0f172a; line-height: 1; font-family: 'Outfit', sans-serif; letter-spacing: -0.03em; }
.kpi-lbl { font-size: 11px; color: #64748b; margin-top: 6px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; }
.charts-row { display: grid; gap: 20px; margin-bottom: 20px; }
.charts-row.cols-3 { grid-template-columns: 1.2fr 1fr 1fr; }
.charts-row.cols-2 { grid-template-columns: 1.8fr 1.2fr; }
.chart-card { background: #ffffff; border-radius: 18px; border: 1.5px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.01), 0 2px 4px -2px rgba(15, 23, 42, 0.02); transition: box-shadow 0.3s, border-color 0.3s; }
.chart-card:hover { box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.04); border-color: #cbd5e1; }
.chart-title { font-size: 14px; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.05em; font-family: 'Outfit', sans-serif; }
.chart-title i { color: #2563eb; font-size: 18px; }
.res-wrap { background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 18px; border: 1.5px solid #e2e8f0; padding: 22px 26px; margin-bottom: 26px; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.01), 0 2px 4px -2px rgba(15, 23, 42, 0.02); }
.res-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.res-val { font-size: 28px; font-weight: 900; font-family: 'Outfit', sans-serif; letter-spacing: -0.02em; }
.res-bar-bg { background: #e2e8f0; border-radius: 999px; height: 12px; overflow: hidden; }
.res-bar-fg { height: 12px; border-radius: 999px; width: 0; transition: width .4s ease-out; }
.tec-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.tec-table th { text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; padding: 12px 10px; font-weight: 700; letter-spacing: 0.05em; }
.tec-table td { border-bottom: 1px solid #f1f5f9; padding: 14px 10px; color: #334155; font-weight: 500; transition: background-color 0.2s; }
.tec-table tr:hover td { background-color: #f8fafc; }
.badge-num { display: inline-block; padding: 4px 10px; border-radius: 8px; font-size: 11.5px; font-weight: 700; text-align: center; min-width: 24px; }
.badge-total { background: #eff6ff; color: #1d4ed8; }
.badge-ok { background: #ecfdf5; color: #15803d; }
.badge-pending { background: #fffbeb; color: #b45309; }
.tec-bar-wrap { width: 100px; height: 8px; background: #e2e8f0; border-radius: 6px; overflow: hidden; }
.tec-bar { height: 8px; background: linear-gradient(90deg, #3b82f6, #60a5fa); border-radius: 6px; }
.tec-progress { display: flex; align-items: center; gap: 10px; min-width: 152px; }
.tec-progress-label { font-size: 11px; font-weight: 700; color: #334155; min-width: 38px; }
.chart-dom-wrap { position: relative; width: 100%; overflow: hidden; }
.dash-error { display: none; margin-bottom: 18px; background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 12px 16px; font-size: 13.5px; font-weight: 600; }
@media (max-width: 1280px) { .kpi-grid.admin { grid-template-columns: repeat(3, minmax(0, 1fr)); } .charts-row.cols-3 { grid-template-columns: 1fr; } .charts-row.cols-2 { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .kpi-grid.admin, .kpi-grid.tech { grid-template-columns: repeat(2, minmax(0, 1fr)); } .dash { padding: 18px 16px; } }
@media (max-width: 480px) { .kpi-grid.admin, .kpi-grid.tech { grid-template-columns: 1fr; } }
</style>
@endpush

@section('contenido')
@php
    $hora = (int) now('America/Guayaquil')->format('H');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $nombre = session('nombre') ?? session('usuario') ?? 'Usuario';
@endphp
<div class="dash">
    <div class="dash-hdr">
        <h1>{{ $saludo }}, {{ $nombre }}</h1>
        <p>Panel operativo con indicadores y gráficos en tiempo real.</p>
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
            <div class="kpi-card"><div class="kpi-icon green"><i class="bi bi-check2-circle"></i></div><div><div class="kpi-val" id="t-entregadas">0</div><div class="kpi-lbl">Resueltas</div></div></div>
        </div>

        <div class="res-wrap">
            <div class="res-top">
                <span style="font-size:13px;font-weight:600;color:#334155;">Tasa de resolución</span>
                <span class="res-val" id="t-tasa">0%</span>
            </div>
            <div class="res-bar-bg"><div class="res-bar-fg" id="t-tasa-bar"></div></div>
        </div>

        <div class="charts-row cols-2">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-graph-up"></i>Órdenes últimos 7 días</div>
                <div class="chart-dom-wrap" style="height: 200px;"><canvas id="t-chart-dias"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-hdd-stack"></i>Tipos de equipo</div>
                <div class="chart-dom-wrap" style="height: 220px;"><canvas id="t-chart-equipos"></canvas></div>
            </div>
        </div>

        <div class="charts-row" style="grid-template-columns:1fr;">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-bar-chart"></i>Historial mensual (6 meses)</div>
                <div class="chart-dom-wrap" style="height: 200px;"><canvas id="t-chart-mensual"></canvas></div>
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
            <div class="kpi-card cas-card"><div class="kpi-icon cyan"><i class="bi bi-shield-check"></i></div><div><div class="kpi-val" id="g-cas">0</div><div class="kpi-lbl" title="Centros de Asistencia Autorizados / Garantías">Centros CAS (OT)</div></div></div>
        </div>

        <div class="charts-row cols-3">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-graph-up"></i>Órdenes últimos 7 días</div>
                <div class="chart-dom-wrap" style="height: 200px;"><canvas id="g-chart-dias"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-pie-chart"></i>Estado de órdenes</div>
                <div class="chart-dom-wrap" style="height: 220px;"><canvas id="g-chart-estados"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-hdd-stack"></i>Tipos de equipo</div>
                <div class="chart-dom-wrap" style="height: 220px;"><canvas id="g-chart-equipos"></canvas></div>
            </div>
        </div>

        <div class="charts-row cols-2">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-person-gear"></i>Rendimiento por técnico</div>
                <div style="overflow:auto;">
                    <table class="tec-table">
                        <thead>
                            <tr><th>Técnico</th><th>Total</th><th>Resueltas</th><th>Pendientes</th><th>Resolución</th></tr>
                        </thead>
                        <tbody id="g-tec-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-box-seam"></i>Estado de repuestos</div>
                <div class="chart-dom-wrap" style="height: 220px;"><canvas id="g-chart-repuestos"></canvas></div>
            </div>
        </div>

        <div class="charts-row cols-2" id="g-sucursales-cas-row" style="display:none;">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-shop-window"></i>Top sucursales por órdenes</div>
                <div class="chart-dom-wrap" style="height: 180px;"><canvas id="g-chart-sucursales"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-shield-check"></i>Distribución de Órdenes en CAS</div>
                <div class="chart-dom-wrap" style="height: 180px;"><canvas id="g-chart-cas"></canvas></div>
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

function chart(id, configFactory) {
    if (charts[id]) charts[id].destroy();
    const canvas = $(id);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const config = typeof configFactory === 'function' ? configFactory(ctx, canvas) : configFactory;
    charts[id] = new Chart(canvas, config);
}

function chartColors() {
    return ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#f43f5e', '#06b6d4', '#64748b'];
}

function getVerticalGradient(ctx, colorStart, colorEnd, height = 180) {
    const gradient = ctx.createLinearGradient(0, 0, 0, height);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

function getHorizontalGradient(ctx, colorStart, colorEnd, width = 300) {
    const gradient = ctx.createLinearGradient(0, 0, width, 0);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

// Configuración global de Chart.js
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.font.weight = 500;
    Chart.defaults.color = '#64748b';
    
    // Tooltips premium
    Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
    Chart.defaults.plugins.tooltip.titleFont = { family: "'Outfit', sans-serif", size: 13, weight: 700 };
    Chart.defaults.plugins.tooltip.bodyFont = { family: "'Plus Jakarta Sans', sans-serif", size: 12 };
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 10;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxWidth = 8;
    Chart.defaults.plugins.tooltip.boxHeight = 8;
    Chart.defaults.plugins.tooltip.boxPadding = 4;
    Chart.defaults.plugins.tooltip.usePointStyle = true;
}

function renderTech(data) {
    $('sec-tech').style.display = 'block';
    $('sec-admin').style.display = 'none';

    const k = data.kpis || {};
    setText('t-mis-ordenes', k.mis_ordenes ?? 0);
    setText('t-pendientes', k.pendientes ?? 0);
    setText('t-en-proceso', k.en_proceso ?? 0);
    setText('t-entregadas', k.resueltas ?? k.entregadas ?? 0);

    const tasa = Number(k.tasa_resolucion ?? 0);
    const color = tasa >= 75 ? '#10b981' : (tasa >= 50 ? '#f59e0b' : '#ef4444');
    setText('t-tasa', `${tasa}%`);
    $('t-tasa').style.color = color;
    $('t-tasa-bar').style.width = `${Math.max(0, Math.min(100, tasa))}%`;
    $('t-tasa-bar').style.background = `linear-gradient(90deg, ${color}, #60a5fa)`;

    const dias = data.charts?.dias || { labels: [], data: [] };
    chart('t-chart-dias', (ctx) => {
        const gradient = getVerticalGradient(ctx, 'rgba(37, 99, 235, 0.22)', 'rgba(37, 99, 235, 0)', 160);
        return {
            type: 'line',
            data: {
                labels: dias.labels,
                datasets: [{
                    label: 'Órdenes',
                    data: dias.data,
                    borderColor: '#2563eb',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawTicks: false },
                        border: { dash: [5, 5], display: false },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        };
    });

    const equipos = data.charts?.equipos || { labels: [], data: [] };
    chart('t-chart-equipos', {
        type: 'doughnut',
        data: {
            labels: equipos.labels,
            datasets: [{
                data: equipos.data,
                backgroundColor: chartColors(),
                borderWidth: 3,
                borderColor: '#ffffff',
                borderRadius: 6,
                spacing: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 14,
                        color: '#475569',
                        font: { size: 11, weight: 600 }
                    }
                }
            }
        }
    });

    const mensual = data.charts?.mensual || { labels: [], data: [] };
    chart('t-chart-mensual', (ctx) => {
        const gradient = getVerticalGradient(ctx, '#3b82f6', 'rgba(59, 130, 246, 0.3)', 120);
        return {
            type: 'bar',
            data: {
                labels: mensual.labels,
                datasets: [{
                    label: 'Órdenes',
                    data: mensual.data,
                    backgroundColor: gradient,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawTicks: false },
                        border: { dash: [5, 5], display: false },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        };
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
    setText('g-cas', `${k.cas_totales ?? 0} (${k.ordenes_cas ?? 0})`);

    const tecs = Array.isArray(data.tecnicos) ? data.tecnicos : [];
    $('g-tec-body').innerHTML = tecs.length
        ? tecs.map(t => {
            const pct = Math.max(0, Math.min(100, Number(t.tasa_resolucion || 0)));
            return `<tr>
                <td>${escapeHtml(t.nombre || '-')}</td>
                <td><span class="badge-num badge-total">${Number(t.total || 0)}</span></td>
                <td><span class="badge-num badge-ok">${Number(t.resueltas || t.entregadas || 0)}</span></td>
                <td><span class="badge-num badge-pending">${Number(t.pendientes || 0)}</span></td>
                <td><div class="tec-progress"><div class="tec-bar-wrap"><div class="tec-bar" style="width:${pct}%"></div></div><span class="tec-progress-label">${pct}%</span></div></td>
            </tr>`;
        }).join('')
        : '<tr><td colspan="5" style="color:#94a3b8;text-align:center;padding:14px;">Sin datos de técnicos.</td></tr>';

    const dias = data.charts?.dias || { labels: [], data: [] };
    chart('g-chart-dias', (ctx) => {
        const gradient = getVerticalGradient(ctx, 'rgba(37, 99, 235, 0.22)', 'rgba(37, 99, 235, 0)', 160);
        return {
            type: 'line',
            data: {
                labels: dias.labels,
                datasets: [{
                    label: 'Órdenes',
                    data: dias.data,
                    borderColor: '#2563eb',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawTicks: false },
                        border: { dash: [5, 5], display: false },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        };
    });

    const estados = data.charts?.estados || { labels: [], data: [], colors: [] };
    chart('g-chart-estados', {
        type: 'doughnut',
        data: {
            labels: estados.labels,
            datasets: [{
                data: estados.data,
                backgroundColor: estados.colors?.length ? estados.colors : chartColors(),
                borderWidth: 3,
                borderColor: '#ffffff',
                borderRadius: 6,
                spacing: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 14,
                        color: '#475569',
                        font: { size: 11, weight: 600 }
                    }
                }
            }
        }
    });

    const equipos = data.charts?.equipos || { labels: [], data: [] };
    chart('g-chart-equipos', (ctx) => {
        const gradient = getVerticalGradient(ctx, '#7c3aed', 'rgba(124, 58, 237, 0.3)', 160);
        return {
            type: 'bar',
            data: {
                labels: equipos.labels,
                datasets: [{
                    label: 'Equipos',
                    data: equipos.data,
                    backgroundColor: gradient,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawTicks: false },
                        border: { dash: [5, 5], display: false },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        };
    });

    const repuestos = data.charts?.repuestos || { labels: [], data: [] };
    chart('g-chart-repuestos', {
        type: 'doughnut',
        data: {
            labels: repuestos.labels,
            datasets: [{
                data: repuestos.data,
                backgroundColor: ['#64748b', '#10b981', '#f43f5e', '#f59e0b', '#3b82f6'],
                borderWidth: 3,
                borderColor: '#ffffff',
                borderRadius: 6,
                spacing: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 14,
                        color: '#475569',
                        font: { size: 11, weight: 600 }
                    }
                }
            }
        }
    });

    const sucursales = data.charts?.sucursales || { labels: [], data: [] };
    const cas = data.charts?.cas || { labels: [], data: [] };
    const row = $('g-sucursales-cas-row');
    if (row) {
        row.style.display = 'grid';
        chart('g-chart-sucursales', (ctx, canvas) => {
            const gradient = getHorizontalGradient(ctx, '#2563eb', 'rgba(37, 99, 235, 0.3)', canvas.width || 400);
            return {
                type: 'bar',
                data: {
                    labels: sucursales.labels,
                    datasets: [{
                        label: 'Órdenes',
                        data: sucursales.data,
                        backgroundColor: gradient,
                        borderRadius: 6,
                        barThickness: 12,
                        maxBarThickness: 16
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawTicks: false },
                            border: { display: false },
                            ticks: { stepSize: 1 }
                        },
                        y: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            };
        });
        chart('g-chart-cas', (ctx, canvas) => {
            const gradient = getHorizontalGradient(ctx, '#0891b2', 'rgba(8, 145, 178, 0.3)', canvas.width || 400);
            return {
                type: 'bar',
                data: {
                    labels: cas.labels,
                    datasets: [{
                        label: 'Órdenes',
                        data: cas.data,
                        backgroundColor: gradient,
                        borderRadius: 6,
                        barThickness: 12,
                        maxBarThickness: 16
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawTicks: false },
                            border: { display: false },
                            ticks: { stepSize: 1 }
                        },
                        y: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            };
        });
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
            throw new Error(res.error || 'No se pudieron cargar las métricas');
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
