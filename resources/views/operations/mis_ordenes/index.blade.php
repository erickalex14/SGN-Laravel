@extends('layouts.app')
@section('titulo', 'Mis Ordenes')

@push('css_adicional')
<style>
:root {
    --mo-blue: #1a3d7c;
    --mo-slate: #0f172a;
    --mo-muted: #64748b;
    --mo-border: #e2e8f0;
    --mo-surface: #f8fafc;
}
.mis-ordenes-container { max-width: 1060px; margin: 0 auto; padding: 28px 22px; }
.form-titulo h2 { font-size: 21px; font-weight: 800; color: var(--mo-slate); margin: 0 0 3px; }
.form-titulo p { color: var(--mo-muted); font-size: 13px; margin: 0 0 22px; }

.mo-kpis { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-bottom: 22px; }
.mo-kpi-card {
    background: #fff; border-radius: 12px; padding: 14px 10px 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,.06); border: 1.5px solid var(--mo-border);
    cursor: pointer; transition: all .18s; text-align: center; position: relative; overflow: hidden;
}
.mo-kpi-card::before { content:''; position:absolute; inset:0 0 auto 0; height:3px; background: currentColor; opacity:.25; }
.mo-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); border-color: #94a3b8; }
.mo-kpi-card.activo { outline: 2.5px solid currentColor; outline-offset: -1px; box-shadow: 0 4px 18px rgba(0,0,0,.12); }
.mo-kpi-card.activo::before { opacity: 1; }
.mo-kpi-num { font-size: 28px; font-weight: 900; line-height: 1; margin-bottom: 4px; }
.mo-kpi-lbl { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .7; }
.mo-kpi-pendiente { color: #b45309; }
.mo-kpi-en-proceso { color: #1d4ed8; }
.mo-kpi-finalizada { color: #15803d; }
.mo-kpi-nota-cred { color: #9d174d; }
.mo-kpi-entregada { color: #0f766e; }

.panel-bloque { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,.06); margin-bottom: 22px; overflow: hidden; border: 1px solid var(--mo-border); }
.panel-header { display: flex; align-items: center; gap: 12px; padding: 15px 20px; border-bottom: 1px solid var(--mo-border); }
.panel-header h3 { font-size: 14px; font-weight: 700; margin: 0; flex: 1; color: var(--mo-slate); }
.panel-icon { font-size: 17px; }
.asignadas-header { background: linear-gradient(135deg,#eff6ff,#dbeafe); color: #1e40af; }
.panel-count { background: rgba(0,0,0,.08); padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }

.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; padding: 16px; }
.empty-msg { grid-column: 1/-1; text-align: center; color: var(--mo-muted); font-size: 13px; padding: 32px 0; }

.orden-card {
    background: #fff; border: 1.5px solid var(--mo-border); border-radius: 13px; overflow: hidden;
    transition: box-shadow .18s, border-color .18s, transform .18s; display: flex; flex-direction: column;
}
.orden-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.09); border-color: #93c5fd; transform: translateY(-2px); }
.orden-card[data-estado="Pendiente"] { border-top: 3px solid #f59e0b; }
.orden-card[data-estado="En proceso"] { border-top: 3px solid #3b82f6; }
.orden-card[data-estado="Finalizada"] { border-top: 3px solid #10b981; }
.orden-card[data-estado="Entregada"] { border-top: 3px solid #0d9488; }
.orden-card[data-estado="Nota de Credito"] { border-top: 3px solid #db2777; }
.card-top { display: flex; align-items: center; justify-content: space-between; padding: 11px 14px 7px; cursor: pointer; }
.card-nro { font-family: 'Courier New', monospace; font-weight: 800; font-size: 13.5px; color: var(--mo-blue); letter-spacing: .02em; }
.card-cliente { padding: 0 14px 3px; font-size: 13.5px; font-weight: 700; color: var(--mo-slate); cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.card-equipo { padding: 0 14px 3px; font-size: 12px; color: #475569; cursor: pointer; }
.card-serie { padding: 0 14px 5px; font-size: 11px; color: #94a3b8; }
.card-creds {
    margin: 2px 14px 0; font-size: 11.5px; font-weight: 600; color: #2563eb; cursor: pointer;
    display: inline-flex; align-items: center; background: #eff6ff; padding: 3px 10px; border-radius: 20px;
    transition: background .15s; width: fit-content;
}
.card-creds:hover { background: #dbeafe; }
.card-meta-row { padding: 7px 14px 10px; display: flex; flex-wrap: wrap; gap: 5px; align-items: center; border-top: 1px solid #f1f5f9; margin-top: auto; }
.tarj-meta { font-size: 11px; color: var(--mo-muted); display: inline-flex; align-items: center; gap: 3px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 999px; padding: 2px 8px; }

.card-actions { display: flex; gap: 6px; padding: 8px 12px 11px; border-top: 1px solid #f1f5f9; }
.btn-accion {
    flex: 1; border: none; padding: 7px 8px; border-radius: 8px; font-size: 11.5px; font-weight: 600;
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    transition: all .15s; text-decoration: none;
}
.btn-accion:hover { opacity: .87; transform: translateY(-1px); }
.btn-detalle-orden { background: var(--mo-slate); color: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.22); }
.btn-detalle-orden:hover { color: #fff; }
.btn-imprimir-ot { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.btn-imprimir-nc { background: #fdf2f8; color: #9d174d; border: 1px solid #f9a8d4; }
.btn-nc-pendiente { background: #fefce8; color: #92400e; border: 1px solid #fde68a; cursor:not-allowed; opacity:.65; }

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(4px); }
.modal-box {
    background: #fff; border-radius: 18px; max-width: 640px; width: 96%; max-height: 90vh; overflow-y: auto;
    position: relative; box-shadow: 0 24px 60px rgba(0,0,0,.22); animation: modalIn .22s ease;
}
@keyframes modalIn { from { opacity:0; transform:scale(.96) translateY(8px); } to { opacity:1; transform:scale(1) translateY(0); } }
.modal-close {
    position: absolute; top: 14px; right: 16px; background: #f1f5f9; border: none; width: 30px; height: 30px;
    border-radius: 50%; font-size: 16px; cursor: pointer; color: var(--mo-muted); line-height: 1; display: flex; align-items: center; justify-content: center;
}
.modal-close:hover { background: #e2e8f0; color: var(--mo-slate); }
.det-wrap { padding: 22px 22px 18px; }
.det-titulo { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px; }
.det-nro { font-family: monospace; font-size: 19px; font-weight: 900; color: var(--mo-blue); }
.det-badges { display: flex; gap: 7px; flex-wrap: wrap; }
.det-seccion h4 { font-size: 11px; font-weight: 700; color: var(--mo-muted); text-transform: uppercase; letter-spacing: .06em; margin: 0 0 10px; display: flex; align-items: center; gap: 6px; }
.det-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; margin-bottom: 4px; }
.det-campo { display: flex; flex-direction: column; gap: 2px; }
.det-campo label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
.det-campo span { font-size: 13px; color: var(--mo-slate); }
.det-full { grid-column: 1/-1; }
.det-sep { border: none; border-top: 1px solid #f1f5f9; margin: 14px 0; }

.gestion-panel { background: #f8fafc; border: 1px solid var(--mo-border); border-radius: 12px; padding: 16px; margin-top: 4px; }
.gestion-panel-title { font-size: 11px; font-weight: 800; color: var(--mo-muted); text-transform: uppercase; letter-spacing: .07em; margin: 0 0 12px; display: flex; align-items: center; gap: 6px; }
.gestion-row {
    display: grid; grid-template-columns: 28px 100px 1fr 28px; align-items: center; gap: 8px; background: #fff;
    border: 1.5px solid var(--mo-border); border-radius: 9px; padding: 9px 12px; margin-bottom: 8px;
}
.gestion-row:last-child { margin-bottom: 0; }
.gestion-row.garantia-row { border-color: #e9d5ff; background: #faf5ff; }
.gestion-row.repuesto-row { border-color: #bbf7d0; background: #f0fdf4; }
.gestion-icon { font-size: 15px; text-align: center; }
.gestion-label { font-size: 11.5px; font-weight: 700; color: #475569; white-space: nowrap; }
.gestion-select {
    width: 100%; border: 1.5px solid var(--mo-border); border-radius: 7px; padding: 6px 8px; font-size: 12.5px; font-weight: 600;
    color: var(--mo-slate); background: #fff; cursor: pointer; font-family: inherit; outline: none;
}
.gestion-feedback { font-size: 15px; text-align: center; min-width: 20px; }
.gestion-actions-rep { margin-top: 10px; display: flex; gap: 8px; }
.btn-mini-rep {
    border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; border-radius: 7px; padding: 6px 10px;
    font-size: 12px; font-weight: 700; cursor: pointer;
}
.btn-mini-rep.danger { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
.rep-picker { margin-top: 10px; position: relative; }
.rep-input {
    width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 7px 10px; font-size: 12.5px; outline: none;
    transition: border-color .18s, box-shadow .18s, background .18s;
}
.rep-input:focus { border-color: #1d4ed8; box-shadow: 0 0 0 2px rgba(37,99,235,.12); }
.rep-resultados {
    display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1.5px solid #cbd5e1;
    border-radius: 10px; box-shadow: 0 14px 28px rgba(15,23,42,.16); z-index: 10; max-height: 220px; overflow-y: auto;
}
.rep-item {
    padding: 10px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; gap: 8px; align-items: center; justify-content: space-between;
}
.rep-item:last-child { border-bottom: none; }
.rep-item:hover { background: #f8fafc; }
.rep-empty { padding: 12px; color: #94a3b8; font-size: 12px; text-align: center; }
.rep-seleccionado {
    margin-top: 8px; display: none; align-items: center; justify-content: space-between; gap: 8px; background: #fffbeb;
    border: 1px solid #fde68a; color: #92400e; border-radius: 8px; padding: 7px 10px; font-size: 12px; font-weight: 700;
}
.rep-clear { border: none; background: transparent; color: #b45309; font-size: 14px; cursor: pointer; line-height: 1; padding: 0; }

#modal-creds { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px); }
#modal-creds.open { display: flex; }
.creds-box { background: #fff; border-radius: 16px; padding: 24px 26px; max-width: 400px; width: 92%; box-shadow: 0 12px 40px rgba(0,0,0,.18); }
.creds-box h4 { font-size: 15px; font-weight: 700; color: var(--mo-slate); margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
.cred-row { background: #f8fafc; border: 1.5px solid var(--mo-border); border-radius: 10px; padding: 11px 14px; margin-bottom: 9px; }
.cred-row .cred-user { font-size: 11px; font-weight: 700; color: var(--mo-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; }
.cred-row .cred-pass { font-size: 15px; font-weight: 700; color: var(--mo-slate); font-family: monospace; letter-spacing: .05em; }
.cred-row .cred-label { font-size: 10px; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
.btn-cerrar-creds { margin-top: 12px; width: 100%; background: #f1f5f9; color: #475569; border: 1.5px solid var(--mo-border); border-radius: 9px; padding: 10px; font-size: 13px; font-weight: 600; cursor: pointer; }

#modal-nota-credito { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; align-items:center; justify-content:center; }

@media(max-width:800px){ .mo-kpis{ grid-template-columns: repeat(3,1fr); } }
@media(max-width:600px){
    .mis-ordenes-container { padding: 14px 10px; }
    .cards-grid { grid-template-columns: 1fr; gap: 10px; }
    .det-grid { grid-template-columns: 1fr; }
    .gestion-row { grid-template-columns: 24px 76px 1fr 24px; }
}
</style>
@endpush

@section('contenido')
@php
    $rows = $ordenes->map(function ($ord) {
        $esEmpresa = $ord instanceof \App\Models\Operations\OrdenEmpresa;
        $credenciales = $esEmpresa ? collect() : collect($ord->equipo?->credenciales ?? [])->map(fn($c) => [
            'usuario' => (string) ($c->usuario ?? ''),
            'contrasena' => (string) ($c->contrasena ?? ''),
            'es_patron' => (bool) ($c->es_patron ?? false),
        ])->values();

        $ultimoNc = $esEmpresa ? null : collect($ord->solicitudesNc ?? [])->sortByDesc('id')->first();
        $informe = $esEmpresa ? null : collect($ord->informes ?? [])->sortByDesc('id')->first();

        return [
            'id' => (int) $ord->id,
            'nro_orden' => (string) $ord->nro_orden,
            'estado_orden' => (string) ($esEmpresa ? ($ord->estado ?? '') : ($ord->estado_orden ?? '')),
            'estado_repuesto' => (string) ($esEmpresa ? 'No requerido' : ($ord->estado_repuesto ?: 'No requerido')),
            'fecha_de_ingreso' => (string) ($esEmpresa ? ($ord->fecha_ingreso ?? '') : ($ord->fecha_de_ingreso ?? '')),
            'fecha_entrega' => (string) ($esEmpresa ? '' : ($ord->fecha_entrega ?? '')),
            'motivo_ingreso' => (string) ($esEmpresa ? ('Empresa - ' . ($ord->subtipo ?? '')) : ($ord->motivo_ingreso ?? '')),
            'estado_garantia' => (string) ($esEmpresa ? '' : ($ord->estado_garantia ?? 'Pendiente')),
            'cliente' => $esEmpresa
                ? (string) ($ord->empresa->nombre ?? '')
                : trim(((string) ($ord->cliente->nombres ?? '')) . ' ' . ((string) ($ord->cliente->apellidos ?? ''))),
            'identificacion' => (string) ($esEmpresa ? ($ord->empresa->ruc ?? '') : ($ord->cliente->identificacion ?? '')),
            'numero_contacto' => (string) ($esEmpresa ? ($ord->empresa->telefono ?? '') : ($ord->cliente->numero_contacto ?? '')),
            'correo' => (string) ($esEmpresa ? ($ord->empresa->correo ?? '') : ($ord->cliente->correo ?? '')),
            'equipo_id' => (int) ($ord->equipo_id ?? 0),
            'tipo' => (string) ($ord->equipo->tipo ?? ''),
            'marca' => (string) ($ord->equipo->marca ?? ''),
            'modelo' => (string) ($ord->equipo->modelo ?? ''),
            'serie' => (string) ($ord->equipo->serie ?? ''),
            'falla' => (string) ($esEmpresa ? ($ord->descripcion ?? $ord->equipo->falla ?? '') : ($ord->equipo->falla ?? '')),
            'observacion' => (string) ($ord->equipo->observacion ?? ''),
            'tecnico' => (string) ($esEmpresa ? ($ord->tecnico->nombre_tecnico ?? '') : (session('nombre') ?? session('usuario') ?? '')),
            'sucursal' => (string) ($ord->sucursal->ciudad ?? $ord->sucursal->nombre ?? ''),
            'nro_factura' => (string) ($esEmpresa ? ($ord->nro_ticket ?? '') : ($ord->nro_factura ?? '')),
            'nro_sucursal_cliente' => (string) ($esEmpresa ? '' : ($ord->nro_sucursal_cliente ?? '')),
            'ingresado_por_nombre' => (string) ($esEmpresa
                ? ($ord->ingresadoPor->nombre_tecnico ?? $ord->ingresadoPor->usuario ?? '')
                : ($ord->usuarioIngreso->nombre_tecnico ?? $ord->usuarioIngreso->usuario ?? '')),
            'repuesto_inventario_id' => (int) ($esEmpresa ? 0 : ($ord->repuesto_inventario_id ?? 0)),
            'repuesto_codigo' => (string) ($esEmpresa ? '' : ($ord->repuestoInventario->codigo ?? '')),
            'repuesto_nombre' => (string) ($esEmpresa ? '' : ($ord->repuestoInventario->nombre ?? $ord->repuestoInventario->descripcion ?? '')),
            'tipo_orden' => $esEmpresa ? 'empresa' : 'personal',
            'credenciales' => $credenciales,
            'nc_id' => $ultimoNc ? (int) $ultimoNc->id : null,
            'nc_estado' => $ultimoNc ? (string) $ultimoNc->estado : null,
            'informe_id' => $informe ? (int) $informe->id : null,
        ];
    })->values();

    $cntTotal = $rows->count();
    $cntPendiente = $rows->filter(fn($o) => in_array($o['estado_orden'], ['Pendiente', 'Abierta'], true))->count();
    $cntProceso = $rows->filter(fn($o) => $o['estado_orden'] === 'En proceso')->count();
    $cntFinal = $rows->filter(fn($o) => $o['estado_orden'] === 'Finalizada')->count();
    $cntNc = $rows->filter(fn($o) => $o['estado_orden'] === 'Nota de Credito')->count();
    $cntEnt = $rows->filter(fn($o) => $o['estado_orden'] === 'Entregada')->count();

@endphp

<section class="modulo activo">
<div class="mis-ordenes-container">
    <div class="form-titulo">
        <h2><i class="bi bi-list-check me-2"></i>Mis Ordenes</h2>
        <p>Ordenes asignadas a tu usuario</p>
    </div>

    <div class="mo-kpis">
        <div class="mo-kpi-card mo-kpi-pendiente" onclick="filtrarOrdenes('Pendiente')" id="mo-kpi-pendiente">
            <div class="mo-kpi-num">{{ $cntPendiente }}</div><div class="mo-kpi-lbl">Pendiente</div>
        </div>
        <div class="mo-kpi-card mo-kpi-en-proceso" onclick="filtrarOrdenes('En proceso')" id="mo-kpi-enproceso">
            <div class="mo-kpi-num">{{ $cntProceso }}</div><div class="mo-kpi-lbl">En Proceso</div>
        </div>
        <div class="mo-kpi-card mo-kpi-finalizada" onclick="filtrarOrdenes('Finalizada')" id="mo-kpi-finalizada">
            <div class="mo-kpi-num">{{ $cntFinal }}</div><div class="mo-kpi-lbl">Finalizada</div>
        </div>
        <div class="mo-kpi-card mo-kpi-nota-cred" onclick="filtrarOrdenes('Nota de Credito')" id="mo-kpi-notacred">
            <div class="mo-kpi-num">{{ $cntNc }}</div><div class="mo-kpi-lbl">Nota de Credito</div>
        </div>
        <div class="mo-kpi-card mo-kpi-entregada" onclick="filtrarOrdenes('Entregada')" id="mo-kpi-entregada">
            <div class="mo-kpi-num">{{ $cntEnt }}</div><div class="mo-kpi-lbl">Entregada</div>
        </div>
        <div class="mo-kpi-card" onclick="filtrarOrdenes('')" id="mo-kpi-todos">
            <div class="mo-kpi-num">{{ $cntTotal }}</div><div class="mo-kpi-lbl">Total</div>
        </div>
    </div>

    <div class="panel-bloque" id="panel-ordenes">
        <div class="panel-header asignadas-header">
            <span class="panel-icon"><i class="bi bi-list-check"></i></span>
            <h3>Mis Ordenes</h3>
            <span class="panel-count" id="panel-count-visible">{{ $cntTotal }}</span>
        </div>
        <div class="cards-grid" id="cards-grid-principal">
            @forelse($rows as $o)
                @php
                    $e = $o['estado_orden'];
                    $estadoBg = match($e) {
                        'Pendiente', 'Abierta' => '#fef9c3',
                        'En proceso' => '#dbeafe',
                        'Finalizada' => '#dcfce7',
                        'Entregada' => '#f0fdf4',
                        'Nota de Credito' => '#fce7f3',
                        default => '#f1f5f9',
                    };
                    $estadoColor = match($e) {
                        'Pendiente', 'Abierta' => '#854d0e',
                        'En proceso' => '#1e40af',
                        'Finalizada' => '#166534',
                        'Entregada' => '#15803d',
                        'Nota de Credito' => '#9d174d',
                        default => '#475569',
                    };

                    $rep = $o['estado_repuesto'];
                    $repBg = match($rep) {
                        'Requerido' => '#fef3c7',
                        'Con stock', 'Con Stock' => '#dcfce7',
                        'Sin stock', 'Sin Stock' => '#fee2e2',
                        'En espera', 'En espera del repuesto' => '#fef3c7',
                        default => '#f1f5f9',
                    };
                    $repColor = match($rep) {
                        'Requerido', 'En espera', 'En espera del repuesto' => '#92400e',
                        'Con stock', 'Con Stock' => '#166534',
                        'Sin stock', 'Sin Stock' => '#991b1b',
                        default => '#475569',
                    };
                @endphp
                <div class="orden-card" data-estado="{{ $o['estado_orden'] }}" id="card-{{ $o['tipo_orden'] }}-{{ $o['id'] }}" data-orden='@json($o)'>
                    <div class="card-top" onclick="verDetalleOrden(this.parentElement)">
                        <span class="card-nro">{{ $o['nro_orden'] }}</span>
                        <span style="background:{{ $estadoBg }};color:{{ $estadoColor }};padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">{{ $o['estado_orden'] }}</span>
                    </div>
                    <div class="card-cliente" onclick="verDetalleOrden(this.parentElement)"><i class="bi bi-person"></i> {{ $o['cliente'] }}</div>
                    <div class="card-equipo" onclick="verDetalleOrden(this.parentElement)"><i class="bi bi-hdd"></i> {{ trim($o['tipo'].' '.$o['marca'].' '.$o['modelo']) }}</div>
                    <div class="card-serie"><i class="bi bi-tag"></i> Serie: {{ $o['serie'] ?: '-' }}</div>

                    @if(collect($o['credenciales'])->count() > 0)
                        <div class="card-creds" onclick="mostrarCredenciales(event, {{ $o['id'] }})">
                            <i class="bi bi-key me-1"></i>{{ collect($o['credenciales'])->count() }} credencial(es) <i class="bi bi-eye ms-1"></i>
                        </div>
                    @endif

                    <div class="card-meta-row">
                        <span class="tarj-meta"><i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::parse($o['fecha_de_ingreso'])->format('d/m/Y H:i') }}</span>
                        <span style="background:{{ $repBg }};color:{{ $repColor }};padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">{{ $o['estado_repuesto'] }}</span>
                        @if(!empty($o['ingresado_por_nombre']))
                            <span class="tarj-meta" title="Ingresado por"><i class="bi bi-person-check"></i> {{ $o['ingresado_por_nombre'] }}</span>
                        @endif
                    </div>

                    <div class="card-actions">
                        <button class="btn-accion btn-detalle-orden" onclick="verDetalleOrden(this.closest('[data-orden]'))">
                            <i class="bi bi-sliders"></i> {{ $o['tipo_orden'] === 'empresa' ? 'Detalle' : 'Gestionar' }}
                        </button>
                        @if($o['tipo_orden'] === 'personal')
                            <a class="btn-accion btn-imprimir-ot" href="{{ route('ordenes.imprimir', ['id' => $o['id']]) }}" target="_blank">
                                <i class="bi bi-printer"></i> Reimprimir OT
                            </a>
                        @else
                            <a class="btn-accion btn-imprimir-ot" href="{{ route('ordenes_empresa.imprimir', ['id' => $o['id']]) }}" target="_blank">
                                <i class="bi bi-printer"></i> Reimprimir OT
                            </a>
                        @endif
                        @if($o['estado_orden'] === 'Nota de Credito')
                            @if($o['nc_id'] && $o['nc_estado'] === 'Aprobada')
                                <a class="btn-accion btn-imprimir-nc" href="{{ route('notas_credito.imprimir', ['id' => $o['nc_id']]) }}" target="_blank">
                                    <i class="bi bi-receipt-cutoff"></i> Imprimir NC
                                </a>
                            @else
                                <button class="btn-accion btn-nc-pendiente" disabled>
                                    <i class="bi bi-hourglass-split"></i> NC Pendiente
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-msg">No tienes ordenes asignadas actualmente.</div>
            @endforelse
        </div>
        <div class="empty-msg" id="empty-filtro" style="display:none;">No hay ordenes con ese estado.</div>
    </div>
</div>
</section>

<div id="modal-detalle" class="modal-overlay" style="display:none;" onclick="cerrarDetalle(event)">
    <div class="modal-box">
        <button class="modal-close" onclick="cerrarModal()">&#10005;</button>
        <div id="modal-contenido"></div>
    </div>
</div>

<div id="modal-creds" onclick="if(event.target===this)cerrarCreds()">
    <div class="creds-box">
        <h4><i class="bi bi-key me-2" style="color:#2563eb;"></i>Credenciales del Equipo</h4>
        <div id="creds-lista"></div>
        <button class="btn-cerrar-creds" onclick="cerrarCreds()">Cerrar</button>
    </div>
</div>

<div id="modal-nota-credito" onclick="if(event.target===this)cerrarModalNC()">
    <div style="background:#fff;border-radius:14px;padding:28px 30px;max-width:480px;width:94%;box-shadow:0 10px 40px rgba(0,0,0,.25);max-height:92vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <i class="bi bi-receipt-cutoff" style="font-size:22px;color:#9d174d;"></i>
            <h4 style="margin:0;font-size:17px;font-weight:800;color:#7f1d1d;">Solicitud Nota de Credito</h4>
        </div>
        <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">Orden: <b id="nc-nro-orden-lbl"></b></p>
        <p style="margin:0 0 20px;font-size:11.5px;color:#f59e0b;font-weight:600;"><i class="bi bi-info-circle me-1"></i>La solicitud debe ser aprobada para poder imprimir.</p>
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Fecha</label>
            <input type="text" id="nc-fecha-display" readonly style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;background:#f8fafc;color:#64748b;">
        </div>
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Tecnico responsable</label>
            <input type="text" id="nc-tecnico-display" readonly style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;background:#f8fafc;color:#64748b;">
        </div>
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Asunto <span style="color:#ef4444;">*</span></label>
            <input type="text" id="nc-asunto" maxlength="200" autocomplete="off" placeholder="Ej: Devolucion por garantia aceptada"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;">
        </div>
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Detalles <span style="color:#ef4444;">*</span></label>
            <textarea id="nc-detalles" rows="4" maxlength="2000" placeholder="Describe los detalles de la nota de credito..."
                      style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;resize:vertical;"></textarea>
        </div>
        <div id="nc-error" style="display:none;background:#fef2f2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;padding:9px 13px;font-size:13px;font-weight:600;margin-bottom:14px;"></div>
        <div style="display:flex;gap:10px;">
            <button onclick="confirmarNC()" id="btn-confirmar-nc"
                    style="flex:1;background:linear-gradient(135deg,#991b1b,#7f1d1d);color:#fff;border:none;border-radius:9px;padding:11px;font-size:13.5px;font-weight:700;cursor:pointer;">
                <i class="bi bi-send me-2"></i>Enviar Solicitud
            </button>
            <button onclick="cerrarModalNC()" style="background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;border-radius:9px;padding:11px 18px;font-size:13.5px;font-weight:600;cursor:pointer;">
                Cancelar
            </button>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
const _moRows = @json($rows);
const _moCsrf = @json(csrf_token());
const _moUrlEstado = @json(route('mis_ordenes.estado'));
const _moUrlRepEstado = @json(route('mis_ordenes.repuesto_estado'));
const _moUrlGarEstado = @json(route('mis_ordenes.garantia_estado'));
const _moUrlRepAsignar = @json(route('mis_ordenes.repuesto_asignar'));
const _moUrlRepRevertir = @json(route('mis_ordenes.repuesto_revertir'));
const _moUrlInformeVer = @json(route('informes.ver'));
const _moUrlInformePrintBase = @json(url('/operaciones/informes'));
const _moUrlInformesIndex = @json(route('informes.index'));
const _moUrlBuscarRepuestos = @json(route('mis_ordenes.repuestos.buscar'));

let _ncOrdenId = 0;
const _repTimers = {};

function _h(v) {
    return String(v ?? '').replace(/[&<>"']/g, (s) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[s]));
}

function _badgeEstadoHtml(estado) {
    const map = {
        'Pendiente': ['#fef9c3', '#854d0e'],
        'Abierta': ['#fef9c3', '#854d0e'],
        'En proceso': ['#dbeafe', '#1e40af'],
        'Finalizada': ['#dcfce7', '#166534'],
        'Entregada': ['#f0fdf4', '#15803d'],
        'Nota de Credito': ['#fce7f3', '#9d174d'],
    };
    const pair = map[estado] || ['#f1f5f9', '#475569'];
    return `<span style="background:${pair[0]};color:${pair[1]};padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">${_h(estado)}</span>`;
}

function _badgeRepuestoHtml(estado) {
    const map = {
        'No requerido': ['#f1f5f9', '#475569'],
        'Requerido': ['#fef3c7', '#92400e'],
        'Con stock': ['#dcfce7', '#166534'],
        'Con Stock': ['#dcfce7', '#166534'],
        'Sin stock': ['#fee2e2', '#991b1b'],
        'Sin Stock': ['#fee2e2', '#991b1b'],
        'En espera': ['#fef3c7', '#92400e'],
        'En espera del repuesto': ['#fef3c7', '#92400e'],
    };
    const pair = map[estado] || ['#f1f5f9', '#475569'];
    return `<span style="background:${pair[0]};color:${pair[1]};padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">${_h(estado)}</span>`;
}

function filtrarOrdenes(estado) {
    const cards = document.querySelectorAll('.orden-card');
    let visibles = 0;
    cards.forEach((card) => {
        const est = card.getAttribute('data-estado') || '';
        const estNorm = est === 'Abierta' ? 'Pendiente' : est;
        const show = !estado || estNorm === estado;
        card.style.display = show ? '' : 'none';
        if (show) visibles++;
    });
    const cnt = document.getElementById('panel-count-visible');
    if (cnt) cnt.textContent = visibles;
    const empty = document.getElementById('empty-filtro');
    const grid = document.getElementById('cards-grid-principal');
    if (empty && grid) {
        empty.style.display = visibles === 0 ? '' : 'none';
        grid.style.display = visibles === 0 ? 'none' : '';
    }

    ['todos','pendiente','enproceso','finalizada','notacred','entregada'].forEach((k) => {
        const el = document.getElementById('mo-kpi-' + k);
        if (el) el.classList.remove('activo');
    });
    const mapa = { '': 'todos', 'Pendiente': 'pendiente', 'En proceso': 'enproceso', 'Finalizada': 'finalizada', 'Nota de Credito': 'notacred', 'Entregada': 'entregada' };
    const active = document.getElementById('mo-kpi-' + (mapa[estado] || 'todos'));
    if (active) active.classList.add('activo');
}

async function cambiarEstado(ordenId, nuevoEstado, nroOrden, tipoOrden = 'personal') {
    if (!nuevoEstado) return;
    if (tipoOrden !== 'empresa' && nuevoEstado === 'Nota de Credito') {
        abrirModalNC(ordenId);
        return;
    }
    if (!confirm(`Confirma la actualizacion de la orden ${nroOrden} a estado: ${nuevoEstado}?`)) return;

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('id', ordenId);
    fd.append('estado', nuevoEstado);
    fd.append('tipo_orden', tipoOrden);

    try {
        const r = await fetch(_moUrlEstado, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo actualizar el estado.');
            return;
        }
        location.reload();
    } catch {
        alert('Error de conexion.');
    }
}

async function cambiarEstadoGarantia(ordenId, nuevoEstado) {
    if (!nuevoEstado) return;
    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('estado_garantia', nuevoEstado);
    try {
        const r = await fetch(_moUrlGarEstado, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo actualizar garantia.');
            return;
        }
        location.reload();
    } catch {
        alert('Error de conexion.');
    }
}

async function cambiarEstadoRepuesto(ordenId, nuevoEstado) {
    if (!nuevoEstado) return;
    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('estado_repuesto', nuevoEstado);
    try {
        const r = await fetch(_moUrlRepEstado, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo actualizar repuesto.');
            return;
        }
        location.reload();
    } catch {
        alert('Error de conexion.');
    }
}

function limpiarRepuestoSeleccionadoMisOrdenes(ordenId) {
    const hid = document.getElementById('rep-inv-' + ordenId);
    const inp = document.getElementById('rep-inv-query-' + ordenId);
    const tag = document.getElementById('rep-inv-selected-' + ordenId);
    const text = document.getElementById('rep-inv-selected-text-' + ordenId);
    if (hid) hid.value = '';
    if (inp) inp.value = '';
    if (tag) tag.style.display = 'none';
    if (text) text.textContent = '';
}

function seleccionarRepuestoMisOrdenes(ordenId, repuestoId, codigo, nombre) {
    const hid = document.getElementById('rep-inv-' + ordenId);
    const inp = document.getElementById('rep-inv-query-' + ordenId);
    const box = document.getElementById('rep-inv-resultados-' + ordenId);
    const tag = document.getElementById('rep-inv-selected-' + ordenId);
    const text = document.getElementById('rep-inv-selected-text-' + ordenId);

    const cod = String(codigo || '').trim();
    const nom = String(nombre || '').trim();
    if (hid) hid.value = Number(repuestoId || 0) || '';
    if (inp) inp.value = cod || nom;
    if (text) text.textContent = (cod || '-') + ' - ' + (nom || '-');
    if (tag) tag.style.display = 'flex';
    if (box) box.style.display = 'none';
}

function renderRepuestosMisOrdenes(ordenId, repuestos) {
    const box = document.getElementById('rep-inv-resultados-' + ordenId);
    if (!box) return;

    if (!Array.isArray(repuestos) || repuestos.length === 0) {
        box.innerHTML = '<div class="rep-empty">No se encontraron repuestos con stock.</div>';
        box.style.display = 'block';
        return;
    }

    box.innerHTML = repuestos.map((r) => `
        <div class="rep-item" onclick="seleccionarRepuestoMisOrdenes(
            ${Number(ordenId)},
            ${Number(r.id || 0)},
            '${String(r.codigo || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'")}',
            '${String(r.nombre || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'
        )">
            <span><code style="font-size:11.5px;color:#b45309;font-weight:800;">${_h(r.codigo || '-')}</code> ${_h(r.nombre || '-')}</span>
            <span style="background:#dcfce7;color:#166534;font-size:10px;padding:2px 7px;border-radius:999px;font-weight:700;">Stock ${Number(r.stock || 0)}</span>
        </div>
    `).join('');
    box.style.display = 'block';
}

function buscarRepuestoMisOrdenes(ordenId, q) {
    const texto = String(q || '').trim();
    const box = document.getElementById('rep-inv-resultados-' + ordenId);
    if (!box) return;

    clearTimeout(_repTimers[ordenId]);
    _repTimers[ordenId] = setTimeout(async () => {
        if (texto.length < 2) {
            box.style.display = 'none';
            return;
        }

        try {
            const url = _moUrlBuscarRepuestos + '?stock_only=1&q=' + encodeURIComponent(texto);
            const r = await fetch(url, { cache: 'no-store' });
            const d = await r.json();
            if (!d.ok) {
                box.innerHTML = '<div class="rep-empty">No se pudo consultar el catalogo.</div>';
                box.style.display = 'block';
                return;
            }
            renderRepuestosMisOrdenes(ordenId, d.repuestos || []);
        } catch {
            box.innerHTML = '<div class="rep-empty">Error de conexion al buscar repuestos.</div>';
            box.style.display = 'block';
        }
    }, 260);
}

function onInputBuscarRepuestoMisOrdenes(ordenId, q) {
    const hid = document.getElementById('rep-inv-' + ordenId);
    const tag = document.getElementById('rep-inv-selected-' + ordenId);
    const text = document.getElementById('rep-inv-selected-text-' + ordenId);
    if (hid) hid.value = '';
    if (tag) tag.style.display = 'none';
    if (text) text.textContent = '';
    buscarRepuestoMisOrdenes(ordenId, q);
}

async function asignarRepuesto(ordenId) {
    const sel = document.getElementById('rep-inv-' + ordenId);
    if (!sel || !sel.value) {
        alert('Seleccione un repuesto.');
        return;
    }
    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('repuesto_inventario_id', sel.value);
    try {
        const r = await fetch(_moUrlRepAsignar, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo asignar repuesto.');
            return;
        }
        location.reload();
    } catch {
        alert('Error de conexion.');
    }
}

async function revertirRepuesto(ordenId) {
    if (!confirm('Confirma revertir el repuesto y devolver stock a inventario?')) return;
    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    try {
        const r = await fetch(_moUrlRepRevertir, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            alert(d.error || 'No se pudo revertir repuesto.');
            return;
        }
        location.reload();
    } catch {
        alert('Error de conexion.');
    }
}

function abrirModalNC(ordenId) {
    const row = _moRows.find((x) => Number(x.id) === Number(ordenId));
    if (!row) return;
    if ((row.estado_garantia || 'Pendiente') !== 'Aceptada') {
        alert('La garantia debe estar en estado Aceptada para solicitar una Nota de Credito.');
        return;
    }
    if (!row.informe_id) {
        alert('Esta orden no tiene informe tecnico registrado. Debe crear el informe antes de solicitar NC.');
        return;
    }
    _ncOrdenId = Number(ordenId);
    document.getElementById('nc-nro-orden-lbl').textContent = row.nro_orden || '-';
    document.getElementById('nc-fecha-display').value = new Date().toLocaleDateString('es-EC');
    document.getElementById('nc-tecnico-display').value = row.tecnico || '';
    document.getElementById('nc-asunto').value = '';
    document.getElementById('nc-detalles').value = '';
    document.getElementById('nc-error').style.display = 'none';
    document.getElementById('modal-nota-credito').style.display = 'flex';
    document.getElementById('nc-asunto').focus();
}

function cerrarModalNC() {
    document.getElementById('modal-nota-credito').style.display = 'none';
    _ncOrdenId = 0;
}

async function confirmarNC() {
    const asunto = document.getElementById('nc-asunto').value.trim();
    const detalles = document.getElementById('nc-detalles').value.trim();
    const err = document.getElementById('nc-error');
    err.style.display = 'none';
    if (!asunto) { err.textContent = 'El asunto es obligatorio.'; err.style.display = 'block'; return; }
    if (!detalles) { err.textContent = 'Los detalles son obligatorios.'; err.style.display = 'block'; return; }

    const btn = document.getElementById('btn-confirmar-nc');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Enviando...';

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('id', _ncOrdenId);
    fd.append('estado', 'Nota de Credito');
    fd.append('nc_asunto', asunto);
    fd.append('nc_detalles', detalles);

    try {
        const r = await fetch(_moUrlEstado, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            err.textContent = d.error || 'No se pudo procesar.';
            err.style.display = 'block';
            return;
        }
        cerrarModalNC();
        location.reload();
    } catch {
        err.textContent = 'Error de conexion.';
        err.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Enviar Solicitud';
    }
}

function abrirInformeDeOrden(ordenId) {
    try { localStorage.setItem('sgn_informe_orden_id', String(ordenId)); } catch {}
    window.location.href = _moUrlInformesIndex + '?orden_id=' + encodeURIComponent(String(ordenId));
}

async function verPdfInforme(ordenId) {
    try {
        const r = await fetch(_moUrlInformeVer + '?orden_id=' + encodeURIComponent(String(ordenId)), { cache: 'no-store' });
        const d = await r.json();
        if (!d.ok || !d.informe || !d.informe.id) {
            alert('Esta orden aun no tiene informe.');
            return;
        }
        window.open(_moUrlInformePrintBase + '/' + d.informe.id + '/imprimir', '_blank');
    } catch {
        alert('No se pudo consultar el informe.');
    }
}

function verDetalleOrden(cardEl) {
    if (!cardEl) return;
    let o = null;
    try { o = JSON.parse(cardEl.getAttribute('data-orden') || '{}'); } catch { return; }
    if (!o || !o.id) return;

    const esGarantia = (o.motivo_ingreso || '') === 'Validacion de Garantia';
    const esEmpresa = (o.tipo_orden || '') === 'empresa';
    const repSeleccionado = Number(o.repuesto_inventario_id || 0) > 0;
    const repTextoSeleccionado = [o.repuesto_codigo || '', o.repuesto_nombre || '']
        .filter(Boolean)
        .join(' - ');

    const html = `
        <div class="det-wrap">
            <div class="det-titulo">
                <span class="det-nro">${_h(o.nro_orden)}</span>
                <div class="det-badges">
                    ${_badgeEstadoHtml(o.estado_orden || '-')}
                    ${_badgeRepuestoHtml(o.estado_repuesto || '-')}
                </div>
            </div>

            <div class="det-seccion">
                <h4><i class="bi bi-card-list"></i>Datos generales</h4>
                <div class="det-grid">
                    <div class="det-campo"><label>Cliente</label><span>${_h(o.cliente || '-')}</span></div>
                    <div class="det-campo"><label>Identificacion</label><span>${_h(o.identificacion || '-')}</span></div>
                    <div class="det-campo"><label>Telefono</label><span>${_h(o.numero_contacto || '-')}</span></div>
                    <div class="det-campo"><label>Correo</label><span>${_h(o.correo || '-')}</span></div>
                    <div class="det-campo det-full"><label>Equipo</label><span>${_h((o.tipo || '') + ' ' + (o.marca || '') + ' ' + (o.modelo || ''))}</span></div>
                    <div class="det-campo"><label>Serie</label><span>${_h(o.serie || '-')}</span></div>
                    <div class="det-campo"><label>Sucursal</label><span>${_h(o.sucursal || '-')}</span></div>
                    <div class="det-campo"><label>Motivo</label><span>${_h(o.motivo_ingreso || '-')}</span></div>
                    ${esEmpresa ? `<div class="det-campo"><label>Nro. Ticket</label><span>${_h(o.nro_factura || '-')}</span></div>` : ''}
                    <div class="det-campo det-full"><label>Falla</label><span>${_h(o.falla || '-')}</span></div>
                    <div class="det-campo det-full"><label>Observacion</label><span>${_h(o.observacion || '-')}</span></div>
                </div>
            </div>

            <hr class="det-sep">

            ${esEmpresa ? `
            <div class="gestion-panel">
                <div class="gestion-panel-title"><i class="bi bi-sliders2"></i>Gestion de orden empresa</div>
                <div class="gestion-row">
                    <span class="gestion-icon"><i class="bi bi-activity"></i></span>
                    <span class="gestion-label">Estado</span>
                    <select class="gestion-select" onchange="cambiarEstado(${Number(o.id)}, this.value, '${_h(String(o.nro_orden || '')).replace(/'/g, "\\'")}', 'empresa')">
                        <option value="">Cambiar a...</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Entregada">Entregada</option>
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>
                <div class="gestion-actions-rep" style="margin-top:12px;">
                    <button type="button" class="btn-mini-rep" onclick="abrirInformeDeOrden(${-1 * Number(o.id)})"><i class="bi bi-pencil-square me-1"></i>Gestionar informe</button>
                    <button type="button" class="btn-mini-rep" onclick="verPdfInforme(${-1 * Number(o.id)})"><i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF informe</button>
                </div>
            </div>
            ` : `
            <div class="gestion-panel">
                <div class="gestion-panel-title"><i class="bi bi-sliders2"></i>Gestion de la orden</div>

                <div class="gestion-row">
                    <span class="gestion-icon"><i class="bi bi-activity"></i></span>
                    <span class="gestion-label">Estado</span>
                    <select class="gestion-select" onchange="cambiarEstado(${Number(o.id)}, this.value, '${_h(String(o.nro_orden || '')).replace(/'/g, "\\'")}', 'personal')">
                        <option value="">Cambiar a...</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Entregada">Entregada</option>
                        <option value="Nota de Credito">Nota de Credito</option>
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>

                ${esGarantia ? `
                <div class="gestion-row garantia-row">
                    <span class="gestion-icon"><i class="bi bi-shield-check"></i></span>
                    <span class="gestion-label">Garantia</span>
                    <select class="gestion-select" onchange="cambiarEstadoGarantia(${Number(o.id)}, this.value)">
                        <option value="">Cambiar...</option>
                        <option value="Pendiente" ${(o.estado_garantia || 'Pendiente') === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                        <option value="Aceptada" ${(o.estado_garantia || '') === 'Aceptada' ? 'selected' : ''}>Aceptada</option>
                        <option value="Rechazada" ${(o.estado_garantia || '') === 'Rechazada' ? 'selected' : ''}>Rechazada</option>
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>` : ''}

                <div class="gestion-row repuesto-row">
                    <span class="gestion-icon"><i class="bi bi-tools"></i></span>
                    <span class="gestion-label">Repuesto</span>
                    <select class="gestion-select" onchange="cambiarEstadoRepuesto(${Number(o.id)}, this.value)">
                        <option value="No requerido" ${(o.estado_repuesto || '') === 'No requerido' ? 'selected' : ''}>No requerido</option>
                        <option value="Requerido" ${(o.estado_repuesto || '') === 'Requerido' ? 'selected' : ''}>Requerido</option>
                        <option value="Con stock" ${(o.estado_repuesto || '') === 'Con stock' ? 'selected' : ''}>Con stock</option>
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>

                <div class="rep-picker">
                    <input type="hidden" id="rep-inv-${Number(o.id)}" value="${repSeleccionado ? Number(o.repuesto_inventario_id) : ''}">
                    <input type="text" class="rep-input" id="rep-inv-query-${Number(o.id)}" placeholder="Buscar repuesto por codigo o nombre..." autocomplete="off"
                           value="${repSeleccionado ? _h(o.repuesto_codigo || o.repuesto_nombre || '') : ''}"
                           oninput="onInputBuscarRepuestoMisOrdenes(${Number(o.id)}, this.value)"
                           onfocus="buscarRepuestoMisOrdenes(${Number(o.id)}, this.value)">
                    <div class="rep-resultados" id="rep-inv-resultados-${Number(o.id)}"></div>
                    <div class="rep-seleccionado" id="rep-inv-selected-${Number(o.id)}" style="${repSeleccionado ? 'display:flex;' : 'display:none;'}">
                        <span id="rep-inv-selected-text-${Number(o.id)}">${_h(repTextoSeleccionado || 'Repuesto seleccionado')}</span>
                        <button type="button" class="rep-clear" onclick="limpiarRepuestoSeleccionadoMisOrdenes(${Number(o.id)})" title="Quitar seleccion">&times;</button>
                    </div>
                    <div class="gestion-actions-rep">
                        <button type="button" class="btn-mini-rep" onclick="asignarRepuesto(${Number(o.id)})">Asignar repuesto</button>
                        <button type="button" class="btn-mini-rep danger" onclick="revertirRepuesto(${Number(o.id)})">Revertir repuesto</button>
                    </div>
                </div>

                <div class="gestion-actions-rep" style="margin-top:12px;">
                    <button type="button" class="btn-mini-rep" onclick="abrirInformeDeOrden(${Number(o.id)})"><i class="bi bi-pencil-square me-1"></i>Gestionar informe</button>
                    <button type="button" class="btn-mini-rep" onclick="verPdfInforme(${Number(o.id)})"><i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF informe</button>
                </div>
            </div>
            `}
        </div>
    `;

    const modal = document.getElementById('modal-detalle');
    const cont = document.getElementById('modal-contenido');
    if (cont) cont.innerHTML = html;
    if (modal) modal.style.display = 'flex';
}

function cerrarModal() {
    const m = document.getElementById('modal-detalle');
    if (m) m.style.display = 'none';
}
function cerrarDetalle(e) {
    if (e.target && e.target.id === 'modal-detalle') cerrarModal();
}

function mostrarCredenciales(e, ordenId) {
    e.stopPropagation();
    const row = _moRows.find((x) => Number(x.id) === Number(ordenId));
    const creds = row?.credenciales || [];
    const lista = document.getElementById('creds-lista');
    if (!lista) return;
    if (!creds.length) {
        lista.innerHTML = '<p style="color:#94a3b8;font-size:13px;text-align:center;padding:10px 0;">Sin credenciales registradas.</p>';
    } else {
        lista.innerHTML = creds.map((cr, i) => {
            if (cr.es_patron) {
                const img = _h(cr.contrasena || '');
                return `
                    <div class="cred-row">
                        ${cr.usuario ? `<div class="cred-label">Usuario</div><div class="cred-user">${_h(cr.usuario)}</div><div style="margin-top:8px"></div>` : ''}
                        <div class="cred-label">Tipo</div>
                        <div class="cred-user" style="color:#7c3aed;"><i class="bi bi-grid-3x3-gap me-1"></i>Patron de dibujo</div>
                        <button type="button" class="btn-mini-rep" onclick="verPatron(${i})"><i class="bi bi-eye me-1"></i>Ver patron</button>
                        <div class="patron-img-wrap" id="patron-img-${i}" style="display:none;margin-top:10px;">
                            <img src="${img}" style="width:160px;height:160px;border-radius:10px;border:2px solid #e2e8f0;">
                        </div>
                    </div>`;
            }
            return `
                <div class="cred-row">
                    ${cr.usuario ? `<div class="cred-label">Usuario</div><div class="cred-user">${_h(cr.usuario)}</div><div style="margin-top:8px"></div>` : ''}
                    <div class="cred-label">Contrasena / PIN</div>
                    <div class="cred-pass">${_h(cr.contrasena || '')}</div>
                </div>`;
        }).join('');
    }
    const modal = document.getElementById('modal-creds');
    if (modal) modal.classList.add('open');
}

function verPatron(idx) {
    const el = document.getElementById('patron-img-' + idx);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function cerrarCreds() {
    const modal = document.getElementById('modal-creds');
    if (modal) modal.classList.remove('open');
}

document.addEventListener('DOMContentLoaded', () => filtrarOrdenes('Pendiente'));
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarCreds();
        cerrarModal();
        cerrarModalNC();
    }
});
document.addEventListener('click', (e) => {
    if (!(e.target instanceof Element)) return;
    if (e.target.closest('.rep-picker')) return;
    document.querySelectorAll('.rep-resultados').forEach((el) => {
        el.style.display = 'none';
    });
});
</script>
@endpush
