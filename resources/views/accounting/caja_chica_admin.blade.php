@extends('layouts.app')
@section('titulo', 'Administración de Caja Chica')

@section('contenido')
<div class="container-fluid px-4 py-3" style="max-width: 1400px;">
    <!-- Encabezado de Página -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 mb-0 text-gray-800" style="font-weight:700; color:#0f766e;">
                <i class="bi bi-shield-check me-2"></i>Administración de Caja Chica
            </h1>
            <p class="text-muted mb-0 small">Panel de control y autorizaciones de tesorería general para administradores</p>
        </div>
        <div>
            <button type="button" class="btn btn-teal" style="background:#0f766e; color:#fff; border:none; padding:8px 20px; font-weight:600;" onclick="mostrarModalAbrirCaja()">
                <i class="bi bi-plus-circle me-1"></i>Abrir Caja Chica
            </button>
        </div>
    </div>

    <!-- Lista de Cajas Chicas Activas / Historial -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark" style="font-size: 15px;">
                <i class="bi bi-list-stars me-2"></i>Monitoreo de Cajas Chicas por Sucursal
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nro. Caja</th>
                        <th>Sucursal</th>
                        <th>Custodio</th>
                        <th>Período Contable (23 al 22)</th>
                        <th class="text-end">Fondo Fijo</th>
                        <th class="text-end">Total Gastado</th>
                        <th class="text-end">Saldo Disponible</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 250px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="admin-cajas-body">
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">Cargando cajas chicas...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Abrir Caja Chica -->
<div class="modal fade" id="abrirCajaModal" tabindex="-1" aria-labelledby="abrirCajaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header border-0 bg-light py-3" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold text-teal" style="color: #0f766e;"><i class="bi bi-wallet2 me-2"></i>Abrir Periodo de Caja Chica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="abrirCajaForm" onsubmit="guardarNuevaCaja(event)">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Sucursal *</label>
                            <select class="form-select" id="open-sucursal" required onchange="actualizarCodigoSucursal()">
                                @foreach($sucursales as $s)
                                    <option value="{{$s->id}}" data-secuencial="{{$s->secuencial}}" {{$s->id == $sucursalId ? 'selected' : ''}}>{{$s->ciudad}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Custodio Responsable *</label>
                            <select class="form-select" id="open-custodio" required>
                                <option value="">Seleccione el custodio...</option>
                                @foreach($usuarios as $u)
                                    <option value="{{$u->id}}">{{$u->nombre_tecnico}} ({{$u->usuario}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Fondo Inicial ($) *</label>
                            <input type="number" step="0.01" class="form-control fw-bold" id="open-fondo" value="1000.00" required readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Fecha de Apertura (Inicio Periodo) *</label>
                            <input type="date" class="form-control" id="open-fecha" required>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 px-3 mb-0 small">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                El periodo contable comprende desde el **23 del mes seleccionado** hasta el **22 del mes siguiente**.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-teal" style="background:#0f766e; color:#fff; border:none; padding:8px 20px; font-weight:600;">
                        <i class="bi bi-check-circle me-1"></i>Abrir Caja
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
                <input type="hidden" id="reimburse-caja-id">
                <div class="modal-body p-4">
                    <p class="text-muted small">
                        Al reembolsar esta caja chica, se cambiará su estado a <strong>Reembolsada</strong> y se generará automáticamente la nueva caja chica abierta para el custodio con el fondo recargado a $1,000.00.
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

<!-- Modal: Ver Detalle / Auditoría -->
<div class="modal fade" id="detalleCajaModal" tabindex="-1" aria-labelledby="detalleCajaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header border-0 bg-light py-3" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-search me-2"></i>Auditoría de Caja Chica: <span id="audit-nro-caja"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Estadísticas Internas -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Fondo Fijo</span>
                            <div class="fw-bold fs-5 text-dark" id="audit-fondo">$0.00</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Gastado</span>
                            <div class="fw-bold fs-5 text-danger" id="audit-gastado">$0.00</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Disponible</span>
                            <div class="fw-bold fs-5 text-success" id="audit-disponible">$0.00</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Vueltos Pend.</span>
                            <div class="fw-bold fs-5 text-warning" id="audit-vueltos">$0.00</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Fecha</th>
                                <th>Nro. Comprobante</th>
                                <th>Descripción</th>
                                <th>Tipo Gasto</th>
                                <th class="text-end">Subt. 0%</th>
                                <th class="text-end">Subt. IVA</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">V. Entregado</th>
                                <th>Beneficiario</th>
                                <th class="text-end">Vuelto</th>
                                <th class="text-center">Estado Vuelto</th>
                            </tr>
                        </thead>
                        <tbody id="audit-detalles-body">
                            <!-- JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js_adicional')
<script>
    const _jwtToken = @json($token);
    const _apiUrl = @json($apiUrl);
    const _esSuperAdmin = @json($esSuperAdmin);

    let abrirCajaModal = null;
    let reimburseModal = null;
    let detalleCajaModal = null;

    document.addEventListener('DOMContentLoaded', () => {
        abrirCajaModal = new bootstrap.Modal(document.getElementById('abrirCajaModal'));
        reimburseModal = new bootstrap.Modal(document.getElementById('reimburseModal'));
        detalleCajaModal = new bootstrap.Modal(document.getElementById('detalleCajaModal'));

        // Default opening date to the 23rd of current month
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        document.getElementById('open-fecha').value = `${yyyy}-${mm}-23`;

        cargarCajasAdmin();
    });

    function getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + _jwtToken
        };
    }

    async function cargarCajasAdmin() {
        try {
            const res = await fetch(`${_apiUrl}/api/cajachica`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await res.json();
            if (!data.ok) {
                Swal.fire('Error', data.error || 'Error al obtener cajas chicas.', 'error');
                return;
            }

            renderCajasTable(data.data);
        } catch (e) {
            console.error(e);
            Swal.fire('Error de conexión', 'No se pudo contactar con el microservicio en ' + _apiUrl, 'error');
        }
    }

    function renderCajasTable(cajas) {
        const tbody = document.getElementById('admin-cajas-body');
        tbody.innerHTML = '';

        if (cajas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted p-4"><i class="bi bi-info-circle me-1"></i>No hay registros de cajas chicas en el sistema.</td></tr>`;
            return;
        }

        cajas.forEach(c => {
            const totalGastado = c.detalles ? c.detalles.reduce((acc, d) => acc + Number(d.total), 0) : 0;
            const saldoDisponible = Number(c.fondoInicial) - totalGastado;

            const tr = document.createElement('tr');

            // Badge de Estado
            let badgeClass = 'bg-secondary';
            if (c.estado === 'Abierta') badgeClass = 'bg-success';
            if (c.estado === 'Cerrada') badgeClass = 'bg-danger';
            if (c.estado === 'Reembolsada') badgeClass = 'bg-primary';

            // Botón Reembolsar (Recargar) - Solo si está cerrada y es SuperAdmin
            let actionButtons = `
                <button type="button" class="btn btn-sm btn-outline-info p-1 py-0 me-1" onclick="verAuditoria(${c.id})" title="Ver Detalles / Auditoría"><i class="bi bi-search"></i> Ver</button>
                <button type="button" class="btn btn-sm btn-outline-teal p-1 py-0 me-1" onclick="exportarExcel(${c.id})" title="Excel"><i class="bi bi-file-earmark-excel"></i></button>
            `;

            if (c.estado === 'Cerrada' && _esSuperAdmin) {
                actionButtons += `
                    <button type="button" class="btn btn-sm btn-success p-1 py-0 me-1" onclick="mostrarModalReembolso(${c.id}, ${totalGastado})" title="Reembolsar y Abrir Siguiente"><i class="bi bi-cash-coin"></i> Reembolsar</button>
                `;
            }

            if (c.estado === 'Abierta') {
                actionButtons += `
                    <button type="button" class="btn btn-sm btn-outline-danger p-1 py-0" onclick="forzarCerrarCaja(${c.id})" title="Forzar Cierre"><i class="bi bi-lock"></i> Cerrar</button>
                `;
            }

            tr.innerHTML = `
                <td class="ps-3 fw-bold">${c.nroCajaChica}</td>
                <td>${c.codigoSucursal}</td>
                <td>${c.custodioNombre}</td>
                <td>${formatPeriodo(c.fechaCreacion)}</td>
                <td class="text-end fw-semibold">$${Number(c.fondoInicial).toFixed(2)}</td>
                <td class="text-end text-danger">$${totalGastado.toFixed(2)}</td>
                <td class="text-end text-success fw-semibold">$${saldoDisponible.toFixed(2)}</td>
                <td class="text-center"><span class="badge ${badgeClass}">${c.estado}</span></td>
                <td class="text-center">${actionButtons}</td>
            `;

            tbody.appendChild(tr);
        });
    }

    function mostrarModalAbrirCaja() {
        abrirCajaModal.show();
    }

    function actualizarCodigoSucursal() {
        // Nada requerido, se lee en el submit
    }

    async function guardarNuevaCaja(e) {
        e.preventDefault();

        const selectSucursal = document.getElementById('open-sucursal');
        const selectCustodio = document.getElementById('open-custodio');
        
        const sucursalId = parseInt(selectSucursal.value);
        const optionSucursal = selectSucursal.options[selectSucursal.selectedIndex];
        const secuencial = optionSucursal.getAttribute('data-secuencial') || 'UIO';
        
        const optionCustodio = selectCustodio.options[selectCustodio.selectedIndex];
        const custodioNombre = optionCustodio.text.split(' (')[0];
        const custodioId = parseInt(selectCustodio.value);

        const fechaCreacion = document.getElementById('open-fecha').value;

        // Validar que la fecha elegida sea el 23
        const selectedDay = new Date(fechaCreacion + 'T00:00:00').getDate();
        if (selectedDay !== 23) {
            const confirm = await Swal.fire({
                title: 'Fecha no recomendada',
                text: 'El periodo contable reglamentario de Caja Chica debe iniciar el día 23 de cada mes. ¿Deseas continuar de todas formas?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'No, cambiar al 23',
                confirmButtonColor: '#0f766e'
            });
            if (!confirm.isConfirmed) return;
        }

        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    sucursalId: sucursalId,
                    codigoSucursal: secuencial,
                    fondoInicial: 1000.00,
                    custodioUsuarioId: custodioId,
                    custodioNombre: custodioNombre,
                    fechaCreacion: fechaCreacion
                })
            });

            const data = await res.json();
            if (data.ok) {
                abrirCajaModal.hide();
                await Swal.fire('¡Abierta!', 'Periodo de Caja Chica abierto exitosamente.', 'success');
                cargarCajasAdmin();
            } else {
                Swal.fire('Error', data.error || 'No se pudo abrir la caja.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión con el microservicio.', 'error');
        }
    }

    async function forzarCerrarCaja(id) {
        const confirm = await Swal.fire({
            title: '¿Forzar Cierre de Caja Chica?',
            text: 'Se bloquearán todos los registros de comprobantes para el custodio de esta caja.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e11d48'
        });

        if (!confirm.isConfirmed) return;

        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/${id}/close`, {
                method: 'POST',
                headers: getHeaders()
            });

            const data = await res.json();
            if (data.ok) {
                await Swal.fire('Cerrada', 'Caja chica cerrada correctamente.', 'success');
                cargarCajasAdmin();
            } else {
                Swal.fire('Error', data.error || 'No se pudo cerrar la caja.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    }

    function mostrarModalReembolso(id, totalGastado) {
        document.getElementById('reimburse-caja-id').value = id;
        document.getElementById('reimburse-monto').value = totalGastado.toFixed(2);
        document.getElementById('reimburse-suggested-text').innerHTML = `Sugerido para completar los $1,000.00: <strong>$${totalGastado.toFixed(2)}</strong>`;
        reimburseModal.show();
    }

    async function reembolsarCajaChica(e) {
        e.preventDefault();

        const id = document.getElementById('reimburse-caja-id').value;
        const monto = parseFloat(document.getElementById('reimburse-monto').value || 0);

        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/${id}/reimburse`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    montoRecarga: monto
                })
            });

            const data = await res.json();
            if (data.ok) {
                reimburseModal.hide();
                await Swal.fire('Reembolsada', 'Caja reembolsada y periodo siguiente abierto correctamente (inicia el 23).', 'success');
                cargarCajasAdmin();
            } else {
                Swal.fire('Error', data.error || 'No se pudo realizar el reembolso.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    }

    async function verAuditoria(id) {
        Swal.showLoading();

        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/${id}`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await res.json();
            if (!data.ok) {
                Swal.fire('Error', 'No se pudo cargar el detalle.', 'error');
                return;
            }

            const c = data.data;
            document.getElementById('audit-nro-caja').innerText = c.nroCajaChica;

            const totalGastado = c.detalles.reduce((acc, d) => acc + Number(d.total), 0);
            const saldoDisponible = Number(c.fondoInicial) - totalGastado;
            const vueltosPendientes = c.detalles
                .filter(d => d.estadoVuelto === 'Pendiente')
                .reduce((acc, d) => acc + Number(d.vueltoEsperado), 0);

            document.getElementById('audit-fondo').innerText = '$' + Number(c.fondoInicial).toFixed(2);
            document.getElementById('audit-gastado').innerText = '$' + totalGastado.toFixed(2);
            document.getElementById('audit-disponible').innerText = '$' + saldoDisponible.toFixed(2);
            document.getElementById('audit-vueltos').innerText = '$' + vueltosPendientes.toFixed(2);

            const tbody = document.getElementById('audit-detalles-body');
            tbody.innerHTML = '';

            if (c.detalles.length === 0) {
                tbody.innerHTML = `<tr><td colspan="12" class="text-center text-muted p-3">No hay comprobantes registrados en esta caja.</td></tr>`;
            } else {
                c.detalles.forEach((d, idx) => {
                    const tr = document.createElement('tr');
                    
                    let vBadge = '';
                    if (d.estadoVuelto === 'Pendiente') vBadge = '<span class="badge bg-warning text-dark">Pendiente</span>';
                    else if (d.estadoVuelto === 'Devuelto') vBadge = '<span class="badge bg-success">Devuelto</span>';
                    else vBadge = '<span class="text-muted">No Aplica</span>';

                    let comprobanteLink = _h(d.nroComprobante);
                    if (d.comprobanteUrl) {
                        comprobanteLink += ` <a href="${d.comprobanteUrl}" target="_blank" class="text-teal ms-1" title="Ver Comprobante Adjunto"><i class="bi bi-file-earmark-pdf text-danger" style="font-size: 15px;"></i></a>`;
                    }

                    tr.innerHTML = `
                        <td>${idx + 1}</td>
                        <td>${formatFecha(d.fechaComprobante)}</td>
                        <td class="fw-semibold">${comprobanteLink}</td>
                        <td>${_h(d.descripcion)}</td>
                        <td><span class="badge bg-secondary">${_h(d.tipoGasto)}</span></td>
                        <td class="text-end">$${Number(d.subtotalSinIva).toFixed(2)}</td>
                        <td class="text-end">$${Number(d.subtotalConIva).toFixed(2)}</td>
                        <td class="text-end fw-semibold">$${Number(d.total).toFixed(2)}</td>
                        <td class="text-end text-muted">$${Number(d.valorEntregado).toFixed(2)}</td>
                        <td>${_h(d.usuarioBeneficiado ?? '')}</td>
                        <td class="text-end text-warning fw-semibold">$${Number(d.vueltoEsperado).toFixed(2)}</td>
                        <td class="text-center">${vBadge}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            Swal.close();
            detalleCajaModal.show();
        } catch (e) {
            Swal.fire('Error', 'Error de conexión al cargar la auditoría.', 'error');
        }
    }

    async function exportarExcel(id) {
        Swal.showLoading();
        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/${id}/export`, {
                method: 'GET',
                headers: getHeaders()
            });

            if (!res.ok) {
                Swal.fire('Error', 'No se pudo generar el reporte Excel.', 'error');
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

    function formatPeriodo(fechaCreacionStr) {
        if (!fechaCreacionStr) return '';
        const d = new Date(fechaCreacionStr);
        d.setMinutes(d.getMinutes() + d.getTimezoneOffset());
        
        const start = new Date(d);
        const end = new Date(d);
        end.setMonth(end.getMonth() + 1);
        end.setDate(end.getDate() - 1);
        
        return formatFecha(start) + ' al ' + formatFecha(end);
    }

    function formatFecha(isoStr) {
        if (!isoStr) return '';
        const d = new Date(isoStr);
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
