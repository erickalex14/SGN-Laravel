@extends('layouts.app')
@section('titulo', 'Presupuestos')

@section('contenido')
<section class="modulo activo">
<div class="pres-container">
    <div class="form-titulo">
        <h2><i class="bi bi-receipt me-2"></i>Presupuestos</h2>
        <p>Genera y gestiona presupuestos para tus ordenes de servicio</p>
    </div>

    <div class="pres-card">
        <div class="pres-card-title"><i class="bi bi-search me-2"></i>Seleccionar Orden</div>
        <div class="pres-buscar-row">
            <select id="sel-orden-pres" onchange="cargarOrdenPres(this.value)">
                <option value="">-- Selecciona una orden --</option>
                @foreach($ordenes as $o)
                <option value="{{ $o->id }}"
                        data-nro="{{ $o->nro_orden }}"
                        data-cliente="{{ $o->cliente }}"
                        data-equipo="{{ trim(($o->tipo ?? '').' '.($o->marca ?? '').' '.($o->modelo ?? '')) }}"
                        data-estado="{{ $o->estado_orden }}"
                        data-motivo="{{ $o->motivo_ingreso }}"
                        data-garantia="{{ $o->estado_garantia ?? '' }}">
                    {{ $o->nro_orden }} - {{ $o->cliente }} - {{ trim(($o->tipo ?? '').' '.($o->marca ?? '')) }}
                </option>
                @endforeach
            </select>
        </div>
        <div id="pres-orden-info" style="display:none;" class="pres-orden-info">
            <div class="pres-info-item"><span class="pres-info-lbl">Nro. Orden</span><span id="pres-nro-orden">-</span></div>
            <div class="pres-info-item"><span class="pres-info-lbl">Cliente</span><span id="pres-cliente">-</span></div>
            <div class="pres-info-item"><span class="pres-info-lbl">Equipo</span><span id="pres-equipo">-</span></div>
            <div class="pres-info-item"><span class="pres-info-lbl">Estado</span><span id="pres-estado">-</span></div>
        </div>
    </div>

    <div id="pres-form-wrap" style="display:none;">
        <div class="pres-card">
            <div class="pres-card-title">
                <span><i class="bi bi-list-check me-2"></i>Items del Presupuesto</span>
                <div class="pres-btns-add">
                    <button type="button" class="pres-btn-catalogo" onclick="abrirCatalogoPres()">
                        <i class="bi bi-tag me-1"></i>Del catalogo
                    </button>
                    <button type="button" class="pres-btn-custom" onclick="agregarItemCustom()">
                        <i class="bi bi-plus-circle me-1"></i>Personalizado
                    </button>
                </div>
            </div>
            <div id="pres-lista-items">
                <div class="pres-empty" id="pres-empty-msg">Sin items. Agrega desde el catalogo o personalizado.</div>
            </div>
            <div class="pres-totales" id="pres-totales" style="display:none;">
                <div class="pres-total-row"><span>Subtotal</span><span id="pres-subtotal">$0.00</span></div>
                <div class="pres-total-row pres-iva"><span>IVA 15%</span><span id="pres-iva">$0.00</span></div>
                <div class="pres-total-row pres-total-final"><span>TOTAL</span><span id="pres-total">$0.00</span></div>
            </div>
        </div>

        <div class="pres-card">
            <div class="pres-card-title"><i class="bi bi-chat-left-text me-2"></i>Notas / Condiciones</div>
            <textarea id="pres-notas" rows="3" placeholder="Observaciones, condiciones de pago, tiempo estimado de entrega..."></textarea>
        </div>

        <div class="pres-acciones">
            <button type="button" class="pres-btn-imprimir" onclick="imprimirPresupuesto()">
                <i class="bi bi-printer me-2"></i>Imprimir / Guardar PDF
            </button>
            <button type="button" class="pres-btn-limpiar" onclick="limpiarPresupuesto()">
                <i class="bi bi-x-circle me-2"></i>Limpiar
            </button>
        </div>
    </div>
</div>

