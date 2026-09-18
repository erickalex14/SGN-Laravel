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
            <h1 class="rep-title">Reportería y Auditoría de Cajas Chicas</h1>
            <div class="rep-subtitle">Monitoreo de Fondos Fijos, Custodios por Sucursal, Vales de Gastos y Comprobantes Adjuntos</div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn-excel" onclick="exportarExcelCajaChica()">
                <i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo
            </button>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir Reporte PDF
            </button>
        </div>
    </div>

    <!-- NAVEGACIÓN SUPERIOR DE SUBPÁGINAS -->
    @include('accounting.reportes.partials.top_subnav')

    <!-- FILTROS MULTICRITERIO PARA CAJA CHICA -->
    <form method="GET" action="{{ route('contabilidad.reportes.caja_chica') }}" class="filter-bar">
        <div class="filter-group">
            <i class="bi bi-calendar-range" style="color: #2563eb; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Desde:</label>
            <input type="date" name="fecha_inicio" class="filter-input" value="{{ $fechaInicio }}" onchange="this.form.submit()">
        </div>

        <div class="filter-group">
            <label style="color: #0f172a; font-weight: 700;">Hasta:</label>
            <input type="date" name="fecha_fin" class="filter-input" value="{{ $fechaFin }}" onchange="this.form.submit()">
        </div>

        <div class="filter-group">
            <i class="bi bi-wallet2" style="color: #d97706; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Caja Chica:</label>
            <select name="caja_chica_id" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Todas las Cajas Chicas --</option>
                @foreach($cajasChicasCabeceras as $ccSel)
                    <option value="{{ $ccSel->id }}" {{ (string)$cajaChicaIdFiltro === (string)$ccSel->id ? 'selected' : '' }}>
                        {{ $ccSel->nro_caja_chica }} ({{ $ccSel->sucursal_ciudad ?? $ccSel->codigo_sucursal }})
                    </option>
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
        @endif

        <div class="filter-group">
            <i class="bi bi-tags" style="color: #059669; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Tipo Gasto:</label>
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

        @if($cajaChicaIdFiltro !== '' || $sucursalFiltro !== '' || $tipoGastoFiltro !== '' || $tecnicoFiltro !== '' || $fechaInicio !== \Carbon\Carbon::now()->startOfYear()->format('Y-m-d'))
            <a href="{{ route('contabilidad.reportes.caja_chica') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline; font-size: 0.85rem;">
                Limpiar filtros
            </a>
        @endif
    </form>

    <!-- CARDS DE CADA CAJA CHICA INDIVIDUAL POR SUCURSAL -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="bi bi-boxes" style="color: #d97706;"></i>
                <span>Cajas Chicas Registradas por Sucursal ({{ $cajasChicasCabeceras->count() }} Cajas)</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">
            @forelse($cajasChicasCabeceras as $cc)
                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-left: 5px solid {{ (string)$cajaChicaIdFiltro === (string)$cc->id ? '#2563eb' : '#d97706' }}; border-radius: 10px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
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
                        <div style="border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 6px; display: flex; justify-content: space-between; align-items: center;">
                            <div><strong>Saldo Disponible:</strong> <strong style="color: #2563eb; font-size: 0.95rem;">${{ number_format($cc->saldo_disponible, 2) }}</strong></div>
                            <a href="{{ route('contabilidad.reportes.caja_chica', ['caja_chica_id' => $cc->id, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="btn-details" style="background: #eff6ff; color: #1e40af; border-color: #bfdbfe;">
                                <i class="bi bi-filter me-1"></i>Ver Gastos
                            </a>
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
                        <th>Caja Chica / Sucursal</th>
                        <th>Nro. Comprobante</th>
                        <th>Fecha</th>
                        <th>Tipo Gasto</th>
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
                            <td>
                                <div><strong>{{ $gasto->nro_caja_chica }}</strong></div>
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.75rem;">{{ $gasto->sucursal_ciudad ?? $gasto->codigo_sucursal }}</span>
                            </td>
                            <td><strong>{{ $gasto->nro_comprobante ?: 'VALE-' . str_pad($gasto->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($gasto->fecha_comprobante ?? $gasto->created_at)->format('d/m/Y') }}</td>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    async function exportarExcelCajaChica() {
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

            const fillHeader = color => ({ type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + color } });
            const borderThin = { top: { style: 'thin', color: { argb: 'FFE2E8F0' } }, left: { style: 'thin', color: { argb: 'FFE2E8F0' } }, bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } }, right: { style: 'thin', color: { argb: 'FFE2E8F0' } } };

            // HOJA 1: RESUMEN DE CAJAS CHICAS POR SUCURSAL
            const ws1 = wb.addWorksheet('Fondos Cajas Chicas');
            ws1.columns = [
                { header: 'Nro. Caja Chica', key: 'nro', width: 25 },
                { header: 'Sucursal', key: 'sucursal', width: 20 },
                { header: 'Custodio Responsable', key: 'custodio', width: 25 },
                { header: 'Estado', key: 'estado', width: 15 },
                { header: 'Fondo Inicial ($)', key: 'fondo', width: 20 },
                { header: 'Gastos Ejecutados ($)', key: 'gastos', width: 22 },
                { header: 'Saldo Disponible ($)', key: 'saldo', width: 22 }
            ];

            ws1.insertRow(1, ['NOVITEC SGN - RESUMEN DE FONDOS DE CAJAS CHICAS POR SUCURSAL']);
            ws1.mergeCells('A1:G1');
            ws1.getCell('A1').font = { bold: true, size: 13, color: { argb: 'FFFFFFFF' } };
            ws1.getCell('A1').fill = fillHeader('D97706');
            ws1.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };

            const headerRow1 = ws1.getRow(3);
            headerRow1.values = ['Nro. Caja Chica', 'Sucursal', 'Custodio Responsable', 'Estado', 'Fondo Inicial ($)', 'Gastos Ejecutados ($)', 'Saldo Disponible ($)'];
            headerRow1.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            headerRow1.eachCell(c => { c.fill = fillHeader('1E293B'); });

            @foreach($cajasChicasCabeceras as $cc)
                ws1.addRow({
                    nro: '{{ $cc->nro_caja_chica }}',
                    sucursal: '{{ $cc->sucursal_ciudad ?? $cc->codigo_sucursal }}',
                    custodio: '{{ $cc->custodio_nombre }}',
                    estado: '{{ $cc->estado }}',
                    fondo: {{ (float)$cc->fondo_inicial }},
                    gastos: {{ (float)$cc->total_gastos }},
                    saldo: {{ (float)$cc->saldo_disponible }}
                });
            @endforeach

            ws1.eachRow((row, rowNumber) => {
                if (rowNumber > 3) {
                    row.border = borderThin;
                    row.getCell(5).numFormat = '$#,##0.00';
                    row.getCell(6).numFormat = '$#,##0.00';
                    row.getCell(7).numFormat = '$#,##0.00';
                }
            });

            // HOJA 2: DETALLE COMPLETO DE GASTOS Y VALES DE LA CAJA CHICA
            const ws2 = wb.addWorksheet('Detalle Completo de Gastos');
            ws2.columns = [
                { header: 'Nro. Caja Chica', key: 'caja_nro', width: 22 },
                { header: 'Nro. Comprobante', key: 'nro', width: 25 },
                { header: 'Fecha Comprobante', key: 'fecha', width: 18 },
                { header: 'Sucursal / Código', key: 'sucursal', width: 18 },
                { header: 'Tipo Gasto / Categoría', key: 'tipo', width: 25 },
                { header: 'Proveedor', key: 'proveedor', width: 22 },
                { header: 'Descripción / Justificación', key: 'descripcion', width: 35 },
                { header: 'Beneficiario / Técnico', key: 'beneficiario', width: 25 },
                { header: 'Subtotal sin IVA ($)', key: 'subtotal_sin', width: 20 },
                { header: 'Subtotal con IVA ($)', key: 'subtotal_con', width: 20 },
                { header: 'IVA 15% ($)', key: 'iva', width: 16 },
                { header: 'Monto Retención ($)', key: 'retencion', width: 20 },
                { header: 'Nro. Retención', key: 'nro_retencion', width: 18 },
                { header: 'Total Gastado ($)', key: 'total', width: 18 },
                { header: 'Valor Entregado ($)', key: 'entregado', width: 20 },
                { header: 'Vuelto Esperado ($)', key: 'vuelto', width: 18 },
                { header: 'Estado Vuelto', key: 'estado_vuelto', width: 16 }
            ];

            ws2.insertRow(1, ['NOVITEC SGN - AUDITORÍA DETALLADA DE VALES Y GASTOS DE CAJA CHICA']);
            ws2.mergeCells('A1:Q1');
            ws2.getCell('A1').font = { bold: true, size: 13, color: { argb: 'FFFFFFFF' } };
            ws2.getCell('A1').fill = fillHeader('D97706');
            ws2.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };

            const headerRow2 = ws2.getRow(3);
            headerRow2.values = [
                'Nro. Caja Chica', 'Nro. Comprobante', 'Fecha Comprobante', 'Sucursal / Código', 'Tipo Gasto / Categoría', 
                'Proveedor', 'Descripción / Justificación', 'Beneficiario / Técnico', 'Subtotal sin IVA ($)', 
                'Subtotal con IVA ($)', 'IVA 15% ($)', 'Monto Retención ($)', 'Nro. Retención', 'Total Gastado ($)', 
                'Valor Entregado ($)', 'Vuelto Esperado ($)', 'Estado Vuelto'
            ];
            headerRow2.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            headerRow2.eachCell(c => { c.fill = fillHeader('1E293B'); });

            @foreach($gastosCajaChica as $gasto)
                ws2.addRow({
                    caja_nro: '{{ $gasto->nro_caja_chica }}',
                    nro: '{{ $gasto->nro_comprobante ?: "VALE-" . str_pad($gasto->id, 5, "0", STR_PAD_LEFT) }}',
                    fecha: '{{ \Carbon\Carbon::parse($gasto->fecha_comprobante ?? $gasto->created_at)->format("d/m/Y") }}',
                    sucursal: '{{ $gasto->sucursal_ciudad ?? $gasto->codigo_sucursal }}',
                    tipo: '{{ $gasto->tipo_gasto }}',
                    proveedor: '{{ $gasto->proveedor ?: "Varios" }}',
                    descripcion: '{{ $gasto->descripcion }}',
                    beneficiario: '{{ $gasto->usuario_beneficiado ?: ($gasto->custodio_nombre ?? "Solicitante") }}',
                    subtotal_sin: {{ (float)$gasto->subtotal_sin_iva }},
                    subtotal_con: {{ (float)$gasto->subtotal_con_iva }},
                    iva: {{ (float)$gasto->iva }},
                    retencion: {{ (float)$gasto->monto_retencion }},
                    nro_retencion: '{{ $gasto->nro_retencion ?? "" }}',
                    total: {{ (float)$gasto->total }},
                    entregado: {{ (float)($gasto->valor_entregado ?? $gasto->total) }},
                    vuelto: {{ (float)($gasto->vuelto_esperado ?? 0) }},
                    estado_vuelto: '{{ $gasto->estado_vuelto ?? "N/A" }}'
                });
            @endforeach

            ws2.eachRow((row, rowNumber) => {
                if (rowNumber > 3) {
                    row.border = borderThin;
                    row.getCell(9).numFormat = '$#,##0.00';
                    row.getCell(10).numFormat = '$#,##0.00';
                    row.getCell(11).numFormat = '$#,##0.00';
                    row.getCell(12).numFormat = '$#,##0.00';
                    row.getCell(14).numFormat = '$#,##0.00';
                    row.getCell(15).numFormat = '$#,##0.00';
                    row.getCell(16).numFormat = '$#,##0.00';
                }
            });

            // Descargar XLSX
            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Reporte_Caja_Chica_Detallado_Novitec_${new Date().toISOString().slice(0, 10)}.xlsx`;
            a.click();
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'No se pudo generar la exportación Excel de Cajas Chicas.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo';
        }
    }
</script>
@endsection
