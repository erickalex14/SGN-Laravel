@extends('layouts.app')
@section('titulo', 'Pre-Ordenes')

@push('css_adicional')
<style>
.modulo{padding:30px;background:#f1f5f9;min-height:100%;}
.po-container{max-width:1300px;margin:0 auto;}
.form-titulo{margin-bottom:24px;padding-bottom:18px;border-bottom:2px solid #e2e8f0;}
.form-titulo h2{margin:0 0 4px;color:#0f172a;font-size:22px;font-weight:700;}
.form-titulo p{margin:0;color:#94a3b8;font-size:14px;}
.po-toolbar{display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap;}
.po-search{padding:9px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;width:280px;}
.po-count-badge{background:#eff6ff;color:#1d4ed8;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;border:1px solid #bfdbfe;}
.po-refresh{padding:8px 16px;background:#fff;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;}
.po-table-box{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;}
.po-table{width:100%;border-collapse:collapse;font-size:13px;}
.po-table th{padding:10px 14px;text-align:left;font-weight:700;font-size:11px;color:#64748b;background:#f8fafc;border-bottom:1px solid #e2e8f0;text-transform:uppercase;}
.po-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.po-nro{font-weight:800;color:#1d4ed8;font-size:13px;}
.po-cliente{font-weight:600;color:#0f172a;}
.po-sub{font-size:11px;color:#94a3b8;margin-top:2px;}
.po-codigo{background:#f1f5f9;padding:2px 7px;border-radius:4px;font-size:11px;font-family:monospace;}
.btn-ingresar{padding:6px 14px;background:#16a34a;color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;}
.btn-ver-fotos{padding:5px 10px;background:#eff6ff;color:#2563eb;border:1.5px solid #bfdbfe;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;}
.po-empty{padding:60px;text-align:center;color:#94a3b8;font-size:14px;}
.po-modal-overlay,.pof-modal-overlay{position:fixed;inset:0;z-index:9000;display:none;align-items:center;justify-content:center;}
.po-modal-overlay{background:rgba(0,0,0,.55);}
.pof-modal-overlay{background:rgba(0,0,0,.75);}
.po-modal-overlay.open,.pof-modal-overlay.open{display:flex;}
.po-modal{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;}
.po-resumen-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:20px;}
.po-resumen-row{display:flex;gap:8px;padding:4px 0;font-size:13px;}
.po-resumen-row:not(:last-child){border-bottom:1px solid #f1f5f9;}
.po-rl{color:#94a3b8;font-weight:600;min-width:130px;flex-shrink:0;}
.po-rv{color:#0f172a;font-weight:500;word-break:break-word;}
.po-campo{display:flex;flex-direction:column;gap:6px;margin-bottom:16px;}
.po-campo input,.po-campo select,.po-campo textarea{padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;}
.po-modal-botones{display:flex;gap:10px;}
.btn-confirmar-ingreso{flex:1;padding:12px;background:#16a34a;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;}
.btn-confirmar-ingreso:disabled{background:#86efac;cursor:not-allowed;}
.btn-cancelar-modal{padding:12px 20px;background:#f1f5f9;color:#64748b;border:1.5px solid #e2e8f0;border-radius:9px;}
.po-modal-msg{padding:10px 14px;border-radius:8px;font-size:13px;font-weight:600;display:none;margin-top:12px;}
.po-modal-msg.ok{background:#dcfce7;color:#166534;border:1px solid #86efac;}
.po-modal-msg.err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
.pof-modal{background:#fff;border-radius:14px;padding:24px;width:100%;max-width:700px;max-height:90vh;overflow-y:auto;}
.pof-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.pof-img{width:100%;border-radius:8px;border:1px solid #e2e8f0;}
.pof-lbl{font-size:11px;color:#94a3b8;text-align:center;margin-top:4px;}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="po-container">
    <div class="form-titulo">
        <h2><i class="bi bi-inbox-arrow-down me-2"></i>Pre-Ordenes Pendientes</h2>
        <p>Ingresa las pre-ordenes del portal de garantias como ordenes de trabajo en el SGN</p>
    </div>

    @php
        $ciudadesUnicas = [];
        foreach($preordenes as $po) {
            if (!empty($po->ciudad_procedencia)) {
                $ciudadesUnicas[] = trim(strtoupper($po->ciudad_procedencia));
            }
            if (!empty($po->sucursal_ciudad)) {
                $ciudadesUnicas[] = trim(strtoupper($po->sucursal_ciudad));
            }
        }
        $ciudadesUnicas = array_unique($ciudadesUnicas);
        sort($ciudadesUnicas);
    @endphp

    <div class="po-toolbar">
        <input type="text" class="po-search" id="po-buscar" placeholder="Buscar por nro, cliente, factura, codigo..." oninput="poBuscar()">
        <select class="po-search" id="po-filtro-ciudad" onchange="poBuscar()" style="width: 180px;">
            <option value="">-- Ciudad --</option>
            @foreach($ciudadesUnicas as $ciudad)
                <option value="{{ strtolower($ciudad) }}">{{ $ciudad }}</option>
            @endforeach
        </select>
        <span class="po-count-badge" id="po-count">{{ count($preordenes) }} pendiente(s)</span>
        <button class="po-refresh" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
    </div>

    <div class="po-table-box">
        @if(count($preordenes) === 0)
            <div class="po-empty"><i class="bi bi-inbox"></i>No hay pre-ordenes pendientes de ingresar.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="po-table">
                <thead>
                    <tr>
                        <th>Pre-Orden</th><th>Cliente</th><th>Factura</th><th>Equipo</th><th>Suc. Cliente</th><th>Ingresado / Procedencia</th><th>Fotos</th><th>Accion</th>
                    </tr>
                </thead>
                <tbody id="po-tbody">
                    @foreach($preordenes as $po)
                    @php
                        $poJson = [
                            'id' => $po->id, 'nro_preorden' => $po->nro_preorden, 'nombres' => $po->nombres, 'apellidos' => $po->apellidos,
                            'telefono' => $po->telefono, 'correo' => $po->correo, 'nro_factura' => $po->nro_factura, 'fecha_facturacion' => $po->fecha_facturacion,
                            'codigo_producto' => $po->codigo_producto, 'desc_producto' => $po->desc_producto, 'marca_producto' => $po->marca_producto,
                            'tipo_producto' => $po->tipo_producto, 'detalle_equipo' => $po->detalle_equipo, 'sucursal_id' => $po->sucursal_id,
                            'nro_sucursal_cliente' => $po->nro_sucursal_cliente, 'sucursal_cliente_nombre' => $po->sucursal_cliente_nombre,
                            'sucursal_cliente_numero' => $po->sucursal_cliente_numero, 'sucursal_ciudad' => $po->sucursal_ciudad,
                            'ciudad_procedencia' => $po->ciudad_procedencia
                        ];
                        $fotos = [$po->foto_1, $po->foto_2, $po->foto_3, $po->foto_4];
                    @endphp
                    <tr data-nro="{{ strtolower($po->nro_preorden) }}"
                        data-cliente="{{ strtolower(trim($po->nombres.' '.$po->apellidos)) }}"
                        data-fac="{{ strtolower($po->nro_factura ?? '') }}"
                        data-cod="{{ strtolower($po->codigo_producto ?? '') }}"
                        data-ciudad-proc="{{ strtolower($po->ciudad_procedencia ?? '') }}"
                        data-ciudad-suc="{{ strtolower($po->sucursal_ciudad ?? '') }}">
                        <td><div class="po-nro">{{ $po->nro_preorden }}</div><div class="po-sub">{{ $po->fecha_registro ? \Carbon\Carbon::parse($po->fecha_registro)->format('d/m/Y H:i') : '-' }}</div></td>
                        <td><div class="po-cliente">{{ $po->nombres }} {{ $po->apellidos }}</div><div class="po-sub">{{ $po->telefono }}</div></td>
                        <td><div>{{ $po->nro_factura ?: '-' }}</div><div class="po-sub">{{ $po->fecha_facturacion ? \Carbon\Carbon::parse($po->fecha_facturacion)->format('d/m/Y') : '-' }}</div></td>
                        <td><span class="po-codigo">{{ $po->codigo_producto ?: '-' }}</span><div class="po-sub">{{ trim(($po->tipo_producto ?? '').' '.($po->marca_producto ?? '').' - '.($po->desc_producto ?? '')) }}</div></td>
                        <td>{{ $po->sucursal_cliente_numero ? str_pad((int)$po->sucursal_cliente_numero, 3, '0', STR_PAD_LEFT).' - '.$po->sucursal_cliente_nombre : '-' }}</td>
                        <td>
                            @if(!empty($po->ciudad_procedencia))
                                <div class="po-sub" style="font-weight: 700; color: #0f172a;"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ strtoupper($po->ciudad_procedencia) }}</div>
                            @endif
                            <div class="po-sub" style="font-size: 11px;"><i class="bi bi-shop text-primary me-1"></i>{{ $po->sucursal_ciudad ?: '-' }}</div>
                        </td>
                        <td>
                            <button class="btn-ver-fotos" onclick='poVerFotos(@json($fotos))'><i class="bi bi-images me-1"></i>Fotos</button>
                            <button class="btn-ver-fotos" onclick="poImprimirPreorden({{ $po->id }})"><i class="bi bi-printer me-1"></i>Comprobante</button>
                        </td>
                        <td><button class="btn-ingresar" onclick='poAbrirModal(@json($poJson))'><i class="bi bi-box-arrow-in-down me-1"></i>Ingresar</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    <div id="po-pager"></div>
</div>
</section>

<div class="po-modal-overlay" id="po-modal-overlay">
    <div class="po-modal">
        <h3><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Ingresar Pre-Orden al SGN</h3>
        <p id="pm-sub"></p>
        <div class="po-resumen-box" id="pm-resumen"></div>
        <div class="po-campo">
            <label>Tecnico Asignado *</label>
            <select id="pm-tecnico">
                <option value="">-- Seleccionar --</option>
                @foreach($tecnicos as $t)
                <option value="{{ $t->id }}">{{ $t->nombre_tecnico }}</option>
                @endforeach
            </select>
        </div>
        <div class="po-campo"><label>Direccion del Cliente *</label><input type="text" id="pm-direccion" maxlength="200" oninput="this.value=this.value.toUpperCase()"></div>
        <div class="po-campo"><label>Serie del Equipo</label><input type="text" id="pm-serie" maxlength="100" oninput="this.value=this.value.toUpperCase()"></div>
        <div class="po-campo"><label>Fecha Prometido *</label><input type="date" id="pm-fecha-prometido"></div>
        <div class="po-campo"><label>Observaciones adicionales</label><textarea id="pm-obs" rows="2"></textarea></div>
        <div id="pm-msg" class="po-modal-msg"></div>
        <div class="po-modal-botones">
            <button class="btn-confirmar-ingreso" id="btn-confirmar" onclick="poConfirmarIngreso()">Crear Orden de Trabajo</button>
            <button class="btn-cancelar-modal" onclick="poCerrarModal()">Cancelar</button>
        </div>
    </div>
</div>

<div class="pof-modal-overlay" id="pof-overlay" onclick="pofCerrar(event)">
    <div class="pof-modal">
        <h3><i class="bi bi-images me-2"></i>Fotos del Equipo</h3>
        <div class="pof-grid" id="pof-grid"></div>
        <button class="btn-cancelar-modal" style="margin-top:16px;width:100%;" onclick="document.getElementById('pof-overlay').classList.remove('open')">Cerrar</button>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
var _poActual = null;
function poBuscar() {
    var q = document.getElementById('po-buscar').value.toLowerCase().trim();
    var ciudad = document.getElementById('po-filtro-ciudad').value.toLowerCase().trim();
    var filas = document.querySelectorAll('#po-tbody tr[data-nro]');
    var vis = 0;
    filas.forEach(function(tr){
        var matchQ = !q || tr.dataset.nro.includes(q) || tr.dataset.cliente.includes(q) || tr.dataset.fac.includes(q) || tr.dataset.cod.includes(q);
        var matchCiudad = !ciudad || 
            (tr.dataset.ciudadProc && tr.dataset.ciudadProc.includes(ciudad)) || 
            (tr.dataset.ciudadSuc && tr.dataset.ciudadSuc.includes(ciudad));
        
        var match = matchQ && matchCiudad;
        tr.style.display = match ? '' : 'none'; 
        if (match) vis++;
    });
    document.getElementById('po-count').textContent = vis + ' pendiente(s)';
}
function poVerFotos(fotos) {
    var labels = ['Lado derecho', 'Lado izquierdo', 'De frente', 'Parte trasera'];
    var grid = document.getElementById('pof-grid'); grid.innerHTML = '';
    fotos.forEach(function(url, i){
        if (!url) return;
        var div = document.createElement('div');
        var src = String(url).replace(/^\//, '');
        var fullSrc = src.startsWith('http') ? src : '/warranties/' + src;
        div.innerHTML = '<a href="' + fullSrc + '" target="_blank"><img class="pof-img" src="' + fullSrc + '" onerror="this.style.display=\'none\'" loading="lazy"></a><div class="pof-lbl">' + labels[i] + '</div>';
        grid.appendChild(div);
    });
    document.getElementById('pof-overlay').classList.add('open');
}
function pofCerrar(e){ if(e.target===document.getElementById('pof-overlay')) document.getElementById('pof-overlay').classList.remove('open'); }
function poAbrirModal(po) {
    _poActual = po;
    var nroOrden = String(po.nro_preorden || '').replace(/^PRE(OR)?-/i, '');
    document.getElementById('pm-sub').textContent = 'Pre-orden: ' + po.nro_preorden + ' -> Orden: ' + nroOrden;
    var rows = [
        ['Nro. Orden', '<strong style="color:#1d4ed8">' + nroOrden + '</strong>'],
        ['Cliente', (po.nombres || '') + ' ' + (po.apellidos || '')],
        ['Telefono', po.telefono || '-'],
        ['Correo', po.correo || '-'],
        ['Factura', po.nro_factura || '-'],
        ['Fecha Factura', po.fecha_facturacion || '-'],
        ['Codigo', po.codigo_producto || '-'],
        ['Equipo', ((po.tipo_producto || '') + ' ' + (po.marca_producto || '') + ' - ' + (po.desc_producto || '')).trim()],
        ['Problema', po.detalle_equipo || '-'],
        ['Suc. Cliente', po.sucursal_cliente_numero ? String(po.sucursal_cliente_numero).padStart(3, '0') + ' - ' + (po.sucursal_cliente_nombre || '') : '-'],
        ['Procedencia', po.ciudad_procedencia || '-']
    ];
    var html = '';
    rows.forEach(function(r){ html += '<div class="po-resumen-row"><span class="po-rl">' + r[0] + '</span><span class="po-rv">' + r[1] + '</span></div>'; });
    document.getElementById('pm-resumen').innerHTML = html;
    document.getElementById('pm-tecnico').value = '';
    document.getElementById('pm-direccion').value = '';
    document.getElementById('pm-serie').value = '';
    document.getElementById('pm-obs').value = '';
    document.getElementById('pm-fecha-prometido').value = '';
    document.getElementById('pm-msg').style.display = 'none';
    document.getElementById('po-modal-overlay').classList.add('open');
}
function poCerrarModal() { document.getElementById('po-modal-overlay').classList.remove('open'); _poActual = null; }
function pmMsg(tipo, texto) {
    var el = document.getElementById('pm-msg');
    el.className = 'po-modal-msg ' + tipo; el.textContent = texto; el.style.display = 'block';
}
async function poConfirmarIngreso() {
    if (!_poActual) return;
    var tecnico_id = document.getElementById('pm-tecnico').value;
    var direccion = document.getElementById('pm-direccion').value.trim();
    var serie = document.getElementById('pm-serie').value.trim();
    var obs = document.getElementById('pm-obs').value.trim();
    var fechaPrometido = document.getElementById('pm-fecha-prometido').value;
    if (!tecnico_id) { pmMsg('err', 'Selecciona un tecnico.'); return; }
    if (!direccion) { pmMsg('err', 'La direccion es obligatoria.'); return; }
    if (!fechaPrometido) { pmMsg('err', 'La fecha prometido es obligatoria.'); return; }
    if (fechaPrometido <= new Date().toISOString().split('T')[0]) { pmMsg('err', 'La fecha prometido debe ser futura.'); return; }
    var fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('preorden_id', _poActual.id);
    fd.append('tecnico_id', tecnico_id);
    fd.append('direccion', direccion);
    fd.append('serie', serie);
    fd.append('observacion', obs);
    fd.append('fecha_prometido', fechaPrometido);
    var btn = document.getElementById('btn-confirmar');
    btn.disabled = true;
    btn.innerHTML = 'Creando orden...';
    try {
        var r = await fetch('{{ route("preordenes.ingresar") }}', { method: 'POST', body: fd });
        var data = await r.json();
        if (!data.ok) { pmMsg('err', data.error || 'Error al ingresar.'); return; }
        pmMsg('ok', 'Orden ' + data.nro_orden + ' creada correctamente.');
        setTimeout(function(){
            var tbody = document.getElementById('po-tbody');
            if (!tbody) return;
            var filas = tbody.querySelectorAll('tr[data-nro]');
            filas.forEach(function(tr){
                if (_poActual && tr.dataset.nro === String(_poActual.nro_preorden || '').toLowerCase()) tr.remove();
            });
            var restantes = tbody.querySelectorAll('tr[data-nro]').length;
            if (restantes === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="po-empty" style="padding:40px;text-align:center;color:#94a3b8;"><i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>No hay pre-ordenes pendientes.</td></tr>';
            }
            document.getElementById('po-count').textContent = Math.max(restantes, 0) + ' pendiente(s)';
            poCerrarModal();
        }, 1000);
    } catch (e) {
        pmMsg('err', 'Error de conexion.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Crear Orden de Trabajo';
    }
}
function poImprimirPreorden(id) {
    var f = document.createElement('form');
    f.method = 'POST'; f.action = '{{ route("preordenes.reporte") }}'; f.target = '_blank';
    var token = document.createElement('input');
    token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}';
    var inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'preorden_id'; inp.value = id;
    f.appendChild(token); f.appendChild(inp); document.body.appendChild(f); f.submit(); document.body.removeChild(f);
}
document.getElementById('po-modal-overlay').addEventListener('click', function(e){ if (e.target === this) poCerrarModal(); });

var _poPager = null;
document.addEventListener('DOMContentLoaded', function() {
    _poPager = new SgnPager({
        containerSelector: '#po-tbody',
        itemSelector: 'tr[data-nro]',
        pagerContainerSelector: '#po-pager',
        pageSize: 15
    });
});
</script>
@endpush
