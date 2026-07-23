@extends('layouts.app')
@section('titulo', 'Gestión de Caja Chica')

@section('contenido')
<div class="container-fluid px-4 py-3" style="max-width: 1450px;">
    <!-- Encabezado de Página -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 mb-0 text-gray-800" style="font-weight:700; color:#0f766e;">
                <i class="bi bi-wallet2 me-2"></i>Gestión de Caja Chica (Custodio)
            </h1>
            <p class="text-muted mb-0 small">Registrar comprobantes de gastos menores para la sucursal de <span id="sucursal-subtitle-name">{{$sucursalNombre}}</span></p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-teal p-2 fs-6" style="background-color: #0f766e;">
                Centro de Costo: <strong id="centro-costo-badge">{{$codigoSucursal}}</strong>
            </span>
        </div>
    </div>

    <!-- Sección: No hay caja chica abierta asignada -->
    <div id="no-caja-container" class="card shadow-sm border-0 mb-4 p-5 text-center" style="display:none; border-radius: 12px;">
        <div class="my-3">
            <i class="bi bi-wallet-fill" style="font-size: 4rem; color: #94a3b8;"></i>
        </div>
        <h3 class="h4" style="font-weight:600; color:#334155;">No tienes ninguna Caja Chica activa asignada</h3>
        <p class="text-muted mx-auto" style="max-width: 550px;">
            Para poder registrar tus gastos, el administrador de contabilidad debe abrir y asignarte un periodo de Caja Chica correspondiente al mes en curso. Por favor, solicita la apertura a tu administrador.
        </p>
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
                    <div class="text-muted small text-uppercase fw-bold">Total Gastado (Neto)</div>
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
                    <span class="text-muted small"><i class="bi bi-calendar4-week me-1"></i>Período: <strong id="info-periodo">-</strong></span>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <button type="button" class="btn btn-sm btn-teal" style="background:#0f766e; color:#fff; border:none;" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel me-1"></i>Descargar Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-cerrar-caja" onclick="cerrarCajaChica()">
                        <i class="bi bi-lock me-1"></i>Cerrar Caja
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <!-- Botón de Agregar Comprobante (Solo si está Abierta) -->
                <div class="p-3 bg-light d-flex justify-content-between align-items-center border-top border-bottom">
                    <span class="text-muted small">Listado de facturas, notas de venta, retenciones y vales de caja</span>
                    <button type="button" class="btn btn-sm btn-teal" id="btn-agregar-item" style="background:#0f766e; color:#fff; border:none; padding:6px 16px; border-radius:6px;" onclick="mostrarModalAgregarItem()">
                        <i class="bi bi-plus-lg me-1"></i>Agregar Comprobante
                    </button>
                </div>

                <!-- Tabla de Transacciones -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabla-detalles" style="font-size: 13px;">
                        <thead class="table-light text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-3" style="width: 45px;">Item</th>
                                <th style="width: 95px;">Fecha</th>
                                <th style="width: 140px;">Nro. Comprobante</th>
                                <th>Descripción</th>
                                <th style="width: 150px;">Tipo de Gasto</th>
                                <th class="text-end" style="width: 90px;">Subt. 0%</th>
                                <th class="text-end" style="width: 90px;">Subt. IVA</th>
                                <th class="text-end" style="width: 75px;">IVA</th>
                                <th class="text-end" style="width: 95px;">Tot. Fact.</th>
                                <th class="text-end text-danger" style="width: 95px;">Retención</th>
                                <th class="text-end text-success fw-bold" style="width: 100px;">Neto Pagado</th>
                                <th class="text-end" style="width: 95px; background-color: #f8fafc;">V. Entregado</th>
                                <th style="width: 150px; background-color: #f8fafc;">Beneficiario</th>
                                <th class="text-end" style="width: 90px; background-color: #f8fafc;">Vuelto</th>
                                <th class="text-center" style="width: 110px; background-color: #f8fafc;">Est. Vuelto</th>
                                <th class="text-center" style="width: 85px;">Acciones</th>
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

    <!-- Historial de Cajas Chicas Cerradas/Reembolsadas de la Sucursal -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark" style="font-size: 15px;"><i class="bi bi-clock-history me-2"></i>Historial de Cajas Chicas de la Sucursal</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nro Caja Chica</th>
                        <th>Período Contable</th>
                        <th>Fecha Cierre</th>
                        <th>Custodio</th>
                        <th class="text-end">Fondo Fijo</th>
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
                            <label class="form-label fw-semibold small text-teal">Usuario Beneficiado / Empleado *</label>
                            <select class="form-select border-teal" id="form-beneficiario" required>
                                <option value="">Seleccione el empleado...</option>
                                @foreach($usuarios as $u)
                                    <option value="{{$u->nombre_tecnico}}">{{$u->nombre_tecnico}} ({{$u->usuario}})</option>
                                @endforeach
                            </select>
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
                            <label class="form-label fw-semibold small">Descripción detallada del Gasto *</label>
                            <textarea class="form-control" id="form-descripcion" rows="2" placeholder="Motivo del gasto, compra realizada o actividad autorizada..." required></textarea>
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
                            <label class="form-label fw-semibold text-teal small">Total Factura (Sin Ret.)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-teal-light text-teal fw-bold">$</span>
                                <input type="text" class="form-control fw-bold text-teal bg-light" id="form-total" value="0.00" readonly>
                            </div>
                        </div>

                        <!-- Sección de Retención SRI -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-danger">Monto Retención SRI ($)</label>
                            <div class="input-group">
                                <span class="input-group-text text-danger">$</span>
                                <input type="number" step="0.01" class="form-control border-danger" id="form-retencion" value="0.00" oninput="actualizarCalculosForm()">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-danger">Nro. Comprobante Retención</label>
                            <input type="text" class="form-control" id="form-nro-retencion" placeholder="Ej: 001-002-0000123">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-success small">Neto Efectivo Pagado</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white fw-bold">$</span>
                                <input type="text" class="form-control fw-bold text-success bg-light" id="form-neto-pagar" value="0.00" readonly>
                            </div>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-primary">Valor Entregado (Efectivo)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control border-primary" id="form-entregado" value="0.00" oninput="actualizarCalculosForm()">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-primary">Estado Vuelto</label>
                            <select class="form-select border-primary" id="form-estado-vuelto">
                                <option value="No Aplica">No Aplica</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Devuelto">Devuelto</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-teal">Adjuntar Comprobante (PDF o Imagen)</label>
                            <input type="file" class="form-control border-teal" id="form-file" accept=".pdf,image/*">
                            <input type="hidden" id="form-comprobante-url">
                            <div class="form-text small mt-1" id="form-file-link-container" style="display:none;">
                                <a href="#" id="form-file-link" target="_blank" class="text-teal fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-1"></i>Ver Comprobante Adjunto Actual</a>
                            </div>
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

