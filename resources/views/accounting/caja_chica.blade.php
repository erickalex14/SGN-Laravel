@extends('layouts.app')
@section('titulo', 'Control de Caja Chica')

@section('contenido')
<div class="container-fluid px-4 py-3" style="max-width: 1400px;">
    <!-- Encabezado de Página -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 mb-0 text-gray-800" style="font-weight:700; color:#0f766e;">
                <i class="bi bi-wallet2 me-2"></i>Control de Caja Chica
            </h1>
            <p class="text-muted mb-0 small">Módulo de tesorería y contabilidad general para la sucursal de {{$sucursalNombre}}</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-teal p-2 fs-6" style="background-color: #0f766e;">
                Centro de Costo: <strong>{{$codigoSucursal}}</strong>
            </span>
        </div>
    </div>

    <!-- Sección: No hay caja chica abierta -->
    <div id="no-caja-container" class="card shadow-sm border-0 mb-4 p-5 text-center" style="display:none; border-radius: 12px;">
        <div class="my-3">
            <i class="bi bi-wallet2" style="font-size: 4rem; color: #64748b;"></i>
        </div>
        <h3 class="h4" style="font-weight:600; color:#334155;">No hay ninguna Caja Chica activa</h3>
        <p class="text-muted mx-auto" style="max-width: 500px;">
            Para comenzar a registrar comprobantes y facturas de compras de la sucursal, es necesario iniciar un nuevo periodo de Caja Chica. El fondo fijo asignado es de $1,000.00.
        </p>
        @if($esSuperAdmin || $sucursalId == 1737 || $sucursalNombre == 'QUITO') {{-- Permitir si es Quito o Superadmin --}}
        <div class="mt-4">
            <button type="button" class="btn btn-lg btn-teal" style="background:#0f766e; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:600;" onclick="abrirNuevaCaja()">
                <i class="bi bi-plus-circle me-2"></i>Abrir Caja Chica ($1,000.00)
            </button>
        </div>
        @else
        <div class="alert alert-warning d-inline-block mx-auto mt-3">
            Solo el custodio asignado o los administradores pueden abrir el periodo de caja chica para esta sucursal.
        </div>
        @endif
    </div>

    <!-- Sección Principal (Caja Activa) -->
    <div id="caja-activa-container" style="display:none;">
        <!-- Fila de Tarjetas de Estadísticas -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3" style="border-radius:10px; border-left: 4px solid #0f766e !important;">
                    <div class="text-muted small text-uppercase fw-bold">Fondo Inicial</div>
                    <div class="h3 mb-0 mt-2 fw-bold text-dark" id="stat-fondo-inicial">$0.00</div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3" style="border-radius:10px; border-left: 4px solid #e11d48 !important;">
                    <div class="text-muted small text-uppercase fw-bold">Total Gastado</div>
                    <div class="h3 mb-0 mt-2 fw-bold text-danger" id="stat-total-gastado">$0.00</div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3" style="border-radius:10px; border-left: 4px solid #16a34a !important;">
                    <div class="text-muted small text-uppercase fw-bold">Saldo Disponible</div>
                    <div class="h3 mb-0 mt-2 fw-bold text-success" id="stat-saldo-disponible">$0.00</div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3" style="border-radius:10px; border-left: 4px solid #d97706 !important;">
                    <div class="text-muted small text-uppercase fw-bold">Vueltos Pendientes</div>
                    <div class="h3 mb-0 mt-2 fw-bold text-warning" id="stat-vueltos-pendientes">$0.00</div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Control -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between" style="border-radius: 12px 12px 0 0;">
                <div class="d-flex align-items-center gap-3">
                    <span class="px-3 py-1 bg-light text-dark fw-bold rounded" id="info-nro-caja" style="font-size: 14px;">-</span>
                    <span class="badge" id="info-estado" style="font-size: 12px; padding:6px 10px;">-</span>
                    <span class="text-muted small"><i class="bi bi-person me-1"></i>Custodio: <strong id="info-custodio">-</strong></span>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <button type="button" class="btn btn-sm btn-teal" style="background:#0f766e; color:#fff; border:none;" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel me-1"></i>Descargar Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-cerrar-caja" onclick="cerrarCajaChica()">
                        <i class="bi bi-lock me-1"></i>Cerrar Caja
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="btn-reembolsar-caja" style="display:none;" onclick="abrirModalReembolso()">
                        <i class="bi bi-cash-coin me-1"></i>Reembolsar y Recargar
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <!-- Botón de Agregar Comprobante (Solo si está Abierta) -->
                <div class="p-3 bg-light d-flex justify-content-between align-items-center border-top border-bottom">
                    <span class="text-muted small">Listado de facturas, notas de venta y vales de caja</span>
                    <button type="button" class="btn btn-sm btn-teal" id="btn-agregar-item" style="background:#0f766e; color:#fff; border:none; padding:6px 16px; border-radius:6px;" onclick="mostrarModalAgregarItem()">
                        <i class="bi bi-plus-lg me-1"></i>Agregar Comprobante
                    </button>
                </div>

                <!-- Tabla de Transacciones -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabla-detalles" style="font-size: 13px;">
                        <thead class="table-light text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-3" style="width: 50px;">Item</th>
                                <th style="width: 100px;">Fecha</th>
                                <th style="width: 140px;">Nro. Comprobante</th>
                                <th style="width: 180px;">Proveedor</th>
                                <th>Descripción</th>
                                <th style="width: 150px;">Tipo de Gasto</th>
                                <th class="text-end" style="width: 100px;">Subt. 0%</th>
                                <th class="text-end" style="width: 100px;">Subt. IVA</th>
                                <th class="text-end" style="width: 80px;">IVA</th>
                                <th class="text-end" style="width: 100px;">Total</th>
                                <th class="text-end" style="width: 100px; background-color: #f8fafc;">V. Entregado</th>
                                <th style="width: 140px; background-color: #f8fafc;">Beneficiario</th>
                                <th class="text-end" style="width: 100px; background-color: #f8fafc;">Vuelto</th>
                                <th class="text-center" style="width: 120px; background-color: #f8fafc;">Est. Vuelto</th>
                                <th class="text-center" style="width: 90px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="detalles-body">
                            <!-- Filas dinámicas por javascript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Cajas Chicas Cerradas/Reembolsadas -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark" style="font-size: 15px;"><i class="bi bi-clock-history me-2"></i>Historial de Cajas Chicas</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Código Caja Chica</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Cierre</th>
                        <th>Custodio</th>
                        <th class="text-end">Fondo Inicial</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="historial-body">
                    <!-- Dinámico -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Agregar/Editar Ítem -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header border-0 bg-light py-3" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold text-teal" id="itemModalLabel" style="color: #0f766e;"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="itemForm" onsubmit="guardarItem(event)">
                <input type="hidden" id="form-item-id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Fecha del Comprobante *</label>
                            <input type="date" class="form-control" id="form-fecha" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Nro. de Comprobante / Factura / Vale *</label>
                            <input type="text" class="form-control" id="form-nro" placeholder="Ej: 001-001-000000001 o Vale 01" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Proveedor *</label>
                            <input type="text" class="form-control" id="form-proveedor" placeholder="Nombre completo del proveedor" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Tipo de Gasto *</label>
                            <select class="form-select" id="form-tipo-gasto" required>
                                <option value="">Seleccione el tipo...</option>
                                <option value="Adecuaciones locales">Adecuaciones locales</option>
                                <option value="Alimentación">Alimentación</option>
                                <option value="Cafetería">Cafetería</option>
                                <option value="Credenciales">Credenciales</option>
                                <option value="Hospedaje">Hospedaje</option>
                                <option value="Movilización">Movilización</option>
                                <option value="Recarga celulares">Recarga celulares</option>
                                <option value="Suministros de aseo">Suministros de aseo</option>
                                <option value="Suministros de oficina">Suministros de oficina</option>
                                <option value="Otros gastos">Otros gastos</option>
                                <option value="Gastos no deducibles">Gastos no deducibles</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Descripción detallada *</label>
                            <textarea class="form-control" id="form-descripcion" rows="2" placeholder="Motivo de la compra o actividad gestionada..." required></textarea>
                        </div>
                        
                        <div class="col-12"><hr class="my-2"></div>
                        
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">Sub Total Sin IVA (Tarifa 0%)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="form-subtotal-sin" value="0.00" oninput="actualizarCalculosForm()">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">Sub Total Con IVA (Sujeto a 15%)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="form-subtotal-con" value="0.00" oninput="actualizarCalculosForm()">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-teal small">Total Calculado</label>
                            <div class="input-group">
                                <span class="input-group-text bg-teal-light text-teal fw-bold">$</span>
                                <input type="text" class="form-control fw-bold text-teal bg-light" id="form-total" value="0.00" readonly>
                            </div>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        
                        <!-- Nuevos campos solicitados -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-primary">Valor Entregado (Efectivo)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control border-primary" id="form-entregado" value="0.00" oninput="actualizarCalculosForm()">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-primary">Usuario Beneficiado / Empleado</label>
                            <input type="text" class="form-control border-primary" id="form-beneficiario" placeholder="Nombre de quien recibe">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-primary">Estado Vuelto</label>
                            <select class="form-select border-primary" id="form-estado-vuelto">
                                <option value="No Aplica">No Aplica</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Devuelto">Devuelto</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <span class="small text-muted">Vuelto esperado: <strong id="form-vuelto-esperado-label">$0.00</strong></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-teal" style="background:#0f766e; color:#fff; border:none; padding:8px 20px; font-weight:600;">
                        <i class="bi bi-save me-1"></i>Guardar Comprobante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reembolsar y Recargar -->
