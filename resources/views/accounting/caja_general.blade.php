@extends('layouts.app')

@section('contenido')
<style>
    .cg-container {
        padding: 28px 24px;
        max-width: 1450px;
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
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
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
    .metric-card.info::before { background: #0284c7; }
    .metric-label {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .metric-value {
        color: #0f172a;
        font-size: 1.8rem;
        font-weight: 800;
        margin-top: 8px;
    }
    .btn-action {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-action:hover {
        background: #1d4ed8;
    }
    .btn-success-action {
        background: #10b981;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-right: 8px;
    }
    .btn-success-action:hover {
        background: #059669;
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
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    .badge-efectivo { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .badge-bancos { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
    .search-results-box {
        max-height: 180px;
        overflow-y: auto;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        margin-top: 6px;
        background: #fff;
        display: none;
    }
    .search-item {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        text-align: left;
    }
    .search-item:hover {
        background: #eff6ff;
    }
</style>

<div class="cg-container">
    <div class="cg-header">
        <div>
            <h1 class="cg-title">Caja General y Arqueos Diarios</h1>
            <div class="cg-subtitle">Sucursal: {{ $sucursalNombre }} ({{ $codigoSucursal }}) — Control de Cobros a Clientes Externos y Arqueos</div>
        </div>
        <div>
            <button type="button" class="btn-success-action" onclick="abrirModalIngresoCobro()">+ Registrar Cobro de Orden</button>
            <button type="button" class="btn-action" onclick="abrirModalArqueo()">Realizar Arqueo Ciego del Día</button>
        </div>
    </div>

    <div class="cg-metrics">
        <div class="metric-card warning">
            <div class="metric-label">Efectivo Cobrado Hoy (Caja General)</div>
            <div class="metric-value">${{ number_format($totalEfectivoCalculado, 2) }}</div>
        </div>
        <div class="metric-card info">
            <div class="metric-label">Cobrado Hoy en Bancos (Tarjeta / Transf)</div>
            <div class="metric-value">${{ number_format($totalBancosCalculado, 2) }}</div>
        </div>
        <div class="metric-card success">
            <div class="metric-label">Total Cobros Registrados Hoy</div>
            <div class="metric-value">{{ count($cobrosEfectivo) + count($cobrosBancos) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Estado de Cierre Hoy</div>
            <div class="metric-value" style="font-size: 1.2rem; margin-top: 12px;">
                @if(count($arqueos) > 0 && isset($arqueos[0]['fecha']) && \Carbon\Carbon::parse($arqueos[0]['fecha'])->isToday())
                    <span class="badge badge-exacto">Arqueado Hoy</span>
                @else
                    <span class="badge badge-pendiente">Pendiente Arqueo</span>
                @endif
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: COBROS EN EFECTIVO (CAJA GENERAL) -->
    <div class="cg-card">
        <div class="cg-card-title">
            <span>Cobros de Cliente Externo — Efectivo (Ingresan a Caja General)</span>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 400;">Sumados para el Arqueo Ciego Diario</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Cliente</th>
                        <th>Equipo / Serie</th>
                        <th>Método de Pago</th>
                        <th>Destino Cuenta</th>
                        <th>Monto Cobrado</th>
                        <th>Registrado Por</th>
                        <th>Fecha / Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobrosEfectivo as $cbr)
                        @php $cObj = (object) $cbr; @endphp
                        <tr>
                            <td><strong>{{ $cObj->nro_orden }}</strong></td>
                            <td>{{ $cObj->cliente_nombre }}</td>
                            <td>{{ $cObj->equipo_info ?? 'N/A' }}</td>
                            <td><span class="badge badge-efectivo">{{ $cObj->metodo_pago }}</span></td>
                            <td><strong>Caja General</strong></td>
                            <td><strong style="color: #059669;">${{ number_format((float)$cObj->monto_cobrado, 2) }}</strong></td>
                            <td>{{ $cObj->usuario_nombre }}</td>
                            <td>{{ \Carbon\Carbon::parse($cObj->fecha_cobro)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">No hay cobros en efectivo registrados hoy en Caja General.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 2: COBROS EN BANCOS (TARJETA / TRANSFERENCIA / DEPÓSITO) -->
    <div class="cg-card">
        <div class="cg-card-title">
            <span>Cobros de Cliente Externo — Tarjetas y Transferencias (Ingresan a Bancos)</span>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 400;">Datafast, Kushki, Transferencias y Depósitos</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Cliente</th>
                        <th>Equipo / Serie</th>
                        <th>Método de Pago</th>
                        <th>Destino Cuenta</th>
                        <th>Monto Cobrado</th>
                        <th>Registrado Por</th>
                        <th>Fecha / Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobrosBancos as $cbr)
                        @php $cObj = (object) $cbr; @endphp
                        <tr>
                            <td><strong>{{ $cObj->nro_orden }}</strong></td>
                            <td>{{ $cObj->cliente_nombre }}</td>
                            <td>{{ $cObj->equipo_info ?? 'N/A' }}</td>
                            <td><span class="badge badge-bancos">{{ $cObj->metodo_pago }}</span></td>
                            <td><strong>Bancos</strong></td>
                            <td><strong style="color: #0284c7;">${{ number_format((float)$cObj->monto_cobrado, 2) }}</strong></td>
                            <td>{{ $cObj->usuario_nombre }}</td>
                            <td>{{ \Carbon\Carbon::parse($cObj->fecha_cobro)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">No hay cobros bancarios o por tarjeta registrados hoy.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 3: HISTORIAL DE ARQUEOS Y CIERRES DIARIOS -->
    <div class="cg-card">
        <div class="cg-card-title">Historial de Arqueos y Cierres Diarios</div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto Sistema (Efectivo)</th>
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
    let ordenSeleccionada = null;

    function abrirModalIngresoCobro() {
        ordenSeleccionada = null;

        Swal.fire({
            title: 'Registrar Cobro Manual (Cliente Externo)',
            width: '600px',
            html: `
                <div style="text-align: left; font-size: 0.875rem; color: #0f172a;">
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 700; color: #0f172a;">1. Buscar / Digitar Número de Orden de Trabajo:</label>
                        <div style="display: flex; gap: 8px; margin-top: 4px;">
                            <input type="text" id="swal-search-orden" class="swal2-input" placeholder="Ej. OT-1002 o Nombre de cliente..." style="margin: 0; flex: 1;">
                            <button type="button" class="btn-action" onclick="buscarOrdenAjax()">Buscar</button>
                        </div>
                        <div id="swal-results-box" class="search-results-box"></div>
                    </div>

                    <div id="swal-orden-info" style="display: none; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 14px;">
                        <div><strong>Orden:</strong> <span id="info-nro-orden"></span></div>
                        <div><strong>Cliente:</strong> <span id="info-cliente"></span></div>
                        <div><strong>Equipo:</strong> <span id="info-equipo"></span></div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 700; color: #0f172a;">2. Valor Cobrado ($):</label>
                        <input type="number" step="0.01" id="swal-monto-cobrado" class="swal2-input" placeholder="0.00" style="margin-top: 4px;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 700; color: #0f172a;">3. Método de Pago:</label>
                        <select id="swal-metodo-pago" class="swal2-input" onchange="actualizarDestinoCuenta()" style="margin-top: 4px;">
                            <option value="Efectivo">Efectivo (Caja General)</option>
                            <option value="Tarjeta Datafast/Kushki">Tarjeta Datafast / Kushki (Bancos)</option>
                            <option value="Transferencia Bancaria">Transferencia Bancaria (Bancos)</option>
                            <option value="Depósito Bancario">Depósito Bancario (Bancos)</option>
                        </select>
                    </div>

                    <div id="swal-destino-notice" style="background: #dcfce7; color: #15803d; padding: 10px 14px; border-radius: 8px; font-weight: 700; border: 1px solid #86efac; margin-bottom: 12px;">
                        Cuenta Destino: CAJA GENERAL (Control de Efectivo en Recepción)
                    </div>

                    <div>
                        <label style="font-weight: 700; color: #0f172a;">4. Observaciones / Nro. Boucher (Opcional):</label>
                        <textarea id="swal-cobro-obs" class="swal2-textarea" placeholder="Nro. de boucher, referencia o nota..." style="margin-top: 4px; height: 65px;"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar Cobro',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            preConfirm: () => {
                const searchNro = document.getElementById('swal-search-orden').value.trim();
                const monto = parseFloat(document.getElementById('swal-monto-cobrado').value);
                const metodo = document.getElementById('swal-metodo-pago').value;
                const obs = document.getElementById('swal-cobro-obs').value;

                const nroOrden = ordenSeleccionada ? ordenSeleccionada.nro_orden : searchNro;
                const ordenId = ordenSeleccionada ? ordenSeleccionada.id : null;
                const clienteNombre = ordenSeleccionada ? ordenSeleccionada.cliente : 'Cliente Externo';
                const equipoInfo = ordenSeleccionada ? ordenSeleccionada.equipo : '';

                if (!nroOrden) {
                    Swal.showValidationMessage('Debe digitar o seleccionar una orden de trabajo.');
                    return false;
                }
                if (isNaN(monto) || monto <= 0) {
                    Swal.showValidationMessage('Debe ingresar un monto cobrado válido mayor a $0.00.');
                    return false;
                }

                return {
                    orden_id: ordenId,
                    nro_orden: nroOrden,
                    cliente_nombre: clienteNombre,
                    equipo_info: equipoInfo,
                    monto_cobrado: monto,
                    metodo_pago: metodo,
                    observaciones: obs
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarCobro(result.value);
            }
        });
    }

    function buscarOrdenAjax() {
        const q = document.getElementById('swal-search-orden').value.trim();
        const box = document.getElementById('swal-results-box');
        if (!q || q.length < 2) {
            box.style.display = 'none';
            return;
        }

        fetch("{{ route('cajageneral.buscar_orden') }}?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(res => {
                if (res.ok && res.ordenes && res.ordenes.length > 0) {
                    let html = '';
                    res.ordenes.forEach(o => {
                        html += `
                            <div class="search-item" onclick='seleccionarOrden(${JSON.stringify(o)})'>
                                <strong>${o.nro_orden}</strong> — ${o.cliente}<br>
                                <span style="font-size: 0.78rem; color: #64748b;">${o.equipo} | Sugerido: $${o.total_sugerido.toFixed(2)}</span>
                            </div>
                        `;
                    });
                    box.innerHTML = html;
                    box.style.display = 'block';
                } else {
                    box.innerHTML = '<div style="padding: 10px; color: #94a3b8;">No se encontraron órdenes con ese criterio.</div>';
                    box.style.display = 'block';
                }
            })
            .catch(() => {
                box.style.display = 'none';
            });
    }

    function seleccionarOrden(ord) {
        ordenSeleccionada = ord;
        document.getElementById('swal-search-orden').value = ord.nro_orden;
        document.getElementById('swal-results-box').style.display = 'none';

        document.getElementById('info-nro-orden').innerText = ord.nro_orden;
        document.getElementById('info-cliente').innerText = ord.cliente;
        document.getElementById('info-equipo').innerText = ord.equipo;
        document.getElementById('swal-orden-info').style.display = 'block';

        if (ord.total_sugerido && ord.total_sugerido > 0) {
            document.getElementById('swal-monto-cobrado').value = ord.total_sugerido.toFixed(2);
        }
    }

    function actualizarDestinoCuenta() {
        const metodo = document.getElementById('swal-metodo-pago').value;
        const notice = document.getElementById('swal-destino-notice');

        if (metodo === 'Efectivo') {
            notice.style.background = '#dcfce7';
            notice.style.color = '#15803d';
            notice.style.borderColor = '#86efac';
            notice.innerText = 'Cuenta Destino: CAJA GENERAL (Control de Efectivo en Recepción)';
        } else {
            notice.style.background = '#e0f2fe';
            notice.style.color = '#0369a1';
            notice.style.borderColor = '#7dd3fc';
            notice.innerText = 'Cuenta Destino: BANCOS (Cuenta Bancaria - Tarjeta / Transferencia)';
        }
    }

    function enviarCobro(payload) {
        fetch("{{ route('cajageneral.guardar_cobro') }}", {
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
                Swal.fire('Cobro Guardado', res.mensaje, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.error || 'No se pudo guardar el cobro.', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Fallo de conexión al servidor.', 'error'));
    }

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
