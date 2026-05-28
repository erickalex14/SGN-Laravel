@extends('layouts.app')
@section('titulo', 'Informes Técnicos')

@push('css_adicional')
    <style>
        /* CSS basado en los estandares estructurales de SGN para modulos de operaciones */
        .inf-wrap { max-width: 1200px; margin: 0 auto; padding: 28px 24px; }
        .inf-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
        .inf-hdr h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
        .inf-hdr p { margin: 0; color: #64748b; font-size: 14px; }
        .inf-tabs { display: flex; gap: 8px; margin-bottom: 24px; }
        .inf-tab { padding: 10px 24px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; color: #475569; font-weight: 700; font-size: 13.5px; cursor: pointer; transition: all .2s; }
        .inf-tab:hover { background: #e2e8f0; }
        .inf-tab.activo { background: #2563eb; color: #fff; border-color: #2563eb; }
        .inf-panel { display: none; }
        .inf-panel.activo { display: block; }
        .inf-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; margin-bottom: 24px; }
        .inf-card-hdr { padding: 16px 20px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-weight: 700; color: #1e293b; display: flex; align-items: center; justify-content: space-between; }
        .inf-card-body { padding: 24px; }
        .inf-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .inf-table th { background: #f8fafc; padding: 12px 16px; text-align: left; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        .inf-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
        .inf-table tr:hover td { background: #f8fafc; }
        .campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        .campo label { font-size: 13px; font-weight: 600; color: #475569; }
        .campo textarea, .campo select, .campo input { padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; background: #fff; transition: border-color .2s; }
        .campo textarea:focus, .campo select:focus, .campo input:focus { outline: none; border-color: #2563eb; }
        .campo textarea { min-height: 90px; resize: vertical; }
        .req { color: #ef4444; }
        .btn-submit { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: opacity .2s; }
        .btn-submit:hover { opacity: .9; }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
        .btn-accion { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-accion:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
        .msg-box { display: none; padding: 14px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
        .msg-box.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .msg-box.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .file-upload-wrapper { border: 2px dashed #cbd5e1; padding: 20px; border-radius: 8px; text-align: center; background: #f8fafc; cursor: pointer; transition: border-color .2s; }
        .file-upload-wrapper:hover { border-color: #2563eb; }
        .file-upload-wrapper input[type="file"] { display: none; }
        .file-list { margin-top: 10px; font-size: 12px; color: #475569; text-align: left; }
        .ord-badge { font-family: monospace; font-size: 13px; font-weight: 700; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; color: #0f172a; border: 1px solid #cbd5e1; }
    </style>
@endpush

@section('contenido')
    <div class="inf-wrap">
        <div class="inf-hdr">
            <div>
                <h2><i class="bi bi-file-earmark-medical" style="color:#2563eb;"></i> Informes Técnicos</h2>
                <p>Generación y consulta de documentación técnica final de los equipos.</p>
            </div>
        </div>

        <div class="inf-tabs">
            <button class="inf-tab activo" onclick="infTab('generar', this)">Generar Informe</button>
            <button class="inf-tab" onclick="infTab('historial', this)">Historial de Informes</button>
        </div>

        <div id="inf-msg" class="msg-box"></div>

        <div class="inf-panel activo" id="panel-generar">
            <form id="form-informe" onsubmit="event.preventDefault(); guardarInforme();">
                <div class="inf-card">
                    <div class="inf-card-hdr">
                        <span><i class="bi bi-info-circle me-2"></i> Detalles de la Orden</span>
                    </div>
                    <div class="inf-card-body">
                        <div class="campo">
                            <label>Seleccione la Orden a Reportar <span class="req">*</span></label>
                            <select id="orden_id" required onchange="cargarInformeExistente(this.value)">
                                <option value="">-- Seleccione una orden pendiente --</option>
                                @foreach($ordenesPendientes as $ord)
                                    <option value="{{ $ord->id }}">
                                        {{ $ord->nro_orden }} - {{ $ord->cliente_nombre }} ({{ $ord->equipo_nombre ?: 'Equipo no especificado' }}){{ ($ord->tipo_orden ?? '') === 'empresa' && !empty($ord->nro_factura) ? ' - Ticket '.$ord->nro_factura : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="info-informe-existente" style="display:none;margin-top:8px;padding:9px 12px;border:1px solid #bfdbfe;background:#eff6ff;color:#1e40af;border-radius:8px;font-size:12.5px;">
                                Esta orden ya tiene informe registrado. Puedes actualizarlo y volver a guardar.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inf-card">
                    <div class="inf-card-hdr">
                        <span><i class="bi bi-clipboard2-pulse me-2"></i> Estructura del Informe Técnico</span>
                    </div>
                    <div class="inf-card-body">
                        <div class="campo">
                            <label>Antecedentes / Problema Inicial <span class="req">*</span></label>
                            <textarea id="antecedentes" required placeholder="Describa cómo ingresó el equipo y la falla reportada..."></textarea>
                        </div>
                        <div class="campo">
                            <label>Proceso Técnico Realizado <span class="req">*</span></label>
                            <textarea id="proceso" required placeholder="Detalle los pasos técnicos ejecutados, mediciones, cambios de repuestos..."></textarea>
                        </div>
                        <div class="campo">
                            <label>Conclusión / Diagnóstico Final <span class="req">*</span></label>
                            <textarea id="conclusion" required placeholder="Causa raíz del problema detectado..."></textarea>
                        </div>
                        <div class="campo">
                            <label>Recomendaciones para el Cliente</label>
                            <textarea id="recomendaciones" placeholder="Ej: Realizar mantenimiento preventivo cada 6 meses..."></textarea>
                        </div>
                        <div class="campo">
                            <label>Estado Final del Equipo <span class="req">*</span></label>
                            <select id="estado_equipo" required>
                                <option value="Operativo">Operativo</option>
                                <option value="Reparado parcialmente">Reparado parcialmente</option>
                                <option value="Desguace">Desguace</option>
                                <option value="En espera de repuesto">En espera de repuesto</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="inf-card">
                    <div class="inf-card-hdr">
                        <span><i class="bi bi-camera me-2"></i> Evidencia Fotográfica Adicional (Opcional)</span>
                    </div>
                    <div class="inf-card-body">
                        <label class="file-upload-wrapper" onclick="document.getElementById('fotos').click()">
                            <i class="bi bi-cloud-arrow-up" style="font-size:24px; color:#64748b;"></i><br>
                            <span style="font-weight:600; color:#475569;">Haga clic aquí para subir imágenes</span><br>
                            <span style="font-size:12px; color:#94a3b8;">Formatos permitidos: JPG, PNG, WEBP. (Máx. 10 archivos)</span>
                            <input type="file" id="fotos" multiple accept="image/jpeg, image/png, image/webp" onchange="mostrarArchivos()">
                        </label>
                        <div id="file-list" class="file-list"></div>
                    </div>
                </div>

                <button type="submit" id="btn-guardar" class="btn-submit">
                    <i class="bi bi-floppy"></i> Guardar y Finalizar Informe
                </button>
            </form>
        </div>

        <div class="inf-panel" id="panel-historial">
            <div class="inf-card">
                <div style="overflow-x:auto;">
                    <table class="inf-table">
                        <thead>
                        <tr>
                            <th>Nro. Orden</th>
                            <th>Fecha Informe</th>
                            <th>Cliente</th>
                            <th>Equipo</th>
                            <th>Estado Final</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($informesGenerados as $inf)
                            <tr>
                                <td><span class="ord-badge">{{ $inf->nro_orden ?? '-' }}</span></td>
                                <td>{{ !empty($inf->fecha_informe) ? \Carbon\Carbon::parse($inf->fecha_informe)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $inf->cliente_nombre ?? '-' }}</td>
                                <td>{{ $inf->equipo_nombre ?: '-' }}</td>
                                <td><strong>{{ $inf->estado_equipo }}</strong></td>
                                <td style="text-align:right;">
                                    <a href="{{ url('/operaciones/informes/'.$inf->id.'/imprimir') }}" target="_blank" class="btn-accion">
                                        <i class="bi bi-printer"></i> Imprimir PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">No ha generado informes técnicos aún.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js_adicional')
    <script>
        const _urlVerInformePorOrden = '{{ route("informes.ver") }}';
        function infTab(panel, btn) {
            document.querySelectorAll('.inf-tab').forEach(b => b.classList.remove('activo'));
            document.querySelectorAll('.inf-panel').forEach(p => p.classList.remove('activo'));
            btn.classList.add('activo');
            document.getElementById('panel-' + panel).classList.add('activo');
        }

        async function cargarInformeExistente(ordenId) {
            const aviso = document.getElementById('info-informe-existente');
            if (!ordenId) {
                if (aviso) aviso.style.display = 'none';
                return;
            }

            try {
                const r = await fetch(_urlVerInformePorOrden + '?orden_id=' + encodeURIComponent(ordenId), { cache: 'no-store' });
                const d = await r.json();
                if (!d.ok || !d.informe) {
                    if (aviso) aviso.style.display = 'none';
                    return;
                }

                document.getElementById('antecedentes').value = d.informe.antecedentes || '';
                document.getElementById('proceso').value = d.informe.proceso || '';
                document.getElementById('conclusion').value = d.informe.conclusion || '';
                document.getElementById('recomendaciones').value = d.informe.recomendaciones || '';
                if (d.informe.estado_equipo) {
                    document.getElementById('estado_equipo').value = d.informe.estado_equipo;
                }
                if (aviso) aviso.style.display = 'block';
            } catch {
                if (aviso) aviso.style.display = 'none';
            }
        }

        function mostrarArchivos() {
            const input = document.getElementById('fotos');
            const list = document.getElementById('file-list');
            list.innerHTML = '';

            if(input.files.length > 10) {
                alert('Se permite un máximo de 10 fotografías por informe.');
                input.value = '';
                return;
            }

            Array.from(input.files).forEach(file => {
                list.innerHTML += `<div><i class="bi bi-image me-1"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>`;
            });
        }

        function mostrarMensaje(isError, texto) {
            const box = document.getElementById('inf-msg');
            box.className = 'msg-box ' + (isError ? 'err' : 'ok');
            box.innerHTML = texto;
            box.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function guardarInforme() {
            const ordenId = document.getElementById('orden_id').value;
            if(!ordenId) {
                mostrarMensaje(true, 'Debe seleccionar una orden pendiente.');
                return;
            }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('orden_id', ordenId);
            fd.append('antecedentes', document.getElementById('antecedentes').value.trim());
            fd.append('proceso', document.getElementById('proceso').value.trim());
            fd.append('conclusion', document.getElementById('conclusion').value.trim());
            fd.append('recomendaciones', document.getElementById('recomendaciones').value.trim());
            fd.append('estado_equipo', document.getElementById('estado_equipo').value);

            const archivos = document.getElementById('fotos').files;
            Array.from(archivos).forEach(file => {
                fd.append('fotos[]', file);
            });

            const btn = document.getElementById('btn-guardar');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando informe...';

            try {
                const r = await fetch('{{ route("informes.store") }}', { method:'POST', body:fd });
                const d = await r.json();

                if(d.ok) {
                    mostrarMensaje(false, `<strong>¡Completado!</strong> ${d.mensaje}`);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    mostrarMensaje(true, d.error);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar y Finalizar Informe';
                }
            } catch(e) {
                mostrarMensaje(true, 'Error de comunicación. Verifique su conexión y el peso de las imágenes.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar y Finalizar Informe';
            }
        }
    </script>
@endpush
