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
    .filter-input {
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
            <h1 class="rep-title">Reportería y Auditoría Recuento B2B & Caja Bancos</h1>
            <div class="rep-subtitle">Conciliación de Lotes Facturados a Empresas Clientes, Retenciones SRI y Transferencias Bancarias</div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn-excel" onclick="exportarExcelB2BDetallado()">
                <i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo Detallado
            </button>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir Reporte PDF
            </button>
        </div>
    </div>

    <!-- NAVEGACIÓN SUPERIOR DE SUBPÁGINAS -->
    @include('accounting.reportes.partials.top_subnav')

    <!-- FILTROS DE FECHAS -->
    <form method="GET" action="{{ route('contabilidad.reportes.b2b') }}" class="filter-bar">
        <div class="filter-group">
            <i class="bi bi-calendar-range" style="color: #2563eb; font-size: 1.1rem;"></i>
            <label style="color: #0f172a; font-weight: 700;">Desde:</label>
            <input type="date" name="fecha_inicio" class="filter-input" value="{{ $fechaInicio }}" onchange="this.form.submit()">
        </div>

        <div class="filter-group">
            <label style="color: #0f172a; font-weight: 700;">Hasta:</label>
            <input type="date" name="fecha_fin" class="filter-input" value="{{ $fechaFin }}" onchange="this.form.submit()">
        </div>

        @if($fechaInicio !== \Carbon\Carbon::now()->startOfYear()->format('Y-m-d'))
            <a href="{{ route('contabilidad.reportes.b2b') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline; font-size: 0.85rem;">
                Limpiar filtros
            </a>
        @endif
    </form>

    <!-- TABLA DE AUDITORÍA B2B -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="bi bi-building-check" style="color: #7c3aed;"></i>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    async function exportarExcelB2BDetallado() {
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

            // HOJA 1: DETALLE COMPLETO DE ÓRDENES B2B (EXECUTIVE AUDIT SHEET)
            const ws1 = wb.addWorksheet('Auditoría Detallada por Órden');
            ws1.columns = [
                { header: 'Nro. Lote B2B', key: 'lote', width: 24 },
                { header: 'Empresa Facturada', key: 'empresa', width: 28 },
                { header: 'Nro. Órden Trabajo', key: 'orden', width: 20 },
                { header: 'Tipo Cobro / Cobertura', key: 'subtipo', width: 22 },
                { header: 'Cliente Final / Razón Social', key: 'cliente', width: 28 },
                { header: 'Cédula / RUC', key: 'cedula', width: 18 },
                { header: 'Teléfono Contacto', key: 'telefono', width: 18 },
                { header: 'Técnico Responsable', key: 'tecnico', width: 22 },
                { header: 'Equipo (Tipo / Marca / Modelo)', key: 'equipo', width: 30 },
                { header: 'Nro. Serie (S/N)', key: 'serie', width: 22 },
                { header: 'Horas Trab.', key: 'horas', width: 14 },
                { header: 'Tarifa Aplicada ($)', key: 'tarifa', width: 20 },
                { header: 'Valor Órden ($)', key: 'valor', width: 18 },
                { header: 'Subtotal Lote ($)', key: 'subtotal_lote', width: 18 },
                { header: 'IVA 15% Lote ($)', key: 'iva_lote', width: 16 },
                { header: 'Total con IVA ($)', key: 'total_lote', width: 18 },
                { header: 'Retención Renta ($)', key: 'ret_renta', width: 18 },
                { header: 'Retención IVA ($)', key: 'ret_iva', width: 16 },
                { header: 'Neto Banco ($)', key: 'neto_banco', width: 18 },
                { header: 'Banco Destino', key: 'banco', width: 20 },
                { header: 'Fecha Recuento', key: 'fecha', width: 20 }
            ];

            // Título Banner
            ws1.insertRow(1, ['NOVITEC SGN - INFORME DE AUDITORÍA DETALLADA DE RECUENTO Y FACTURACIÓN B2B']);
            ws1.mergeCells('A1:U1');
            ws1.getCell('A1').font = { bold: true, size: 14, color: { argb: 'FFFFFFFF' } };
            ws1.getCell('A1').fill = fillHeader('0F172A');
            ws1.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };

            ws1.insertRow(2, [`Período Auditado: {{ $fechaInicio }} al {{ $fechaFin }} | Generado: ${new Date().toLocaleString()}`]);
            ws1.mergeCells('A2:U2');
            ws1.getCell('A2').font = { italic: true, size: 10, color: { argb: 'FF475569' } };

            const headerRow1 = ws1.getRow(4);
            headerRow1.values = [
                'Nro. Lote B2B', 'Empresa Facturada', 'Nro. Órden Trabajo', 'Tipo Cobro / Cobertura', 
                'Cliente Final / Razón Social', 'Cédula / RUC', 'Teléfono Contacto', 'Técnico Responsable', 
                'Equipo (Tipo / Marca / Modelo)', 'Nro. Serie (S/N)', 'Horas Trab.', 'Tarifa Aplicada ($)', 
                'Valor Órden ($)', 'Subtotal Lote ($)', 'IVA 15% Lote ($)', 'Total con IVA ($)', 
                'Retención Renta ($)', 'Retención IVA ($)', 'Neto Banco ($)', 'Banco Destino', 'Fecha Recuento'
            ];
            headerRow1.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            headerRow1.eachCell(c => { c.fill = fillHeader('1E293B'); c.alignment = { horizontal: 'left', vertical: 'middle' }; });

            @foreach($lotesB2B as $lote)
                @if(isset($lote->items) && $lote->items->count() > 0)
                    @foreach($lote->items as $it)
                        @php
                            $equipoFull = trim(($it->equipo_tipo ?? '') . ' ' . ($it->equipo_marca ?? '') . ' ' . ($it->equipo_modelo ?? ''));
                        @endphp
                        ws1.addRow({
                            lote: '{{ $lote->nro_lote }}',
                            empresa: '{{ $lote->empresa_nombre }}',
                            orden: '{{ $it->nro_orden ?? "N/A" }}',
                            subtipo: '{{ $it->subtipo ?? "Garantía" }}',
                            cliente: '{{ $it->cliente_final_nombre ?? "Cliente" }}',
                            cedula: '{{ $it->cliente_identificacion ?? "" }}',
                            telefono: '{{ $it->cliente_telefono ?? "" }}',
                            tecnico: '{{ $it->tecnico_nombre ?? "" }}',
                            equipo: '{{ $equipoFull ?: "Equipo N/A" }}',
                            serie: '{{ $it->equipo_serie ?? "" }}',
                            horas: {{ (float)($it->horas_trabajadas ?? 1) }},
                            tarifa: {{ (float)($it->tarifa_aplicada ?? $it->valor_total) }},
                            valor: {{ (float)$it->valor_total }},
                            subtotal_lote: {{ (float)$lote->subtotal }},
                            iva_lote: {{ (float)$lote->monto_iva }},
                            total_lote: {{ (float)$lote->total_con_iva }},
                            ret_renta: {{ (float)$lote->monto_retencion_renta }},
                            ret_iva: {{ (float)$lote->monto_retencion_iva }},
                            neto_banco: {{ (float)$lote->monto_neto_banco }},
                            banco: '{{ $lote->banco_destino ?? "Banco Pichincha" }}',
                            fecha: '{{ \Carbon\Carbon::parse($lote->created_at)->format("d/m/Y H:i") }}'
                        });
                    @endforeach
                @else
                    ws1.addRow({
                        lote: '{{ $lote->nro_lote }}',
                        empresa: '{{ $lote->empresa_nombre }}',
                        orden: 'N/A (Lote global)',
                        subtipo: 'Lote Consolidado',
                        cliente: '{{ $lote->empresa_nombre }}',
                        cedula: '',
                        telefono: '',
                        tecnico: '',
                        equipo: 'Varias Órdenes',
                        serie: '',
                        horas: 0,
                        tarifa: 0,
                        valor: {{ (float)$lote->subtotal }},
                        subtotal_lote: {{ (float)$lote->subtotal }},
                        iva_lote: {{ (float)$lote->monto_iva }},
                        total_lote: {{ (float)$lote->total_con_iva }},
                        ret_renta: {{ (float)$lote->monto_retencion_renta }},
                        ret_iva: {{ (float)$lote->monto_retencion_iva }},
                        neto_banco: {{ (float)$lote->monto_neto_banco }},
                        banco: '{{ $lote->banco_destino ?? "Banco Pichincha" }}',
                        fecha: '{{ \Carbon\Carbon::parse($lote->created_at)->format("d/m/Y H:i") }}'
                    });
                @endif
            @endforeach

            ws1.eachRow((row, rowNumber) => {
                if (rowNumber > 4) {
                    row.border = borderThin;
                    row.getCell(12).numFormat = '$#,##0.00';
                    row.getCell(13).numFormat = '$#,##0.00';
                    row.getCell(14).numFormat = '$#,##0.00';
                    row.getCell(15).numFormat = '$#,##0.00';
                    row.getCell(16).numFormat = '$#,##0.00';
                    row.getCell(17).numFormat = '$#,##0.00';
                    row.getCell(18).numFormat = '$#,##0.00';
                    row.getCell(19).numFormat = '$#,##0.00';
                }
            });

            // HOJA 2: RESUMEN POR LOTE DE FACTURACIÓN
            const ws2 = wb.addWorksheet('Resumen de Lotes');
            ws2.columns = [
                { header: 'Nro. Lote', key: 'lote', width: 25 },
                { header: 'Empresa Cliente', key: 'empresa', width: 30 },
                { header: 'Cant. Órdenes', key: 'ordenes', width: 16 },
                { header: 'Subtotal ($)', key: 'subtotal', width: 18 },
                { header: 'IVA 15% ($)', key: 'iva', width: 16 },
                { header: 'Total con IVA ($)', key: 'total', width: 20 },
                { header: 'Retención Renta ($)', key: 'ret_renta', width: 20 },
                { header: 'Retención IVA ($)', key: 'ret_iva', width: 18 },
                { header: 'Neto Banco ($)', key: 'neto', width: 20 },
                { header: 'Banco Destino', key: 'banco', width: 20 },
                { header: 'Fecha Registro', key: 'fecha', width: 20 }
            ];

            ws2.insertRow(1, ['NOVITEC SGN - RESUMEN EJECUTIVO DE LOTES B2B']);
            ws2.mergeCells('A1:K1');
            ws2.getCell('A1').font = { bold: true, size: 13, color: { argb: 'FFFFFFFF' } };
            ws2.getCell('A1').fill = fillHeader('7C3AED');
            ws2.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };

            const headerRow2 = ws2.getRow(3);
            headerRow2.values = ['Nro. Lote', 'Empresa Cliente', 'Cant. Órdenes', 'Subtotal ($)', 'IVA 15% ($)', 'Total con IVA ($)', 'Retención Renta ($)', 'Retención IVA ($)', 'Neto Banco ($)', 'Banco Destino', 'Fecha Registro'];
            headerRow2.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            headerRow2.eachCell(c => { c.fill = fillHeader('1E293B'); });

            @foreach($lotesB2B as $lote)
                ws2.addRow({
                    lote: '{{ $lote->nro_lote }}',
                    empresa: '{{ $lote->empresa_nombre }}',
                    ordenes: {{ (int)$lote->total_ordenes }},
                    subtotal: {{ (float)$lote->subtotal }},
                    iva: {{ (float)$lote->monto_iva }},
                    total: {{ (float)$lote->total_con_iva }},
                    ret_renta: {{ (float)$lote->monto_retencion_renta }},
                    ret_iva: {{ (float)$lote->monto_retencion_iva }},
                    neto: {{ (float)$lote->monto_neto_banco }},
                    banco: '{{ $lote->banco_destino ?? "Banco Pichincha" }}',
                    fecha: '{{ \Carbon\Carbon::parse($lote->created_at)->format("d/m/Y H:i") }}'
                });
            @endforeach

            ws2.eachRow((row, rowNumber) => {
                if (rowNumber > 3) {
                    row.border = borderThin;
                    row.getCell(4).numFormat = '$#,##0.00';
                    row.getCell(5).numFormat = '$#,##0.00';
                    row.getCell(6).numFormat = '$#,##0.00';
                    row.getCell(7).numFormat = '$#,##0.00';
                    row.getCell(8).numFormat = '$#,##0.00';
                    row.getCell(9).numFormat = '$#,##0.00';
                }
            });

            // Descargar XLSX
            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Auditoria_B2B_Ordenes_Detalladas_Novitec_${new Date().toISOString().slice(0, 10)}.xlsx`;
            a.click();
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'No se pudo generar la exportación Excel B2B.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo Detallado';
        }
    }
</script>
@endsection
