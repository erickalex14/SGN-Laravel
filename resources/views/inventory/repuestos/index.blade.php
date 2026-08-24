@extends('layouts.app')
@section('titulo', 'Catálogo de Repuestos')

@push('css_adicional')
    <style>
        .rep-wrap { max-width: 1200px; margin: 0 auto; padding: 28px 24px; }
        .rep-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
        .rep-hdr-text h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; }
        .rep-hdr-text p { margin: 0; color: #64748b; font-size: 14px; }
        .rep-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; }
        .rep-card-hdr { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; flex-wrap: wrap; gap: 10px; }
        .rep-card-hdr h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .btn-nuevo { background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-nuevo:hover { background: #059669; }
        .input-search { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; width: 240px; outline:none; transition:border-color .2s; }
        .input-search:focus { border-color:#2563eb; }
        .rep-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .rep-table th { text-align: left; padding: 12px 16px; background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #e2e8f0; }
        .rep-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
        .rep-table tr:hover td { background: #f8fafc; }
        .btn-action { background: none; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b; transition: all .15s; }
        .btn-action:hover { background: #e2e8f0; color: #0f172a; }
        .btn-action.del:hover { background: #fef2f2; color: #dc2626; }
        .stock-badge { padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .stock-ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .stock-low { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .stock-out { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.6); backdrop-filter: blur(2px); z-index: 9999; display: none; align-items: center; justify-content: center; }
        .modal-overlay.activo { display: flex; }
        .modal-box { background: #fff; width: 100%; max-width: 600px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,.15); display: flex; flex-direction: column; max-height:90vh; overflow-y:auto; }
        .modal-hdr { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position:sticky; top:0; background:#fff; z-index:10; }
        .modal-hdr h3 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
        .btn-cerrar { background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 0; }
        .btn-cerrar:hover { color: #dc2626; }
        .modal-body { padding: 24px; }
        .modal-ftr { padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 16px 16px; position:sticky; bottom:0; }
        .campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .campo label { font-size: 13px; font-weight: 600; color: #475569; }
        .campo input, .campo select, .campo textarea { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; transition: border-color .2s; }
        .campo input:focus, .campo select:focus, .campo textarea:focus { outline: none; border-color: #2563eb; }
        .campo textarea { resize:vertical; min-height:70px; }
        .req { color: #ef4444; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .btn-sec { padding: 9px 16px; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 600; color: #475569; cursor: pointer; }
        .btn-ok { padding: 9px 16px; background: #2563eb; border: none; border-radius: 8px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-ok:hover { background: #1d4ed8; }
        .msg-box { display: none; padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>

@endpush

@section('contenido')
    <div class="rep-wrap">
        <div class="rep-hdr">
            <div class="rep-hdr-text">
                <h2><i class="bi bi-tools me-2" style="color:#10b981;"></i>Catálogo de Repuestos</h2>
                <p>Gestión de inventario físico, partes, piezas y costos.</p>
            </div>
        </div>

        <div class="rep-card">
            <div class="rep-card-hdr">
                <h3><i class="bi bi-list-ul me-2" style="color:#64748b;"></i>Repuestos en Bodega (<span id="count-rep">{{ count($repuestos) }}</span>)</h3>
                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <input type="text" id="buscador" class="input-search" placeholder="Buscar por código, nombre, parte..." oninput="filtrarTabla()">
                    <a href="{{ route('repuestos.auditoria') }}" class="btn-nuevo" style="background:#4f46e5; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                        <i class="bi bi-journal-text"></i> Historial de Auditoría
                    </a>
                    <button class="btn-nuevo" onclick="abrirModal()">
                        <i class="bi bi-plus-circle"></i> Nuevo Repuesto
                    </button>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="rep-table" id="tabla-repuestos">
                    <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nro. Parte</th>
                        <th>Nombre</th>
                        <th>Stock</th>
                        <th>Costo ($)</th>
                        <th>Marca / Tipo</th>
                        <th>Bodega</th>
                        <th style="width:90px; text-align:right;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="repuestos-tbody">
                    @forelse($repuestos as $r)
                        <tr data-row="repuesto">
                            <td><strong>{{ $r->codigo }}</strong></td>
                            <td style="color:#64748b;">{{ $r->nro_parte ?: '-' }}</td>
                            <td>{{ $r->nombre }}</td>
                            <td>
                                @php
                                    $cClass = $r->stock > 5 ? 'stock-ok' : ($r->stock > 0 ? 'stock-low' : 'stock-out');
                                @endphp
                                <span class="stock-badge {{ $cClass }}">{{ $r->stock }}</span>
                            </td>
                            <td>{{ number_format($r->costo, 2) }}</td>
                            <td style="font-size:12px;">{{ $r->marca_id ?: 'N/A' }} / {{ $r->tipo_dispositivo_id ?: 'N/A' }}</td>
                            <td>{{ $r->bodega ?: '-' }}</td>
                            <td style="text-align:right;">
                                <button class="btn-action" title="Editar" onclick="abrirModal({{ json_encode($r) }})"><i class="bi bi-pencil"></i></button>
                                <button class="btn-action del" title="Eliminar" onclick="eliminarRepuesto({{ $r->id }})"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr id="tr-vacio"><td colspan="8" style="text-align:center; padding:40px; color:#94a3b8;">No se han registrado repuestos.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div id="repuestos-pager" style="padding: 10px 20px 20px;"></div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-repuesto">
        <div class="modal-box">
            <div class="modal-hdr">
                <h3 id="mr-title">Nuevo Repuesto</h3>
                <button class="btn-cerrar" onclick="cerrarModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div id="mr-msg" class="msg-box err"></div>
                <input type="hidden" id="r-id">

                <div class="grid-2">
                    <div class="campo">
                        <label>Código <span class="req">*</span></label>
                        <input type="text" id="r-codigo" maxlength="100" placeholder="Código interno" oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Nro. de Parte</label>
                        <input type="text" id="r-parte" maxlength="100" placeholder="Número de fabricante" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>

                <div class="campo">
                    <label>Nombre del Repuesto <span class="req">*</span></label>
                    <input type="text" id="r-nombre" maxlength="255" placeholder="Ej: Pantalla LED 15.6 30 pines" oninput="this.value=this.value.toUpperCase()">
                </div>

                <div class="grid-2">
                    <div class="campo">
                        <label>Stock Actual <span class="req">*</span></label>
                        <input type="number" id="r-stock" min="0" value="0">
                    </div>
                    <div class="campo">
                        <label>Costo (USD) <span class="req">*</span></label>
                        <input type="number" id="r-costo" step="0.01" min="0" value="0.00">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="campo">
                        <label>Marca</label>
                        <input type="text" id="r-marca" maxlength="100" placeholder="Ej: SAMSUNG" oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Tipo de Dispositivo</label>
                        <input type="text" id="r-tipo" maxlength="100" placeholder="Ej: LAPTOP, CELULAR" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>

                <div class="campo">
                    <label>Ubicación / Bodega</label>
                    <input type="text" id="r-bodega" maxlength="100" placeholder="Ej: Estante A2, Bodega Sur" oninput="this.value=this.value.toUpperCase()">
                </div>

                <div class="campo" style="margin-bottom:0;">
                    <label>Descripción detallada</label>
                    <textarea id="r-desc" placeholder="Especificaciones adicionales, compatibilidad..."></textarea>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn-sec" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-ok" id="btn-guardar" onclick="guardarRepuesto()"><i class="bi bi-floppy"></i> Guardar Registro</button>
            </div>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        function abrirModal(datos = null) {
            document.getElementById('mr-msg').style.display = 'none';
            const title = document.getElementById('mr-title');

            const id = document.getElementById('r-id');
            const codigo = document.getElementById('r-codigo');
            const parte = document.getElementById('r-parte');
            const nombre = document.getElementById('r-nombre');
            const stock = document.getElementById('r-stock');
            const costo = document.getElementById('r-costo');
            const marca = document.getElementById('r-marca');
            const tipo = document.getElementById('r-tipo');
            const bodega = document.getElementById('r-bodega');
            const desc = document.getElementById('r-desc');

            if (datos) {
                title.textContent = 'Editar Repuesto';
                id.value = datos.id;
                codigo.value = datos.codigo;
                parte.value = datos.nro_parte || '';
                nombre.value = datos.nombre;
                stock.value = datos.stock;
                costo.value = parseFloat(datos.costo).toFixed(2);
                marca.value = datos.marca_id;
                tipo.value = datos.tipo_dispositivo_id;
                bodega.value = datos.bodega || '';
                desc.value = datos.descripcion || '';
            } else {
                title.textContent = 'Nuevo Repuesto';
                id.value = '';
                codigo.value = '';
                parte.value = '';
                nombre.value = '';
                stock.value = '0';
                costo.value = '0.00';
                marca.value = '';
                tipo.value = '';
                bodega.value = '';
                desc.value = '';
            }

            document.getElementById('modal-repuesto').classList.add('activo');
            setTimeout(() => codigo.focus(), 100);
        }

        function cerrarModal() {
            document.getElementById('modal-repuesto').classList.remove('activo');
        }

        function mostrarError(msg) {
            const el = document.getElementById('mr-msg');
            el.textContent = msg;
            el.style.display = 'block';
        }

        async function guardarRepuesto() {
            const id = document.getElementById('r-id').value;
            const codigo = document.getElementById('r-codigo').value.trim();
            const nombre = document.getElementById('r-nombre').value.trim();
            const marca = document.getElementById('r-marca').value;
            const tipo = document.getElementById('r-tipo').value;

            if (!codigo || !nombre) {
                mostrarError('Los campos marcados con (*) son obligatorios.');
                return;
            }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', id ? 'editar' : 'crear');
            if (id) fd.append('id', id);
            fd.append('codigo', codigo);
            fd.append('nro_parte', document.getElementById('r-parte').value.trim());
            fd.append('nombre', nombre);
            fd.append('stock', document.getElementById('r-stock').value);
            fd.append('costo', document.getElementById('r-costo').value);
            fd.append('marca_id', marca);
            fd.append('tipo_dispositivo_id', tipo);
            fd.append('bodega', document.getElementById('r-bodega').value.trim());
            fd.append('descripcion', document.getElementById('r-desc').value.trim());

            const btn = document.getElementById('btn-guardar');
            btn.disabled = true;

            try {
                const r = await fetch('{{ route("repuestos.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json().catch(() => null);

                if (r.ok && d && d.ok) {
                    location.reload();
                } else {
                    const msg = (d && (d.error || d.mensaje)) ? (d.error || d.mensaje) : `Error (${r.status}): ${r.statusText || 'Acceso denegado o error del servidor'}`;
                    mostrarError(msg);
                }
            } catch (e) {
                mostrarError('Error de comunicación con el servidor: ' + e.message);
            } finally {
                btn.disabled = false;
            }
        }

        async function eliminarRepuesto(id) {
            if (!confirm('¿Confirma que desea eliminar el repuesto seleccionado? Esta acción no se puede deshacer.')) return;

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('accion', 'eliminar');
            fd.append('id', id);

            try {
                const r = await fetch('{{ route("repuestos.guardar") }}', { method: 'POST', body: fd });
                const d = await r.json();
                if (d.ok) location.reload();
                else alert(d.error);
            } catch (e) {
                alert('Error de comunicación con el servidor.');
            }
        }

        function filtrarTabla() {
            const q = document.getElementById('buscador').value.toLowerCase();
            const filas = document.querySelectorAll('#repuestos-tbody tr[data-row="repuesto"]');
            let conteo = 0;

            filas.forEach(tr => {
                if (tr.textContent.toLowerCase().includes(q)) {
                    tr.style.display = '';
                    conteo++;
                } else {
                    tr.style.display = 'none';
                }
            });

            document.getElementById('count-rep').textContent = conteo;
        }

        let _repPager = null;
        document.addEventListener('DOMContentLoaded', () => {
            _repPager = new SgnPager({
                containerSelector: '#repuestos-tbody',
                itemSelector: 'tr[data-row="repuesto"]',
                pagerContainerSelector: '#repuestos-pager',
                pageSize: 15
            });
        });
    </script>
@endpush
