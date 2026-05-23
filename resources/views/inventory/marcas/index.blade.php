@extends('layouts.app')
@section('titulo', 'Marcas y Tipos de Dispositivo')

@push('css_adicional')
    <style>
        /* CSS extraído directamente del legacy para visualizacion idéntica */
        .mt-wrap { max-width: 1000px; margin: 0 auto; padding: 28px 24px; }
        .mt-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
        .mt-hdr-text h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; }
        .mt-hdr-text p { margin: 0; color: #64748b; font-size: 14px; }
        .mt-tabs { display: flex; gap: 8px; margin-bottom: 24px; }
        .mt-tab { padding: 10px 24px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; color: #475569; font-weight: 700; font-size: 13.5px; cursor: pointer; transition: all .2s; }
        .mt-tab:hover { background: #e2e8f0; }
        .mt-tab.activo { background: #2563eb; color: #fff; border-color: #2563eb; }
        .mt-panel { display: none; }
        .mt-panel.activo { display: block; }
        .mt-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; }
        .mt-card-hdr { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; }
        .mt-card-hdr h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; }
        .btn-nuevo { background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-nuevo:hover { background: #059669; }
        .mt-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .mt-table th { text-align: left; padding: 12px 20px; background: #f8fafc; color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #e2e8f0; }
        .mt-table td { padding: 12px 20px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
        .mt-table tr:hover td { background: #f8fafc; }
        .mt-table tr:last-child td { border-bottom: none; }
        .btn-action { background: none; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b; transition: all .15s; }
        .btn-action:hover { background: #e2e8f0; color: #0f172a; }
        .btn-action.del:hover { background: #fef2f2; color: #dc2626; }
        .mt-empty { padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; }
        .mt-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); backdrop-filter: blur(2px); z-index: 9999; display: none; align-items: center; justify-content: center; }
        .mt-modal-overlay.activo { display: flex; }
        .mt-modal { background: #fff; width: 100%; max-width: 440px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,.15); display: flex; flex-direction: column; }
        .mt-m-hdr { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .mt-m-hdr h3 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
        .mt-close { background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 0; }
        .mt-close:hover { color: #dc2626; }
        .mt-m-body { padding: 24px; }
        .mt-m-ftr { padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 16px 16px; }
        .campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .campo label { font-size: 13px; font-weight: 600; color: #475569; }
        .campo input { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; transition: border-color .2s; }
        .campo input:focus { outline: none; border-color: #2563eb; }
        .req { color: #ef4444; }
        .btn-sec { padding: 9px 16px; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 600; color: #475569; cursor: pointer; }
        .btn-ok { padding: 9px 16px; background: #2563eb; border: none; border-radius: 8px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-ok:hover { background: #1d4ed8; }
        .msg-box { display: none; padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .code-badge { background: #f1f5f9; padding: 3px 8px; border-radius: 6px; font-family: monospace; font-size: 12px; font-weight: 700; color: #475569; border: 1px solid #e2e8f0; }
    </style>
@endpush

@section('contenido')
    <div class="mt-wrap">
        <div class="mt-hdr">
            <div class="mt-hdr-text">
                <h2><i class="bi bi-tags me-2" style="color:#2563eb;"></i>Configuración de Catálogo</h2>
                <p>Gestiona las marcas y los tipos de dispositivo del sistema.</p>
            </div>
        </div>

        <div class="mt-tabs">
            <button class="mt-tab activo" onclick="mtTab('marcas', this)">Marcas</button>
            <button class="mt-tab" onclick="mtTab('tipos', this)">Tipos de Dispositivo</button>
        </div>

        <div class="mt-panel activo" id="panel-marcas">
            <div class="mt-card">
                <div class="mt-card-hdr">
                    <h3><i class="bi bi-tag-fill me-2" style="color:#64748b;"></i>Marcas Registradas ({{ count($marcas) }})</h3>
                    <button class="btn-nuevo" onclick="mtModalMarca()">
                        <i class="bi bi-plus-circle"></i> Nueva Marca
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="mt-table">
                        <thead><tr><th>Nombre de la Marca</th><th style="width:100px;text-align:right;">Acciones</th></tr></thead>
                        <tbody>
                        @forelse($marcas as $m)
                            <tr>
                                <td><strong>{{ $m->nombre }}</strong></td>
                                <td style="text-align:right;">
                                    <button class="btn-action" title="Editar" onclick="mtModalMarca({{ $m->id }}, '{{ addslashes($m->nombre) }}')"><i class="bi bi-pencil"></i></button>
                                    <button class="btn-action del" title="Eliminar" onclick="mtEliminar('marca', {{ $m->id }})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><div class="mt-empty">No hay marcas registradas.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-panel" id="panel-tipos">
            <div class="mt-card">
                <div class="mt-card-hdr">
                    <h3><i class="bi bi-pc-display me-2" style="color:#64748b;"></i>Tipos de Dispositivo ({{ count($tipos) }})</h3>
                    <button class="btn-nuevo" onclick="mtModalTipo()">
                        <i class="bi bi-plus-circle"></i> Nuevo Tipo
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="mt-table">
                        <thead><tr><th style="width:120px;">Código</th><th>Nombre del Tipo</th><th style="width:100px;text-align:right;">Acciones</th></tr></thead>
                        <tbody>
                        @forelse($tipos as $t)
                            <tr>
                                <td><span class="code-badge">{{ $t->codigo }}</span></td>
                                <td><strong>{{ $t->nombre }}</strong></td>
                                <td style="text-align:right;">
                                    <button class="btn-action" title="Editar" onclick="mtModalTipo({{ $t->id }}, '{{ addslashes($t->codigo) }}', '{{ addslashes($t->nombre) }}')"><i class="bi bi-pencil"></i></button>
                                    <button class="btn-action del" title="Eliminar" onclick="mtEliminar('tipo', {{ $t->id }})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="mt-empty">No hay tipos de dispositivo registrados.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-modal-overlay" id="modal-marca">
        <div class="mt-modal">
            <div class="mt-m-hdr">
                <h3 id="mm-title">Nueva Marca</h3>
                <button class="mt-close" onclick="document.getElementById('modal-marca').classList.remove('activo')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="mt-m-body">
                <div id="mm-msg" class="msg-box err"></div>
                <input type="hidden" id="m-id">
                <div class="campo" style="margin-bottom:0;">
                    <label>Nombre de la marca <span class="req">*</span></label>
                    <input type="text" id="m-nombre" maxlength="150" placeholder="Ej: Dell, HP, Lenovo..." oninput="this.value=this.value.toUpperCase()">
                </div>
            </div>
            <div class="mt-m-ftr">
                <button class="btn-sec" onclick="document.getElementById('modal-marca').classList.remove('activo')">Cancelar</button>
                <button class="btn-ok" id="btn-gm" onclick="guardarMarca()"><i class="bi bi-floppy"></i> Guardar</button>
            </div>
        </div>
    </div>

    <div class="mt-modal-overlay" id="modal-tipo">
        <div class="mt-modal">
            <div class="mt-m-hdr">
                <h3 id="mt-title">Nuevo Tipo de Dispositivo</h3>
                <button class="mt-close" onclick="document.getElementById('modal-tipo').classList.remove('activo')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="mt-m-body">
                <div id="mt-msg" class="msg-box err"></div>
                <input type="hidden" id="t-id">
                <div class="campo">
                    <label>Código Interno <span class="req">*</span></label>
                    <input type="text" id="t-codigo" maxlength="50" placeholder="Ej: LPT para Laptop..." oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="campo" style="margin-bottom:0;">
                    <label>Nombre del Tipo <span class="req">*</span></label>
                    <input type="text" id="t-nombre" maxlength="150" placeholder="Ej: Laptop, All In One..." oninput="this.value=this.value.toUpperCase()">
                </div>
            </div>
            <div class="mt-m-ftr">
                <button class="btn-sec" onclick="document.getElementById('modal-tipo').classList.remove('activo')">Cancelar</button>
                <button class="btn-ok" id="btn-gt" onclick="guardarTipo()"><i class="bi bi-floppy"></i> Guardar</button>
            </div>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        function mtTab(panel, btn) {
            document.querySelectorAll('.mt-tab').forEach(b => b.classList.remove('activo'));
            document.querySelectorAll('.mt-panel').forEach(p => p.classList.remove('activo'));
            btn.classList.add('activo');
            document.getElementById('panel-' + panel).classList.add('activo');
        }

        function mtModalMarca(id = null, nombre = '') {
            document.getElementById('mm-msg').style.display = 'none';
            document.getElementById('mm-title').textContent = id ? 'Editar Marca' : 'Nueva Marca';
            document.getElementById('m-id').value = id || '';
            document.getElementById('m-nombre').value = nombre;
            document.getElementById('modal-marca').classList.add('activo');
            setTimeout(() => document.getElementById('m-nombre').focus(), 100);
        }

        function mtModalTipo(id = null, codigo = '', nombre = '') {
            document.getElementById('mt-msg').style.display = 'none';
            document.getElementById('mt-title').textContent = id ? 'Editar Tipo' : 'Nuevo Tipo';
            document.getElementById('t-id').value = id || '';
            document.getElementById('t-codigo').value = codigo;
            document.getElementById('t-nombre').value = nombre;
            document.getElementById('modal-tipo').classList.add('activo');
            setTimeout(() => document.getElementById('t-codigo').focus(), 100);
        }

        function showError(id, msg) {
            const el = document.getElementById(id);
            el.textContent = msg; el.style.display = 'block';
        }

        async function guardarMarca() {
            const nombre = document.getElementById('m-nombre').value.trim();
            if(!nombre) { showError('mm-msg', 'El nombre es obligatorio.'); return; }

            const id = document.getElementById('m-id').value;
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', id ? 'editar' : 'crear');
            if(id) fd.append('id', id);
            fd.append('nombre', nombre);

            const btn = document.getElementById('btn-gm');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("marcas.guardar") }}', { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) location.reload();
                else showError('mm-msg', d.error);
            } catch(e) { showError('mm-msg', 'Error de conexión.'); }
            finally { btn.disabled = false; }
        }

        async function guardarTipo() {
            const codigo = document.getElementById('t-codigo').value.trim();
            const nombre = document.getElementById('t-nombre').value.trim();
            if(!codigo || !nombre) { showError('mt-msg', 'Ambos campos son obligatorios.'); return; }

            const id = document.getElementById('t-id').value;
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', id ? 'editar' : 'crear');
            if(id) fd.append('id', id);
            fd.append('codigo', codigo);
            fd.append('nombre', nombre);

            const btn = document.getElementById('btn-gt');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("tipos_dispositivo.guardar") }}', { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) location.reload();
                else showError('mt-msg', d.error);
            } catch(e) { showError('mt-msg', 'Error de conexión.'); }
            finally { btn.disabled = false; }
        }

        async function mtEliminar(tipo, id) {
            if(!confirm(`¿Seguro que deseas eliminar este registro?`)) return;

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', 'eliminar');
            fd.append('id', id);

            const ruta = tipo === 'marca' ? '{{ route("marcas.guardar") }}' : '{{ route("tipos_dispositivo.guardar") }}';

            try {
                const r = await fetch(ruta, { method:'POST', body:fd });
                const d = await r.json();
                if(d.ok) location.reload();
                else alert(d.error);
            } catch(e) { alert('Error de conexión.'); }
        }
    </script>
@endpush
