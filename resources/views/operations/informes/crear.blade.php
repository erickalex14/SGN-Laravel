@extends('layouts.app')
@section('titulo', 'Crear Informe Técnico')

@push('css_adicional')
<style>
/*
   CREAR INFORME TÉCNICO
 */
.ci-wrap { max-width: 960px; margin: 0 auto; padding: 26px 20px; }

/* Header */
.ci-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.ci-hdr h2 { font-size:21px; font-weight:800; color:#0f172a; margin:0; display:flex; align-items:center; gap:10px; }
.ci-hdr p  { color:#64748b; font-size:13px; margin:4px 0 0; }
.ci-btn-mis {
    display:inline-flex; align-items:center; gap:8px;
    background:#f1f5f9; color:#334155; border:1.5px solid #e2e8f0;
    border-radius:10px; padding:9px 18px; font-size:13px; font-weight:700;
    text-decoration:none; transition:background .15s;
}
.ci-btn-mis:hover { background:#e2e8f0; color:#0f172a; }

/* Tarjeta generica */
.ci-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:16px;
    box-shadow:0 2px 10px rgba(0,0,0,.04); margin-bottom:18px;
    overflow:hidden; animation:fadeUp .22s ease;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
.ci-card-hd {
    display:flex; align-items:center; gap:12px;
    padding:14px 20px; background:linear-gradient(135deg,#eff6ff,#dbeafe);
    border-bottom:1px solid #bfdbfe;
}
.ci-card-hd h3 { font-size:14px; font-weight:700; color:#1e40af; margin:0; }
.ci-step {
    width:26px; height:26px; border-radius:50%; background:#2563eb;
    color:#fff; font-size:13px; font-weight:800;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ci-card-body { padding:20px; }

/* Busqueda */
.ci-tipos { display:flex; flex-wrap:wrap; gap:7px; margin-bottom:14px; }
.ci-tipo {
    padding:7px 14px; border-radius:8px; border:1.5px solid #e2e8f0;
    background:#f8fafc; color:#475569; font-size:12.5px; font-weight:700;
    cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:5px;
}
.ci-tipo:hover { border-color:#93c5fd; background:#eff6ff; color:#1d4ed8; }
.ci-tipo.activo { background:#2563eb; border-color:#2563eb; color:#fff; }

.ci-search-row { display:flex; gap:10px; }
.ci-input-wrap { flex:1; position:relative; }
.ci-input-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none; }
.ci-input {
    width:100%; padding:11px 12px 11px 38px; border:1.5px solid #e2e8f0;
    border-radius:10px; font-size:14px; color:#0f172a; background:#f8fafc;
    transition:border-color .2s, box-shadow .2s; font-family:inherit; box-sizing:border-box;
}
.ci-input:focus { outline:none; border-color:#2563eb; background:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.ci-btn-buscar {
    background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff;
    border:none; border-radius:10px; padding:11px 20px; font-size:14px;
    font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px;
    transition:opacity .2s, transform .1s; white-space:nowrap;
}
.ci-btn-buscar:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
.ci-btn-buscar:disabled { opacity:.55; cursor:not-allowed; }

/* Resultados */
.ci-resultados { margin-top:14px; display:flex; flex-direction:column; gap:8px; }
.ci-result-card {
    border:1.5px solid #e2e8f0; border-radius:10px; padding:13px 16px;
    cursor:pointer; transition:all .15s; background:#fafafa;
    display:flex; align-items:center; justify-content:space-between; gap:12px;
}
.ci-result-card:hover { border-color:#2563eb; background:#eff6ff; }
.ci-result-card.tiene { border-color:#10b981; background:#f0fdf4; }
.ci-result-left { display:flex; flex-direction:column; gap:3px; min-width:0; }
.ci-result-nro { font-family:monospace; font-weight:800; font-size:14px; color:#0f172a; }
.ci-result-sub { font-size:12.5px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ci-result-right { display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0; }
.ci-pill { font-size:10.5px; font-weight:700; padding:2px 9px; border-radius:20px; }
.ci-pill-pers { background:#f1f5f9; color:#475569; }
.ci-pill-emp  { background:#ede9fe; color:#6d28d9; }
.ci-pill-tiene { background:#d1fae5; color:#065f46; }
.ci-empty { padding:20px; text-align:center; color:#94a3b8; font-size:13px; }

/* Mensaje de estado */
.ci-msg { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:10px; font-size:13.5px; font-weight:600; margin-top:14px; animation:fadeUp .2s ease; }
.ci-msg-ok  { background:#ecfdf5; color:#065f46; border:1px solid #6ee7b7; }
.ci-msg-err { background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; }
.ci-msg-warn { background:#fef9c3; color:#78350f; border:1px solid #fde68a; }

/* Resumen de orden */
.ci-resumen {
    display:grid; grid-template-columns:repeat(4,1fr); gap:12px;
    background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px;
    padding:13px 16px; margin-bottom:16px; animation:fadeUp .2s ease;
}
.ci-res-lbl { font-size:10px; font-weight:700; color:#0369a1; text-transform:uppercase; letter-spacing:.04em; display:block; margin-bottom:2px; }
.ci-res-val { font-size:13px; font-weight:600; color:#0f172a; }

/* Campos */
.campo { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
.campo label { font-size:13px; font-weight:600; color:#374151; }
.campo select, .campo input[type="text"], .campo input[type="date"], .campo textarea {
    border:1.5px solid #e2e8f0; border-radius:8px; padding:9px 12px;
    font-size:13.5px; color:#0f172a; background:#f8fafc;
    transition:border-color .2s, box-shadow .2s; font-family:inherit; resize:vertical;
}
.campo select:focus, .campo input:focus, .campo textarea:focus {
    outline:none; border-color:#2563eb; background:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
.campo select:disabled, .campo input:disabled, .campo textarea:disabled {
    background:#f1f5f9; color:#94a3b8; cursor:not-allowed;
}
.req { color:#ef4444; }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

/* Upload fotos */
.upload-zone {
    border:2px dashed #cbd5e1; border-radius:12px; padding:28px;
    text-align:center; cursor:pointer; transition:all .2s;
    color:#94a3b8; background:#f8fafc;
}
.upload-zone:hover, .upload-zone.drag-over { border-color:#2563eb; background:#eff6ff; color:#2563eb; }
.upload-zone.disabled { opacity:.5; pointer-events:none; }
.upload-zone i { font-size:30px; display:block; margin-bottom:7px; }
.upload-zone p { font-size:13px; font-weight:600; margin:0 0 3px; }
.upload-zone small { font-size:12px; }

.fotos-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; margin-top:14px; }
.foto-item { position:relative; border-radius:10px; overflow:hidden; border:1.5px solid #e2e8f0; }
.foto-item img { width:100%; height:120px; object-fit:cover; display:block; }
.foto-item input { width:100%; border:none; border-top:1px solid #e2e8f0; padding:5px 8px; font-size:11px; background:#fff; box-sizing:border-box; }
.foto-item input:focus { outline:none; background:#eff6ff; }
.foto-del { position:absolute; top:5px; right:5px; background:rgba(239,68,68,.85); color:#fff; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:10px; }
.foto-del:hover { background:#dc2626; }
.foto-badge { position:absolute; top:5px; left:5px; background:rgba(37,99,235,.85); color:#fff; border-radius:4px; padding:2px 5px; font-size:9px; font-weight:700; pointer-events:none; }

/* Botones accion */
.ci-botones { display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap; margin-top:8px; }
.ci-btn {
    display:inline-flex; align-items:center; gap:7px;
    padding:10px 22px; border-radius:10px; font-size:13.5px; font-weight:700;
    border:none; cursor:pointer; transition:opacity .2s, transform .1s;
}
.ci-btn:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
.ci-btn:disabled { opacity:.45; cursor:not-allowed; transform:none; }
.ci-btn-guardar  { background:linear-gradient(135deg,#10b981,#059669); color:#fff; box-shadow:0 3px 10px rgba(16,185,129,.3); }
.ci-btn-preview  { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; }
.ci-btn-imprimir { background:#0f172a; color:#fff; }
.ci-btn-limpiar  { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }

/* Spinner */
.spin { display:inline-block; width:15px; height:15px; border:2.5px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to{transform:rotate(360deg)} }

@media (max-width:768px) {
    .ci-resumen { grid-template-columns:1fr 1fr; }
    .grid-2 { grid-template-columns:1fr; }
    .ci-botones { justify-content:stretch; }
    .ci-btn { flex:1; justify-content:center; }
    .ci-search-row { flex-wrap:wrap; }
    .ci-btn-buscar { flex:1; justify-content:center; }
}
@media (max-width:480px) {
    .ci-resumen { grid-template-columns:1fr; }
    .ci-wrap { padding:14px 12px; }
}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="ci-wrap">

    {{-- Header --}}
    <div class="ci-hdr">
        <div>
            <h2><i class="bi bi-file-earmark-plus" style="color:#2563eb;"></i>Crear / Editar Informe</h2>
            <p>{{ !empty($modoEdicionAdmin) ? 'Edita el informe tecnico seleccionado.' : 'Busca la orden de servicio y redacta el informe tecnico.' }}</p>
        </div>
        <a href="{{ route('informes.mis') }}" class="ci-btn-mis">
            <i class="bi bi-journal-text"></i>Mis Informes
        </a>
    </div>

    {{-- Mensaje global --}}
    <div id="ci-msg-global" style="display:none;" class="ci-msg"></div>

    {{-- PASO 1: Buscar orden  --}}
    <div class="ci-card" id="paso-buscar">
        <div class="ci-card-hd">
            <span class="ci-step">1</span>
            <h3>Buscar Orden de Servicio</h3>
        </div>
        <div class="ci-card-body">
            {{-- Tipos de busqueda --}}
            <div class="ci-tipos" id="ci-tipos">
                <button class="ci-tipo activo" data-tipo="nro_orden"><i class="bi bi-hash"></i>Nro. Orden</button>
                <button class="ci-tipo" data-tipo="nombre"><i class="bi bi-person"></i>Nombre</button>
                <button class="ci-tipo" data-tipo="cedula"><i class="bi bi-card-text"></i>Cedula / RUC</button>
                <button class="ci-tipo" data-tipo="factura"><i class="bi bi-receipt"></i>Factura / Ticket</button>
                <button class="ci-tipo" data-tipo="empresa"><i class="bi bi-building"></i>Empresa</button>
            </div>
            {{-- Input --}}
            <div class="ci-search-row">
                <div class="ci-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="ci-q" class="ci-input" placeholder="Ej: UIO-000001" autocomplete="off">
                </div>
                <button id="ci-btn-buscar" class="ci-btn-buscar">
                    <i class="bi bi-search"></i><span>Buscar</span>
                </button>
            </div>
            {{-- Resultados --}}
            <div id="ci-resultados" class="ci-resultados" style="display:none;"></div>
        </div>
    </div>

    {{-- PASOS 2 y 3: Formulario (oculto hasta elegir orden)  --}}
    <div id="ci-formulario" style="display:none;">

        {{-- Barra de orden seleccionada --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <button class="ci-btn ci-btn-limpiar" onclick="_ciLimpiar()" style="padding:8px 14px;font-size:12.5px;">
                <i class="bi bi-arrow-left"></i>Cambiar orden
            </button>
            <div id="ci-titulo-orden" style="font-size:15px;font-weight:800;color:#0f172a;font-family:monospace;"></div>
        </div>

        {{-- Resumen --}}
        <div class="ci-resumen" id="ci-resumen">
            <div><span class="ci-res-lbl">Nro. Orden</span><span id="ci-res-nro" class="ci-res-val"></span></div>
            <div><span class="ci-res-lbl">Cliente</span><span id="ci-res-cliente" class="ci-res-val"></span></div>
            <div><span class="ci-res-lbl">Equipo</span><span id="ci-res-equipo" class="ci-res-val"></span></div>
            <div><span class="ci-res-lbl">Estado</span><span id="ci-res-estado" class="ci-res-val"></span></div>
        </div>

        {{-- Alerta (bloqueo / info) --}}
        <div id="ci-alerta" style="display:none;" class="ci-msg ci-msg-warn" role="alert"></div>

        {{-- Paso 2: Redactar --}}
        <div class="ci-card">
            <div class="ci-card-hd">
                <span class="ci-step">2</span>
                <h3>Redactar Informe</h3>
            </div>
            <div class="ci-card-body">
                <div class="grid-2">
                    <div class="campo">
                        <label for="inf-antecedentes">Antecedentes <span class="req">*</span></label>
                        <textarea id="inf-antecedentes" rows="4" placeholder="Describe los antecedentes del caso..."></textarea>
                    </div>
                    <div class="campo">
                        <label for="inf-proceso">Proceso <span class="req">*</span></label>
                        <textarea id="inf-proceso" rows="4" placeholder="Detalla el proceso de diagnóstico o reparación..."></textarea>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label for="inf-conclusion">Conclusion</label>
                        <textarea id="inf-conclusion" rows="3" placeholder="Conclusion del informe..."></textarea>
                    </div>
                    <div class="campo">
                        <label for="inf-recomendaciones">Recomendaciones <small style="font-weight:400;color:#94a3b8;">(opcional)</small></label>
                        <textarea id="inf-recomendaciones" rows="3" placeholder="Recomendaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="campo">
                        <label for="inf-estado-equipo">Estado Final del Equipo <span class="req">*</span></label>
                        <select id="inf-estado-equipo">
                            <option value="Operativo">Operativo</option>
                            <option value="Reparado parcialmente">Reparado parcialmente</option>
                            <option value="Sin reparación posible">Sin reparación posible</option>
                            <option value="Desguace">Desguace</option>
                            <option value="En espera de repuesto">En espera de repuesto</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="inf-fecha">Fecha del Informe</label>
                        <input type="date" id="inf-fecha" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Paso 3: Fotos --}}
        <div class="ci-card">
            <div class="ci-card-hd">
                <span class="ci-step">3</span>
                <h3>Evidencia Fotográfica <small style="font-weight:400;font-size:12px;color:#64748b;">(opcional)</small></h3>
            </div>
            <div class="ci-card-body">
                <div id="upload-zone" class="upload-zone"
                    onclick="document.getElementById('inp-fotos').click()"
                    ondragover="event.preventDefault();this.classList.add('drag-over')"
                    ondragleave="this.classList.remove('drag-over')"
                    ondrop="_soltarFotos(event)">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Haz clic o arrastra fotos aqui</p>
                    <small>JPG, PNG, WEBP — Máximo 10 fotos</small>
                </div>
                <input type="file" id="inp-fotos" accept="image/*" multiple style="display:none"
                       onchange="_agregarFotos(this.files)">
                <div id="fotos-grid" class="fotos-grid"></div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="ci-msg" id="ci-msg-form" style="display:none;"></div>
        <div class="ci-botones">
            <button class="ci-btn ci-btn-guardar" id="btn-guardar" onclick="_guardar()">
                <i class="bi bi-floppy"></i>Guardar Informe
            </button>
            <button class="ci-btn ci-btn-preview" onclick="_previsualizar(false)">
                <i class="bi bi-file-earmark-pdf"></i>Previsualizar
            </button>
            <button class="ci-btn ci-btn-imprimir" onclick="_previsualizar(true)">
                <i class="bi bi-printer"></i>Imprimir
            </button>
            <button class="ci-btn ci-btn-limpiar" onclick="_ciLimpiar()">
                <i class="bi bi-x-circle"></i>Limpiar
            </button>
        </div>
    </div>

</div>
</section>
@endsection

@push('js_adicional')
<script>
window.onerror = function(msg, url, line, col, error) {
    var errDiv = document.createElement('div');
    errDiv.style.cssText = 'position:fixed;top:0;left:0;width:100%;background:#ef4444;color:white;z-index:999999;padding:20px;font-size:16px;font-weight:bold;font-family:monospace;border-bottom:4px solid #b91c1c;';
    errDiv.innerHTML = 'JS ERROR: ' + msg + '<br>Line: ' + line + '<br>Col: ' + col;
    document.body.prepend(errDiv);
};
</script>
<script>
(function () {
    'use strict';

    /*  Config  */
    var URL_BUSCAR   = @json(!empty($modoEdicionAdmin) ? route('informes.editar.buscar') : route('informes.crear.buscar'));
    var URL_VER      = @json(route('informes.ver'));
    var URL_GUARDAR  = @json(route('informes.store'));
    var URL_ACTUALIZAR_BASE = @json(url('/operaciones/informes'));
    var URL_IMPRIMIR = @json(url('/operaciones/informes'));
    var CSRF         = @json(csrf_token());
    // Se añade soporte para evitar el error si el controlador no envía la variable, usando como respaldo el nombre del usuario autenticado
    var NOM_TEC      = @json((string)($nombreTécnico ?? auth()->user()->name ?? ''));
    var LOGO_URL     = @json(asset('Novitecpdf.png'));
    var FECHA_HOY    = @json(date('Y-m-d'));
    var ORDEN_ID_PRECARGADO = @json((int)$ordenIdPrecargado);
    var INFORME_ID_EDICION = @json((int)($informeIdEdicion ?? 0));
    var MODO_EDICION_ADMIN = @json((bool)($modoEdicionAdmin ?? false));

    /* Estado  */
    var _orden    = null;
    var _fotos    = [];
    var _fotasOrig = 0;
    var _tipo     = 'nro_orden';
    var _timer    = null;
    var _bloqueado = false;
    var ESTADOS_BLOQUEO = ['nota de credito','finalizado','finalizada','entregada'];
    var CAMPOS = ['inf-antecedentes','inf-proceso','inf-conclusion','inf-recomendaciones','inf-estado-equipo','inf-fecha'];

    /* DOM  */
    var elQ       = document.getElementById('ci-q');
    var elBtn     = document.getElementById('ci-btn-buscar');
    var elRes     = document.getElementById('ci-resultados');
    var elForm    = document.getElementById('ci-formulario');
    var elBuscar  = document.getElementById('paso-buscar');
    var elBtnG    = document.getElementById('btn-guardar');
    var elMsgG    = document.getElementById('ci-msg-global');
    var elMsgF    = document.getElementById('ci-msg-form');
    var elAlerta  = document.getElementById('ci-alerta');

    /*  Tipos de busqueda  */
    var PLACEHOLDERS = {
        nro_orden: 'Ej: UIO-000001',
        nombre:    'Ej: Juan Perez',
        cedula:    'Ej: 1712345678',
        factura:   'Ej: FAC-0001',
        empresa:   'Ej: TechCorp',
    };

    document.getElementById('ci-tipos').addEventListener('click', function (e) {
        var btn = e.target.closest('.ci-tipo');
        if (!btn) return;
        document.querySelectorAll('.ci-tipo').forEach(function (b) { b.classList.remove('activo'); });
        btn.classList.add('activo');
        _tipo = btn.dataset.tipo;
        elQ.placeholder = PLACEHOLDERS[_tipo] || '';
        elQ.value = '';
        elRes.style.display = 'none';
        elRes.innerHTML = '';
        elQ.focus();
    });

    /*  Buscar  */
    elBtn.addEventListener('click', _buscar);
    elQ.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); _buscar(); } });

    function _buscar() {
        var q = elQ.value.trim();
        if (q.length < 2) { _msgGlobal('err', 'Escribe al menos 2 caracteres.'); return; }
        _msgGlobalOcultar();
        _buscarAjax(q, _tipo);
    }

    function _buscarAjax(q, tipo) {
        elBtn.disabled = true;
        elBtn.innerHTML = '<span class="spin"></span><span>Buscando...</span>';
        elRes.style.display = 'none';
        elRes.innerHTML = '';

        fetch(URL_BUSCAR + '?q=' + encodeURIComponent(q) + '&tipo=' + encodeURIComponent(tipo), { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                elBtn.disabled  = false;
                elBtn.innerHTML = '<i class="bi bi-search"></i><span>Buscar</span>';
                if (!data.ok) {
                    elRes.innerHTML = '<div class="ci-empty"><i class="bi bi-inbox" style="font-size:26px;display:block;margin-bottom:6px;"></i>' + _esc(data.error) + '</div>';
                    elRes.style.display = 'flex';
                    return;
                }
                if (tipo === 'id' && (data.ordenes || []).length === 1) {
                    _seleccionarOrden(data.ordenes[0]);
                    return;
                }
                _renderResultados(data.ordenes || []);
            })
            .catch(function () {
                elBtn.disabled  = false;
                elBtn.innerHTML = '<i class="bi bi-search"></i><span>Buscar</span>';
                _msgGlobal('err', 'Error de conexion. Intenta de nuevo.');
            });
    }

    function _renderResultados(items) {
        elRes.innerHTML = '';
        if (!items.length) {
            elRes.innerHTML = '<div class="ci-empty">No se encontraron órdenes.</div>';
            elRes.style.display = 'flex';
            return;
        }
        items.forEach(function (o) {
            var card = document.createElement('div');
            card.className = 'ci-result-card' + (o.tiene_informe ? ' tiene' : '');
            card.innerHTML =
                '<div class="ci-result-left">' +
                    '<span class="ci-result-nro">' + _esc(o.nro_orden) + '</span>' +
                    '<span class="ci-result-sub">' + _esc(o.cliente_nombre || '—') + ' · ' + _esc(o.equipo_nombre || '—') + '</span>' +
                '</div>' +
                '<div class="ci-result-right">' +
                    '<span class="ci-pill ' + (o.tipo_orden === 'empresa' ? 'ci-pill-emp' : 'ci-pill-pers') + '">' +
                        (o.tipo_orden === 'empresa' ? 'Empresa' : 'Personal') +
                    '</span>' +
                    (o.tiene_informe ? '<span class="ci-pill ci-pill-tiene"><i class="bi bi-check-circle"></i> Con informe</span>' : '') +
                    '<span style="font-size:11px;color:#94a3b8;">' + _esc(o.estado_orden || '') + '</span>' +
                '</div>';
            card.addEventListener('click', function () { _seleccionarOrden(o); });
            elRes.appendChild(card);
        });
        elRes.style.display = 'flex';
    }

    /*  Seleccionar orden  */
    function _debeBloquearEstado(estadoOrden) {
        var estado = String(estadoOrden || '').toLowerCase().trim();
        if (MODO_EDICION_ADMIN && estado === 'nota de credito') {
            return false;
        }
        return ESTADOS_BLOQUEO.indexOf(estado) !== -1;
    }

    function _seleccionarOrden(o) {
        _orden     = o;
        _bloqueado = _debeBloquearEstado(o.estado_orden);

        // UI
        document.getElementById('ci-titulo-orden').textContent = o.nro_orden;
        document.getElementById('ci-res-nro').textContent      = o.nro_orden;
        document.getElementById('ci-res-cliente').textContent  = o.cliente_nombre || '—';
        document.getElementById('ci-res-equipo').textContent   = o.equipo_nombre  || '—';
        document.getElementById('ci-res-estado').textContent   = o.estado_orden   || '—';

        // Bloquear campos si la orden esta cerrada
        CAMPOS.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.disabled = _bloqueado;
        });
        var zone = document.getElementById('upload-zone');
        var inp  = document.getElementById('inp-fotos');
        if (zone) zone.classList.toggle('disabled', _bloqueado);
        if (inp)  inp.disabled = _bloqueado;
        if (elBtnG) elBtnG.disabled = _bloqueado;

        if (_bloqueado) {
            elAlerta.className = 'ci-msg ci-msg-warn';
            elAlerta.innerHTML = '<i class="bi bi-lock"></i>Informe de solo lectura, la orden esta en estado <strong>' + _esc(o.estado_orden) + '</strong>.';
            elAlerta.style.display = 'flex';
        } else {
            elAlerta.style.display = 'none';
        }

        // Mostrar formulario, colapsar busqueda
        elBuscar.style.display = 'none';
        elForm.style.display   = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Cargar informe si ya existe
        _limpiarForm();
        if (o.tiene_informe) {
            _cargarInformeExistente(o.id);
        }
    }

    /*  Cargar informe existente  */
    function _cargarInformeExistente(ordenId) {
        fetch(URL_VER + '?orden_id=' + ordenId, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok || !data.existe) return;
                var inf = data.informe;
                _setVal('inf-antecedentes',   inf.antecedentes   || '');
                _setVal('inf-proceso',         inf.proceso         || '');
                _setVal('inf-conclusion',      inf.conclusion      || '');
                _setVal('inf-recomendaciones', inf.recomendaciones || '');
                _setVal('inf-estado-equipo',   inf.estado_equipo  || 'Operativo');
                _setVal('inf-fecha',           (inf.fecha_informe  || '').substring(0, 10));

                _fotos = (inf.fotos || []).map(function (f) {
                    return { dataUrl: f.dataUrl || f.src || '', caption: f.caption || '', esExistente: true, id: f.id || null };
                });
                _fotasOrig = _fotos.length;
                if (_orden) _orden.repuestos_usados = inf.repuestos_usados || [];
                _renderFotos();
            })
            .catch(function () {});
    }

    /*  Guardar  */
    window._guardar = async function () {
        if (!_validar()) return;

        elBtnG.disabled  = true;
        elBtnG.innerHTML = '<span class="spin"></span>Guardando...';

        try {
            var fd = new FormData();
            fd.append('_token',          CSRF);
            fd.append('orden_id',        _orden.id);
            fd.append('antecedentes',    _getVal('inf-antecedentes'));
            fd.append('proceso',         _getVal('inf-proceso'));
            fd.append('conclusion',      _getVal('inf-conclusion'));
            fd.append('recomendaciones', _getVal('inf-recomendaciones'));
            fd.append('estado_equipo',   _getVal('inf-estado-equipo'));
            fd.append('fecha_informe',   _getVal('inf-fecha'));

            var nuevas  = _fotos.filter(function (f) { return !f.esExistente && f.blob; });
            var exist   = _fotos.filter(function (f) { return f.esExistente; });
            if (nuevas.length || exist.length < _fotasOrig) {
                for (var i = 0; i < _fotos.length; i++) {
                    var f    = _fotos[i];
                    var blob = f.esExistente ? await _urlABlob(f.dataUrl) : f.blob;
                    fd.append('fotos[]',    blob, 'foto_' + (i + 1) + '.jpg');
                    fd.append('captions[]', f.caption || '');
                }
            }

            var urlGuardar = MODO_EDICION_ADMIN && INFORME_ID_EDICION > 0
                ? URL_ACTUALIZAR_BASE + '/' + INFORME_ID_EDICION + '/actualizar'
                : URL_GUARDAR;

            var resp = await fetch(urlGuardar, { method: 'POST', body: fd });
            var data = await resp.json();

            if (data.ok) {
                _msgForm('ok', data.mensaje || (MODO_EDICION_ADMIN ? 'Informe actualizado correctamente.' : 'Informe guardado correctamente.'));
                _orden.tiene_informe = true;
                _fotasOrig = _fotos.length;
                _fotos = _fotos.map(function (f) {
                    return { dataUrl: f.dataUrl, caption: f.caption, esExistente: true, id: f.id || null };
                });
                _renderFotos();
            } else {
                _msgForm('err', data.error || 'Error al guardar.');
            }
        } catch (e) {
            _msgForm('err', 'Error de comunicacion: ' + e.message);
        } finally {
            elBtnG.disabled  = false;
            elBtnG.innerHTML = '<i class="bi bi-floppy"></i>Guardar Informe';
        }
    };

    /* Previsualizar (client-side PDF)  */
    window._previsualizar = function (imprimir) {
        if (!_validar()) return;
        var win = window.open('', '_blank', 'width=950,height=760');
        if (!win) { _msgForm('err', 'El navegador bloqueo la ventana emergente.'); return; }
        win.document.write(_buildHtml(_datosActuales(), _fotos));
        win.document.close();
        if (imprimir) win.onload = function () { win.print(); };
    };

    /*  Limpiar  */
    window._ciLimpiar = function () {
        _orden = null; _fotos = []; _fotasOrig = 0; _bloqueado = false;
        elBuscar.style.display = 'block';
        elForm.style.display   = 'none';
        elRes.style.display    = 'none';
        elRes.innerHTML = '';
        elQ.value = '';
        _msgGlobalOcultar();
        _limpiarForm();
        elQ.focus();
    };

    function _limpiarForm() {
        CAMPOS.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (id === 'inf-estado-equipo') el.value = 'Operativo';
            else if (id === 'inf-fecha')    el.value = FECHA_HOY;
            else                            el.value = '';
            el.disabled = false;
        });
        var zone = document.getElementById('upload-zone');
        var inp  = document.getElementById('inp-fotos');
        if (zone) zone.classList.remove('disabled');
        if (inp)  inp.disabled = false;
        if (elBtnG) elBtnG.disabled = false;
        elMsgF.style.display = 'none';
        _fotos = []; _renderFotos();
    }

    /*  Fotos  */
    window._agregarFotos = function (files) {
        var arr = Array.from(files);
        if (_fotos.length + arr.length > 10) {
            arr = arr.slice(0, 10 - _fotos.length);
            _msgForm('err', 'Máximo 10 fotos por informe.');
        }
        arr.forEach(function (file) {
            _comprimirFoto(file, function (url, blob) {
                _fotos.push({ dataUrl: url, caption: '', esExistente: false, blob: blob });
                _renderFotos();
            });
        });
        document.getElementById('inp-fotos').value = '';
    };
    window._soltarFotos = function (ev) {
        ev.preventDefault();
        document.getElementById('upload-zone').classList.remove('drag-over');
        window._agregarFotos(ev.dataTransfer.files);
    };
    window._elimFoto = function (i) { _fotos.splice(i, 1); _renderFotos(); };
    window._captFoto = function (i, v) { _fotos[i].caption = v; };

    function _renderFotos() {
        var grid = document.getElementById('fotos-grid');
        if (!_fotos.length) { grid.innerHTML = ''; return; }
        grid.innerHTML = _fotos.map(function (f, i) {
            return '<div class="foto-item">' +
                (f.esExistente ? '<div class="foto-badge">Guardada</div>' : '') +
                '<div class="foto-del" onclick="_elimFoto(' + i + ')"><i class="bi bi-x-lg"></i></div>' +
                '<img src="' + _esc(f.dataUrl) + '" alt="foto">' +
                '<input type="text" placeholder="Descripcion" value="' + _esc(f.caption || '') + '" oninput="_captFoto(' + i + ',this.value)">' +
                '</div>';
        }).join('');
    }

    function _comprimirFoto(file, cb) {
        var Q = 0.75, MAX = 1200;
        var r = new FileReader();
        r.onload = function (ev) {
            var img = new Image();
            img.onload = function () {
                var w = img.width, h = img.height;
                if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; }
                if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; }
                var c = document.createElement('canvas');
                c.width = w; c.height = h;
                c.getContext('2d').drawImage(img, 0, 0, w, h);
                var url = c.toDataURL('image/jpeg', Q);
                c.toBlob(function (blob) { cb(url, blob); }, 'image/jpeg', Q);
            };
            img.src = ev.target.result;
        };
        r.readAsDataURL(file);
    }

    function _urlABlob(url) {
        if ((url || '').startsWith('data:')) {
            var arr = url.split(','), mime = (arr[0].match(/:(.*?);/) || [,'image/jpeg'])[1];
            var b = atob(arr[1]), n = b.length, u8 = new Uint8Array(n);
            while (n--) u8[n] = b.charCodeAt(n);
            return Promise.resolve(new Blob([u8], { type: mime }));
        }
        return fetch(url).then(function (r) { return r.blob(); });
    }

    /*  Validacion  */
    function _validar() {
        if (!_orden) { _msgForm('err', 'Selecciona una orden primero.'); return false; }
        if (!_getVal('inf-antecedentes')) { _msgForm('err', 'Los antecedentes son obligatorios.'); return false; }
        if (!_getVal('inf-proceso'))      { _msgForm('err', 'El proceso es obligatorio.'); return false; }
        return true;
    }

    /*  PDF client-side  */
    function _datosActuales() {
        return {
            antecedentes:           _getVal('inf-antecedentes'),
            proceso:                _getVal('inf-proceso'),
            conclusion:             _getVal('inf-conclusion'),
            recomendaciones:        _getVal('inf-recomendaciones'),
            estado_equipo:          _getVal('inf-estado-equipo'),
            fecha_informe:          _getVal('inf-fecha'),
            tecnico:                _orden ? (_orden.tecnico || NOM_TEC) : NOM_TEC,
            nro_orden:              _orden ? _orden.nro_orden : '',
            cliente:                _orden ? _orden.cliente_nombre : '',
            cliente_identificacion: _orden ? _orden.cliente_identificacion : '',
            cliente_telefono:       _orden ? _orden.cliente_telefono : '',
            cliente_correo:         _orden ? _orden.cliente_correo : '',
            cliente_direccion:      _orden ? _orden.cliente_direccion : '',
            equipo_tipo:            _orden ? _orden.equipo_tipo : '',
            equipo_marca:           _orden ? _orden.equipo_marca : '',
            equipo_modelo:          _orden ? _orden.equipo_modelo : '',
            equipo_serie:           _orden ? _orden.equipo_serie : '',
            nro_factura:            _orden ? _orden.nro_factura : '',
            nro_factura_2:          _orden ? _orden.nro_factura_2 : '',
            estado_orden:           _orden ? _orden.estado_orden.replace(/Credito/g, 'Crédito').replace(/credito/g, 'crédito') : '',
            repuestos_usados:       _orden ? (_orden.repuestos_usados || []) : [],
        };
    }

    function _buildHtml(inf, fotos) {
        var mes = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        var p = (inf.fecha_informe || '').split('-');
        var fFmt = p.length === 3 ? p[2]+' de '+(mes[parseInt(p[1])]||p[1])+' de '+p[0] : (inf.fecha_informe||'');
        var clr = {'Operativo':'#10b981','Reparado parcialmente':'#f59e0b','Sin reparación posible':'#ef4444','Desguace':'#ef4444','En espera de repuesto':'#3b82f6'}[inf.estado_equipo]||'#64748b';
        var fotH='';
        if (fotos&&fotos.length) {
            var f='';
            for(var i=0;i<fotos.length;i+=2){
                f+='<tr><td style="padding:6px;text-align:center;"><img src="'+fotos[i].dataUrl+'" style="max-width:220px;max-height:180px;border-radius:4px;border:1px solid #ddd;">'+(fotos[i].caption?'<div style="font-size:8pt;color:#555;margin-top:4px;">'+fotos[i].caption+'</div>':'')+'</td>';
                f+=fotos[i+1]?'<td style="padding:6px;text-align:center;"><img src="'+fotos[i+1].dataUrl+'" style="max-width:220px;max-height:180px;border-radius:4px;border:1px solid #ddd;">'+(fotos[i+1].caption?'<div style="font-size:8pt;color:#555;margin-top:4px;">'+fotos[i+1].caption+'</div>':'')+'</td>':'<td></td>';
                f+='</tr>';
            }
            fotH='<div class="s">Evidencia Fotográfica</div><table style="width:100%;border-collapse:collapse;">'+f+'</table>';
        }
        var repH='';
        if(inf.repuestos_usados&&inf.repuestos_usados.length){
            repH='<tr><td colspan="2"><span class="l">Repuestos Utilizados</span>'+inf.repuestos_usados.map(function(r){return'<div style="margin-bottom:3px;">'+(r.codigo?'<strong>'+r.codigo+'</strong> &mdash; ':'')+r.nombre+(r.nro_parte?' <span style="color:#64748b;font-size:9pt;">('+r.nro_parte+')</span>':'')+'</div>';}).join('')+'</td></tr>';
        }
        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Informe '+(inf.nro_orden||'')+'</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;font-size:9pt;color:#000;background:#fff}@media print{@page{size:A4 portrait;margin:10mm}.np{display:none!important}body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}.w{width:100%;max-width:190mm;margin:auto;padding:6mm}.h{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1.5px solid #000;padding-bottom:6px;margin-bottom:8px}.h .i{font-size:8.5pt;line-height:1.6}.h .i .e{font-size:11pt;font-weight:bold}.h img{height:42px}.oh{display:flex;justify-content:space-between;align-items:center;background:#1a56db;color:#fff;padding:5px 10px;border-radius:3px;margin-bottom:8px}.oh .n{font-size:13pt;font-weight:bold}.oh .m{font-size:8pt;text-align:right;line-height:1.7}.s{background:#dbeafe;font-weight:bold;font-size:7.5pt;text-transform:uppercase;padding:3px 8px;border-left:3px solid #1a56db;margin-bottom:1px;margin-top:6px}table.d{width:100%;border-collapse:collapse;margin-bottom:7px}table.d td{border:1px solid #d1d5db;padding:4px 7px;font-size:8.5pt;vertical-align:top}table.d td .l{font-size:6.5pt;color:#6b7280;font-weight:bold;text-transform:uppercase;display:block;margin-bottom:1px}.tc{border:1px solid #d1d5db;padding:5px 8px;font-size:8.5pt;margin-bottom:7px;min-height:28px;white-space:pre-wrap;line-height:1.55}.b{display:inline-block;padding:2px 10px;border-radius:20px;font-size:8pt;font-weight:700;color:#fff}.fi{display:flex;justify-content:space-between;margin:10px 0}.fb{width:44%;text-align:center}.fl{border-top:1px solid #000;padding-top:4px;font-size:8.5pt;margin-top:28px}.bp{position:fixed;top:10px;right:10px;background:#1a56db;color:white;border:none;padding:10px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:bold;z-index:999;box-shadow:0 2px 8px rgba(0,0,0,.2)}</style></he'+'ad><body><button class="bp np" onclick="window.print()">&#128424; Imprimir / Guardar PDF</button><div class="w"><div class="h"><div class="i"><div class="e">Novitecnología Cía. Ltda.</div><div><b>Teléfonos:</b></div><div><b>GYE:</b> 04-6031337 / 0960500158 &nbsp;&nbsp; <b>UIO:</b> 02-6001635 / 0960500156</div><div>https://www.novitec.com.ec</div></div><img src="'+LOGO_URL+'" alt="Novitec"></div><div class="oh"><div class="n">'+(inf.nro_orden||'')+' &mdash; INFORME TÉCNICO</div><div class="m">Fecha: '+fFmt+'<br>Técnico: '+(inf.tecnico||NOM_TEC)+'</div></div><div class="s">Datos del Cliente</div><table class="d"><tr><td width="50%"><span class="l">Cliente</span>'+(inf.cliente||'')+'</td><td width="50%"><span class="l">Identificación / RUC</span>'+(inf.cliente_identificacion||'—')+'</td></tr><tr><td><span class="l">Teléfono</span>'+(inf.cliente_telefono||'—')+'</td><td><span class="l">Correo</span>'+(inf.cliente_correo||'—')+'</td></tr>'+(inf.cliente_direccion?'<tr><td colspan="2"><span class="l">Dirección</span>'+inf.cliente_direccion+'</td></tr>':'')+'</table><div class="s">Datos de la Orden</div><table class="d"><tr><td width="50%"><span class="l">Nro. de Orden</span>'+(inf.nro_orden||'')+'</td><td width="50%"><span class="l">Nro. Factura</span>'+([inf.nro_factura,inf.nro_factura_2].filter(Boolean).join(' / ')||'—')+'</td></tr><tr><td><span class="l">Estado de la Orden</span>'+(inf.estado_orden||'')+'</td><td><span class="l">Estado Final del Equipo</span><span class="b" style="background:'+clr+';">'+(inf.estado_equipo||'')+'</span></td></tr>'+repH+'</table><div class="s">Datos del Equipo</div><table class="d"><tr><td width="25%"><span class="l">Tipo</span>'+(inf.equipo_tipo||'—')+'</td><td width="25%"><span class="l">Marca</span>'+(inf.equipo_marca||'—')+'</td><td width="25%"><span class="l">Modelo</span>'+(inf.equipo_modelo||'—')+'</td><td width="25%"><span class="l">Serie</span>'+(inf.equipo_serie||'—')+'</td></tr></table><div class="s">Antecedentes</div><div class="tc">'+(inf.antecedentes||'')+'</div><div class="s">Proceso</div><div class="tc">'+(inf.proceso||'')+'</div>'+(inf.conclusion?'<div class="s">Conclusión</div><div class="tc">'+inf.conclusion+'</div>':'')+(inf.recomendaciones?'<div class="s">Recomendaciones</div><div class="tc">'+inf.recomendaciones+'</div>':'')+fotH+'<div class="fi"><div class="fb"><div class="fl">Técnico responsable</div></div><div class="fb"><div class="fl">Recibido conforme</div></div></div><div style="text-align:center;margin-top:10px;font-size:7pt;color:#94a3b8;border-top:1px solid #e5e7eb;padding-top:6px;">Novitecnología Cía. Ltda. — Sistema de Gestión Novitec</div></div></body></html>';
    }

    /*  Helpers  */
    function _getVal(id) { var el=document.getElementById(id); return el?el.value.trim():''; }
    function _setVal(id, v) { var el=document.getElementById(id); if(el)el.value=v; }
    function _esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function _msgForm(t, txt) { elMsgF.className='ci-msg ci-msg-'+(t==='ok'?'ok':'err'); elMsgF.innerHTML='<i class="bi bi-'+(t==='ok'?'check-circle':'exclamation-circle')+'"></i>'+_esc(txt); elMsgF.style.display='flex'; if(t==='ok')setTimeout(function(){elMsgF.style.display='none';},5000); elMsgF.scrollIntoView({behavior:'smooth',block:'nearest'}); }
    function _msgGlobal(t, txt) { elMsgG.className='ci-msg ci-msg-'+(t==='ok'?'ok':'err'); elMsgG.innerHTML='<i class="bi bi-exclamation-circle"></i>'+_esc(txt); elMsgG.style.display='flex'; }
    function _msgGlobalOcultar() { elMsgG.style.display='none'; }

    /*  Precarga por ?orden_id= (desde Mis Informes a Editar)  */
    document.addEventListener('DOMContentLoaded', function () {
        if (ORDEN_ID_PRECARGADO !== 0) {
            _buscarAjax(String(ORDEN_ID_PRECARGADO), 'id');
        }
    });

}());
</script>
@endpush
