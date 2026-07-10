@extends('layouts.app')
@section('titulo', 'Reportes y Balances - Caja')

@push('css_adicional')
<style>
    .caja-wrap { max-width: 1000px; margin: 0 auto; padding: 20px; }
    .caja-hdr { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; flex-wrap: wrap; }
    .caja-hdr h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }

    .seccion { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 24px; }
    .seccion-hdr { padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .seccion-title { margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .seccion-body { padding: 24px; }

    .caja-btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px; padding: 10px 18px; border-radius: 8px; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
    .caja-btn.primary { background: #2563eb; color: white; }
    .caja-btn.primary:hover { background: #1d4ed8; }
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 10px; }
    .metric-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-align: center; }
    .metric-item.balance { background: #eff6ff; border-color: #bfdbfe; }
    .metric-lbl { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px; }
    .metric-val { font-size: 18px; font-weight: 800; font-family: monospace; }
    
    .caja-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .caja-field label { font-size: 13px; font-weight: 600; color: #475569; }
    .caja-field input, .caja-field select { padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; }
    
    .comp-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; margin-top: 15px; }
    .comp-table th { padding: 12px 14px; background: #f8fafc; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; }
    .comp-table td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; }
    
    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr; }
        .metric-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('contenido')
<div class="caja-wrap">
    <div class="caja-hdr">
        <h2>
            <i class="bi bi-file-earmark-bar-graph" style="color: #2563eb;"></i>
            Reportes y Balances
        </h2>
        <a href="{{ route('caja.movimientos') }}" class="caja-btn" style="background: #f1f5f9; color: #475569;">
            <i class="bi bi-arrow-left"></i> Volver a Caja
        </a>
    </div>

    <!-- Filtros de Fecha -->
    <div class="seccion">
        <div class="seccion-hdr">
            <h3 class="seccion-title">
                <i class="bi bi-funnel"></i>
                Filtro del Periodo
            </h3>
        </div>
        <div class="seccion-body">
            <form method="GET" action="{{ route('caja.reportes') }}" id="form-fechas" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <div class="caja-field" style="margin: 0; min-width: 150px;">
                    <label for="filtro_periodo">Rango Predefinido</label>
                    <select name="filtro_periodo" id="filtro_periodo" onchange="toggleCustomDates()">
                        <option value="hoy" {{ $filtroPeriodo === 'hoy' ? 'selected' : '' }}>Hoy</option>
                        <option value="esta_semana" {{ $filtroPeriodo === 'esta_semana' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="este_mes" {{ $filtroPeriodo === 'este_mes' ? 'selected' : '' }}>Este Mes</option>
                        <option value="este_anio" {{ $filtroPeriodo === 'este_anio' ? 'selected' : '' }}>Este Año</option>
                        <option value="personalizado" {{ $filtroPeriodo === 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                    </select>
                </div>
                <div class="caja-field" id="div-desde" style="margin: 0; display: {{ $filtroPeriodo === 'personalizado' ? 'flex' : 'none' }};">
                    <label for="fecha_desde">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="{{ $fechaDesde }}">
                </div>
                <div class="caja-field" id="div-hasta" style="margin: 0; display: {{ $filtroPeriodo === 'personalizado' ? 'flex' : 'none' }};">
                    <label for="fecha_hasta">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ $fechaHasta }}">
                </div>
                <button type="submit" class="caja-btn primary">
                    <i class="bi bi-search"></i> Generar Reporte
                </button>
            </form>
        </div>
    </div>

    <!-- Métricas por Caja -->
    <div class="grid-2">
        <div class="seccion">
            <div class="seccion-hdr" style="border-left: 4px solid #0284c7;">
                <h3 class="seccion-title" style="color: #0369a1;">
                    <i class="bi bi-wallet2"></i>
                    Balance Caja Chica
                </h3>
            </div>
            <div class="seccion-body">
                <div class="metric-grid">
                    <div class="metric-item">
                        <div class="metric-lbl" style="color: #166534;">Ingresos</div>
                        <div class="metric-val" style="color: #166534;">${{ number_format($metricsChica['ingresos'], 2) }}</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-lbl" style="color: #991b1b;">Egresos</div>
                        <div class="metric-val" style="color: #991b1b;">${{ number_format($metricsChica['egresos'], 2) }}</div>
                    </div>
                    <div class="metric-item balance">
                        <div class="metric-lbl" style="color: #1e3a8a;">Neto</div>
                        <div class="metric-val" style="color: #1e3a8a;">${{ number_format($metricsChica['balance'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="seccion">
            <div class="seccion-hdr" style="border-left: 4px solid #059669;">
                <h3 class="seccion-title" style="color: #047857;">
                    <i class="bi bi-safe"></i>
                    Balance Caja Grande
                </h3>
            </div>
            <div class="seccion-body">
                <div class="metric-grid">
                    <div class="metric-item">
                        <div class="metric-lbl" style="color: #166534;">Ingresos</div>
                        <div class="metric-val" style="color: #166534;">${{ number_format($metricsGrande['ingresos'], 2) }}</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-lbl" style="color: #991b1b;">Egresos</div>
                        <div class="metric-val" style="color: #991b1b;">${{ number_format($metricsGrande['egresos'], 2) }}</div>
                    </div>
                    <div class="metric-item balance">
                        <div class="metric-lbl" style="color: #1e3a8a;">Neto</div>
                        <div class="metric-val" style="color: #1e3a8a;">${{ number_format($metricsGrande['balance'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparativa entre Periodos -->
    <div class="seccion">
        <div class="seccion-hdr">
            <h3 class="seccion-title">
                <i class="bi bi-arrow-left-right"></i>
                Comparador Mensual / Anual de Flujos
            </h3>
        </div>
        <div class="seccion-body">
            <form method="GET" action="{{ route('caja.reportes') }}" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px;">
                <input type="hidden" name="filtro_periodo" value="{{ $filtroPeriodo }}">
                <input type="hidden" name="fecha_desde" value="{{ $fechaDesde }}">
                <input type="hidden" name="fecha_hasta" value="{{ $fechaHasta }}">

                <div class="caja-field" style="margin: 0; min-width: 180px;">
                    <label for="comp_mes_base">Mes Base</label>
                    <input type="month" name="comp_mes_base" id="comp_mes_base" value="{{ request('comp_mes_base', date('Y-m')) }}" required>
                </div>
                <div class="caja-field" style="margin: 0; min-width: 180px;">
                    <label for="comp_mes_ref">Mes a Comparar</label>
                    <input type="month" name="comp_mes_ref" id="comp_mes_ref" value="{{ request('comp_mes_ref') }}" required>
                </div>
                <button type="submit" class="caja-btn primary">
                    <i class="bi bi-arrow-left-right"></i> Comparar
                </button>
            </form>

            @if($comparativa)
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h4 style="margin: 0 0 10px; font-size: 15px; font-weight: 700; color: #0f172a;">
                        Dashboard Comparativo: {{ $comparativa['base_nombre'] }} vs {{ $comparativa['ref_nombre'] }}
                    </h4>
                    <table class="comp-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Mes Base ({{ $comparativa['base_nombre'] }})</th>
                                <th>Mes Ref. ({{ $comparativa['ref_nombre'] }})</th>
                                <th>Diferencia ($)</th>
                                <th>Diferencia (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Ingresos -->
                            <tr>
                                <td style="font-weight: 600; color: #166534;">Total Ingresos</td>
                                <td style="font-family: monospace;">${{ number_format($comparativa['base']['ingresos'], 2) }}</td>
                                <td style="font-family: monospace;">${{ number_format($comparativa['ref']['ingresos'], 2) }}</td>
                                <td style="font-family: monospace; font-weight: 700; color: {{ $comparativa['ingresos_diff'] >= 0 ? '#166534' : '#991b1b' }}">
                                    {{ $comparativa['ingresos_diff'] >= 0 ? '+' : '' }}${{ number_format($comparativa['ingresos_diff'], 2) }}
                                </td>
                                <td>
                                    @if($comparativa['ref']['ingresos'] > 0)
                                        @php $pctIncomes = ($comparativa['ingresos_diff'] / $comparativa['ref']['ingresos']) * 100; @endphp
                                        <span style="font-weight: 700; color: {{ $pctIncomes >= 0 ? '#166534' : '#991b1b' }}">
                                            {{ $pctIncomes >= 0 ? '+' : '' }}{{ number_format($pctIncomes, 1) }}%
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <!-- Egresos -->
                            <tr>
                                <td style="font-weight: 600; color: #991b1b;">Total Egresos (Gastos)</td>
                                <td style="font-family: monospace;">${{ number_format($comparativa['base']['egresos'], 2) }}</td>
                                <td style="font-family: monospace;">${{ number_format($comparativa['ref']['egresos'], 2) }}</td>
                                <td style="font-family: monospace; font-weight: 700; color: {{ $comparativa['egresos_diff'] >= 0 ? '#991b1b' : '#166534' }}">
                                    {{ $comparativa['egresos_diff'] >= 0 ? '+' : '' }}${{ number_format($comparativa['egresos_diff'], 2) }}
                                </td>
                                <td>
                                    @if($comparativa['ref']['egresos'] > 0)
                                        @php $pctExpenses = ($comparativa['egresos_diff'] / $comparativa['ref']['egresos']) * 100; @endphp
                                        <span style="font-weight: 700; color: {{ $pctExpenses >= 0 ? '#991b1b' : '#166534' }}">
                                            {{ $pctExpenses >= 0 ? '+' : '' }}{{ number_format($pctExpenses, 1) }}%
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <!-- Balance -->
                            <tr style="background: #eff6ff; font-weight: 700;">
                                <td style="color: #1e3a8a;">Balance Neto</td>
                                <td style="font-family: monospace; color: #1e3a8a;">${{ number_format($comparativa['base']['balance'], 2) }}</td>
                                <td style="font-family: monospace; color: #1e3a8a;">${{ number_format($comparativa['ref']['balance'], 2) }}</td>
                                <td colspan="2" style="text-align: center; font-family: monospace; color: {{ ($comparativa['base']['balance'] - $comparativa['ref']['balance']) >= 0 ? '#166534' : '#991b1b' }}">
                                    @php $balDiff = $comparativa['base']['balance'] - $comparativa['ref']['balance']; @endphp
                                    {{ $balDiff >= 0 ? 'Mejora de: +' : 'Reducción de: ' }}${{ number_format(abs($balDiff), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @elseif(request()->filled('comp_mes_ref'))
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: center; color: #64748b;">
                    <i class="bi bi-info-circle"></i> Seleccione un mes base y un mes de comparación para desplegar el dashboard comparativo.
                </div>
            @endif
        </div>
    </div>
</div>

@push('js_adicional')
<script>
    function toggleCustomDates() {
        const period = document.getElementById('filtro_periodo').value;
        const divDesde = document.getElementById('div-desde');
        const divHasta = document.getElementById('div-hasta');
        if (period === 'personalizado') {
            divDesde.style.display = 'flex';
            divHasta.style.display = 'flex';
        } else {
            divDesde.style.display = 'none';
            divHasta.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
