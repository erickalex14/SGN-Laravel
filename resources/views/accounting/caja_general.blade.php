@extends('layouts.app')

@section('contenido')
<style>
    .cg-container {
        padding: 24px;
        max-width: 1400px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .cg-header {
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
    .cg-title {
        color: #f8fafc;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }
    .cg-subtitle {
        color: #94a3b8;
        font-size: 0.875rem;
        margin-top: 4px;
    }
    .cg-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .metric-card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 14px;
        padding: 20px;
        position: relative;
        overflow: hidden;
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
        background: #3b82f6;
    }
    .metric-card.success::before { background: #10b981; }
    .metric-card.warning::before { background: #f59e0b; }
    .metric-label {
        color: #94a3b8;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .metric-value {
        color: #f8fafc;
        font-size: 2rem;
        font-weight: 800;
        margin-top: 8px;
    }
    .btn-action {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4);
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(37, 99, 235, 0.5);
    }
    .cg-card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 28px;
    }
    .cg-card-title {
        color: #f8fafc;
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 16px;
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
        font-size: 0.9rem;
    }
    .custom-table th {
        background: #0f172a;
        color: #94a3b8;
        text-align: left;
        padding: 12px 16px;
        font-weight: 600;
    }
    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #334155;
    }
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-exacto { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
    .badge-faltante { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
    .badge-sobrante { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
    .badge-depositado { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; }
    .badge-pendiente { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border: 1px solid #64748b; }
</style>

<div class="cg-container">
    <div class="cg-header">
        <div>
            <h1 class="cg-title">Caja General y Arqueos Diarios</h1>
            <div class="cg-subtitle">Sucursal: {{ $sucursalNombre }} ({{ $codigoSucursal }}) — Control de Efectivo en Recepción</div>
        </div>
        <div>
            <button type="button" class="btn-action" onclick="abrirModalArqueo()">Realizar Arqueo Ciego del Día</button>
        </div>
    </div>

    <div class="cg-metrics">
        <div class="metric-card warning">
            <div class="metric-label">Efectivo Cobrado Hoy en Recepción</div>
            <div class="metric-value">${{ number_format($totalEfectivoCalculado, 2) }}</div>
        </div>
        <div class="metric-card success">
            <div class="metric-label">Órdenes Liquidadas Hoy</div>
            <div class="metric-value">{{ $ordenesEfectivo->count() }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Estado de Cierre Hoy</div>
            <div class="metric-value" style="font-size: 1.3rem; margin-top: 14px;">
                @if(count($arqueos) > 0 && isset($arqueos[0]['fecha']) && \Carbon\Carbon::parse($arqueos[0]['fecha'])->isToday())
                    <span class="badge badge-exacto">Arqueado Hoy</span>
                @else
                    <span class="badge badge-pendiente">Pendiente Arqueo</span>
                @endif
            </div>
        </div>
    </div>

    <div class="cg-card">
        <div class="cg-card-title">Órdenes en Efectivo Cobradas Hoy en Recepción</div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Cliente</th>
                        <th>Equipo / Serie</th>
                        <th>Estado</th>
                        <th>Monto Cobrado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenesEfectivo as $ord)
                        <tr>
                            <td><strong>{{ $ord->nro_orden }}</strong></td>
                            <td>{{ $ord->cliente->nombres ?? '' }} {{ $ord->cliente->apellidos ?? '' }}</td>
                            <td>{{ $ord->equipo->tipo ?? '' }} - {{ $ord->equipo->serie ?? 'N/A' }}</td>
                            <td><span class="badge badge-depositado">{{ $ord->estado }}</span></td>
                            <td><strong>${{ number_format((float)($ord->total ?? $ord->presupuesto ?? 0), 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 24px;">No hay órdenes registradas en efectivo el día de hoy.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="cg-card">
        <div class="cg-card-title">Historial de Arqueos y Cierres Diarios</div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto Sistema</th>
                        <th>Monto Físico Contado</th>
                        <th>Diferencia</th>
                        <th>Resultado Arqueo</th>
                        <th>Estado Depósito</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arqueos as $arq)
                        @php
                            $arqObj = (object) $arq;
                            $diff = (float)($arqObj->diferencia ?? $arqObj->Diferencia ?? 0);
                            $tipoDiff = $arqObj->tipo_diferencia ?? $arqObj->TipoDiferencia ?? 'Cuadre Exacto';
                            $estado = $arqObj->estado ?? $arqObj->Estado ?? 'Pendiente Deposito';
                            $arqId = $arqObj->id ?? $arqObj->Id ?? 0;
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($arqObj->fecha ?? $arqObj->Fecha ?? now())->format('d/m/Y H:i') }}</td>
                            <td>${{ number_format((float)($arqObj->monto_sistema ?? $arqObj->MontoSistema ?? 0), 2) }}</td>
                            <td>${{ number_format((float)($arqObj->monto_fisico ?? $arqObj->MontoFisico ?? 0), 2) }}</td>
                            <td style="color: {{ $diff < 0 ? '#f87171' : ($diff > 0 ? '#fbbf24' : '#34d399') }};">
                                ${{ number_format($diff, 2) }}
                            </td>
                            <td>
                                @if($diff < 0)
                                    <span class="badge badge-faltante">Faltante</span>
                                @elseif($diff > 0)
                                    <span class="badge badge-sobrante">Sobrante</span>
                                @else
                                    <span class="badge badge-exacto">Cuadre Exacto</span>
                                @endif
                            </td>
                            <td>
                                @if($estado === 'Depositado')
                                    <span class="badge badge-depositado">Depositado</span>
                                @else
                                    <span class="badge badge-pendiente">Pendiente Depósito</span>
                                @endif
                            </td>
                            <td>
                                @if($estado !== 'Depositado')
                                    <button class="btn-action" style="padding: 6px 12px; font-size: 0.75rem;" onclick="abrirModalDeposito({{ $arqId }})">Adjuntar Depósito</button>
                                @else
                                    <span style="color: #34d399; font-size: 0.8rem; font-weight: 600;">Depósito Completado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No hay registros de arqueos anteriores.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Arqueo Ciego -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function abrirModalArqueo() {
        const montoSistema = {{ $totalEfectivoCalculado }};
        Swal.fire({
            title: 'Arqueo Ciego de Caja General',
            html: `
                <div style="text-align: left; font-size: 0.9rem; color: #334155;">
                    <p><strong>Sucursal:</strong> {{ $sucursalNombre }} ({{ $codigoSucursal }})</p>
                    <p>Contar el dinero en efectivo físico presente en la caja de recepción e ingresar el valor contado:</p>
                    <div style="margin-top: 12px;">
                        <label style="font-weight: 600;">Monto Físico Contado ($):</label>
                        <input type="number" step="0.01" id="swal-monto-fisico" class="swal2-input" placeholder="0.00" style="margin-top: 4px;">
                    </div>
                    <div style="margin-top: 12px;">
                        <label style="font-weight: 600;">Observaciones / Justificación de diferencia:</label>
                        <textarea id="swal-obs" class="swal2-textarea" placeholder="Notas sobre el cierre o diferencia..." style="margin-top: 4px;"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Arqueo',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const montoFisico = document.getElementById('swal-monto-fisico').value;
                const obs = document.getElementById('swal-obs').value;
                if (!montoFisico || isNaN(montoFisico)) {
                    Swal.showValidationMessage('Debe ingresar un monto físico válido.');
                    return false;
                }
                return { montoFisico: parseFloat(montoFisico), obs: obs };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarArqueo(montoSistema, result.value.montoFisico, result.value.obs);
            }
        });
    }

    function enviarArqueo(montoSistema, montoFisico, observaciones) {
        fetch("{{ route('cajageneral.guardar_arqueo') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sucursal_id: {{ $sucursalId }},
                codigo_sucursal: "{{ $codigoSucursal }}",
                monto_sistema: montoSistema,
                monto_fisico: montoFisico,
                observaciones: observaciones
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                Swal.fire('Arqueo Registrado', res.mensaje, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.error || 'No se pudo guardar el arqueo', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión', 'error'));
    }

    function abrirModalDeposito(arqueoId) {
        Swal.fire({
            title: 'Registrar Depósito Bancario',
            html: `
                <div style="text-align: left; font-size: 0.9rem; color: #334155;">
                    <div style="margin-top: 8px;">
                        <label style="font-weight: 600;">Nro. Comprobante de Depósito / Papeleta:</label>
                        <input type="text" id="swal-nro-dep" class="swal2-input" placeholder="Ej: DEP-987654" style="margin-top: 4px;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar Depósito',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const nroDep = document.getElementById('swal-nro-dep').value;
                if (!nroDep || nroDep.trim() === '') {
                    Swal.showValidationMessage('Ingrese el número de comprobante.');
                    return false;
                }
                return { nroDep: nroDep.trim() };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarDeposito(arqueoId, result.value.nroDep);
            }
        });
    }

    function enviarDeposito(arqueoId, nroDeposito) {
        fetch("{{ route('cajageneral.subir_deposito') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                arqueo_id: arqueoId,
                nro_comprobante_deposito: nroDeposito
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                Swal.fire('Depósito Registrado', res.mensaje, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.error || 'No se pudo guardar el depósito', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Fallo de conexión', 'error'));
    }
</script>
@endsection
