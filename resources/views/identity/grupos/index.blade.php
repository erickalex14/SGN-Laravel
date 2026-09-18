@extends('layouts.app')

@section('titulo', 'Grupos de Acceso')

@push('css_adicional')
    <style>
        /* CSS Extraido integramente de modulo-grupos-acceso.php */
        .ga-wrap { padding: 28px 24px; max-width: 1100px; margin: 0 auto; }
        .ga-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
        .ga-hdr-text h2 { margin: 0 0 4px; font-size: 22px; font-weight: 800; color: #0f172a; }
        .ga-hdr-text p { margin: 0; color: #64748b; font-size: 13.5px; }
        .btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13.5px; border: none; cursor: pointer; transition: opacity .2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { opacity: .9; }
        .ga-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .ga-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px; transition: border-color .2s, box-shadow .2s; display: flex; flex-direction: column; }
        .ga-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 16px rgba(0,0,0,.04); }
        .ga-c-hdr { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .ga-c-title { margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .ga-badge-sa { font-size: 10px; background: #fef08a; color: #854d0e; padding: 2px 8px; border-radius: 20px; font-weight: 800; letter-spacing: .5px; }
        .ga-c-desc { font-size: 13px; color: #64748b; line-height: 1.5; flex: 1; margin-bottom: 16px; }
        .ga-c-ftr { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: auto; }
        .ga-c-users { font-size: 12px; color: #94a3b8; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        .ga-c-actions { display: flex; gap: 6px; }
        .ga-btn-icon { width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; cursor: pointer; transition: all .15s; }
        .ga-btn-icon:hover { background: #f1f5f9; color: #0f172a; }
        .ga-btn-icon.del:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
        .ga-btn-icon.perm:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
        .ga-empty { text-align: center; padding: 40px; color: #94a3b8; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; font-size: 14px; }
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,.6); backdrop-filter: blur(2px); z-index: 9999; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,.15); display: flex; flex-direction: column; max-height: 90vh; }
        .modal-box.large { max-width: 900px; }
        .modal-hdr { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .modal-hdr h3 { margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; }
        .modal-close { background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 0; line-height: 1; transition: color .2s; }
        .modal-close:hover { color: #ef4444; }
        .modal-body { padding: 24px; overflow-y: auto; }
        .modal-ftr { padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; background: #f8fafc; border-radius: 0 0 16px 16px; }
        .campo { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
        .campo label { font-size: 13px; font-weight: 600; color: #475569; }
        .campo input, .campo textarea { padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; transition: border-color .2s; }
        .campo input:focus, .campo textarea:focus { outline: none; border-color: #2563eb; }
        .campo textarea { resize: vertical; min-height: 80px; }
        .toggle-wrap { display: flex; align-items: center; gap: 12px; margin-top: 8px; }
        .toggle { position: relative; width: 44px; height: 24px; display: inline-block; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #cbd5e1; transition: .3s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; transition: .3s; border-radius: 50%; }
        .toggle input:checked + .slider { background: #2563eb; }
        .toggle input:checked + .slider:before { transform: translateX(20px); }
        .btn-sec { background: #fff; border: 1.5px solid #cbd5e1; color: #475569; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background .2s; }
        .btn-sec:hover { background: #f8fafc; }
        .btn-ok { background: #10b981; color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: opacity .2s; display:flex; gap:6px; align-items:center; }
        .btn-ok:hover { opacity: .9; }
        .msg-box { display: none; padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .msg-box.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .perm-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .perm-table th { background: #f8fafc; padding: 12px; text-align: center; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; font-size: 11.5px; position: sticky; top: -24px; z-index: 10; }
        .perm-table th:first-child { text-align: left; }
        .perm-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .perm-table td:first-child { text-align: left; font-weight: 600; color: #1e293b; text-transform: capitalize; }
        .perm-table tr:hover td { background: #f8fafc; }
        .chk-mod { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }
    </style>
@endpush

@section('contenido')
    <div class="ga-wrap">
        <div class="ga-hdr">
            <div class="ga-hdr-text">
                <h2><i class="bi bi-shield-lock me-2" style="color:#2563eb"></i>Grupos de Acceso</h2>
                <p>Gestiona los roles y permisos del sistema.</p>
            </div>
            <button class="btn-primary" onclick="abrirModalGrupo()">
                <i class="bi bi-plus-lg"></i> Nuevo Grupo
            </button>
        </div>

        @if ($grupos->isEmpty())
            <div class="ga-empty">
                <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:12px;"></i>
                No hay grupos registrados.
            </div>
        @else
            <div class="ga-grid">
                @foreach ($grupos as $g)
                    <div class="ga-card">
                        <div class="ga-c-hdr">
                            <h3 class="ga-c-title">
                                {{ $g->nombre }}
                                @if ($g->es_superadmin)
                                    <span class="ga-badge-sa">SUPERADMIN</span>
                                @endif
                            </h3>
                        </div>
                        <div class="ga-c-desc">{{ $g->descripcion ?: 'Sin descripción.' }}</div>
                        <div class="ga-c-ftr">
                            <div class="ga-c-users">
                                <i class="bi bi-people-fill"></i> {{ $g->usuarios_count }} usuario(s)
                            </div>
                            <div class="ga-c-actions">
                                <button class="ga-btn-icon perm" title="Permisos" onclick="abrirModalPermisos({{ $g->id }}, '{{ addslashes($g->nombre) }}')">
                                    <i class="bi bi-key"></i>
                                </button>
                                <button class="ga-btn-icon" title="Editar" onclick="abrirModalGrupo({{ json_encode($g) }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="ga-btn-icon del" title="Eliminar" onclick="eliminarGrupo({{ $g->id }}, {{ $g->usuarios_count }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="modal-overlay" id="modal-grupo">
        <div class="modal-box">
            <div class="modal-hdr">
                <h3 id="mg-title">Nuevo Grupo</h3>
                <button class="modal-close" onclick="cerrarModal('modal-grupo')"><i class="bi bi-x"></i></button>
            </div>
            <div class="modal-body">
                <div id="mg-msg" class="msg-box"></div>
                <input type="hidden" id="g-id">
                <div class="campo">
                    <label>Nombre del Grupo</label>
                    <input type="text" id="g-nombre" maxlength="100" placeholder="Ej: Técnicos, Administradores...">
                </div>
                <div class="campo">
                    <label>Descripción</label>
                    <textarea id="g-desc" maxlength="255" placeholder="Breve descripción del rol..."></textarea>
                </div>
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="checkbox" id="g-sa">
                        <span class="slider"></span>
                    </label>
                    <span style="font-size:13px;font-weight:600;color:#1e293b;">Es Superadministrador (Acceso Total)</span>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn-sec" onclick="cerrarModal('modal-grupo')">Cancelar</button>
                <button class="btn-ok" id="btn-guardar-g" onclick="guardarGrupo()">
                    <i class="bi bi-floppy"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-permisos">
        <div class="modal-box large">
            <div class="modal-hdr">
                <h3>Permisos: <span id="mp-title-name" style="color:#2563eb"></span></h3>
                <button class="modal-close" onclick="cerrarModal('modal-permisos')"><i class="bi bi-x"></i></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <div id="mp-msg" class="msg-box" style="margin:16px; display:none;"></div>
                <input type="hidden" id="p-grupo-id">

                <div style="padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display:flex; gap:16px;">
                    <button class="btn-sec" style="padding:4px 10px; font-size:12px;" onclick="marcarTodos(true)">Marcar Todos</button>
                    <button class="btn-sec" style="padding:4px 10px; font-size:12px;" onclick="marcarTodos(false)">Desmarcar Todos</button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="perm-table" id="tabla-permisos">
                        <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Ver</th>
                            <th>Crear</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                        </thead>
                        <tbody id="p-tbody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn-sec" onclick="cerrarModal('modal-permisos')">Cerrar</button>
                <button class="btn-ok" id="btn-guardar-p" onclick="guardarPermisos()">
                    <i class="bi bi-shield-check"></i> Guardar Permisos
                </button>
            </div>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        // El listado exacto de módulos legados se mantiene
        const modulos = [
            'ordenes_crear','ordenes_editar','ordenes_buscar',
            'ordenes_mis','ordenes_asignadas','preordenes',
            'informes','presupuestos','solicitar_nc','solicitar_repuesto',
            'reportes','notas_credito','repuestos_admin',
            'inv_productos','inv_marcas','inv_repuestos',
            'precios','caja_general','caja_chica','recuento_b2b',
            'nomina_mis_datos','nomina_admin','solicitudes_vacaciones',
            'sucursales','sucursales_novicompu',
            'empresas','cas','mi_cuenta','usuarios','grupos_acceso'
        ];
        const acciones = ['ver', 'crear', 'editar', 'eliminar'];

        function mostrarMsg(id, esError, texto) {
            const el = document.getElementById(id);
            el.className = 'msg-box ' + (esError ? 'err' : 'ok');
            el.textContent = texto;
            el.style.display = 'block';
        }

        function cerrarModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function abrirModalGrupo(datos = null) {
            document.getElementById('mg-msg').style.display = 'none';
            if (datos) {
                document.getElementById('mg-title').textContent = 'Editar Grupo';
                document.getElementById('g-id').value = datos.id;
                document.getElementById('g-nombre').value = datos.nombre;
                document.getElementById('g-desc').value = datos.descripcion || '';
                document.getElementById('g-sa').checked = datos.es_superadmin == 1;
            } else {
                document.getElementById('mg-title').textContent = 'Nuevo Grupo';
                document.getElementById('g-id').value = '';
                document.getElementById('g-nombre').value = '';
                document.getElementById('g-desc').value = '';
                document.getElementById('g-sa').checked = false;
            }
            document.getElementById('modal-grupo').classList.add('active');
        }

        async function guardarGrupo() {
            const id = document.getElementById('g-id').value;
            const nombre = document.getElementById('g-nombre').value.trim();
            if (!nombre) {
                mostrarMsg('mg-msg', true, 'El nombre es obligatorio.');
                return;
            }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            if (id) fd.append('id', id);
            fd.append('nombre', nombre);
            fd.append('descripcion', document.getElementById('g-desc').value.trim());
            fd.append('es_superadmin', document.getElementById('g-sa').checked ? 1 : 0);

            const btn = document.getElementById('btn-guardar-g');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("grupos.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json();
                if (d.ok) {
                    mostrarMsg('mg-msg', false, d.mensaje);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarMsg('mg-msg', true, d.error);
                    btn.disabled = false;
                }
            } catch(e) {
                mostrarMsg('mg-msg', true, 'Error de conexión.');
                btn.disabled = false;
            }
        }

        async function eliminarGrupo(id, countUsers) {
            if (countUsers > 0) {
                alert('No se puede eliminar. Hay ' + countUsers + ' usuario(s) en este grupo.');
                return;
            }
            if (!confirm('¿Eliminar este grupo de acceso?')) return;

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('id', id);

            try {
                const r = await fetch('{{ route("grupos.eliminar") }}', { method: 'POST', body: fd });
                const d = await r.json();
                if (d.ok) {
                    location.reload();
                } else {
                    alert(d.error);
                }
            } catch(e) { alert('Error de conexión.'); }
        }

        async function abrirModalPermisos(grupoId, nombre) {
            document.getElementById('mp-msg').style.display = 'none';
            document.getElementById('mp-title-name').textContent = nombre;
            document.getElementById('p-grupo-id').value = grupoId;

            // Generar la tabla de permisos
            const tbody = document.getElementById('p-tbody');
            tbody.innerHTML = '<tr><td colspan="6" style="color:#94a3b8;">Cargando permisos...</td></tr>';
            document.getElementById('modal-permisos').classList.add('active');

            try {
                const r = await fetch('{{ url("/grupos/permisos") }}/' + grupoId);
                const d = await r.json();

                // Convertimos el array a objeto rapido para buscar
                const permisosGuardados = {};
                if (d.ok && d.permisos) {
                    d.permisos.forEach(p => {
                        if (!permisosGuardados[p.modulo]) permisosGuardados[p.modulo] = {};
                        permisosGuardados[p.modulo][p.accion] = p.permitido == 1;
                    });
                }

                let html = '';
                modulos.forEach(mod => {
                    html += '<tr><td>' + mod.replace('_', ' ') + '</td>';
                    acciones.forEach(acc => {
                        const checked = (permisosGuardados[mod] && permisosGuardados[mod][acc]) ? 'checked' : '';
                        html += `<td><input type="checkbox" class="chk-mod" data-mod="${mod}" data-acc="${acc}" ${checked}></td>`;
                    });
                    html += '</tr>';
                });
                tbody.innerHTML = html;

            } catch(e) {
                tbody.innerHTML = '<tr><td colspan="6" style="color:#ef4444;">Error al cargar permisos.</td></tr>';
            }
        }

        function marcarTodos(estado) {
            document.querySelectorAll('.chk-mod').forEach(cb => cb.checked = estado);
        }

        async function guardarPermisos() {
            const grupoId = document.getElementById('p-grupo-id').value;
            const checks = document.querySelectorAll('.chk-mod');

            const dataObj = {};
            checks.forEach(cb => {
                const mod = cb.getAttribute('data-mod');
                const acc = cb.getAttribute('data-acc');
                if (!dataObj[mod]) dataObj[mod] = {};
                dataObj[mod][acc] = cb.checked;
            });

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('grupo_id', grupoId);

            // Laravel espera el array 'permisos'
            Object.keys(dataObj).forEach(mod => {
                Object.keys(dataObj[mod]).forEach(acc => {
                    fd.append(`permisos[${mod}][${acc}]`, dataObj[mod][acc] ? '1' : '0');
                });
            });

            const btn = document.getElementById('btn-guardar-p');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("grupos.permisos.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json();
                if (d.ok) {
                    mostrarMsg('mp-msg', false, 'Permisos guardados correctamente.');
                    setTimeout(() => cerrarModal('modal-permisos'), 1500);
                } else {
                    mostrarMsg('mp-msg', true, d.error);
                }
            } catch(e) {
                mostrarMsg('mp-msg', true, 'Error de conexión.');
            } finally {
                btn.disabled = false;
            }
        }
    </script>
@endpush
