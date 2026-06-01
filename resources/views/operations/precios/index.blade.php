@extends('layouts.app')
@section('titulo', 'Catálogo de Precios y Tipos de Servicios')

@push('css_adicional')
    <style>
        /* Estilos legacy integrados */
        .cat-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 24px; }
        .cat-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
        .cat-hdr-text h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; }
        .cat-hdr-text p { margin: 0; color: #64748b; font-size: 14px; }
        .cat-tabs { display: flex; gap: 8px; margin-bottom: 24px; }
        .cat-tab { padding: 10px 24px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; color: #475569; font-weight: 700; font-size: 13.5px; cursor: pointer; transition: all .2s; }
        .cat-tab:hover { background: #e2e8f0; }
        .cat-tab.activo { background: #2563eb; color: #fff; border-color: #2563eb; }
        .cat-panel { display: none; }
        .cat-panel.activo { display: block; }
        .cat-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; }
        .cat-card-hdr { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; }
        .cat-card-hdr h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; }
        .btn-nuevo { background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-nuevo:hover { background: #059669; }
        .cat-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .cat-table th { text-align: left; padding: 12px 20px; background: #f8fafc; color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #e2e8f0; }
        .cat-table td { padding: 12px 20px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
        .cat-table tr:hover td { background: #f8fafc; }
        .cat-table tr:last-child td { border-bottom: none; }
        .btn-action { background: none; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b; transition: all .15s; }
        .btn-action:hover { background: #e2e8f0; color: #0f172a; }
        .btn-action.del:hover { background: #fef2f2; color: #dc2626; }
        .badge { padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .badge.act { background: #dcfce7; color: #166534; }
        .badge.inact { background: #f1f5f9; color: #94a3b8; }
        .cat-empty { padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); backdrop-filter: blur(2px); z-index: 9999; display: none; align-items: center; justify-content: center; }
        .modal-overlay.activo { display: flex; }
        .modal-box { background: #fff; width: 100%; max-width: 500px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,.15); display: flex; flex-direction: column; }
        .m-hdr { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .m-hdr h3 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
        .m-close { background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 0; }
        .m-close:hover { color: #dc2626; }
        .m-body { padding: 24px; }
        .m-ftr { padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 16px 16px; }
        .campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .campo label { font-size: 13px; font-weight: 600; color: #475569; }
        .campo input, .campo select, .campo textarea { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; transition: border-color .2s; }
        .campo input:focus, .campo select:focus, .campo textarea:focus { outline: none; border-color: #2563eb; }
        .req { color: #ef4444; }
        .btn-sec { padding: 9px 16px; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 600; color: #475569; cursor: pointer; }
        .btn-ok { padding: 9px 16px; background: #2563eb; border: none; border-radius: 8px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-ok:hover { background: #1d4ed8; }
        .msg-box { display: none; padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .desc-text { font-size: 12px; color: #64748b; display: block; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
@endpush

@section('contenido')
    <div class="cat-wrap">
        <div class="cat-hdr">
            <div class="cat-hdr-text">
                <h2><i class="bi bi-tags me-2" style="color:#2563eb;"></i>Catálogo de Precios y Tipos de Servicios</h2>
                <p>Define los costos base y clasificaciones para las órdenes técnicas.</p>
            </div>
        </div>

        <div class="cat-tabs">
            <button class="cat-tab activo" onclick="catTab('precios', this)">Precios Estándar</button>
            <button class="cat-tab" onclick="catTab('tipos', this)">Tipos de Servicio</button>
        </div>

        <div class="cat-panel activo" id="panel-precios">
            <div class="cat-card">
                <div class="cat-card-hdr">
                    <h3><i class="bi bi-currency-dollar me-2" style="color:#64748b;"></i>Precios Estándar Registrados ({{ count($precios) }})</h3>
                    <button class="btn-nuevo" onclick="abrirModalPrecio()">
                        <i class="bi bi-plus-circle"></i> Nuevo Precio
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="cat-table">
                        <thead>
                        <tr>
                            <th>Servicio / Reparación</th>
                            <th>Descripción</th>
                            <th>Precio Sugerido</th>
                            <th>Estado</th>
                            <th style="width:100px;text-align:right;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="precios-tbody">
                        @forelse($precios as $p)
                            <tr data-row="precio">
                                <td><strong>{{ $p->servicio }}</strong></td>
                                <td><span class="desc-text" title="{{ $p->descripcion }}">{{ $p->descripcion ?: '-' }}</span></td>
                                <td><strong>${{ number_format($p->precio, 2) }}</strong></td>
                                <td><span class="badge {{ $p->activo ? 'act' : 'inact' }}">{{ $p->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                <td style="text-align:right;">
                                    <button class="btn-action" title="Editar" onclick="abrirModalPrecio({{ json_encode($p) }})"><i class="bi bi-pencil"></i></button>
                                    <button class="btn-action del" title="Eliminar" onclick="eliminarRegistro('precio', {{ $p->id }})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="cat-empty">No hay precios estándar registrados.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="precios-pager" style="padding: 10px 20px 20px;"></div>
            </div>
        </div>

        <div class="cat-panel" id="panel-tipos">
            <div class="cat-card">
                <div class="cat-card-hdr">
                    <h3><i class="bi bi-ui-radios me-2" style="color:#64748b;"></i>Tipos de Servicio ({{ count($tipos) }})</h3>
                    <button class="btn-nuevo" onclick="abrirModalTipo()">
                        <i class="bi bi-plus-circle"></i> Nuevo Tipo
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="cat-table">
                        <thead>
                        <tr>
                            <th>Nombre del Tipo</th>
                            <th>Descripción</th>
                            <th>Precio Base ($)</th>
                            <th>Estado</th>
                            <th style="width:100px;text-align:right;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="tipos-tbody">
                        @forelse($tipos as $t)
                            <tr data-row="tipo">
                                <td><strong>{{ $t->nombre }}</strong></td>
                                <td><span class="desc-text" title="{{ $t->descripcion }}">{{ $t->descripcion ?: '-' }}</span></td>
                                <td><strong>${{ number_format($t->precio, 2) }}</strong></td>
                                <td><span class="badge {{ $t->activo ? 'act' : 'inact' }}">{{ $t->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                <td style="text-align:right;">
                                    <button class="btn-action" title="Editar" onclick="abrirModalTipo({{ json_encode($t) }})"><i class="bi bi-pencil"></i></button>
                                    <button class="btn-action del" title="Eliminar" onclick="eliminarRegistro('tipo', {{ $t->id }})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="cat-empty">No hay tipos de servicio registrados.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="tipos-pager" style="padding: 10px 20px 20px;"></div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-precio">
        <div class="modal-box">
            <div class="m-hdr">
                <h3 id="mp-title">Nuevo Precio Estándar</h3>
                <button class="m-close" onclick="cerrarModal('modal-precio')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="m-body">
                <div id="mp-msg" class="msg-box err"></div>
                <input type="hidden" id="p-id">

                <div class="campo">
                    <label>Nombre del Servicio <span class="req">*</span></label>
                    <input type="text" id="p-servicio" maxlength="255" placeholder="Ej: Cambio de Pantalla Laptop">
                </div>

                <div class="campo">
                    <label>Precio Sugerido (USD) <span class="req">*</span></label>
                    <input type="number" id="p-precio" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="campo">
                    <label>Descripción</label>
                    <textarea id="p-desc" placeholder="Detalles o consideraciones sobre el servicio..." rows="3"></textarea>
                </div>

                <div class="campo" style="margin-bottom:0;">
                    <label>Estado</label>
                    <select id="p-activo">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="m-ftr">
                <button class="btn-sec" onclick="cerrarModal('modal-precio')">Cancelar</button>
                <button class="btn-ok" id="btn-gp" onclick="guardarPrecio()"><i class="bi bi-floppy"></i> Guardar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-tipo">
        <div class="modal-box">
            <div class="m-hdr">
                <h3 id="mt-title">Nuevo Tipo de Servicio</h3>
                <button class="m-close" onclick="cerrarModal('modal-tipo')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="m-body">
                <div id="mt-msg" class="msg-box err"></div>
                <input type="hidden" id="t-id">

                <div class="campo">
                    <label>Nombre del Tipo de Servicio <span class="req">*</span></label>
                    <input type="text" id="t-nombre" maxlength="255" placeholder="Ej: Mantenimiento Preventivo">
                </div>

                <div class="campo">
                    <label>Precio Base (USD) <span class="req">*</span></label>
                    <input type="number" id="t-precio" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="campo">
                    <label>Descripción</label>
                    <textarea id="t-desc" placeholder="Detalles de lo que incluye..." rows="3"></textarea>
                </div>

                <div class="campo" style="margin-bottom:0;">
                    <label>Estado</label>
                    <select id="t-activo">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="m-ftr">
                <button class="btn-sec" onclick="cerrarModal('modal-tipo')">Cancelar</button>
                <button class="btn-ok" id="btn-gt" onclick="guardarTipo()"><i class="bi bi-floppy"></i> Guardar</button>
            </div>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        function catTab(panel, btn) {
            document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('activo'));
            document.querySelectorAll('.cat-panel').forEach(p => p.classList.remove('activo'));
            btn.classList.add('activo');
            document.getElementById('panel-' + panel).classList.add('activo');
        }

        function cerrarModal(id) {
            document.getElementById(id).classList.remove('activo');
        }

        function showError(id, msg) {
            const el = document.getElementById(id);
            el.textContent = msg; el.style.display = 'block';
        }

        // Lógica de Precios Estándar
        function abrirModalPrecio(datos = null) {
            document.getElementById('mp-msg').style.display = 'none';
            const id = document.getElementById('p-id');
            const servicio = document.getElementById('p-servicio');
            const precio = document.getElementById('p-precio');
            const desc = document.getElementById('p-desc');
            const activo = document.getElementById('p-activo');

            if (datos) {
                document.getElementById('mp-title').textContent = 'Editar Precio Estándar';
                id.value = datos.id;
                servicio.value = datos.servicio;
                precio.value = parseFloat(datos.precio).toFixed(2);
                desc.value = datos.descripcion || '';
                activo.value = datos.activo;
            } else {
                document.getElementById('mp-title').textContent = 'Nuevo Precio Estándar';
                id.value = ''; servicio.value = ''; precio.value = '0.00'; desc.value = ''; activo.value = '1';
            }

            document.getElementById('modal-precio').classList.add('activo');
        }

        async function guardarPrecio() {
            const servicio = document.getElementById('p-servicio').value.trim();
            const precio = document.getElementById('p-precio').value;

            if(!servicio || precio === '') { showError('mp-msg', 'El servicio y el precio son obligatorios.'); return; }

            const id = document.getElementById('p-id').value;
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', id ? 'editar' : 'crear');
            if(id) fd.append('id', id);
            fd.append('servicio', servicio);
            fd.append('precio', precio);
            fd.append('descripcion', document.getElementById('p-desc').value.trim());
            fd.append('activo', document.getElementById('p-activo').value);

            const btn = document.getElementById('btn-gp');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("precios.guardar") }}', { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) location.reload();
                else showError('mp-msg', d.error);
            } catch(e) { showError('mp-msg', 'Error de conexión.'); }
            finally { btn.disabled = false; }
        }

        // Lógica de Tipos de Servicio
        function abrirModalTipo(datos = null) {
            document.getElementById('mt-msg').style.display = 'none';
            const id = document.getElementById('t-id');
            const nombre = document.getElementById('t-nombre');
            const precio = document.getElementById('t-precio');
            const desc = document.getElementById('t-desc');
            const activo = document.getElementById('t-activo');

            if (datos) {
                document.getElementById('mt-title').textContent = 'Editar Tipo de Servicio';
                id.value = datos.id;
                nombre.value = datos.nombre;
                precio.value = parseFloat(datos.precio).toFixed(2);
                desc.value = datos.descripcion || '';
                activo.value = datos.activo;
            } else {
                document.getElementById('mt-title').textContent = 'Nuevo Tipo de Servicio';
                id.value = ''; nombre.value = ''; precio.value = '0.00'; desc.value = ''; activo.value = '1';
            }

            document.getElementById('modal-tipo').classList.add('activo');
        }

        async function guardarTipo() {
            const nombre = document.getElementById('t-nombre').value.trim();
            const precio = document.getElementById('t-precio').value;

            if(!nombre || precio === '') { showError('mt-msg', 'El nombre y el precio base son obligatorios.'); return; }

            const id = document.getElementById('t-id').value;
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', id ? 'editar' : 'crear');
            if(id) fd.append('id', id);
            fd.append('nombre', nombre);
            fd.append('precio', precio);
            fd.append('descripcion', document.getElementById('t-desc').value.trim());
            fd.append('activo', document.getElementById('t-activo').value);

            const btn = document.getElementById('btn-gt');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("tipos_servicio.guardar") }}', { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) location.reload();
                else showError('mt-msg', d.error);
            } catch(e) { showError('mt-msg', 'Error de conexión.'); }
            finally { btn.disabled = false; }
        }

        async function eliminarRegistro(contexto, id) {
            if(!confirm(`¿Confirma que desea eliminar el registro seleccionado?`)) return;

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', 'eliminar');
            fd.append('id', id);

            const ruta = contexto === 'precio' ? '{{ route("precios.guardar") }}' : '{{ route("tipos_servicio.guardar") }}';

            try {
                const r = await fetch(ruta, { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) location.reload();
                else alert(d.error);
            } catch(e) { alert('Error de conexión.'); }
        }

        let _preciosPager = null;
        let _tiposPager = null;
        document.addEventListener('DOMContentLoaded', () => {
            _preciosPager = new SgnPager({
                containerSelector: '#precios-tbody',
                itemSelector: 'tr[data-row="precio"]',
                pagerContainerSelector: '#precios-pager',
                pageSize: 10
            });
            _tiposPager = new SgnPager({
                containerSelector: '#tipos-tbody',
                itemSelector: 'tr[data-row="tipo"]',
                pagerContainerSelector: '#tipos-pager',
                pageSize: 10
            });
        });
    </script>
@endpush
