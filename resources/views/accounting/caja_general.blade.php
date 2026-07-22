@extends('layouts.app')

@section('contenido')
<style>
    .cg-container {
        padding: 28px 24px;
        max-width: 1400px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .cg-header {
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
    .cg-title {
        color: #0f172a;
        font-size: 1.4rem;
        font-weight: 800;
        margin: 0;
    }
    .cg-subtitle {
        color: #64748b;
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
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
        background: #2563eb;
    }
    .metric-card.success::before { background: #10b981; }
    .metric-card.warning::before { background: #f59e0b; }
    .metric-label {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .metric-value {
        color: #0f172a;
        font-size: 2rem;
        font-weight: 800;
        margin-top: 8px;
    }
    .btn-action {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-action:hover {
        background: #1d4ed8;
    }
    .cg-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 28px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .cg-card-title {
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        color: #1e293b;
        font-size: 0.875rem;
    }
    .custom-table th {
        background: #f8fafc;
        color: #475569;
        text-align: left;
        padding: 12px 16px;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .custom-table tr:hover td {
        background: #f8fafc;
    }
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-exacto { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-faltante { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .badge-sobrante { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .badge-depositado { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-pendiente { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
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
            <div class="metric-value" style="font-size: 1.2rem; margin-top: 14px;">
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
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">No hay órdenes registradas en efectivo el día de hoy.</td>
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
                            <td style="color: {{ $diff < 0 ? '#ef4444' : ($diff > 0 ? '#d97706' : '#10b981') }}; font-weight: 700;">
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
                                    <span style="color: #10b981; font-size: 0.8rem; font-weight: 600;">Depósito Completado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">No hay registros de arqueos anteriores.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function abrirModalArqueo() {
        const montoSistema = {{ $totalEfectivoCalculado }};
        Swal.fire({
            title: 'Arqueo Ciego de Caja General',
            html: `
                <div style="text-align: left; font-size: 0.9rem; color: #0f172a;">
                    <p><strong>Sucursal:</strong> {{ $sucursalNombre }} ({{ $codigoSucursal }})</p>
                    <p style="color: #64748b;">Contar el dinero en efectivo físico presente en la caja de recepción e ingresar el valor contado:</p>
                    <div style="margin-top: 12px;">
                        <label style="font-weight: 700; color: #0f172a;">Monto Físico Contado ($):</label>
                        <input type="number" step="0.01" id="swal-monto-fisico" class="swal2-input" placeholder="0.00" style="margin-top: 4px;">
                    </div>
                    <div style="margin-top: 12px;">
                        <label style="font-weight: 700; color: #0f172a;">Observaciones / Justificación:</label>
                        <textarea id="swal-obs" class="swal2-textarea" placeholder="Notas sobre el cierre o diferencia..." style="margin-top: 4px;"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Arqueo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
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
                <div style="text-align: left; font-size: 0.9rem; color: #0f172a;">
                    <div style="margin-top: 8px;">
                        <label style="font-weight: 700; color: #0f172a;">Nro. Comprobante de Depósito / Papeleta:</label>
                        <input type="text" id="swal-nro-dep" class="swal2-input" placeholder="Ej: DEP-987654" style="margin-top: 4px;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar Depósito',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
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
