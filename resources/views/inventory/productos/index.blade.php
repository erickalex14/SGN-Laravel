@extends('layouts.app')
@section('titulo', 'Catálogo de Productos')

@push('css_adicional')
    <style>
        .prod-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 24px; }
        .prod-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
        .prod-hdr-text h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; }
        .prod-hdr-text p { margin: 0; color: #64748b; font-size: 14px; }
        .prod-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; }
        .prod-card-hdr { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; flex-wrap: wrap; gap: 10px; }
        .prod-card-hdr h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .btn-nuevo { background: #2563eb; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-nuevo:hover { background: #1d4ed8; }
        .prod-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .prod-table th { text-align: left; padding: 12px 20px; background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #e2e8f0; }
        .prod-table td { padding: 12px 20px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
        .prod-table tr:hover td { background: #f8fafc; }
        .btn-action { background: none; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b; transition: all .15s; }
        .btn-action:hover { background: #e2e8f0; color: #0f172a; }
        .btn-action.del:hover { background: #fef2f2; color: #dc2626; }
        .badge-tipo { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; font-family: monospace; }
        .prod-empty { padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); backdrop-filter: blur(2px); z-index: 9999; display: none; align-items: center; justify-content: center; }
        .modal-overlay.activo { display: flex; }
        .modal-box { background: #fff; width: 100%; max-width: 500px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,.15); display: flex; flex-direction: column; }
        .modal-hdr { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .modal-hdr h3 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
        .btn-cerrar { background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 0; }
        .btn-cerrar:hover { color: #dc2626; }
        .modal-body { padding: 24px; }
        .modal-ftr { padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 16px 16px; }
        .campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .campo label { font-size: 13px; font-weight: 600; color: #475569; }
        .campo input, .campo select { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; transition: border-color .2s; }
        .campo input:focus, .campo select:focus { outline: none; border-color: #2563eb; }
        .req { color: #ef4444; }
        .btn-sec { padding: 9px 16px; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 600; color: #475569; cursor: pointer; }
        .btn-ok { padding: 9px 16px; background: #2563eb; border: none; border-radius: 8px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-ok:hover { background: #1d4ed8; }
        .msg-box { display: none; padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .input-search { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; width: 240px; }
    </style>
@endpush

@section('contenido')
    <div class="prod-wrap">
        <div class="prod-hdr">
            <div class="prod-hdr-text">
                <h2><i class="bi bi-box-seam me-2" style="color:#2563eb;"></i>Catálogo de Productos</h2>
                <p>Define los productos estructurados para las órdenes de servicio.</p>
            </div>
        </div>

        <div class="prod-card">
            <div class="prod-card-hdr">
                <h3><i class="bi bi-list-ul me-2" style="color:#64748b;"></i>Productos Registrados (<span id="count-prod">{{ count($productos) }}</span>)</h3>
                <div style="display:flex; gap:12px; align-items:center;">
                    <input type="text" id="buscador" class="input-search" placeholder="Buscar producto..." oninput="filtrarTabla()">
                    <button class="btn-nuevo" onclick="abrirModal()">
                        <i class="bi bi-plus-circle"></i> Nuevo Producto
                    </button>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="prod-table" id="tabla-productos">
                    <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Marca</th>
                        <th>Tipo</th>
                        <th style="width:90px; text-align:right;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="productos-tbody">
                    @forelse($productos as $p)
                        <tr data-row="producto">
                            <td><strong>{{ $p->codigo }}</strong></td>
                            <td>{{ $p->descripcion }}</td>
                            <td>{{ $p->marca ? $p->marca->nombre : 'N/A' }}</td>
                            <td>
                                @if($p->tipoDispositivo)
                                    <span class="badge-tipo">{{ $p->tipoDispositivo->codigo }}</span> {{ $p->tipoDispositivo->nombre }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <button class="btn-action" title="Editar" onclick="abrirModal({{ json_encode($p) }})"><i class="bi bi-pencil"></i></button>
                                <button class="btn-action del" title="Eliminar" onclick="eliminarProducto({{ $p->id }})"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr id="tr-vacio"><td colspan="5"><div class="prod-empty">No se han registrado productos.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div id="productos-pager" style="padding: 10px 20px 20px;"></div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-producto">
        <div class="modal-box">
            <div class="modal-hdr">
                <h3 id="mp-title">Nuevo Producto</h3>
                <button class="btn-cerrar" onclick="cerrarModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div id="mp-msg" class="msg-box err"></div>
                <input type="hidden" id="p-id">

                <div class="campo">
                    <label>Código Interno <span class="req">*</span></label>
                    <input type="text" id="p-codigo" maxlength="100" placeholder="Ej: LPT-DELL-XPS" oninput="this.value=this.value.toUpperCase()">
                </div>

                <div class="campo">
                    <label>Descripción <span class="req">*</span></label>
                    <input type="text" id="p-descripcion" maxlength="255" placeholder="Ej: Laptop Dell XPS 13" oninput="this.value=this.value.toUpperCase()">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="campo" style="margin-bottom:0;">
                        <label>Marca <span class="req">*</span></label>
                        <select id="p-marca">
                            <option value="">-- Seleccionar --</option>
                            @foreach($marcas as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo" style="margin-bottom:0;">
                        <label>Tipo de Dispositivo <span class="req">*</span></label>
                        <select id="p-tipo">
                            <option value="">-- Seleccionar --</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->id }}">{{ $t->codigo }} - {{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn-sec" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-ok" id="btn-guardar" onclick="guardarProducto()"><i class="bi bi-floppy"></i> Guardar</button>
            </div>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        function abrirModal(datos = null) {
            document.getElementById('mp-msg').style.display = 'none';
            const title = document.getElementById('mp-title');
            const id = document.getElementById('p-id');
            const codigo = document.getElementById('p-codigo');
            const desc = document.getElementById('p-descripcion');
            const marca = document.getElementById('p-marca');
            const tipo = document.getElementById('p-tipo');

            if (datos) {
                title.textContent = 'Editar Producto';
                id.value = datos.id;
                codigo.value = datos.codigo;
                desc.value = datos.descripcion;
                marca.value = datos.marca_id;
                tipo.value = datos.tipo_dispositivo_id;
            } else {
                title.textContent = 'Nuevo Producto';
                id.value = '';
                codigo.value = '';
                desc.value = '';
                marca.value = '';
                tipo.value = '';
            }

            document.getElementById('modal-producto').classList.add('activo');
            setTimeout(() => codigo.focus(), 100);
        }

        function cerrarModal() {
            document.getElementById('modal-producto').classList.remove('activo');
        }

        function mostrarError(msg) {
            const el = document.getElementById('mp-msg');
            el.textContent = msg;
            el.style.display = 'block';
        }

        async function guardarProducto() {
            const id = document.getElementById('p-id').value;
            const codigo = document.getElementById('p-codigo').value.trim();
            const desc = document.getElementById('p-descripcion').value.trim();
            const marca = document.getElementById('p-marca').value;
            const tipo = document.getElementById('p-tipo').value;

            if (!codigo || !desc || !marca || !tipo) {
                mostrarError('Todos los campos son obligatorios.');
                return;
            }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', id ? 'editar' : 'crear');
            if (id) fd.append('id', id);
            fd.append('codigo', codigo);
            fd.append('descripcion', desc);
            fd.append('marca_id', marca);
            fd.append('tipo_dispositivo_id', tipo);

            const btn = document.getElementById('btn-guardar');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("productos.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json();

                if (d.ok) {
                    location.reload();
                } else {
                    mostrarError(d.error);
                }
            } catch (e) {
                mostrarError('Se produjo un error de conexión al servidor.');
            } finally {
                btn.disabled = false;
            }
        }

        async function eliminarProducto(id) {
            if (!confirm('¿Confirma que desea eliminar el producto seleccionado?')) return;

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', 'eliminar');
            fd.append('id', id);

            try {
                const r = await fetch('{{ route("productos.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json();
                if (d.ok) location.reload();
                else alert(d.error);
            } catch (e) {
                alert('Se produjo un error de conexión al servidor.');
            }
        }

        function filtrarTabla() {
            const q = document.getElementById('buscador').value.toLowerCase();
            const filas = document.querySelectorAll('#tabla-productos tbody tr:not(#tr-vacio)');
            let conteo = 0;

            filas.forEach(tr => {
                if (tr.textContent.toLowerCase().includes(q)) {
                    tr.style.display = '';
                    conteo++;
                } else {
                    tr.style.display = 'none';
                }
            });

            document.getElementById('count-prod').textContent = conteo;
        }

        let _prodPager = null;
        document.addEventListener('DOMContentLoaded', () => {
            _prodPager = new SgnPager({
                containerSelector: '#productos-tbody',
                itemSelector: 'tr[data-row="producto"]',
                pagerContainerSelector: '#productos-pager',
                pageSize: 15
            });
        });
    </script>
@endpush
