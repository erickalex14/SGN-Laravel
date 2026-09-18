@extends('layouts.app')

@section('contenido')
<style>
    .historial-container {
        padding: 24px;
        max-width: 1600px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .historial-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        background: #ffffff;
        padding: 20px 24px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .historial-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .historial-subtitle {
        color: #64748b;
        font-size: 0.85rem;
        margin-top: 4px;
    }
    .top-nav-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 2px;
    }
    .top-nav-btn {
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-bottom: none;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #475569;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .top-nav-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .top-nav-btn.active {
        background: #ffffff;
        color: #2563eb;
        border-color: #2563eb #2563eb #ffffff;
        margin-bottom: -2px;
        box-shadow: 0 -2px 6px rgba(37,99,235,0.08);
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.02);
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        align-items: end;
    }
    .filter-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .filter-control {
        width: 100%;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        font-size: 0.88rem;
        background: #f8fafc;
        color: #0f172a;
    }
    .filter-control:focus {
        border-color: #2563eb;
        background: #ffffff;
        outline: none;
    }
    .table-container {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .lotes-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.86rem;
    }
    .lotes-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .lotes-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        vertical-align: middle;
    }
    .lotes-table tbody tr:hover {
        background: #f8fafc;
    }
    .order-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        margin: 2px;
        color: #1e40af;
    }

    /* Modal Detalle */
    .modal-backdrop-custom {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-dialog-custom {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }
    .modal-header-custom {
        padding: 18px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-radius: 16px 16px 0 0;
    }
    .modal-body-custom {
        padding: 24px;
    }
</style>

<div class="historial-container">
    {{-- Encabezado Principal --}}
    <div class="historial-header">
        <div>
            <h1 class="historial-title">
                <i class="bi bi-journal-bookmark text-primary"></i> Historial de Facturas por Lote (Milenium)
            </h1>
            <div class="historial-subtitle">
                Consulta y audita las facturas registradas en Milenium y las órdenes que fueron agrupadas y cobradas.
            </div>
        </div>
        <div>
            <a href="{{ route('facturacion_lotes.index') }}" class="btn btn-primary" style="font-weight: 700; padding: 9px 18px; border-radius: 8px;">
                <i class="bi bi-plus-circle me-1"></i> Facturar Nuevo Lote
            </a>
        </div>
    </div>

    {{-- Pestañas Superiores --}}
    <div class="top-nav-tabs">
        <a href="{{ route('facturacion_lotes.index') }}" class="top-nav-btn">
            <i class="bi bi-check2-square"></i> Órdenes Elegibles (≥ 05 Sep)
        </a>
        <a href="{{ route('facturacion_lotes.historial') }}" class="top-nav-btn active">
            <i class="bi bi-journal-bookmark"></i> Lotes y Facturas Registradas
        </a>
    </div>

    {{-- Filtros --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('facturacion_lotes.historial') }}">
            <div class="filter-grid">
                <div>
                    <label class="filter-label">Buscar</label>
                    <input type="text" name="buscar" class="filter-control" placeholder="Nro. Factura, autorización o nro. de orden..." value="{{ $filtros['buscar'] ?? '' }}">
                </div>
                <div>
                    <label class="filter-label">Fecha Desde</label>
                    <input type="date" name="fecha_desde" class="filter-control" value="{{ $filtros['fecha_desde'] ?? '' }}">
                </div>
                <div>
                    <label class="filter-label">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" class="filter-control" value="{{ $filtros['fecha_hasta'] ?? '' }}">
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 8px 18px; border-radius: 8px; flex: 1;">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                    <a href="{{ route('facturacion_lotes.historial') }}" class="btn btn-light" style="border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 14px;" title="Limpiar Filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabla Historial --}}
    <div class="table-container">
        <table class="lotes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nro. Factura Milenium</th>
                    <th>Fecha Factura</th>
                    <th>Valor Facturado ($)</th>
                    <th>Nro. Autorización SRI</th>
                    <th>Órdenes Agrupadas</th>
                    <th>Registrado Por</th>
                    <th>Fecha Registro</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lotes as $lote)
                    <tr>
                        <td>
                            <strong style="color: #64748b;">#{{ $lote['id'] }}</strong>
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-family: monospace; font-size: 0.95rem; background: #f1f5f9; padding: 4px 8px; border-radius: 6px;">
                                {{ $lote['nro_factura'] }}
                            </strong>
                        </td>
                        <td>
                            <span style="font-weight: 600;">{{ date('d/m/Y', strtotime($lote['fecha_factura'])) }}</span>
                        </td>
                        <td>
                            <span style="font-weight: 800; color: #166534; font-size: 0.95rem;">
                                ${{ number_format((float) $lote['valor_facturado'], 2) }}
                            </span>
                        </td>
                        <td>
                            @if(!empty($lote['nro_autorizacion']))
                                <span style="font-family: monospace; font-size: 0.78rem; color: #475569;" title="{{ $lote['nro_autorizacion'] }}">
                                    {{ Str::limit($lote['nro_autorizacion'], 20) }}
                                </span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 320px;">
                                @foreach($lote['ordenes'] as $item)
                                    <span class="order-tag">
                                        <i class="bi bi-tag-fill" style="font-size: 0.7rem;"></i> {{ $item['nro_orden'] }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #334155;">
                                {{ $lote['creado_por']['nombre_tecnico'] ?? $lote['creado_por']['usuario'] ?? 'Sistema' }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <div>{{ date('d/m/Y', strtotime($lote['created_at'])) }}</div>
                            <div style="font-size: 0.72rem; color: #64748b;">{{ date('H:i', strtotime($lote['created_at'])) }}</div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn btn-sm btn-outline-primary" style="font-weight: 600;" onclick="verDetalleLote({{ $lote['id'] }})">
                                <i class="bi bi-eye"></i> Detalle
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #64748b;">
                            <i class="bi bi-inbox" style="font-size: 2.2rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                            <strong>No se han registrado facturas por lote aún.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Ver Detalle --}}
<div id="modal-detalle-lote" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h5 style="margin: 0; font-weight: 800; color: #0f172a;" id="detalle-modal-title">
                Detalle de Factura Milenium
            </h5>
            <button type="button" style="background: none; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer;" onclick="cerrarModalDetalle()">&times;</button>
        </div>
        <div class="modal-body-custom" id="detalle-modal-content">
            <div class="text-center py-4">
                <span class="spinner-border spinner-border-sm"></span> Cargando información...
            </div>
        </div>
    </div>
</div>

<script>
async function verDetalleLote(id) {
    const modal = document.getElementById('modal-detalle-lote');
    const content = document.getElementById('detalle-modal-content');
    modal.style.display = 'flex';
    content.innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span> Cargando...</div>';

    try {
        const res = await fetch(`{{ url('/contabilidad/facturacion-lotes') }}/${id}/detalle`);
        const data = await res.json();

        if (res.ok && data.ok) {
            const l = data.lote;
            let ordenesHtml = '';
            (l.ordenes || []).forEach(o => {
                ordenesHtml += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border-radius: 6px; margin-bottom: 6px; border: 1px solid #e2e8f0;">
                        <div>
                            <strong style="color: #1e40af;">${o.nro_orden}</strong>
                            <span class="badge bg-secondary ms-2" style="font-size: 0.68rem; text-transform: uppercase;">${o.tipo_orden}</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-danger" style="font-size: 0.72rem; padding: 2px 8px;" onclick="desvincularOrden(${o.id}, '${o.nro_orden}')" title="Desvincular orden de esta factura">
                                <i class="bi bi-x-circle"></i> Desvincular
                            </button>
                        </div>
                    </div>
                `;
            });

            content.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Nro. Factura Milenium</div>
                        <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">${l.nro_factura}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Valor Facturado Total</div>
                        <div style="font-size: 1.1rem; font-weight: 800; color: #166534;">$${parseFloat(l.valor_facturado).toFixed(2)}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Fecha de Factura</div>
                        <div style="font-weight: 600;">${l.fecha_factura ? l.fecha_factura.substring(0, 10) : '-'}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Nro. Autorización SRI</div>
                        <div style="font-family: monospace; font-size: 0.85rem; word-break: break-all;">${l.nro_autorizacion || '-'}</div>
                    </div>
                </div>

                ${l.observaciones ? `
                    <div style="margin-bottom: 18px; padding: 10px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px;">
                        <strong style="font-size: 0.8rem; color: #92400e;">Observaciones:</strong>
                        <p style="margin: 0; font-size: 0.85rem; color: #78350f;">${l.observaciones}</p>
                    </div>
                ` : ''}

                <div style="margin-top: 10px;">
                    <div style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 8px;">
                        Órdenes asociadas a esta factura (${(l.ordenes || []).length}):
                    </div>
                    <div style="max-height: 220px; overflow-y: auto;">
                        ${ordenesHtml || '<span style="color: #94a3b8;">No hay órdenes asociadas</span>'}
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `<div class="alert alert-danger">${data.message || 'Error al cargar'}</div>`;
        }
    } catch (e) {
        content.innerHTML = `<div class="alert alert-danger">Error de conexión: ${e.message}</div>`;
    }
}

function cerrarModalDetalle() {
    document.getElementById('modal-detalle-lote').style.display = 'none';
}

async function desvincularOrden(ordenLoteId, nroOrden) {
    if (!confirm(`¿Está seguro de desvincular la orden ${nroOrden} de esta factura? La orden volverá a estado "Pendiente de Facturación".`)) {
        return;
    }

    try {
        const res = await fetch(`{{ url('/contabilidad/facturacion-lotes/orden') }}/${ordenLoteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const d = await res.json();
        if (res.ok && d.ok) {
            alert(d.message);
            window.location.reload();
        } else {
            alert(d.message || 'Error al desvincular la orden.');
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('modal-detalle-lote');
    if (e.target === modal) {
        cerrarModalDetalle();
    }
});
</script>
@endsection
