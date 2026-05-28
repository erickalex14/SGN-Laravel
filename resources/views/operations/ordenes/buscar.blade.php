@extends('layouts.app')
@section('titulo', 'Buscar Ordenes')

@push('css_adicional')
<style>
.bo-wrap { max-width: 980px; margin: 0 auto; padding: 26px 22px; }
.bo-head h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; }
.bo-head p { margin: 0 0 18px; color: #64748b; font-size: 13px; }
.bo-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
.bo-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.bo-tab { border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; padding: 8px 12px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
.bo-tab.activo { background: #2563eb; border-color: #2563eb; color: #fff; }
.bo-row { display: flex; gap: 8px; align-items: center; }
.bo-input { flex: 1; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
.bo-btn { border: 0; border-radius: 8px; padding: 10px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
.bo-btn.buscar { background: #1d4ed8; color: #fff; }
.bo-btn.limpiar { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
.bo-msg { margin-top: 10px; display: none; border-radius: 8px; padding: 10px 12px; font-size: 13px; font-weight: 600; }
.bo-msg.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.bo-msg.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.bo-res-count { color: #475569; font-size: 13px; font-weight: 700; margin-bottom: 10px; }
.bo-item { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; margin-bottom: 8px; cursor: pointer; background: #fff; }
.bo-item:hover { border-color: #3b82f6; background: #f8fafc; }
.bo-item-top { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
.bo-nro { font-weight: 800; color: #0f172a; }
.bo-badges { display: flex; gap: 6px; flex-wrap: wrap; }
.bo-badge { border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 700; }
.st-pend { background: #fef9c3; color: #854d0e; }
.st-proc { background: #dbeafe; color: #1e40af; }
.st-ok { background: #dcfce7; color: #166534; }
.st-ent { background: #ecfdf5; color: #047857; }
.st-nc { background: #fce7f3; color: #9d174d; }
.st-rep { background: #f1f5f9; color: #334155; }
.bo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px; }
.bo-k { font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700; }
.bo-v { font-size: 13px; color: #1e293b; font-weight: 600; }
.bo-back { margin-bottom: 10px; }
.bo-sec { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 10px; background: #fff; }
.bo-sec-h { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 12px; font-weight: 800; color: #1e293b; font-size: 13px; }
.bo-sec-b { padding: 12px; }
.bo-line { margin-bottom: 8px; }
.bo-line:last-child { margin-bottom: 0; }
.bo-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
.bo-print { border: 0; border-radius: 8px; padding: 9px 12px; font-size: 12px; font-weight: 800; cursor: pointer; color: #fff; }
.bo-print.ot { background: #0f172a; }
.bo-print.inf { background: #1d4ed8; }
.bo-print.edit { background: #16a34a; }
@media (max-width: 700px) { .bo-grid { grid-template-columns: 1fr; } .bo-row { flex-wrap: wrap; } }
</style>
@endpush

@section('contenido')
<div class="bo-wrap">
    <div class="bo-head">
        <h2><i class="bi bi-search"></i> Buscar Ordenes</h2>
        <p>Busqueda por numero de orden, cedula, nombre, serie o factura.</p>
    </div>

    <div class="bo-card">
        <div class="bo-tabs" id="bo-tabs">
            <button class="bo-tab activo" data-tipo="nro_orden">Nro. Orden</button>
            <button class="bo-tab" data-tipo="cedula">Cedula / RUC</button>
            <button class="bo-tab" data-tipo="nombre">Nombre</button>
            <button class="bo-tab" data-tipo="serie">Serie</button>
            <button class="bo-tab" data-tipo="factura">Factura</button>
        </div>
        <div class="bo-row">
            <input id="bo-q" class="bo-input" placeholder="Ej: UIO-000001" autocomplete="off">
            <button id="bo-btn-buscar" class="bo-btn buscar">Buscar</button>
            <button id="bo-btn-limpiar" class="bo-btn limpiar">Limpiar</button>
        </div>
        <div id="bo-msg" class="bo-msg err"></div>
    </div>

    <div id="bo-res" style="display:none;">
        <div id="bo-res-count" class="bo-res-count"></div>
        <div id="bo-list"></div>
    </div>

    <div id="bo-det" style="display:none;">
        <button class="bo-btn limpiar bo-back" id="bo-back">Volver a resultados</button>
        <div id="bo-det-content"></div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
(() => {
    const urlBuscar = '{{ route("ordenes_buscar.listar") }}';
    const urlImprimirOrdenBase = '/operaciones/ordenes/';
    const urlImprimirOrdenEmpresaBase = '/operaciones/ordenes-empresa/';
    const urlImprimirInformeBase = '/operaciones/informes/';
    const urlEditarBase = '/operaciones/ordenes/editar/';

    const tabs = document.getElementById('bo-tabs');
    const input = document.getElementById('bo-q');
    const btnBuscar = document.getElementById('bo-btn-buscar');
    const btnLimpiar = document.getElementById('bo-btn-limpiar');
    const msg = document.getElementById('bo-msg');
    const resWrap = document.getElementById('bo-res');
    const resCount = document.getElementById('bo-res-count');
    const list = document.getElementById('bo-list');
    const detWrap = document.getElementById('bo-det');
    const detContent = document.getElementById('bo-det-content');
    const btnBack = document.getElementById('bo-back');

    let tipo = 'nro_orden';
    let resultados = [];

    const placeholders = {
        nro_orden: 'Ej: UIO-000001 o numero consecutivo',
        cedula: 'Ej: 1712345678',
        nombre: 'Ej: Juan Perez',
        serie: 'Ej: SN123456',
        factura: 'Ej: 001-001-000000123',
    };

    function showMsg(texto, error = true) {
        msg.className = 'bo-msg ' + (error ? 'err' : 'ok');
        msg.textContent = texto;
        msg.style.display = 'block';
    }

    function clearMsg() {
        msg.style.display = 'none';
        msg.textContent = '';
    }

    function estadoClase(v) {
        const t = (v || '').toLowerCase();
        if (t === 'pendiente' || t === 'abierta') return 'st-pend';
        if (t === 'en proceso') return 'st-proc';
        if (t === 'finalizada') return 'st-ok';
        if (t === 'entregada') return 'st-ent';
        if (t === 'nota de credito') return 'st-nc';
        return 'st-rep';
    }

    function badge(html) {
        return '<span class="bo-badge ' + html.cls + '">' + html.lbl + '</span>';
    }

    function badgesOrden(o) {
        const out = [];
        out.push(badge({ cls: estadoClase(o.estado_orden), lbl: o.estado_orden || '-' }));
        if (o.estado_repuesto && o.estado_repuesto !== 'No requerido') {
            out.push(badge({ cls: 'st-rep', lbl: o.estado_repuesto }));
        }
        return out.join('');
    }

    function renderResultados(items) {
        resultados = items;
        resCount.textContent = items.length + ' orden(es) encontrada(s)';
        list.innerHTML = '';
        items.forEach((o) => {
            const div = document.createElement('div');
            div.className = 'bo-item';
            div.innerHTML =
                '<div class="bo-item-top">' +
                    '<div class="bo-nro">' + (o.nro_orden || '-') + '</div>' +
                    '<div class="bo-badges">' + badgesOrden(o) + '</div>' +
                '</div>' +
                '<div class="bo-grid">' +
                    '<div><div class="bo-k">Cliente</div><div class="bo-v">' + (o.cliente || '-') + '</div></div>' +
                    '<div><div class="bo-k">Tecnico</div><div class="bo-v">' + (o.tecnico || '-') + '</div></div>' +
                    '<div><div class="bo-k">Equipo</div><div class="bo-v">' + [o.tipo, o.marca, o.modelo].filter(Boolean).join(' ') + '</div></div>' +
                    '<div><div class="bo-k">Ingreso</div><div class="bo-v">' + (o.fecha_de_ingreso || '-') + '</div></div>' +
                '</div>';
            div.addEventListener('click', () => renderDetalle(o));
            list.appendChild(div);
        });
        resWrap.style.display = 'block';
        detWrap.style.display = 'none';
    }

    function renderDetalle(o) {
        const esEmpresa = o.tipo_orden === 'empresa';
        const facturas = esEmpresa
            ? (o.nro_factura || '-')
            : ([o.nro_factura, o.nro_factura_2].filter(Boolean).join(' / ') || '-');
        const clienteNombre = [o.nombres, o.apellidos].filter(Boolean).join(' ') || o.cliente || '-';
        const btnInforme = o.informe_id
            ? '<button class="bo-print inf" id="btn-inf">Imprimir Informe</button>'
            : '';
        const btnEditar = o.tipo_orden === 'personal'
            ? '<button class="bo-print edit" id="btn-edit">Editar Orden</button>'
            : '';

        detContent.innerHTML =
            '<div class="bo-sec"><div class="bo-sec-h">Resumen de Orden</div><div class="bo-sec-b">' +
                '<div class="bo-line"><strong>' + (o.nro_orden || '-') + '</strong> ' + badgesOrden(o) + '</div>' +
                '<div class="bo-grid">' +
                    '<div><div class="bo-k">Tipo</div><div class="bo-v">' + (o.tipo_orden || '-') + '</div></div>' +
                    '<div><div class="bo-k">Ingreso</div><div class="bo-v">' + (o.fecha_de_ingreso || '-') + '</div></div>' +
                    '<div><div class="bo-k">Motivo</div><div class="bo-v">' + (o.motivo_ingreso || '-') + '</div></div>' +
                    '<div><div class="bo-k">' + (esEmpresa ? 'Nro. Ticket' : 'Factura') + '</div><div class="bo-v">' + facturas + '</div></div>' +
                    '<div><div class="bo-k">Tecnico</div><div class="bo-v">' + (o.tecnico || '-') + '</div></div>' +
                    '<div><div class="bo-k">Sucursal</div><div class="bo-v">' + (o.sucursal || '-') + '</div></div>' +
                '</div>' +
            '</div></div>' +

            '<div class="bo-sec"><div class="bo-sec-h">Cliente</div><div class="bo-sec-b">' +
                '<div class="bo-grid">' +
                    '<div><div class="bo-k">Nombre</div><div class="bo-v">' + clienteNombre + '</div></div>' +
                    '<div><div class="bo-k">Identificacion</div><div class="bo-v">' + (o.identificacion || '-') + '</div></div>' +
                    '<div><div class="bo-k">Telefono</div><div class="bo-v">' + (o.numero_contacto || '-') + '</div></div>' +
                    '<div><div class="bo-k">Correo</div><div class="bo-v">' + (o.correo || '-') + '</div></div>' +
                '</div>' +
            '</div></div>' +

            '<div class="bo-sec"><div class="bo-sec-h">Equipo</div><div class="bo-sec-b">' +
                '<div class="bo-grid">' +
                    '<div><div class="bo-k">Tipo</div><div class="bo-v">' + (o.tipo || '-') + '</div></div>' +
                    '<div><div class="bo-k">Marca</div><div class="bo-v">' + (o.marca || '-') + '</div></div>' +
                    '<div><div class="bo-k">Modelo</div><div class="bo-v">' + (o.modelo || '-') + '</div></div>' +
                    '<div><div class="bo-k">Serie</div><div class="bo-v">' + (o.serie || '-') + '</div></div>' +
                '</div>' +
                '<div class="bo-line" style="margin-top:8px;"><div class="bo-k">Falla</div><div class="bo-v">' + (o.falla || '-') + '</div></div>' +
                '<div class="bo-line"><div class="bo-k">Observacion</div><div class="bo-v">' + (o.observacion || '-') + '</div></div>' +
            '</div></div>' +

            '<div class="bo-sec"><div class="bo-sec-h">Informe Tecnico</div><div class="bo-sec-b">' +
                (o.antecedentes
                    ? '<div class="bo-line"><div class="bo-k">Fecha Informe</div><div class="bo-v">' + (o.fecha_informe || '-') + '</div></div>' +
                      '<div class="bo-line"><div class="bo-k">Estado Equipo</div><div class="bo-v">' + (o.estado_equipo || '-') + '</div></div>' +
                      '<div class="bo-line"><div class="bo-k">Antecedentes</div><div class="bo-v">' + o.antecedentes + '</div></div>' +
                      '<div class="bo-line"><div class="bo-k">Proceso</div><div class="bo-v">' + (o.proceso || '-') + '</div></div>'
                    : '<div class="bo-v">Esta orden aun no tiene informe tecnico registrado.</div>') +
                '<div class="bo-actions">' +
                    '<button class="bo-print ot" id="btn-ot">Imprimir OT</button>' +
                    btnInforme +
                    btnEditar +
                '</div>' +
            '</div></div>';

        document.getElementById('btn-ot').onclick = () => {
            const base = esEmpresa ? urlImprimirOrdenEmpresaBase : urlImprimirOrdenBase;
            window.open(base + o.orden_id + '/imprimir', '_blank');
        };
        if (o.informe_id) {
            document.getElementById('btn-inf').onclick = () => window.open(urlImprimirInformeBase + o.informe_id + '/imprimir', '_blank');
        }
        if (o.tipo_orden === 'personal') {
            document.getElementById('btn-edit').onclick = () => window.location.href = urlEditarBase + o.orden_id;
        }

        resWrap.style.display = 'none';
        detWrap.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function buscar() {
        const q = (input.value || '').trim();
        if (!q) {
            showMsg('Ingresa un valor para buscar.');
            return;
        }

        clearMsg();
        btnBuscar.disabled = true;
        btnBuscar.textContent = 'Buscando...';
        resWrap.style.display = 'none';
        detWrap.style.display = 'none';

        try {
            const url = urlBuscar + '?tipo=' + encodeURIComponent(tipo) + '&q=' + encodeURIComponent(q);
            const r = await fetch(url, { cache: 'no-store' });
            const d = await r.json();
            if (!d.ok) {
                showMsg(d.error || 'No se encontraron resultados.');
                return;
            }
            renderResultados(d.ordenes || []);
            showMsg('Busqueda completada.', false);
        } catch (e) {
            showMsg('Error de conexion al buscar.');
        } finally {
            btnBuscar.disabled = false;
            btnBuscar.textContent = 'Buscar';
        }
    }

    tabs.addEventListener('click', (e) => {
        const t = e.target.closest('.bo-tab');
        if (!t) return;
        document.querySelectorAll('.bo-tab').forEach(el => el.classList.remove('activo'));
        t.classList.add('activo');
        tipo = t.dataset.tipo;
        input.placeholder = placeholders[tipo] || '';
        input.value = '';
        input.focus();
        clearMsg();
        resWrap.style.display = 'none';
        detWrap.style.display = 'none';
    });

    btnBuscar.addEventListener('click', buscar);
    btnLimpiar.addEventListener('click', () => {
        input.value = '';
        clearMsg();
        resWrap.style.display = 'none';
        detWrap.style.display = 'none';
        resultados = [];
        input.focus();
    });
    btnBack.addEventListener('click', () => {
        if (resultados.length > 0) {
            renderResultados(resultados);
        } else {
            detWrap.style.display = 'none';
        }
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscar();
        }
    });
})();
</script>
@endpush
