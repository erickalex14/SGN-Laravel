@extends('layouts.app')

@section('titulo', 'Sucursales Novicompu')

@push('css_adicional')
    <style>
        /* Se ha conservado absolutamente todo el CSS entregado en el archivo modulo-sucursales-cliente.php */
        .sc-container { max-width: 1200px; margin: 0 auto; }
        .form-titulo { margin-bottom: 24px; padding-bottom: 18px; border-bottom: 2px solid #e2e8f0; }
        .form-titulo h2 { margin: 0 0 4px; color: #0f172a; font-size: 22px; font-weight: 700; }
        .form-titulo p  { margin: 0; color: #94a3b8; font-size: 14px; }
        .sc-layout { display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start; }
        @media (max-width: 900px) { .sc-layout { grid-template-columns: 1fr; } }
        .sc-form-box { display: flex; flex-direction: column; gap: 18px; }
        .seccion { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: white; }
        .seccion-header { display:flex; align-items:center; gap:10px; padding:12px 18px; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
        .seccion-icon { font-size:17px; color:#2563eb; }
        .seccion-header h3 { margin:0; font-size:15px; font-weight:600; color:#1e293b; }
        .sc-campos { padding: 20px 18px; display: flex; flex-direction: column; gap: 14px; }
        .campo { display: flex; flex-direction: column; gap: 5px; }
        .campo label { font-size: 13px; font-weight: 600; color: #475569; }
        .campo input, .campo select { padding: 9px 13px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; color: #1e293b; outline: none; transition: border-color .2s, box-shadow .2s; background: white; }
        .campo input:focus, .campo select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .campo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .req { color: #ef4444; }
        .campo-hint { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }
        .toggle-wrap { display: flex; align-items: center; gap: 10px; padding: 10px 0; }
        .toggle-wrap label { font-size: 13px; font-weight: 600; color: #475569; margin: 0; }
        .toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #cbd5e1; border-radius: 24px; transition: .3s; }
        .toggle-slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: .3s; }
        .toggle input:checked + .toggle-slider { background: #16a34a; }
        .toggle input:checked + .toggle-slider:before { transform: translateX(20px); }
        .sc-botones { display: flex; gap: 10px; }
        .btn-guardar-sc { flex: 1; padding: 11px 0; background: #2563eb; color: white; border: none; border-radius: 9px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .2s; }
        .btn-guardar-sc:hover { background: #1d4ed8; }
        .btn-guardar-sc:disabled { background: #93c5fd; cursor: not-allowed; }
        .btn-cancelar-sc { padding: 11px 20px; background: #f1f5f9; color: #64748b; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .2s; }
        .btn-cancelar-sc:hover { background: #e2e8f0; }
        .sc-msg { padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display: none; margin-top: 4px; }
        .sc-msg.ok  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .sc-msg.err { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .sc-table-box { background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .sc-table-toolbar { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; flex-wrap: wrap; }
        .sc-table-toolbar h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; flex: 1; }
        .sc-search { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; width: 220px; transition: border-color .2s; }
        .sc-search:focus { border-color: #2563eb; }
        .filter-btn { padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1.5px solid #e2e8f0; background: white; cursor: pointer; color: #475569; transition: all .15s; }
        .filter-btn.active { background: #2563eb; color: white; border-color: #2563eb; }
        .filter-btn:hover:not(.active) { background: #f1f5f9; }
        .sc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .sc-table th { padding: 10px 14px; text-align: left; font-weight: 700; font-size: 12px; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-transform: uppercase; letter-spacing: .5px; }
        .sc-table td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
        .sc-table tr:hover td { background: #f8fafc; }
        .sc-table tr:last-child td { border-bottom: none; }
        .badge-activa   { background: #dcfce7; color: #166534; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-inactiva { background: #fee2e2; color: #991b1b; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .btn-editar-row { padding: 5px 12px; background: #eff6ff; color: #2563eb; border: 1.5px solid #bfdbfe; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; white-space: nowrap; }
        .btn-editar-row:hover { background: #2563eb; color: white; }
        .btn-toggle-row { padding: 5px 12px; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; border: 1.5px solid; white-space: nowrap; }
        .btn-toggle-row.desactivar { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
        .btn-toggle-row.desactivar:hover { background: #ef4444; color: white; border-color: #ef4444; }
        .btn-toggle-row.activar    { background: #f0fdf4; color: #166534; border-color: #86efac; }
        .btn-toggle-row.activar:hover    { background: #16a34a; color: white; border-color: #16a34a; }
        .sc-empty { padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; }
        .sc-count { font-size: 12px; color: #94a3b8; padding: 10px 18px; border-top: 1px solid #f1f5f9; }
    </style>
@endpush

@section('contenido')
    <section class="modulo activo">
        <div class="sc-container">

            <div class="form-titulo">
                <h2><i class="bi bi-shop me-2"></i>Sucursales Novicompu</h2>
                <p>Gestiona las sucursales Novicompu disponibles para asignar en órdenes de servicio</p>
            </div>

            <div class="sc-layout">

                <div class="sc-form-box">
                    <div class="seccion">
                        <div class="seccion-header">
                            <span class="seccion-icon"><i class="bi bi-plus-circle"></i></span>
                            <h3 id="sc-form-titulo">Nueva Sucursal</h3>
                        </div>
                        <div class="sc-campos">
                            <input type="hidden" id="sc-edit-id" value="">

                            <div class="campo-grid">
                                <div class="campo">
                                    <label>Número <span class="req">*</span></label>
                                    <input type="number" id="sc-numero" min="1" max="9999" placeholder="Ej: 1" autocomplete="off">
                                    <span class="campo-hint">Número de sucursal</span>
                                </div>
                                <div class="campo">
                                    <label>Código <span class="req">*</span></label>
                                    <input type="text" id="sc-codigo" maxlength="10" placeholder="Ej: N001" autocomplete="off"
                                           oninput="this.value=this.value.toUpperCase()">
                                    <span class="campo-hint">Código único (ej. N001)</span>
                                </div>
                            </div>

                            <div class="campo">
                                <label>Nombre <span class="req">*</span></label>
                                <input type="text" id="sc-nombre" maxlength="100" placeholder="Ej: RIO COCA" autocomplete="off"
                                       oninput="this.value=this.value.toUpperCase()">
                            </div>

                            <div class="campo">
                                <label>Provincia</label>
                                <select id="sc-provincia">
                                    <option value="">— Seleccione provincia —</option>
                                    @foreach ($provincias_db as $prov)
                                        <option value="{{ $prov }}">{{ $prov }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="campo">
                                <label>Sucursal Novitec</label>
                                <select id="sc-novitec">
                                    <option value="">— Sin asignar —</option>
                                    @foreach ($sucursales_novitec as $sn)
                                        <option value="{{ $sn->secuencial }}">
                                            {{ str_pad($sn->nro_sucursal, 2, '0', STR_PAD_LEFT) }} — {{ $sn->ciudad }} ({{ $sn->secuencial }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="campo-hint">Sucursal Novitecnología asignada</span>
                            </div>

                            <div class="toggle-wrap">
                                <label>Activa</label>
                                <label class="toggle">
                                    <input type="checkbox" id="sc-activa" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span id="sc-activa-label" style="font-size:13px;color:#475569;">Sí</span>
                            </div>

                            <div id="sc-msg" class="sc-msg"></div>

                            <div class="sc-botones">
                                <button type="button" class="btn-guardar-sc" id="btn-sc-guardar" onclick="scGuardar()">
                                    <i class="bi bi-floppy me-1"></i>Guardar
                                </button>
                                <button type="button" class="btn-cancelar-sc" id="btn-sc-cancelar" onclick="scCancelar()" style="display:none;">
                                    <i class="bi bi-x-circle me-1"></i>Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sc-table-box">
                    <div class="sc-table-toolbar">
                        <h3><i class="bi bi-list-ul me-2"></i>Sucursales registradas</h3>
                        <input type="text" class="sc-search" id="sc-buscar" placeholder="Buscar por número o nombre..." oninput="scFiltrar()">
                        <button class="filter-btn active" id="filter-todas"    onclick="scSetFiltro('todas')">Todas</button>
                        <button class="filter-btn"         id="filter-activas" onclick="scSetFiltro('activas')">Activas</button>
                        <button class="filter-btn"         id="filter-inact"   onclick="scSetFiltro('inactivas')">Inactivas</button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="sc-table" id="sc-tabla">
                            <thead>
                            <tr>
                                <th>Nro.</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Provincia</th>
                                <th>Novitec</th>
                                <th>Estado</th>
                                <th style="width:140px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="sc-tbody">
                            @forelse ($sucursales as $s)
                                <tr data-id="{{ $s->id }}"
                                    data-numero="{{ $s->numero }}"
                                    data-codigo="{{ $s->codigo }}"
                                    data-nombre="{{ $s->nombre }}"
                                    data-provincia="{{ $s->provincia ?? '' }}"
                                    data-novitec="{{ $s->novitec_sucursal ?? '' }}"
                                    data-activa="{{ $s->activa ? '1' : '0' }}">
                                    <td><strong>{{ str_pad($s->numero, 3, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $s->codigo }}</code></td>
                                    <td>{{ $s->nombre }}</td>
                                    <td style="color:#64748b;">{{ $s->provincia ?? '-' }}</td>
                                    <td style="color:#64748b;">{{ $s->novitec_sucursal ?? '-' }}</td>
                                    <td>
                                        @if ($s->activa)
                                            <span class="badge-activa">Activa</span>
                                        @else
                                            <span class="badge-inactiva">Inactiva</span>
                                        @endif
                                    </td>
                                    <td style="display:flex;gap:6px;">
                                        <button class="btn-editar-row" onclick="scEditar(this)">
                                            <i class="bi bi-pencil me-1"></i>Editar
                                        </button>
                                        @if ($s->activa)
                                            <button class="btn-toggle-row desactivar" onclick="scToggle({{ $s->id }}, 0, this)">
                                                <i class="bi bi-eye-slash me-1"></i>Desactivar
                                            </button>
                                        @else
                                            <button class="btn-toggle-row activar" onclick="scToggle({{ $s->id }}, 1, this)">
                                                <i class="bi bi-eye me-1"></i>Activar
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="sc-empty">No hay sucursales registradas.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="sc-count" id="sc-count">
                        {{ count($sucursales) }} sucursal(es) en total
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('js_adicional')
    <script>
        // El JS es idéntico, solo sustituimos las llamadas a fetch por rutas blade.
        var _scFiltroActual = 'todas';

        document.getElementById('sc-activa').addEventListener('change', function() {
            document.getElementById('sc-activa-label').textContent = this.checked ? 'Sí' : 'No';
        });

        function scMostrarMsg(tipo, texto) {
            var el = document.getElementById('sc-msg');
            el.className = 'sc-msg ' + tipo;
            el.textContent = texto;
            el.style.display = 'block';
            if (tipo === 'ok') setTimeout(function() { el.style.display = 'none'; }, 3500);
        }

        function scCancelar() {
            document.getElementById('sc-edit-id').value = '';
            document.getElementById('sc-numero').value = '';
            document.getElementById('sc-codigo').value = '';
            document.getElementById('sc-nombre').value = '';
            document.getElementById('sc-provincia').value = '';
            document.getElementById('sc-novitec').value = '';
            document.getElementById('sc-activa').checked = true;
            document.getElementById('sc-activa-label').textContent = 'Sí';
            document.getElementById('sc-form-titulo').textContent = 'Nueva Sucursal';
            document.getElementById('btn-sc-cancelar').style.display = 'none';
            document.getElementById('sc-msg').style.display = 'none';
            document.querySelector('.seccion-icon i').className = 'bi bi-plus-circle';
            document.getElementById('sc-numero').disabled = false;
            document.getElementById('sc-codigo').disabled = false;
        }

        function scEditar(btn) {
            var tr = btn.closest('tr');
            document.getElementById('sc-edit-id').value   = tr.dataset.id;
            document.getElementById('sc-numero').value    = tr.dataset.numero;
            document.getElementById('sc-codigo').value    = tr.dataset.codigo;
            document.getElementById('sc-nombre').value    = tr.dataset.nombre;
            document.getElementById('sc-provincia').value = tr.dataset.provincia;
            document.getElementById('sc-novitec').value   = tr.dataset.novitec;
            var activa = tr.dataset.activa === '1';
            document.getElementById('sc-activa').checked = activa;
            document.getElementById('sc-activa-label').textContent = activa ? 'Sí' : 'No';
            document.getElementById('sc-form-titulo').textContent = 'Editando Sucursal';
            document.querySelector('.seccion-icon i').className = 'bi bi-pencil';
            document.getElementById('btn-sc-cancelar').style.display = '';
            document.getElementById('sc-msg').style.display = 'none';
            document.getElementById('sc-numero').disabled = true;
            document.getElementById('sc-codigo').disabled = true;
            document.querySelector('.sc-form-box').scrollIntoView({ behavior: 'smooth' });
        }

        function scGuardar() {
            var id      = document.getElementById('sc-edit-id').value.trim();
            var numero  = document.getElementById('sc-numero').value.trim();
            var codigo  = document.getElementById('sc-codigo').value.trim();
            var nombre  = document.getElementById('sc-nombre').value.trim();
            var prov    = document.getElementById('sc-provincia').value.trim();
            var novitec = document.getElementById('sc-novitec').value.trim();
            var activa  = document.getElementById('sc-activa').checked ? 1 : 0;

            if (!id && (!numero || isNaN(parseInt(numero)))) { scMostrarMsg('err', 'El número de sucursal es obligatorio.'); return; }
            if (!id && !codigo) { scMostrarMsg('err', 'El código es obligatorio.'); return; }
            if (!nombre) { scMostrarMsg('err', 'El nombre es obligatorio.'); return; }

            var fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            if (id) fd.append('id', id);
            fd.append('numero',          numero);
            fd.append('codigo',          codigo);
            fd.append('nombre',          nombre);
            fd.append('provincia',       prov);
            fd.append('novitec_sucursal', novitec);
            fd.append('activa',          activa);

            var btn = document.getElementById('btn-sc-guardar');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Guardando...';

            // Ruteo dinámico hacia Laravel
            var url = id
                ? '{{ route("sucursales_cliente.actualizar") }}'
                : '{{ route("sucursales_cliente.crear") }}';

            fetch(url, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Guardar';
                    if (!data.ok) { scMostrarMsg('err', data.error || 'Error al guardar.'); return; }
                    scMostrarMsg('ok', data.mensaje || 'Guardado correctamente.');
                    scActualizarTabla(data.sucursal, id ? 'update' : 'insert');
                    if (!id) scCancelar();
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Guardar';
                    scMostrarMsg('err', 'Error de conexión.');
                });
        }

        function scToggle(id, activar, btn) {
            if (!confirm((activar ? '¿Activar' : '¿Desactivar') + ' esta sucursal?')) return;
            var fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('id', id);
            fd.append('activa', activar);

            fetch('{{ route("sucursales_cliente.toggle") }}', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.ok) { alert(data.error || 'Error.'); return; }
                    scActualizarTabla(data.sucursal, 'update');
                })
                .catch(function() { alert('Error de conexión.'); });
        }

        // Las funciones scActualizarTabla, esc, scSetFiltro, scFiltrar y scActualizarCount
        // se mantienen estructuralmente idénticas a tu vanilla JS.

        function scActualizarTabla(s, modo) {
            var tbody = document.getElementById('sc-tbody');
            var nroStr = String(s.numero).padStart(3, '0');
            var activaBadge = s.activa
                ? '<span class="badge-activa">Activa</span>'
                : '<span class="badge-inactiva">Inactiva</span>';
            var btnToggle = s.activa
                ? '<button class="btn-toggle-row desactivar" onclick="scToggle(' + s.id + ', 0, this)"><i class="bi bi-eye-slash me-1"></i>Desactivar</button>'
                : '<button class="btn-toggle-row activar" onclick="scToggle(' + s.id + ', 1, this)"><i class="bi bi-eye me-1"></i>Activar</button>';

            var nuevaFila = '<tr data-id="' + s.id + '" data-numero="' + s.numero + '" data-codigo="' + esc(s.codigo) + '" data-nombre="' + esc(s.nombre) + '" data-provincia="' + esc(s.provincia||'') + '" data-novitec="' + esc(s.novitec_sucursal||'') + '" data-activa="' + (s.activa ? '1' : '0') + '">' +
                '<td><strong>' + nroStr + '</strong></td>' +
                '<td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">' + esc(s.codigo) + '</code></td>' +
                '<td>' + esc(s.nombre) + '</td>' +
                '<td style="color:#64748b;">' + esc(s.provincia || '-') + '</td>' +
                '<td style="color:#64748b;">' + esc(s.novitec_sucursal || '-') + '</td>' +
                '<td>' + activaBadge + '</td>' +
                '<td style="display:flex;gap:6px;"><button class="btn-editar-row" onclick="scEditar(this)"><i class="bi bi-pencil me-1"></i>Editar</button>' + btnToggle + '</td>' +
                '</tr>';

            if (modo === 'update') {
                var existente = tbody.querySelector('tr[data-id="' + s.id + '"]');
                if (existente) {
                    existente.outerHTML = nuevaFila;
                }
            } else {
                var empty = tbody.querySelector('.sc-empty');
                if (empty) empty.closest('tr').remove();
                tbody.insertAdjacentHTML('beforeend', nuevaFila);
            }
            scFiltrar();
            scActualizarCount();
        }

        function esc(str) {
            return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function scSetFiltro(f) {
            _scFiltroActual = f;
            ['todas','activas','inact'].forEach(function(k) {
                document.getElementById('filter-' + k).classList.toggle('active', k === (f === 'inactivas' ? 'inact' : f));
            });
            scFiltrar();
        }

        function scFiltrar() {
            var q = (document.getElementById('sc-buscar').value || '').toLowerCase().trim();
            var filas = document.getElementById('sc-tbody').querySelectorAll('tr[data-id]');
            var visible = 0;
            filas.forEach(function(tr) {
                var activa = tr.dataset.activa === '1';
                var matchEstado = _scFiltroActual === 'todas'
                    || (_scFiltroActual === 'activas' && activa)
                    || (_scFiltroActual === 'inactivas' && !activa);
                var matchQ = !q
                    || tr.dataset.numero.includes(q)
                    || tr.dataset.nombre.toLowerCase().includes(q)
                    || tr.dataset.codigo.toLowerCase().includes(q)
                    || (tr.dataset.provincia || '').toLowerCase().includes(q);
                var show = matchEstado && matchQ;
                tr.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            scActualizarCount(visible);
        }

        function scActualizarCount(visible) {
            var total = document.getElementById('sc-tbody').querySelectorAll('tr[data-id]').length;
            var el = document.getElementById('sc-count');
            if (visible !== undefined && visible !== total) {
                el.textContent = visible + ' de ' + total + ' sucursal(es)';
            } else {
                el.textContent = total + ' sucursal(es) en total';
            }
        }
    </script>
@endpush
