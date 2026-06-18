@extends('layouts.app')

@section('titulo', 'Sucursales Novitec')

@push('css_adicional')
    <style>
        /* CSS original intacto */
        .suc-wrap{max-width:1100px;margin:0 auto;padding:28px 20px;}
        .suc-tabs{display:flex;gap:4px;margin-bottom:22px;border-bottom:2px solid #e2e8f0;}
        .suc-tab{padding:9px 20px;font-size:13.5px;font-weight:700;color:#64748b;background:none;border:none;border-bottom:2.5px solid transparent;margin-bottom:-2px;cursor:pointer;transition:color .15s,border-color .15s;}
        .suc-tab:hover{color:#2563eb;}
        .suc-tab.activo{color:#2563eb;border-bottom-color:#2563eb;}
        .suc-panel{display:none;}
        .suc-panel.activo{display:block;}
        .suc-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1.5px solid #e2e8f0;margin-bottom:20px;overflow:hidden;}
        .suc-card-hdr{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1.5px solid #f1f5f9;background:#f8fafc;}
        .suc-card-hdr h3{margin:0;font-size:15px;font-weight:700;color:#0f172a;}
        .suc-card-body{padding:20px;}
        .suc-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;}
        .campo{display:flex;flex-direction:column;gap:5px;}
        .campo label{font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;}
        .campo input{padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s;}
        .campo input:focus{border-color:#2563eb;}
        .campo input.input-error{border-color:#ef4444;}
        .campo .field-err{display:none;font-size:11.5px;color:#ef4444;font-weight:600;margin-top:2px;}
        .campo .field-err.visible{display:block;}
        .suc-btns{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;}
        .btn-suc-ok{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:9px;padding:10px 22px;font-size:13.5px;font-weight:700;cursor:pointer;}
        .btn-suc-ok:hover{opacity:.88;}
        .btn-suc-sec{background:#f1f5f9;color:#475569;border:none;border-radius:9px;padding:10px 18px;font-size:13.5px;font-weight:600;cursor:pointer;}
        .suc-msg{padding:10px 14px;border-radius:8px;font-weight:600;font-size:13px;margin-bottom:12px;}
        .suc-msg.ok{background:#dcfce7;color:#166534;}
        .suc-msg.error{background:#fee2e2;color:#991b1b;}
        .es-layout{display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;}
        .es-lista{background:#fff;border-radius:12px;border:1.5px solid #e2e8f0;overflow:hidden;max-height:520px;display:flex;flex-direction:column;}
        .es-lista-hdr{padding:12px 16px;border-bottom:1.5px solid #f1f5f9;font-size:13px;font-weight:700;color:#64748b;background:#f8fafc;}
        .es-lista-scroll{overflow-y:auto;flex:1;}
        .es-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid #f8fafc;cursor:pointer;transition:background .12s;}
        .es-item:hover{background:#f1f5f9;}
        .es-item.activo{background:#eff6ff;border-right:3px solid #2563eb;}
        .es-item-nro{font-size:11px;font-weight:700;background:#e2e8f0;color:#475569;border-radius:4px;padding:1px 6px;font-family:monospace;}
        .es-item-ciudad{font-weight:600;font-size:13px;flex:1;}
        .es-item-seq{font-size:11px;color:#94a3b8;}
        .es-placeholder{background:#fff;border-radius:12px;border:1.5px dashed #e2e8f0;padding:40px;text-align:center;color:#94a3b8;font-size:13px;}
        .nro-badge{font-size:11px;font-weight:700;background:#e2e8f0;color:#475569;border-radius:4px;padding:1px 6px;font-family:monospace;}
        table.suc-tabla{width:100%;border-collapse:collapse;font-size:13px;}
        table.suc-tabla th{padding:9px 12px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;border-bottom:2px solid #f1f5f9;background:#f8fafc;}
        table.suc-tabla td{padding:9px 12px;border-bottom:1px solid #f8fafc;vertical-align:middle;}
        table.suc-tabla tr:last-child td{border-bottom:none;}
        table.suc-tabla tr:hover td{background:#f8fafc;}
        @media(max-width:700px){.es-layout{grid-template-columns:1fr;}.suc-grid{grid-template-columns:1fr;}}
    </style>
@endpush

@section('contenido')
    <section class="modulo activo">
        <div class="suc-wrap">
            <div class="form-titulo" style="margin-bottom:18px;">
                <h2><i class="bi bi-shop me-2"></i>Sucursales Novitec</h2>
                <p>Crear y editar sucursales de Novitecnología</p>
            </div>

            <div class="suc-tabs">
                <button class="suc-tab activo" onclick="sucTab('crear',this)"><i class="bi bi-plus-circle me-1"></i>Crear Sucursal</button>
                <button class="suc-tab" onclick="sucTab('editar',this)"><i class="bi bi-pencil-square me-1"></i>Editar Sucursal</button>
            </div>

            <div class="suc-panel activo" id="suc-panel-crear">
                <div class="suc-card">
                    <div class="suc-card-hdr"><h3><i class="bi bi-plus-circle me-2"></i>Nueva Sucursal</h3></div>
                    <div class="suc-card-body">
                        <div id="cs-msg"></div>
                        <div class="suc-grid">
                            <div class="campo">
                                <label>Nro. Sucursal <span style="color:#ef4444;">*</span></label>
                                <input type="number" id="cs_nro_sucursal" placeholder="Ej: 1" min="1" max="999">
                                <span class="field-err" id="cs_nro_sucursal-error"></span>
                            </div>
                            <div class="campo">
                                <label>Ciudad / Nombre <span style="color:#ef4444;">*</span></label>
                                <input type="text" id="cs_ciudad" placeholder="Ej: Quito">
                                <span class="field-err" id="cs_ciudad-error"></span>
                            </div>
                            <div class="campo">
                                <label>Secuencial <span style="color:#ef4444;">*</span></label>
                                <input type="text" id="cs_secuencial" placeholder="Ej: UIO" oninput="this.value=this.value.toUpperCase()">
                                <span class="field-err" id="cs_secuencial-error"></span>
                            </div>
                            <div class="campo">
                                <label>Nro. Base <span style="font-size:11px;color:#94a3b8;">(opcional)</span></label>
                                <input type="text" id="cs_nro_base" placeholder="09XXXXXXXX">
                                <span class="field-err" id="cs_nro_base-error"></span>
                            </div>
                        </div>
                        <div class="suc-btns">
                            <button class="btn-suc-sec" onclick="limpiarFormSucursal()"><i class="bi bi-x-circle me-1"></i>Limpiar</button>
                            <button class="btn-suc-ok" id="btn-crear-sucursal" onclick="crearSucursal()"><i class="bi bi-floppy me-1"></i>Crear Sucursal</button>
                        </div>
                    </div>
                </div>
                <div class="suc-card">
                    <div class="suc-card-hdr">
                        <h3><i class="bi bi-table me-2"></i>sucursales registradas</h3>
                        <span style="font-size:12px;font-weight:700;background:#eff6ff;color:#2563eb;border-radius:20px;padding:3px 12px;" id="cs-count">{{ count($sucursales) }}</span>
                    </div>
                    <div class="suc-card-body" style="padding:0;">
                        <table class="suc-tabla">
                            <thead><tr><th>Nro.</th><th>Ciudad</th><th>Secuencial</th></tr></thead>
                            <tbody id="cs-tbody">
                            @foreach ($sucursales as $s)
                                <tr>
                                    <td><span class="nro-badge">{{ str_pad($s->nro_sucursal, 3, '0', STR_PAD_LEFT) }}</span></td>
                                    <td>{{ $s->ciudad }}</td>
                                    <td>{{ $s->secuencial }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="suc-panel" id="suc-panel-editar">
                <div class="es-layout">
                    <div class="es-lista">
                        <div class="es-lista-hdr">Selecciona una sucursal</div>
                        <div class="es-lista-scroll">
                            @foreach ($sucursales as $s)
                                <div class="es-item" onclick="seleccionarSucursal(this)"
                                     data-suc="{{ json_encode($s) }}">
                                    <span class="es-item-nro">{{ str_pad($s->nro_sucursal, 3, '0', STR_PAD_LEFT) }}</span>
                                    <span class="es-item-ciudad">{{ $s->ciudad }}</span>
                                    <span class="es-item-seq">{{ $s->secuencial }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <div class="es-placeholder" id="es-placeholder">
                            <i class="bi bi-arrow-left-circle" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>
                            Selecciona una sucursal para editarla
                        </div>
                        <div id="es-panel" style="display:none;">
                            <div class="suc-card">
                                <div class="suc-card-hdr">
                                    <h3 id="es-panel-nombre">—</h3>
                                    <span style="font-size:12px;color:#94a3b8;" id="es-panel-sub"></span>
                                </div>
                                <div class="suc-card-body">
                                    <div id="es-msg"></div>
                                    <input type="hidden" id="es_id">
                                    <div class="suc-grid">
                                        <div class="campo">
                                            <label>Nro. Sucursal <span style="color:#ef4444;">*</span></label>
                                            <input type="number" id="es_nro_sucursal" min="1" max="999">
                                            <span class="field-err" id="es_nro_sucursal-error"></span>
                                        </div>
                                        <div class="campo">
                                            <label>Ciudad / Nombre <span style="color:#ef4444;">*</span></label>
                                            <input type="text" id="es_ciudad">
                                            <span class="field-err" id="es_ciudad-error"></span>
                                        </div>
                                        <div class="campo">
                                            <label>Secuencial <span style="color:#ef4444;">*</span></label>
                                            <input type="text" id="es_secuencial" oninput="this.value=this.value.toUpperCase()">
                                            <span class="field-err" id="es_secuencial-error"></span>
                                        </div>
                                        <div class="campo">
                                            <label>Nro. Base <span style="font-size:11px;color:#94a3b8;">(opcional)</span></label>
                                            <input type="text" id="es_nro_base">
                                            <span class="field-err" id="es_nro_base-error"></span>
                                        </div>
                                    </div>
                                    <div class="suc-btns">
                                        <button class="btn-suc-sec" onclick="cancelarEdicionSucursal()"><i class="bi bi-x-circle me-1"></i>Cancelar</button>
                                        <button class="btn-suc-ok" id="btn-guardar-sucursal" onclick="guardarSucursal()"><i class="bi bi-floppy me-1"></i>Guardar Cambios</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js_adicional')
    <script>
        function sucTab(panel, btn) {
            document.querySelectorAll('.suc-tab').forEach(function(t){ t.classList.remove('activo'); });
            document.querySelectorAll('.suc-panel').forEach(function(p){ p.classList.remove('activo'); });
            btn.classList.add('activo');
            document.getElementById('suc-panel-' + panel).classList.add('activo');
        }

        function _mostrarMsgCS(tipo, texto) {
            let box = document.getElementById('cs-msg');
            if (!box) return;
            box.className = 'suc-msg' + (tipo ? ' ' + tipo : '');
            box.textContent = texto;
            if (tipo === 'ok') setTimeout(function(){ box.className='suc-msg'; box.textContent=''; }, 5000);
        }

        function _mostrarMsgES(tipo, texto) {
            let box = document.getElementById('es-msg');
            if (!box) return;
            box.className = 'suc-msg' + (tipo ? ' ' + tipo : '');
            box.textContent = texto;
            if (tipo === 'ok') setTimeout(function(){ box.className='suc-msg'; box.textContent=''; }, 5000);
        }

        function limpiarFormSucursal() {
            document.getElementById('cs_nro_sucursal').value = '';
            document.getElementById('cs_ciudad').value = '';
            document.getElementById('cs_secuencial').value = '';
            document.getElementById('cs_nro_base').value = '';
            _mostrarMsgCS('', '');
        }

        function seleccionarSucursal(elem) {
            document.querySelectorAll('.es-item').forEach(el => el.classList.remove('activo'));
            elem.classList.add('activo');

            let s = JSON.parse(elem.getAttribute('data-suc'));
            document.getElementById('es-placeholder').style.display = 'none';
            document.getElementById('es-panel').style.display = 'block';

            document.getElementById('es-panel-nombre').textContent = s.ciudad;
            document.getElementById('es-panel-sub').textContent = 'Nro: ' + s.nro_sucursal;

            document.getElementById('es_id').value = s.id;
            document.getElementById('es_nro_sucursal').value = s.nro_sucursal;
            document.getElementById('es_ciudad').value = s.ciudad;
            document.getElementById('es_secuencial').value = s.secuencial;
            document.getElementById('es_nro_base').value = s.nro_base || '';

            _mostrarMsgES('', '');
        }

        function cancelarEdicionSucursal() {
            document.querySelectorAll('.es-item').forEach(el => el.classList.remove('activo'));
            document.getElementById('es-panel').style.display = 'none';
            document.getElementById('es-placeholder').style.display = 'flex';
        }

        function crearSucursal() {
            let fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('nro_sucursal', document.getElementById('cs_nro_sucursal').value);
            fd.append('ciudad', document.getElementById('cs_ciudad').value);
            fd.append('secuencial', document.getElementById('cs_secuencial').value);
            fd.append('nro_base', document.getElementById('cs_nro_base').value);

            let btn = document.getElementById('btn-crear-sucursal');
            btn.disabled = true;

            fetch('{{ route("sucursales.crear") }}', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (!data.ok) { _mostrarMsgCS('error', data.error); return; }
                    _mostrarMsgCS('ok', data.mensaje);
                    limpiarFormSucursal();
                    setTimeout(() => location.reload(), 1500); // Recarga para actualizar tabla y lista
                })
                .catch(() => _mostrarMsgCS('error', 'Error de conexión.'))
                .finally(() => btn.disabled = false);
        }

        function guardarSucursal() {
            let fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('id', document.getElementById('es_id').value);
            fd.append('nro_sucursal', document.getElementById('es_nro_sucursal').value);
            fd.append('ciudad', document.getElementById('es_ciudad').value);
            fd.append('secuencial', document.getElementById('es_secuencial').value);
            fd.append('nro_base', document.getElementById('es_nro_base').value);

            let btn = document.getElementById('btn-guardar-sucursal');
            btn.disabled = true;

            fetch('{{ route("sucursales.actualizar") }}', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (!data.ok) { _mostrarMsgES('error', data.error); return; }
                    _mostrarMsgES('ok', data.mensaje);
                    setTimeout(() => location.reload(), 1500);
                })
                .catch(() => _mostrarMsgES('error', 'Error de conexión.'))
                .finally(() => btn.disabled = false);
        }
    </script>
@endpush
