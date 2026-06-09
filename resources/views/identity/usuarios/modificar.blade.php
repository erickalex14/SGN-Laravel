@extends('layouts.app')
@section('titulo', 'Modificar Usuario')

@push('css_adicional')
    <style>
        /* CSS del listado y form modificado */
        .mu-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 320px 1fr; gap: 24px; align-items: start; }
        @media(max-width:850px){ .mu-container { grid-template-columns: 1fr; } }
        .mu-lista { background: white; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; }
        .mu-lista-hdr { padding: 14px 18px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-weight: 700; color: #1e293b; display:flex; justify-content:space-between; }
        .mu-lista-scroll { max-height: 700px; overflow-y: auto; }
        .u-item { padding: 12px 18px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background .15s; display:flex; flex-direction:column; gap:4px; }
        .u-item:hover { background: #f8fafc; }
        .u-item.activo { background: #eff6ff; border-left: 4px solid #2563eb; padding-left: 14px; }
        .u-item strong { color: #0f172a; font-size: 14px; display:flex; align-items:center; justify-content:space-between; }
        .u-item span { color: #64748b; font-size: 12px; }
        .badge { font-size:10px; padding:2px 8px; border-radius:12px; font-weight:700; }
        .bg-act { background:#dcfce7; color:#166534; }
        .bg-inact { background:#fee2e2; color:#991b1b; }
        .mu-form { background: white; border-radius: 14px; padding: 28px; border: 1.5px solid #e2e8f0; display: none; }
        .mu-form.visible { display: block; }
        .mu-placeholder { background: white; border-radius: 14px; padding: 60px 20px; text-align: center; border: 1.5px dashed #cbd5e1; color: #94a3b8; }
        .mu-hdr { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; }
        .mu-hdr h3 { margin:0; font-size:18px; color:#0f172a; font-weight:800; }
        .btn-toggle { padding:6px 14px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; border:none; }
        .btn-desact { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
        .btn-act { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
        /* Mismos campos del crear */
        .cu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .campo { display: flex; flex-direction: column; gap: 5px; }
        .campo label { font-size: 12px; font-weight: 700; color: #475569; }
        .campo input, .campo select { padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; background: #fff; }
        .campo input:focus, .campo select:focus { outline: none; border-color: #2563eb; }
        .cu-seccion { margin-top: 20px; padding-top: 16px; border-top: 1.5px solid #f1f5f9; }
        .cu-seccion h3 { font-size: 15px; margin:0 0 12px; color:#1e293b; font-weight:700; }
        .chk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
        .chk-item { font-size: 13px; display:flex; align-items:center; gap:6px; color:#334155; }
        .btn-guardar { background: #2563eb; color: white; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 20px; }
        .perm-table { width:100%; border-collapse:collapse; font-size:12px; }
        .perm-table th { background:#f8fafc; padding:8px; border-bottom:1px solid #e2e8f0; text-align:center; }
        .perm-table td { padding:8px; border-bottom:1px solid #f1f5f9; text-align:center; }
        .perm-table td:first-child, .perm-table th:first-child { text-align:left; }
    </style>
@endpush

@section('contenido')
    <div class="form-titulo" style="margin-bottom:20px;">
        <h2><i class="bi bi-person-lines-fill" style="color:#2563eb;"></i>Gestión de Usuarios</h2>
    </div>

    <div class="mu-container">
        <div class="mu-lista">
            <div class="mu-lista-hdr">
                <span>Usuarios Registrados</span>
                <span style="color:#2563eb;background:#eff6ff;padding:2px 8px;border-radius:12px;font-size:11px;">{{ count($usuarios) }}</span>
            </div>
            <div class="mu-lista-scroll">
                @foreach($usuarios as $u)
                    <div class="u-item" onclick="cargarDatos(this, {{ json_encode($u) }})">
                        <strong>{{ $u->nombre_tecnico }}
                            <span class="badge {{ $u->activo ? 'bg-act' : 'bg-inact' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</span>
                        </strong>
                        <span>@ {{ $u->usuario }} | {{ $u->grupo ? $u->grupo->nombre : 'Sin Grupo' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mu-placeholder" id="mu-placeholder">
            <i class="bi bi-hand-index-thumb" style="font-size:32px;display:block;margin-bottom:10px;"></i>
            Selecciona un usuario de la lista para modificar sus datos.
        </div>

        <div class="mu-form" id="mu-form">
            <div class="mu-hdr">
                <h3 id="form-title">Modificando: —</h3>
                <button class="btn-toggle" id="btn-estado" onclick="toggleEstado()"></button>
            </div>

            <input type="hidden" id="mu-id">
            <input type="hidden" id="mu-estado-actual">

            <div class="cu-grid">
                <div class="campo">
                    <label>Usuario (Login)</label>
                    <input type="text" id="mu-usuario" maxlength="100">
                </div>
                <div class="campo">
                    <label>Nueva Contraseña <span style="font-size:10px;font-weight:400;color:#94a3b8;">(Dejar vacía para no cambiar)</span></label>
                    <input type="password" id="mu-clave" placeholder="******">
                </div>
            </div>

            <div class="cu-grid">
                <div class="campo">
                    <label>Nombre Completo</label>
                    <input type="text" id="mu-nombre" maxlength="100">
                </div>
                <div class="campo">
                    <label>Teléfono</label>
                    <input type="text" id="mu-telefono" maxlength="20">
                </div>
            </div>

            <div class="cu-grid">
                <div class="campo">
                    <label>Correo Electrónico</label>
                    <input type="email" id="mu-correo" maxlength="100">
                </div>
                <div class="campo">
                    <label>Rol de Usuario</label>
                    <select id="mu-rol">
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->rol }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="cu-grid">
                <div class="campo">
                    <label>Grupo de Acceso</label>
                    <select id="mu-grupo">
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo">
                    <label>Sucursal Principal</label>
                    <select id="mu-sucursal">
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->ciudad }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="cu-seccion">
                <h3><i class="bi bi-buildings"></i>Sucursales Secundarias</h3>
                <div class="chk-grid">
                    @foreach($sucursales as $s)
                        <label class="chk-item">
                            <input type="checkbox" class="chk-suc" value="{{ $s->id }}" id="suc_{{ $s->id }}">
                            {{ $s->ciudad }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="cu-seccion">
                <h3><i class="bi bi-tools"></i>Centros de Asistencia Técnica (CAS)</h3>
                <div class="chk-grid">
                    @foreach($casList as $c)
                        <label class="chk-item">
                            <input type="checkbox" class="chk-cas" value="{{ $c->id }}" id="cas_{{ $c->id }}">
                            {{ $c->nombre }} ({{ $c->ciudad ?? 'N/A' }})
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="cu-seccion">
                <h3><i class="bi bi-shield-lock"></i>Permisos Adicionales</h3>
                <table class="perm-table">
                    <thead><tr><th>Módulo</th><th>Ver</th><th>Crear</th><th>Editar</th><th>Eliminar</th></tr></thead>
                    <tbody>
                    @php $modulos = ['ordenes_crear','ordenes_editar','ordenes_buscar','ordenes_mis','ordenes_asignadas','preordenes','informes','presupuestos','solicitar_nc','solicitar_repuesto','reportes','notas_credito','repuestos_admin','inv_productos','inv_marcas','inv_repuestos','precios','sucursales','sucursales_novicompu','empresas','cas','mi_cuenta','usuarios','grupos_acceso']; @endphp
                    @foreach($modulos as $mod)
                        <tr>
                            <td>{{ str_replace('_', ' ', $mod) }}</td>
                            @foreach(['ver', 'crear', 'editar', 'eliminar'] as $acc)
                                <td><input type="checkbox" class="chk-mod" data-mod="{{ $mod }}" data-acc="{{ $acc }}"></td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="chk-item" style="margin-top:16px;">
                <input type="checkbox" id="mu-nc" value="1"> <strong>Autorizar Notas de Crédito</strong>
            </div>

            <button class="btn-guardar" onclick="guardarEdicion()"><i class="bi bi-floppy"></i> Guardar Cambios</button>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        function cargarDatos(item, u) {
            document.querySelectorAll('.u-item').forEach(el => el.classList.remove('activo'));
            item.classList.add('activo');

            document.getElementById('mu-placeholder').style.display = 'none';
            document.getElementById('mu-form').classList.add('visible');

            document.getElementById('form-title').textContent = 'Editando: ' + u.nombre_tecnico;
            document.getElementById('mu-id').value = u.id;
            document.getElementById('mu-estado-actual').value = u.activo;

            const btnEst = document.getElementById('btn-estado');
            if(u.activo == 1) {
                btnEst.className = 'btn-toggle btn-desact';
                btnEst.innerHTML = '<i class="bi bi-person-x"></i> Desactivar Usuario';
            } else {
                btnEst.className = 'btn-toggle btn-act';
                btnEst.innerHTML = '<i class="bi bi-person-check"></i> Activar Usuario';
            }

            document.getElementById('mu-usuario').value = u.usuario;
            document.getElementById('mu-clave').value = '';
            document.getElementById('mu-nombre').value = u.nombre_tecnico;
            document.getElementById('mu-telefono').value = u.telefono || '';
            document.getElementById('mu-correo').value = u.correo_tec || '';
            document.getElementById('mu-rol').value = u.rol_id;
            document.getElementById('mu-grupo').value = u.grupo_id;
            document.getElementById('mu-sucursal').value = u.sucursal_id;
            document.getElementById('mu-nc').checked = (u.acceso_nc == 1);

            document.querySelectorAll('.chk-suc').forEach(cb => cb.checked = false);
            fetch('{{ url("/usuarios") }}/' + u.id + '/sucursales')
                .then(r => r.json())
                .then(d => { if(d.ok) d.sucursales.forEach(sId => { const c = document.getElementById('suc_'+sId); if(c) c.checked = true; }); });

            document.querySelectorAll('.chk-cas').forEach(cb => cb.checked = false);
            fetch('{{ url("/usuarios") }}/' + u.id + '/cas')
                .then(r => r.json())
                .then(d => { if(d.ok) d.cas.forEach(cId => { const c = document.getElementById('cas_'+cId); if(c) c.checked = true; }); });

            document.querySelectorAll('.chk-mod').forEach(cb => cb.checked = false);
            fetch('{{ url("/usuarios") }}/' + u.id + '/permisos')
                .then(r => r.json())
                .then(d => {
                    if(d.ok) d.permisos.forEach(p => {
                        if(p.permitido == 1) {
                            const c = document.querySelector(`.chk-mod[data-mod="${p.modulo}"][data-acc="${p.accion}"]`);
                            if(c) c.checked = true;
                        }
                    });
                });
        }

        async function guardarEdicion() {
            const id = document.getElementById('mu-id').value;
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('id', id);
            fd.append('usuario', document.getElementById('mu-usuario').value.trim());
            fd.append('clave', document.getElementById('mu-clave').value.trim());
            fd.append('nombre_tecnico', document.getElementById('mu-nombre').value.trim());
            fd.append('telefono', document.getElementById('mu-telefono').value.trim());
            fd.append('correo_tec', document.getElementById('mu-correo').value.trim());
            fd.append('rol_id', document.getElementById('mu-rol').value);
            fd.append('grupo_id', document.getElementById('mu-grupo').value);
            fd.append('sucursal_id', document.getElementById('mu-sucursal').value);
            fd.append('acceso_nc', document.getElementById('mu-nc').checked ? 1 : 0);

            document.querySelectorAll('.chk-suc:checked').forEach(cb => fd.append('sucursales[]', cb.value));
            document.querySelectorAll('.chk-cas:checked').forEach(cb => fd.append('cas[]', cb.value));
            document.querySelectorAll('.chk-mod:checked').forEach(cb => fd.append(`permisos[${cb.dataset.mod}][${cb.dataset.acc}]`, '1'));

            try {
                const r = await fetch('{{ route("usuarios.actualizar") }}', { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) { alert(d.mensaje); location.reload(); }
                else { alert(d.error); }
            } catch(e) { alert('Error de conexión'); }
        }

        async function toggleEstado() {
            if(!confirm('¿Seguro que deseas cambiar el estado de acceso de este usuario?')) return;
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('id', document.getElementById('mu-id').value);

            try {
                const r = await fetch('{{ route("usuarios.toggle") }}', { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) location.reload(); else alert(d.error);
            } catch(e) { alert('Error de red'); }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setupDynamicValidation(document.getElementById('mu-usuario'), EcuadorianValidator.validarCedula, (v) => {
                if (v.length === 0) return 'El usuario (cédula) es requerido.';
                if (/[^\d]/.test(v)) return 'El usuario sólo debe contener números.';
                return 'El usuario debe ser un número de cédula ecuatoriano de 10 dígitos válido.';
            });
            setupDynamicValidation(document.getElementById('mu-telefono'), EcuadorianValidator.validarTelefono, (v) => {
                if (/[^\d]/.test(v)) return 'El teléfono sólo debe contener números.';
                return 'El teléfono debe ser un celular de 10 dígitos (ej: 0987654321) o convencional de 9 dígitos (ej: 022345678) de Ecuador.';
            });
            setupDynamicValidation(document.getElementById('mu-correo'), EcuadorianValidator.validarEmail, (v) => {
                return 'El correo electrónico no tiene un formato válido.';
            });
        });
    </script>
@endpush
