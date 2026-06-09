@extends('layouts.app')

@section('titulo', 'Centros Autorizados (CAS)')

@push('css_adicional')
    <style>
        /* CSS idéntico al legacy  */
        .cas-wrap { padding: 28px 24px; max-width: 1050px; margin: 0 auto; }
        .cas-titulo h2 { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 4px; }
        .cas-titulo p  { color: #94a3b8; font-size: 13px; margin: 0 0 24px; }
        .cas-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.06); margin-bottom: 24px; overflow: hidden; }
        .cas-card-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 22px; background: linear-gradient(135deg,#f0fdf4,#dcfce7); border-bottom: 1px solid #bbf7d0; }
        .cas-card-header h3 { font-size: 14px; font-weight: 700; color: #166534; margin: 0; }
        .cas-card-body { padding: 22px; }
        .cas-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .cas-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .cas-grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; }
        .campo { display: flex; flex-direction: column; gap: 6px; }
        .campo label.campo-lbl { font-size: 13px; font-weight: 600; color: #374151; }
        .campo input, .campo textarea { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 9px 12px; font-size: 13.5px; color: #0f172a; background: #f8fafc; font-family: inherit; transition: border-color .2s, box-shadow .2s; }
        .campo input:focus, .campo textarea:focus { outline: none; border-color: #16a34a; background: #fff; box-shadow: 0 0 0 3px rgba(22,163,74,.1); }
        .campo textarea { resize: vertical; min-height: 64px; }
        .req { color: #ef4444; }
        .ms-wrap { position: relative; }
        .ms-box { min-height: 38px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 4px 8px; display: flex; flex-wrap: wrap; gap: 4px; align-items: center; cursor: pointer; background: #f8fafc; transition: border-color .2s; }
        .ms-box:hover { border-color: #16a34a; }
        .ms-tag { display: inline-flex; align-items: center; gap: 4px; background: #dcfce7; color: #166534; border-radius: 20px; padding: 2px 10px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .ms-tag-x { cursor: pointer; font-size: 15px; line-height: 1; color: #16a34a; margin-left: 2px; font-weight: 400; }
        .ms-tag-x:hover { color: #991b1b; }
        .ms-placeholder { font-size: 13px; color: #94a3b8; padding: 2px 4px; }
        .ms-dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 200; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,.08); max-height: 200px; overflow-y: auto; display: none; }
        .ms-dropdown.open { display: block; }
        .ms-opt { padding: 8px 12px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #374151; border-bottom: 1px solid #f1f5f9; }
        .ms-opt:last-child { border-bottom: none; }
        .ms-opt:hover { background: #f0fdf4; }
        .ms-opt.sel { color: #166534; background: #f0fdf4; }
        .ms-chk { width: 15px; height: 15px; border: 1.5px solid #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; flex-shrink: 0; color: #fff; }
        .ms-opt.sel .ms-chk { background: #16a34a; border-color: #16a34a; }
        .sin-marcas { color: #94a3b8; font-size: 12.5px; padding: 4px 2px; }
        .cas-msg { display: none; padding: 11px 16px; border-radius: 9px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .cas-msg.ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .cas-msg.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .cas-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: all .15s; }
        .cas-btn-green { background: #16a34a; color: #fff; }
        .cas-btn-green:hover { background: #15803d; }
        .cas-btn-gray  { background: #f1f5f9; color: #475569; }
        .cas-btn-gray:hover  { background: #e2e8f0; }
        .cas-btn-sm    { padding: 5px 12px; font-size: 12px; }
        .cas-table-wrap { overflow-x: auto; }
        table.cas-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
        .cas-tbl th { background: #f8fafc; padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e2e8f0; }
        .cas-tbl td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
        .cas-tbl tr:hover td { background: #f8fafc; }
        .badge-activo   { background: #dcfce7; color: #166534; padding: 2px 9px; border-radius: 20px; font-size: 11.5px; font-weight: 700; }
        .badge-inactivo { background: #f1f5f9; color: #94a3b8;  padding: 2px 9px; border-radius: 20px; font-size: 11.5px; font-weight: 700; }
        .tag-marca { display:inline-block; background:#dbeafe; color:#1e40af; border-radius:20px; padding:2px 9px; font-size:11.5px; font-weight:600; margin:1px 2px; }
        .cas-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9000; align-items: center; justify-content: center; }
        .cas-overlay.visible { display: flex; }
        .cas-modal { background: #fff; border-radius: 16px; padding: 28px; width: 700px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .cas-modal h3 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 20px; }
        @media(max-width:600px){ .cas-grid, .cas-grid-3, .cas-grid-4 { grid-template-columns: 1fr; } }
    </style>

    <script>
        const CAS_MARCAS_DATA = @json($marcas_list);
    </script>
@endpush

@section('contenido')
    <div class="cas-wrap">
        <div class="cas-titulo">
            <h2><i class="bi bi-building-check me-2" style="color:#16a34a"></i>Centros de Servicio Autorizado (CAS)</h2>
            <p>Registro de centros a los que se envían dispositivos cuando la garantía no es cubierta por Novitecnología.</p>
        </div>

        <div class="cas-card">
            <div class="cas-card-header">
                <h3><i class="bi bi-plus-circle me-2"></i>Registrar nuevo CAS</h3>
            </div>
            <div class="cas-card-body">
                <div id="cas-msg-nuevo" class="cas-msg"></div>
                <div class="cas-grid" style="margin-bottom:16px">
                    <div class="campo">
                        <label class="campo-lbl">Nombre del cas <span class="req">*</span></label>
                        <input type="text" id="cas-nombre" placeholder="Ej: Samsung Service Center Quito" maxlength="120">
                    </div>
                    <div class="campo">
                        <label class="campo-lbl">Marcas que atiende</label>
                        <div class="ms-wrap" id="ms-wrap-nuevo">
                            <div class="ms-box" id="ms-box-nuevo" onclick="msToggle('nuevo')">
                                <span class="ms-placeholder" id="ms-ph-nuevo">Seleccionar marcas...</span>
                            </div>
                            <div class="ms-dropdown" id="ms-dd-nuevo">
                                @if ($marcas_list->isEmpty())
                                    <div class="ms-opt"><span class="sin-marcas">No hay marcas en Inventario.</span></div>
                                @else
                                    @foreach ($marcas_list as $m)
                                        <div class="ms-opt" data-id="{{ $m->id }}" data-nombre="{{ $m->nombre }}" onclick="msToggleOpt(this,'nuevo')">
                                            <div class="ms-chk"></div>
                                            {{ $m->nombre }}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cas-grid-4" style="margin-bottom:16px">
                    <div class="campo">
                        <label class="campo-lbl">Prefijo Órdenes</label>
                        <input type="text" id="cas-prefijo" placeholder="Ej: UIO, GYE" maxlength="10">
                    </div>
                    <div class="campo">
                        <label class="campo-lbl">Teléfono</label>
                        <input type="text" id="cas-telefono" placeholder="02-XXX-XXXX" maxlength="30">
                    </div>
                    <div class="campo">
                        <label class="campo-lbl">Correo electrónico</label>
                        <input type="email" id="cas-correo" placeholder="cas@ejemplo.com" maxlength="120">
                    </div>
                    <div class="campo">
                        <label class="campo-lbl">Ciudad</label>
                        <input type="text" id="cas-ciudad" placeholder="Quito, Guayaquil…" maxlength="80">
                    </div>
                </div>
                <div class="cas-grid" style="margin-bottom:16px">
                    <div class="campo">
                        <label class="campo-lbl">Dirección</label>
                        <input type="text" id="cas-direccion" placeholder="Av. / Calle, N°, sector" maxlength="200">
                    </div>
                    <div class="campo">
                        <label class="campo-lbl">Persona de contacto</label>
                        <input type="text" id="cas-contacto" placeholder="Nombre del encargado" maxlength="100">
                    </div>
                </div>
                <div class="campo" style="margin-bottom:18px">
                    <label class="campo-lbl">Notas adicionales</label>
                    <textarea id="cas-notas" placeholder="Horarios de atención, proceso de envío, observaciones…"></textarea>
                </div>
                <button class="cas-btn cas-btn-green" onclick="guardarCAS()">
                    <i class="bi bi-floppy"></i> Guardar cas
                </button>
            </div>
        </div>

        <div class="cas-card">
            <div class="cas-card-header">
                <h3><i class="bi bi-list-ul me-2"></i>CAS registrados ({{ count($cas_list) }})</h3>
                <input type="text" id="cas-buscar" oninput="filtrarCAS()" placeholder="Buscar…"
                       style="padding:6px 12px;border:1.5px solid #bbf7d0;border-radius:7px;font-size:13px;width:200px;background:#fff">
            </div>
            <div class="cas-card-body" style="padding:0">
                <div class="cas-table-wrap">
                    <table class="cas-tbl" id="cas-tabla">
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Prefijo</th>
                            <th>Marcas que atiende</th>
                            <th>Ciudad</th>
                            <th>Teléfono</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @if ($cas_list->isEmpty())
                            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px">No hay cas registrados aún.</td></tr>
                        @else
                            @foreach ($cas_list as $c)
                                @php
                                    $tags = '';
                                    if ($c->marca) {
                                        $ids_guardados = array_map('trim', explode(',', $c->marca));
                                        foreach ($marcas_list as $m) {
                                            if (in_array((string)$m->id, $ids_guardados)) {
                                                $tags .= '<span class="tag-marca">'.htmlspecialchars($m->nombre).'</span>';
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td><strong>{{ $c->nombre }}</strong></td>
                                    <td><code style="font-weight:bold;color:#16a34a">{{ $c->prefijo ?? '—' }}</code></td>
                                    <td>{!! $tags ?: '<span style="color:#94a3b8">—</span>' !!}</td>
                                    <td>{{ $c->ciudad ?? '—' }}</td>
                                    <td>{{ $c->telefono ?? '—' }}</td>
                                    <td>{{ $c->contacto ?? '—' }}</td>
                                    <td>
                                        {!! $c->activo
                                            ? '<span class="badge-activo">Activo</span>'
                                            : '<span class="badge-inactivo">Inactivo</span>' !!}
                                    </td>
                                    <td>
                                        <button class="cas-btn cas-btn-gray cas-btn-sm"
                                                onclick="editarCAS({{ json_encode($c) }})">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="cas-overlay" id="cas-modal-overlay">
        <div class="cas-modal">
            <h3><i class="bi bi-pencil-square me-2" style="color:#16a34a"></i>Editar CAS</h3>
            <input type="hidden" id="edit-cas-id">
            <div id="cas-msg-edit" class="cas-msg"></div>
            <div class="cas-grid" style="margin-bottom:16px">
                <div class="campo">
                    <label class="campo-lbl">Nombre <span class="req">*</span></label>
                    <input type="text" id="edit-cas-nombre" maxlength="120">
                </div>
                <div class="campo">
                    <label class="campo-lbl">Marcas que atiende</label>
                    <div class="ms-wrap" id="ms-wrap-edit">
                        <div class="ms-box" id="ms-box-edit" onclick="msToggle('edit')">
                            <span class="ms-placeholder" id="ms-ph-edit">Seleccionar marcas...</span>
                        </div>
                        <div class="ms-dropdown" id="ms-dd-edit">
                            @if ($marcas_list->isEmpty())
                                <div class="ms-opt"><span class="sin-marcas">No hay marcas en Inventario.</span></div>
                            @else
                                @foreach ($marcas_list as $m)
                                    <div class="ms-opt" data-id="{{ $m->id }}" data-nombre="{{ $m->nombre }}" onclick="msToggleOpt(this,'edit')">
                                        <div class="ms-chk"></div>
                                        {{ $m->nombre }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="cas-grid-4" style="margin-bottom:16px">
                <div class="campo">
                    <label class="campo-lbl">Prefijo Órdenes</label>
                    <input type="text" id="edit-cas-prefijo" maxlength="10">
                </div>
                <div class="campo">
                    <label class="campo-lbl">Teléfono</label>
                    <input type="text" id="edit-cas-telefono" maxlength="30">
                </div>
                <div class="campo">
                    <label class="campo-lbl">Correo</label>
                    <input type="email" id="edit-cas-correo" maxlength="120">
                </div>
                <div class="campo">
                    <label class="campo-lbl">Ciudad</label>
                    <input type="text" id="edit-cas-ciudad" maxlength="80">
                </div>
            </div>
            <div class="cas-grid" style="margin-bottom:16px">
                <div class="campo">
                    <label class="campo-lbl">Dirección</label>
                    <input type="text" id="edit-cas-direccion" maxlength="200">
                </div>
                <div class="campo">
                    <label class="campo-lbl">Contacto</label>
                    <input type="text" id="edit-cas-contacto" maxlength="100">
                </div>
            </div>
            <div class="campo" style="margin-bottom:16px">
                <label class="campo-lbl">Notas</label>
                <textarea id="edit-cas-notas"></textarea>
            </div>
            <div class="campo" style="margin-bottom:20px">
                <label class="campo-lbl">Estado</label>
                <select id="edit-cas-activo"
                        style="border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:13.5px;background:#f8fafc;font-family:inherit">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button class="cas-btn cas-btn-gray" onclick="cerrarModal()">Cancelar</button>
                <button class="cas-btn cas-btn-green" onclick="guardarEdicionCAS()">
                    <i class="bi bi-floppy"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        // Selector marcas dropdown... (Funciones visuales identicas al legacy)
        function msToggle(ctx) { const dd = document.getElementById('ms-dd-' + ctx); dd.classList.toggle('open'); }
        function msToggleOpt(el, ctx) { el.classList.toggle('sel'); el.querySelector('.ms-chk').textContent = el.classList.contains('sel') ? '✓' : ''; msRenderTags(ctx); }
        function msRenderTags(ctx) {
            const box = document.getElementById('ms-box-' + ctx); const ph = document.getElementById('ms-ph-' + ctx);
            const sel = document.querySelectorAll('#ms-dd-' + ctx + ' .ms-opt.sel');
            box.querySelectorAll('.ms-tag').forEach(t => t.remove());
            if (sel.length === 0) { ph.style.display = ''; return; }
            ph.style.display = 'none';
            sel.forEach(opt => {
                const tag = document.createElement('span'); tag.className = 'ms-tag';
                tag.innerHTML = opt.dataset.nombre + ' <span class="ms-tag-x" onclick="msRemoveTag(event,\''+ ctx +'\',\''+ opt.dataset.id +'\')" >×</span>';
                box.insertBefore(tag, ph);
            });
        }
        function msRemoveTag(e, ctx, id) { e.stopPropagation(); const opt = document.querySelector('#ms-dd-' + ctx + ' .ms-opt[data-id="' + id + '"]'); if (opt) { opt.classList.remove('sel'); opt.querySelector('.ms-chk').textContent = ''; } msRenderTags(ctx); }
        function msGetIds(ctx) { const ids = []; document.querySelectorAll('#ms-dd-' + ctx + ' .ms-opt.sel').forEach(el => ids.push(el.dataset.id)); return ids.join(','); }
        function msSetIds(ctx, idsStr) {
            const ids = idsStr ? idsStr.split(',').map(s => s.trim()) : [];
            document.querySelectorAll('#ms-dd-' + ctx + ' .ms-opt').forEach(el => {
                const sel = ids.includes(el.dataset.id); el.classList.toggle('sel', sel); el.querySelector('.ms-chk').textContent = sel ? '✓' : '';
            });
            msRenderTags(ctx);
        }
        function msClear(ctx) { document.querySelectorAll('#ms-dd-' + ctx + ' .ms-opt').forEach(el => { el.classList.remove('sel'); el.querySelector('.ms-chk').textContent = ''; }); msRenderTags(ctx); }
        document.addEventListener('click', function(e) { ['nuevo','edit'].forEach(ctx => { const wrap = document.getElementById('ms-wrap-' + ctx); const dd = document.getElementById('ms-dd-' + ctx); if (wrap && dd && !wrap.contains(e.target)) dd.classList.remove('open'); }); });

        function mostrarMsg(id, ok, txt) {
            const el = document.getElementById(id);
            el.className = 'cas-msg ' + (ok ? 'ok' : 'err');
            el.textContent = txt; el.style.display = 'block';
            if (ok) setTimeout(() => el.style.display = 'none', 3500);
        }

        async function guardarCAS() {
            const nombre = document.getElementById('cas-nombre').value.trim();
            if (!nombre) { mostrarMsg('cas-msg-nuevo', false, 'El nombre es obligatorio.'); return; }

            const fd = new FormData();
            fd.append('_token',    '{{ csrf_token() }}');
            fd.append('accion',    'crear');
            fd.append('nombre',    nombre);
            fd.append('prefijo',   document.getElementById('cas-prefijo').value.trim());
            fd.append('marca',     msGetIds('nuevo'));
            fd.append('telefono',  document.getElementById('cas-telefono').value.trim());
            fd.append('correo',    document.getElementById('cas-correo').value.trim());
            fd.append('ciudad',    document.getElementById('cas-ciudad').value.trim());
            fd.append('direccion', document.getElementById('cas-direccion').value.trim());
            fd.append('contacto',  document.getElementById('cas-contacto').value.trim());
            fd.append('notas',     document.getElementById('cas-notas').value.trim());

            try {
                const r = await fetch('{{ route("cas.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json();
                mostrarMsg('cas-msg-nuevo', d.ok, d.mensaje || d.error);
                if (d.ok) {
                    document.getElementById('cas-nombre').value = '';
                    ['prefijo','telefono','correo','ciudad','direccion','contacto','notas'].forEach(f => document.getElementById('cas-' + f).value = '');
                    msClear('nuevo');
                    setTimeout(() => location.reload(), 1200);
                }
            } catch(e) { mostrarMsg('cas-msg-nuevo', false, 'Error de conexión.'); }
        }

        function editarCAS(data) {
            document.getElementById('edit-cas-id').value        = data.id;
            document.getElementById('edit-cas-nombre').value    = data.nombre || '';
            document.getElementById('edit-cas-prefijo').value   = data.prefijo || '';
            document.getElementById('edit-cas-telefono').value  = data.telefono || '';
            document.getElementById('edit-cas-correo').value    = data.correo || '';
            document.getElementById('edit-cas-ciudad').value    = data.ciudad || '';
            document.getElementById('edit-cas-direccion').value = data.direccion || '';
            document.getElementById('edit-cas-contacto').value  = data.contacto || '';
            document.getElementById('edit-cas-notas').value     = data.notas || '';
            document.getElementById('edit-cas-activo').value    = data.activo;
            msSetIds('edit', data.marca || '');
            document.getElementById('cas-msg-edit').style.display = 'none';
            document.getElementById('cas-modal-overlay').classList.add('visible');
        }

        function cerrarModal() { document.getElementById('cas-modal-overlay').classList.remove('visible'); }

        async function guardarEdicionCAS() {
            const nombre = document.getElementById('edit-cas-nombre').value.trim();
            if (!nombre) { mostrarMsg('cas-msg-edit', false, 'El nombre es obligatorio.'); return; }

            const fd = new FormData();
            fd.append('_token',    '{{ csrf_token() }}');
            fd.append('accion',    'editar');
            fd.append('id',        document.getElementById('edit-cas-id').value);
            fd.append('nombre',    nombre);
            fd.append('prefijo',   document.getElementById('edit-cas-prefijo').value.trim());
            fd.append('marca',     msGetIds('edit'));
            fd.append('telefono',  document.getElementById('edit-cas-telefono').value.trim());
            fd.append('correo',    document.getElementById('edit-cas-correo').value.trim());
            fd.append('ciudad',    document.getElementById('edit-cas-ciudad').value.trim());
            fd.append('direccion', document.getElementById('edit-cas-direccion').value.trim());
            fd.append('contacto',  document.getElementById('edit-cas-contacto').value.trim());
            fd.append('notas',     document.getElementById('edit-cas-notas').value.trim());
            fd.append('activo',    document.getElementById('edit-cas-activo').value);

            try {
                const r = await fetch('{{ route("cas.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json();
                mostrarMsg('cas-msg-edit', d.ok, d.mensaje || d.error);
                if (d.ok) setTimeout(() => { cerrarModal(); location.reload(); }, 1200);
            } catch(e) { mostrarMsg('cas-msg-edit', false, 'Error de conexión.'); }
        }

        document.getElementById('cas-modal-overlay').addEventListener('click', function(e) { if (e.target === this) cerrarModal(); });

        function filtrarCAS() {
            const q = document.getElementById('cas-buscar').value.toLowerCase();
            document.querySelectorAll('#cas-tabla tbody tr').forEach(tr => {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Validaciones nuevo CAS
            setupDynamicValidation(document.getElementById('cas-telefono'), EcuadorianValidator.validarTelefono, (v) => {
                if (/[^\d]/.test(v)) return 'El teléfono sólo debe contener números.';
                return 'El teléfono debe ser un celular de 10 dígitos (ej: 0987654321) o convencional de 9 dígitos (ej: 022345678) de Ecuador.';
            });
            setupDynamicValidation(document.getElementById('cas-correo'), EcuadorianValidator.validarEmail, (v) => {
                return 'El correo electrónico no tiene un formato válido.';
            });

            // Validaciones edición CAS
            setupDynamicValidation(document.getElementById('edit-cas-telefono'), EcuadorianValidator.validarTelefono, (v) => {
                if (/[^\d]/.test(v)) return 'El teléfono sólo debe contener números.';
                return 'El teléfono debe ser un celular de 10 dígitos (ej: 0987654321) o convencional de 9 dígitos (ej: 022345678) de Ecuador.';
            });
            setupDynamicValidation(document.getElementById('edit-cas-correo'), EcuadorianValidator.validarEmail, (v) => {
                return 'El correo electrónico no tiene un formato válido.';
            });
        });
    </script>
@endpush
