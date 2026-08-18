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
.btn-mini-rep.violet { border-color: #ddd6fe; background: #f5f3ff; color: #6d28d9; }
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
    $usuario = auth()->user();
    $rol = $usuario && $usuario->rol ? mb_strtolower(trim((string) $usuario->rol->rol)) : '';
    $grupo = $usuario && $usuario->grupo ? mb_strtolower(trim((string) $usuario->grupo->nombre)) : '';
    $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
    $rolesAdmitidos = ['admin', 'administrador', 'admin master', 'administrador master'];
    $tienePermisoEditar = session('es_superadmin') === true 
        || !empty(session('permisos', [])['ordenes_editar']['editar']) 
        || !empty(session('permisos', [])['ordenes_editar']['ver']);
    $esAdminOAdminMaster = in_array($rol, $rolesAdmitidos, true)
        || in_array($grupo, $rolesAdmitidos, true)
        || in_array($sessionGrupo, $rolesAdmitidos, true)
        || $tienePermisoEditar;

    $sucursalesClienteMapa = \App\Models\Directory\SucursalCliente::query()
        ->orderBy('numero')
        ->get(['numero', 'nombre'])
        ->keyBy(fn($s) => (string) (int) $s->numero);

    $rows = $ordenes->map(function ($ord) use ($sucursalesClienteMapa) {
        $esEmpresa = $ord instanceof \App\Models\Operations\OrdenEmpresa;
        $nroSucursalCliente = (int) ($ord->nro_sucursal_cliente ?? 0);
        $sucursalCliente = $nroSucursalCliente > 0 ? $sucursalesClienteMapa->get((string) $nroSucursalCliente) : null;
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
            'estado_repuesto' => (string) ($ord->estado_repuesto ?: 'No requerido'),
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
            'producto_inventario_codigo' => (string) ($ord->equipo->producto_inventario_codigo ?? ''),
            'falla' => (string) ($esEmpresa ? ($ord->descripcion ?? $ord->equipo->falla ?? '') : ($ord->equipo->falla ?? '')),
            'observacion' => (string) ($ord->equipo->observacion ?? ''),
            'tecnico' => (string) ($esEmpresa ? ($ord->tecnico->nombre_tecnico ?? '') : (session('nombre') ?? session('usuario') ?? '')),
            'sucursal' => (string) ($ord->sucursal->ciudad ?? $ord->sucursal->nombre ?? ''),
            'nro_factura' => (string) ($esEmpresa ? ($ord->nro_ticket ?? '') : ($ord->nro_factura ?? '')),
            'nro_sucursal_cliente' => (string) ($ord->nro_sucursal_cliente ?? ''),
            'sucursal_cliente_nombre' => (string) ($sucursalCliente->nombre ?? ''),
            'ingresado_por_nombre' => (string) ($esEmpresa
                ? ($ord->ingresadoPor->nombre_tecnico ?? $ord->ingresadoPor->usuario ?? '')
                : ($ord->usuarioIngreso->nombre_tecnico ?? $ord->usuarioIngreso->usuario ?? '')),
            'repuesto_inventario_id' => (int) ($ord->repuesto_inventario_id ?? 0),
            'repuesto_codigo' => (string) ($ord->repuestoInventario->codigo ?? ''),
            'repuesto_nombre' => (string) ($ord->repuestoInventario->nombre ?? $ord->repuestoInventario->descripcion ?? ''),
            'memo_entrega' => (string) ($ord->memo_entrega ?? ''),
            'foto_evidencia_entrega' => (string) ($ord->foto_evidencia_entrega ?? ''),
            'tipo_orden' => $esEmpresa ? 'empresa' : 'personal',
            'empresa_id' => $esEmpresa ? (int) $ord->empresa_id : null,
            'subtipo' => $esEmpresa ? (string) $ord->subtipo : null,
            'productos_inventario_st' => ($esEmpresa && (int)$ord->empresa_id === 1 && $ord->subtipo === 'Stock')
                ? \App\Models\Inventory\ProductoInventarioFisicoSt::where('orden_empresa_id', $ord->id)->get()->map(function($p) {
                    return [
                        'id' => (int) $p->id,
                        'serie' => (string) $p->serie,
                        'nombre' => (string) $p->nombre,
                        'estado' => (string) $p->estado,
                        'detalle_outlet' => (string) ($p->detalle_outlet ?? ''),
                    ];
                })->values()
                : [],
            'credenciales' => $credenciales,
            'nc_id' => $ultimoNc ? (int) $ultimoNc->id : null,
            'nc_estado' => $ultimoNc ? (string) $ultimoNc->estado : null,
            'transferencia_plataforma' => $esEmpresa ? null : $ord->transferencia_plataforma,
            'transferencia_numero' => $esEmpresa ? null : $ord->transferencia_numero,
            'informe_id' => $informe ? (int) $informe->id : null,
            'repuestos_asignados' => collect($ord->ordenRepuestos ?? [])->map(fn($or) => [
                'id' => (int) $or->id,
                'repuesto_id' => (int) $or->repuesto_id,
                'codigo' => (string) ($or->repuesto->codigo ?? ''),
                'nombre' => (string) ($or->repuesto->nombre ?? $or->repuesto->descripcion ?? ''),
                'cantidad' => (int) $or->cantidad,
                'costo_unitario' => (float) ($or->repuesto->costo ?? 0),
                'costo_total' => (float) (($or->repuesto->costo ?? 0) * $or->cantidad),
            ])->values(),
            'total_costo_repuestos' => (float) collect($ord->ordenRepuestos ?? [])->sum(fn($or) => ($or->repuesto->costo ?? 0) * $or->cantidad),
            'llamadas' => collect($ord->llamadas ?? [])->map(fn($ll) => [
                'id' => (int) $ll->id,
                'fecha_hora' => $ll->fecha_hora ? $ll->fecha_hora->format('d/m/Y H:i') : '',
                'usuario_nombre' => $ll->usuario?->nombre_tecnico ?? $ll->usuario?->usuario ?? 'Técnico',
                'observacion' => (string) ($ll->observacion ?? 'Llamada registrada sin observaciones.'),
            ])->values(),
            'horas_trabajadas' => $esEmpresa ? $ord->horas_trabajadas : null,
            'valor_hora' => $esEmpresa ? $ord->valor_hora : null,
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
                        <span class="card-estado-badge" style="background:{{ $estadoBg }};color:{{ $estadoColor }};padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">{{ $o['estado_orden'] }}</span>
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
                        <span class="card-repuesto-badge" style="background:{{ $repBg }};color:{{ $repColor }};padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">{{ $o['estado_repuesto'] }}</span>
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
        <div id="mo-pager" style="margin: 0 16px 16px;"></div>
    </div>
</div>
</section>

<div id="modal-detalle" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <button class="modal-close" onclick="cerrarModal()">&#10005;</button>
        <div id="modal-contenido"></div>
    </div>
</div>

<div id="modal-creds">
    <div class="creds-box" style="position:relative;">
        <button type="button" class="modal-close" onclick="cerrarCreds()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:18px;color:#64748b;cursor:pointer;" title="Cerrar">&#10005;</button>
        <h4><i class="bi bi-key me-2" style="color:#2563eb;"></i>Credenciales del Equipo</h4>
        <div id="creds-lista"></div>
        <button class="btn-cerrar-creds" onclick="cerrarCreds()">Cerrar</button>
    </div>
</div>

<div id="modal-nota-credito">
    <div style="background:#fff;border-radius:14px;padding:28px 30px;max-width:480px;width:94%;box-shadow:0 10px 40px rgba(0,0,0,.25);max-height:92vh;overflow-y:auto;position:relative;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <i class="bi bi-receipt-cutoff" style="font-size:22px;color:#9d174d;"></i>
                <h4 style="margin:0;font-size:17px;font-weight:800;color:#7f1d1d;">Solicitud Nota de Credito</h4>
            </div>
            <button type="button" onclick="cerrarModalNC()" style="background:none;border:none;font-size:18px;color:#64748b;cursor:pointer;padding:4px 8px;border-radius:6px;margin-left:auto;" title="Cerrar">&#10005;</button>
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

<div id="modal-solicitud-repuesto" class="modal-overlay" style="display:none;">
    <div style="background:#fff;border-radius:14px;padding:28px 30px;max-width:480px;width:94%;box-shadow:0 10px 40px rgba(0,0,0,.25);max-height:92vh;overflow-y:auto;box-sizing:border-box;position:relative;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <i class="bi bi-tools" style="font-size:22px;color:#2563eb;"></i>
                <h4 style="margin:0;font-size:17px;font-weight:800;color:#1e3a8a;">Solicitud de Repuesto</h4>
            </div>
            <button type="button" onclick="cerrarModalSR()" style="background:none;border:none;font-size:18px;color:#64748b;cursor:pointer;padding:4px 8px;border-radius:6px;margin-left:auto;" title="Cerrar">&#10005;</button>
        </div>
        <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">Orden: <b id="sr-nro-orden-lbl"></b></p>
        <p style="margin:0 0 20px;font-size:11.5px;color:#64748b;line-height:1.4;"><i class="bi bi-info-circle me-1"></i>Ingresa los detalles del repuesto requerido. Se creará un ticket en bodega y el estado de la orden pasará a <b>Requerido</b>.</p>
        
        <input type="hidden" id="sr-orden-id" value="">
        <input type="hidden" id="sr-orden-tipo" value="personal">
        
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Nombre del Repuesto <span style="color:#ef4444;">*</span></label>
            <input type="text" id="sr-repuesto-nombre" maxlength="255" placeholder="Ej: Pantalla A123"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;box-sizing:border-box;">
        </div>
        
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;margin-bottom:14px;">
            <div>
                <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Cantidad <span style="color:#ef4444;">*</span></label>
                <input type="number" id="sr-cantidad" min="1" value="1"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Nro de Parte</label>
                <input type="text" id="sr-nro-parte" maxlength="100" placeholder="Ej: PN-987654"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;box-sizing:border-box;">
            </div>
        </div>
        
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Link de Compra</label>
            <input type="url" id="sr-link-compra" placeholder="https://..."
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;box-sizing:border-box;">
        </div>
        
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Descripción / Notas Adicionales <span style="color:#ef4444;">*</span></label>
            <textarea id="sr-descripcion" rows="3" placeholder="Detalles técnicos adicionales..."
                      style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 11px;font-size:13px;resize:vertical;box-sizing:border-box;"></textarea>
        </div>
        
        <div id="sr-error" style="display:none;background:#fef2f2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;padding:9px 13px;font-size:13px;font-weight:600;margin-bottom:14px;"></div>
        
        <div style="display:flex;gap:10px;">
            <button onclick="confirmarSR()" id="btn-confirmar-sr"
                    style="flex:1;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:9px;padding:11px;font-size:13.5px;font-weight:700;cursor:pointer;">
                <i class="bi bi-send me-2"></i>Enviar Solicitud
            </button>
            <button onclick="cerrarModalSR()" style="background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;border-radius:9px;padding:11px 18px;font-size:13.5px;font-weight:600;cursor:pointer;">
                Cancelar
            </button>
        </div>
    </div>
</div>

<div id="modal-alert" class="modal-overlay" style="display:none;">
    <div style="background:#fff;border-radius:18px;padding:32px 30px;max-width:440px;width:92%;box-shadow:0 24px 60px rgba(0,0,0,.22);text-align:center;animation:modalIn .2s ease;position:relative;">
        <div id="alert-icon-container" style="border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;border: 1.5px solid #fca5a5;background:#fef2f2;">
            <!-- Icono dinámico -->
        </div>
        <h4 style="margin:0 0 10px;font-size:17px;font-weight:800;color:#0f172a;" id="alert-title">Acción Requerida</h4>
        <p style="margin:0 0 24px;font-size:13.5px;color:#475569;line-height:1.6;" id="alert-message"></p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button id="alert-btn-confirm" onclick="cerrarAlerta(true)" style="flex:1;background:#ef4444;color:#fff;border:none;border-radius:9px;padding:11px;font-size:13.5px;font-weight:700;cursor:pointer;transition:background .15s;outline:none;">
                Entendido
            </button>
            <button id="alert-btn-cancel" onclick="cerrarAlerta(false)" style="background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;border-radius:9px;padding:11px 18px;font-size:13.5px;font-weight:600;cursor:pointer;display:none;outline:none;">
                Cancelar
            </button>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
const PUEDE_REASIGNAR = @json($esAdminOAdminMaster);
const _moRows = @json($rows);
const _moCsrf = @json(csrf_token());
const _moUrlEstado = @json(route('mis_ordenes.estado'));
const _moUrlRepEstado = @json(route('mis_ordenes.repuesto_estado'));
const _moUrlGarEstado = @json(route('mis_ordenes.garantia_estado'));
const _moUrlRepAsignar = @json(route('mis_ordenes.repuesto_asignar'));
const _moUrlRepRevertir = @json(route('mis_ordenes.repuesto_revertir'));
const _moUrlInformeVer = @json(route('informes.ver'));
const _moUrlInformePrintBase = @json(url('/operaciones/informes'));
const _moUrlInformesCrear = @json(route('informes.crear'));
const _moUrlBuscarRepuestos = @json(route('mis_ordenes.repuestos.buscar'));
const _moUrlSolicitarRepuesto = @json(route('solicitudes_repuestos.solicitar'));
const _moUrlReasignar = @json(route('mis_ordenes.reasignar'));
const _moTecnicos = @json($tecnicos);
const _moUrlRegistrarLlamada = @json(route('ordenes.llamadas.registrar'));
const _moUrlEnviarEmail = @json(route('mis_ordenes.enviar_email'));
const _moUrlInvFisicoGuardar = @json(route('inventario_fisico.guardar'));
const _moUrlInvFisicoObtener = @json(url('/operaciones/ordenes-empresa/inventario-fisico'));

let _ncOrdenId = 0;
const _repTimers = {};
let _resolveAlertPromise = null;

function mostrarAlertaEstetica(mensaje, tipo = 'error', titulo = '', esConfirmacion = false) {
    return new Promise((resolve) => {
        const box = document.getElementById('modal-alert');
        const titleEl = document.getElementById('alert-title');
        const msgEl = document.getElementById('alert-message');
        const iconContainer = document.getElementById('alert-icon-container');
        const btnConfirm = document.getElementById('alert-btn-confirm');
        const btnCancel = document.getElementById('alert-btn-cancel');

        if (!titulo) {
            if (tipo === 'success') titulo = 'Éxito';
            else if (tipo === 'warning') titulo = 'Advertencia';
            else if (tipo === 'confirm') titulo = 'Confirmación';
            else titulo = 'Acción Requerida';
        }

        titleEl.textContent = titulo;
        msgEl.innerHTML = mensaje;

        // Reset default styles
        iconContainer.style.background = '';
        iconContainer.style.borderColor = '';
        btnConfirm.style.background = '';
        btnConfirm.style.color = '#fff';
        btnCancel.style.display = 'none';

        if (tipo === 'success') {
            iconContainer.innerHTML = '<i class="bi bi-check-circle" style="font-size:24px;color:#16a34a;"></i>';
            iconContainer.style.background = '#f0fdf4';
            iconContainer.style.borderColor = '#86efac';
            btnConfirm.style.background = '#10b981';
            btnConfirm.textContent = 'Aceptar';
        } else if (tipo === 'warning') {
            iconContainer.innerHTML = '<i class="bi bi-exclamation-triangle" style="font-size:24px;color:#d97706;"></i>';
            iconContainer.style.background = '#fffbeb';
            iconContainer.style.borderColor = '#fde68a';
            btnConfirm.style.background = '#f59e0b';
            btnConfirm.textContent = 'Entendido';
        } else if (tipo === 'confirm') {
            iconContainer.innerHTML = '<i class="bi bi-question-circle" style="font-size:24px;color:#2563eb;"></i>';
            iconContainer.style.background = '#eff6ff';
            iconContainer.style.borderColor = '#93c5fd';
            btnConfirm.style.background = '#2563eb';
            btnConfirm.textContent = 'Confirmar';
            btnCancel.style.display = 'block';
            btnCancel.textContent = 'Cancelar';
        } else {
            // error
            iconContainer.innerHTML = '<i class="bi bi-exclamation-circle" style="font-size:24px;color:#dc2626;"></i>';
            iconContainer.style.background = '#fef2f2';
            iconContainer.style.borderColor = '#fca5a5';
            btnConfirm.style.background = '#ef4444';
            btnConfirm.textContent = 'Cerrar';
        }

        if (esConfirmacion) {
            btnCancel.style.display = 'block';
        }

        box.style.display = 'flex';
        _resolveAlertPromise = resolve;
    });
}

function cerrarAlerta(confirmado) {
    const box = document.getElementById('modal-alert');
    if (box) box.style.display = 'none';
    if (_resolveAlertPromise) {
        _resolveAlertPromise(!!confirmado);
        _resolveAlertPromise = null;
    }
}

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

function _moFindRow(ordenId, tipoOrden = 'personal') {
    return _moRows.find((x) => Number(x.id) === Number(ordenId) && (x.tipo_orden || 'personal') === tipoOrden)
        || _moRows.find((x) => Number(x.id) === Number(ordenId));
}

function _moCardId(row) {
    return 'card-' + (row.tipo_orden || 'personal') + '-' + Number(row.id || 0);
}

function _moEstadoColors(estado) {
    const map = {
        'Pendiente': ['#fef9c3', '#854d0e'],
        'Abierta': ['#fef9c3', '#854d0e'],
        'En proceso': ['#dbeafe', '#1e40af'],
        'Finalizada': ['#dcfce7', '#166534'],
        'Entregada': ['#f0fdf4', '#15803d'],
        'Nota de Credito': ['#fce7f3', '#9d174d'],
    };
    return map[estado] || ['#f1f5f9', '#475569'];
}

function _moRepuestoColors(estado) {
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
    return map[estado] || ['#f1f5f9', '#475569'];
}

function _moOption(value, label, actual) {
    const selected = String(value || '') === String(actual || '') ? ' selected' : '';
    return `<option value="${_h(value)}"${selected}>${_h(label || value)}</option>`;
}

function _estadoOrdenOptions(actual, esEmpresa = false) {
    const estados = esEmpresa
        ? ['Pendiente', 'En proceso', 'Finalizada', 'Entregada']
        : ['Pendiente', 'En proceso', 'Finalizada', 'Entregada', 'Nota de Credito'];
    const normalizado = actual === 'Abierta' ? 'Pendiente' : actual;
    const lista = estados.includes(normalizado) ? estados : [normalizado, ...estados].filter(Boolean);
    return lista.map((estado) => _moOption(estado, estado === normalizado ? `Actual: ${estado}` : estado, normalizado)).join('');
}

function _moActualizarCard(row) {
    if (!row) return;
    const card = document.getElementById(_moCardId(row));
    if (!card) return;

    card.dataset.orden = JSON.stringify(row);
    card.dataset.estado = row.estado_orden || '';

    const estadoBadge = card.querySelector('.card-estado-badge');
    if (estadoBadge) {
        const [bg, color] = _moEstadoColors(row.estado_orden || '');
        estadoBadge.textContent = row.estado_orden || '-';
        estadoBadge.style.background = bg;
        estadoBadge.style.color = color;
    }

    const repBadge = card.querySelector('.card-repuesto-badge');
    if (repBadge) {
        const [bg, color] = _moRepuestoColors(row.estado_repuesto || '');
        repBadge.textContent = row.estado_repuesto || '-';
        repBadge.style.background = bg;
        repBadge.style.color = color;
    }
}

function _moRefrescarModal(row) {
    _moActualizarCard(row);
    const modal = document.getElementById('modal-detalle');
    if (!modal || modal.style.display === 'none') return;
    const card = document.getElementById(_moCardId(row));
    if (card) verDetalleOrden(card);
}

function _moActualizarKpis() {
    const total = _moRows.length;
    const pendientes = _moRows.filter((o) => ['Pendiente', 'Abierta'].includes(o.estado_orden)).length;
    const proceso = _moRows.filter((o) => o.estado_orden === 'En proceso').length;
    const finalizadas = _moRows.filter((o) => o.estado_orden === 'Finalizada').length;
    const notas = _moRows.filter((o) => o.estado_orden === 'Nota de Credito').length;
    const entregadas = _moRows.filter((o) => o.estado_orden === 'Entregada').length;
    const valores = {
        'mo-kpi-pendiente': pendientes,
        'mo-kpi-enproceso': proceso,
        'mo-kpi-finalizada': finalizadas,
        'mo-kpi-notacred': notas,
        'mo-kpi-entregada': entregadas,
        'mo-kpi-todos': total,
    };
    Object.entries(valores).forEach(([id, valor]) => {
        const el = document.querySelector('#' + id + ' .mo-kpi-num');
        if (el) el.textContent = valor;
    });
}

function _moAplicarCambioLocal(row) {
    _moActualizarCard(row);
    _moActualizarKpis();
    filtrarOrdenes(_moFiltroActual);
    _moRefrescarModal(row);
}

let _moFiltroActual = 'Pendiente';

function filtrarOrdenes(estado) {
    _moFiltroActual = estado;
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

    let horasTrabajadas = null;
    if (tipoOrden === 'empresa' && nuevoEstado === 'Finalizada') {
        const row = _moFindRow(ordenId, tipoOrden);
        if (row && row.cliente && row.cliente.trim().toUpperCase() === 'RB-HEALTH ECUADOR CIA LTDA') {
            const { value: horas } = await Swal.fire({
                title: 'Horas Trabajadas',
                text: 'Por favor, ingrese el número de horas trabajadas para RB-HEALTH ECUADOR CIA LTDA:',
                input: 'number',
                inputAttributes: {
                    min: '0.1',
                    step: '0.1'
                },
                inputPlaceholder: 'Ej: 2.5',
                showCancelButton: true,
                confirmButtonText: 'Guardar y Finalizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#dc2626',
                allowOutsideClick: false,
                inputValidator: (value) => {
                    if (!value || isNaN(parseFloat(value)) || parseFloat(value) <= 0) {
                        return '¡Debe ingresar un número de horas válido mayor a 0!';
                    }
                }
            });

            if (!horas) {
                const rowCancel = _moFindRow(ordenId, tipoOrden);
                if (rowCancel) _moRefrescarModal(rowCancel);
                return;
            }
            horasTrabajadas = horas;
        }
    }

    let memoEntrega = null;
    let fotoEvidenciaFile = null;
    if (['entregada', 'entregado'].includes(String(nuevoEstado || '').toLowerCase())) {
        const row = _moFindRow(ordenId, tipoOrden);
        const memoPrevio = row?.memo_entrega || '';
        const fotoPrevia = row?.foto_evidencia_entrega || '';

        const { value: entregaData } = await Swal.fire({
            title: 'Requisitos de Entrega de Orden',
            html: `
                <div style="text-align:left; font-size:13px; color:#475569; margin-bottom:12px;">
                    Para entregar la orden <b>${_h(nroOrden)}</b> debe ingresar el memo de entrega y adjuntar la foto de evidencia.
                </div>
                <div style="text-align:left; margin-bottom:14px;">
                    <label style="font-weight:700; font-size:12px; color:#1e293b; display:block; margin-bottom:4px;">
                        Memo de Entrega <span style="color:#ef4444;">*</span>
                    </label>
                    <textarea id="swal-memo-entrega" class="swal2-textarea" style="width:100%; height:85px; margin:0; box-sizing:border-box; font-size:13px; border-radius:8px; border:1.5px solid #cbd5e1; padding:8px 10px; resize:vertical;" placeholder="Ej: Entregado al cliente titular con accesorios completos y comprobante firmado...">${_h(memoPrevio)}</textarea>
                </div>
                <div style="text-align:left;">
                    <label style="font-weight:700; font-size:12px; color:#1e293b; display:block; margin-bottom:4px;">
                        Foto de Evidencia de Entrega <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="file" id="swal-foto-evidencia" accept="image/*" capture="environment" style="width:100%; font-size:12px; padding:6px; border:1.5px dashed #059669; border-radius:8px; background:#f0fdf4; cursor:pointer;" onchange="previewFotoEvidenciaSwal(this)">
                    ${fotoPrevia ? `
                        <div style="margin-top:6px; font-size:11.5px; color:#059669;">
                            <i class="bi bi-check-circle-fill me-1"></i>Ya existe una foto previa de evidencia registrada. (Puedes subir una nueva para reemplazarla)
                        </div>
                    ` : ''}
                    <div id="swal-foto-preview-container" style="display:none; margin-top:10px; text-align:center;">
                        <img id="swal-foto-preview" style="max-width:100%; max-height:160px; border-radius:8px; border:2px solid #059669; object-fit:cover;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar y Entregar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            allowOutsideClick: false,
            focusConfirm: false,
            didOpen: () => {
                window.previewFotoEvidenciaSwal = function(input) {
                    const container = document.getElementById('swal-foto-preview-container');
                    const img = document.getElementById('swal-foto-preview');
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                            container.style.display = 'block';
                        };
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        container.style.display = 'none';
                    }
                };
            },
            preConfirm: () => {
                const memoEl = document.getElementById('swal-memo-entrega');
                const fotoEl = document.getElementById('swal-foto-evidencia');
                const memo = memoEl ? memoEl.value.trim() : '';
                const fotoFile = fotoEl && fotoEl.files ? fotoEl.files[0] : null;

                if (!memo) {
                    Swal.showValidationMessage('Debe ingresar un memo de entrega obligatorio.');
                    return false;
                }
                if (!fotoFile && !fotoPrevia) {
                    Swal.showValidationMessage('Debe tomar o adjuntar una foto de evidencia de entrega obligatoriamente.');
                    return false;
                }
                return { memo, fotoFile };
            }
        });

        if (!entregaData) {
            const rowCancel = _moFindRow(ordenId, tipoOrden);
            if (rowCancel) _moRefrescarModal(rowCancel);
            return;
        }

        memoEntrega = entregaData.memo;
        fotoEvidenciaFile = entregaData.fotoFile;
    }

    const verificado = await mostrarAlertaEstetica(`¿Confirma la actualización de la orden <b>${_h(nroOrden)}</b> a estado: <b>${_h(nuevoEstado)}</b>?`, 'confirm', 'Confirmar Cambio de Estado');
    if (!verificado) {
        const rowCancel = _moFindRow(ordenId, tipoOrden);
        if (rowCancel) _moRefrescarModal(rowCancel);
        return;
    }

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('id', ordenId);
    fd.append('estado', nuevoEstado);
    fd.append('tipo_orden', tipoOrden);
    if (horasTrabajadas !== null) {
        fd.append('horas_trabajadas', horasTrabajadas);
    }
    if (memoEntrega !== null) {
        fd.append('memo_entrega', memoEntrega);
    }
    if (fotoEvidenciaFile !== null) {
        fd.append('foto_evidencia', fotoEvidenciaFile);
    }

    try {
        const r = await fetch(_moUrlEstado, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            await mostrarAlertaEstetica(d.error || 'No se pudo actualizar el estado.', 'error', 'Error de Operación');
            return;
        }
        const row = _moFindRow(ordenId, tipoOrden);
        if (row) {
            row.estado_orden = nuevoEstado;
            if (memoEntrega !== null) {
                row.memo_entrega = memoEntrega;
            }
            if (d.foto_evidencia_entrega) {
                row.foto_evidencia_entrega = d.foto_evidencia_entrega;
            }
            if (nuevoEstado === 'Nota de Credito') {
                row.nc_estado = row.nc_estado || 'Pendiente';
            }
            if (horasTrabajadas !== null) {
                row.horas_trabajadas = parseFloat(horasTrabajadas);
                row.valor_hora = 52.0;
            }
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Estado actualizado correctamente.', 'success', 'Estado Actualizado');
    } catch {
        await mostrarAlertaEstetica('Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error', 'Error de Conexión');
    }
}

async function guardarHorasTrabajadas(ordenId, nroOrden, estadoActual) {
    const valInput = document.getElementById('gestion-horas-' + ordenId);
    if (!valInput) return;
    const val = valInput.value.trim();
    if (!val || isNaN(parseFloat(val)) || parseFloat(val) <= 0) {
        await mostrarAlertaEstetica('Debe ingresar un número de horas válido mayor a 0.', 'error', 'Horas Inválidas');
        return;
    }

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('id', ordenId);
    fd.append('estado', estadoActual);
    fd.append('tipo_orden', 'empresa');
    fd.append('horas_trabajadas', val);

    const feedback = document.getElementById('feedback-horas-' + ordenId);
    if (feedback) feedback.classList.add('loading');

    try {
        const r = await fetch(_moUrlEstado, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            await mostrarAlertaEstetica(d.error || 'No se pudo actualizar las horas.', 'error', 'Error');
            return;
        }
        const row = _moFindRow(ordenId, 'empresa');
        if (row) {
            row.horas_trabajadas = parseFloat(val);
            row.valor_hora = 52.0;
            _moAplicarCambioLocal(row);
            const cardEl = document.getElementById('card-empresa-' + ordenId);
            if (cardEl) {
                cardEl.setAttribute('data-orden', JSON.stringify(row));
                verDetalleOrden(cardEl);
            }
        }
        await mostrarAlertaEstetica('Horas trabajadas guardadas correctamente.', 'success', 'Éxito');
    } catch {
        await mostrarAlertaEstetica('Error al guardar las horas trabajadas.', 'error', 'Error');
    } finally {
        if (feedback) feedback.classList.remove('loading');
    }
}

async function reasignarOrden(ordenId, nuevoTecnicoId, tipoOrden = 'personal') {
    if (!nuevoTecnicoId) return;

    const verificado = await mostrarAlertaEstetica(`¿Confirma reasignar esta orden al nuevo técnico seleccionado?`, 'confirm', 'Confirmar Reasignación');
    if (!verificado) {
        const row = _moFindRow(ordenId, tipoOrden);
        if (row) _moRefrescarModal(row);
        return;
    }

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('tecnico_id', nuevoTecnicoId);
    fd.append('tipo_orden', tipoOrden);

    try {
        const r = await fetch(_moUrlReasignar, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            await mostrarAlertaEstetica(d.error || 'No se pudo reasignar la orden.', 'error', 'Error de Reasignación');
            location.reload();
            return;
        }
        location.reload();
    } catch {
        await mostrarAlertaEstetica('Error de conexión con el servidor.', 'error', 'Error de Conexión');
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
            await mostrarAlertaEstetica(d.error || 'No se pudo actualizar la garantía.', 'error', 'Error de Garantía');
            return;
        }
        const row = _moFindRow(ordenId, 'personal');
        if (row) {
            row.estado_garantia = nuevoEstado;
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Estado de garantia actualizado.', 'success', 'Garantia Actualizada');
    } catch {
        await mostrarAlertaEstetica('Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error', 'Error de Conexión');
    }
}

async function cambiarEstadoRepuesto(ordenId, nuevoEstado, tipoOrden = 'personal') {
    if (!nuevoEstado) return;
    if (nuevoEstado === 'Requerido') {
        abrirModalSR(ordenId, tipoOrden);
        return;
    }
    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('estado_repuesto', nuevoEstado);
    fd.append('tipo_orden', tipoOrden);
    try {
        const r = await fetch(_moUrlRepEstado, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            await mostrarAlertaEstetica(d.error || 'No se pudo actualizar el repuesto.', 'error', 'Error de Repuesto');
            const row = _moFindRow(ordenId, tipoOrden);
            if (row) {
                _moAplicarCambioLocal(row);
            }
            return;
        }
        const row = _moFindRow(ordenId, tipoOrden);
        if (row) {
            row.estado_repuesto = nuevoEstado;
            if (nuevoEstado !== 'Con stock') {
                row.repuesto_inventario_id = 0;
                row.repuesto_codigo = '';
                row.repuesto_nombre = '';
            }
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Estado de repuesto actualizado.', 'success', 'Repuesto Actualizado');
    } catch {
        await mostrarAlertaEstetica('Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error', 'Error de Conexión');
    }
}

function limpiarRepuestoSeleccionadoMisOrdenes(ordenId) {
    const hid = document.getElementById('rep-inv-' + ordenId);
    const inp = document.getElementById('rep-inv-query-' + ordenId);
    const tag = document.getElementById('rep-inv-selected-' + ordenId);
    const text = document.getElementById('rep-inv-selected-text-' + ordenId);
    const qty = document.getElementById('rep-inv-qty-' + ordenId);
    if (hid) {
        hid.value = '';
        delete hid.dataset.codigo;
        delete hid.dataset.nombre;
    }
    if (inp) inp.value = '';
    if (tag) tag.style.display = 'none';
    if (text) text.textContent = '';
    if (qty) qty.value = '1';
}

function seleccionarRepuestoMisOrdenes(ordenId, repuestoId, codigo, nombre) {
    const hid = document.getElementById('rep-inv-' + ordenId);
    const inp = document.getElementById('rep-inv-query-' + ordenId);
    const box = document.getElementById('rep-inv-resultados-' + ordenId);
    const tag = document.getElementById('rep-inv-selected-' + ordenId);
    const text = document.getElementById('rep-inv-selected-text-' + ordenId);
    const qty = document.getElementById('rep-inv-qty-' + ordenId);

    const cod = String(codigo || '').trim();
    const nom = String(nombre || '').trim();
    if (hid) {
        hid.value = Number(repuestoId || 0) || '';
        hid.dataset.codigo = cod;
        hid.dataset.nombre = nom;
    }
    if (inp) inp.value = cod || nom;
    if (text) text.textContent = (cod || '-') + ' - ' + (nom || '-');
    if (qty) qty.value = '1';
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

    box.innerHTML = '';
    repuestos.forEach((r) => {
        const item = document.createElement('div');
        item.className = 'rep-item';
        item.style.cssText = 'cursor:pointer; user-select:none;';
        item.innerHTML = `
            <span><code style="font-size:11.5px;color:#b45309;font-weight:800;">${_h(r.codigo || '-')}</code> ${_h(r.nombre || '-')}</span>
            <span style="background:#dcfce7;color:#166534;font-size:10px;padding:2px 7px;border-radius:999px;font-weight:700;">Stock ${Number(r.stock || 0)}</span>
        `;
        
        item._repuestoData = r;

        const doSelect = (e) => {
            if (e) e.preventDefault();
            seleccionarRepuestoMisOrdenes(ordenId, r.id, r.codigo, r.nombre);
        };

        item.onmousedown = doSelect;
        item.onclick = doSelect;

        box.appendChild(item);
    });
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
    if (hid) {
        hid.value = '';
        delete hid.dataset.codigo;
        delete hid.dataset.nombre;
    }
    if (tag) tag.style.display = 'none';
    if (text) text.textContent = '';
    buscarRepuestoMisOrdenes(ordenId, q);
}

async function asignarRepuesto(ordenId, tipoOrden = 'personal') {
    let sel = document.getElementById('rep-inv-' + ordenId);
    if (!sel || !sel.value) {
        const firstItem = document.querySelector('#rep-inv-resultados-' + ordenId + ' .rep-item');
        if (firstItem && firstItem._repuestoData) {
            const r = firstItem._repuestoData;
            seleccionarRepuestoMisOrdenes(ordenId, r.id, r.codigo, r.nombre);
            sel = document.getElementById('rep-inv-' + ordenId);
        }
    }
    if (!sel || !sel.value) {
        await mostrarAlertaEstetica('Por favor, <b>seleccione un repuesto</b> del listado de búsqueda antes de continuar.', 'warning', 'Selección Requerida');
        return;
    }
    const qtyInput = document.getElementById('rep-inv-qty-' + ordenId);
    const cantidad = qtyInput ? parseInt(qtyInput.value || 1) : 1;

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('repuesto_inventario_id', sel.value);
    fd.append('tipo_orden', tipoOrden);
    fd.append('cantidad', cantidad);
    try {
        const r = await fetch(_moUrlRepAsignar, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            await mostrarAlertaEstetica(d.error || 'No se pudo asignar el repuesto.', 'error', 'Error de Asignación');
            return;
        }
        const row = _moFindRow(ordenId, tipoOrden);
        if (row) {
            const repuestoId = Number(sel.value || 0);
            const codigo = sel.dataset.codigo || '';
            const nombre = sel.dataset.nombre || '';
            row.estado_repuesto = 'Con stock';
            row.repuesto_inventario_id = repuestoId;
            row.repuesto_codigo = codigo;
            row.repuesto_nombre = nombre;
            row.repuestos_asignados = Array.isArray(row.repuestos_asignados) ? row.repuestos_asignados : [];
            
            const existing = row.repuestos_asignados.find((ra) => Number(ra.repuesto_id) === repuestoId);
            if (existing) {
                existing.cantidad = Number(existing.cantidad) + cantidad;
            } else {
                row.repuestos_asignados.push({
                    id: repuestoId,
                    repuesto_id: repuestoId,
                    codigo,
                    nombre,
                    cantidad: cantidad,
                });
            }
            limpiarRepuestoSeleccionadoMisOrdenes(ordenId);
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Repuesto asignado correctamente.', 'success', 'Repuesto Asignado');
    } catch {
        await mostrarAlertaEstetica('Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error', 'Error de Conexión');
    }
}

async function revertirRepuesto(ordenId, tipoOrden = 'personal') {
    const verificado = await mostrarAlertaEstetica('¿Confirma revertir todos los repuestos asignados y devolver el stock correspondiente al inventario?', 'confirm', 'Revertir Todos los Repuestos');
    if (!verificado) return;

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('tipo_orden', tipoOrden);
    try {
        const r = await fetch(_moUrlRepRevertir, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            await mostrarAlertaEstetica(d.error || 'No se pudo revertir la asignación del repuesto.', 'error', 'Error al Revertir');
            return;
        }
        const row = _moFindRow(ordenId, tipoOrden);
        if (row) {
            row.repuestos_asignados = [];
            row.estado_repuesto = 'No requerido';
            row.repuesto_inventario_id = 0;
            row.repuesto_codigo = '';
            row.repuesto_nombre = '';
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Repuesto revertido correctamente.', 'success', 'Repuesto Revertido');
    } catch {
        await mostrarAlertaEstetica('Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error', 'Error de Conexión');
    }
}

async function revertirRepuestoIndividual(ordenId, repuestoId, tipoOrden = 'personal') {
    const verificado = await mostrarAlertaEstetica('¿Confirma revertir este repuesto específico y devolver su stock correspondiente al inventario?', 'confirm', 'Revertir Asignación de Repuesto');
    if (!verificado) return;

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('repuesto_id', repuestoId);
    fd.append('tipo_orden', tipoOrden);
    try {
        const r = await fetch(_moUrlRepRevertir, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            await mostrarAlertaEstetica(d.error || 'No se pudo revertir la asignación de este repuesto.', 'error', 'Error al Revertir');
            return;
        }
        const row = _moFindRow(ordenId, tipoOrden);
        if (row) {
            row.repuestos_asignados = (row.repuestos_asignados || []).filter((ra) => Number(ra.repuesto_id) !== Number(repuestoId));
            if (row.repuestos_asignados.length > 0) {
                const principal = row.repuestos_asignados[0];
                row.estado_repuesto = 'Con stock';
                row.repuesto_inventario_id = Number(principal.repuesto_id || 0);
                row.repuesto_codigo = principal.codigo || '';
                row.repuesto_nombre = principal.nombre || '';
            } else {
                row.estado_repuesto = 'No requerido';
                row.repuesto_inventario_id = 0;
                row.repuesto_codigo = '';
                row.repuesto_nombre = '';
            }
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Repuesto revertido correctamente.', 'success', 'Repuesto Revertido');
    } catch {
        await mostrarAlertaEstetica('Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error', 'Error de Conexión');
    }
}

async function abrirModalNC(ordenId) {
    const row = _moRows.find((x) => Number(x.id) === Number(ordenId));
    if (!row) return;
    if ((row.estado_garantia || 'Pendiente') !== 'Aceptada') {
        await mostrarAlertaEstetica('La garantía de esta orden debe estar en estado <b>Aceptada</b> para poder solicitar una Nota de Crédito.', 'warning', 'Requisito de Garantía');
        return;
    }
    if (!row.informe_id) {
        await mostrarAlertaEstetica('Esta orden no tiene un informe técnico registrado. Debe <b>crear el informe técnico</b> antes de poder solicitar una Nota de Crédito.', 'warning', 'Informe Requerido');
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

function cerrarModalNC(restaurarSelect = true) {
    const ordenId = _ncOrdenId;
    document.getElementById('modal-nota-credito').style.display = 'none';
    if (restaurarSelect && ordenId) {
        const row = _moFindRow(ordenId, 'personal');
        if (row) _moRefrescarModal(row);
    }
    _ncOrdenId = 0;
}

/*  Solicitud de Repuesto Modal  */
function abrirModalSR(ordenId, tipoOrden = 'personal') {
    const row = _moFindRow(ordenId, tipoOrden);
    if (!row) return;

    document.getElementById('sr-orden-id').value = ordenId;
    document.getElementById('sr-orden-tipo').value = tipoOrden;
    document.getElementById('sr-nro-orden-lbl').textContent = row.nro_orden || '-';
    document.getElementById('sr-repuesto-nombre').value = '';
    document.getElementById('sr-cantidad').value = '1';
    document.getElementById('sr-nro-parte').value = '';
    document.getElementById('sr-link-compra').value = '';
    document.getElementById('sr-descripcion').value = '';
    document.getElementById('sr-error').style.display = 'none';
    document.getElementById('modal-solicitud-repuesto').style.display = 'flex';
    document.getElementById('sr-repuesto-nombre').focus();
}

function cerrarModalSR(restaurarSelect = true) {
    document.getElementById('modal-solicitud-repuesto').style.display = 'none';
    if (!restaurarSelect) return;
    
    const ordenId = document.getElementById('sr-orden-id').value;
    const tipoOrden = document.getElementById('sr-orden-tipo').value;
    const row = _moFindRow(ordenId, tipoOrden);
    if (row) {
        const panel = document.querySelector('.mis-ordenes-container');
        if (panel) {
            const sel = panel.querySelector('.gestion-row.repuesto-row select');
            if (sel) {
                sel.value = row.estado_repuesto || 'No requerido';
            }
        }
    }
}

async function confirmarSR() {
    const ordenId = document.getElementById('sr-orden-id').value;
    const tipoOrden = document.getElementById('sr-orden-tipo').value;
    const repNombre = document.getElementById('sr-repuesto-nombre').value.trim();
    const cantidad = document.getElementById('sr-cantidad').value.trim();
    const nroParte = document.getElementById('sr-nro-parte').value.trim();
    const linkCompra = document.getElementById('sr-link-compra').value.trim();
    const descripcion = document.getElementById('sr-descripcion').value.trim();
    const err = document.getElementById('sr-error');
    
    err.style.display = 'none';
    if (!repNombre) { err.textContent = 'El nombre del repuesto es obligatorio.'; err.style.display = 'block'; return; }
    if (!cantidad || Number(cantidad) < 1) { err.textContent = 'La cantidad debe ser al menos 1.'; err.style.display = 'block'; return; }
    if (!descripcion) { err.textContent = 'La descripción es obligatoria.'; err.style.display = 'block'; return; }

    const btn = document.getElementById('btn-confirmar-sr');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Enviando...';

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    fd.append('orden_id', ordenId);
    fd.append('tipo_orden', tipoOrden);
    fd.append('cantidad', cantidad);
    fd.append('repuesto_nombre', repNombre);
    fd.append('nro_parte', nroParte);
    fd.append('link_compra', linkCompra);
    fd.append('descripcion', descripcion);

    try {
        const r = await fetch(_moUrlSolicitarRepuesto, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.ok) {
            err.textContent = d.error || 'No se pudo registrar la solicitud.';
            err.style.display = 'block';
            return;
        }
        cerrarModalSR(false);
        const row = _moFindRow(ordenId, tipoOrden);
        if (row) {
            row.estado_repuesto = 'Requerido';
            row.repuesto_inventario_id = 0;
            row.repuesto_codigo = '';
            row.repuesto_nombre = '';
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Solicitud registrada correctamente.', 'success', 'Solicitud Registrada');
    } catch {
        err.textContent = 'Error de conexión con el servidor.';
        err.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Enviar Solicitud';
    }
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
        const ordenIdNc = _ncOrdenId;
        cerrarModalNC(false);
        const row = _moFindRow(ordenIdNc, 'personal');
        if (row) {
            row.estado_orden = 'Nota de Credito';
            row.nc_estado = row.nc_estado || 'Pendiente';
            _moAplicarCambioLocal(row);
        }
        await mostrarAlertaEstetica(d.mensaje || 'Solicitud de nota de credito registrada.', 'success', 'Nota de Credito');
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
    window.location.href = _moUrlInformesCrear + '?orden_id=' + encodeURIComponent(String(ordenId));
}

async function verPdfInforme(ordenId) {
    try {
        const r = await fetch(_moUrlInformeVer + '?orden_id=' + encodeURIComponent(String(ordenId)), { cache: 'no-store' });
        const d = await r.json();
        if (!d.ok || !d.informe || !d.informe.id) {
            await mostrarAlertaEstetica('Esta orden aún no cuenta con un informe técnico registrado.', 'warning', 'Sin Informe Técnico');
            return;
        }
        window.open(_moUrlInformePrintBase + '/' + d.informe.id + '/imprimir', '_blank');
    } catch {
        await mostrarAlertaEstetica('No se pudo consultar el informe técnico debido a un error en el servidor.', 'error', 'Error de Consulta');
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
    const sucursalClienteTexto = o.nro_sucursal_cliente
        ? String(o.nro_sucursal_cliente).padStart(3, '0') + (o.sucursal_cliente_nombre ? ' - ' + o.sucursal_cliente_nombre : '')
        : '-';

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
                    <div class="det-campo"><label>Código Producto</label><span>${_h(o.producto_inventario_codigo || '-')}</span></div>
                    <div class="det-campo"><label>Sucursal</label><span>${_h(o.sucursal || '-')}</span></div>
                    <div class="det-campo"><label>Sucursal Cliente</label><span>${_h(sucursalClienteTexto)}</span></div>
                    <div class="det-campo"><label>Motivo</label><span>${_h(o.motivo_ingreso || '-')}</span></div>
                    ${esEmpresa 
                        ? `<div class="det-campo"><label>Nro. Ticket</label><span>${_h(o.nro_factura || '-')}</span></div>` 
                        : `<div class="det-campo"><label>Nro. Factura</label><span>${_h(o.nro_factura || '-')}</span></div>`
                    }
                    ${esEmpresa ? `
                    <div class="det-campo"><label>Valor Hora</label><span>${o.valor_hora ? '$' + parseFloat(o.valor_hora).toFixed(2) : '-'}</span></div>
                    <div class="det-campo"><label>Horas Trabajadas</label><span>${o.horas_trabajadas !== null && o.horas_trabajadas !== undefined ? o.horas_trabajadas : '-'}</span></div>
                    ` : ''}
                    <div class="det-campo det-full"><label>Falla</label><span>${_h(o.falla || '-')}</span></div>
                    <div class="det-campo det-full"><label>Observacion</label><span>${_h(o.observacion || '-')}</span></div>
                    ${(o.memo_entrega || o.foto_evidencia_entrega) ? `
                    <div class="det-campo det-full" style="grid-column: 1 / -1; margin-top: 8px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px;">
                        <label style="color:#166534; font-weight:700; font-size:12.5px; display:flex; align-items:center; gap:6px; margin-bottom:6px;">
                            <i class="bi bi-patch-check-fill" style="color:#16a34a; font-size:16px;"></i>Evidencia de Entrega
                        </label>
                        ${o.memo_entrega ? `<div style="font-size:12px; color:#14532d; margin-bottom:6px;"><b>Memo:</b> ${_h(o.memo_entrega)}</div>` : ''}
                        ${o.foto_evidencia_entrega ? `
                            <div style="margin-top:6px;">
                                <span style="font-size:11px; font-weight:700; color:#166534; display:block; margin-bottom:4px;"><i class="bi bi-camera me-1"></i>Foto Adjunta:</span>
                                <a href="${_h(o.foto_evidencia_entrega)}" target="_blank" title="Clic para abrir imagen en tamaño completo">
                                    <img src="${_h(o.foto_evidencia_entrega)}" style="max-width:100%; max-height:220px; border-radius:8px; border:2px solid #86efac; object-fit:cover; cursor:pointer;" alt="Foto Evidencia Entrega">
                                </a>
                            </div>
                        ` : ''}
                    </div>
                    ` : ''}
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
                        ${_estadoOrdenOptions(o.estado_orden, true)}
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>
                ${o.cliente && o.cliente.trim().toUpperCase() === 'RB-HEALTH ECUADOR CIA LTDA' ? `
                <div class="gestion-row">
                    <span class="gestion-icon"><i class="bi bi-clock"></i></span>
                    <span class="gestion-label">Horas Trabajadas</span>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <input type="number" step="0.1" min="0.1" id="gestion-horas-${Number(o.id)}" class="gestion-select" style="padding: 4px 8px; border: 1.5px solid var(--mo-border); border-radius: 6px; width: 80px;" 
                            value="${o.horas_trabajadas || ''}" placeholder="Ej: 2.5">
                        <button type="button" class="btn-mini-rep" style="background:var(--mo-primary); color:#fff; border:none; padding:4px 8px; margin:0;" onclick="guardarHorasTrabajadas(${Number(o.id)}, '${_h(String(o.nro_orden || '')).replace(/'/g, "\\'")}', '${o.estado_orden}')">
                            Guardar
                        </button>
                    </div>
                    <span class="gestion-feedback" id="feedback-horas-${Number(o.id)}">&#8635;</span>
                </div>
                ` : ''}
                ${PUEDE_REASIGNAR ? `
                <div class="gestion-row">
                    <span class="gestion-icon"><i class="bi bi-person-gear"></i></span>
                    <span class="gestion-label">Reasignar</span>
                    <select class="gestion-select" onchange="reasignarOrden(${Number(o.id)}, this.value, 'empresa')">
                        <option value="">Seleccionar técnico...</option>
                        ${_moTecnicos.map(t => `<option value="${t.id}">${_h(t.nombre_tecnico)} (${t.pendientes + t.en_proceso} OT)</option>`).join('')}
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>
                ` : ''}

                ${o.motivo_ingreso === 'Empresa - Stock' || o.motivo_ingreso === 'Empresa - Autoconsumo' ? `
                <div class="gestion-row repuesto-row">
                    <span class="gestion-icon"><i class="bi bi-tools"></i></span>
                    <span class="gestion-label">Repuesto</span>
                    <select class="gestion-select" onchange="cambiarEstadoRepuesto(${Number(o.id)}, this.value, 'empresa')">
                        <option value="No requerido" ${(o.estado_repuesto || '') === 'No requerido' ? 'selected' : ''}>No requerido</option>
                        <option value="Requerido" ${(o.estado_repuesto || '') === 'Requerido' ? 'selected' : ''}>Requerido</option>
                        <option value="Con stock" ${(o.estado_repuesto || '') === 'Con stock' ? 'selected' : ''}>Con stock</option>
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>

                ${o.tipo_orden === 'empresa' && o.empresa_id === 1 && o.subtipo === 'Stock' && o.productos_inventario_st ? o.productos_inventario_st.map(p => `
                <div class="gestion-row">
                    <span class="gestion-icon"><i class="bi bi-box-seam" style="color: #0f766e;"></i></span>
                    <span class="gestion-label" style="color: #0f766e; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;" title="${_h(p.nombre)}">ST: ${_h(p.serie)}</span>
                    <select class="gestion-select" onchange="cambiarEstadoFisicoDirecto(${Number(o.id)}, ${p.id}, this.value)" style="border-color:#0f766e;">
                        <option value="Tienda" ${p.estado === 'Tienda' ? 'selected' : ''}>Tienda (Operativo)</option>
                        <option value="Incinerox" ${p.estado === 'Incinerox' ? 'selected' : ''}>Incinerox (Incinerar)</option>
                        <option value="Outlet" ${p.estado === 'Outlet' ? 'selected' : ''}>Outlet (Con Detalle)</option>
                    </select>
                    <span class="gestion-feedback" id="feedback-fisico-${p.id}">&#8635;</span>
                </div>
                
                <div class="detalle-outlet-container" id="container-detalle-${p.id}" style="display: ${p.estado === 'Outlet' ? 'block' : 'none'}; padding: 4px 12px 10px 40px; margin-top: -6px; border-bottom: 1px solid var(--mo-border);">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="text" class="form-control form-control-sm" id="input-detalle-${p.id}" value="${_h(p.detalle_outlet)}" placeholder="Describa el detalle de outlet..." style="font-size:12px; border-radius:6px; background:#fff; color:#000;">
                        <button type="button" class="btn btn-sm btn-teal" style="background:#0f766e; color:#fff; border:none; padding:4px 10px; font-size:11px; border-radius:6px; margin:0;" onclick="guardarDetalleOutletDirecto(${Number(o.id)}, ${p.id})">Guardar</button>
                    </div>
                </div>
                `).join('') : ''}

                <div class="rep-picker">
                    <div class="assigned-repuestos-list" style="margin-bottom:12px;">
                        <span style="font-size:11px; font-weight:700; color:var(--mo-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                            <i class="bi bi-box-seam me-1"></i>Repuestos Asignados en Stock:
                        </span>
                        ${o.repuestos_asignados && o.repuestos_asignados.length > 0 ? `
                            <div style="background:#fff; border:1px solid var(--mo-border); border-radius:8px; overflow:hidden; overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
                                    <thead>
                                        <tr style="background:#f8fafc; border-bottom:1px solid var(--mo-border); color:var(--mo-muted); font-weight:700;">
                                            <th style="padding:6px 8px;">Código</th>
                                            <th style="padding:6px 8px;">Nombre</th>
                                            <th style="padding:6px 8px; text-align:center; width:60px;">Cant.</th>
                                            <th style="padding:6px 8px; text-align:center; width:65px;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${o.repuestos_asignados.map(ra => `
                                            <tr style="border-bottom:1px solid #f1f5f9;">
                                                <td style="padding:6px 8px; font-family:monospace; font-weight:600; color:#b45309;">${_h(ra.codigo)}</td>
                                                <td style="padding:6px 8px; color:var(--mo-slate);">${_h(ra.nombre)}</td>
                                                <td style="padding:6px 8px; text-align:center; font-weight:700; color:var(--mo-teal);">${ra.cantidad || 1}</td>
                                                <td style="padding:6px 8px; text-align:center;">
                                                    <button type="button" class="btn-mini-rep danger" style="padding:2px 6px; font-size:10px; margin:0;"
                                                            onclick="revertirRepuestoIndividual(${Number(o.id)}, ${Number(ra.repuesto_id)}, 'empresa')" title="Revertir este repuesto">
                                                        <i class="bi bi-trash"></i> Revertir
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        ` : `
                            <p style="margin:0 0 8px; font-size:12px; color:var(--mo-muted); font-style:italic;">Ningún repuesto de stock asignado actualmente.</p>
                        `}
                    </div>

                    <div style="position:relative; border-top:1px dashed var(--mo-border); margin:12px 0 8px; padding-top:10px;">
                        <span style="font-size:11px; font-weight:700; color:var(--mo-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                            <i class="bi bi-search me-1"></i>Asignar Nuevo Repuesto:
                        </span>
                        <input type="hidden" id="rep-inv-${Number(o.id)}" value="">
                        <input type="text" class="rep-input" id="rep-inv-query-${Number(o.id)}" placeholder="Buscar repuesto por codigo o nombre..." autocomplete="off"
                               value=""
                               oninput="onInputBuscarRepuestoMisOrdenes(${Number(o.id)}, this.value)"
                               onfocus="buscarRepuestoMisOrdenes(${Number(o.id)}, this.value)">
                        <div class="rep-resultados" id="rep-inv-resultados-${Number(o.id)}"></div>
                        <div class="rep-seleccionado" id="rep-inv-selected-${Number(o.id)}" style="display:none; align-items:center; gap:8px; margin-bottom:8px;">
                            <span id="rep-inv-selected-text-${Number(o.id)}" style="flex-grow:1;">Repuesto seleccionado</span>
                            <div style="display:flex; align-items:center; gap:4px;">
                                <label style="font-size:11px; font-weight:bold; color:var(--mo-muted); margin:0;">Cant:</label>
                                <input type="number" id="rep-inv-qty-${Number(o.id)}" min="1" value="1" style="width:60px; height:26px; font-size:12px; padding:2px 5px; border:1px solid var(--mo-border); border-radius:4px;">
                            </div>
                            <button type="button" class="rep-clear" onclick="limpiarRepuestoSeleccionadoMisOrdenes(${Number(o.id)})" title="Quitar seleccion" style="margin-left:4px;">&times;</button>
                        </div>
                    </div>
                    
                    <div class="gestion-actions-rep">
                        <button type="button" class="btn-mini-rep" onclick="asignarRepuesto(${Number(o.id)}, 'empresa')">Asignar repuesto</button>
                        <button type="button" class="btn-mini-rep danger" onclick="revertirRepuesto(${Number(o.id)}, 'empresa')">Revertir todos</button>
                    </div>
                </div>
                ` : ''}

                <div class="gestion-actions-rep" style="margin-top:12px;">
                    <button type="button" class="btn-mini-rep" onclick="abrirInformeDeOrden(${-1 * Number(o.id)})"><i class="bi bi-pencil-square me-1"></i>Gestionar informe</button>
                    <button type="button" class="btn-mini-rep" onclick="verPdfInforme(${-1 * Number(o.id)})"><i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF informe</button>
                    <button type="button" class="btn-mini-rep violet" onclick="registrarLlamadaCliente(${Number(o.id)}, 'empresa')"><i class="bi bi-telephone-plus me-1"></i>Llamada cliente</button>
                    <button type="button" class="btn-mini-rep" onclick="abrirModalEnviarEmail(${Number(o.id)}, 'empresa')"><i class="bi bi-envelope me-1"></i>Enviar email</button>
                </div>

                <div class="llamadas-section" style="margin-top: 14px; border-top: 1px dashed var(--mo-border); padding-top: 12px;">
                    <span style="font-size:11px; font-weight:700; color:var(--mo-muted); text-transform:uppercase; display:block; margin-bottom:8px;">
                        <i class="bi bi-clock-history me-1"></i>Historial de Llamadas (${o.llamadas ? o.llamadas.length : 0}):
                    </span>
                    ${o.llamadas && o.llamadas.length > 0 ? `
                        <div class="llamadas-timeline" style="display: flex; flex-direction: column; gap: 8px; max-height: 150px; overflow-y: auto; padding-right: 4px;">
                            ${o.llamadas.map(ll => `
                                <div style="background: #f8fafc; border: 1px solid var(--mo-border); border-radius: 8px; padding: 8px 10px; font-size: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 3px; font-weight: 600; color: var(--mo-muted); font-size: 10.5px;">
                                        <span><i class="bi bi-person me-1"></i>${_h(ll.usuario_nombre)}</span>
                                        <span><i class="bi bi-calendar3 me-1"></i>${_h(ll.fecha_hora)}</span>
                                    </div>
                                    <div style="color: var(--mo-slate); line-height: 1.3;">
                                        ${_h(ll.observacion)}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <p style="margin:0; font-size:12px; color:var(--mo-muted); font-style:italic;">No se han registrado llamadas para esta orden.</p>
                    `}
                </div>
            </div>
            ` : `
            <div class="gestion-panel">
                <div class="gestion-panel-title"><i class="bi bi-sliders2"></i>Gestion de la orden</div>

                <div class="gestion-row">
                    <span class="gestion-icon"><i class="bi bi-activity"></i></span>
                    <span class="gestion-label">Estado</span>
                    <select class="gestion-select" onchange="cambiarEstado(${Number(o.id)}, this.value, '${_h(String(o.nro_orden || '')).replace(/'/g, "\\'")}', 'personal')">
                        ${_estadoOrdenOptions(o.estado_orden, false)}
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>
                ${PUEDE_REASIGNAR ? `
                <div class="gestion-row">
                    <span class="gestion-icon"><i class="bi bi-person-gear"></i></span>
                    <span class="gestion-label">Reasignar</span>
                    <select class="gestion-select" onchange="reasignarOrden(${Number(o.id)}, this.value, 'personal')">
                        <option value="">Seleccionar técnico...</option>
                        ${_moTecnicos.map(t => `<option value="${t.id}">${_h(t.nombre_tecnico)} (${t.pendientes + t.en_proceso} OT)</option>`).join('')}
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>
                ` : ''}

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
                    <select class="gestion-select" onchange="cambiarEstadoRepuesto(${Number(o.id)}, this.value, 'personal')">
                        <option value="No requerido" ${(o.estado_repuesto || '') === 'No requerido' ? 'selected' : ''}>No requerido</option>
                        <option value="Requerido" ${(o.estado_repuesto || '') === 'Requerido' ? 'selected' : ''}>Requerido</option>
                        <option value="Con stock" ${(o.estado_repuesto || '') === 'Con stock' ? 'selected' : ''}>Con stock</option>
                    </select>
                    <span class="gestion-feedback">&#8635;</span>
                </div>

                <div class="rep-picker">
                    <div class="assigned-repuestos-list" style="margin-bottom:12px;">
                        <span style="font-size:11px; font-weight:700; color:var(--mo-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                            <i class="bi bi-box-seam me-1"></i>Repuestos Asignados en Stock:
                        </span>
                        ${o.repuestos_asignados && o.repuestos_asignados.length > 0 ? `
                            <div style="background:#fff; border:1px solid var(--mo-border); border-radius:8px; overflow:hidden; overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
                                    <thead>
                                        <tr style="background:#f8fafc; border-bottom:1px solid var(--mo-border); color:var(--mo-muted); font-weight:700;">
                                            <th style="padding:6px 8px;">Código</th>
                                            <th style="padding:6px 8px;">Nombre</th>
                                            <th style="padding:6px 8px; text-align:center; width:60px;">Cant.</th>
                                            <th style="padding:6px 8px; text-align:center; width:65px;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${o.repuestos_asignados.map(ra => `
                                            <tr style="border-bottom:1px solid #f1f5f9;">
                                                <td style="padding:6px 8px; font-family:monospace; font-weight:600; color:#b45309;">${_h(ra.codigo)}</td>
                                                <td style="padding:6px 8px; color:var(--mo-slate);">${_h(ra.nombre)}</td>
                                                <td style="padding:6px 8px; text-align:center; font-weight:700; color:var(--mo-teal);">${ra.cantidad || 1}</td>
                                                <td style="padding:6px 8px; text-align:center;">
                                                    <button type="button" class="btn-mini-rep danger" style="padding:2px 6px; font-size:10px; margin:0;"
                                                            onclick="revertirRepuestoIndividual(${Number(o.id)}, ${Number(ra.repuesto_id)}, 'personal')" title="Revertir este repuesto">
                                                        <i class="bi bi-trash"></i> Revertir
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        ` : `
                            <p style="margin:0 0 8px; font-size:12px; color:var(--mo-muted); font-style:italic;">Ningún repuesto de stock asignado actualmente.</p>
                        `}
                    </div>

                    <div style="position:relative; border-top:1px dashed var(--mo-border); margin:12px 0 8px; padding-top:10px;">
                        <span style="font-size:11px; font-weight:700; color:var(--mo-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                            <i class="bi bi-search me-1"></i>Asignar Nuevo Repuesto:
                        </span>
                        <input type="hidden" id="rep-inv-${Number(o.id)}" value="">
                        <input type="text" class="rep-input" id="rep-inv-query-${Number(o.id)}" placeholder="Buscar repuesto por codigo o nombre..." autocomplete="off"
                               value=""
                               oninput="onInputBuscarRepuestoMisOrdenes(${Number(o.id)}, this.value)"
                               onfocus="buscarRepuestoMisOrdenes(${Number(o.id)}, this.value)">
                        <div class="rep-resultados" id="rep-inv-resultados-${Number(o.id)}"></div>
                        <div class="rep-seleccionado" id="rep-inv-selected-${Number(o.id)}" style="display:none; align-items:center; gap:8px; margin-bottom:8px;">
                            <span id="rep-inv-selected-text-${Number(o.id)}" style="flex-grow:1;">Repuesto seleccionado</span>
                            <div style="display:flex; align-items:center; gap:4px;">
                                <label style="font-size:11px; font-weight:bold; color:var(--mo-muted); margin:0;">Cant:</label>
                                <input type="number" id="rep-inv-qty-${Number(o.id)}" min="1" value="1" style="width:60px; height:26px; font-size:12px; padding:2px 5px; border:1px solid var(--mo-border); border-radius:4px;">
                            </div>
                            <button type="button" class="rep-clear" onclick="limpiarRepuestoSeleccionadoMisOrdenes(${Number(o.id)})" title="Quitar seleccion" style="margin-left:4px;">&times;</button>
                        </div>
                    </div>
                    
                    <div class="gestion-actions-rep">
                        <button type="button" class="btn-mini-rep" onclick="asignarRepuesto(${Number(o.id)}, 'personal')">Asignar repuesto</button>
                        <button type="button" class="btn-mini-rep danger" onclick="revertirRepuesto(${Number(o.id)}, 'personal')">Revertir todos</button>
                    </div>
                </div>

                <div class="gestion-actions-rep" style="margin-top:12px;">
                    <button type="button" class="btn-mini-rep" onclick="abrirInformeDeOrden(${Number(o.id)})"><i class="bi bi-pencil-square me-1"></i>Gestionar informe</button>
                    <button type="button" class="btn-mini-rep" onclick="verPdfInforme(${Number(o.id)})"><i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF informe</button>
                    <button type="button" class="btn-mini-rep violet" onclick="registrarLlamadaCliente(${Number(o.id)}, 'personal')"><i class="bi bi-telephone-plus me-1"></i>Llamada cliente</button>
                    <button type="button" class="btn-mini-rep" onclick="abrirModalEnviarEmail(${Number(o.id)}, 'personal')"><i class="bi bi-envelope me-1"></i>Enviar email</button>
                </div>

                ${(o.estado_orden === 'Nota de Credito' && (o.motivo_ingreso || '') === 'Validacion de Garantia' && o.nc_estado === 'Aprobada') ? `
                    ${!o.transferencia_numero ? `
                        <div style="background:#fff3cd; border:1px solid #ffeeba; border-radius:10px; padding:12px; margin-top:14px; margin-bottom:12px;">
                            <h5 style="color:#856404; font-size:12px; font-weight:700; margin:0 0 6px;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Transferencia de Inventario Requerida</h5>
                            <p style="font-size:11.5px; color:#856404; margin:0 0 10px;">La Nota de Crédito está aprobada. Registra la transferencia de inventario para cerrar la orden:</p>
                            <div style="margin-bottom:8px;">
                                <select id="det-plataforma" style="width:100%; border:1.5px solid #ffeeba; border-radius:6px; padding:6px; font-size:12px; background:#fff;">
                                    <option value="MBA3">MBA3</option>
                                    <option value="Milenium">Milenium</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                            <div style="margin-bottom:10px;">
                                <input type="text" id="det-numero" placeholder="Nro. transferencia de inventario..." style="width:100%; border:1.5px solid #ffeeba; border-radius:6px; padding:6px; font-size:12px; box-sizing:border-box;">
                            </div>
                            <button type="button" class="btn-mini-rep" style="width:100%; font-weight:700; text-align:center; padding:6px 0; background:#d97706; color:#fff;" onclick="registrarTransferenciaDetalle(${Number(o.id)})">
                                Registrar y Cerrar
                            </button>
                        </div>
                    ` : `
                        <div style="background:#d4edda; border:1px solid #c3e6cb; border-radius:10px; padding:12px; margin-top:14px; margin-bottom:12px; color:#155724;">
                            <h5 style="font-size:12px; font-weight:700; margin:0 0 4px;"><i class="bi bi-check-circle-fill me-1"></i>Orden Cerrada por NC</h5>
                            <p style="font-size:11.5px; margin:0;">
                                <b>Plataforma:</b> ${_h(o.transferencia_plataforma)} <br>
                                <b>Transf. Inventario:</b> ${_h(o.transferencia_numero)}
                            </p>
                        </div>
                    `}
                ` : ''}

                <div class="llamadas-section" style="margin-top: 14px; border-top: 1px dashed var(--mo-border); padding-top: 12px;">
                    <span style="font-size:11px; font-weight:700; color:var(--mo-muted); text-transform:uppercase; display:block; margin-bottom:8px;">
                        <i class="bi bi-clock-history me-1"></i>Historial de Llamadas (${o.llamadas ? o.llamadas.length : 0}):
                    </span>
                    ${o.llamadas && o.llamadas.length > 0 ? `
                        <div class="llamadas-timeline" style="display: flex; flex-direction: column; gap: 8px; max-height: 150px; overflow-y: auto; padding-right: 4px;">
                            ${o.llamadas.map(ll => `
                                <div style="background: #f8fafc; border: 1px solid var(--mo-border); border-radius: 8px; padding: 8px 10px; font-size: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 3px; font-weight: 600; color: var(--mo-muted); font-size: 10.5px;">
                                        <span><i class="bi bi-person me-1"></i>${_h(ll.usuario_nombre)}</span>
                                        <span><i class="bi bi-calendar3 me-1"></i>${_h(ll.fecha_hora)}</span>
                                    </div>
                                    <div style="color: var(--mo-slate); line-height: 1.3;">
                                        ${_h(ll.observacion)}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <p style="margin:0; font-size:12px; color:var(--mo-muted); font-style:italic;">No se han registrado llamadas para esta orden.</p>
                    `}
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
    // Backdrop click disabled - use close button
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

var _moPager = null;
document.addEventListener('DOMContentLoaded', () => {
    filtrarOrdenes('Pendiente');
    _moPager = new SgnPager({
        containerSelector: '#cards-grid-principal',
        itemSelector: '.orden-card',
        pagerContainerSelector: '#mo-pager',
        pageSize: 12
    });
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarCreds();
        cerrarModal();
        cerrarModalNC();
        cerrarModalSR();
        cerrarAlerta(false);
    }
});
document.addEventListener('click', (e) => {
    if (!(e.target instanceof Element)) return;
    if (e.target.closest('.rep-picker')) return;
    document.querySelectorAll('.rep-resultados').forEach((el) => {
        el.style.display = 'none';
    });
});

async function registrarLlamadaCliente(ordenId, tipoOrden) {
    const { value: observacion } = await Swal.fire({
        title: 'Registrar Llamada a Cliente',
        input: 'textarea',
        inputLabel: 'Observación de la llamada (opcional)',
        inputPlaceholder: 'Escribe detalles de la conversación, coordinación de entrega, etc...',
        inputAttributes: {
            'maxlength': 1000,
            'autocapitalize': 'off',
            'autocorrect': 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#64748b',
        allowOutsideClick: false,
    });

    if (observacion === undefined) return;

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    if (tipoOrden === 'empresa') {
        fd.append('orden_empresa_id', ordenId);
    } else {
        fd.append('orden_id', ordenId);
    }
    fd.append('observacion', observacion);

    try {
        Swal.fire({
            title: 'Registrando...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        });

        const r = await fetch(_moUrlRegistrarLlamada, { method: 'POST', body: fd });
        const d = await r.json();
        
        if (!d.ok) {
            Swal.fire('Error', d.error || 'No se pudo registrar la llamada.', 'error');
            return;
        }

        const row = _moFindRow(ordenId, tipoOrden);
        if (row) {
            row.llamadas = row.llamadas || [];
            row.llamadas.unshift(d.llamada);
            _moAplicarCambioLocal(row);
        }

        Swal.fire({
            icon: 'success',
            title: 'Llamada Registrada',
            text: d.mensaje || 'Se ha registrado la llamada correctamente.',
            confirmButtonColor: '#7c3aed',
            timer: 2000
        });

    } catch (e) {
        Swal.fire('Error', 'Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error');
    }
}

async function abrirModalEnviarEmail(ordenId, tipoOrden) {
    const o = _moFindRow(ordenId, tipoOrden);
    if (!o) return;

    if (!o.correo || o.correo.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente sin Correo',
            text: 'Esta orden no tiene un correo electrónico registrado para el cliente.',
            confirmButtonColor: '#3b82f6',
        });
        return;
    }

    const { value: formValues } = await Swal.fire({
        title: 'Enviar Email al Cliente',
        html: `
            <div style="text-align: left; margin-bottom: 12px;">
                <label style="font-weight: 700; font-size: 13px; color: #475569; display: block; margin-bottom: 4px;">Enviar a:</label>
                <input id="swal-email-dest" type="text" class="swal2-input" style="width: 100%; margin: 0; box-sizing: border-box; font-size: 14px;" value="${_h(o.correo)}" disabled>
            </div>
            <div style="text-align: left; margin-bottom: 12px;">
                <label style="font-weight: 700; font-size: 13px; color: #475569; display: block; margin-bottom: 4px;">Asunto <span style="color: #ef4444;">*</span></label>
                <input id="swal-email-asunto" type="text" class="swal2-input" style="width: 100%; margin: 0; box-sizing: border-box; font-size: 14px;" value="Novedades sobre su orden ${_h(o.nro_orden)}">
            </div>
            <div style="text-align: left;">
                <label style="font-weight: 700; font-size: 13px; color: #475569; display: block; margin-bottom: 4px;">Mensaje <span style="color: #ef4444;">*</span></label>
                <textarea id="swal-email-mensaje" class="swal2-textarea" style="width: 100%; height: 160px; margin: 0; box-sizing: border-box; font-size: 14px; resize: vertical;" placeholder="Escribe el cuerpo del correo electrónico aquí..."></textarea>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Enviar Email',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        allowOutsideClick: false,
        preConfirm: () => {
            const asunto = document.getElementById('swal-email-asunto').value.trim();
            const mensaje = document.getElementById('swal-email-mensaje').value.trim();
            if (!asunto) {
                Swal.showValidationMessage('El asunto es obligatorio.');
                return false;
            }
            if (!mensaje) {
                Swal.showValidationMessage('El mensaje es obligatorio.');
                return false;
            }
            return { asunto, mensaje };
        }
    });

    if (!formValues) return;

    const fd = new FormData();
    fd.append('_token', _moCsrf);
    if (tipoOrden === 'empresa') {
        fd.append('orden_empresa_id', ordenId);
    } else {
        fd.append('orden_id', ordenId);
    }
    fd.append('asunto', formValues.asunto);
    fd.append('contenido', formValues.mensaje);

    try {
        Swal.fire({
            title: 'Enviando Correo...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        });

        const r = await fetch(_moUrlEnviarEmail, { method: 'POST', body: fd });
        const d = await r.json();
        
        if (!d.ok) {
            Swal.fire('Error', d.error || 'No se pudo enviar el correo.', 'error');
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Correo Enviado',
            text: d.mensaje || 'Se ha enviado el correo correctamente.',
            confirmButtonColor: '#2563eb',
            timer: 2000
        });

    } catch (e) {
        Swal.fire('Error', 'Error de conexión con el servidor. Inténtalo de nuevo más tarde.', 'error');
    }
}

// Transferencia y cierre condicional para Notas de Crédito
function verificarTransferenciasPendientes() {
    if (typeof _moRows === 'undefined') return;
    const ordenPendiente = _moRows.find(o => 
        o.estado_orden === 'Nota de Credito' &&
        o.nc_estado === 'Aprobada' &&
        (o.motivo_ingreso || '') === 'Validacion de Garantia' &&
        !o.transferencia_numero &&
        localStorage.getItem('dismissed_nc_transfer_' + o.id) !== 'true'
    );

    if (ordenPendiente) {
        abrirPopupTransferencia(ordenPendiente);
    }
}

function abrirPopupTransferencia(o) {
    localStorage.setItem('dismissed_nc_transfer_' + o.id, 'true');
    Swal.fire({
        title: 'Nota de Crédito Aprobada',
        html: `
            <p style="font-size:14px; text-align:left; color:#4b5563; margin-bottom:15px; line-height:1.4;">
                Tu nota de crédito para la orden <b>${_h(o.nro_orden)}</b> ha sido aprobada. 
                <br>Para poder cerrar la orden, debes ingresar el número de transferencia de inventario:
            </p>
            <div style="text-align:left; margin-bottom:12px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:4px;">Plataforma</label>
                <select id="swal-plataforma" class="swal2-select" style="width:100%; margin:0; display:block; box-sizing:border-box; font-size:14px; padding:8px; border-radius:6px; border:1px solid #d1d5db; background:#fff;">
                    <option value="MBA3">MBA3</option>
                    <option value="Milenium">Milenium</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            <div id="swal-otro-plataforma-wrapper" style="text-align:left; margin-bottom:12px; display: none;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:4px;">Especifique la Plataforma</label>
                <input id="swal-plataforma-otro" class="swal2-input" placeholder="Ej: Banco Pichincha, etc..." style="width:100%; margin:0; display:block; box-sizing:border-box; font-size:14px; padding:8px; border-radius:6px; border:1px solid #d1d5db;">
            </div>
            <div style="text-align:left; margin-bottom:15px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:4px;">Número de Transferencia de Inventario</label>
                <input id="swal-numero" class="swal2-input" placeholder="Ingrese el número de transferencia (solo números)..." style="width:100%; margin:0; display:block; box-sizing:border-box; font-size:14px; padding:8px; border-radius:6px; border:1px solid #d1d5db;">
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Registrar y Cerrar Orden',
        cancelButtonText: 'Aún no está lista la transferencia',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            const select = document.getElementById('swal-plataforma');
            const wrapper = document.getElementById('swal-otro-plataforma-wrapper');
            select.addEventListener('change', (e) => {
                if (e.target.value === 'Otros') {
                    wrapper.style.display = 'block';
                } else {
                    wrapper.style.display = 'none';
                }
            });
        },
        preConfirm: () => {
            const selectPlat = document.getElementById('swal-plataforma').value;
            let plataforma = selectPlat;
            if (selectPlat === 'Otros') {
                plataforma = document.getElementById('swal-plataforma-otro').value.trim();
                if (!plataforma) {
                    Swal.showValidationMessage('Por favor especifique la plataforma');
                    return false;
                }
            }
            const numero = document.getElementById('swal-numero').value.trim();
            if (!numero) {
                Swal.showValidationMessage('Por favor ingrese el número de transferencia de inventario');
                return false;
            }
            if (!/^\d+$/.test(numero)) {
                Swal.showValidationMessage('El número de transferencia solo debe contener números');
                return false;
            }
            return { plataforma, numero };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            guardarTransferenciaAjax(o.id, result.value.plataforma, result.value.numero);
        } else {
            localStorage.setItem('dismissed_nc_transfer_' + o.id, 'true');
            if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    title: 'Información',
                    html: 'Puedes ingresar este número más tarde desde el panel de <b>Gestionar Orden</b> de esta orden.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
            }
        }
    });
}

async function guardarTransferenciaAjax(ordenId, plataforma, numero) {
    try {
        Swal.fire({
            title: 'Procesando...',
            text: 'Registrando transferencia y cerrando orden',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const res = await fetch('{{ route("mis_ordenes.registrar_transferencia") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _moCsrf
            },
            body: JSON.stringify({
                orden_id: ordenId,
                plataforma: plataforma,
                numero: numero
            })
        });

        const d = await res.json();
        if (d.ok) {
            const row = _moRows.find(x => Number(x.id) === Number(ordenId));
            if (row) {
                row.transferencia_plataforma = plataforma;
                row.transferencia_numero = numero;
                row.fecha_finalizacion = d.fecha_finalizacion;
                _moAplicarCambioLocal(row);
            }
            await Swal.fire({
                title: '¡Completado!',
                text: d.mensaje || 'Transferencia registrada correctamente.',
                icon: 'success'
            });
            window.location.reload();
        } else {
            throw new Error(d.error || 'Ocurrió un error inesperado');
        }
    } catch (e) {
        Swal.fire('Error', e.message || 'No se pudo guardar la transferencia.', 'error');
    }
}

function registrarTransferenciaDetalle(ordenId) {
    const plataforma = document.getElementById('det-plataforma').value;
    const numero = document.getElementById('det-numero').value.trim();
    if (!numero) {
        mostrarAlertaEstetica('Por favor, ingresa el número de transferencia de inventario.', 'warning', 'Número Requerido');
        return;
    }
    guardarTransferenciaAjax(ordenId, plataforma, numero);
}

async function abrirModalInventarioFisico(ordenId) {
    Swal.fire({
        title: 'Cargando información...',
        text: 'Obteniendo los productos del inventario físico.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    try {
        const response = await fetch(`${_moUrlInvFisicoObtener}/${ordenId}`);
        const data = await response.json();
        Swal.close();

        if (!data.ok || !data.productos || data.productos.length === 0) {
            Swal.fire('Información', 'No se encontraron productos registrados en el inventario físico para esta orden.', 'info');
            return;
        }

        let html = `
        <div style="text-align:left; font-size:13px; max-height: 400px; overflow-y: auto;">
            <p class="text-muted mb-3">Establece el estado físico de cada equipo en la oficina.</p>
            <table class="table table-sm align-middle table-borderless" style="width:100%;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0; color:#475569; font-weight:700;">
                        <th style="padding:6px 0;">Serie</th>
                        <th style="padding:6px 0;">Producto</th>
                        <th style="padding:6px 0; width: 180px;">Estado Físico</th>
                    </tr>
                </thead>
                <tbody>
        `;

        data.productos.forEach(p => {
            html += `
            <tr style="border-bottom:1px solid #f1f5f9;" class="prod-inv-row" data-id="${p.id}">
                <td style="padding:8px 0; font-family:monospace; font-weight:600; color:#1e293b;">${_h(p.serie)}</td>
                <td style="padding:8px 0; color:#334155; font-size:12px;">${_h(p.nombre)}</td>
                <td style="padding:8px 0; width: 180px;">
                    <select class="form-select form-select-sm select-estado-fisico" style="border-radius:6px; font-size:12px;" onchange="toggleDetalleOutletRow(${p.id}, this.value)">
                        <option value="Tienda" ${p.estado === 'Tienda' ? 'selected' : ''}>Tienda (Operativo)</option>
                        <option value="Incinerox" ${p.estado === 'Incinerox' ? 'selected' : ''}>Incinerox (Incinerar)</option>
                        <option value="Outlet" ${p.estado === 'Outlet' ? 'selected' : ''}>Outlet (Con Detalle)</option>
                    </select>
                </td>
            </tr>
            <tr id="row-detalle-${p.id}" style="display:${p.estado === 'Outlet' ? 'table-row' : 'none'}; border-bottom:1px solid #f1f5f9;">
                <td colspan="3" style="padding: 4px 0 8px 0;">
                    <input type="text" class="form-control form-control-sm input-detalle-outlet" id="detalle-${p.id}" placeholder="Describa el detalle de outlet..." value="${_h(p.detalle_outlet || '')}" style="border-radius:6px; font-size:12px;">
                </td>
            </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        </div>
        `;

        Swal.fire({
            title: '<span style="font-size:16px; font-weight:700;"><i class="bi bi-box-seam text-teal me-2"></i>Inventario Físico Servicio Técnico</span>',
            html: html,
            width: '650px',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-save me-1"></i>Guardar Cambios',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0f766e',
            cancelButtonColor: '#64748b',
            allowOutsideClick: false,
            preConfirm: () => {
                const productos = [];
                const rows = document.querySelectorAll('.prod-inv-row');
                rows.forEach(row => {
                    const id = row.getAttribute('data-id');
                    const estadoEl = row.querySelector('.select-estado-fisico');
                    const estado = estadoEl ? estadoEl.value : 'Tienda';
                    const inputDet = row.nextElementSibling ? row.nextElementSibling.querySelector('.input-detalle-outlet') : null;
                    const detalle_outlet = inputDet ? inputDet.value.trim() : '';
                    productos.push({ id: parseInt(id), estado, detalle_outlet });
                });
                return productos;
            }
        }).then(async (result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const saveRes = await fetch(_moUrlInvFisicoGuardar, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _moCsrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        orden_empresa_id: ordenId,
                        productos: result.value
                    })
                });

                const saveResult = await saveRes.json().catch(() => null);
                Swal.close();

                if (saveRes.ok && saveResult && saveResult.ok) {
                    Swal.fire('¡Guardado!', saveResult.mensaje || 'Estados físicos actualizados.', 'success');
                } else {
                    const errText = (saveResult && (saveResult.error || saveResult.mensaje)) ? (saveResult.error || saveResult.mensaje) : `Error en servidor (${saveRes.status})`;
                    Swal.fire('Error', errText, 'error');
                }
            }
        });

    } catch (e) {
        Swal.close();
        Swal.fire('Error', 'No se pudo conectar con el servidor: ' + e.message, 'error');
    }
}

function toggleDetalleOutletRow(id, valor) {
    const row = document.getElementById(`row-detalle-${id}`);
    if (row) {
        if (valor === 'Outlet') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
            const detInp = document.getElementById(`detalle-${id}`);
            if (detInp) detInp.value = '';
        }
    }
}

async function cambiarEstadoFisicoDirecto(ordenId, productoId, nuevoEstado) {
    const feedback = document.getElementById('feedback-fisico-' + productoId);
    if (feedback) feedback.classList.add('loading');

    const containerDetalle = document.getElementById('container-detalle-' + productoId);
    if (containerDetalle) {
        if (nuevoEstado === 'Outlet') {
            containerDetalle.style.display = 'block';
        } else {
            containerDetalle.style.display = 'none';
        }
    }

    const inputDet = document.getElementById('input-detalle-' + productoId);
    const detalleVal = (nuevoEstado === 'Outlet' && inputDet) ? inputDet.value.trim() : null;
    const csrfToken = _moCsrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const response = await fetch(_moUrlInvFisicoGuardar, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                orden_empresa_id: ordenId,
                productos: [
                    {
                        id: productoId,
                        estado: nuevoEstado,
                        detalle_outlet: detalleVal
                    }
                ]
            })
        });

        const res = await response.json().catch(() => null);
        if (!response.ok || !res || !res.ok) {
            const errText = (res && (res.error || res.mensaje)) ? (res.error || res.mensaje) : `Error en el servidor (${response.status})`;
            await mostrarAlertaEstetica(errText, 'error', 'Error');
            return;
        }

        const row = _moRows.find(x => Number(x.id) === Number(ordenId));
        if (row && row.productos_inventario_st) {
            const p = row.productos_inventario_st.find(x => Number(x.id) === Number(productoId));
            if (p) {
                p.estado = nuevoEstado;
                if (nuevoEstado !== 'Outlet') {
                    p.detalle_outlet = '';
                    if (inputDet) inputDet.value = '';
                }
            }
            const cardEl = document.getElementById('card-empresa-' + ordenId);
            if (cardEl) {
                cardEl.setAttribute('data-orden', JSON.stringify(row));
            }
        }
        await mostrarAlertaEstetica('Estado físico actualizado correctamente.', 'success', 'Completado');
    } catch (e) {
        await mostrarAlertaEstetica('Error al comunicarse con el servidor: ' + e.message, 'error', 'Error');
    } finally {
        if (feedback) feedback.classList.remove('loading');
    }
}