<div id="modal-cat-pres" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;"
     onclick="if(event.target===this)cerrarCatalogoPres()">
    <div class="modal-cat-inner">
        <h4><i class="bi bi-tag me-2" style="color:#2563eb;"></i>Seleccionar del Catalogo</h4>
        <input type="text" id="buscar-cat-pres" placeholder="Buscar servicio..." oninput="filtrarCatPres(this.value)">
        <div id="lista-cat-pres">Cargando...</div>
        <button onclick="cerrarCatalogoPres()" class="pres-btn-cerrar-modal">Cerrar</button>
    </div>
</div>
</section>
@endsection

@push('css_adicional')
<style>
.pres-container{max-width:860px;margin:0 auto;padding:28px 20px;}
.form-titulo h2{font-size:20px;font-weight:800;color:#0f172a;margin:0 0 4px;}
.form-titulo p{color:#94a3b8;font-size:13px;margin:0 0 24px;}
.pres-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:20px;overflow:hidden;}
.pres-card-title{display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border-bottom:1.5px solid #e2e8f0;padding:13px 20px;font-size:14px;font-weight:700;color:#1e293b;}
.pres-buscar-row{padding:18px 20px;}
.pres-buscar-row select{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;}
.pres-orden-info{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:0 20px 18px;}
.pres-info-item{display:flex;flex-direction:column;gap:2px;}
.pres-info-lbl{font-size:10.5px;font-weight:700;text-transform:uppercase;color:#94a3b8;}
.pres-btns-add{display:flex;gap:8px;}
.pres-btn-catalogo,.pres-btn-custom{border:none;border-radius:7px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer;}
.pres-btn-catalogo{background:#eff6ff;color:#1d4ed8;}
.pres-btn-custom{background:#f0fdf4;color:#166534;}
#pres-lista-items{padding:14px 20px;}
.pres-empty{font-size:12.5px;color:#94a3b8;font-style:italic;padding:6px 0;}
.pres-item-fila{display:grid;grid-template-columns:1fr 110px auto;gap:10px;align-items:center;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:9px;padding:11px 14px;margin-bottom:8px;}
.pres-item-info{display:flex;flex-direction:column;gap:3px;}
.pres-item-nombre,.pres-item-desc{border:none;background:transparent;width:100%;}
.pres-item-precio{border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 9px;text-align:right;width:100%;}
.pres-btn-del{background:#fee2e2;color:#dc2626;border:none;border-radius:7px;padding:7px 10px;cursor:pointer;}
.pres-totales{border-top:1.5px solid #e2e8f0;padding:14px 20px;display:flex;flex-direction:column;align-items:flex-end;gap:6px;}
.pres-total-row{display:flex;gap:40px;justify-content:flex-end;font-size:13px;font-weight:600;color:#374151;}
.pres-total-final{border-top:2px solid #e2e8f0;padding-top:8px;margin-top:2px;}
#pres-notas{width:100%;border:none;padding:16px 20px;font-size:13px;resize:vertical;min-height:80px;}
.pres-acciones{display:flex;gap:12px;margin-top:4px;}
.pres-btn-imprimir{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:9px;padding:12px 24px;font-size:14px;font-weight:700;cursor:pointer;}
.pres-btn-limpiar{background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;border-radius:9px;padding:12px 20px;}
.modal-cat-inner{background:#fff;border-radius:14px;padding:24px 28px;max-width:540px;width:90%;max-height:80vh;overflow-y:auto;}
.modal-cat-inner input{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;margin-bottom:14px;}
.pres-cat-item{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:9px;cursor:pointer;border:1.5px solid #e2e8f0;margin-bottom:4px;}
.pres-btn-cerrar-modal{margin-top:14px;width:100%;background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;border-radius:9px;padding:10px;}
</style>
@endpush

@push('js_adicional')
<script>
(function() {
var _catalogoPres = @json($catalogo);
var _itemCount = 0;
var _ordenActual = null;

function _normalizar(texto) {
    return String(texto || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
}

window.cargarOrdenPres = function(id) {
    var sel = document.getElementById('sel-orden-pres');
    var opt = sel.options[sel.selectedIndex];
    var info = document.getElementById('pres-orden-info');
    var wrap = document.getElementById('pres-form-wrap');

    if (!id) {
        info.style.display = 'none';
        wrap.style.display = 'none';
        _ordenActual = null;
        return;
    }

    _ordenActual = {
        id: id,
        nro: opt.getAttribute('data-nro'),
        cliente: opt.getAttribute('data-cliente'),
        equipo: opt.getAttribute('data-equipo'),
        estado: opt.getAttribute('data-estado'),
        motivo: opt.getAttribute('data-motivo'),
        garantia: opt.getAttribute('data-garantia')
    };

    document.getElementById('pres-nro-orden').textContent = _ordenActual.nro;
    document.getElementById('pres-cliente').textContent = _ordenActual.cliente;
    document.getElementById('pres-equipo').textContent = _ordenActual.equipo;
    document.getElementById('pres-estado').textContent = _ordenActual.estado;

    info.style.display = 'grid';
    wrap.style.display = 'block';

    limpiarPresupuesto();

    if (_ordenActual.motivo === 'Garantia' && _ordenActual.garantia === 'Aceptada') {
        var itemGarantia = _catalogoPres.find(function(p) {
            return _normalizar(p.servicio) === 'revision de garantia';
        });
        if (itemGarantia) {
            _agregarItemFila(itemGarantia.servicio, itemGarantia.precio, itemGarantia.descripcion || '');
        }
    }
};

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
            '<div><div class="cat-nombre">' + p.servicio + '</div>' +
            (p.descripcion ? '<div class="cat-desc">' + p.descripcion + '</div>' : '') +
            '<div class="cat-desc">Sin IVA: $' + base.toFixed(2) + ' + IVA 15%: <b style="color:#059669;">$' + total + '</b></div></div>' +
            '<div class="cat-precio">$' + total + '</div>' +
            '</div>';
    }).join('');
}

window.agregarDesdeCat = function(id) {
    var p = _catalogoPres.find(function(x) {
        return Number(x.id) === Number(id);
    });
    if (!p) {
        return;
    }

    _agregarItemFila(p.servicio, p.precio, p.descripcion || '');
    cerrarCatalogoPres();
};

window.agregarItemCustom = function() {
    _agregarItemFila('', 0, '');
};

function _agregarItemFila(nombre, precio, desc) {
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
    inpNombre.placeholder = 'Nombre del servicio';

    var inpDesc = document.createElement('input');
    inpDesc.type = 'text';
    inpDesc.className = 'pres-item-desc';
    inpDesc.value = desc || '';
    inpDesc.placeholder = 'Descripcion opcional';

    info.appendChild(inpNombre);
    info.appendChild(inpDesc);

    var inpPrecio = document.createElement('input');
    inpPrecio.type = 'number';
    inpPrecio.className = 'pres-item-precio';
    inpPrecio.value = precio || '';
    inpPrecio.min = '0';
    inpPrecio.step = '0.01';
    inpPrecio.placeholder = '$0.00';
    inpPrecio.title = 'Precio sin IVA';
    inpPrecio.addEventListener('input', recalcularTotales);

    var btnDel = document.createElement('button');
    btnDel.type = 'button';
    btnDel.className = 'pres-btn-del';
    btnDel.innerHTML = '<i class="bi bi-trash"></i>';
    btnDel.addEventListener('click', function() {
        fila.remove();
        recalcularTotales();
        if (!document.querySelector('.pres-item-fila')) {
            lista.innerHTML = '<div class="pres-empty" id="pres-empty-msg">Sin items. Agrega desde el catalogo o personalizado.</div>';
            document.getElementById('pres-totales').style.display = 'none';
        }
    });

    fila.appendChild(info);
    fila.appendChild(inpPrecio);
    fila.appendChild(btnDel);
    lista.appendChild(fila);

    recalcularTotales();
}

function recalcularTotales() {
    var inputs = document.querySelectorAll('.pres-item-precio');
    var subtotal = 0;
    inputs.forEach(function(i) {
        subtotal += parseFloat(i.value) || 0;
    });

    var iva = subtotal * 0.15;
    var total = subtotal + iva;
    var totEl = document.getElementById('pres-totales');

    if (inputs.length > 0) {
        totEl.style.display = 'flex';
        document.getElementById('pres-subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('pres-iva').textContent = '$' + iva.toFixed(2);
        document.getElementById('pres-total').textContent = '$' + total.toFixed(2);
    } else {
        totEl.style.display = 'none';
    }
}

window.imprimirPresupuesto = function() {
    if (!_ordenActual) {
        alert('Selecciona una orden primero.');
        return;
    }

    var filas = document.querySelectorAll('.pres-item-fila');
    if (!filas.length) {
        alert('Agrega al menos un item al presupuesto.');
        return;
    }

    var items = [];
    filas.forEach(function(f) {
        var nombre = f.querySelector('.pres-item-nombre').value.trim();
        var desc = f.querySelector('.pres-item-desc').value.trim();
        var precio = parseFloat(f.querySelector('.pres-item-precio').value) || 0;
        if (nombre) {
            items.push({ nombre: nombre, desc: desc, precio: precio });
        }
    });

    if (!items.length) {
        alert('Agrega al menos un item valido.');
        return;
    }

    var notas = document.getElementById('pres-notas').value.trim();
    var subtotal = items.reduce(function(s, i) { return s + i.precio; }, 0);
    var iva = subtotal * 0.15;
    var total = subtotal + iva;
    var tecnico = @json(session('nombre') ?? session('usuario') ?? '');
    var fecha = new Date().toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });

    var filasTbl = items.map(function(i) {
        return '<tr>' +
            '<td style="padding:7px 10px;border:1px solid #e2e8f0;">' + i.nombre +
            (i.desc ? '<div style="font-size:10px;color:#64748b;">' + i.desc + '</div>' : '') + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #e2e8f0;text-align:right;font-weight:600;">$' + i.precio.toFixed(2) + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #e2e8f0;text-align:right;font-weight:600;color:#059669;">$' + (i.precio * 1.15).toFixed(2) + '</td>' +
            '</tr>';
    }).join('');

    var html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">' +
        '<title>Presupuesto ' + _ordenActual.nro + '</title>' +
        '<style>' +
        '* { margin:0; padding:0; box-sizing:border-box; }' +
        'body { font-family:Arial,sans-serif; font-size:9pt; color:#000; background:#fff; }' +
        '@media print { @page { size:A4 portrait; margin:10mm; } .no-print { display:none!important; } body { print-color-adjust:exact; -webkit-print-color-adjust:exact; } }' +
        '.wrap { max-width:190mm; margin:auto; padding:6mm; }' +
        '.header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1.5px solid #000; padding-bottom:6px; margin-bottom:10px; }' +
        '.empresa { font-size:11pt; font-weight:bold; }' +
        '.header-info { font-size:8.5pt; line-height:1.6; }' +
        '.badge-pres { background:#1a56db; color:#fff; padding:5px 12px; border-radius:4px; font-size:13pt; font-weight:bold; }' +
        '.sec { background:#dbeafe; font-weight:bold; font-size:7.5pt; text-transform:uppercase; padding:3px 8px; border-left:3px solid #1a56db; margin:8px 0 3px; }' +
        'table { width:100%; border-collapse:collapse; margin-bottom:8px; }' +
        'td { font-size:8.5pt; vertical-align:top; }' +
        '.lbl { font-size:6.5pt; color:#6b7280; font-weight:bold; text-transform:uppercase; display:block; margin-bottom:1px; }' +
        '.firma-box { width:44%; text-align:center; }' +
        '.firma-linea { border-top:1px solid #000; padding-top:4px; font-size:8.5pt; margin-top:28px; }' +
        '.nota { background:#fef9c3; border:1px solid #fde047; border-radius:3px; font-size:7.5pt; color:#713f12; text-align:center; padding:5px 10px; margin-top:10px; }' +
        '</style></head><body>' +
        '<button class="no-print" onclick="window.print()" style="position:fixed;top:10px;right:10px;background:#1a56db;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:bold;">&#128438; Imprimir / Guardar PDF</button>' +
        '<div class="wrap">' +
        '<div class="header">' +
            '<div class="header-info">' +
                '<div class="empresa">Novitecnologia Cia. Ltda.</div>' +
                '<div><b>GYE:</b> 04-6031337 &nbsp; <b>UIO:</b> 02-6001635 &nbsp; <b>MTA:</b> 05-2611080</div>' +
                '<div>soporte@novitec.com.ec &nbsp; www.novitec.com.ec</div>' +
            '</div>' +
            '<div style="text-align:right;">' +
                '<div class="badge-pres">' + _ordenActual.nro + '</div>' +
                '<div style="font-size:8pt;margin-top:4px;color:#475569;">Presupuesto - ' + fecha + '</div>' +
            '</div>' +
        '</div>' +
        '<div class="sec">Datos de la Orden</div>' +
        '<table><tr>' +
            '<td width="30%" style="border:1px solid #e2e8f0;padding:5px 8px;"><span class="lbl">Nro. Orden</span>' + _ordenActual.nro + '</td>' +
            '<td width="40%" style="border:1px solid #e2e8f0;padding:5px 8px;"><span class="lbl">Cliente</span>' + _ordenActual.cliente + '</td>' +
            '<td width="30%" style="border:1px solid #e2e8f0;padding:5px 8px;"><span class="lbl">Tecnico</span>' + tecnico + '</td>' +
        '</tr><tr>' +
            '<td colspan="3" style="border:1px solid #e2e8f0;padding:5px 8px;"><span class="lbl">Equipo</span>' + _ordenActual.equipo + '</td>' +
        '</tr></table>' +
        '<div class="sec">Detalle del Presupuesto</div>' +
        '<table>' +
            '<tr style="background:#f1f5f9;">' +
                '<th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:left;font-size:8pt;">Servicio / Reparacion</th>' +
                '<th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:right;font-size:8pt;">Sin IVA</th>' +
                '<th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:right;font-size:8pt;">Con IVA 15%</th>' +
            '</tr>' +
            filasTbl +
        '</table>' +
        '<table style="width:45%;margin-left:auto;">' +
            '<tr><td style="padding:5px 10px;border:1px solid #e2e8f0;"><span class="lbl">Subtotal</span></td>' +
                '<td style="padding:5px 10px;border:1px solid #e2e8f0;text-align:right;font-weight:600;">$' + subtotal.toFixed(2) + '</td></tr>' +
            '<tr><td style="padding:5px 10px;border:1px solid #e2e8f0;"><span class="lbl">IVA 15%</span></td>' +
                '<td style="padding:5px 10px;border:1px solid #e2e8f0;text-align:right;font-weight:600;color:#f59e0b;">$' + iva.toFixed(2) + '</td></tr>' +
            '<tr style="background:#f0fdf4;"><td style="padding:5px 10px;border:1px solid #e2e8f0;"><span class="lbl">TOTAL</span></td>' +
                '<td style="padding:5px 10px;border:1px solid #e2e8f0;text-align:right;font-size:12pt;font-weight:800;color:#059669;">$' + total.toFixed(2) + '</td></tr>' +
        '</table>' +
        (notas ? '<div class="sec">Notas / Condiciones</div><div style="font-size:8.5pt;padding:6px 8px;border:1px solid #e2e8f0;border-radius:4px;">' + notas + '</div>' : '') +
        '<div style="display:flex;justify-content:space-between;margin-top:20px;">' +
            '<div class="firma-box"><div class="firma-linea">Tecnico:</div></div>' +
            '<div class="firma-box"><div class="firma-linea">Cliente acepta:</div></div>' +
        '</div>' +
        '<div class="nota"><b>NOTA:</b> Este presupuesto es valido por 15 dias calendario desde la fecha de emision. Los precios incluyen IVA 15%.</div>' +
        '<div style="text-align:center;margin-top:8px;font-size:7pt;color:#94a3b8;border-top:1px solid #e5e7eb;padding-top:6px;">Novitecnologia Cia. Ltda. - Sistema de Gestion Novitec</div>' +
        '</div></body></html>';

    var win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
};

window.limpiarPresupuesto = function() {
    document.getElementById('pres-lista-items').innerHTML = '<div class="pres-empty" id="pres-empty-msg">Sin items. Agrega desde el catalogo o personalizado.</div>';
    document.getElementById('pres-totales').style.display = 'none';
    document.getElementById('pres-notas').value = '';
    _itemCount = 0;
    recalcularTotales();
};
})();
</script>
@endpush
