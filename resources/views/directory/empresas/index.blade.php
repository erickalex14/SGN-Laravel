@extends('layouts.app')

@section('titulo', 'Empresas')

@push('css_adicional')
    <style>
        .emp-container { max-width: 900px; margin: 0 auto; padding: 28px 24px; }
        .form-titulo h2 { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 4px; }
        .form-titulo p { margin: 0 0 20px; color: #64748b; font-size: 13px; }
        .emp-card, .emp-tabla-wrap { background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.03); margin-bottom:20px; overflow:hidden; }
        .emp-card-header, .emp-tabla-header { padding:14px 18px; border-bottom:1.5px solid #e2e8f0; background:#f8fafc; display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .emp-card-header h3, .emp-tabla-header h3 { margin:0; font-size:15px; font-weight:700; color:#1e293b; display:flex; align-items:center; }
        .emp-card-body { padding:18px; }
        .emp-grid, .emp-grid-3 { display:grid; gap:12px; }
        .emp-grid { grid-template-columns:1fr 1fr; }
        .emp-grid-3 { grid-template-columns:1fr 1fr 1fr; }
        .campo { display:flex; flex-direction:column; gap:6px; }
        .campo label { font-size:12px; font-weight:700; color:#475569; }
        .campo input { border:1.5px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:13px; }
        .campo input:focus { outline:none; border-color:#2563eb; }
        .req { color:#ef4444; }
        .emp-botones { display:flex; justify-content:flex-end; gap:8px; }
        .btn-limpiar-emp, .btn-guardar-emp, .btn-emp-edit, .btn-emp-del { border:none; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; padding:8px 12px; }
        .btn-limpiar-emp { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .btn-guardar-emp { background:#2563eb; color:#fff; }
        .btn-emp-edit { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .btn-emp-del { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .emp-count { background:#dbeafe; color:#1d4ed8; border-radius:999px; padding:4px 10px; font-size:12px; font-weight:700; }
        .emp-table { width:100%; border-collapse:collapse; }
        .emp-table th { background:#f8fafc; color:#475569; font-size:12px; text-transform:uppercase; letter-spacing:.4px; border-bottom:2px solid #e2e8f0; padding:12px 10px; text-align:left; }
        .emp-table td { border-bottom:1px solid #f1f5f9; padding:12px 10px; font-size:13px; color:#1e293b; vertical-align:middle; }
        .emp-table tr:hover td { background:#f8fafc; }
        .emp-msg { display:none; border-radius:8px; padding:10px 12px; font-size:13px; font-weight:600; margin-bottom:12px; }
        .emp-msg.ok { display:block; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
        .emp-msg.err { display:block; background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .emp-empty { text-align: center; color: #94a3b8; padding: 32px; font-size: 14px; }
    </style>
@endpush

@section('contenido')
    <section class="modulo activo">
        <div class="emp-container">

            <div class="form-titulo">
                <h2><i class="bi bi-buildings me-2"></i>Empresas</h2>
                <p>Gestión de empresas a las que se brinda servicio</p>
            </div>

            <div class="emp-card">
                <div class="emp-card-header">
                    <i class="bi bi-building-add" style="font-size:18px;color:#1e40af;"></i>
                    <h3 id="emp-form-titulo">Nueva Empresa</h3>
                </div>
                <div class="emp-card-body">
                    <div id="emp-msg" class="emp-msg"></div>
                    <input type="hidden" id="emp-id" value="">

                    <div class="emp-grid" style="margin-bottom:16px;">
                        <div class="campo">
                            <label>Nombre de la Empresa <span class="req">*</span></label>
                            <input type="text" id="emp-nombre" maxlength="200" placeholder="Ej: Empresa ABC S.A." autocomplete="off">
                        </div>
                        <div class="campo">
                            <label>RUC <span class="req">*</span></label>
                            <input type="text" id="emp-ruc" maxlength="13" placeholder="Ej: 1790012345001" autocomplete="off">
                        </div>
                    </div>
                    <div class="emp-grid-3">
                        <div class="campo">
                            <label>Número de Contacto</label>
                            <input type="text" id="emp-telefono" maxlength="15" placeholder="Ej: 0999999999" autocomplete="off">
                        </div>
                        <div class="campo" style="grid-column:span 2;">
                            <label>Correo</label>
                            <input type="email" id="emp-correo" maxlength="200" placeholder="Ej: contacto@empresa.com" autocomplete="off">
                        </div>
                    </div>
                    <div class="campo" style="grid-column:span 3; margin-top: 16px;">
                        <label>Direccion</label>
                        <input type="text" id="emp-direccion" maxlength="200" placeholder="Ej: Galo Plaza Lasso n12-34" autocomplete="off">
                    </div>

                    <div class="emp-botones" style="margin-top:20px;">
                        <button class="btn-limpiar-emp" onclick="limpiarFormEmp()">
                            <i class="bi bi-x-circle me-1"></i>Limpiar
                        </button>
                        <button class="btn-guardar-emp" onclick="guardarEmpresa()">
                            <i class="bi bi-floppy me-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>

            <div class="emp-tabla-wrap">
                <div class="emp-tabla-header">
                    <h3><i class="bi bi-list-ul me-2"></i>Empresas Registradas</h3>
                    <span class="emp-count" id="emp-count">{{ count($empresas) }}</span>
                </div>
                <div id="emp-tabla-body">
                    @if ($empresas->isEmpty())
                        <div class="emp-empty"><i class="bi bi-inbox me-2"></i>No hay empresas registradas.</div>
                    @else
                        <table class="emp-table">
                            <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>RUC</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Dirección</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody id="emp-tbody">
                            @foreach ($empresas as $e)
                                <tr id="emp-row-{{ $e->id }}">
                                    <td class="emp-nombre-cell">{{ $e->nombre }}</td>
                                    <td>{{ $e->ruc }}</td>
                                    <td>{{ $e->telefono ?? '—' }}</td>
                                    <td>{{ $e->correo ?? '—' }}</td>
                                    <td>{{ $e->direccion_empresa ?? '—' }}</td>
                                    <td style="white-space:nowrap;display:flex;gap:6px;">
                                        <button class="btn-emp-edit" onclick="editarEmpresa({{ $e->id }}, {{ json_encode($e) }})">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn-emp-del" onclick="eliminarEmpresa({{ $e->id }}, '{{ addslashes($e->nombre) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </section>
@endsection

@push('js_adicional')
    <script>
        function _esc(v) {
            return String(v ?? '').replace(/[&<>"']/g, function(s) {
                return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' })[s];
            });
        }

        function mostrarMsgEmp(tipo, texto) {
            var box = document.getElementById('emp-msg');
            if (!box) return;
            box.className = 'emp-msg ' + (tipo === 'ok' ? 'ok' : 'err');
            box.textContent = texto || '';
        }

        function limpiarFormEmp() {
            document.getElementById('emp-id').value = '';
            document.getElementById('emp-nombre').value = '';
            document.getElementById('emp-ruc').value = '';
            document.getElementById('emp-telefono').value = '';
            document.getElementById('emp-correo').value = '';
            document.getElementById('emp-direccion').value = '';
            document.getElementById('emp-form-titulo').textContent = 'Nueva Empresa';
        }

        function editarEmpresa(id, data) {
            document.getElementById('emp-id').value = id;
            document.getElementById('emp-nombre').value = data.nombre || '';
            document.getElementById('emp-ruc').value = data.ruc || '';
            document.getElementById('emp-telefono').value = data.telefono || '';
            document.getElementById('emp-correo').value = data.correo || '';
            document.getElementById('emp-direccion').value = data.direccion_empresa || '';
            document.getElementById('emp-form-titulo').textContent = 'Editar Empresa';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function guardarEmpresa() {
            let id      = document.getElementById('emp-id').value.trim();
            let nombre  = document.getElementById('emp-nombre').value.trim();
            let ruc     = document.getElementById('emp-ruc').value.trim();
            let tel     = document.getElementById('emp-telefono').value.trim();
            let correo  = document.getElementById('emp-correo').value.trim();
            let direccion  = document.getElementById('emp-direccion').value.trim();

            if (!nombre) { mostrarMsgEmp('err', 'El nombre de la empresa es obligatorio.'); return; }
            if (!ruc)    { mostrarMsgEmp('err', 'El RUC es obligatorio.'); return; }

            var fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}'); // Requerido por la seguridad de Laravel
            fd.append('id',      id);
            fd.append('nombre',  nombre);
            fd.append('ruc',     ruc);
            fd.append('telefono',tel);
            fd.append('correo',  correo);
            fd.append('direccion', direccion);

            var btn = document.querySelector('.btn-guardar-emp');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Guardando...';

            // Apuntamos a la nueva ruta
            fetch('{{ route("empresas.guardar") }}', { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    if (!data.ok) { mostrarMsgEmp('err', data.error || 'Error al guardar.'); return; }
                    mostrarMsgEmp('ok', data.mensaje || 'Empresa guardada correctamente.');
                    limpiarFormEmp();
                    recargarTabla();
                })
                .catch(function(){ mostrarMsgEmp('err', 'Error de conexión.'); })
                .finally(function(){
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Guardar';
                });
        }

        function eliminarEmpresa(id, nombre) {
            if (!confirm('¿Eliminar la empresa "' + nombre + '"?')) return;
            var fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('id',     id);
            fd.append('accion', 'eliminar');

            fetch('{{ route("empresas.guardar") }}', { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    if (!data.ok) { mostrarMsgEmp('err', data.error || 'Error al eliminar.'); return; }
                    mostrarMsgEmp('ok', 'Empresa eliminada.');
                    recargarTabla();
                })
                .catch(function(){ mostrarMsgEmp('err', 'Error de conexión.'); });
        }

        function recargarTabla() {
            fetch('{{ route("empresas.listar") }}')
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    var cnt  = document.getElementById('emp-count');
                    var body = document.getElementById('emp-tabla-body');
                    if (!data.ok || !data.empresas.length) {
                        if (cnt) cnt.textContent = '0';
                        body.innerHTML = '<div class="emp-empty"><i class="bi bi-inbox me-2"></i>No hay empresas registradas.</div>';
                        return;
                    }
                    if (cnt) cnt.textContent = data.empresas.length;
                    var rows = data.empresas.map(function(e) {
                        return '<tr id="emp-row-' + e.id + '">' +
                            '<td class="emp-nombre-cell">' + _esc(e.nombre) + '</td>' +
                            '<td>' + _esc(e.ruc) + '</td>' +
                            '<td>' + _esc(e.telefono || '—') + '</td>' +
                            '<td>' + _esc(e.correo || '—') + '</td>' +
                            '<td>' + _esc(e.direccion_empresa || '—') + '</td>' +
                            '<td style="white-space:nowrap;display:flex;gap:6px;">' +
                            '<button class="btn-emp-edit" onclick="editarEmpresa(' + e.id + ',' + JSON.stringify(e).replace(/"/g, '&quot;') + ')"><i class="bi bi-pencil"></i> Editar</button>' +
                            '<button class="btn-emp-del" onclick="eliminarEmpresa(' + e.id + ',\'' + _esc(e.nombre).replace(/'/g,"\\'") + '\')"><i class="bi bi-trash"></i></button>' +
                            '</td></tr>';
                    }).join('');
                    body.innerHTML = '<table class="emp-table"><thead><tr><th>Nombre</th><th>RUC</th><th>Teléfono</th><th>Correo</th><th>Dirección</th><th></th></tr></thead><tbody>' + rows + '</tbody></table>';
                });
        }
        //...
    </script>
@endpush
