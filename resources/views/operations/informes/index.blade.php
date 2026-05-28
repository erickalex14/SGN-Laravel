@extends('layouts.app')
@section('titulo', 'Informes Técnicos')

@push('css_adicional')
<style>
/* ═══════════════════════════════════════════
   MÓDULO INFORMES TÉCNICOS — Reestructurado
═══════════════════════════════════════════ */
.inf-wrap { max-width: 920px; margin: 0 auto; padding: 28px 20px; }

/* ── Header ─────────────────────────────── */
.inf-header { margin-bottom: 24px; }
.inf-header h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 4px; display:flex; align-items:center; gap:10px; }
.inf-header p  { color: #64748b; font-size: 13px; margin: 0; }

/* ── Cards de paso ──────────────────────── */
.inf-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    margin-bottom: 20px;
    overflow: hidden;
    animation: fadeUp .22s ease;
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:none; }
}
.inf-card-head {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 15px 22px;
    background: linear-gradient(135deg,#eff6ff,#dbeafe);
    border-bottom: 1px solid #bfdbfe;
}
.inf-card-head h3 { font-size: 14px; font-weight: 700; color: #1e40af; margin: 0; }
.inf-step {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    font-size: 13px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.inf-card-body { padding: 22px; }

/* ── Formulario ─────────────────────────── */
.campo { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.campo label { font-size:13px; font-weight:600; color:#374151; }
.campo select, .campo input[type="text"],
.campo input[type="number"], .campo input[type="date"],
.campo textarea {
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    padding: 9px 12px; font-size: 13.5px; color: #0f172a;
    background: #f8fafc; transition: border-color .2s, box-shadow .2s;
    font-family: inherit; resize: vertical;
}
.campo select:focus, .campo input:focus, .campo textarea:focus {
    outline: none; border-color: #2563eb;
    background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.campo select:disabled, .campo input:disabled, .campo textarea:disabled {
    background: #f1f5f9; color: #94a3b8; cursor: not-allowed; opacity: .8;
}
.req { color: #ef4444; }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }

/* ── Resumen orden ──────────────────────── */
.orden-resumen {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 14px 18px;
    margin-top: 14px;
    animation: fadeUp .2s ease;
}
.res-lbl { font-size: 10px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing:.04em; display:block; margin-bottom:2px; }
.res-val { font-size: 13px; font-weight: 600; color: #0f172a; }

/* ── Alerta de bloqueo ──────────────────── */
.inf-alerta {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 13px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 0;
    animation: fadeUp .2s ease;
}
.inf-alerta.lock { background:#fef9c3; color:#78350f; border:1px solid #fde68a; }
.inf-alerta.info { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }
.inf-alerta i { flex-shrink:0; margin-top:1px; }

/* ── Upload fotos ───────────────────────── */
.upload-zone {
    border: 2px dashed #cbd5e1; border-radius: 12px;
    padding: 32px; text-align: center; cursor: pointer;
    transition: all .2s; color: #94a3b8; background: #f8fafc;
}
.upload-zone:hover, .upload-zone.drag-over {
    border-color: #2563eb; background: #eff6ff; color: #2563eb;
}
.upload-zone i { font-size: 32px; display: block; margin-bottom: 8px; }
.upload-zone p { font-size: 13px; font-weight: 600; margin: 0 0 4px; }
.upload-zone small { font-size: 12px; }
.upload-zone.disabled { opacity:.5; pointer-events:none; }

/* ── Grid fotos ─────────────────────────── */
.fotos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)); gap: 14px; margin-top: 18px; }
.foto-item { position: relative; border-radius: 10px; overflow: hidden; border: 1.5px solid #e2e8f0; background: #f8fafc; }
.foto-item img { width: 100%; height: 125px; object-fit: cover; display: block; }
.foto-item input { width: 100%; border: none; border-top: 1px solid #e2e8f0; padding: 6px 8px; font-size: 11px; background: #fff; color: #374151; box-sizing: border-box; }
.foto-item input:focus { outline:none; background:#eff6ff; }
.foto-del { position:absolute; top:6px; right:6px; background:rgba(239,68,68,.85); color:#fff; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:11px; transition:background .15s; }
.foto-del:hover { background:#dc2626; }
.foto-badge { position:absolute; top:6px; left:6px; background:rgba(37,99,235,.85); color:#fff; border-radius:4px; padding:2px 6px; font-size:9px; font-weight:700; pointer-events:none; }

/* ── Mensaje feedback ───────────────────── */
.inf-msg { display:flex; align-items:center; gap:10px; padding:13px 18px; border-radius:10px; font-size:13.5px; font-weight:600; margin-bottom:16px; animation: fadeUp .2s ease; }
.inf-msg-ok  { background:#ecfdf5; color:#065f46; border:1px solid #6ee7b7; }
.inf-msg-err { background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; }

/* ── Botones ─────────────────────────────── */
.inf-botones { display:flex; gap:12px; justify-content:flex-end; margin-top:8px; flex-wrap:wrap; }
.btn-guardar {
    background: linear-gradient(135deg,#10b981,#059669);
    color: #fff; border: none; padding: 11px 24px; border-radius: 10px;
    font-size: 14px; font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .1s;
    box-shadow: 0 3px 12px rgba(16,185,129,.3);
    display:flex; align-items:center; gap:8px;
}
.btn-guardar:hover:not(:disabled) { opacity:.92; transform:translateY(-1px); }
.btn-guardar:disabled { opacity:.45; cursor:not-allowed; transform:none; }
.btn-preview {
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
    color: #fff; border: none; padding: 11px 24px; border-radius: 10px;
    font-size: 14px; font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .1s;
    display:flex; align-items:center; gap:8px;
}
.btn-preview:hover { opacity:.92; transform:translateY(-1px); }
.btn-print {
    background: #0f172a; color: #fff; border: none;
    padding: 11px 24px; border-radius: 10px; font-size: 14px;
    font-weight: 600; cursor: pointer; transition: opacity .2s;
    display:flex; align-items:center; gap:8px;
}
.btn-print:hover { opacity:.85; }
.btn-limpiar {
    background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0;
    padding: 11px 20px; border-radius: 10px; font-size: 14px;
    font-weight: 600; cursor: pointer; transition: background .15s;
    display:flex; align-items:center; gap:8px;
}
.btn-limpiar:hover { background:#e2e8f0; }

/* ── Spinner ─────────────────────────────── */
.spin { display:inline-block; width:16px; height:16px; border:2.5px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* ── Responsive ─────────────────────────── */
@media (max-width: 768px) {
    .grid-2 { grid-template-columns: 1fr; }
    .orden-resumen { grid-template-columns: 1fr 1fr; }
    .inf-botones { justify-content: stretch; }
    .btn-guardar, .btn-preview, .btn-print, .btn-limpiar { flex:1; justify-content:center; }
}
@media (max-width: 480px) {
    .orden-resumen { grid-template-columns: 1fr; }
    .inf-wrap { padding: 16px 12px; }
}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="inf-wrap">

    {{-- ─── Header ─── --}}
    <div class="inf-header">
        <h2><i class="bi bi-file-earmark-medical" style="color:#2563eb;"></i>Informes Técnicos</h2>
        <p>{{ $esAdmin ? 'Consulta e imprime los informes técnicos de las órdenes.' : 'Selecciona una orden para redactar o editar su informe técnico.' }}</p>
    </div>

    {{-- ─── Mensaje de feedback ─── --}}
    <div id="inf-msg" style="display:none;" class="inf-msg"></div>

    {{-- ═══ PASO 1: Seleccionar Orden ═══ --}}
    <div class="inf-card">
        <div class="inf-card-head">
            <span class="inf-step">1</span>
            <h3>Seleccionar Orden de Servicio</h3>
        </div>
        <div class="inf-card-body">
            <div class="campo">
                <label for="sel-orden">Orden <span class="req">*</span></label>
                <select id="sel-orden">
                    <option value="">— Selecciona una orden —</option>
                    @foreach($ordenesPendientes as $ord)
                    <option value="{{ $ord->id }}"
                        data-json="{{ json_encode([
                            'nro'                    => $ord->nro_orden,
                            'cliente'                => $ord->cliente_nombre,
                            'cliente_identificacion' => $ord->cliente_identificacion,
                            'cliente_telefono'       => $ord->cliente_telefono,
                            'cliente_correo'         => $ord->cliente_correo,
                            'cliente_direccion'      => $ord->cliente_direccion,
                            'equipo'                 => $ord->equipo_nombre,
                            'equipo_tipo'            => $ord->equipo_tipo,
                            'equipo_marca'           => $ord->equipo_marca,
                            'equipo_modelo'          => $ord->equipo_modelo,
                            'equipo_serie'           => $ord->equipo_serie,
                            'nro_factura'            => $ord->nro_factura,
                            'nro_factura_2'          => $ord->nro_factura_2,
                            'estado'                 => $ord->estado_orden,
                            'tecnico'                => $ord->tecnico,
                            'ingresado'              => $ord->ingresado_por_nombre,
                            'tiene'                  => (bool) $ord->tiene_informe,
                        ], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) }}">
                        {{ $ord->nro_orden }} — {{ $ord->cliente_nombre }}
                        ({{ trim($ord->equipo_tipo . ' ' . $ord->equipo_marca) }})
                        {{ $ord->tiene_informe ? ' ✓' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Resumen de la orden seleccionada --}}
            <div id="orden-resumen" style="display:none;" class="orden-resumen">
                <div><span class="res-lbl">Nro. Orden</span><span id="res-nro" class="res-val"></span></div>
                <div><span class="res-lbl">Cliente</span><span id="res-cliente" class="res-val"></span></div>
                <div><span class="res-lbl">Equipo</span><span id="res-equipo" class="res-val"></span></div>
                <div><span class="res-lbl">Estado</span><span id="res-estado" class="res-val"></span></div>
            </div>

            {{-- Alerta de estado (bloqueo / sin informe) --}}
            <div id="inf-alerta" style="display:none; margin-top:14px;"></div>
        </div>
    </div>

    {{-- ═══ PASO 2: Redactar Informe (solo técnico) ═══ --}}
    @if(!$esAdmin)
    <div class="inf-card" id="paso2" style="display:none;">
        <div class="inf-card-head">
            <span class="inf-step">2</span>
            <h3>Redactar Informe Técnico</h3>
        </div>
        <div class="inf-card-body">
            <div class="grid-2">
                <div class="campo">
                    <label for="inf-antecedentes">Antecedentes <span class="req">*</span></label>
                    <textarea id="inf-antecedentes" rows="4" placeholder="Describe los antecedentes del caso..."></textarea>
                </div>
                <div class="campo">
                    <label for="inf-proceso">Proceso <span class="req">*</span></label>
                    <textarea id="inf-proceso" rows="4" placeholder="Detalla el proceso de diagnóstico/reparación..."></textarea>
                </div>
            </div>
            <div class="grid-2">
                <div class="campo">
                    <label for="inf-conclusion">Conclusión <span class="req">*</span></label>
                    <textarea id="inf-conclusion" rows="3" placeholder="Conclusión del informe..."></textarea>
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

    {{-- ═══ PASO 3: Evidencia Fotográfica (solo técnico) ═══ --}}
    <div class="inf-card" id="paso3" style="display:none;">
        <div class="inf-card-head">
            <span class="inf-step">3</span>
            <h3>Evidencia Fotográfica</h3>
        </div>
        <div class="inf-card-body">
            <div id="upload-zone" class="upload-zone"
                 onclick="document.getElementById('inp-fotos').click()"
                 ondragover="event.preventDefault();this.classList.add('drag-over')"
                 ondragleave="this.classList.remove('drag-over')"
                 ondrop="_soltarFotos(event)">
                <i class="bi bi-cloud-arrow-up"></i>
                <p>Haz clic o arrastra fotos aquí</p>
                <small>JPG, PNG, WEBP — Máximo 10 fotos</small>
            </div>
            <input type="file" id="inp-fotos" accept="image/*" multiple style="display:none"
                   onchange="_agregarFotos(this.files)">
            <div id="fotos-grid" class="fotos-grid"></div>
        </div>
    </div>
    @endif

    {{-- ═══ Botones ═══ --}}
    <div class="inf-botones" id="inf-botones" style="display:none;">
        @if(!$esAdmin)
        <button class="btn-guardar" id="btn-guardar" onclick="_guardarInforme()">
            <i class="bi bi-floppy"></i>Guardar Informe
        </button>
        <button class="btn-preview" onclick="_previsualizarInforme(false)">
            <i class="bi bi-file-earmark-pdf"></i>Previsualizar PDF
        </button>
        <button class="btn-print" onclick="_previsualizarInforme(true)">
            <i class="bi bi-printer"></i>Imprimir
        </button>
        @else
        <button class="btn-preview" onclick="_verInformeAdmin(false)">
            <i class="bi bi-eye"></i>Ver Informe
        </button>
        <button class="btn-print" onclick="_verInformeAdmin(true)">
            <i class="bi bi-printer"></i>Imprimir Informe
        </button>
        @endif
        <button class="btn-limpiar" onclick="_limpiar()">
            <i class="bi bi-x-circle"></i>Limpiar
        </button>
    </div>

</div>
</section>
@endsection

@push('js_adicional')
<script>
/* ══════════════════════════════════════════════════════════
   INFORMES TÉCNICOS — Reestructurado
   Mejoras:
   - Datos de orden via JSON en data-json (sin acentos rotos)
   - Feedback visual con spinner en botón guardar
   - Bloqueo claro por estado de orden
   - Carga de informe existente robusta
   - Fotos: blob + dataUrl correctamente gestionados
══════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    /* ── Configuración ───────────────────────────────────────── */
    var ES_ADMIN     = {{ $esAdmin ? 'true' : 'false' }};
    var NOM_TEC      = {!! json_encode($nombreTecnico ?? '') !!};
    var FECHA_HOY    = '{{ date("Y-m-d") }}';
    var URL_VER      = '{{ route("informes.ver") }}';
    var URL_GUARDAR  = '{{ route("informes.store") }}';
    var URL_IMPRIMIR = '{{ url("/operaciones/informes") }}';
    var CSRF         = '{{ csrf_token() }}';
    var LOGO_URL     = '{{ asset("Novitecpdf.png") }}';

    /* ── Estado ──────────────────────────────────────────────── */
    var _orden           = null; // datos de la orden activa
    var _fotos           = [];   // [{dataUrl, caption, esExistente, blob?, id?}]
    var _fotasOrig       = 0;    // cuántas fotos había al cargar
    var _bloqueado       = false;
    var _informeId       = null; // id del informe si ya existe

    /* ── DOM ─────────────────────────────────────────────────── */
    var elSel       = document.getElementById('sel-orden');
    var elResumen   = document.getElementById('orden-resumen');
    var elAlerta    = document.getElementById('inf-alerta');
    var elMsg       = document.getElementById('inf-msg');
    var elBotones   = document.getElementById('inf-botones');
    var elBtnGuardar= document.getElementById('btn-guardar');

    var CAMPOS_FORM = ['inf-antecedentes','inf-proceso','inf-conclusion',
                       'inf-recomendaciones','inf-estado-equipo','inf-fecha'];
    var ESTADOS_BLOQUEO = ['nota de credito','finalizado','entregada','finalizada'];

    /* ── Selector de orden ───────────────────────────────────── */
    elSel.addEventListener('change', function () { _seleccionarOrden(this.value); });

    function _seleccionarOrden(ordenId) {
        _resetUI();
        if (!ordenId) return;

        var opt  = elSel.options[elSel.selectedIndex];
        var json = {};
        try { json = JSON.parse(opt.dataset.json || '{}'); } catch(e) {}

        _orden = Object.assign({ id: parseInt(ordenId) }, json);
        _bloqueado = ESTADOS_BLOQUEO.indexOf((_orden.estado || '').toLowerCase().trim()) !== -1;

        // Resumen
        document.getElementById('res-nro').textContent     = _orden.nro      || '—';
        document.getElementById('res-cliente').textContent = _orden.cliente   || '—';
        document.getElementById('res-equipo').textContent  = _orden.equipo    || '—';
        document.getElementById('res-estado').textContent  = _orden.estado    || '—';
        elResumen.style.display = 'grid';

        if (ES_ADMIN) {
            _modoAdmin();
        } else {
            _modoTecnico();
        }
    }

    /* ── Modo Admin ──────────────────────────────────────────── */
    function _modoAdmin() {
        if (_orden.tiene) {
            elBotones.style.display = 'flex';
            _ocultarAlerta();
        } else {
            elBotones.style.display = 'none';
            _mostrarAlerta('info',
                '<i class="bi bi-info-circle"></i>' +
                'Esta orden aún no tiene un informe técnico registrado.');
        }
    }

    /* ── Modo Técnico ────────────────────────────────────────── */
    function _modoTecnico() {
        document.getElementById('paso2').style.display = 'block';
        document.getElementById('paso3').style.display = 'block';
        elBotones.style.display = 'flex';

        // Bloqueo de campos
        CAMPOS_FORM.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.disabled = _bloqueado;
        });

        var zone = document.getElementById('upload-zone');
        var inp  = document.getElementById('inp-fotos');
        if (zone) { zone.classList.toggle('disabled', _bloqueado); }
        if (inp)  { inp.disabled = _bloqueado; }
        if (elBtnGuardar) { elBtnGuardar.disabled = _bloqueado; }

        if (_bloqueado) {
            _mostrarAlerta('lock',
                '<i class="bi bi-lock"></i>' +
                'Este informe es de solo lectura — la orden está en estado <strong>' + _esc(_orden.estado) + '</strong>.');
        } else {
            _ocultarAlerta();
        }

        // Cargar datos si existe informe
        if (_orden.tiene) {
            _cargarInformeExistente(_orden.id);
        } else {
            _limpiarForm();
        }
    }

    /* ── Cargar informe existente ────────────────────────────── */
    function _cargarInformeExistente(ordenId) {
        fetch(URL_VER + '?orden_id=' + ordenId, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok || !data.existe) return;
                var inf = data.informe;
                _informeId = inf.id || null;

                if (!ES_ADMIN) {
                    _setVal('inf-antecedentes',   inf.antecedentes   || '');
                    _setVal('inf-proceso',         inf.proceso         || '');
                    _setVal('inf-conclusion',      inf.conclusion      || '');
                    _setVal('inf-recomendaciones', inf.recomendaciones || '');
                    _setVal('inf-estado-equipo',   inf.estado_equipo  || 'Operativo');
                    _setVal('inf-fecha',           (inf.fecha_informe || '').substring(0, 10));
                }

                _fotos = (inf.fotos || []).map(function (f) {
                    return {
                        dataUrl:     f.dataUrl || f.src || '',
                        caption:     f.caption || '',
                        esExistente: true,
                        id:          f.id || null
                    };
                });
                _fotasOrig = _fotos.length;

                // Guardar repuestos en el objeto orden
                if (_orden) _orden.repuestos_usados = inf.repuestos_usados || [];

                if (!ES_ADMIN) _renderFotos();
            })
            .catch(function () {/* silencioso */});
    }

    /* ── Guardar informe ─────────────────────────────────────── */
    window._guardarInforme = async function () {
        if (!_validar()) return;

        elBtnGuardar.disabled  = true;
        elBtnGuardar.innerHTML = '<span class="spin"></span>Guardando...';

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

            var nuevas      = _fotos.filter(function (f) { return !f.esExistente && f.blob; });
            var existentes  = _fotos.filter(function (f) { return f.esExistente; });
            var hayNuevas   = nuevas.length > 0;
            var hayElim     = existentes.length < _fotasOrig;

            if (hayNuevas || hayElim) {
                for (var i = 0; i < _fotos.length; i++) {
                    var f    = _fotos[i];
                    var blob = f.esExistente ? await _urlABlob(f.dataUrl) : f.blob;
                    fd.append('fotos[]',    blob, 'foto_' + (i + 1) + '.jpg');
                    fd.append('captions[]', f.caption || '');
                }
            }

            var resp = await fetch(URL_GUARDAR, { method: 'POST', body: fd });
            var data = await resp.json();

            if (data.ok) {
                _mostrarMsg('ok', data.mensaje || 'Informe guardado correctamente.');
                _orden.tiene = true;
                _informeId   = data.informe_id || _informeId;

                // Actualizar ✓ en el select
                var opt = elSel.options[elSel.selectedIndex];
                if (opt && !opt.text.includes('✓')) opt.text += ' ✓';
                var json = {};
                try { json = JSON.parse(opt.dataset.json || '{}'); } catch(e) {}
                json.tiene = true;
                opt.dataset.json = JSON.stringify(json);

                // Marcar fotos como existentes
                _fotasOrig = _fotos.length;
                _fotos     = _fotos.map(function (f) {
                    return { dataUrl: f.dataUrl, caption: f.caption, esExistente: true, id: f.id || null };
                });
                _renderFotos();
            } else {
                _mostrarMsg('err', data.error || 'Error al guardar el informe.');
            }
        } catch (err) {
            _mostrarMsg('err', 'Error de comunicación: ' + err.message);
        } finally {
            elBtnGuardar.disabled  = false;
            elBtnGuardar.innerHTML = '<i class="bi bi-floppy"></i>Guardar Informe';
        }
    };

    /* ── Previsualizar / Imprimir (Técnico: client-side) ─────── */
    window._previsualizarInforme = function (imprimir) {
        if (!_validar()) return;
        _abrirVentana(_datosActuales(), _fotos, imprimir);
    };

    /* ── Ver informe (Admin: server-side) ────────────────────── */
    window._verInformeAdmin = function (imprimir) {
        if (!_orden) return;
        fetch(URL_VER + '?orden_id=' + _orden.id, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok || !data.existe) {
                    _mostrarMsg('err', 'No se encontró el informe.');
                    return;
                }
                var url = URL_IMPRIMIR + '/' + data.informe.id + '/imprimir';
                var win = window.open(url, '_blank', 'width=950,height=760');
                if (imprimir && win) win.onload = function () { win.print(); };
            });
    };

    /* ── Limpiar ─────────────────────────────────────────────── */
    window._limpiar = function () {
        elSel.value = '';
        _resetUI();
        _orden = null;
        elMsg.style.display = 'none';
    };

    /* ── Fotos ───────────────────────────────────────────────── */
    window._agregarFotos = function (files) {
        var arr = Array.from(files);
        if (_fotos.length + arr.length > 10) {
            arr = arr.slice(0, 10 - _fotos.length);
            _mostrarMsg('err', 'Máximo 10 fotos por informe.');
        }
        arr.forEach(function (file) {
            _comprimirFoto(file, function (dataUrl, blob) {
                _fotos.push({ dataUrl: dataUrl, caption: '', esExistente: false, blob: blob });
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

    function _eliminarFoto(idx) { _fotos.splice(idx, 1); _renderFotos(); }
    function _actualizarCaption(idx, val) { _fotos[idx].caption = val; }

    function _renderFotos() {
        var grid = document.getElementById('fotos-grid');
        if (!_fotos.length) { grid.innerHTML = ''; return; }
        grid.innerHTML = _fotos.map(function (f, i) {
            return '<div class="foto-item">' +
                (f.esExistente ? '<div class="foto-badge">Guardada</div>' : '') +
                '<div class="foto-del" onclick="_elimFoto(' + i + ')"><i class="bi bi-x-lg"></i></div>' +
                '<img src="' + _esc(f.dataUrl) + '" alt="foto">' +
                '<input type="text" placeholder="Descripción" value="' + _esc(f.caption || '') + '" ' +
                'oninput="_captFoto(' + i + ',this.value)">' +
                '</div>';
        }).join('');
    }

    // Exponemos para onclick inline
    window._elimFoto = _eliminarFoto;
    window._captFoto = _actualizarCaption;

    function _comprimirFoto(file, cb) {
        var MAX = 1200, Q = 0.75;
        var reader = new FileReader();
        reader.onload = function (ev) {
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
        reader.readAsDataURL(file);
    }

    function _urlABlob(url) {
        if ((url || '').startsWith('data:')) {
            var arr  = url.split(',');
            var mime = (arr[0].match(/:(.*?);/) || [, 'image/jpeg'])[1];
            var b    = atob(arr[1]);
            var n    = b.length;
            var u8   = new Uint8Array(n);
            while (n--) u8[n] = b.charCodeAt(n);
            return Promise.resolve(new Blob([u8], { type: mime }));
        }
        return fetch(url).then(function (r) { return r.blob(); });
    }

    /* ── Validación ──────────────────────────────────────────── */
    function _validar() {
        if (!_orden) { _mostrarMsg('err', 'Selecciona una orden.'); return false; }
        if (ES_ADMIN) return true;
        if (!_getVal('inf-antecedentes')) { _mostrarMsg('err', 'Los antecedentes son obligatorios.'); return false; }
        if (!_getVal('inf-proceso'))      { _mostrarMsg('err', 'El proceso es obligatorio.'); return false; }
        if (!_getVal('inf-conclusion'))   { _mostrarMsg('err', 'La conclusión es obligatoria.'); return false; }
        return true;
    }

    /* ── PDF client-side ─────────────────────────────────────── */
    function _datosActuales() {
        return {
            antecedentes:           _getVal('inf-antecedentes'),
            proceso:                _getVal('inf-proceso'),
            conclusion:             _getVal('inf-conclusion'),
            recomendaciones:        _getVal('inf-recomendaciones'),
            estado_equipo:          _getVal('inf-estado-equipo'),
            fecha_informe:          _getVal('inf-fecha'),
            tecnico:                _orden ? (_orden.tecnico || NOM_TEC) : NOM_TEC,
            nro_orden:              _orden ? _orden.nro            : '',
            cliente:                _orden ? _orden.cliente         : '',
            cliente_identificacion: _orden ? _orden.cliente_identificacion : '',
            cliente_telefono:       _orden ? _orden.cliente_telefono       : '',
            cliente_correo:         _orden ? _orden.cliente_correo         : '',
            cliente_direccion:      _orden ? _orden.cliente_direccion      : '',
            equipo_tipo:            _orden ? _orden.equipo_tipo    : '',
            equipo_marca:           _orden ? _orden.equipo_marca   : '',
            equipo_modelo:          _orden ? _orden.equipo_modelo  : '',
            equipo_serie:           _orden ? _orden.equipo_serie   : '',
            nro_factura:            _orden ? _orden.nro_factura    : '',
            nro_factura_2:          _orden ? _orden.nro_factura_2  : '',
            estado_orden:           _orden ? _orden.estado         : '',
            repuestos_usados:       _orden ? (_orden.repuestos_usados || []) : [],
        };
    }

    function _abrirVentana(inf, fotos, imprimir) {
        var html = _buildHtml(inf, fotos);
        var win  = window.open('', '_blank', 'width=950,height=760');
        if (!win) { _mostrarMsg('err', 'El navegador bloqueó la ventana emergente.'); return; }
        win.document.write(html);
        win.document.close();
        if (imprimir) win.onload = function () { win.print(); };
    }

    function _buildHtml(inf, fotos) {
        var meses = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        var p     = (inf.fecha_informe || '').split('-');
        var fFmt  = p.length === 3
            ? p[2] + ' de ' + (meses[parseInt(p[1])] || p[1]) + ' de ' + p[0]
            : (inf.fecha_informe || '');

        var colores = {
            'Operativo':             '#10b981',
            'Reparado parcialmente': '#f59e0b',
            'Sin reparación posible':'#ef4444',
            'Desguace':              '#ef4444',
            'En espera de repuesto': '#3b82f6',
        };
        var estadoColor = colores[inf.estado_equipo] || '#64748b';

        var fotH = '';
        if (fotos && fotos.length) {
            var filas = '';
            for (var i = 0; i < fotos.length; i += 2) {
                filas += '<tr><td style="padding:6px;text-align:center;">' +
                    '<img src="' + fotos[i].dataUrl + '" style="max-width:220px;max-height:180px;border-radius:4px;border:1px solid #ddd;">' +
                    (fotos[i].caption ? '<div style="font-size:8pt;color:#555;margin-top:4px;">' + fotos[i].caption + '</div>' : '') +
                    '</td>';
                if (fotos[i + 1]) {
                    filas += '<td style="padding:6px;text-align:center;">' +
                        '<img src="' + fotos[i + 1].dataUrl + '" style="max-width:220px;max-height:180px;border-radius:4px;border:1px solid #ddd;">' +
                        (fotos[i + 1].caption ? '<div style="font-size:8pt;color:#555;margin-top:4px;">' + fotos[i + 1].caption + '</div>' : '') +
                        '</td>';
                } else { filas += '<td></td>'; }
                filas += '</tr>';
            }
            fotH = '<div class="sec">Evidencia Fotográfica</div>' +
                   '<table style="width:100%;border-collapse:collapse;">' + filas + '</table>';
        }

        var repH = '';
        if (inf.repuestos_usados && inf.repuestos_usados.length) {
            repH = '<tr><td colspan="2"><span class="lbl">Repuestos Utilizados</span>' +
                inf.repuestos_usados.map(function (r) {
                    return '<div style="margin-bottom:3px;">' +
                        (r.codigo ? '<strong>' + r.codigo + '</strong> &mdash; ' : '') +
                        r.nombre +
                        (r.nro_parte ? ' <span style="color:#64748b;font-size:9pt;">(Nro. Parte: ' + r.nro_parte + ')</span>' : '') +
                        '</div>';
                }).join('') + '</td></tr>';
        }

        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">' +
            '<title>Informe ' + (inf.nro_orden || '') + '</title>' +
            '<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;font-size:9pt;color:#000;background:#fff}' +
            '@media print{@page{size:A4 portrait;margin:10mm}.no-print{display:none!important}body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}' +
            '.wrap{width:100%;max-width:190mm;margin:auto;padding:6mm}' +
            '.hdr{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1.5px solid #000;padding-bottom:6px;margin-bottom:8px}' +
            '.hdr-info{font-size:8.5pt;line-height:1.6}.hdr-info .empresa{font-size:11pt;font-weight:bold}.hdr img{height:42px}' +
            '.ord-hdr{display:flex;justify-content:space-between;align-items:center;background:#1a56db;color:#fff;padding:5px 10px;border-radius:3px;margin-bottom:8px}' +
            '.ord-hdr .nro{font-size:13pt;font-weight:bold}.ord-hdr .meta{font-size:8pt;text-align:right;line-height:1.7}' +
            '.sec{background:#dbeafe;font-weight:bold;font-size:7.5pt;text-transform:uppercase;padding:3px 8px;border-left:3px solid #1a56db;margin-bottom:1px;margin-top:6px}' +
            'table.d{width:100%;border-collapse:collapse;margin-bottom:7px}table.d td{border:1px solid #d1d5db;padding:4px 7px;font-size:8.5pt;vertical-align:top}' +
            'table.d td .lbl{font-size:6.5pt;color:#6b7280;font-weight:bold;text-transform:uppercase;display:block;margin-bottom:1px}' +
            '.tc{border:1px solid #d1d5db;padding:5px 8px;font-size:8.5pt;margin-bottom:7px;min-height:28px;white-space:pre-wrap;line-height:1.55}' +
            '.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:8pt;font-weight:700;color:#fff}' +
            '.firmas{display:flex;justify-content:space-between;margin:10px 0}.firma{width:44%;text-align:center}' +
            '.firma-ln{border-top:1px solid #000;padding-top:4px;font-size:8.5pt;margin-top:28px}' +
            '.btn-prt{position:fixed;top:10px;right:10px;background:#1a56db;color:white;border:none;padding:10px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:bold;z-index:999;box-shadow:0 2px 8px rgba(0,0,0,.2)}' +
            '</style></head><body>' +
            '<button class="btn-prt no-print" onclick="window.print()">&#128424; Imprimir / Guardar PDF</button>' +
            '<div class="wrap">' +
            '<div class="hdr"><div class="hdr-info">' +
            '<div class="empresa">Novitecnologia Cia. Ltda.</div>' +
            '<div><b>Teléfonos:</b></div>' +
            '<div><b>GYE:</b> 04-6031337 / 0960500158 &nbsp;&nbsp; <b>UIO:</b> 02-6001635 / 0960500156</div>' +
            '<div>https://www.novitec.com.ec</div></div>' +
            '<img src="' + LOGO_URL + '" alt="Novitec"></div>' +
            '<div class="ord-hdr">' +
            '<div class="nro">' + (inf.nro_orden || '') + ' &mdash; INFORME TÉCNICO</div>' +
            '<div class="meta">Fecha: ' + fFmt + '<br>Técnico: ' + (inf.tecnico || NOM_TEC) + '</div></div>' +
            '<div class="sec">Datos del Cliente</div>' +
            '<table class="d"><tr>' +
            '<td width="50%"><span class="lbl">Cliente</span>' + (inf.cliente || '') + '</td>' +
            '<td width="50%"><span class="lbl">Identificación / RUC</span>' + (inf.cliente_identificacion || '—') + '</td></tr><tr>' +
            '<td><span class="lbl">Teléfono</span>' + (inf.cliente_telefono || '—') + '</td>' +
            '<td><span class="lbl">Correo</span>' + (inf.cliente_correo || '—') + '</td></tr>' +
            (inf.cliente_direccion ? '<tr><td colspan="2"><span class="lbl">Dirección</span>' + inf.cliente_direccion + '</td></tr>' : '') +
            '</table>' +
            '<div class="sec">Datos de la Orden</div>' +
            '<table class="d"><tr>' +
            '<td width="50%"><span class="lbl">Nro. de Orden</span>' + (inf.nro_orden || '') + '</td>' +
            '<td width="50%"><span class="lbl">Nro. Factura</span>' +
                ([inf.nro_factura, inf.nro_factura_2].filter(Boolean).join(' / ') || '—') + '</td></tr><tr>' +
            '<td><span class="lbl">Estado de la Orden</span>' + (inf.estado_orden || '') + '</td>' +
            '<td><span class="lbl">Estado Final del Equipo</span>' +
            '<span class="badge" style="background:' + estadoColor + ';">' + (inf.estado_equipo || '') + '</span></td></tr>' +
            repH + '</table>' +
            '<div class="sec">Datos del Equipo</div>' +
            '<table class="d"><tr>' +
            '<td width="25%"><span class="lbl">Tipo</span>'   + (inf.equipo_tipo   || '—') + '</td>' +
            '<td width="25%"><span class="lbl">Marca</span>'  + (inf.equipo_marca  || '—') + '</td>' +
            '<td width="25%"><span class="lbl">Modelo</span>' + (inf.equipo_modelo || '—') + '</td>' +
            '<td width="25%"><span class="lbl">Serie</span>'  + (inf.equipo_serie  || '—') + '</td></tr></table>' +
            '<div class="sec">Antecedentes</div><div class="tc">' + (inf.antecedentes || '') + '</div>' +
            '<div class="sec">Proceso</div><div class="tc">' + (inf.proceso || '') + '</div>' +
            (inf.conclusion    ? '<div class="sec">Conclusión</div><div class="tc">' + inf.conclusion + '</div>' : '') +
            (inf.recomendaciones ? '<div class="sec">Recomendaciones</div><div class="tc">' + inf.recomendaciones + '</div>' : '') +
            fotH +
            '<div class="firmas">' +
            '<div class="firma"><div class="firma-ln">Técnico responsable</div></div>' +
            '<div class="firma"><div class="firma-ln">Recibido conforme</div></div>' +
            '</div>' +
            '<div style="text-align:center;margin-top:10px;font-size:7pt;color:#94a3b8;border-top:1px solid #e5e7eb;padding-top:6px;">Novitecnología Cía. Ltda. — Sistema de Gestión Novitec</div>' +
            '</div></body></html>';
    }

    /* ── Helpers UI ──────────────────────────────────────────── */
    function _resetUI() {
        elResumen.style.display = 'none';
        elBotones.style.display = 'none';
        _ocultarAlerta();
        if (!ES_ADMIN) {
            document.getElementById('paso2').style.display = 'none';
            document.getElementById('paso3').style.display = 'none';
            _limpiarForm();
        }
        _fotos      = [];
        _fotasOrig  = 0;
        _informeId  = null;
        _bloqueado  = false;
    }

    function _limpiarForm() {
        CAMPOS_FORM.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (id === 'inf-estado-equipo') { el.value = 'Operativo'; }
            else if (id === 'inf-fecha')    { el.value = FECHA_HOY; }
            else                            { el.value = ''; }
            el.disabled = false;
        });
        var zone = document.getElementById('upload-zone');
        var inp  = document.getElementById('inp-fotos');
        if (zone) zone.classList.remove('disabled');
        if (inp)  inp.disabled = false;
        if (elBtnGuardar) elBtnGuardar.disabled = false;
        _fotos = [];
        _renderFotos();
    }

    function _mostrarAlerta(tipo, html) {
        elAlerta.className = 'inf-alerta ' + tipo;
        elAlerta.innerHTML = html;
        elAlerta.style.display = 'flex';
    }
    function _ocultarAlerta() { elAlerta.style.display = 'none'; }

    function _mostrarMsg(tipo, txt) {
        elMsg.className  = 'inf-msg inf-msg-' + (tipo === 'ok' ? 'ok' : 'err');
        elMsg.innerHTML  = '<i class="bi bi-' + (tipo === 'ok' ? 'check-circle' : 'exclamation-circle') + '"></i>' + _esc(txt);
        elMsg.style.display = 'flex';
        if (tipo === 'ok') setTimeout(function () { elMsg.style.display = 'none'; }, 5000);
        elMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function _getVal(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }
    function _setVal(id, v) {
        var el = document.getElementById(id);
        if (el) el.value = v;
    }
    function _esc(s) {
        return String(s || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

}());
</script>
@endpush
