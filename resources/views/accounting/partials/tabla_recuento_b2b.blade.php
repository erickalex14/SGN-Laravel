<div class="table-responsive">
    <table class="custom-table">
        <thead>
            <tr>
                <th style="width: 40px;">
                    <input type="checkbox" onclick="toggleSelectAllGrupo(this, '{{ $tipoGrupo }}')">
                </th>
                <th>Nro. Orden</th>
                <th>Empresa / Cliente Final</th>
                <th>Subtipo</th>
                <th>Equipo / Marca / Serie</th>
                <th>Técnico(s) y Horas</th>
                <th>Tarifa Aplicada</th>
                <th>Valor Calculado</th>
                <th style="text-align: center;">Detalles</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenesGrupo as $ord)
                @php
                    $empNombre = $ord->empresa->nombre ?? 'Empresa';
                    $isRB = str_contains(strtoupper($empNombre), 'RB');
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
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" class="chk-orden" 
                            data-id="{{ $ord->id }}"
                            data-tipo-orden="{{ $ord->tipo_orden_origen ?? 'empresa' }}"
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
                            data-total="{{ $ord->valor_total_calculado }}"
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
                    <td>
                        @if($isRB)
                            $50.00 / hr
                        @elseif($subtipoNorm === 'Servicios')
                            $25.00 / hr / técnico
                        @elseif($subtipoNorm === 'Garantía')
                            Cobro Estándar Novicompu (${{ number_format($ord->tarifa_calculada, 2) }})
                        @else
                            Presupuesto / Valor Fijo
                        @endif
                    </td>
                    <td>
                        <strong style="color: #059669; font-size: 0.95rem;">${{ number_format($ord->valor_total_calculado, 2) }}</strong>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-details" onclick="toggleDetails({{ $ord->id }})">
                            <i class="bi bi-info-circle me-1"></i>Ver Detalles
                        </button>
                    </td>
                </tr>
                <tr class="details-row" id="details-row-{{ $ord->id }}">
                    <td colspan="9">
                        <div class="details-container">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                                <div>
                                    <strong style="color: #0f172a;">Detalles de la Orden:</strong><br>
                                    <span>Nro. Ticket / Código: {{ $ord->nro_orden }}</span><br>
                                    <span>Cliente Final: <strong>{{ $cliNombre }}</strong> (CI: {{ $cliIdent }})</span><br>
                                    <span>Estado: <strong>{{ $ord->estado ?? $ord->estado_orden }}</strong></span><br>
                                    <span>Sucursal: <strong>{{ $ord->sucursal->ciudad ?? 'Quito' }}</strong></span>
                                </div>
                                <div>
                                    <strong style="color: #0f172a;">Descripción del Servicio / Falla:</strong><br>
                                    <span>{{ $ord->descripcion ?? $ord->falla ?? $ord->motivo_ingreso ?? 'Sin descripción registrada' }}</span>
                                </div>
                                <div>
                                    <strong style="color: #0f172a;">Observaciones / Memo:</strong><br>
                                    <span>{{ $ord->memo_entrega ?? $ord->observaciones ?? $ord->observacion ?? 'Sin observaciones adicionales' }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #94a3b8; padding: 24px;">No hay órdenes registradas en esta sección.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
