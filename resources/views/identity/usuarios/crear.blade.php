@extends('layouts.app')
@section('titulo', 'Crear Usuario')

@push('css_adicional')
    <style>
        .modulo { padding: 30px; background: #f1f5f9; min-height: 100%; }
        .cu-container { max-width: 900px; margin: 0 auto; background: white; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.04); padding: 30px; border: 1.5px solid #e2e8f0; }
        .form-titulo { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #f1f5f9; }
        .form-titulo h2 { margin: 0 0 6px; color: #0f172a; font-size: 22px; font-weight: 800; display:flex; align-items:center; gap:10px; }
        .form-titulo p { margin: 0; color: #64748b; font-size: 14px; }
        .cu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
        .campo { display: flex; flex-direction: column; gap: 6px; }
        .campo label { font-size: 13px; font-weight: 700; color: #475569; }
        .campo input, .campo select { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #1e293b; transition: all .2s; font-family: inherit; background: #fff; }
        .campo input:focus, .campo select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .req { color: #ef4444; }
        .cu-seccion { margin-top: 24px; padding-top: 20px; border-top: 1.5px solid #f1f5f9; }
        .cu-seccion h3 { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 16px; display:flex; align-items:center; gap:8px; }
        .chk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
        .chk-item { display: flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; }
        .chk-item input { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }
        .btn-guardar { background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 8px; margin-top: 24px; width: 100%; justify-content: center; }
        .btn-guardar:hover { background: #1d4ed8; }
        .msg-box { display: none; padding: 14px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
        .msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .msg-box.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .perm-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top:10px; }
        .perm-table th { background: #f8fafc; padding: 10px; border-bottom: 2px solid #e2e8f0; color: #475569; text-align: center; }
        .perm-table th:first-child { text-align: left; }
        .perm-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .perm-table td:first-child { text-align: left; font-weight: 600; text-transform: capitalize; color: #1e293b; }
        .perm-table tr:hover td { background: #f8fafc; }
    </style>
@endpush

@section('contenido')
    <div class="cu-container">
        <div class="form-titulo">
            <h2><i class="bi bi-person-plus" style="color:#2563eb;"></i>Crear Nuevo Usuario</h2>
            <p>Registra un nuevo técnico o administrador en el sistema</p>
        </div>

        <div id="cu-msg" class="msg-box"></div>

        <div class="cu-grid">
            <div class="campo">
                <label>Usuario (Login) <span class="req">*</span></label>
                <input type="text" id="cu-usuario" maxlength="100" placeholder="Ej: jdoe">
            </div>
            <div class="campo">
                <label>Contraseña <span class="req">*</span></label>
                <input type="password" id="cu-clave" placeholder="Contraseña de acceso">
            </div>
        </div>

        <div class="cu-grid">
            <div class="campo">
                <label>Nombre Completo <span class="req">*</span></label>
                <input type="text" id="cu-nombre" maxlength="100" placeholder="Ej: John Doe">
            </div>
            <div class="campo">
                <label>Teléfono</label>
                <input type="text" id="cu-telefono" maxlength="20" placeholder="Ej: 0999999999">
            </div>
        </div>

        <div class="cu-grid">
            <div class="campo">
                <label>Correo Electrónico</label>
                <input type="email" id="cu-correo" maxlength="100" placeholder="usuario@empresa.com">
            </div>
            <div class="campo">
                <label>Rol de Usuario <span class="req">*</span></label>
                <select id="cu-rol">
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id }}">{{ $rol->rol }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="cu-grid">
            <div class="campo">
                <label>Grupo de Acceso <span class="req">*</span></label>
                <select id="cu-grupo">
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->nombre }} @if($grupo->es_superadmin) (Admin) @endif</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label>Sucursal Principal <span class="req">*</span></label>
                <select id="cu-sucursal">
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}">{{ str_pad($s->nro_sucursal,3,'0',STR_PAD_LEFT) }} - {{ $s->ciudad }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="cu-seccion">
            <h3><i class="bi bi-buildings"></i>Sucursales Secundarias Asignadas</h3>
            <div class="chk-grid">
                @foreach($sucursales as $s)
                    <label class="chk-item">
                        <input type="checkbox" class="chk-suc" value="{{ $s->id }}">
                        {{ $s->ciudad }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="cu-seccion">
            <h3><i class="bi bi-tools"></i>Centros de Asistencia Técnica (CAS) Asignados</h3>
            <div class="chk-grid">
                @foreach($casList as $c)
                    <label class="chk-item">
                        <input type="checkbox" class="chk-cas" value="{{ $c->id }}">
                        {{ $c->nombre }} ({{ $c->ciudad ?? 'N/A' }})
                    </label>
                @endforeach
            </div>
        </div>

        <div class="cu-seccion">
            <h3><i class="bi bi-shield-lock"></i>Permisos Específicos Adicionales</h3>
            <p style="font-size:13px; color:#64748b; margin-bottom:12px;">Se sumarán a los permisos que ya tiene su Grupo de Acceso.</p>
            <table class="perm-table">
                <thead>
                <tr>
                    <th>Módulo</th><th>Ver</th><th>Crear</th><th>Editar</th><th>Eliminar</th>
                </tr>
                </thead>
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

        <div class="chk-item" style="margin-top:20px; font-weight:700;">
            <input type="checkbox" id="cu-nc" value="1">
            Permitir Autorización de Notas de Crédito
        </div>

        <button class="btn-guardar" onclick="guardarUsuario()"><i class="bi bi-floppy"></i> Registrar Usuario</button>
    </div>
@endsection

@push('js_adicional')
    <script>
        function mostrarMsg(isError, texto) {
            const box = document.getElementById('cu-msg');
            box.className = 'msg-box ' + (isError ? 'err' : 'ok');
            box.textContent = texto;
            box.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function guardarUsuario() {
            const usuario = document.getElementById('cu-usuario').value.trim();
            const clave = document.getElementById('cu-clave').value.trim();
            const nombre = document.getElementById('cu-nombre').value.trim();

            if(!usuario || !clave || !nombre) {
                mostrarMsg(true, 'Usuario, Contraseña y Nombre son obligatorios.'); return;
            }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('usuario', usuario);
            fd.append('clave', clave);
            fd.append('nombre_tecnico', nombre);
            fd.append('telefono', document.getElementById('cu-telefono').value.trim());
            fd.append('correo_tec', document.getElementById('cu-correo').value.trim());
            fd.append('rol_id', document.getElementById('cu-rol').value);
            fd.append('grupo_id', document.getElementById('cu-grupo').value);
            fd.append('sucursal_id', document.getElementById('cu-sucursal').value);
            fd.append('acceso_nc', document.getElementById('cu-nc').checked ? 1 : 0);

            document.querySelectorAll('.chk-suc:checked').forEach(cb => { fd.append('sucursales[]', cb.value); });
            document.querySelectorAll('.chk-cas:checked').forEach(cb => { fd.append('cas[]', cb.value); });

            document.querySelectorAll('.chk-mod:checked').forEach(cb => {
                fd.append(`permisos[${cb.dataset.mod}][${cb.dataset.acc}]`, '1');
            });

            const btn = document.querySelector('.btn-guardar');
            btn.disabled = true; btn.innerHTML = 'Guardando...';

            try {
                const r = await fetch('{{ route("usuarios.guardar") }}', { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) {
                    mostrarMsg(false, d.mensaje);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarMsg(true, d.error);
                    btn.disabled = false; btn.innerHTML = '<i class="bi bi-floppy"></i> Registrar Usuario';
                }
            } catch(e) {
                mostrarMsg(true, 'Error de conexión.');
                btn.disabled = false; btn.innerHTML = '<i class="bi bi-floppy"></i> Registrar Usuario';
            }
        }
    </script>
@endpush
