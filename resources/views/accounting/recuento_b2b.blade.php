@extends('layouts.app')

@section('contenido')
<style>
    .b2b-container {
        padding: 24px;
        max-width: 1500px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .b2b-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        background: #1e293b;
        padding: 20px 28px;
        border-radius: 16px;
        border: 1px solid #334155;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
    }
    .b2b-title {
        color: #f8fafc;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }
    .b2b-subtitle {
        color: #94a3b8;
        font-size: 0.875rem;
        margin-top: 4px;
    }
    .filter-bar {
        display: flex;
        gap: 16px;
        align-items: center;
        background: #1e293b;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #334155;
        margin-bottom: 24px;
    }
    .filter-select {
        background: #0f172a;
        color: #f8fafc;
        border: 1px solid #475569;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    .b2b-card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 28px;
    }
    .b2b-card-title {
        color: #f8fafc;
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #334155;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        color: #cbd5e1;
        font-size: 0.875rem;
    }
    .custom-table th {
        background: #0f172a;
        color: #94a3b8;
        text-align: left;
        padding: 12px 14px;
        font-weight: 600;
    }
    .custom-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #334155;
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-novicompu { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; }
    .badge-rb { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
    .badge-garantia { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
    .badge-servicios { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid #a855f7; }
    .btn-primary {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.4);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(16, 185, 129, 0.5);
    }
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
</style>

<div class="b2b-container">
    <div class="b2b-header">
        <div>
            <h1 class="b2b-title">Bandeja de Recuento y Facturación B2B</h1>
            <div class="b2b-subtitle">Selección manual de órdenes finalizadas de empresas para emisión de factura lote y cobro</div>
        </div>
        <div>
            <button type="button" id="btn-procesar-lote" class="btn-primary" disabled onclick="abrirModalCobroLote()">
                Cobrar Lote Seleccionado (<span id="count-selected">0</span>) — Total: $<span id="sum-selected">0.00</span>
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('recuentob2b.index') }}" class="filter-bar">
        <label style="color: #cbd5e1; font-weight: 600;">Filtrar por Empresa:</label>
        <select name="empresa" class="filter-select" onchange="this.form.submit()">
            <option value="">-- Todas las Empresas --</option>
            <option value="Novicompu" {{ $empresaFiltro === 'Novicompu' ? 'selected' : '' }}>Novicompu</option>
            <option value="RB" {{ $empresaFiltro === 'RB' ? 'selected' : '' }}>RB Health</option>
        </select>
        <a href="{{ route('recuentob2b.index') }}" style="color: #94a3b8; text-decoration: underline; font-size: 0.85rem;">Limpiar filtro</a>
    </form>

    <div class="b2b-card">
        <div class="b2b-card-title">
            <span>Órdenes Pendientes de Cobro B2B</span>
            <span style="font-size: 0.85rem; font-weight: 400; color: #94a3b8;">Marque las órdenes que serán cobradas en el próximo lote</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="check-all" onclick="toggleSelectAll(this)"></th>
                        <th>Nro. Orden</th>
                        <th>Empresa</th>
                        <th>Subtipo</th>
                        <th>Técnicos</th>
                        <th>Horas Rep.</th>
                        <th>Regla Tarifa Aplicada</th>
                        <th>Valor Calculado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $ord)
                        @php
                            $empNombre = $ord->empresa->nombre ?? 'Empresa';
                            $isRB = str_contains(strtoupper($empNombre), 'RB');
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" class="chk-orden" 
                                    data-id="{{ $ord->id }}"
                                    data-nro="{{ $ord->nro_orden }}"
                                    data-empresa="{{ $empNombre }}"
                                    data-subtipo="{{ $ord->subtipo }}"
                                    data-horas="{{ $ord->horas_calculadas }}"
                                    data-tecnicos="{{ $ord->tecnicos_count }}"
                                    data-tarifa="{{ $ord->tarifa_calculada }}"
                                    data-total="{{ $ord->valor_total_calculado }}"
                                    onchange="actualizarSeleccion()">
                            </td>
                            <td><strong>{{ $ord->nro_orden }}</strong></td>
                            <td>
                                <span class="badge {{ $isRB ? 'badge-rb' : 'badge-novicompu' }}">{{ $empNombre }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $ord->subtipo === 'Garantia' || $ord->subtipo === 'Garantía' ? 'badge-garantia' : 'badge-servicios' }}">
                                    {{ $ord->subtipo ?? 'Servicios' }}
                                </span>
                            </td>
                            <td>{{ $ord->tecnicos_count }} técnico(s)</td>
                            <td>{{ number_format($ord->horas_calculadas, 1) }} hrs</td>
                            <td>
                                @if($isRB)
                                    $50.00 / hr
                                @elseif($ord->subtipo === 'Servicios')
                                    $25.00 / hr / técnico
                                @elseif($ord->subtipo === 'Garantia' || $ord->subtipo === 'Garantía')
                                    Valor Garantía ($35.00)
                                @else
                                    Presupuesto / Manual
                                @endif
                            </td>
                            <td><strong style="color: #34d399;">${{ number_format($ord->valor_total_calculado, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 24px;">No hay órdenes pendientes de cobro B2B.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="b2b-card">
        <div class="b2b-card-title">Historial de Lotes de Recuento Procesados</div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nro. Lote</th>
                        <th>Empresa</th>
                        <th>Total Órdenes</th>
                        <th>Subtotal Facturado</th>
                        <th>Pago Neto Banco</th>
                        <th>Retenciones SRI</th>
                        <th>Banco Destino</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotesProcesados as $lote)
                        @php
                            $lObj = (object) $lote;
                        @endphp
                        <tr>
                            <td><strong>{{ $lObj->nro_lote ?? $lObj->NroLote ?? '' }}</strong></td>
                            <td>{{ $lObj->empresa_nombre ?? $lObj->EmpresaNombre ?? '' }}</td>
                            <td>{{ $lObj->total_ordenes ?? $lObj->TotalOrdenes ?? 0 }} órdenes</td>
                            <td>${{ number_format((float)($lObj->subtotal ?? $lObj->Subtotal ?? 0), 2) }}</td>
                            <td><strong style="color: #60a5fa;">${{ number_format((float)($lObj->monto_neto_banco ?? $lObj->MontoNetoBanco ?? 0), 2) }}</strong></td>
                            <td>
                                Renta: ${{ number_format((float)($lObj->monto_retencion_renta ?? $lObj->MontoRetencionRenta ?? 0), 2) }}<br>
                                IVA: ${{ number_format((float)($lObj->monto_retencion_iva ?? $lObj->MontoRetencionIva ?? 0), 2) }}
                            </td>
                            <td>{{ $lObj->banco_destino ?? $lObj->BancoDestino ?? 'Banco Pichincha' }}</td>
                            <td>{{ \Carbon\Carbon::parse($lObj->created_at ?? $lObj->CreatedAt ?? now())->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 24px;">No hay lotes procesados previamente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let seleccionadas = [];

    function toggleSelectAll(master) {
        document.querySelectorAll('.chk-orden').forEach(chk => {
            chk.checked = master.checked;
        });
        actualizarSeleccion();
    }

    function actualizarSeleccion() {
        seleccionadas = [];
        let total = 0.0;
        document.querySelectorAll('.chk-orden:checked').forEach(chk => {
            const data = chk.dataset;
            const itemTotal = parseFloat(data.total) || 0;
            total += itemTotal;
            seleccionadas.push({
                id: parseInt(data.id),
                nro_orden: data.nro,
                empresa: data.empresa,
                subtipo: data.subtipo,
                horas: parseFloat(data.horas),
                tecnicos_count: parseInt(data.tecnicos),
                tarifa: parseFloat(data.tarifa),
                valor_total: itemTotal
            });
        });

        document.getElementById('count-selected').innerText = seleccionadas.length;
        document.getElementById('sum-selected').innerText = total.toFixed(2);
        document.getElementById('btn-procesar-lote').disabled = (seleccionadas.length === 0);
    }

    function abrirModalCobroLote() {
        if (seleccionadas.length === 0) return;

        const subtotal = parseFloat(document.getElementById('sum-selected').innerText);
        const empresaNombre = seleccionadas[0].empresa;

        Swal.fire({
            title: 'Procesar Cobro Lote B2B',
            html: `
                <div style="text-align: left; font-size: 0.875rem; color: #334155;">
                    <p><strong>Empresa:</strong> ${empresaNombre}</p>
                    <p><strong>Órdenes Seleccionadas:</strong> ${seleccionadas.length}</p>
                    <p><strong>Subtotal Factura Lote:</strong> $${subtotal.toFixed(2)}</p>
                    
                    <div style="margin-top: 12px;">
                        <label style="font-weight: 600;">Banco Destino del Pago:</label>
                        <select id="swal-banco" class="swal2-input" style="margin-top: 4px;">
                            <option value="Banco Pichincha Cta Cte">Banco Pichincha Cta Cte</option>
                            <option value="Banco Guayaquil Cta Cte">Banco Guayaquil Cta Cte</option>
                            <option value="Produbanco">Produbanco</option>
                        </select>
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="font-weight: 600;">Monto Neto Depositado en Banco ($):</label>
                        <input type="number" step="0.01" id="swal-neto" class="swal2-input" value="${subtotal.toFixed(2)}" style="margin-top: 4px;">
                    </div>

                    <div style="margin-top: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-weight: 600;">Retención Renta ($):</label>
                            <input type="number" step="0.01" id="swal-ret-renta" class="swal2-input" value="0.00" style="margin-top: 4px;">
                        </div>
                        <div>
                            <label style="font-weight: 600;">Retención IVA ($):</label>
                            <input type="number" step="0.01" id="swal-ret-iva" class="swal2-input" value="0.00" style="margin-top: 4px;">
                        </div>
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="font-weight: 600;">Nro. Comprobante Retención SRI:</label>
                        <input type="text" id="swal-nro-ret" class="swal2-input" placeholder="Ej: 001-002-000012345" style="margin-top: 4px;">
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="font-weight: 600;">Nro. Comprobante / Transf. Bancaria:</label>
                        <input type="text" id="swal-nro-pago" class="swal2-input" placeholder="Ej: TRF-98765432" style="margin-top: 4px;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Cobro y Facturar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const banco = document.getElementById('swal-banco').value;
                const neto = parseFloat(document.getElementById('swal-neto').value) || 0;
                const retRenta = parseFloat(document.getElementById('swal-ret-renta').value) || 0;
                const retIva = parseFloat(document.getElementById('swal-ret-iva').value) || 0;
                const nroRet = document.getElementById('swal-nro-ret').value;
                const nroPago = document.getElementById('swal-nro-pago').value;

                if (neto <= 0) {
                    Swal.showValidationMessage('Ingrese un monto neto recibido válido.');
                    return false;
                }

                return {
                    banco_destino: banco,
                    monto_neto_banco: neto,
                    monto_retencion_renta: retRenta,
                    monto_retencion_iva: retIva,
                    nro_retencion: nroRet,
                    nro_comprobante_pago: nroPago
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                procesarCobroBackend(empresaNombre, result.value);
            }
        });
    }

    function procesarCobroBackend(empresaNombre, payload) {
        payload.empresa_nombre = empresaNombre;
        payload.ordenes = seleccionadas;

        fetch("{{ route('recuentob2b.procesar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                Swal.fire('Cobro Procesado', res.mensaje, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.error || 'No se pudo procesar el cobro del lote.', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión al procesar.', 'error'));
    }
</script>
@endsection
