@extends('layouts.app')
@section('titulo', 'Auditoría de Repuestos')

@push('css_adicional')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    /* •••••••••••••••••••••••••••••••••••••••••••••••••••
       AUDITORÍA DE INVENTARIO — SGN Premium Theme
    ••••••••••••••••••••••••••••••••••••••••••••••••••• */
    .aud-wrap { max-width: 1420px; margin: 0 auto; padding: 24px 20px; font-family: 'Inter', system-ui, sans-serif; }
    
    .aud-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
    .aud-hdr-text h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .aud-hdr-text p { margin: 0; color: #64748b; font-size: 14px; }
    
    .btn-back { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; padding: 8px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
    .btn-back:hover { background: #e2e8f0; color: #0f172a; }

    /* Ã¢”â‚¬Ã¢”â‚¬ KPIs Ã¢”â‚¬Ã¢”â‚¬ */
    .aud-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .aud-kpi { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; border-top: 4px solid transparent; padding: 18px 16px; text-align: center; box-shadow: 0 1px 6px rgba(0,0,0,.04); transition: box-shadow .2s, transform .2s; }
    .aud-kpi:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); transform: translateY(-2px); }
    .aud-kpi i { font-size: 24px; display: block; margin-bottom: 6px; }
    .aud-kpi-val { font-size: 26px; font-weight: 900; color: #0f172a; line-height: 1.1; word-break: break-all; }
    .aud-kpi-lbl { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-top: 5px; }
    .aud-kpi.c-indigo { border-top-color: #6366f1; } .aud-kpi.c-indigo i { color: #6366f1; }
    .aud-kpi.c-green { border-top-color: #10b981; } .aud-kpi.c-green i { color: #10b981; }
    .aud-kpi.c-amber { border-top-color: #f59e0b; } .aud-kpi.c-amber i { color: #f59e0b; }
    .aud-kpi.c-blue { border-top-color: #3b82f6; } .aud-kpi.c-blue i { color: #3b82f6; }

    /* Ã¢”â‚¬Ã¢”â‚¬ Card & Filtros Ã¢”â‚¬Ã¢”â‚¬ */
    .aud-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 16px rgba(0,0,0,.04); margin-bottom: 24px; overflow: hidden; }
    .aud-card-head { display: flex; align-items: center; gap: 8px; padding: 14px 20px; background: linear-gradient(135deg,#f8fafc,#f1f5f9); border-bottom: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: .05em; }
    .ch-right { margin-left: auto; display: flex; gap: 8px; align-items: center; }

    .aud-filtros-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; padding: 20px 20px 0; }
    .aud-campo { display: flex; flex-direction: column; gap: 6px; }
    .aud-campo label { font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .05em; }
    .aud-campo select, .aud-campo input[type=date] { border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 13.5px; color: #0f172a; background: #fff; transition: border-color .2s, box-shadow .2s; outline:none; }
    .aud-campo select:focus, .aud-campo input[type=date]:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
    
    .aud-btns-row { display: flex; gap: 10px; padding: 16px 20px 20px; flex-wrap: wrap; align-items: center; border-bottom: 1px dashed #e2e8f0; }
    .btn-aud { display: inline-flex; align-items: center; gap: 6px; border: none; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .15s; white-space: nowrap; text-decoration: none; }
    .btn-aud:hover { transform: translateY(-1px); }
    .btn-aud:active { transform: translateY(0); }
    .btn-aud-primary { background: linear-gradient(135deg,#4f46e5,#4338ca); color: #fff; box-shadow: 0 3px 12px rgba(79,70,229,.3); }
    .btn-aud-primary:hover { background: #4338ca; box-shadow: 0 5px 16px rgba(79,70,229,.4); }
    .btn-aud-ghost { background: #f8fafc; color: #475569; border: 1.5px solid #cbd5e1; }
    .btn-aud-ghost:hover { background: #f1f5f9; color: #0f172a; }
    .btn-aud-green { background: linear-gradient(135deg,#10b981,#059669); color: #fff; }
    .btn-aud-dark { background: #0f172a; color: #fff; }
    .btn-aud-dark:hover { background: #1e293b; }
    
    .input-search-box { border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; width: 230px; font-family: inherit; transition: border-color .2s; outline: none; }
    .input-search-box:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); }

    /* Ã¢”â‚¬Ã¢”â‚¬ Tabla Ã¢”â‚¬Ã¢”â‚¬ */
    .aud-tbl-outer { overflow-x: auto; }
    .aud-tbl { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .aud-tbl th { background: #f8fafc; padding: 12px 16px; font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; border-bottom: 2px solid #e2e8f0; white-space: nowrap; cursor: pointer; user-select: none; }
    .aud-tbl th:hover { background: #f1f5f9; color: #4f46e5; }
    .aud-tbl td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
    .aud-tbl tr:last-child td { border-bottom: none; }
    .aud-tbl tr:hover td { background: #f8fbff; }
    
    .aud-code { font-family: monospace; font-weight: 700; color: #b45309; font-size: 13px; }
    .aud-nro-orden { font-family: monospace; font-weight: 800; color: #4f46e5; text-decoration: none; }
    .aud-nro-orden:hover { text-decoration: underline; }
    
    .aud-pagination { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; border-top: 1px solid #f1f5f9; font-size: 12.5px; color: #64748b; flex-wrap: wrap; gap: 8px; }
    .aud-pag-btns { display: flex; gap: 4px; }
    .aud-pag-btn { border: 1.5px solid #cbd5e1; background: #fff; color: #475569; border-radius: 6px; padding: 5px 12px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .aud-pag-btn:hover, .aud-pag-btn.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
    .aud-pag-btn:disabled { opacity: .4; cursor: not-allowed; }

    .aud-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px; color: #94a3b8; text-align: center; gap: 10px; }
    .aud-empty i { font-size: 48px; }
    .aud-empty h4 { font-size: 16px; font-weight: 700; color: #64748b; margin: 0; }
    .aud-empty p { font-size: 13px; margin: 0; }

    /* Ã¢”â‚¬Ã¢”â‚¬ IMPRESIÃƒ“N / PRINT CSS Ã¢”â‚¬Ã¢”â‚¬ */
    @media print {
        header, footer, nav, aside, .btn-back, .aud-card-head, .aud-filtros-grid, .aud-btns-row, .aud-pagination, #buscador-container {
            display: none !important;
        }
        body, .aud-wrap {
            background: #fff !important;
            color: #000 !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 11px !important;
        }
        .aud-wrap {
            max-width: 100% !important;
        }
        .aud-hdr {
            border-bottom: 2px solid #000 !important;
            margin-bottom: 15px !important;
            padding-bottom: 8px !important;
        }
        .aud-kpis {
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 10px !important;
            margin-bottom: 15px !important;
        }
        .aud-kpi {
            border: 1px solid #000 !important;
            padding: 10px !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            transform: none !important;
            background: #fcfcfc !important;
        }
        .aud-kpi i {
            display: none !important;
        }
        .aud-kpi-val {
            font-size: 18px !important;
        }
        .aud-card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            border-radius: 8px !important;
        }
        .aud-tbl {
            font-size: 10px !important;
        }
        .aud-tbl th {
            background: #f0f0f0 !important;
            color: #000 !important;
            border-bottom: 1.5px solid #000 !important;
            padding: 6px 8px !important;
        }
        .aud-tbl td {
            padding: 6px 8px !important;
            border-bottom: 1px solid #ccc !important;
        }
        .aud-nro-orden {
            color: #000 !important;
            font-weight: 700 !important;
        }
    }
</style>
@endpush

@section('contenido')
<div class="aud-wrap">

    {{-- Encabezado --}}
    <div class="aud-hdr">
        <div class="aud-hdr-text">
            <h2><i class="bi bi-shield-check" style="color:#4f46e5;"></i> Auditoría de Repuestos</h2>
            <p>Monitoreo detallado de stock restado de bodega, asignaciones y consumo en órdenes de servicio.</p>
        </div>
        <div>
            <a href="{{ route('repuestos.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Catálogo de Repuestos
            </a>
        </div>
    </div>

    {{-- PHP de Cálculo de Métricas KPIs --}}
    @php
        $totalItems = $auditorias->sum('cantidad');
        $totalCosto = $auditorias->sum(fn($a) => ($a->repuesto->costo ?? 0) * $a->cantidad);
        
        // Repuesto más usado
        $repuestoMasUsado = 'Ninguno';
        $repuestoMasUsadoCant = 0;
        $agrupadoRep = $auditorias->groupBy('repuesto_id');
        if ($agrupadoRep->isNotEmpty()) {
            $maxRep = $agrupadoRep->map->sum('cantidad')->sortDesc();
            $maxId = $maxRep->keys()->first();
            $repMas = $auditorias->firstWhere('repuesto_id', $maxId);
            if ($repMas && $repMas->repuesto) {
                $repuestoMasUsado = $repMas->repuesto->nombre;
                $repuestoMasUsadoCant = $maxRep->first();
            }
        }

        // Técnico con más consumo
        $tecnicoLider = 'Ninguno';
        $tecnicoLiderCant = 0;
        $agrupadoTec = $auditorias->groupBy(fn($a) => $a->usuario_id ?: ($a->orden->tecnico_id ?? $a->ordenEmpresa->tecnico_id ?? 0));
        if ($agrupadoTec->isNotEmpty()) {
            $maxTec = $agrupadoTec->map->sum('cantidad')->sortDesc();
            $tecId = $maxTec->keys()->first();
            $tecMas = $auditorias->first(fn($a) => ($a->usuario_id ?: ($a->orden->tecnico_id ?? $a->ordenEmpresa->tecnico_id ?? 0)) == $tecId);
            if ($tecMas) {
                $tecnicoLider = $tecMas->usuario->nombre_tecnico ?? $tecMas->orden->tecnico->nombre_tecnico ?? $tecMas->ordenEmpresa->tecnico->nombre_tecnico ?? 'N/A';
                $tecnicoLiderCant = $maxTec->first();
            }
        }
    @endphp

    {{-- KPIs Dashboard --}}
    <div class="aud-kpis">
        <div class="aud-kpi c-indigo">
            <i class="bi bi-boxes"></i>
            <div class="aud-kpi-val">{{ $totalItems }} uds</div>
            <div class="aud-kpi-lbl">Repuestos Utilizados</div>
        </div>
        <div class="aud-kpi c-green">
            <i class="bi bi-currency-dollar"></i>
            <div class="aud-kpi-val">${{ number_format($totalCosto, 2) }}</div>
            <div class="aud-kpi-lbl">Costo Total de Salidas</div>
        </div>
        <div class="aud-kpi c-amber">
            <i class="bi bi-star"></i>
            <div class="aud-kpi-val" style="font-size:15px; font-weight:800; padding:4px 0;" title="{{ $repuestoMasUsado }}">
                {{ strlen($repuestoMasUsado) > 28 ? substr($repuestoMasUsado, 0, 26) . '...' : $repuestoMasUsado }}
            </div>
            <div class="aud-kpi-lbl">Repuesto Más Usado ({{ $repuestoMasUsadoCant }} uds)</div>
        </div>
        <div class="aud-kpi c-blue">
            <i class="bi bi-person-check"></i>
            <div class="aud-kpi-val" style="font-size:15px; font-weight:800; padding:4px 0;" title="{{ $tecnicoLider }}">
                {{ strlen($tecnicoLider) > 28 ? substr($tecnicoLider, 0, 26) . '...' : $tecnicoLider }}
            </div>
            <div class="aud-kpi-lbl">Técnico Más Activo ({{ $tecnicoLiderCant }} uds)</div>
        </div>
    </div>

    {{-- Tarjeta de Filtros --}}
    <div class="aud-card">
        <div class="aud-card-head">
            <i class="bi bi-funnel"></i> Filtros de Auditoría
            <div class="ch-right">
                <a href="{{ route('repuestos.auditoria') }}" class="btn-aud btn-aud-sm btn-aud-ghost" style="padding: 5px 12px; font-size:11.5px;">
                    <i class="bi bi-x-circle"></i> Limpiar Filtros
                </a>
            </div>
        </div>
        <form method="GET" action="{{ route('repuestos.auditoria') }}">
            <div class="aud-filtros-grid">
                <div class="aud-campo">
                    <label>Repuesto Específico</label>
                    <select name="repuesto_id" onchange="this.form.submit()">
                        <option value="">-- Todos los Repuestos --</option>
                        @foreach($repuestosList as $rl)
                            <option value="{{ $rl->id }}" {{ request('repuesto_id') == $rl->id ? 'selected' : '' }}>
                                {{ $rl->codigo }} - {{ $rl->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="aud-campo">
                    <label>Técnico / Responsable</label>
                    <select name="usuario_id" onchange="this.form.submit()">
                        <option value="">-- Todos los Técnicos --</option>
                        @foreach($tecnicosList as $tl)
                            <option value="{{ $tl->id }}" {{ request('usuario_id') == $tl->id ? 'selected' : '' }}>
                                {{ $tl->nombre_tecnico ?: $tl->usuario }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="aud-campo">
                    <label>Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" onchange="this.form.submit()">
                </div>
                <div class="aud-campo">
                    <label>Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" onchange="this.form.submit()">
                </div>
            </div>
            
            <div class="aud-btns-row">
                <button type="submit" class="btn-aud btn-aud-primary">
                    <i class="bi bi-funnel-fill"></i> Aplicar Filtros
                </button>
                <button type="button" class="btn-aud btn-aud-dark" onclick="abrirReporteImpresion()">
                    <i class="bi bi-printer-fill"></i> Imprimir Reporte (PDF)
                </button>
                <button type="button" class="btn-aud btn-aud-green" onclick="exportarExcel()">
                    <i class="bi bi-file-earmark-excel-fill"></i> Descargar XLSX
                </button>
                <button type="button" class="btn-aud btn-aud-ghost" onclick="exportarCSV()">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Descargar CSV
                </button>
            </div>
        </form>

        {{-- Resultados de la Grilla --}}
        @if($auditorias->isNotEmpty())
            <div class="aud-card-head" style="border-top: 1px solid #e2e8f0; border-bottom: 2px solid #e2e8f0;" id="buscador-container">
                <i class="bi bi-table"></i> Historial de Movimientos de Stock
                <div class="ch-right">
                    <input type="text" class="input-search-box" id="aud-buscador" placeholder="🔍 Buscar en tabla..." oninput="filtrarTablaLocal(this.value)">
                </div>
            </div>
            
            <div class="aud-tbl-outer">
                <table class="aud-tbl" id="aud-tabla">
                    <thead>
                        <tr>
                            <th onclick="sortTablaLocal(0, 'fecha')">Fecha / Hora</th>
                            <th onclick="sortTablaLocal(1, 'codigo')">Código</th>
                            <th onclick="sortTablaLocal(2, 'nombre')">Nombre del Repuesto</th>
                            <th onclick="sortTablaLocal(3, 'tecnico')">Usuario / Técnico</th>
                            <th onclick="sortTablaLocal(4, 'orden')">Orden Relacionada</th>
                            <th onclick="sortTablaLocal(5, 'tipo_orden')">Tipo de Orden</th>
                            <th style="text-align:center;" onclick="sortTablaLocal(6, 'cantidad')">Cant</th>
                            <th style="text-align:right;" onclick="sortTablaLocal(7, 'costo_u')">Costo Unit. ($)</th>
                            <th style="text-align:right;" onclick="sortTablaLocal(8, 'costo_t')">Costo Total ($)</th>
                        </tr>
                    </thead>
                    <tbody id="aud-tbody">
                        @foreach($auditorias as $a)
                            @php
                                $fechaHora = \Carbon\Carbon::parse($a->fecha)->format('d/m/Y H:i');
                                $tecnicoNombre = $a->usuario->nombre_tecnico ?? $a->orden->tecnico->nombre_tecnico ?? $a->ordenEmpresa->tecnico->nombre_tecnico ?? 'N/A';
                                $costoUnit = $a->repuesto->costo ?? 0;
                                $costoTotal = $costoUnit * $a->cantidad;
                                $tipoOrden = $a->orden 
                                    ? ($a->orden->motivo_ingreso ?? 'N/A') 
                                    : ($a->ordenEmpresa ? ('Empresa - ' . ($a->ordenEmpresa->subtipo ?? '')) : 'N/A');
                                
                                // Estilos dinámicos premium para el tipo de orden
                                $badgeStyle = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                                if ($tipoOrden === 'Servicio Cliente Externo') {
                                    $badgeStyle = 'background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;';
                                } elseif ($tipoOrden === 'Validacion de Garantia' || $tipoOrden === 'Validación de Garantía') {
                                    $badgeStyle = 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
                                } elseif ($tipoOrden === 'Servicios a Empresas') {
                                    $badgeStyle = 'background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff;';
                                } elseif (str_contains($tipoOrden, 'Empresa')) {
                                    $badgeStyle = 'background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff;';
                                }
                            @endphp
                            <tr data-row="auditoria" data-fila="{{ json_encode([
                                'fecha' => $a->fecha,
                                'codigo' => $a->repuesto->codigo ?? '',
                                'nombre' => $a->repuesto->nombre ?? '',
                                'tecnico' => $tecnicoNombre,
                                'orden' => $a->orden ? ($a->orden->nro_orden ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->nro_orden ?? '') : ''),
                                'tipo_orden' => $tipoOrden,
                                'cantidad' => $a->cantidad,
                                'costo_u' => $costoUnit,
                                'costo_t' => $costoTotal,
                                'fecha_ingreso_orden' => $a->orden ? ($a->orden->fecha_de_ingreso ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->fecha_ingreso ?? '') : ''),
                                'fecha_prometido_orden' => $a->orden ? ($a->orden->fecha_prometido ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->fecha_prometido ?? '') : ''),
                                'fecha_entrega_orden' => $a->orden ? ($a->orden->fecha_entrega ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->fecha_entrega ?? '') : ''),
                                'estado_orden' => $a->orden ? ($a->orden->estado_orden ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->estado ?? '') : ''),
                                'estado_repuesto_orden' => $a->orden ? ($a->orden->estado_repuesto ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->estado_repuesto ?? '') : ''),
                                'estado_garantia_orden' => $a->orden ? ($a->orden->estado_garantia ?? '') : '',
                                'factura' => $a->orden ? ($a->orden->nro_factura ?? '') : '',
                                'factura_2' => $a->orden ? ($a->orden->nro_factura_2 ?? '') : '',
                                'cliente' => $a->orden 
                                    ? trim((($a->orden->cliente->nombres ?? '') . ' ' . ($a->orden->cliente->apellidos ?? '')))
                                    : ($a->ordenEmpresa ? trim($a->ordenEmpresa->empresa->nombre ?? '') : ''),
                                'cliente_identificacion' => $a->orden ? ($a->orden->cliente->identificacion ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->empresa->ruc ?? '') : ''),
                                'cliente_telefono' => $a->orden ? ($a->orden->cliente->numero_contacto ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->empresa->telefono ?? '') : ''),
                                'cliente_correo' => $a->orden ? ($a->orden->cliente->correo ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->empresa->correo ?? '') : ''),
                                'cliente_direccion' => $a->orden ? ($a->orden->cliente->direccion_clientes ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->empresa->direccion_empresa ?? '') : ''),
                                'tipo_equipo' => $a->orden ? ($a->orden->equipo->tipo ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->equipo->tipo ?? '') : ''),
                                'equipo' => $a->orden
                                    ? trim((($a->orden->equipo->tipo ?? '') . ' ' . ($a->orden->equipo->marca ?? '') . ' ' . ($a->orden->equipo->modelo ?? '')))
                                    : ($a->ordenEmpresa ? trim((($a->ordenEmpresa->equipo->tipo ?? '') . ' ' . ($a->ordenEmpresa->equipo->marca ?? '') . ' ' . ($a->ordenEmpresa->equipo->modelo ?? ''))) : ''),
                                'marca' => $a->orden ? ($a->orden->equipo->marca ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->equipo->marca ?? '') : ''),
                                'modelo' => $a->orden ? ($a->orden->equipo->modelo ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->equipo->modelo ?? '') : ''),
                                'serie_equipo' => $a->orden ? ($a->orden->equipo->serie ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->equipo->serie ?? '') : ''),
                                'sucursal' => $a->orden ? ($a->orden->sucursal->ciudad ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->sucursal->ciudad ?? '') : ''),
                                'cas' => $a->orden ? ($a->orden->cas->nombre ?? '') : '',
                                'motivo_ingreso' => $tipoOrden,
                                'falla_reportada' => $a->orden ? ($a->orden->equipo->falla ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->descripcion ?? '') : ''),
                                'observacion_equipo' => $a->orden ? ($a->orden->equipo->observacion ?? '') : ($a->ordenEmpresa ? ($a->ordenEmpresa->descripcion ?? '') : '')
                            ]) }}">
                                <td style="font-size:12px; white-space:nowrap;">{{ $fechaHora }}</td>
                                <td class="aud-code">{{ $a->repuesto->codigo ?? '-' }}</td>
                                <td style="font-weight:500;">{{ $a->repuesto->nombre ?? '-' }}</td>
                                <td>{{ $tecnicoNombre }}</td>
                                <td>
                                    @if($a->orden)
                                        <a href="{{ route('ordenes.imprimir', ['id' => $a->orden->id]) }}" target="_blank" class="aud-nro-orden" title="Imprimir Comprobante OT">
                                            <i class="bi bi-printer me-1"></i>{{ $a->orden->nro_orden }}
                                        </a>
                                    @elseif($a->ordenEmpresa)
                                        <a href="{{ route('ordenes_empresa.imprimir', ['id' => $a->ordenEmpresa->id]) }}" target="_blank" class="aud-nro-orden" title="Imprimir Comprobante OT Empresa">
                                            <i class="bi bi-printer me-1"></i>{{ $a->ordenEmpresa->nro_orden }}
                                        </a>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; {{ $badgeStyle }}">
                                        {{ $tipoOrden }}
                                    </span>
                                </td>
                                <td style="text-align:center; font-weight:700;">{{ $a->cantidad }}</td>
                                <td style="text-align:right; color:#475569;">${{ number_format($costoUnit, 2) }}</td>
                                <td style="text-align:right; font-weight:700; color:#0f172a;">${{ number_format($costoTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="auditoria-pager" style="padding: 10px 20px 20px;"></div>
        @else
            <div class="aud-empty">
                <i class="bi bi-journal-x" style="color:#cbd5e1;"></i>
                <h4>Sin registros de auditoría</h4>
                <p>No se encontraron movimientos de stock en el inventario con los filtros seleccionados.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('js_adicional')
<script>
    let _allRows = [];
    let _filteredRows = [];
    let _sortCol = -1;
    let _sortDir = 1;
    let _audPager = null;

    function escHtml(str) {
        return (str || '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initTabla() {
        const tbody = document.getElementById('aud-tbody');
        if (!tbody) return;

        const trs = tbody.querySelectorAll('tr[data-row="auditoria"]');
        _allRows = Array.from(trs).map(tr => {
            const data = JSON.parse(tr.getAttribute('data-fila') || '{}');
            return {
                element: tr,
                data: data
            };
        });
        _filteredRows = _allRows.slice();
        
        _audPager = new SgnPager({
            containerSelector: '#aud-tbody',
            itemSelector: 'tr[data-row="auditoria"]',
            pagerContainerSelector: '#auditoria-pager',
            pageSize: 15
        });
    }

    window.filtrarTablaLocal = function(q) {
        q = q.toLowerCase().trim();
        if (!q) {
            _filteredRows = _allRows.slice();
        } else {
            _filteredRows = _allRows.filter(r => {
                return Object.values(r.data).join(' ').toLowerCase().includes(q);
            });
        }
        _allRows.forEach(r => {
            if (_filteredRows.includes(r)) {
                r.element.style.display = '';
            } else {
                r.element.style.display = 'none';
            }
        });
    };

    window.sortTablaLocal = function(col, key) {
        if (_sortCol === col) {
            _sortDir *= -1;
        } else {
            _sortCol = col;
            _sortDir = 1;
        }

        _filteredRows.sort((a, b) => {
            let valA = a.data[key];
            let valB = b.data[key];

            if (typeof valA === 'number' && typeof valB === 'number') {
                return (valA - valB) * _sortDir;
            }
            return String(valA).localeCompare(String(valB), 'es') * _sortDir;
        });

        // Reordenar elementos reales en el DOM
        const tbody = document.getElementById('aud-tbody');
        if (tbody) {
            _filteredRows.forEach(r => tbody.appendChild(r.element));
        }

        // Agregar indicadores a las cabeceras
        document.querySelectorAll('.aud-tbl th').forEach((th, i) => {
            th.innerHTML = th.innerHTML.replace(/ [▲▼]/g, '');
            if (i === col) {
                th.innerHTML += _sortDir === 1 ? ' ▲' : ' ▼';
            }
        });

        if (_audPager) {
            _audPager.currentPage = 1;
            _audPager.render();
        }
    };

    // Exportador Nativo a CSV
    window.exportarCSV = function() {
        if (!_filteredRows.length) {
            alert('No hay datos para exportar.');
            return;
        }

        let csv = '\uFEFF'; // UTF-8 BOM
        csv += 'Fecha / Hora,Código,Nombre del Repuesto,Usuario / Técnico,Orden de Servicio,Tipo de Orden,Cantidad,Costo Unit ($),Costo Total ($)\r\n';

        _filteredRows.forEach(r => {
            const d = r.data;
            const fecha = new Date(d.fecha).toLocaleString('es-EC').replace(',', '');
            const fila = [
                `"${fecha}"`,
                `"${d.codigo.replace(/"/g, '""')}"`,
                `"${d.nombre.replace(/"/g, '""')}"`,
                `"${d.tecnico.replace(/"/g, '""')}"`,
                `"${d.orden || 'N/A'}"`,
                `"${(d.tipo_orden || 'N/A').replace(/"/g, '""')}"`,
                d.cantidad,
                d.costo_u.toFixed(2),
                d.costo_t.toFixed(2)
            ];
            csv += fila.join(',') + '\r\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'Auditoria_Stock_Repuestos_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const routeImprimirReporte = '{{ route("repuestos.imprimir_reporte") }}';

    window.abrirReporteImpresion = function() {
        const params = new URLSearchParams(window.location.search);
        const busq = document.getElementById('aud-buscador') ? document.getElementById('aud-buscador').value.trim() : '';
        if (busq) {
            params.append('buscar', busq);
        }
        const url = routeImprimirReporte + '?' + params.toString();
        window.open(url, '_blank');
    };

@verbatim
    function cargarExcelJS() {
        return new Promise((resolve, reject) => {
            if (window.ExcelJS) { resolve(); return; }
            const urls = [
                'https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js',
                'https://unpkg.com/exceljs@4.4.0/dist/exceljs.min.js'
            ];
            let i = 0;
            function tryNext() {
                if (i >= urls.length) { reject(new Error('No se pudo cargar ExcelJS')); return; }
                const s = document.createElement('script'); s.src = urls[i++];
                s.onload = () => window.ExcelJS ? resolve() : tryNext();
                s.onerror = tryNext;
                document.head.appendChild(s);
            }
            tryNext();
        });
    }

    window.exportarExcel = function() {
        if (!_filteredRows.length) {
            alert('No hay datos para exportar.');
            return;
        }

        const btn = document.querySelector('button[onclick="exportarExcel()"]');
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

        cargarExcelJS().then(async () => {
            const wb = new ExcelJS.Workbook();
            wb.creator = 'SGN - Novitecnologia';
            wb.created = new Date();

            const headers = [
                'Fecha / Hora', 'Código', 'Nombre del Repuesto', 'Usuario / Técnico', 'Orden de Servicio', 'Tipo de Orden',
                'Cant', 'Costo Unit ($)', 'Costo Total ($)', 'F. Ingreso Orden', 'F. Prometido', 'F. Entrega',
                'Estado Orden', 'Estado Repuesto', 'Estado Garantía', 'Factura', 'Factura 2', 'Cliente',
                'C.I./RUC Cliente', 'Teléfono Cliente', 'Correo Cliente', 'Dirección Cliente', 'Tipo Equipo',
                'Equipo', 'Marca', 'Modelo', 'Serie Equipo', 'Sucursal', 'CAS', 'Motivo Ingreso',
                'Falla Reportada', 'Observación Equipo'
            ];
            const totalCols = headers.length;

            const ws = wb.addWorksheet('Auditoría Repuestos', {
                views: [{ showGridLines: true }]
            });

            ws.columns = [
                { width: 18 }, { width: 15 }, { width: 35 }, { width: 28 }, { width: 18 }, { width: 24 },
                { width: 8 }, { width: 15 }, { width: 15 }, { width: 16 }, { width: 14 }, { width: 14 },
                { width: 16 }, { width: 16 }, { width: 16 }, { width: 18 }, { width: 18 }, { width: 28 },
                { width: 18 }, { width: 16 }, { width: 24 }, { width: 30 }, { width: 16 }, { width: 28 },
                { width: 16 }, { width: 18 }, { width: 18 }, { width: 16 }, { width: 16 }, { width: 24 },
                { width: 28 }, { width: 28 }
            ];

            const C = {
                azulO: '1E3A8A', azul: '1E40AF', azulL: 'DBEAFE', azulXL: 'EFF6FF',
                verdeO: '065F46', verde: '166534', verdeL: 'DCFCE7', verdeXL: 'ECFDF5',
                ambar: '854D0E', ambarL: 'FEF9C3', rojo: '991B1B', rojoL: 'FEE2E2',
                indigo: '4F46E5', indigoL: 'EEF2FF', indigoXL: 'F5F7FF',
                gris: 'F8FAFC', grisMed: 'E2E8F0', grisOsc: '64748B', blanco: 'FFFFFF', negro: '0F172A'
            };

            const fl = color => ({ type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + color } });
            const bd = (color = 'E2E8F0') => {
                const borderStyle = { style: 'thin', color: { argb: 'FF' + color } };
                return { top: borderStyle, left: borderStyle, bottom: borderStyle, right: borderStyle };
            };
            const fn = (bold, size, color, extra = {}) => Object.assign({ name: 'Arial', bold: !!bold, size: size || 10, color: { argb: 'FF' + (color || C.negro) } }, extra);
            const al = (h = 'left', v = 'middle') => ({ horizontal: h, vertical: v });

            ws.mergeCells(1, 1, 1, totalCols);
            const t1 = ws.getCell(1, 1);
            t1.value = 'REPORTE DE AUDITORÍA - STOCK DE REPUESTOS';
            t1.fill = fl(C.indigo);
            t1.font = fn(true, 14, C.blanco);
            t1.alignment = al('center');
            ws.getRow(1).height = 30;

            ws.mergeCells(2, 1, 2, totalCols);
            const t2 = ws.getCell(2, 1);
            t2.value = `Generado: ${new Date().toLocaleString('es-EC')}   |   Registros: ${_filteredRows.length}`;
            t2.fill = fl(C.indigoL);
            t2.font = fn(false, 10, C.indigo, { italic: true });
            t2.alignment = al('center');
            ws.getRow(2).height = 16;

            let totalItems = 0;
            let totalCosto = 0;
            const repCounts = {};
            const tecCounts = {};

            const dataRows = _filteredRows.map(r => {
                const d = r.data;
                const qty = Number(d.cantidad || 0);
                const costU = Number(d.costo_u || 0);
                const costT = Number(d.costo_t || 0);

                totalItems += qty;
                totalCosto += costT;

                if (d.nombre) {
                    repCounts[d.nombre] = (repCounts[d.nombre] || 0) + qty;
                }
                if (d.tecnico) {
                    tecCounts[d.tecnico] = (tecCounts[d.tecnico] || 0) + qty;
                }

                return [
                    new Date(d.fecha).toLocaleString('es-EC'),
                    d.codigo || '',
                    d.nombre || '',
                    d.tecnico || '',
                    d.orden || 'N/A',
                    d.tipo_orden || 'N/A',
                    qty,
                    costU,
                    costT,
                    d.fecha_ingreso_orden || '',
                    d.fecha_prometido_orden || '',
                    d.fecha_entrega_orden || '',
                    d.estado_orden || '',
                    d.estado_repuesto_orden || '',
                    d.estado_garantia_orden || '',
                    d.factura || '',
                    d.factura_2 || '',
                    d.cliente || '',
                    d.cliente_identificacion || '',
                    d.cliente_telefono || '',
                    d.cliente_correo || '',
                    d.cliente_direccion || '',
                    d.tipo_equipo || '',
                    d.equipo || '',
                    d.marca || '',
                    d.modelo || '',
                    d.serie_equipo || '',
                    d.sucursal || '',
                    d.cas || '',
                    d.motivo_ingreso || '',
                    d.falla_reportada || '',
                    d.observacion_equipo || ''
                ];
            });

            let repuestoMasUsado = 'Ninguno';
            let repuestoMasUsadoCant = 0;
            Object.entries(repCounts).forEach(([name, cant]) => {
                if (cant > repuestoMasUsadoCant) {
                    repuestoMasUsadoCant = cant;
                    repuestoMasUsado = name;
                }
            });

            let tecnicoLider = 'Ninguno';
            let tecnicoLiderCant = 0;
            Object.entries(tecCounts).forEach(([name, cant]) => {
                if (cant > tecnicoLiderCant) {
                    tecnicoLiderCant = cant;
                    tecnicoLider = name;
                }
            });

            const kpiColumnFills = {
                A: C.indigoL, B: C.indigoL,
                C: C.verdeL, D: C.verdeL,
                E: C.ambarL, F: C.ambarL, G: C.ambarL,
                H: C.azulL, I: C.azulL
            };

            for (let r = 4; r <= 6; r++) {
                ws.getRow(r).height = (r === 5) ? 28 : 14;
                ['A','B','C','D','E','F','G','H','I'].forEach(col => {
                    const cell = ws.getCell(`${col}${r}`);
                    cell.fill = fl(kpiColumnFills[col]);
                    cell.border = bd();
                });
            }

            ws.mergeCells('A4:B4'); ws.getCell('A4').value = 'REPUESTOS UTILIZADOS'; ws.getCell('A4').font = fn(true, 8, C.indigo); ws.getCell('A4').alignment = al('center');
            ws.mergeCells('A5:B5'); ws.getCell('A5').value = `${totalItems} uds`; ws.getCell('A5').font = fn(true, 16, C.indigo); ws.getCell('A5').alignment = al('center');
            ws.mergeCells('A6:B6'); ws.getCell('A6').value = 'Movimientos Totales'; ws.getCell('A6').font = fn(false, 9, C.indigo); ws.getCell('A6').alignment = al('center');

            ws.mergeCells('C4:D4'); ws.getCell('C4').value = 'COSTO TOTAL SALIDAS'; ws.getCell('C4').font = fn(true, 8, C.verde); ws.getCell('C4').alignment = al('center');
            ws.mergeCells('C5:D5'); ws.getCell('C5').value = totalCosto; ws.getCell('C5').font = fn(true, 16, C.verde); ws.getCell('C5').alignment = al('center');
            ws.getCell('C5').numFormat = '$#,##0.00';
            ws.mergeCells('C6:D6'); ws.getCell('C6').value = 'Valor Financiero'; ws.getCell('C6').font = fn(false, 9, C.verde); ws.getCell('C6').alignment = al('center');

            ws.mergeCells('E4:G4'); ws.getCell('E4').value = 'REPUESTO MÁS USADO'; ws.getCell('E4').font = fn(true, 8, C.ambar); ws.getCell('E4').alignment = al('center');
            ws.mergeCells('E5:G5'); ws.getCell('E5').value = repuestoMasUsado; ws.getCell('E5').font = fn(true, 11, C.ambar); ws.getCell('E5').alignment = al('center');
            ws.mergeCells('E6:G6'); ws.getCell('E6').value = `Consumo: ${repuestoMasUsadoCant} uds`; ws.getCell('E6').font = fn(false, 9, C.ambar); ws.getCell('E6').alignment = al('center');

            ws.mergeCells('H4:I4'); ws.getCell('H4').value = 'TÉCNICO MÁS ACTIVO'; ws.getCell('H4').font = fn(true, 8, C.azul); ws.getCell('H4').alignment = al('center');
            ws.mergeCells('H5:I5'); ws.getCell('H5').value = tecnicoLider; ws.getCell('H5').font = fn(true, 11, C.azul); ws.getCell('H5').alignment = al('center');
            ws.mergeCells('H6:I6'); ws.getCell('H6').value = `Consumo: ${tecnicoLiderCant} uds`; ws.getCell('H6').font = fn(false, 9, C.azul); ws.getCell('H6').alignment = al('center');

            ws.getRow(7).height = 10;

            ws.getRow(8).height = 22;
            headers.forEach((h, idx) => {
                const cell = ws.getCell(8, idx + 1);
                cell.value = h;
                cell.fill = fl(C.indigo);
                cell.font = fn(true, 10, C.blanco);
                cell.alignment = al('center');
                cell.border = bd('4338CA');
            });
            ws.autoFilter = { from: { row: 8, column: 1 }, to: { row: 8, column: totalCols } };

            dataRows.forEach((rData, idx) => {
                const rNum = idx + 9;
                const row = ws.getRow(rNum);
                row.height = 16;
                const bgBase = idx % 2 === 0 ? C.blanco : C.gris;

                rData.forEach((val, colIdx) => {
                    const cell = row.getCell(colIdx + 1);
                    cell.value = val;
                    cell.border = bd();
                    cell.font = fn(false, 9, C.negro);
                    cell.alignment = al('left', 'middle');
                    cell.fill = fl(bgBase);

                    if (colIdx === 0) {
                        cell.alignment = al('center', 'middle');
                    } else if (colIdx === 1) {
                        cell.font = fn(true, 9, C.ambar, { name: 'Courier New' });
                        cell.alignment = al('center', 'middle');
                    } else if (colIdx === 4) {
                        cell.font = fn(true, 9, C.indigo);
                        cell.alignment = al('center', 'middle');
                    } else if (colIdx === 6) {
                        cell.font = fn(true, 9, C.negro);
                        cell.alignment = al('center', 'middle');
                    } else if (colIdx === 7 || colIdx === 8) {
                        cell.numFormat = '$#,##0.00';
                        cell.font = fn(colIdx === 8, 9, colIdx === 8 ? C.negro : C.grisOsc);
                        cell.alignment = al('right', 'middle');
                    }
                });
            });

            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `auditoria_repuestos_${new Date().toISOString().slice(0, 10)}.xlsx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }).catch(err => {
            alert('Error al generar Excel: ' + err.message);
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    };
@endverbatim

    document.addEventListener('DOMContentLoaded', () => {
        initTabla();
    });
</script>
@endpush
