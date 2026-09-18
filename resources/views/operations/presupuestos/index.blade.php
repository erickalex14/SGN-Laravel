@extends('layouts.app')
@section('titulo', 'Cotizaciones y Proformas')

@section('contenido')
<section class="modulo activo">
<div class="pres-container">
    <div class="form-titulo">
        <h2><i class="bi bi-receipt-cutoff text-primary me-2"></i>Cotizaciones y Proformas</h2>
        <p>Genera proformas para órdenes de servicio o cotiza la venta directa de artículos y repuestos (baterías, cargadores, etc.).</p>
    </div>

    <!-- Pestañas de Modo: Por Orden vs Venta Directa de Artículos -->
    <div class="pres-mode-tabs">
        <button type="button" class="pres-mode-tab active" id="tab-modo-orden" onclick="cambiarModoPres('orden')">
            <i class="bi bi-tools"></i> Por Orden de Servicio
        </button>
        <button type="button" class="pres-mode-tab" id="tab-modo-directo" onclick="cambiarModoPres('directo')">
            <i class="bi bi-bag-check-fill"></i> Venta Directa de Artículos / Repuestos
        </button>
    </div>

    <!-- MODO 1: BÚSQUEDA DINÁMICA POR ORDEN -->
    <div id="wrap-modo-orden">
        <div class="pres-card">
            <div class="pres-card-title">
                <span><i class="bi bi-search me-2"></i>Buscar y Seleccionar Orden</span>
                <span style="font-size:11.5px; font-weight:500; color:#64748b;">Escribe el # de orden, cliente o serie</span>
            </div>
            <div class="pres-buscar-row">
                <div class="pres-search-box-wrap">
                    <i class="bi bi-search pres-search-icon"></i>
                    <input type="text" id="inp-buscar-orden-dinamico" class="pres-input-search" 
                           placeholder="Ej: UIO-001234, Juan Perez, o número de serie..." 
                           autocomplete="off" oninput="buscarOrdenesDinamicas(this.value)">
                    <button type="button" id="btn-limpiar-busqueda-orden" class="pres-search-clear" onclick="limpiarBusquedaOrden()" style="display:none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                    <!-- Menú flotante de resultados predictivos -->
                    <div id="dropdown-ordenes-resultados" class="pres-dropdown-results" style="display:none;"></div>
                </div>

                <!-- Fallback select rápido -->
                <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11.5px; color: #64748b;">O selecciona directamente de las últimas órdenes:</span>
                    <select id="sel-orden-pres" class="pres-select-quick" onchange="cargarOrdenPres(this.value)">
                        <option value="">-- Selecciona de la lista rápida --</option>
                        @foreach($ordenes->take(30) as $o)
                        <option value="{{ $o->id }}"
                                data-nro="{{ $o->nro_orden }}"
                                data-cliente="{{ $o->cliente }}"
                                data-equipo="{{ trim(($o->tipo ?? '').' '.($o->marca ?? '').' '.($o->modelo ?? '')) }}"
                                data-estado="{{ $o->estado_orden }}"
                                data-motivo="{{ $o->motivo_ingreso }}"
                                data-garantia="{{ $o->estado_garantia ?? '' }}">
                            {{ $o->nro_orden }} - {{ $o->cliente }} ({{ trim(($o->tipo ?? '').' '.($o->marca ?? '')) }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Ficha de la orden seleccionada -->
            <div id="pres-orden-info" style="display:none;" class="pres-orden-info">
                <div class="pres-info-item">
                    <span class="pres-info-lbl">Nro. Orden</span>
                    <span id="pres-nro-orden" class="pres-info-val text-primary font-monospace fw-bold">-</span>
                </div>
                <div class="pres-info-item">
                    <span class="pres-info-lbl">Cliente</span>
                    <span id="pres-cliente" class="pres-info-val fw-bold">-</span>
                </div>
                <div class="pres-info-item">
                    <span class="pres-info-lbl">Equipo / Modelo</span>
                    <span id="pres-equipo" class="pres-info-val">-</span>
                </div>
                <div class="pres-info-item">
                    <span class="pres-info-lbl">Estado de la Orden</span>
                    <span id="pres-estado" class="pres-info-val">-</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MODO 2: DATOS DEL CLIENTE PARA VENTA DIRECTA -->
    <div id="wrap-modo-directo" style="display:none;">
        <div class="pres-card">
            <div class="pres-card-title">
                <span><i class="bi bi-person-lines-fill me-2"></i>Datos del Cliente (Venta Directa / Mostrador)</span>
                <span style="font-size:11.5px; font-weight:500; color:#64748b;">Opcionales para proformas rápidas</span>
            </div>
            <div class="pres-cliente-grid">
                <div class="campo">
                    <label>Nombre del Cliente / Razón Social</label>
                    <input type="text" id="dir-cliente-nombre" placeholder="Ej: CONSUMIDOR FINAL o Nombre del Cliente" oninput="this.value = this.value.toUpperCase()">
                </div>
                <div class="campo">
                    <label>Cédula o RUC</label>
                    <input type="text" id="dir-cliente-identificacion" placeholder="10 o 13 dígitos numéricos" maxlength="13">
                </div>
                <div class="campo">
                    <label>Teléfono de Contacto</label>
                    <input type="text" id="dir-cliente-telefono" placeholder="Ej: 0998765432">
                </div>
            </div>
        </div>
    </div>

    <!-- CUERPO DE ITEMS (COMÚN PARA AMBOS MODOS) -->
    <div id="pres-form-wrap" style="display:none;">
        <div class="pres-card">
            <div class="pres-card-title">
                <span><i class="bi bi-list-check me-2"></i>Ítems de la Cotización / Proforma</span>
                <div class="pres-btns-add">
                    <button type="button" class="pres-btn-repuesto" onclick="abrirArticulosPres()">
                        <i class="bi bi-cpu-fill me-1"></i>Artículos / Repuestos
                    </button>
                    <button type="button" class="pres-btn-catalogo" onclick="abrirCatalogoPres()">
                        <i class="bi bi-tag-fill me-1"></i>Mano de Obra
                    </button>
                    <button type="button" class="pres-btn-custom" onclick="agregarItemCustom()">
                        <i class="bi bi-plus-circle-fill me-1"></i>Personalizado
                    </button>
                </div>
            </div>

            <div id="pres-lista-items">
                <div class="pres-empty" id="pres-empty-msg">
                    <i class="bi bi-inbox" style="font-size:24px; display:block; margin-bottom:4px; opacity:.5;"></i>
                    No hay ítems agregados. Usa los botones superiores para agregar artículos, servicios o ítems manuales.
                </div>
            </div>

            <div class="pres-totales" id="pres-totales" style="display:none;">
                <div class="pres-total-row"><span>Subtotal:</span><span id="pres-subtotal">$0.00</span></div>
                <div class="pres-total-row pres-iva"><span>IVA 15%:</span><span id="pres-iva">$0.00</span></div>
                <div class="pres-total-row pres-total-final"><span>TOTAL ESTIMADO:</span><span id="pres-total">$0.00</span></div>
            </div>
        </div>

        <div class="pres-card">
            <div class="pres-card-title"><i class="bi bi-chat-left-text me-2"></i>Observaciones / Condiciones Comerciales</div>
            <textarea id="pres-notas" rows="3" placeholder="Garantía de los repuestos, tiempo de validez de la proforma, condiciones de entrega..."></textarea>
        </div>

        <div class="pres-acciones">
            <button type="button" class="pres-btn-preview" onclick="previsualizarPresupuesto()">
                <i class="bi bi-eye me-2"></i>Previsualizar
            </button>
            <button type="button" class="pres-btn-imprimir" onclick="imprimirPresupuesto()">
                <i class="bi bi-printer me-2"></i>Imprimir / Guardar PDF
            </button>
            <button type="button" class="pres-btn-limpiar" onclick="limpiarPresupuesto()">
                <i class="bi bi-x-circle me-2"></i>Limpiar Ítems
            </button>
        </div>
    </div>
</div>

<!-- MODAL 1: CATÁLOGO DE SERVICIOS (MANO DE OBRA) -->
<div id="modal-cat-pres" class="pres-modal-overlay" style="display:none;">
    <div class="modal-cat-inner">
        <div class="modal-hdr">
            <h4><i class="bi bi-tools text-primary me-2"></i>Catálogo de Servicios y Mano de Obra</h4>
            <button type="button" class="btn-close-modal" onclick="cerrarCatalogoPres()">&times;</button>
        </div>
        <input type="text" id="buscar-cat-pres" placeholder="Buscar servicio (ej: formateo, revisión, mantenimiento)..." oninput="filtrarCatPres(this.value)">
        <div id="lista-cat-pres" class="modal-lista-scroll">Cargando...</div>
        <button type="button" onclick="cerrarCatalogoPres()" class="pres-btn-cerrar-modal">Cerrar</button>
    </div>
</div>

<!-- MODAL 2: CATÁLOGO DE ARTÍCULOS Y REPUESTOS (BATERÍAS, CARGADORES, STOCK) -->
<div id="modal-art-pres" class="pres-modal-overlay" style="display:none;">
    <div class="modal-cat-inner">
        <div class="modal-hdr">
            <h4><i class="bi bi-boxes text-success me-2"></i>Artículos y Repuestos (Baterías, Cargadores, etc.)</h4>
            <button type="button" class="btn-close-modal" onclick="cerrarArticulosPres()">&times;</button>
        </div>
        <input type="text" id="buscar-art-pres" placeholder="Buscar por código o nombre (ej: cargador, bateria, pantalla)..." oninput="debounceBuscarArticulos(this.value)">
        <div id="lista-art-pres" class="modal-lista-scroll">Escribe para buscar artículos en inventario...</div>
        <button type="button" onclick="cerrarArticulosPres()" class="pres-btn-cerrar-modal">Cerrar</button>
    </div>
</div>
</section>
@endsection

@push('css_adicional')
<style>
.pres-container { max-width: 920px; margin: 0 auto; padding: 24px 20px; }
.form-titulo h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 4px; display: flex; align-items: center; }
.form-titulo p { color: #64748b; font-size: 13.5px; margin: 0 0 20px; }

/* Tabs de modo */
.pres-mode-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
.pres-mode-tab { flex: 1; padding: 12px 18px; border: 1.5px solid #e2e8f0; background: #fff; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: #64748b; cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.03); }
.pres-mode-tab:hover { border-color: #cbd5e1; background: #f8fafc; color: #0f172a; }
.pres-mode-tab.active { background: #eff6ff; border-color: #2563eb; color: #1d4ed8; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }

/* Cards */
.pres-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.05); margin-bottom: 20px; overflow: visible; border: 1px solid #e2e8f0; }
.pres-card-title { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 13px 20px; font-size: 14px; font-weight: 700; color: #1e293b; border-radius: 14px 14px 0 0; }
.pres-buscar-row { padding: 18px 20px; }

/* Buscador de Orden Dinámico */
.pres-search-box-wrap { position: relative; width: 100%; }
.pres-search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px; pointer-events: none; }
.pres-input-search { width: 100%; padding: 12px 40px 12px 42px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 14px; outline: none; background: #f8fafc; transition: all .2s; box-sizing: border-box; }
.pres-input-search:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
.pres-search-clear { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 16px; padding: 4px; }
.pres-search-clear:hover { color: #dc2626; }

/* Dropdown flotante de resultados */
.pres-dropdown-results { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,.12); max-height: 280px; overflow-y: auto; z-index: 1000; }
.pres-drop-item { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background .12s; }
.pres-drop-item:hover { background: #f0f7ff; }
.pres-drop-item:last-child { border-bottom: none; }
.pres-drop-nro { font-weight: 800; color: #2563eb; font-family: monospace; font-size: 13.5px; }
.pres-drop-cli { font-size: 12.5px; color: #1e293b; font-weight: 600; margin-left: 8px; }
.pres-drop-sub { font-size: 11.5px; color: #64748b; margin-top: 2px; }

.pres-select-quick { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; color: #475569; max-width: 320px; }

/* Ficha de orden */
.pres-orden-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; padding: 14px 20px; background: #f8fafc; border-top: 1px dashed #e2e8f0; border-radius: 0 0 14px 14px; }
.pres-info-item { display: flex; flex-direction: column; gap: 2px; }
.pres-info-lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #94a3b8; }
.pres-info-val { font-size: 13px; color: #1e293b; }

/* Grid cliente directo */
.pres-cliente-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; padding: 18px 20px; }
.campo { display: flex; flex-direction: column; gap: 5px; }
.campo label { font-size: 12.5px; font-weight: 700; color: #475569; }
.campo input { padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; }
.campo input:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.1); }

/* Botones agregar items */
.pres-btns-add { display: flex; gap: 8px; flex-wrap: wrap; }
.pres-btn-repuesto, .pres-btn-catalogo, .pres-btn-custom { border: none; border-radius: 7px; padding: 7px 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: transform .1s, opacity .15s; }
.pres-btn-repuesto { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.pres-btn-catalogo { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.pres-btn-custom { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
.pres-btn-repuesto:hover, .pres-btn-catalogo:hover, .pres-btn-custom:hover { opacity: .9; transform: translateY(-1px); }

/* Lista de ítems */
#pres-lista-items { padding: 16px 20px; }
.pres-empty { text-align: center; color: #94a3b8; font-size: 13px; padding: 24px 10px; }

.pres-item-fila { display: grid; grid-template-columns: 1fr 75px 100px 100px auto; gap: 10px; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 9px; padding: 10px 14px; margin-bottom: 8px; }
.pres-item-info { display: flex; flex-direction: column; gap: 3px; }
.pres-item-nombre { font-weight: 700; font-size: 13px; color: #0f172a; border: 1px solid transparent; background: transparent; padding: 2px 4px; border-radius: 4px; width: 100%; }
.pres-item-nombre:focus, .pres-item-desc:focus { background: #fff; border-color: #93c5fd; outline: none; }
.pres-item-desc { font-size: 11.5px; color: #64748b; border: 1px solid transparent; background: transparent; padding: 2px 4px; border-radius: 4px; width: 100%; }

.pres-item-cant, .pres-item-precio { border: 1.5px solid #cbd5e1; border-radius: 7px; padding: 6px 8px; font-size: 13px; text-align: right; width: 100%; box-sizing: border-box; }
.pres-item-subtotal-val { font-size: 13px; font-weight: 800; color: #0f172a; text-align: right; }
.pres-btn-del { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 7px; padding: 6px 10px; cursor: pointer; font-size: 13px; }
.pres-btn-del:hover { background: #dc2626; color: #fff; }

/* Totales */
.pres-totales { border-top: 1.5px solid #e2e8f0; padding: 14px 20px; display: flex; flex-direction: column; align-items: flex-end; gap: 6px; background: #fcfdfe; }
.pres-total-row { display: flex; gap: 40px; justify-content: flex-end; font-size: 13.5px; font-weight: 600; color: #475569; }
.pres-total-final { border-top: 2px solid #e2e8f0; padding-top: 8px; margin-top: 2px; font-size: 16px; font-weight: 800; color: #15803d; }

#pres-notas { width: 100%; border: none; padding: 14px 20px; font-size: 13px; resize: vertical; min-height: 75px; box-sizing: border-box; outline: none; }

.pres-acciones { display: flex; gap: 12px; margin-top: 14px; flex-wrap: wrap; }
.pres-btn-preview { background: #fff; color: #1e293b; border: 1.5px solid #cbd5e1; border-radius: 9px; padding: 11px 20px; font-size: 13.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; }
.pres-btn-imprimir { background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; border: none; border-radius: 9px; padding: 11px 24px; font-size: 13.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; box-shadow: 0 2px 8px rgba(37,99,235,.25); }
.pres-btn-limpiar { background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; border-radius: 9px; padding: 11px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
.pres-btn-preview:hover, .pres-btn-limpiar:hover { background: #e2e8f0; }
.pres-btn-imprimir:hover { opacity: .95; transform: translateY(-1px); }

/* Modales */
.pres-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.6); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px; backdrop-filter: blur(2px); }
.modal-cat-inner { background: #fff; border-radius: 14px; padding: 20px 24px; max-width: 600px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0,0,0,.25); }
.modal-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.modal-hdr h4 { margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; }
.btn-close-modal { background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; line-height: 1; }
.modal-cat-inner input { width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; margin-bottom: 12px; box-sizing: border-box; outline: none; }
.modal-cat-inner input:focus { border-color: #2563eb; }
.modal-lista-scroll { overflow-y: auto; max-height: 380px; display: flex; flex-direction: column; gap: 6px; padding-right: 4px; }
.pres-cat-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-radius: 9px; cursor: pointer; border: 1.5px solid #e2e8f0; background: #fff; transition: all .12s; }
.pres-cat-item:hover { border-color: #93c5fd; background: #eff6ff; }
.pres-btn-cerrar-modal { margin-top: 14px; width: 100%; background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 9px; font-weight: 700; cursor: pointer; }
</style>
@endpush

@push('js_adicional')
<script>
(function() {
var _catalogoPres = @json($catalogo);
var _itemCount = 0;
var _ordenActual = null;
var _modoActual = 'orden'; // 'orden' | 'directo'
var _debounceTimer = null;
var _articulosTimer = null;

function _normalizar(texto) {
    return String(texto || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
}

// ── CAMBIO DE MODO ──
window.cambiarModoPres = function(modo) {
    _modoActual = modo;
    var tabOrd = document.getElementById('tab-modo-orden');
    var tabDir = document.getElementById('tab-modo-directo');
    var wrapOrd = document.getElementById('wrap-modo-orden');
    var wrapDir = document.getElementById('wrap-modo-directo');
    var formWrap = document.getElementById('pres-form-wrap');

    if (modo === 'orden') {
        tabOrd.classList.add('active');
        tabDir.classList.remove('active');
        wrapOrd.style.display = 'block';
        wrapDir.style.display = 'none';
        formWrap.style.display = _ordenActual ? 'block' : 'none';
    } else {
        tabDir.classList.add('active');
        tabOrd.classList.remove('active');
        wrapOrd.style.display = 'none';
        wrapDir.style.display = 'block';
        formWrap.style.display = 'block'; // Siempre visible en venta directa
    }
};

// ── BÚSQUEDA DINÁMICA DE ÓRDENES AJAX ──
window.buscarOrdenesDinamicas = function(q) {
    var query = q.trim();
    var drop = document.getElementById('dropdown-ordenes-resultados');
    var btnClear = document.getElementById('btn-limpiar-busqueda-orden');

    btnClear.style.display = query.length > 0 ? 'block' : 'none';

    if (query.length < 2) {
        drop.style.display = 'none';
        return;
    }

    clearTimeout(_debounceTimer);
    _debounceTimer = setTimeout(function() {
        fetch('{{ route("presupuestos.buscar_ordenes") }}?q=' + encodeURIComponent(query))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok || !data.ordenes || !data.ordenes.length) {
                    drop.innerHTML = '<div style="padding:12px; text-align:center; color:#94a3b8; font-size:12.5px;">No se encontraron órdenes con ese criterio</div>';
                    drop.style.display = 'block';
                    return;
                }

                drop.innerHTML = data.ordenes.map(function(o) {
                    var equipo = ((o.tipo || '') + ' ' + (o.marca || '') + ' ' + (o.modelo || '')).trim() || 'Sin equipo';
                    var serie = o.serie ? (' - S/N: ' + o.serie) : '';
                    return '<div class="pres-drop-item" onclick="seleccionarOrdenDinamica(' + JSON.stringify(o).replace(/"/g, '&quot;') + ')">' +
                        '<div>' +
                            '<div><span class="pres-drop-nro">' + (o.nro_orden || '') + '</span><span class="pres-drop-cli">' + (o.cliente || '') + '</span></div>' +
                            '<div class="pres-drop-sub">' + equipo + serie + '</div>' +
                        '</div>' +
                        '<span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:11px; padding:3px 8px; border-radius:6px;">' + (o.estado_orden || '') + '</span>' +
                    '</div>';
                }).join('');
                drop.style.display = 'block';
            })
            .catch(function(e) {
                console.error('Error al buscar órdenes:', e);
            });
    }, 250);
};

window.limpiarBusquedaOrden = function() {
    var inp = document.getElementById('inp-buscar-orden-dinamico');
    var drop = document.getElementById('dropdown-ordenes-resultados');
    var btnClear = document.getElementById('btn-limpiar-busqueda-orden');
    inp.value = '';
    drop.style.display = 'none';
    btnClear.style.display = 'none';
};

window.seleccionarOrdenDinamica = function(o) {
    document.getElementById('dropdown-ordenes-resultados').style.display = 'none';
    document.getElementById('inp-buscar-orden-dinamico').value = o.nro_orden + ' - ' + o.cliente;

    var info = document.getElementById('pres-orden-info');
    var wrap = document.getElementById('pres-form-wrap');

    _ordenActual = {
        id: o.id,
        nro: o.nro_orden,
        cliente: o.cliente,
        equipo: ((o.tipo || '') + ' ' + (o.marca || '') + ' ' + (o.modelo || '')).trim(),
        estado: o.estado_orden,
        motivo: o.motivo_ingreso,
        garantia: o.estado_garantia || ''
    };

    document.getElementById('pres-nro-orden').textContent = _ordenActual.nro;
    document.getElementById('pres-cliente').textContent = _ordenActual.cliente;
    document.getElementById('pres-equipo').textContent = _ordenActual.equipo || 'Equipo no especificado';
    document.getElementById('pres-estado').textContent = _ordenActual.estado;

    info.style.display = 'grid';
    wrap.style.display = 'block';

    limpiarPresupuesto();

    if (_ordenActual.motivo === 'Garantia' && _ordenActual.garantia === 'Aceptada') {
        var itemGarantia = _catalogoPres.find(function(p) {
            return _normalizar(p.servicio) === 'revision de garantia';
        });
        if (itemGarantia) {
            _agregarItemFila(itemGarantia.servicio, itemGarantia.precio, itemGarantia.descripcion || '', 1);
        }
    }
};

window.cargarOrdenPres = function(id) {
    if (!id) return;
    var sel = document.getElementById('sel-orden-pres');
    var opt = sel.options[sel.selectedIndex];
    if (!opt) return;

    seleccionarOrdenDinamica({
        id: id,
        nro_orden: opt.getAttribute('data-nro'),
        cliente: opt.getAttribute('data-cliente'),
        tipo: '',
        marca: '',
        modelo: opt.getAttribute('data-equipo'),
        estado_orden: opt.getAttribute('data-estado'),
        motivo_ingreso: opt.getAttribute('data-motivo'),
        estado_garantia: opt.getAttribute('data-garantia')
    });
};

// Cerrar dropdown al hacer clic afuera
document.addEventListener('click', function(e) {
    var wrap = document.querySelector('.pres-search-box-wrap');
    var drop = document.getElementById('dropdown-ordenes-resultados');
    if (wrap && !wrap.contains(e.target) && drop) {
        drop.style.display = 'none';
    }
});

// ── MODAL 1: SERVICIOS / MANO DE OBRA ──
window.abrirCatalogoPres = function() {
    document.getElementById('modal-cat-pres').style.display = 'flex';
    document.getElementById('buscar-cat-pres').value = '';
    renderCatPres(_catalogoPres);
};

window.cerrarCatalogoPres = function() {
    document.getElementById('modal-cat-pres').style.display = 'none';
};

window.filtrarCatPres = function(q) {
    var filtro = _normalizar(q);
    var lista = _catalogoPres.filter(function(p) {
        return _normalizar(p.servicio).indexOf(filtro) >= 0;
    });
    renderCatPres(lista);
};

function renderCatPres(lista) {
    var el = document.getElementById('lista-cat-pres');
    if (!lista.length) {
        el.innerHTML = '<p style="color:#94a3b8;text-align:center;padding:16px 0;">Sin resultados.</p>';
        return;
    }

    el.innerHTML = lista.map(function(p) {
        var base = parseFloat(p.precio || 0);
        var total = (base * 1.15).toFixed(2);
        return '<div class="pres-cat-item" onclick="agregarDesdeCat(' + p.id + ')">' +
            '<div><div class="cat-nombre fw-bold" style="color:#0f172a; font-size:13px;">' + p.servicio + '</div>' +
            (p.descripcion ? '<div class="cat-desc" style="font-size:11.5px; color:#64748b;">' + p.descripcion + '</div>' : '') +
            '<div class="cat-desc" style="font-size:11.5px; color:#475569; margin-top:2px;">Base: $' + base.toFixed(2) + ' + IVA 15%: <b style="color:#059669;">$' + total + '</b></div></div>' +
            '<div class="cat-precio fw-bold" style="font-size:14px; color:#1d4ed8;">$' + total + '</div>' +
            '</div>';
    }).join('');
}

window.agregarDesdeCat = function(id) {
    var p = _catalogoPres.find(function(x) { return Number(x.id) === Number(id); });
    if (!p) return;
    _agregarItemFila(p.servicio, p.precio, p.descripcion || '', 1);
    cerrarCatalogoPres();
};

// ── MODAL 2: ARTÍCULOS Y REPUESTOS (BATERÍAS, CARGADORES, STOCK) ──
window.abrirArticulosPres = function() {
    document.getElementById('modal-art-pres').style.display = 'flex';
    var inp = document.getElementById('buscar-art-pres');
    inp.value = '';
    inp.focus();
    buscarArticulosAjax('');
};

window.cerrarArticulosPres = function() {
    document.getElementById('modal-art-pres').style.display = 'none';
};

window.debounceBuscarArticulos = function(q) {
    clearTimeout(_articulosTimer);
    _articulosTimer = setTimeout(function() {
        buscarArticulosAjax(q);
    }, 250);
};

function buscarArticulosAjax(query) {
    var el = document.getElementById('lista-art-pres');
    el.innerHTML = '<p style="color:#94a3b8;text-align:center;padding:16px 0;"><i class="bi bi-hourglass-split"></i> Buscando en inventario...</p>';

    fetch('{{ route("presupuestos.buscar_articulos") }}?q=' + encodeURIComponent(query))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok || !data.articulos || !data.articulos.length) {
                el.innerHTML = '<p style="color:#94a3b8;text-align:center;padding:16px 0;">No se encontraron artículos con ese nombre o código.</p>';
                return;
            }

            el.innerHTML = data.articulos.map(function(a) {
                var costo = parseFloat(a.costo || 0);
                var precioSugerido = costo > 0 ? (costo * 1.30).toFixed(2) : '0.00';
                var stockBadge = a.stock > 0 
                    ? '<span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 7px; border-radius:4px;">Stock: ' + a.stock + '</span>'
                    : '<span style="background:#fee2e2; color:#dc2626; font-size:11px; font-weight:700; padding:2px 7px; border-radius:4px;">Sin Stock (' + a.stock + ')</span>';

                var aJson = JSON.stringify(a).replace(/"/g, '&quot;');
                return '<div class="pres-cat-item" onclick="agregarDesdeArticulo(' + aJson + ')">' +
                    '<div>' +
                        '<div style="font-weight:700; font-size:13px; color:#0f172a;">' + (a.nombre || '') + '</div>' +
                        '<div style="font-size:11.5px; color:#64748b;">Código: <span class="font-monospace text-primary">' + (a.codigo || '') + '</span> ' + stockBadge + '</div>' +
                    '</div>' +
                    '<div style="text-align:right;">' +
                        '<span style="font-size:11px; color:#64748b; display:block;">P. Sugerido</span>' +
                        '<span style="font-size:14px; font-weight:800; color:#15803d;">$' + precioSugerido + '</span>' +
                    '</div>' +
                '</div>';
            }).join('');
        })
        .catch(function(err) {
            console.error(err);
            el.innerHTML = '<p style="color:#ef4444;text-align:center;padding:16px 0;">Error al cargar artículos.</p>';
        });
}

window.agregarDesdeArticulo = function(art) {
    var costo = parseFloat(art.costo || 0);
    var precio = costo > 0 ? parseFloat((costo * 1.30).toFixed(2)) : 0;
    var desc = 'Código: ' + (art.codigo || '') + (art.descripcion ? (' - ' + art.descripcion) : '');
    _agregarItemFila(art.nombre, precio, desc, 1);
    cerrarArticulosPres();
};

// ── AGREGAR ÍTEM MANUAL / CUSTOM ──
window.agregarItemCustom = function() {
    _agregarItemFila('', 0, '', 1);
};

function _agregarItemFila(nombre, precio, desc, cantidad) {
    var lista = document.getElementById('pres-lista-items');
    var empty = document.getElementById('pres-empty-msg');
    if (empty) {
        empty.remove();
    }

    var id = ++_itemCount;
    var fila = document.createElement('div');
    fila.className = 'pres-item-fila';
    fila.id = 'pres-item-' + id;

    var info = document.createElement('div');
    info.className = 'pres-item-info';

    var inpNombre = document.createElement('input');
    inpNombre.type = 'text';
    inpNombre.className = 'pres-item-nombre';
    inpNombre.value = nombre || '';
    inpNombre.placeholder = 'Nombre del artículo o servicio...';

    var inpDesc = document.createElement('input');
    inpDesc.type = 'text';
    inpDesc.className = 'pres-item-desc';
    inpDesc.value = desc || '';
    inpDesc.placeholder = 'Detalles adicionales (opcional)...';

    info.appendChild(inpNombre);
    info.appendChild(inpDesc);

    // Cantidad
    var inpCant = document.createElement('input');
    inpCant.type = 'number';
    inpCant.className = 'pres-item-cant';
    inpCant.value = cantidad || 1;
    inpCant.min = '1';
    inpCant.step = '1';
    inpCant.title = 'Cantidad';
    inpCant.addEventListener('input', recalcularTotales);

    // Precio Unitario (Sin IVA)
    var inpPrecio = document.createElement('input');
    inpPrecio.type = 'number';
    inpPrecio.className = 'pres-item-precio';
    inpPrecio.value = precio || '';
    inpPrecio.min = '0';
    inpPrecio.step = '0.01';
    inpPrecio.placeholder = '$0.00';
    inpPrecio.title = 'Precio unitario sin IVA';
    inpPrecio.addEventListener('input', recalcularTotales);

    // Subtotal línea
    var subtotalFila = document.createElement('div');
    subtotalFila.className = 'pres-item-subtotal-val';
    subtotalFila.textContent = '$0.00';

    // Botón borrar
    var btnDel = document.createElement('button');
    btnDel.type = 'button';
    btnDel.className = 'pres-btn-del';
    btnDel.innerHTML = '<i class="bi bi-trash"></i>';
    btnDel.addEventListener('click', function() {
        fila.remove();
        recalcularTotales();
        if (!document.querySelector('.pres-item-fila')) {
            lista.innerHTML = '<div class="pres-empty" id="pres-empty-msg"><i class="bi bi-inbox" style="font-size:24px; display:block; margin-bottom:4px; opacity:.5;"></i>No hay ítems agregados. Usa los botones superiores para agregar artículos, servicios o ítems manuales.</div>';
            document.getElementById('pres-totales').style.display = 'none';
        }
    });

    fila.appendChild(info);
    fila.appendChild(inpCant);
    fila.appendChild(inpPrecio);
    fila.appendChild(subtotalFila);
    fila.appendChild(btnDel);
    lista.appendChild(fila);

    recalcularTotales();
}

function recalcularTotales() {
    var filas = document.querySelectorAll('.pres-item-fila');
    var subtotal = 0;

    filas.forEach(function(f) {
        var cant = parseFloat(f.querySelector('.pres-item-cant')?.value) || 1;
        var pUnit = parseFloat(f.querySelector('.pres-item-precio')?.value) || 0;
        var lineTotal = cant * pUnit;
        subtotal += lineTotal;

        var subEl = f.querySelector('.pres-item-subtotal-val');
        if (subEl) {
            subEl.textContent = '$' + lineTotal.toFixed(2);
        }
    });

    var iva = subtotal * 0.15;
    var total = subtotal + iva;
    var totEl = document.getElementById('pres-totales');

    if (filas.length > 0) {
        totEl.style.display = 'flex';
        document.getElementById('pres-subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('pres-iva').textContent = '$' + iva.toFixed(2);
        document.getElementById('pres-total').textContent = '$' + total.toFixed(2);
    } else {
        totEl.style.display = 'none';
    }
}

function construirPayloadPresupuesto() {
    var filas = document.querySelectorAll('.pres-item-fila');
    if (!filas.length) {
        alert('Agrega al menos un ítem al presupuesto.');
        return null;
    }

    var items = [];
    filas.forEach(function(f) {
        var nombre = f.querySelector('.pres-item-nombre').value.trim();
        var desc = f.querySelector('.pres-item-desc').value.trim();
        var cant = parseInt(f.querySelector('.pres-item-cant').value) || 1;
        var precio = parseFloat(f.querySelector('.pres-item-precio').value) || 0;
        if (nombre) {
            items.push({ 
                nombre: nombre, 
                desc: desc, 
                cantidad: cant,
                precio: precio,
                subtotal_linea: (cant * precio).toFixed(2)
            });
        }
    });

    if (!items.length) {
        alert('Agrega al menos un ítem con nombre válido.');
        return null;
    }

    if (_modoActual === 'orden') {
        if (!_ordenActual || !_ordenActual.id) {
            alert('Por favor busca y selecciona una orden de servicio primero.');
            return null;
        }
        return {
            items: items,
            notas: document.getElementById('pres-notas').value.trim()
        };
    } else {
        // Modo directo
        var cliente = document.getElementById('dir-cliente-nombre').value.trim();
        var iden = document.getElementById('dir-cliente-identificacion').value.trim();
        var tel = document.getElementById('dir-cliente-telefono').value.trim();
        return {
            cliente: cliente || 'CONSUMIDOR FINAL',
            identificacion: iden,
            telefono: tel,
            items: items,
            notas: document.getElementById('pres-notas').value.trim()
        };
    }
}

function abrirVistaPresupuesto(autoImprimir) {
    var payload = construirPayloadPresupuesto();
    if (!payload) return;

    var json = JSON.stringify(payload);
    var base64 = btoa(unescape(encodeURIComponent(json)));

    var url = '';
    if (_modoActual === 'orden') {
        url = '{{ url("/operaciones/presupuestos") }}/' + encodeURIComponent(String(_ordenActual.id)) + '/imprimir'
            + '?payload=' + encodeURIComponent(base64)
            + (autoImprimir ? '&auto=1' : '');
    } else {
        url = '{{ route("presupuestos.imprimir_directa") }}'
            + '?payload=' + encodeURIComponent(base64)
            + (autoImprimir ? '&auto=1' : '');
    }

    var win = window.open(url, '_blank', 'width=980,height=760,scrollbars=yes');
    if (!win) {
        alert('El navegador bloqueó la ventana emergente. Habilita pop-ups para este sitio.');
        return false;
    }
    return true;
}

window.previsualizarPresupuesto = function() {
    abrirVistaPresupuesto(false);
};

window.imprimirPresupuesto = function() {
    abrirVistaPresupuesto(true);
};

window.limpiarPresupuesto = function() {
    document.getElementById('pres-lista-items').innerHTML = '<div class="pres-empty" id="pres-empty-msg"><i class="bi bi-inbox" style="font-size:24px; display:block; margin-bottom:4px; opacity:.5;"></i>No hay ítems agregados. Usa los botones superiores para agregar artículos, servicios o ítems manuales.</div>';
    document.getElementById('pres-totales').style.display = 'none';
    document.getElementById('pres-notas').value = '';
    _itemCount = 0;
    recalcularTotales();
};
})();
</script>
@endpush