<div class="modal fade" id="reimburseModal" tabindex="-1" aria-labelledby="reimburseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header border-0 bg-light py-3" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold text-success" style="color:#16a34a;"><i class="bi bi-cash-coin me-2"></i>Aprobar Reembolso Contable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reimburseForm" onsubmit="reembolsarCajaChica(event)">
                <div class="modal-body p-4">
                    <p class="text-muted small">
                        Al reembolsar esta caja chica, se cambiará su estado a <strong>Reembolsada</strong> y se generará una nueva caja chica abierta para el siguiente periodo.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Monto de Recarga Manual ($) *</label>
                        <input type="number" step="0.01" class="form-control form-control-lg fw-bold text-success" id="reimburse-monto" required>
                        <div class="form-text small" id="reimburse-suggested-text">
                            Sugerido para completar los $1,000.00: <strong>$0.00</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" style="background:#16a34a; color:#fff; border:none; padding:8px 20px; font-weight:600;">
                        <i class="bi bi-check-circle me-1"></i>Autorizar Recarga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // Variables de configuración inyectadas
    const _jwtToken = @json($token);
    const _apiUrl = @json($apiUrl);
    const _sucursalId = @json($sucursalId);
    const _codigoSucursal = @json($codigoSucursal);
    const _sucursalNombre = @json($sucursalNombre);
    const _esSuperAdmin = @json($esSuperAdmin);

    // Estado local de la aplicación
    let activeCaja = null;
    let itemsModal = null;
    let reimburseModal = null;

    document.addEventListener('DOMContentLoaded', () => {
        itemsModal = new bootstrap.Modal(document.getElementById('itemModal'));
        reimburseModal = new bootstrap.Modal(document.getElementById('reimburseModal'));
        
        cargarModulo();
    });

    // Encabezado para peticiones al microservicio en .NET
    function getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + _jwtToken
        };
    }

    async function cargarModulo() {
        try {
            // Cargar cajas chicas
            const res = await fetch(`${_apiUrl}/api/cajachica`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await res.json();
            if (!data.ok) {
                Swal.fire('Error', data.error || 'No se pudo conectar con el microservicio contable.', 'error');
                return;
            }

            const cajas = data.data;
            
            // Buscar si hay alguna caja chica Abierta o Cerrada (activa)
            activeCaja = cajas.find(c => c.estado === 'Abierta' || c.estado === 'Cerrada');

            if (activeCaja) {
                // Si hay caja activa, cargar sus detalles completos
                const detailRes = await fetch(`${_apiUrl}/api/cajachica/${activeCaja.id}`, {
                    method: 'GET',
                    headers: getHeaders()
                });
                const detailData = await detailRes.json();
                if (detailData.ok) {
                    activeCaja = detailData.data;
                }

                document.getElementById('no-caja-container').style.display = 'none';
                document.getElementById('caja-activa-container').style.display = 'block';
                renderCajaActiva(activeCaja);
            } else {
                document.getElementById('caja-activa-container').style.display = 'none';
                document.getElementById('no-caja-container').style.display = 'block';
            }

            // Historial de cajas
            renderHistorial(cajas);

        } catch (e) {
            console.error(e);
            Swal.fire('Error de conexión', 'No se pudo contactar con el microservicio contable en ' + _apiUrl, 'error');
        }
    }

    function renderCajaActiva(caja) {
        // Cabecera info
        document.getElementById('info-nro-caja').innerText = caja.nroCajaChica;
        document.getElementById('info-custodio').innerText = caja.custodioNombre;
        
        const estEl = document.getElementById('info-estado');
        estEl.innerText = caja.estado;
        estEl.className = 'badge';
        if (caja.estado === 'Abierta') {
            estEl.classList.add('bg-success');
            document.getElementById('btn-cerrar-caja').style.display = 'inline-block';
            document.getElementById('btn-reembolsar-caja').style.display = 'none';
            document.getElementById('btn-agregar-item').style.display = 'inline-block';
        } else {
            estEl.classList.add('bg-danger');
            document.getElementById('btn-cerrar-caja').style.display = 'none';
            document.getElementById('btn-agregar-item').style.display = 'none';
            // Solo superadmin puede reembolsar
            if (_esSuperAdmin) {
                document.getElementById('btn-reembolsar-caja').style.display = 'inline-block';
            } else {
                document.getElementById('btn-reembolsar-caja').style.display = 'none';
            }
        }

        // Estadísticas
        const totalGastado = caja.detalles.reduce((acc, d) => acc + Number(d.total), 0);
        const saldoDisponible = Number(caja.fondoInicial) - totalGastado;
        const vueltosPendientes = caja.detalles
            .filter(d => d.estadoVuelto === 'Pendiente')
            .reduce((acc, d) => acc + Number(d.vueltoEsperado), 0);

        document.getElementById('stat-fondo-inicial').innerText = '$' + Number(caja.fondoInicial).toFixed(2);
        document.getElementById('stat-total-gastado').innerText = '$' + totalGastado.toFixed(2);
        document.getElementById('stat-saldo-disponible').innerText = '$' + saldoDisponible.toFixed(2);
        document.getElementById('stat-vueltos-pendientes').innerText = '$' + vueltosPendientes.toFixed(2);

        // Render filas de la tabla
        const tbody = document.getElementById('detalles-body');
        tbody.innerHTML = '';

        if (caja.detalles.length === 0) {
            tbody.innerHTML = `<tr><td colspan="15" class="text-center text-muted p-4"><i class="bi bi-info-circle me-1"></i>No hay comprobantes registrados en este periodo de caja chica.</td></tr>`;
            return;
        }

        caja.detalles.forEach((d, idx) => {
            const tr = document.createElement('tr');
            
            // Botones de acción según el estado
            let btnEditDel = '';
            if (caja.estado === 'Abierta') {
                btnEditDel = `
                    <button type="button" class="btn btn-sm btn-outline-primary p-1 py-0 me-1" onclick="editarItem(${d.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1 py-0" onclick="eliminarItem(${d.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                `;
            } else {
                btnEditDel = `<span class="text-muted small"><i class="bi bi-lock-fill"></i> Bloqueado</span>`;
            }

            // Badge de vuelto
            let vueltoBadge = '';
            if (d.estadoVuelto === 'Pendiente') {
                vueltoBadge = `<span class="badge bg-warning text-dark" style="cursor:pointer;" onclick="toggleEstadoVueltoDirecto(${d.id}, 'Devuelto')" title="Haga clic para marcar como Devuelto"><i class="bi bi-exclamation-triangle-fill me-1"></i>Pendiente</span>`;
            } else if (d.estadoVuelto === 'Devuelto') {
                vueltoBadge = `<span class="badge bg-success" style="cursor:pointer;" onclick="toggleEstadoVueltoDirecto(${d.id}, 'Pendiente')" title="Haga clic para marcar como Pendiente"><i class="bi bi-check-circle-fill me-1"></i>Devuelto</span>`;
            } else {
                vueltoBadge = `<span class="text-muted small">No Aplica</span>`;
            }

            tr.innerHTML = `
                <td class="ps-3 fw-bold">${idx + 1}</td>
                <td>${formatFecha(d.fechaComprobante)}</td>
                <td class="fw-semibold">${_h(d.nroComprobante)}</td>
                <td>${_h(d.proveedor)}</td>
                <td class="text-wrap" style="max-width: 250px;">${_h(d.descripcion)}</td>
                <td><span class="badge bg-secondary py-1 px-2">${_h(d.tipoGasto)}</span></td>
                <td class="text-end">$${Number(d.subtotalSinIva).toFixed(2)}</td>
                <td class="text-end">$${Number(d.subtotalConIva).toFixed(2)}</td>
                <td class="text-end text-muted">$${Number(d.iva).toFixed(2)}</td>
                <td class="text-end fw-semibold">$${Number(d.total).toFixed(2)}</td>
                <td class="text-end font-monospace" style="background-color: #f8fafc;">$${Number(d.valorEntregado).toFixed(2)}</td>
                <td class="text-wrap" style="background-color: #f8fafc; max-width:120px;">${_h(d.usuarioBeneficiado ?? '')}</td>
                <td class="text-end font-monospace fw-semibold" style="background-color: #f8fafc; color:#b45309;">$${Number(d.vueltoEsperado).toFixed(2)}</td>
                <td class="text-center" style="background-color: #f8fafc;">${vueltoBadge}</td>
                <td class="text-center">${btnEditDel}</td>
            `;

            tbody.appendChild(tr);
        });
    }

    function renderHistorial(cajas) {
        const tbody = document.getElementById('historial-body');
        tbody.innerHTML = '';

        const historicas = cajas.filter(c => c.estado !== 'Abierta' && c.estado !== 'Cerrada');

        if (historicas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted p-3">No hay periodos de caja chica anteriores en el historial.</td></tr>`;
            return;
        }

        historicas.forEach(c => {
            const tr = document.createElement('tr');
            let badgeClass = c.estado === 'Reembolsada' ? 'bg-primary' : 'bg-secondary';
            tr.innerHTML = `
                <td class="ps-3 fw-bold">${c.nroCajaChica}</td>
                <td>${formatFecha(c.fechaCreacion)}</td>
                <td>${c.fechaCierre ? formatFecha(c.fechaCierre) : '-'}</td>
                <td>${c.custodioNombre}</td>
                <td class="text-end fw-semibold">$${Number(c.fondoInicial).toFixed(2)}</td>
                <td class="text-center"><span class="badge ${badgeClass}">${c.estado}</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-teal" onclick="exportarExcelHistorial(${c.id})">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Abrir nueva caja chica (inicializa la cabecera)
    async function abrirNuevaCaja() {
        const confirm = await Swal.fire({
            title: '¿Abrir Caja Chica?',
            text: 'Se iniciará un periodo de caja chica de $1,000.00 para la sucursal de ' + _sucursalNombre,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, abrir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0f766e'
        });

        if (!confirm.isConfirmed) return;

        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    sucursalId: _sucursalId,
                    codigoSucursal: _codigoSucursal,
                    fondoInicial: 1000.00
                })
            });

            const data = await res.json();
            if (data.ok) {
                await Swal.fire('¡Abierta!', 'Caja Chica iniciada con éxito.', 'success');
                cargarModulo();
            } else {
                Swal.fire('Error', data.error || 'No se pudo abrir la caja chica.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'No se pudo contactar con el servidor contable.', 'error');
        }
    }

    function mostrarModalAgregarItem() {
        document.getElementById('itemModalLabel').innerHTML = '<i class="bi bi-file-earmark-plus me-2"></i>Registrar Comprobante';
        document.getElementById('itemForm').reset();
        document.getElementById('form-item-id').value = '';
        document.getElementById('form-fecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('form-vuelto-esperado-label').innerText = '$0.00';
        
        itemsModal.show();
    }

    function actualizarCalculosForm() {
        const subtotalSin = parseFloat(document.getElementById('form-subtotal-sin').value || 0);
        const subtotalCon = parseFloat(document.getElementById('form-subtotal-con').value || 0);
        
        const iva = Math.round(subtotalCon * 0.15 * 100) / 100;
        const total = subtotalSin + subtotalCon + iva;
        
        document.getElementById('form-total').value = total.toFixed(2);

        // Vuelto
        const entregado = parseFloat(document.getElementById('form-entregado').value || 0);
        const vueltoLabel = document.getElementById('form-vuelto-esperado-label');
        const estadoVueltoSelect = document.getElementById('form-estado-vuelto');

        if (entregado > total) {
            const vuelto = entregado - total;
            vueltoLabel.innerText = '$' + vuelto.toFixed(2);
            // Si el vuelto es mayor a 0, activar "Pendiente" por defecto
            if (estadoVueltoSelect.value === 'No Aplica') {
                estadoVueltoSelect.value = 'Pendiente';
            }
        } else {
            vueltoLabel.innerText = '$0.00';
            estadoVueltoSelect.value = 'No Aplica';
        }
    }

    async function guardarItem(e) {
        e.preventDefault();
        
        const itemId = document.getElementById('form-item-id').value;
        const payload = {
            fechaComprobante: document.getElementById('form-fecha').value,
            nroComprobante: document.getElementById('form-nro').value.trim(),
            proveedor: document.getElementById('form-proveedor').value.trim(),
            tipoGasto: document.getElementById('form-tipo-gasto').value,
            descripcion: document.getElementById('form-descripcion').value.trim(),
            subtotalSinIva: parseFloat(document.getElementById('form-subtotal-sin').value || 0),
            subtotalConIva: parseFloat(document.getElementById('form-subtotal-con').value || 0),
            valorEntregado: parseFloat(document.getElementById('form-entregado').value || 0),
            usuarioBeneficiado: document.getElementById('form-beneficiario').value.trim() || null,
            estadoVuelto: document.getElementById('form-estado-vuelto').value
        };

        Swal.showLoading();

        try {
            let url = `${_apiUrl}/api/cajachica/${activeCaja.id}/items`;
            let method = 'POST';

            if (itemId) {
                url = `${_apiUrl}/api/cajachica/items/${itemId}`;
                method = 'PUT';
            }

            const res = await fetch(url, {
                method: method,
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.ok) {
                itemsModal.hide();
                await Swal.fire('¡Éxito!', data.message || 'Comprobante guardado.', 'success');
                cargarModulo();
            } else {
                Swal.fire('Error', data.error || 'No se pudo registrar el comprobante.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión con el microservicio contable.', 'error');
        }
    }

    async function editarItem(itemId) {
        const item = activeCaja.detalles.find(d => d.id === itemId);
        if (!item) return;

        document.getElementById('itemModalLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Comprobante';
        document.getElementById('form-item-id').value = item.id;
        document.getElementById('form-fecha').value = item.fechaComprobante.split('T')[0];
        document.getElementById('form-nro').value = item.nroComprobante;
        document.getElementById('form-proveedor').value = item.proveedor;
        document.getElementById('form-tipo-gasto').value = item.tipoGasto;
        document.getElementById('form-descripcion').value = item.descripcion;
        document.getElementById('form-subtotal-sin').value = item.subtotalSinIva;
        document.getElementById('form-subtotal-con').value = item.subtotalConIva;
        document.getElementById('form-entregado').value = item.valorEntregado;
        document.getElementById('form-beneficiario').value = item.usuarioBeneficiado || '';
        document.getElementById('form-estado-vuelto').value = item.estadoVuelto;

        actualizarCalculosForm();
        itemsModal.show();
    }

    async function eliminarItem(itemId) {
        const confirm = await Swal.fire({
            title: '¿Eliminar comprobante?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e11d48'
        });

        if (!confirm.isConfirmed) return;

        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/items/${itemId}`, {
                method: 'DELETE',
                headers: getHeaders()
            });

            const data = await res.json();
            if (data.ok) {
                await Swal.fire('Eliminado', 'Comprobante eliminado con éxito.', 'success');
                cargarModulo();
            } else {
                Swal.fire('Error', data.error || 'No se pudo eliminar el comprobante.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    }

    // Toggle rápido de vuelto desde la fila
    async function toggleEstadoVueltoDirecto(itemId, nuevoEstado) {
        const item = activeCaja.detalles.find(d => d.id === itemId);
        if (!item) return;

        Swal.showLoading();

        try {
            const payload = {
                fechaComprobante: item.fechaComprobante,
                nroComprobante: item.nroComprobante,
                proveedor: item.proveedor,
                tipoGasto: item.tipoGasto,
                descripcion: item.descripcion,
                subtotalSinIva: item.subtotalSinIva,
                subtotalConIva: item.subtotalConIva,
                valorEntregado: item.valorEntregado,
                usuarioBeneficiado: item.usuarioBeneficiado,
                estadoVuelto: nuevoEstado
            };

            const res = await fetch(`${_apiUrl}/api/cajachica/items/${itemId}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.ok) {
                Swal.close();
                cargarModulo();
            } else {
                Swal.fire('Error', data.error || 'No se pudo cambiar el estado.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    }

    async function cerrarCajaChica() {
        const confirm = await Swal.fire({
            title: '¿Cerrar Caja Chica?',
            text: 'Una vez cerrada, no se podrán añadir ni modificar más facturas. Quedará pendiente de aprobación de reembolso por contabilidad.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar caja',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0f766e'
        });

        if (!confirm.isConfirmed) return;

        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/${activeCaja.id}/close`, {
                method: 'POST',
                headers: getHeaders()
            });

            const data = await res.json();
            if (data.ok) {
                await Swal.fire('Cerrada', 'La Caja Chica se cerró con éxito.', 'success');
                cargarModulo();
            } else {
                Swal.fire('Error', data.error || 'No se pudo cerrar la caja.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    }

    function abrirModalReembolso() {
        const totalGastado = activeCaja.detalles.reduce((acc, d) => acc + Number(d.total), 0);
        document.getElementById('reimburse-monto').value = totalGastado.toFixed(2);
        document.getElementById('reimburse-suggested-text').innerHTML = `Sugerido para completar los $1,000.00: <strong>$${totalGastado.toFixed(2)}</strong>`;
        
        reimburseModal.show();
    }

    async function reembolsarCajaChica(e) {
        e.preventDefault();

        const monto = parseFloat(document.getElementById('reimburse-monto').value || 0);

        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/${activeCaja.id}/reimburse`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    montoRecarga: monto
                })
            });

            const data = await res.json();
            if (data.ok) {
                reimburseModal.hide();
                await Swal.fire('Reembolsada', 'Caja Chica reembolsada y nuevo fondo iniciado correctamente.', 'success');
                cargarModulo();
            } else {
                Swal.fire('Error', data.error || 'No se pudo completar el reembolso.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    }

    async function exportarExcel() {
        if (!activeCaja) return;
        exportarExcelHistorial(activeCaja.id);
    }

    async function exportarExcelHistorial(id) {
        Swal.showLoading();
        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/${id}/export`, {
                method: 'GET',
                headers: getHeaders()
            });

            if (!res.ok) {
                const errData = await res.json();
                Swal.fire('Error', errData.error || 'No se pudo generar el reporte.', 'error');
                return;
            }

            const blob = await res.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Informe_Caja_Chica_${id}.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            
            Swal.close();
        } catch (e) {
            Swal.fire('Error', 'Error al descargar el archivo.', 'error');
        }
    }

    // Auxiliares
    function formatFecha(isoStr) {
        if (!isoStr) return '';
        const d = new Date(isoStr);
        // Ajustar huso horario local
        d.setMinutes(d.getMinutes() + d.getTimezoneOffset());
        return d.toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function _h(str) {
        if (!str) return '';
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endpush
