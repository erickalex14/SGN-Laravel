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
            <h1 class="rep-title">Reportería y Auditoría de Caja General</h1>
            <div class="rep-subtitle">Historial de Arqueos Diarios, Cierres de Ventanilla y Comprobantes de Depósito Bancario</div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn-excel" onclick="exportarExcelCajaGeneralDetallado()">
                <i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo Detallado
            </button>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir Reporte PDF
            </button>
        </div>
    </div>

    <!-- NAVEGACIÓN SUPERIOR DE SUBPÁGINAS -->
    @include('accounting.reportes.partials.top_subnav')

    <!-- FILTROS DE FECHAS Y SUCURSAL -->
    <form method="GET" action="{{ route('contabilidad.reportes.caja_general') }}" class="filter-bar">
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
            <a href="{{ route('contabilidad.reportes.caja_general') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline; font-size: 0.85rem;">
                Limpiar filtros
            </a>
        @endif
    </form>

    <!-- HISTORIAL DE ARQUEOS (CUADRE IDENTICO A CAJA GENERAL) -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="bi bi-clock-history" style="color: #059669;"></i>
                <span>Historial de Arqueos y Cierres Diarios ({{ $arqueos->count() }} arqueos)</span>
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #475569;">
                Total Físico Arqueado: <strong style="color: #059669;">${{ number_format($totalFisicoArqueado, 2) }}</strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table" id="tabla-arqueos">
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

    <!-- DETALLE DE COBROS VENTANILLA CON DETALLES DE ÓRDENES -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="bi bi-wallet2" style="color: #2563eb;"></i>
                <span>Detalle de Cobros y Órdenes Registradas en Ventanilla ({{ $cobros->count() }} cobros)</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table" id="tabla-cobros">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Cliente / Cédula</th>
                        <th>Equipo / Serie</th>
                        <th>Técnico</th>
                        <th>Fecha Cobro</th>
                        <th>Método Pago</th>
                        <th>Destino Cuenta</th>
                        <th>Monto Cobrado ($)</th>
                        <th>Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobros as $cob)
                        @php
                            $compUrl = $cob->comprobante_url ?? $cob->comprobante_deposito_path ?? '';
                            $equipoStr = trim(($cob->equipo_tipo ?? '') . ' ' . ($cob->equipo_marca ?? '') . ' ' . ($cob->equipo_modelo ?? ''));
                        @endphp
                        <tr>
                            <td><strong style="color: #2563eb;">{{ $cob->nro_orden ?? 'N/A' }}</strong></td>
                            <td>
                                <div><strong>{{ $cob->cliente_nombre }}</strong></div>
                                @if(!empty($cob->cliente_cedula))
                                    <div style="font-size: 0.775rem; color: #64748b;">C.I/RUC: {{ $cob->cliente_cedula }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $equipoStr ?: ($cob->equipo_info ?: 'Equipo N/A') }}</div>
                                @if(!empty($cob->equipo_serie))
                                    <div style="font-size: 0.775rem; color: #64748b;">S/N: {{ $cob->equipo_serie }}</div>
                                @endif
                            </td>
                            <td>{{ $cob->tecnico_orden ?? ($cob->usuario_nombre ?? 'Técnico') }}</td>
                            <td>{{ \Carbon\Carbon::parse($cob->fecha_cobro)->format('d/m/Y H:i') }}</td>
                            <td>{{ $cob->metodo_pago ?? 'Efectivo' }}</td>
                            <td>
                                <span class="badge" style="background: {{ $cob->destino_cuenta === 'Caja General' ? '#dcfce7' : '#dbeafe' }}; color: {{ $cob->destino_cuenta === 'Caja General' ? '#166534' : '#1e40af' }}; font-weight: 700; padding: 4px 8px;">
                                    {{ $cob->destino_cuenta }}
                                </span>
                            </td>
                            <td><strong style="color: #0f172a;">${{ number_format((float)$cob->monto_cobrado, 2) }}</strong></td>
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
                            <td colspan="9" style="text-align: center; color: #94a3b8; padding: 24px;">No hay cobros registrados en el rango de fechas.</td>
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
    async function exportarExcelCajaGeneralDetallado() {
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

            // HOJA 1: AUDITORÍA DETALLADA COBROS & ÓRDENES (PRINCIPAL UNIFICADA)
            const ws1 = wb.addWorksheet('Auditoría Detallada Cobros');
            ws1.columns = [
                { header: 'Nro. Órden Trabajo', key: 'orden', width: 20 },
                { header: 'Cliente / Razón Social', key: 'cliente', width: 28 },
                { header: 'Cédula / RUC', key: 'cedula', width: 18 },
                { header: 'Teléfono Contacto', key: 'telefono', width: 18 },
                { header: 'Técnico Responsable', key: 'tecnico', width: 22 },
                { header: 'Equipo (Tipo / Marca / Modelo)', key: 'equipo', width: 30 },
                { header: 'Nro. Serie (S/N)', key: 'serie', width: 22 },
                { header: 'Motivo / Falla Reportada', key: 'falla', width: 30 },
                { header: 'Fecha y Hora Cobro', key: 'fecha', width: 20 },
                { header: 'Método de Pago', key: 'metodo', width: 18 },
                { header: 'Destino Cuenta', key: 'destino', width: 18 },
                { header: 'Monto Cobrado ($)', key: 'monto', width: 18 },
                { header: 'Monto Recibido ($)', key: 'recibido', width: 18 },
                { header: 'Vuelto Dado ($)', key: 'vuelto', width: 16 },
                { header: 'Neto Caja ($)', key: 'neto', width: 18 },
                { header: 'Cajero / Usuario', key: 'cajero', width: 22 },
                { header: 'Estado Arqueo', key: 'estado_arq', width: 18 },
                { header: 'Observaciones', key: 'observaciones', width: 30 }
            ];

            // Título Banner Principal
            ws1.insertRow(1, ['NOVITEC SGN - INFORME AUDITOR DE COBROS Y ÓRDENES DE CAJA GENERAL']);
            ws1.mergeCells('A1:R1');
            ws1.getCell('A1').font = { bold: true, size: 14, color: { argb: 'FFFFFFFF' } };
            ws1.getCell('A1').fill = fillHeader('0F172A');
            ws1.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };

            ws1.insertRow(2, [`Período Auditado: {{ $fechaInicio }} al {{ $fechaFin }} | Generado: ${new Date().toLocaleString()}`]);
            ws1.mergeCells('A2:R2');
            ws1.getCell('A2').font = { italic: true, size: 10, color: { argb: 'FF475569' } };

            const headerRow1 = ws1.getRow(4);
            headerRow1.values = [
                'Nro. Órden Trabajo', 'Cliente / Razón Social', 'Cédula / RUC', 'Teléfono Contacto', 'Técnico Responsable', 
                'Equipo (Tipo / Marca / Modelo)', 'Nro. Serie (S/N)', 'Motivo / Falla Reportada', 'Fecha y Hora Cobro', 
                'Método de Pago', 'Destino Cuenta', 'Monto Cobrado ($)', 'Monto Recibido ($)', 'Vuelto Dado ($)', 
                'Neto Caja ($)', 'Cajero / Usuario', 'Estado Arqueo', 'Observaciones'
            ];
            headerRow1.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            headerRow1.eachCell(c => { c.fill = fillHeader('1E293B'); c.alignment = { horizontal: 'left', vertical: 'middle' }; });

            @foreach($cobros as $cob)
                @php
                    $equipoFull = trim(($cob->equipo_tipo ?? '') . ' ' . ($cob->equipo_marca ?? '') . ' ' . ($cob->equipo_modelo ?? ''));
                @endphp
                ws1.addRow({
                    orden: '{{ $cob->nro_orden ?? "N/A" }}',
                    cliente: '{{ $cob->cliente_nombre }}',
                    cedula: '{{ $cob->cliente_cedula ?? "" }}',
                    telefono: '{{ $cob->cliente_telefono ?? "" }}',
                    tecnico: '{{ $cob->tecnico_orden ?? ($cob->usuario_nombre ?? "") }}',
                    equipo: '{{ $equipoFull ?: ($cob->equipo_info ?: "Equipo N/A") }}',
                    serie: '{{ $cob->equipo_serie ?? "" }}',
                    falla: '{{ $cob->equipo_falla ?? "" }}',
                    fecha: '{{ \Carbon\Carbon::parse($cob->fecha_cobro)->format("d/m/Y H:i") }}',
                    metodo: '{{ $cob->metodo_pago ?? "Efectivo" }}',
                    destino: '{{ $cob->destino_cuenta }}',
                    monto: {{ (float)$cob->monto_cobrado }},
                    recibido: {{ (float)($cob->monto_recibido ?? $cob->monto_cobrado) }},
                    vuelto: {{ (float)($cob->vuelto_dado ?? 0) }},
                    neto: {{ (float)($cob->monto_neto_caja ?? $cob->monto_cobrado) }},
                    cajero: '{{ $cob->usuario_nombre ?? "" }}',
                    estado_arq: '{{ $cob->estado_arqueo ?? "Pendiente" }}',
                    observaciones: '{{ $cob->observaciones ?? "" }}'
                });
            @endforeach

            ws1.eachRow((row, rowNumber) => {
                if (rowNumber > 4) {
                    row.border = borderThin;
                    row.getCell(12).numFormat = '$#,##0.00';
                    row.getCell(13).numFormat = '$#,##0.00';
                    row.getCell(14).numFormat = '$#,##0.00';
                    row.getCell(15).numFormat = '$#,##0.00';
                }
            });

            // HOJA 2: RESUMEN DE ARQUEOS DIARIOS
            const ws2 = wb.addWorksheet('Resumen Arqueos Diarios');
            ws2.columns = [
                { header: 'Nro. Arqueo', key: 'nro', width: 22 },
                { header: 'Fecha / Hora', key: 'fecha', width: 20 },
                { header: 'Monto Sistema ($)', key: 'sistema', width: 22 },
                { header: 'Monto Físico ($)', key: 'fisico', width: 22 },
                { header: 'Diferencia ($)', key: 'diferencia', width: 18 },
                { header: 'Resultado Arqueo', key: 'resultado', width: 22 },
                { header: 'Estado Depósito', key: 'estado', width: 20 }
            ];

            ws2.insertRow(1, ['NOVITEC SGN - HISTORIAL DE ARQUEOS Y CIERRES DIARIOS']);
            ws2.mergeCells('A1:G1');
            ws2.getCell('A1').font = { bold: true, size: 13, color: { argb: 'FFFFFFFF' } };
            ws2.getCell('A1').fill = fillHeader('059669');
            ws2.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };

            const headerRow2 = ws2.getRow(3);
            headerRow2.values = ['Nro. Arqueo', 'Fecha / Hora', 'Monto Sistema ($)', 'Monto Físico ($)', 'Diferencia ($)', 'Resultado Arqueo', 'Estado Depósito'];
            headerRow2.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            headerRow2.eachCell(c => { c.fill = fillHeader('1E293B'); });

            @foreach($arqueos as $arq)
                @php
                    $codSuc = $arq->codigo_sucursal ?? 'ACC30';
                    $nroArqStr = $codSuc . '-ARQ-' . str_pad($arq->id, 6, '0', STR_PAD_LEFT);
                @endphp
                ws2.addRow({
                    nro: '{{ $nroArqStr }}',
                    fecha: '{{ \Carbon\Carbon::parse($arq->fecha)->format("d/m/Y H:i") }}',
                    sistema: {{ (float)($arq->monto_sistema ?? $arq->total_efectivo) }},
                    fisico: {{ (float)($arq->monto_fisico ?? $arq->total_efectivo) }},
                    diferencia: {{ (float)$arq->diferencia }},
                    resultado: '{{ $arq->tipo_diferencia ?? "Cuadre Exacto" }}',
                    estado: '{{ $arq->estado ?? "Depositado" }}'
                });
            @endforeach

            ws2.eachRow((row, rowNumber) => {
                if (rowNumber > 3) {
                    row.border = borderThin;
                    row.getCell(3).numFormat = '$#,##0.00';
                    row.getCell(4).numFormat = '$#,##0.00';
                    row.getCell(5).numFormat = '$#,##0.00';
                }
            });

            // Descargar XLSX
            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Informe_Auditoria_Caja_General_Novitec_${new Date().toISOString().slice(0, 10)}.xlsx`;
            a.click();
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'No se pudo generar la exportación Excel de Caja General.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX Nativo Detallado';
        }
    }
</script>
@endsection
