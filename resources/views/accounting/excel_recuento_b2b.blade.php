<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Enterprise - Recuento B2B</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; color: #0f172a; }
        .header-title { font-size: 16px; font-weight: bold; color: #ffffff; background-color: #1e3a8a; padding: 12px; text-align: center; }
        .sub-header { font-size: 10px; color: #1e3a8a; background-color: #dbeafe; padding: 6px; text-align: center; font-style: italic; }
        
        .kpi-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
        .kpi-box { border: 1px solid #cbd5e1; padding: 8px; text-align: center; vertical-align: middle; }
        .kpi-title { font-size: 8px; font-weight: bold; color: #475569; text-transform: uppercase; }
        .kpi-value { font-size: 14px; font-weight: bold; color: #1e3a8a; }

        table.data-table { border-collapse: collapse; width: 100%; margin-top: 6px; }
        table.data-table th { background-color: #1e293b; color: #ffffff; font-weight: bold; border: 1px solid #0f172a; padding: 8px 6px; font-size: 9px; text-align: left; text-transform: uppercase; }
        table.data-table td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: middle; font-size: 9.5px; }
        table.data-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .num { text-align: right; font-family: monospace; font-weight: bold; }
        .center { text-align: center; }
        .total-row { background-color: #dcfce7; font-weight: bold; font-size: 11px; }
        .total-cell { color: #166534; font-weight: bold; text-align: right; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    @php
        $totalOrdenes = $ordenes->count();
        $subtotalGeneral = $ordenes->sum('valor_total_calculado');
        
        $cantGarantia = $ordenes->filter(fn($o) => ($o->subtipo_normalizado ?? '') === 'Garantía')->count();
        $montoGarantia = $ordenes->filter(fn($o) => ($o->subtipo_normalizado ?? '') === 'Garantía')->sum('valor_total_calculado');
        
        $cantServicio = $ordenes->filter(fn($o) => ($o->subtipo_normalizado ?? '') === 'Servicios')->count();
        $montoServicio = $ordenes->filter(fn($o) => ($o->subtipo_normalizado ?? '') === 'Servicios')->sum('valor_total_calculado');

        $cantStockAuto = $ordenes->filter(fn($o) => in_array($o->subtipo_normalizado ?? '', ['Stock', 'Autoconsumo']))->count();
        $montoStockAuto = $ordenes->filter(fn($o) => in_array($o->subtipo_normalizado ?? '', ['Stock', 'Autoconsumo']))->sum('valor_total_calculado');
    @endphp

    <table>
        <tr>
            <td colspan="23" class="header-title">REPORTE ENTERPRISE DE RECUENTO Y FACTURACIÓN B2B — Novitecnología Cía. Ltda.</td>
        </tr>
        <tr>
            <td colspan="23" class="sub-header">
                Generado: {{ $fechaExportacion }}   |   Exportado por: {{ $usuario->nombre_tecnico ?? $usuario->usuario ?? 'Usuario' }}   |   Total Registros: {{ $totalOrdenes }}   |   Monto Facturado: ${{ number_format($subtotalGeneral, 2) }}
            </td>
        </tr>
    </table>

    <table class="kpi-table">
        <tr>
            <td class="kpi-box" style="background-color: #eff6ff;">
                <div class="kpi-title">TOTAL ÓRDENES</div>
                <div class="kpi-value" style="color: #1e40af;">{{ $totalOrdenes }}</div>
            </td>
            <td class="kpi-box" style="background-color: #ecfdf5;">
                <div class="kpi-title">SUBTOTAL RECUENTO B2B</div>
                <div class="kpi-value" style="color: #166534;">${{ number_format($subtotalGeneral, 2) }}</div>
            </td>
            <td class="kpi-box" style="background-color: #fef9c3;">
                <div class="kpi-title">ÓRDENES DE GARANTÍA</div>
                <div class="kpi-value" style="color: #854d0e;">{{ $cantGarantia }} (${{ number_format($montoGarantia, 2) }})</div>
            </td>
            <td class="kpi-box" style="background-color: #dbeafe;">
                <div class="kpi-title">ÓRDENES DE SERVICIO</div>
                <div class="kpi-value" style="color: #1e40af;">{{ $cantServicio }} (${{ number_format($montoServicio, 2) }})</div>
            </td>
            <td class="kpi-box" style="background-color: #f3e8ff;">
                <div class="kpi-title">STOCK / AUTOCONSUMO</div>
                <div class="kpi-value" style="color: #6b21a8;">{{ $cantStockAuto }} (${{ number_format($montoStockAuto, 2) }})</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Nro. Orden</th>
                <th>Tipo Origen</th>
                <th>Empresa Facturada</th>
                <th>Cliente Final (Usuario de la Orden)</th>
                <th>C.I. / RUC</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Subtipo</th>
                <th>Equipo / Marca / Modelo</th>
                <th>Serie / S/N</th>
                <th>Falla / Motivo Ingreso</th>
                <th>Técnico(s) Asignado(s)</th>
                <th class="center">Cant. Téc.</th>
                <th>Sucursal Origen</th>
                <th>F. Ingreso</th>
                <th>F. Entrega</th>
                <th class="num">Horas Trab.</th>
                <th class="num">Tarifa Aplicada ($)</th>
                <th class="num">Valor Novicompu ($)</th>
                <th class="num">Valor RB-Health / Otras ($)</th>
                <th>Estado Orden</th>
                <th>Estado Facturación</th>
                <th>Memo / Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenes as $ord)
                @php
                    $empNombre = $ord->empresa_nombre ?? $ord->empresa->nombre ?? 'N/A';
                    $isRB = str_contains(strtoupper($empNombre), 'RB');
                    $valTotal = (float) ($ord->valor_total_calculado ?? 0);
                    $valNovicompu = !$isRB ? $valTotal : 0.00;
                    $valOtra = $isRB ? $valTotal : 0.00;

                    $eq = $ord->equipo;
                    $eqNombre = 'N/A';
                    $serieStr = 'N/A';
                    if ($eq) {
                        $eqNombre = trim(($eq->tipo ?? '') . ' ' . ($eq->marca ?? '') . ' ' . ($eq->modelo ?? ''));
                        $serieStr = $eq->serie ?? 'N/A';
                    }
                @endphp
                <tr>
                    <td><strong>{{ $ord->nro_orden }}</strong></td>
                    <td class="center">{{ strtoupper($ord->tipo_orden_origen ?? 'empresa') }}</td>
                    <td><strong>{{ $empNombre }}</strong></td>
                    <td>{{ $ord->cliente_nombre ?? 'N/A' }}</td>
                    <td>{{ $ord->identificacion ?? 'N/A' }}</td>
                    <td>{{ $ord->cliente_telefono ?? 'N/A' }}</td>
                    <td>{{ $ord->cliente_correo ?? 'N/A' }}</td>
                    <td><strong>{{ $ord->subtipo_normalizado ?? 'Servicios' }}</strong></td>
                    <td>{{ $eqNombre }}</td>
                    <td>{{ $serieStr }}</td>
                    <td>{{ $ord->descripcion_servicio ?? $ord->motivo_ingreso ?? '-' }}</td>
                    <td>{{ $ord->tecnico_nombre ?? 'N/A' }}</td>
                    <td class="center">{{ $ord->tecnicos_count ?? 1 }}</td>
                    <td>{{ $ord->sucursal_nombre ?? 'N/A' }}</td>
                    <td>{{ $ord->fecha_de_ingreso ?? '-' }}</td>
                    <td>{{ $ord->fecha_entrega ?? $ord->fecha_finalizacion ?? '-' }}</td>
                    <td class="num">{{ number_format((float)($ord->horas_calculadas ?? 1.0), 1) }}</td>
                    <td class="num">${{ number_format((float)($ord->tarifa_calculada ?? 0), 2) }}</td>
                    <td class="num" style="color: {{ $valNovicompu > 0 ? '#166534' : '#64748b' }};">
                        ${{ number_format($valNovicompu, 2) }}
                    </td>
                    <td class="num" style="color: {{ $valOtra > 0 ? '#166534' : '#64748b' }};">
                        ${{ number_format($valOtra, 2) }}
                    </td>
                    <td>{{ $ord->estado ?? $ord->estado_orden ?? 'Finalizada' }}</td>
                    <td>{{ $ord->estado_facturacion ?? 'Pendiente' }}</td>
                    <td>{{ $ord->memo_entrega ?? $ord->observaciones ?? $ord->observacion ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="23" style="text-align: center; color: #94a3b8; padding: 20px;">No se seleccionaron órdenes para exportar.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="18" style="text-align: right; font-weight: bold; padding: 10px;">TOTAL GENERAL RECUENTO B2B:</td>
                <td class="total-cell">${{ number_format($ordenes->filter(fn($o) => !str_contains(strtoupper($o->empresa_nombre ?? ''), 'RB'))->sum('valor_total_calculado'), 2) }}</td>
                <td class="total-cell">${{ number_format($ordenes->filter(fn($o) => str_contains(strtoupper($o->empresa_nombre ?? ''), 'RB'))->sum('valor_total_calculado'), 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
