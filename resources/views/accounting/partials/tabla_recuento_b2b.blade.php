<div class="table-responsive">
    <table class="custom-table">
        <thead>
            <tr>
                <th style="width: 36px;">
                    <input type="checkbox" onclick="toggleSelectAllGrupo(this, '{{ $tipoGrupo }}')">
                </th>
                <th>Nro. Orden</th>
                <th>Empresa / Cliente Final</th>
                <th>Subtipo</th>
                <th>Equipo / Marca / Serie</th>
                <th>Técnico(s) y Horas</th>
                <th style="text-align: right;">Base Fija ($)</th>
                <th style="text-align: right;">Mano de Obra (-50%)</th>
                <th style="text-align: right;">Repuestos (100%)</th>
                <th style="text-align: right;">Total Calculado</th>
                <th style="text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenesGrupo as $ord)
                @php
                    $empNombre = $ord->empresa->nombre ?? 'Empresa';
                    $isRB = str_contains(strtoupper($empNombre), 'RB');
                    $isNovisolutions = !empty($ord->is_novisolutions);
                    $subtipoNorm = $ord->subtipo_normalizado ?? 'Servicios';
                    
                    $subtipoBadgeClass = 'badge-servicio';
                    if ($subtipoNorm === 'Stock') {
                        $subtipoBadgeClass = 'badge-stock';
                    } elseif ($subtipoNorm === 'Autoconsumo') {
                        $subtipoBadgeClass = 'badge-autoconsumo';
                    } elseif ($subtipoNorm === 'Garantía') {
                        $subtipoBadgeClass = 'badge-garantia';
                    }

                    // Datos Cliente
                    if (!empty($ord->cliente)) {
                        $cliNombre = trim(($ord->cliente->nombres ?? '') . ' ' . ($ord->cliente->apellidos ?? ''));
                        $cliIdent = $ord->cliente->identificacion ?? 'N/A';
                        $cliTel = $ord->cliente->numero_contacto ?? 'N/A';
                        $cliMail = $ord->cliente->correo ?? 'N/A';
                    } else {
                        $cliNombre = $empNombre;
                        $cliIdent = $ord->empresa->ruc ?? 'N/A';
                        $cliTel = $ord->empresa->telefono ?? 'N/A';
                        $cliMail = $ord->empresa->correo ?? 'N/A';
                    }

                    // Formatear datos del equipo
                    $eq = $ord->equipo;
                    $eqInfo = 'N/A';
                    if ($eq) {
                        $parts = array_filter([$eq->tipo ?? '', $eq->marca ?? '', $eq->modelo ?? '']);
                        $eqInfo = implode(' · ', $parts);
                        if (!empty($eq->serie)) {
                            $eqInfo .= ' (S/N: ' . $eq->serie . ')';
                        }
                    }

                    // Formatear técnicos
                    $tecnicosNombres = 'Sin técnico asignado';
                    if ($ord->tecnicos && $ord->tecnicos->count() > 0) {
                        $tecnicosNombres = implode(', ', $ord->tecnicos->pluck('nombre_tecnico')->toArray());
                    } elseif (!empty($ord->tecnico->nombre_tecnico)) {
                        $tecnicosNombres = $ord->tecnico->nombre_tecnico;
                    }

                    $tipoOrdenOrigen = $ord->tipo_orden_origen ?? 'empresa';
                    $valFijo = (float)($ord->valor_fijo_calculado ?? 0);
                    $valMoCobrado = (float)($ord->valor_mano_obra_cobrado ?? 0);
                    $valMoOriginal = (float)($ord->valor_mano_obra_original ?? 0);
                    $valRep = (float)($ord->valor_repuestos_calculado ?? 0);
                    $valTotal = (float)($ord->valor_total_calculado ?? 0);
                    $tituloServ = $ord->titulo_servicio_mostrado ?? ($ord->titulo_servicio ?? '');
                    $repDetalle = $ord->repuestos_detalle_str ?? '';
                @endphp
                <tr id="row-orden-{{ $tipoOrdenOrigen }}-{{ $ord->id }}">
                    <td>
                        <input type="checkbox" class="chk-orden" 
                            data-id="{{ $ord->id }}"
                            data-tipo-orden="{{ $tipoOrdenOrigen }}"
                            data-nro="{{ $ord->nro_orden }}"
                            data-empresa="{{ $empNombre }}"
                            data-cliente-nombre="{{ $cliNombre }}"
                            data-identificacion="{{ $cliIdent }}"
                            data-cliente-telefono="{{ $cliTel }}"
                            data-cliente-correo="{{ $cliMail }}"
                            data-subtipo="{{ $subtipoNorm }}"
                            data-equipo="{{ $eqInfo }}"
                            data-tecnico="{{ $tecnicosNombres }}"
                            data-sucursal="{{ $ord->sucursal->ciudad ?? 'N/A' }}"
                            data-fecha-ingreso="{{ $ord->fecha_de_ingreso ?? '-' }}"
                            data-fecha-entrega="{{ $ord->fecha_entrega ?? $ord->fecha_finalizacion ?? '-' }}"
                            data-horas="{{ $ord->horas_calculadas }}"
                            data-tecnicos="{{ $ord->tecnicos_count }}"
                            data-tarifa="{{ $ord->tarifa_calculada }}"
                            data-valor-fijo="{{ $valFijo }}"
                            data-valor-mano-obra="{{ $valMoOriginal }}"
                            data-valor-repuestos="{{ $valRep }}"
                            data-titulo-servicio="{{ $tituloServ }}"
                            data-repuestos-detalle="{{ $repDetalle }}"
                            data-total="{{ $valTotal }}"
                            data-estado="{{ $ord->estado ?? $ord->estado_orden ?? 'Finalizada' }}"
                            data-facturacion="{{ $ord->estado_facturacion ?? 'Pendiente' }}"
                            data-descripcion="{{ $ord->descripcion ?? $ord->falla ?? $ord->motivo_ingreso ?? '-' }}"
                            data-memo="{{ $ord->memo_entrega ?? $ord->observaciones ?? $ord->observacion ?? '-' }}"
                            onchange="actualizarSeleccion()">
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.9rem;">{{ $ord->nro_orden }}</strong>
                    </td>
                    <td>
                        <span class="badge-subtipo badge-empresa">{{ $empNombre }}</span>
                        @if(!empty($ord->cliente))
                            <div style="font-size: 0.775rem; color: #475569; font-weight: 600; margin-top: 2px;">
                                <i class="bi bi-person me-1"></i>{{ $cliNombre }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge-subtipo {{ $subtipoBadgeClass }}">
                            {{ $subtipoNorm }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $eqInfo }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600;">{{ $tecnicosNombres }}</div>
                        <div style="font-size: 0.775rem; color: #64748b;">
                            {{ number_format($ord->horas_calculadas, 1) }} hrs · {{ $ord->sucursal->ciudad ?? 'N/A' }}
                        </div>
                    </td>
                    <!-- BASE FIJA -->
                    <td style="text-align: right;">
                        @if($isNovisolutions)
                            <strong style="color: #1e293b;">${{ number_format($valFijo, 2) }}</strong>
                            <div style="font-size: 0.725rem; color: #64748b;">($28.50 - 50%)</div>
                        @elseif($isRB)
                            <strong style="color: #1e293b;">${{ number_format($valFijo, 2) }}</strong>
                            <div style="font-size: 0.725rem; color: #64748b;">$50/hr</div>
                        @else
                            <strong style="color: #1e293b;">${{ number_format($valFijo, 2) }}</strong>
                            <div style="font-size: 0.725rem; color: #64748b;">Tarifa fija</div>
                        @endif
                    </td>
                    <!-- MANO DE OBRA (-50%) -->
                    <td style="text-align: right;">
                        <div id="display-mo-{{ $tipoOrdenOrigen }}-{{ $ord->id }}">
                            @if($valMoOriginal > 0)
                                <span style="font-weight: 700; color: #2563eb;">+${{ number_format($valMoCobrado, 2) }}</span>
                                <div style="font-size: 0.725rem; color: #64748b;">Orig: ${{ number_format($valMoOriginal, 2) }} (-50%)</div>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">$0.00</span>
                            @endif
                        </div>
                        @if($tituloServ !== '')
                            <div id="display-titulo-{{ $tipoOrdenOrigen }}-{{ $ord->id }}" style="font-size: 0.725rem; color: #0f172a; font-weight: 600; margin-top: 2px;" title="{{ $tituloServ }}">
                                {{ \Illuminate\Support\Str::limit($tituloServ, 22) }}
                            </div>
                        @else
                            <div id="display-titulo-{{ $tipoOrdenOrigen }}-{{ $ord->id }}" style="font-size: 0.725rem; color: #94a3b8; font-style: italic;">Sin título</div>
                        @endif
                    </td>
                    <!-- REPUESTOS (100%) -->
                    <td style="text-align: right;">
                        @if($valRep > 0)
                            <strong style="color: #166534;">+${{ number_format($valRep, 2) }}</strong>
                            <div style="font-size: 0.725rem; color: #166534;" title="{{ $repDetalle }}">
                                <i class="bi bi-cpu me-1"></i>100% cobrado
                            </div>
                        @else
                            <span style="color: #94a3b8;">$0.00</span>
                        @endif
                    </td>
                    <!-- TOTAL CALCULADO -->
                    <td style="text-align: right;">
                        <strong id="display-total-{{ $tipoOrdenOrigen }}-{{ $ord->id }}" style="color: #059669; font-size: 0.95rem;">
                            ${{ number_format($valTotal, 2) }}
                        </strong>
                    </td>
                    <!-- ACCIONES: EDITAR MANO DE OBRA & DETALLES -->
                    <td style="text-align: center; white-space: nowrap;">
                        <button type="button" class="btn-details" style="padding: 4px 8px; margin-right: 4px;" 
                                onclick="abrirModalEditarManoObra({{ $ord->id }}, '{{ $tipoOrdenOrigen }}', '{{ addslashes($ord->nro_orden) }}', {{ $valMoOriginal }}, '{{ addslashes($tituloServ) }}', {{ $valRep }}, {{ $valFijo }})"
                                title="Editar Mano de Obra y Servicio">
                            <i class="bi bi-pencil-square text-primary"></i> M.O.
                        </button>
                        <button type="button" class="btn-details" style="padding: 4px 8px;" onclick="toggleDetails({{ $ord->id }}, '{{ $tipoOrdenOrigen }}')" title="Ver Detalles">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: #94a3b8; padding: 24px;">No hay órdenes registradas en esta sección.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
