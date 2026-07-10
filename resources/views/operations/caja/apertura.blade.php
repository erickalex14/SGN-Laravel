@extends('layouts.app')
@section('titulo', 'Apertura y Cierre de Mes - Caja')

@push('css_adicional')
<style>
    .caja-wrap { max-width: 1000px; margin: 0 auto; padding: 20px; }
    .caja-hdr { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; flex-wrap: wrap; }
    .caja-hdr h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }

    .seccion { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 24px; }
    .seccion-hdr { padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .seccion-title { margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .seccion-body { padding: 24px; }

    .caja-btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px; padding: 10px 18px; border-radius: 8px; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
    .caja-btn.primary { background: #2563eb; color: white; }
    .caja-btn.primary:hover { background: #1d4ed8; }
    .caja-btn.danger { background: #ef4444; color: white; }
    .caja-btn.danger:hover { background: #dc2626; }
    
    .caja-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .caja-table th { padding: 14px 16px; background: #f8fafc; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; }
    .caja-table td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
    
    .caja-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 99px; display: inline-block; }
    .caja-badge.abierto { background: #dcfce7; color: #166534; }
    .caja-badge.cerrado { background: #f1f5f9; color: #475569; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .caja-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .caja-field label { font-size: 13px; font-weight: 600; color: #475569; }
    .caja-field input, .caja-field select { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
    .caja-field input:focus, .caja-field select:focus { border-color: #2563eb; }

    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('contenido')
<div class="caja-wrap">
    <div class="caja-hdr">
        <h2>
            <i class="bi bi-calendar-check" style="color: #2563eb;"></i>
            Apertura y Cierre Mensual
        </h2>
        <a href="{{ route('caja.movimientos') }}" class="caja-btn" style="background: #f1f5f9; color: #475569;">
            <i class="bi bi-arrow-left"></i> Volver a Caja
        </a>
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

    <div class="grid-2">
        <!-- Formulario Apertura -->
        <div class="seccion">
            <div class="seccion-hdr">
                <h3 class="seccion-title">
                    <i class="bi bi-calendar-plus"></i>
                    Aperturar Nuevo Mes
                </h3>
            </div>
            <div class="seccion-body">
                <form method="POST" action="{{ route('caja.apertura.store') }}">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="caja-field">
                            <label for="mes">Mes <span style="color:red;">*</span></label>
                            <select name="mes" id="mes" required>
                                @php
                                    $meses = [
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                    ];
                                    $mesActual = (int) date('n');
                                @endphp
                                @foreach($meses as $num => $nombre)
                                    <option value="{{ $num }}" {{ $mesActual === $num ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="caja-field">
                            <label for="anio">Año <span style="color:red;">*</span></label>
                            <select name="anio" id="anio" required>
                                @php $anioActual = (int) date('Y'); @endphp
                                <option value="{{ $anioActual }}">{{ $anioActual }}</option>
                                <option value="{{ $anioActual + 1 }}">{{ $anioActual + 1 }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="caja-field">
                        <label for="monto_ingreso_chica">Asignación Mensual Caja Chica ($) <span style="color:red;">*</span></label>
                        <input type="number" step="0.01" min="0" name="monto_ingreso_chica" id="monto_ingreso_chica" required placeholder="0.00">
                        <small style="color: #64748b; font-size: 11px;">Monto que se ingresará como recarga al iniciar el mes.</small>
                    </div>

                    <div class="caja-field">
                        <label for="monto_ingreso_grande">Asignación Mensual Caja Grande ($) <span style="color:red;">*</span></label>
                        <input type="number" step="0.01" min="0" name="monto_ingreso_grande" id="monto_ingreso_grande" required placeholder="0.00">
                        <small style="color: #64748b; font-size: 11px;">Monto que se ingresará como recarga al iniciar el mes.</small>
                    </div>

                    <button type="submit" class="caja-btn primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                        <i class="bi bi-calendar-check"></i> Aperturar Mes
                    </button>
                </form>
            </div>
        </div>

        <!-- Explicación de flujos -->
        <div class="seccion" style="background: #f8fafc; border-style: dashed;">
            <div class="seccion-hdr" style="background: none; border-bottom: none;">
                <h3 class="seccion-title" style="color:#0f172a;">
                    <i class="bi bi-info-circle-fill" style="color:#2563eb;"></i>
                    Funcionamiento del Cierre
                </h3>
            </div>
            <div class="seccion-body" style="font-size: 13.5px; line-height: 1.6; color: #475569;">
                <p>El flujo del balance mensual funciona de la siguiente manera:</p>
                <ol style="padding-left: 20px; margin-bottom: 0;">
                    <li style="margin-bottom: 8px;">Al <strong>Aperturar un mes</strong>, el sistema verifica si existe el mes anterior en el historial.</li>
                    <li style="margin-bottom: 8px;">Si el mes anterior ya está <strong>Cerrado</strong>, su balance remanente final se traslada automáticamente como el <strong>Saldo Inicial</strong> del nuevo mes.</li>
                    <li style="margin-bottom: 8px;">Al <strong>Cerrar un mes manualmente</strong>, el saldo actual proyectado de ingresos y egresos de ese mes se congela definitivamente, impidiendo registrar o alterar movimientos en dicho periodo.</li>
                    <li>El sistema enviará una alerta automática por correo electrónico a los administradores el último día del mes para recordar el cierre formal de las cajas.</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Historial de Meses -->
    <div class="seccion">
        <div class="seccion-hdr">
            <h3 class="seccion-title">
                <i class="bi bi-clock-history"></i>
                Historial de Mensualidades Abiertas / Cerradas
            </h3>
        </div>
        <div class="seccion-body" style="padding: 0; overflow-x: auto;">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Año / Mes</th>
                        <th>Caja</th>
                        <th>Saldo Inicial</th>
                        <th>Recarga Recibida</th>
                        <th>Saldo Cierre / Proyectado</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $mesesNombres = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                    @endphp
                    @forelse($mensualidades as $m)
                        <tr>
                            <td style="font-weight: bold;">{{ $m->anio }} - {{ $mesesNombres[$m->mes] ?? 'Mes ' . $m->mes }}</td>
                            <td>
                                <span class="caja-badge {{ $m->caja->tipo }}">
                                    {{ $m->caja->tipo === 'chica' ? 'Chica' : 'Grande' }}
                                </span>
                            </td>
                            <td style="font-family: monospace;">${{ number_format($m->saldo_inicial, 2) }}</td>
                            <td style="font-family: monospace;">${{ number_format($m->monto_ingreso, 2) }}</td>
                            <td style="font-family: monospace; font-weight: 700;">
                                @if($m->cerrado)
                                    ${{ number_format($m->saldo_cierre, 2) }}
                                @else
                                    {{-- Proyectado actual --}}
                                    ${{ number_format($m->caja->balance, 2) }}
                                @endif
                            </td>
                            <td>
                                <span class="caja-badge {{ $m->cerrado ? 'cerrado' : 'abierto' }}">
                                    {{ $m->cerrado ? 'Cerrado' : 'Abierto' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if(!$m->cerrado)
                                    <form method="POST" action="{{ route('caja.cerrar') }}" onsubmit="return confirm('¿Está seguro de cerrar este mes? Esta acción congelará el saldo y no permitirá ingresar o editar gastos de este periodo.');" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="mensualidad_id" value="{{ $m->id }}">
                                        <button type="submit" class="caja-btn danger" style="padding: 5px 12px; font-size: 12px;">
                                            <i class="bi bi-lock-fill"></i> Cerrar Mes
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #64748b; font-size: 12px;"><i class="bi bi-check-circle-fill" style="color: #059669;"></i> Completado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 30px;">
                                No se han registrado aperturas mensuales previas en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