<input type="file" id="hidden-file-upload" accept="image/*,.pdf" style="display:none;" onchange="procesarSubidaDirecta()">

@endsection

@push('js_adicional')
<script>
    const _jwtToken = @json($token);
    const _apiUrl = @json($apiUrl);
    const _sucursalId = @json($sucursalId);
    const _esSuperAdmin = @json($esSuperAdmin);
    const _usuarioId = @json($usuario->id);

    let activeCaja = null;
    let itemsModal = null;

    document.addEventListener('DOMContentLoaded', () => {
        itemsModal = new bootstrap.Modal(document.getElementById('itemModal'));
        cargarModuloGestion();
    });

    function getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + _jwtToken
        };
    }

    function _h(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatFecha(fStr) {
        if (!fStr) return '-';
        const d = new Date(fStr);
        if (isNaN(d.getTime())) return fStr;
        return d.toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatPeriodo(fStr) {
        if (!fStr) return '-';
        const d = new Date(fStr);
        if (isNaN(d.getTime())) return fStr;
        const m = d.toLocaleDateString('es-EC', { month: 'long', year: 'numeric' });
        return m.charAt(0).toUpperCase() + m.slice(1);
    }

    async function cargarModuloGestion() {
        try {
            const res = await fetch(`${_apiUrl}/api/cajachica`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await res.json();
            if (!data.ok) {
                Swal.fire('Error', data.error || 'Error al conectar con contabilidad.', 'error');
                return;
            }

            const cajas = data.data || [];
            const miUsuarioCajas = cajas.filter(c => c.custodioUsuarioId === _usuarioId || _esSuperAdmin);
            
            activeCaja = miUsuarioCajas.find(c => (c.custodioUsuarioId === _usuarioId || _esSuperAdmin) && c.estado === 'Abierta');
            if (!activeCaja) {
                activeCaja = miUsuarioCajas.find(c => (c.custodioUsuarioId === _usuarioId || _esSuperAdmin) && c.estado === 'Cerrada');
            }

            if (activeCaja) {
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

            const historicas = miUsuarioCajas.filter(c => !activeCaja || c.id !== activeCaja.id);
            renderHistorial(historicas);

        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'Error de conexión con el microservicio.', 'error');
        }
    }

    function renderCajaActiva(caja) {
        document.getElementById('info-nro-caja').innerText = caja.nroCajaChica;
        document.getElementById('info-custodio').innerText = caja.custodioNombre;
        document.getElementById('info-periodo').innerText = formatPeriodo(caja.fechaCreacion);

        let sucursalName = 'Quito';
        if (caja.codigoSucursal === 'ACC08') sucursalName = 'Guayaquil';
        else if (caja.codigoSucursal === 'ACC16') sucursalName = 'Manta';

        const subtitleEl = document.getElementById('sucursal-subtitle-name');
        if (subtitleEl) subtitleEl.innerText = sucursalName;

        const ccEl = document.getElementById('centro-costo-badge');
        if (ccEl) ccEl.innerText = caja.codigoSucursal;
        
        const estEl = document.getElementById('info-estado');
        estEl.innerText = caja.estado;
        estEl.className = 'badge';
        if (caja.estado === 'Abierta') {
            estEl.classList.add('bg-success');
            document.getElementById('btn-cerrar-caja').style.display = 'inline-block';
            document.getElementById('btn-agregar-item').style.display = 'inline-block';
        } else {
            estEl.classList.add('bg-danger');
            document.getElementById('btn-cerrar-caja').style.display = 'none';
            document.getElementById('btn-agregar-item').style.display = 'none';
        }

        const totalGastadoFacturas = caja.detalles.reduce((acc, d) => acc + Number(d.total), 0);
        const totalRetenciones = caja.detalles.reduce((acc, d) => acc + Number(d.montoRetencion || 0), 0);
        const netoEfectivoGastado = totalGastadoFacturas - totalRetenciones;

        const vueltosPendientes = caja.detalles
            .filter(d => d.estadoVuelto === 'Pendiente')
            .reduce((acc, d) => acc + Number(d.vueltoEsperado), 0);

        const saldoDisponible = Number(caja.fondoInicial) - netoEfectivoGastado - vueltosPendientes;

        document.getElementById('stat-fondo-inicial').innerText = '$' + Number(caja.fondoInicial).toFixed(2);
        document.getElementById('stat-total-gastado').innerText = '$' + netoEfectivoGastado.toFixed(2);
        document.getElementById('stat-saldo-disponible').innerText = '$' + saldoDisponible.toFixed(2);
        document.getElementById('stat-vueltos-pendientes').innerText = '$' + vueltosPendientes.toFixed(2);

        const tbody = document.getElementById('detalles-body');
        tbody.innerHTML = '';

        if (caja.detalles.length === 0) {
            tbody.innerHTML = `<tr><td colspan="16" class="text-center text-muted p-4"><i class="bi bi-info-circle me-1"></i>No hay comprobantes registrados en esta caja chica.</td></tr>`;
            return;
        }

        caja.detalles.forEach((d, idx) => {
            const tr = document.createElement('tr');
            
            let btnPDF = '';
            if (d.comprobanteUrl) {
                btnPDF = `<a href="${d.comprobanteUrl}" target="_blank" class="btn btn-sm btn-outline-danger p-1 py-0 me-1" title="Ver Comprobante (PDF/Imagen)"><i class="bi bi-file-earmark-pdf"></i></a>`;
            } else {
                btnPDF = `<button type="button" class="btn btn-sm btn-outline-secondary p-1 py-0 me-1" onclick="subirComprobanteDirecto(${d.id})" title="Subir Comprobante"><i class="bi bi-upload"></i></button>`;
            }

            let btnEditDel = '';
            if (caja.estado === 'Abierta') {
                if (d.estadoVuelto === 'Devuelto') {
                    btnEditDel = `<span class="text-muted small"><i class="bi bi-lock-fill text-secondary"></i> Conciliado</span>`;
                } else {
                    btnEditDel = `
                        <button type="button" class="btn btn-sm btn-outline-primary p-1 py-0 me-1" onclick="editarItem(${d.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger p-1 py-0" onclick="eliminarItem(${d.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                    `;
                }
            } else {
                btnEditDel = `<span class="text-muted small"><i class="bi bi-lock-fill"></i> Bloqueado</span>`;
            }

            let vueltoBadge = '';
            if (d.estadoVuelto === 'Pendiente') {
                vueltoBadge = `<span class="badge bg-warning text-dark" style="cursor:pointer;" onclick="toggleEstadoVueltoDirecto(${d.id}, 'Devuelto')" title="Marcar como Devuelto"><i class="bi bi-exclamation-triangle-fill me-1"></i>Pendiente</span>`;
            } else if (d.estadoVuelto === 'Devuelto') {
                vueltoBadge = `<span class="badge bg-success" style="cursor:pointer;" onclick="toggleEstadoVueltoDirecto(${d.id}, 'Pendiente')" title="Marcar como Pendiente"><i class="bi bi-check-circle-fill me-1"></i>Devuelto</span>`;
            } else {
                vueltoBadge = `<span class="text-muted small">No Aplica</span>`;
            }

            const ret = Number(d.montoRetencion || 0);
            const netoPagar = Number(d.total) - ret;

            tr.innerHTML = `
                <td class="ps-3 fw-bold">${idx + 1}</td>
                <td>${formatFecha(d.fechaComprobante)}</td>
                <td class="fw-semibold">${_h(d.nroComprobante)}</td>
                <td class="text-wrap" style="max-width: 250px;">${_h(d.descripcion)}</td>
                <td><span class="badge bg-secondary py-1 px-2">${_h(d.tipoGasto)}</span></td>
                <td class="text-end">$${Number(d.subtotalSinIva).toFixed(2)}</td>
                <td class="text-end">$${Number(d.subtotalConIva).toFixed(2)}</td>
                <td class="text-end text-muted">$${Number(d.iva).toFixed(2)}</td>
                <td class="text-end fw-semibold">$${Number(d.total).toFixed(2)}</td>
                <td class="text-end text-danger">${ret > 0 ? '-$' + ret.toFixed(2) : '$0.00'}</td>
                <td class="text-end fw-bold text-success">$${netoPagar.toFixed(2)}</td>
                <td class="text-end font-monospace" style="background-color: #f8fafc;">$${Number(d.valorEntregado).toFixed(2)}</td>
                <td class="text-wrap" style="background-color: #f8fafc; max-width:140px;">${_h(d.usuarioBeneficiado ?? '')}</td>
                <td class="text-end font-monospace fw-semibold" style="background-color: #f8fafc; color:#b45309;">$${Number(d.vueltoEsperado).toFixed(2)}</td>
                <td class="text-center" style="background-color: #f8fafc;">${vueltoBadge}</td>
                <td class="text-center">${btnPDF}${btnEditDel}</td>
            `;

            tbody.appendChild(tr);
        });
    }

    function renderHistorial(cajas) {
        const tbody = document.getElementById('historial-body');
        tbody.innerHTML = '';

        if (!cajas || cajas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted p-3">No hay periodos de caja chica cerrados en el historial de esta sucursal.</td></tr>`;
            return;
        }

        cajas.forEach(c => {
            const tr = document.createElement('tr');
            let badgeClass = 'bg-secondary';
            if (c.estado === 'Reembolsada') badgeClass = 'bg-primary';
            else if (c.estado === 'Cerrada') badgeClass = 'bg-danger';
            tr.innerHTML = `
                <td class="ps-3 fw-bold">${c.nroCajaChica}</td>
                <td>${formatPeriodo(c.fechaCreacion)}</td>
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

    function mostrarModalAgregarItem() {
        document.getElementById('itemModalLabel').innerHTML = '<i class="bi bi-file-earmark-plus me-2"></i>Registrar Comprobante';
        document.getElementById('itemForm').reset();
        document.getElementById('form-item-id').value = '';
        document.getElementById('form-fecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('form-retencion').value = '0.00';
        document.getElementById('form-nro-retencion').value = '';
        document.getElementById('form-neto-pagar').value = '0.00';
        document.getElementById('form-vuelto-esperado-label').innerText = '$0.00';
        document.getElementById('form-file').value = '';
        document.getElementById('form-comprobante-url').value = '';
        document.getElementById('form-file-link-container').style.display = 'none';
        
        itemsModal.show();
    }

    function actualizarCalculosForm() {
        const subtotalSin = parseFloat(document.getElementById('form-subtotal-sin').value || 0);
        const subtotalCon = parseFloat(document.getElementById('form-subtotal-con').value || 0);
        const retencion = parseFloat(document.getElementById('form-retencion').value || 0);
        
        const iva = Math.round(subtotalCon * 0.15 * 100) / 100;
        const total = subtotalSin + subtotalCon + iva;
        const netoPagar = Math.max(0, total - retencion);
        
        document.getElementById('form-total').value = total.toFixed(2);
        document.getElementById('form-neto-pagar').value = netoPagar.toFixed(2);

        const entregado = parseFloat(document.getElementById('form-entregado').value || 0);
        const vueltoLabel = document.getElementById('form-vuelto-esperado-label');
        const estadoVueltoSelect = document.getElementById('form-estado-vuelto');

        if (entregado > netoPagar) {
            const vuelto = entregado - netoPagar;
            vueltoLabel.innerText = '$' + vuelto.toFixed(2);
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
        const fileInput = document.getElementById('form-file');
        let comprobanteUrl = document.getElementById('form-comprobante-url').value;

        Swal.showLoading();

        if (fileInput.files.length > 0) {
            try {
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                
                const uploadRes = await fetch("{{ route('cajachica.subir_comprobante') }}", {
                    method: 'POST',
                    body: formData
                });
                
                const uploadData = await uploadRes.json();
                if (uploadData.ok) {
                    comprobanteUrl = uploadData.url;
                } else {
                    Swal.fire('Error', uploadData.error || 'No se pudo subir el comprobante.', 'error');
                    return;
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'No se pudo contactar con el servidor para subir el archivo.', 'error');
                return;
            }
        }

        const payload = {
            fechaComprobante: document.getElementById('form-fecha').value,
            nroComprobante: document.getElementById('form-nro').value.trim(),
            proveedor: null,
            tipoGasto: document.getElementById('form-tipo-gasto').value,
            descripcion: document.getElementById('form-descripcion').value.trim(),
            subtotalSinIva: parseFloat(document.getElementById('form-subtotal-sin').value || 0),
            subtotalConIva: parseFloat(document.getElementById('form-subtotal-con').value || 0),
            montoRetencion: parseFloat(document.getElementById('form-retencion').value || 0),
            nroRetencion: document.getElementById('form-nro-retencion').value.trim(),
            valorEntregado: parseFloat(document.getElementById('form-entregado').value || 0),
            usuarioBeneficiado: document.getElementById('form-beneficiario').value,
            estadoVuelto: document.getElementById('form-estado-vuelto').value,
            comprobanteUrl: comprobanteUrl
        };

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
                await Swal.fire('Éxito', data.message || 'Comprobante guardado.', 'success');
                cargarModuloGestion();
            } else {
                let errorMsg = data.error || 'No se pudo registrar el comprobante.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                } else if (data.title) {
                    errorMsg = data.title;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión: ' + e.message, 'error');
        }
    }

    async function editarItem(itemId) {
        const item = activeCaja.detalles.find(d => d.id === itemId);
        if (!item) return;

        if (item.estadoVuelto === 'Devuelto') {
            Swal.fire('Bloqueado', 'No se puede editar un comprobante con vuelto ya devuelto y conciliado.', 'warning');
            return;
        }

        document.getElementById('itemModalLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Comprobante';
        document.getElementById('form-item-id').value = item.id;
        document.getElementById('form-fecha').value = item.fechaComprobante.split('T')[0];
        document.getElementById('form-nro').value = item.nroComprobante;
        document.getElementById('form-tipo-gasto').value = item.tipoGasto;
        document.getElementById('form-descripcion').value = item.descripcion;
        document.getElementById('form-subtotal-sin').value = item.subtotalSinIva;
        document.getElementById('form-subtotal-con').value = item.subtotalConIva;
        document.getElementById('form-retencion').value = item.montoRetencion || 0.00;
        document.getElementById('form-nro-retencion').value = item.nroRetencion || '';
        document.getElementById('form-entregado').value = item.valorEntregado;
        document.getElementById('form-beneficiario').value = item.usuarioBeneficiado || '';
        document.getElementById('form-estado-vuelto').value = item.estadoVuelto;

        document.getElementById('form-file').value = '';
        const urlComprobante = item.comprobanteUrl || '';
        document.getElementById('form-comprobante-url').value = urlComprobante;
        
        const linkContainer = document.getElementById('form-file-link-container');
        if (urlComprobante) {
            document.getElementById('form-file-link').href = urlComprobante;
            linkContainer.style.display = 'block';
        } else {
            linkContainer.style.display = 'none';
        }

        actualizarCalculosForm();
        itemsModal.show();
    }

    async function eliminarItem(itemId) {
        const item = activeCaja.detalles.find(d => d.id === itemId);
        if (item && item.estadoVuelto === 'Devuelto') {
            Swal.fire('Bloqueado', 'No se puede eliminar un comprobante con vuelto ya devuelto y conciliado.', 'warning');
            return;
        }

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
                await Swal.fire('Eliminado', 'El comprobante ha sido eliminado.', 'success');
                cargarModuloGestion();
            } else {
                Swal.fire('Error', data.error || 'No se pudo eliminar el ítem.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Fallo de conexión al eliminar: ' + e.message, 'error');
        }
    }

    async function toggleEstadoVueltoDirecto(itemId, nuevoEstado) {
        Swal.showLoading();
        try {
            const res = await fetch(`${_apiUrl}/api/cajachica/items/${itemId}/reconcile-vuelto`, {
                method: 'POST',
                headers: getHeaders()
            });

            const data = await res.json();
            if (data.ok) {
                await Swal.fire('Conciliado', 'El vuelto ha sido registrado como devuelto exitosamente.', 'success');
                cargarModuloGestion();
            } else {
                Swal.fire('Error', data.error || 'No se pudo actualizar el vuelto.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Fallo de conexión.', 'error');
        }
    }

    async function cerrarCajaChica() {
        const confirm = await Swal.fire({
            title: '¿Cerrar Período de Caja Chica?',
            text: 'Una vez cerrada, no se podrán agregar más comprobantes ni editar los existentes.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar período',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e11d48'
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
                await Swal.fire('Caja Cerrada', 'El período de Caja Chica ha sido cerrado exitosamente.', 'success');
                cargarModuloGestion();
            } else {
                Swal.fire('Error', data.error || 'No se pudo cerrar la Caja Chica.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Fallo de conexión al cerrar.', 'error');
        }
    }

    function exportarExcel() {
        if (!activeCaja) return;
        window.open(`${_apiUrl}/api/cajachica/${activeCaja.id}/export`, '_blank');
    }

    function exportarExcelHistorial(cajaId) {
        window.open(`${_apiUrl}/api/cajachica/${cajaId}/export`, '_blank');
    }

    let itemParaSubidaDirectaId = null;
    function subirComprobanteDirecto(itemId) {
        itemParaSubidaDirectaId = itemId;
        document.getElementById('hidden-file-upload').click();
    }

    async function procesarSubidaDirecta() {
        const fileInput = document.getElementById('hidden-file-upload');
        if (!fileInput.files || fileInput.files.length === 0 || !itemParaSubidaDirectaId) return;

        Swal.showLoading();

        try {
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            const uploadRes = await fetch("{{ route('cajachica.subir_comprobante') }}", {
                method: 'POST',
                body: formData
            });

            const uploadData = await uploadRes.json();
            if (!uploadData.ok) {
                Swal.fire('Error', uploadData.error || 'No se pudo subir el comprobante.', 'error');
                return;
            }

            const res = await fetch(`${_apiUrl}/api/cajachica/items/${itemParaSubidaDirectaId}/comprobante`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify({ comprobanteUrl: uploadData.url })
            });

            const data = await res.json();
            if (data.ok) {
                await Swal.fire('Éxito', 'Comprobante adjuntado correctamente.', 'success');
                cargarModuloGestion();
            } else {
                Swal.fire('Error', data.error || 'No se pudo asociar el comprobante.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Fallo de conexión al subir.', 'error');
        } finally {
            fileInput.value = '';
            itemParaSubidaDirectaId = null;
        }
    }
</script>
@endpush