async function guardarDetalleOutletDirecto(ordenId, productoId) {
    const feedback = document.getElementById('feedback-fisico-' + productoId);
    if (feedback) feedback.classList.add('loading');

    const inputDet = document.getElementById('input-detalle-' + productoId);
    const detalle = inputDet ? inputDet.value.trim() : '';
    const csrfToken = _moCsrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const response = await fetch(_moUrlInvFisicoGuardar, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                orden_empresa_id: ordenId,
                productos: [
                    {
                        id: productoId,
                        estado: 'Outlet',
                        detalle_outlet: detalle
                    }
                ]
            })
        });

        const res = await response.json().catch(() => null);
        if (!response.ok || !res || !res.ok) {
            const errText = (res && (res.error || res.mensaje)) ? (res.error || res.mensaje) : `Error en el servidor (${response.status})`;
            await mostrarAlertaEstetica(errText, 'error', 'Error');
            return;
        }

        const row = _moRows.find(x => Number(x.id) === Number(ordenId));
        if (row && row.productos_inventario_st) {
            const p = row.productos_inventario_st.find(x => Number(x.id) === Number(productoId));
            if (p) {
                p.detalle_outlet = detalle;
            }
            const cardEl = document.getElementById('card-empresa-' + ordenId);
            if (cardEl) {
                cardEl.setAttribute('data-orden', JSON.stringify(row));
            }
        }
        await mostrarAlertaEstetica('Detalle de Outlet guardado correctamente.', 'success', 'Guardado');
    } catch (e) {
        await mostrarAlertaEstetica('Error de conexión.', 'error', 'Error');
    } finally {
        if (feedback) feedback.classList.remove('loading');
    }
}
</script>
@endpush
