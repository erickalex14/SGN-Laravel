@extends('layouts.app')
@section('titulo', 'Gestión de Caja Chica y Caja Grande')

@push('css_adicional')
<style>
    .caja-wrap { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .caja-hdr { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; flex-wrap: wrap; }
    .caja-hdr h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
    
    .caja-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
    .caja-card { border-radius: 16px; padding: 24px; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.08); transition: transform 0.3s; }
    .caja-card:hover { transform: translateY(-4px); }
    .caja-card.chica { background: linear-gradient(135deg, #0284c7, #0369a1); }
    .caja-card.grande { background: linear-gradient(135deg, #059669, #047857); }
    .caja-card-bg-icon { position: absolute; right: -20px; bottom: -20px; font-size: 120px; opacity: 0.15; pointer-events: none; }
    
    .caja-card-lbl { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; font-weight: 700; margin-bottom: 6px; }
    .caja-card-val { font-size: 36px; font-weight: 800; font-family: monospace; }
    .caja-card-status { margin-top: 15px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 99px; }

    .seccion { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 24px; }
    .seccion-hdr { padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #f8fafc; }
    .seccion-title { margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .seccion-body { padding: 24px; }

    .caja-btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px; padding: 10px 18px; border-radius: 8px; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
    .caja-btn.primary { background: #2563eb; color: white; }
    .caja-btn.primary:hover { background: #1d4ed8; }
    .caja-btn.accent { background: #475569; color: white; }
    .caja-btn.accent:hover { background: #334155; }
    .caja-btn.danger { background: #ef4444; color: white; }
    .caja-btn.danger:hover { background: #dc2626; }
    
    .caja-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .caja-table th { padding: 14px 16px; background: #f8fafc; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; }
    .caja-table td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
    .caja-table tr:hover { background: #f8fafc; }

    .caja-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 99px; display: inline-block; }
    .caja-badge.ingreso { background: #dcfce7; color: #166534; }
    .caja-badge.egreso { background: #fef2f2; color: #991b1b; }
    .caja-badge.chica { background: #e0f2fe; color: #0369a1; }
    .caja-badge.grande { background: #d1fae5; color: #065f46; }

    .caja-modal { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
    .caja-modal.open { display: flex; }
    .caja-modal-content { background: white; border-radius: 16px; width: 100%; max-width: 550px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden; animation: modalAnim 0.3s; }
    .caja-modal-hdr { background: #f8fafc; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .caja-modal-hdr h3 { margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; }
    .caja-modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b; }
    .caja-modal-body { padding: 24px; max-height: 80vh; overflow-y: auto; }

    .caja-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .caja-field label { font-size: 13px; font-weight: 600; color: #475569; }
    .caja-field input, .caja-field select, .caja-field textarea { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
    .caja-field input:focus, .caja-field select:focus, .caja-field textarea:focus { border-color: #2563eb; }

    @keyframes modalAnim {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    @media (max-width: 768px) {
        .caja-cards { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('contenido')
<div class="caja-wrap">
    <div class="caja-hdr">
        <h2>
            <i class="bi bi-safe" style="color: #2563eb;"></i>
            Caja General (Chica / Grande)
        </h2>
        @if($mesAbierto && !$mesCerrado)
            <button onclick="abrirModalRegistrar()" class="caja-btn primary">
                <i class="bi bi-plus-lg"></i> Registrar Movimiento
            </button>
        @else
            <a href="{{ route('caja.apertura') }}" class="caja-btn primary">
                <i class="bi bi-calendar-plus"></i> Aperturar Mes
            </a>
        @endif
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
    @endif

    @if(!$mesAbierto)
        <div style="background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
            <i class="bi bi-exclamation-circle-fill" style="font-size: 20px;"></i>
            <div>
                <strong>El mes actual no se encuentra aperturado en este módulo.</strong><br>
                Para poder registrar gastos o ingresos, un administrador debe realizar la recarga mensual.
                <a href="{{ route('caja.apertura') }}" style="color: #b45309; text-decoration: underline; font-weight: 700; margin-left: 5px;">Aperturar ahora &rarr;</a>
            </div>
        </div>
    @endif

    <!-- Cards de saldos -->
    <div class="caja-cards">
        <div class="caja-card chica">
            <i class="bi bi-wallet2 caja-card-bg-icon"></i>
            <div class="caja-card-lbl">Caja Chica</div>
            <div class="caja-card-val">${{ number_format($cajaChicaBalance, 2) }}</div>
            <div class="caja-card-status">
                <i class="bi bi-info-circle"></i> Gastos menores / Caja Sucursal
            </div>
        </div>
        <div class="caja-card grande">
            <i class="bi bi-safe caja-card-bg-icon"></i>
            <div class="caja-card-lbl">Caja Grande</div>
            <div class="caja-card-val">${{ number_format($cajaGrandeBalance, 2) }}</div>
            <div class="caja-card-status">
                <i class="bi bi-info-circle"></i> Caja general de resguardo / Depósitos
            </div>
        </div>
    </div>

    <!-- Filtros y Tabla -->
    <div class="seccion">
        <div class="seccion-hdr">
            <h3 class="seccion-title">
                <i class="bi bi-list-task"></i>
                Listado de Movimientos
            </h3>
            <form method="GET" action="{{ route('caja.movimientos') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <select name="caja_tipo" style="padding: 6px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    <option value="">-- Tipo Caja --</option>
                    <option value="chica" {{ request('caja_tipo') === 'chica' ? 'selected' : '' }}>Caja Chica</option>
                    <option value="grande" {{ request('caja_tipo') === 'grande' ? 'selected' : '' }}>Caja Grande</option>
                </select>
                <select name="mov_tipo" style="padding: 6px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    <option value="">-- Movimiento --</option>
                    <option value="ingreso" {{ request('mov_tipo') === 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                    <option value="egreso" {{ request('mov_tipo') === 'egreso' ? 'selected' : '' }}>Egreso</option>
                </select>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" style="padding: 5px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" style="padding: 5px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                
                <button type="submit" class="caja-btn accent" style="padding: 6px 12px; font-size: 13px;">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
                <a href="{{ route('caja.movimientos') }}" class="caja-btn accent" style="padding: 6px 12px; font-size: 13px; background: #e2e8f0; color: #475569;">
                    Limpiar
                </a>
            </form>
        </div>
        <div class="seccion-body" style="padding: 0; overflow-x: auto;">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Caja</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                        <th>Técnico</th>
                        <th>Registrado por</th>
                        <th>Soportes</th>
                        @if($esSuper)
                            <th style="text-align: center;">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        <tr>
                            <td style="font-weight: 600;">{{ $mov->fecha ? $mov->fecha->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="caja-badge {{ $mov->caja->tipo }}">
                                    {{ $mov->caja->tipo === 'chica' ? 'Chica' : 'Grande' }}
                                </span>
                            </td>
                            <td>
                                <span class="caja-badge {{ $mov->tipo }}">
                                    {{ ucfirst($mov->tipo) }}
                                </span>
                            </td>
                            <td style="text-transform: capitalize; font-size: 12px;">{{ $mov->categoria }}</td>
                            <td style="font-family: monospace; font-weight: 700; color: {{ $mov->tipo === 'ingreso' ? '#166534' : '#991b1b' }}">
                                {{ $mov->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                            </td>
                            <td style="max-width: 250px; word-break: break-word;">{{ $mov->descripcion }}</td>
                            <td>{{ $mov->tecnico->nombre_tecnico ?? '-' }}</td>
                            <td style="font-size: 12px;">{{ $mov->usuario->nombre_tecnico ?? ($mov->usuario->usuario ?? '-') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    @if($mov->justificante_1)
                                        <a href="{{ asset('storage/' . $mov->justificante_1) }}" target="_blank" class="caja-btn accent" style="padding: 4px 8px; font-size: 11px;" title="Ver Comprobante Obligatorio">
                                            <i class="bi bi-file-earmark-pdf"></i> Doc 1
                                        </a>
                                    @endif
                                    @if($mov->justificante_2)
                                        <a href="{{ asset('storage/' . $mov->justificante_2) }}" target="_blank" class="caja-btn accent" style="padding: 4px 8px; font-size: 11px;" title="Ver Comprobante Opcional">
                                            <i class="bi bi-file-earmark-pdf"></i> Doc 2
                                        </a>
                                    @endif
                                    @if(!$mov->justificante_1 && !$mov->justificante_2)
                                        <span style="color: #94a3b8; font-size: 12px; font-style: italic;">Sin soportes</span>
                                    @endif
                                </div>
                            </td>
                            @if($esSuper)
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <button onclick="abrirModalEditar({{ json_encode($mov) }})" class="caja-btn accent" style="padding: 6px 10px; font-size: 12px; background: #f1f5f9; color: #475569;" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('caja.movimiento.destroy', ['id' => $mov->id]) }}" onsubmit="return confirm('¿Está seguro de eliminar este movimiento? Se ajustará automáticamente el saldo de la caja.');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="caja-btn danger" style="padding: 6px 10px; font-size: 12px;" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $esSuper ? 10 : 9 }}" style="text-align: center; color: #94a3b8; padding: 30px;">
                                No se encontraron movimientos de caja registrados en esta búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div style="padding: 16px 24px;">
                {{ $movimientos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Movimiento -->
<div class="caja-modal" id="modal-registrar">
    <div class="caja-modal-content">
        <div class="caja-modal-hdr">
            <h3>Registrar Nuevo Movimiento</h3>
            <button onclick="cerrarModalRegistrar()" class="caja-modal-close">&times;</button>
        </div>
        <div class="caja-modal-body">
            <form method="POST" action="{{ route('caja.movimiento.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="caja-field">
                    <label for="caja_id">Caja Destino <span style="color:red;">*</span></label>
                    <select name="caja_id" id="caja_id" required>
                        <option value="{{ $cajaChica->id ?? '' }}">Caja Chica (Saldo: ${{ number_format($cajaChicaBalance, 2) }})</option>
                        <option value="{{ $cajaGrande->id ?? '' }}">Caja Grande (Saldo: ${{ number_format($cajaGrandeBalance, 2) }})</option>
                    </select>
                </div>

                <div class="caja-field">
                    <label for="tipo">Tipo Movimiento <span style="color:red;">*</span></label>
                    <select name="tipo" id="tipo" required onchange="toggleJustificanteObligatorio()">
                        <option value="egreso">Egreso / Gasto</option>
                        <option value="ingreso">Ingreso</option>
                    </select>
                </div>

                <div class="caja-field">
                    <label for="monto">Monto ($) <span style="color:red;">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="monto" id="monto" required placeholder="0.00">
                </div>

                <div class="caja-field">
                    <label for="fecha">Fecha <span style="color:red;">*</span></label>
                    <input type="date" name="fecha" id="fecha" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="caja-field">
                    <label for="tecnico_id">Técnico Beneficiario (Opcional)</label>
                    <select name="tecnico_id" id="tecnico_id">
                        <option value="">-- Seleccionar Técnico (Si aplica) --</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}">{{ $tec->nombre_tecnico }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="caja-field">
                    <label for="descripcion">Descripción / Concepto <span style="color:red;">*</span></label>
                    <textarea name="descripcion" id="descripcion" rows="3" required placeholder="Detalle el motivo del gasto o ingreso..."></textarea>
                </div>

                <div class="caja-field">
                    <label id="lbl-justificante-1" for="justificante_1">Justificante Obligatorio (Doc 1) <span style="color:red;">*</span></label>
                    <input type="file" name="justificante_1" id="justificante_1" accept="application/pdf,image/*">
                </div>

                <div class="caja-field">
                    <label for="justificante_2">Justificante Opcional (Doc 2)</label>
                    <input type="file" name="justificante_2" id="justificante_2" accept="application/pdf,image/*">
                </div>

                <button type="submit" class="caja-btn primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    Guardar Registro
                </button>
            </form>
        </div>
    </div>
</div>

@if($esSuper)
<!-- Modal Editar Movimiento -->
<div class="caja-modal" id="modal-editar">
    <div class="caja-modal-content">
        <div class="caja-modal-hdr">
            <h3>Editar Movimiento</h3>
            <button onclick="cerrarModalEditar()" class="caja-modal-close">&times;</button>
        </div>
        <div class="caja-modal-body">
            <form id="form-editar" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="caja-field">
                    <label>Tipo Caja</label>
                    <input type="text" id="edit_caja_nombre" readonly style="background: #f1f5f9; color: #64748b;">
                </div>

                <div class="caja-field">
                    <label>Tipo Movimiento</label>
                    <input type="text" id="edit_tipo" readonly style="background: #f1f5f9; color: #64748b;">
                </div>

                <div class="caja-field">
                    <label for="edit_monto">Monto ($) <span style="color:red;">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="monto" id="edit_monto" required>
                </div>

                <div class="caja-field">
                    <label for="edit_fecha">Fecha <span style="color:red;">*</span></label>
                    <input type="date" name="fecha" id="edit_fecha" required>
                </div>

                <div class="caja-field">
                    <label for="edit_tecnico_id">Técnico Beneficiario (Opcional)</label>
                    <select name="tecnico_id" id="edit_tecnico_id">
                        <option value="">-- Seleccionar Técnico (Si aplica) --</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}">{{ $tec->nombre_tecnico }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="caja-field">
                    <label for="edit_descripcion">Descripción / Concepto <span style="color:red;">*</span></label>
                    <textarea name="descripcion" id="edit_descripcion" rows="3" required></textarea>
                </div>

                <div class="caja-field">
                    <label for="edit_justificante_1">Reemplazar Justificante 1 (Opcional)</label>
                    <input type="file" name="justificante_1" id="edit_justificante_1" accept="application/pdf,image/*">
                </div>

                <div class="caja-field">
                    <label for="edit_justificante_2">Reemplazar Justificante 2 (Opcional)</label>
                    <input type="file" name="justificante_2" id="edit_justificante_2" accept="application/pdf,image/*">
                </div>

                <button type="submit" class="caja-btn primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    Actualizar Registro
                </button>
            </form>
        </div>
    </div>
</div>
@endif

@push('js_adicional')
<script>
    function abrirModalRegistrar() {
        document.getElementById('modal-registrar').classList.add('open');
        toggleJustificanteObligatorio();
    }
    function cerrarModalRegistrar() {
        document.getElementById('modal-registrar').classList.remove('open');
    }
    
    function toggleJustificanteObligatorio() {
        const tipoSel = document.getElementById('tipo').value;
        const file1 = document.getElementById('justificante_1');
        const lbl = document.getElementById('lbl-justificante-1');
        if (tipoSel === 'egreso') {
            file1.setAttribute('required', 'required');
            lbl.innerHTML = 'Justificante Obligatorio (Doc 1) <span style="color:red;">*</span>';
        } else {
            file1.removeAttribute('required');
            lbl.innerHTML = 'Justificante Opcional (Doc 1)';
        }
    }

    @if($esSuper)
    function abrirModalEditar(mov) {
        document.getElementById('edit_caja_nombre').value = mov.caja.tipo === 'chica' ? 'Caja Chica' : 'Caja Grande';
        document.getElementById('edit_tipo').value = mov.tipo === 'ingreso' ? 'Ingreso' : 'Egreso';
        document.getElementById('edit_monto').value = mov.monto;
        
        // Formatear fecha YYYY-MM-DD
        let fDate = '';
        if (mov.fecha) {
            fDate = mov.fecha.split('T')[0];
        }
        document.getElementById('edit_fecha').value = fDate;
        
        document.getElementById('edit_tecnico_id').value = mov.tecnico_id || '';
        document.getElementById('edit_descripcion').value = mov.descripcion;
        
        // Cambiar acción del formulario dinámicamente
        document.getElementById('form-editar').action = `/operaciones/caja/movimientos/${mov.id}/editar`;
        
        document.getElementById('modal-editar').classList.add('open');
    }

    function cerrarModalEditar() {
        document.getElementById('modal-editar').classList.remove('open');
    }
    @endif
</script>
@endpush
@endsection
