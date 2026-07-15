@extends('layouts.app')

@section('contenido')
<div class="container-fluid" style="padding: 20px 30px;">
    <!-- Encabezado Principal -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="h3 mb-1" style="font-weight: 700; color: #1e293b;">
                <i class="bi bi-box-seam text-primary me-2"></i>Inventario Físico en Servicio Técnico
            </h2>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Auditoría y control de equipos físicos en oficina para órdenes de stock de Novisolutions.
            </p>
        </div>
    </div>

    <!-- Indicadores / Métricas Rápidas -->
    <div class="row g-3 mb-4">
        <!-- Total -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-primary-emphasis d-block mb-1" style="font-size: 13px; font-weight: 600;">Total Equipos</span>
                        <h3 class="h2 mb-0" style="font-weight: 700; color: #1e3a8a;">{{ $totalProductos }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-boxes text-primary" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tienda -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-success-emphasis d-block mb-1" style="font-size: 13px; font-weight: 600;">En Tienda (Operativo)</span>
                        <h3 class="h2 mb-0" style="font-weight: 700; color: #14532d;">{{ $totalTienda }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Incinerox -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-danger-emphasis d-block mb-1" style="font-size: 13px; font-weight: 600;">Incinerox (A Incinerar)</span>
                        <h3 class="h2 mb-0" style="font-weight: 700; color: #7f1d1d;">{{ $totalIncinerox }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outlet -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-warning-emphasis d-block mb-1" style="font-size: 13px; font-weight: 600;">Outlet (Con Detalle)</span>
                        <h3 class="h2 mb-0" style="font-weight: 700; color: #78350f;">{{ $totalOutlet }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Filtros -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #fff;">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('inventario_fisico.index') }}" class="row g-3 align-items-end">
                <!-- Buscador de texto general -->
                <div class="col-md-4">
                    <label for="buscar" class="form-label" style="font-weight: 600; font-size: 13px; color: #475569;">Búsqueda General</label>
                    <input type="text" name="buscar" id="buscar" class="form-control form-control-sm" placeholder="Buscar por código, serie, nombre o número de orden..." value="{{ request('buscar') }}" style="border-radius: 8px;">
                </div>

                <!-- Filtro por Estado -->
                <div class="col-md-3">
                    <label for="estado" class="form-label" style="font-weight: 600; font-size: 13px; color: #475569;">Estado Físico</label>
                    <select name="estado" id="estado" class="form-select form-select-sm" style="border-radius: 8px;">
                        <option value="">-- Todos los Estados --</option>
                        <option value="Tienda" {{ request('estado') === 'Tienda' ? 'selected' : '' }}>Tienda (Operativo)</option>
                        <option value="Incinerox" {{ request('estado') === 'Incinerox' ? 'selected' : '' }}>Incinerox (A Incinerar)</option>
                        <option value="Outlet" {{ request('estado') === 'Outlet' ? 'selected' : '' }}>Outlet (Con Detalle)</option>
                    </select>
                </div>

                <!-- Botones de Acción de Filtros -->
                <div class="col-md-5 d-flex justify-content-end gap-2">
                    <a href="{{ route('inventario_fisico.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                    </a>
                    <button type="button" class="btn btn-success btn-sm" onclick="exportarExcelInventario()" style="border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 500; background: #2563eb; border-color: #2563eb;">
                        <i class="bi bi-funnel me-1"></i>Filtrar Resultados
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Contenido -->
    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-inventario" style="min-width: 1000px;">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 130px;">Sucursal</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 140px;">Orden Asoc.</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 140px;">Código</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 160px;">Serie</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569;">Nombre Producto</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 130px; text-align: center;">Estado Físico</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 250px;">Detalles Outlet</th>
                            <th style="padding: 15px 20px; font-weight: 600; font-size: 12px; color: #475569; width: 130px;">Fecha Ingreso</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: 0;">
                        @forelse($productos as $p)
                            @php
                                $badgeClass = 'bg-secondary-subtle text-secondary';
                                if ($p->estado === 'Tienda') {
                                    $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                } elseif ($p->estado === 'Incinerox') {
                                    $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                } elseif ($p->estado === 'Outlet') {
                                    $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                }
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 14px 20px; font-size: 13px; color: #475569; font-weight: 600;">
                                    {{ $p->sucursal?->ciudad ?: ($p->sucursal?->nombre ?: '-') }}
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; font-weight: 600; color: #1e293b;">
                                    @if($p->ordenEmpresa)
                                        <span class="badge bg-light text-dark border">#{{ $p->ordenEmpresa->nro_orden }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #64748b; font-family: monospace; font-weight: 600;">
                                    {{ $p->codigo }}
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #1e293b; font-family: monospace; font-weight: 600;">
                                    {{ $p->serie }}
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #334155; font-weight: 500;">
                                    {{ $p->nombre }}
                                </td>
                                <td style="padding: 14px 20px; text-align: center;">
                                    <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 11px; border-radius: 6px;">
                                        {{ $p->estado }}
                                    </span>
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #475569; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $p->detalle_outlet }}">
                                    {{ $p->detalle_outlet ?: '-' }}
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #64748b;">
                                    {{ $p->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted" style="font-size: 14px;">
                                    <i class="bi bi-search d-block mb-2 style-3" style="font-size: 24px; color: #94a3b8;"></i>
                                    No se encontraron productos en el inventario físico que coincidan con los filtros de tu sucursal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($productos->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size: 13px;">
                Mostrando registros del <strong>{{ $productos->firstItem() }}</strong> al <strong>{{ $productos->lastItem() }}</strong> de un total de <strong>{{ $productos->total() }}</strong>
            </div>
            <div>
                {!! $productos->links('pagination::bootstrap-4') !!}
            </div>
        </div>
    @endif
</div>

<!-- Lógica de Exportación a Excel vía ExcelJS -->
<script>
    async function exportarExcelInventario() {
        // 1. Asegurar que ExcelJS esté cargado
        if (typeof ExcelJS === 'undefined') {
            Swal.fire({
                title: 'Cargando ExcelJS...',
                text: 'Por favor espera un momento mientras cargamos el motor de reportes.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            await new Promise((resolve) => {
                const script = document.createElement('script');
                script.src = "https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js";
                script.onload = () => {
                    Swal.close();
                    resolve();
                };
                document.head.appendChild(script);
            });
        }

        // 2. Extraer datos directamente de la tabla actual para exportación rápida
        const filas = [];
        const tabla = document.getElementById('tabla-inventario');
        const tbodyFilas = tabla.querySelectorAll('tbody tr');

        if (tbodyFilas.length === 1 && tbodyFilas[0].cells.length === 1) {
            Swal.fire('Atención', 'No hay datos disponibles para exportar.', 'warning');
            return;
        }

        tbodyFilas.forEach(row => {
            const c = row.cells;
            if (c.length < 8) return;
            filas.push({
                sucursal: c[0].innerText.trim(),
                orden: c[1].innerText.trim(),
                codigo: c[2].innerText.trim(),
                serie: c[3].innerText.trim(),
                nombre: c[4].innerText.trim(),
                estado: c[5].innerText.trim(),
                detalles: c[6].innerText.trim(),
                fecha: c[7].innerText.trim(),
            });
        });

        // 3. Generar Libro ExcelJS
        const wb = new ExcelJS.Workbook();
        const ws = wb.addWorksheet('Inventario Físico ST');

        // Estilos
        const headerFont = { name: 'Arial', size: 11, bold: true, color: { argb: 'FFFFFF' } };
        const headerFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '2563EB' } };
        const borderStyle = {
            top: { style: 'thin', color: { argb: 'E2E8F0' } },
            left: { style: 'thin', color: { argb: 'E2E8F0' } },
            bottom: { style: 'thin', color: { argb: 'E2E8F0' } },
            right: { style: 'thin', color: { argb: 'E2E8F0' } }
        };

        // Encabezados
        ws.columns = [
            { header: 'Sucursal', key: 'sucursal', width: 20 },
            { header: 'Orden Asoc.', key: 'orden', width: 15 },
            { header: 'Código', key: 'codigo', width: 20 },
            { header: 'Serie', key: 'serie', width: 25 },
            { header: 'Nombre Producto', key: 'nombre', width: 35 },
            { header: 'Estado Físico', key: 'estado', width: 18 },
            { header: 'Detalles Outlet', key: 'detalles', width: 30 },
            { header: 'Fecha Ingreso', key: 'fecha', width: 15 }
        ];

        // Formatear fila de cabecera
        const headerRow = ws.getRow(1);
        headerRow.height = 25;
        headerRow.eachCell((cell) => {
            cell.font = headerFont;
            cell.fill = headerFill;
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
        });

        // Insertar datos
        filas.forEach(f => {
            const r = ws.addRow(f);
            r.height = 20;
            r.eachCell((cell, colNum) => {
                cell.font = { name: 'Arial', size: 10 };
                cell.border = borderStyle;
                cell.alignment = { vertical: 'middle' };
                
                // Centrar ciertas columnas
                if (colNum === 1 || colNum === 2 || colNum === 3 || colNum === 4 || colNum === 6 || colNum === 8) {
                    cell.alignment = { vertical: 'middle', horizontal: 'center' };
                }
            });
        });

        // Descarga
        const buffer = await wb.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Auditoria_Inventario_Fisico_ST_${new Date().toISOString().slice(0,10)}.xlsx`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
@endsection
