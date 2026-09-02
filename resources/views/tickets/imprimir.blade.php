@php
    $solicitante = $ticket->solicitante;
    $asignadoA = $ticket->asignadoA;
    $sucursalCliente = $ticket->sucursalCliente;
    
    // Extracción inteligente de AnyDesk y datos MBA3 de la descripción
    $desc = $ticket->descripcion ?? '';
    $anydesk = '';
    if (preg_match('/(?:anydesk|any\s*desk|id\s*anydesk)[:\s\-]*([0-9\s]{9,12})/i', $desc, $m)) {
        $anydesk = trim($m[1]);
    }
    
    $iconoMba = '';
    if (preg_match('/(?:ícono|icono|urdp|acceso|servidor)[:\s\-]*([a-zA-Z0-9\-\_s]+?)(?:\n|\r|,|\.|$)/i', $desc, $mIcono)) {
        $iconoMba = trim($mIcono[1]);
    }

    $userMba = '';
    if (preg_match('/(?:usuario\s*mba|user\s*mba|vendedor)[:\s\-]*([a-zA-Z0-9\-\_s]+?)(?:\n|\r|,|\.|$)/i', $desc, $mMba)) {
        $userMba = trim($mMba[1]);
    }
    
    $depto = $solicitante?->departamento;
    $esCasoMba = ($ticket->categoria === 'Casos MBA3' || str_contains($ticket->descripcion, 'CASO MBA3') || str_contains($ticket->categoria, 'MBA'));
    
    // Extracción de datos específicos (Vendedor / Usuario MBA / Ícono Milenium / Ícono MBA)
    $cargoEsp = '';
    if (preg_match('/(?:cargo(?:\s*en\s*la\s*sucursal)?)[:\s\-]*([^\n\r]+)/i', $desc, $mCargo)) {
        $cargoEsp = trim($mCargo[1]);
    }
    $nombresEsp = '';
    if (preg_match('/(?:nombres?\s*completos?)[:\s\-]*([^\n\r]+)/i', $desc, $mNombres)) {
        $nombresEsp = trim($mNombres[1]);
    }
    $cedulaEsp = '';
    if (preg_match('/(?:(?:n[uú]mero\s*de\s*)?c[eé]dula)[:\s\-]*([0-9]{10,13})/i', $desc, $mCedula)) {
        $cedulaEsp = trim($mCedula[1]);
    }
    $telefonoEsp = '';
    if (preg_match('/(?:tel(?:[eé]fono)?(?:\s*corporativo)?|celular)[:\s\-]*([0-9\s\-\+]{9,15})/i', $desc, $mTel)) {
        $telefonoEsp = trim($mTel[1]);
    }
    $correoEsp = '';
    if (preg_match('/(?:correo(?:\s*corporativo)?(?:\s*de\s*tienda)?)[:\s\-]*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $desc, $mCor)) {
        $correoEsp = trim($mCor[1]);
    }
    $sucursalEsp = '';
    if (preg_match('/(?:sucursal)[:\s\-]*([^\n\r]+)/i', $desc, $mSuc)) {
        $sucursalEsp = trim($mSuc[1]);
    }
    $grupoMilEsp = '';
    if (preg_match('/(?:grupo\s*de\s*milenium)[:\s\-]*([^\n\r]+)/i', $desc, $mGrp)) {
        $grupoMilEsp = trim($mGrp[1]);
    }
    $accesoParecidoEsp = '';
    if (preg_match('/(?:acceso\s*parecido\s*a)[:\s\-]*([^\n\r]+)/i', $desc, $mAcc)) {
        $accesoParecidoEsp = trim($mAcc[1]);
    }
    $tieneFichaEspecifica = ($nombresEsp || $cedulaEsp || $cargoEsp);
    
    // Colores de estado
    $estadoLabels = [
        'abierto' => 'Abierto',
        'en_proceso' => 'En Atención / Proceso',
        'en_espera' => 'En Espera',
        'en_mba' => 'En Manos de MBA (48h)',
        'resuelto' => 'Resuelto con Éxito',
        'cerrado' => 'Cerrado',
        'cancelado' => 'Cancelado',
    ];
    $estadoTexto = $estadoLabels[$ticket->estado] ?? ucfirst($ticket->estado);

    // Mensajes de bitácora (cronológico)
    $mensajes = $ticket->mensajes->sortBy('created_at');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket {{ $ticket->codigo_ticket }} — Novitecnología</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 7.6pt; color: #000; background: #fff; line-height: 1.3; }
.no-print { display: inline-flex; }
.wrap { width: 100%; max-width: 190mm; margin: auto; padding: 4mm; }

/* ── Encabezado Corporativo ── */
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 3px; margin-bottom: 4px; gap: 10px; }
.header-info { font-size: 7pt; line-height: 1.35; }
.header-info .empresa { font-size: 9pt; font-weight: 700; color: #0f172a; }
.header img { height: 34px; }

/* ── Barra de Título del Ticket ── */
.orden-header { display: flex; justify-content: space-between; align-items: center; background: #1a56db; color: #fff; padding: 3px 8px; border-radius: 3px; margin-bottom: 4px; }
.orden-header.sistemas { background: #7c3aed; }
.orden-header .nro { font-size: 10pt; font-weight: 700; }
.orden-header .meta { font-size: 6.5pt; text-align: right; line-height: 1.4; }

/* ── Secciones y Tablas ── */
.sec-titulo { background: #dbeafe; font-weight: 700; font-size: 6.5pt; text-transform: uppercase; padding: 2px 6px; border-left: 3px solid #1a56db; margin-bottom: 1px; margin-top: 3px; }
.sec-titulo.sistemas { background: #ede9fe; border-left-color: #7c3aed; }
.sec-titulo.alerta { background: #fef3c7; border-left-color: #d97706; color: #92400e; }
.sec-titulo.resolucion { background: #dcfce7; border-left-color: #16a34a; color: #166534; }
.sec-titulo.mba { background: #f3e8ff; border-left-color: #9333ea; color: #6b21a8; }

table.datos { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
table.datos td { border: 1px solid #d1d5db; padding: 2.5px 5px; font-size: 7pt; vertical-align: top; }
table.datos td .lbl { font-size: 5.5pt; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0; }
.badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 6.5pt; font-weight: 700; text-transform: uppercase; }

/* ── Botón Imprimir ── */
.btn-print { position: fixed; top: 12px; right: 12px; background: #1a56db; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 12.5px; cursor: pointer; font-weight: 700; z-index: 999; box-shadow: 0 3px 10px rgba(0,0,0,.25); }
.btn-print:hover { background: #1e40af; }

/* ── Alertas y Notas Especiales ── */
.alerta-box { padding: 4px 8px; border-radius: 3px; font-size: 7pt; margin-bottom: 3px; border: 1px solid; }
.alerta-box.mba { background: #faf5ff; border-color: #c084fc; color: #581c87; }
.alerta-box.resolucion { background: #f0fdf4; border-color: #86efac; color: #14532d; }

/* ── Tabla de Bitácora / Mensajes ── */
table.bitacora-tbl { width: 100%; border-collapse: collapse; margin-bottom: 3px; font-size: 6.8pt; }
table.bitacora-tbl th { background: #f1f5f9; color: #475569; padding: 2px 5px; text-align: left; font-size: 6pt; text-transform: uppercase; border-bottom: 1.5px solid #cbd5e1; }
table.bitacora-tbl td { border-bottom: 1px solid #e2e8f0; padding: 2.5px 5px; vertical-align: top; }
table.bitacora-tbl tr.nota-interna td { background: #fefce8; }
table.bitacora-tbl tr.soporte td { background: #f8fafc; }

/* ── Firmas y Pie ── */
.firmas { display: flex; justify-content: space-between; margin-top: 14px; }
.firma-box { width: 44%; text-align: center; }
.firma-linea { border-top: 1px solid #000; padding-top: 3px; font-size: 6.8pt; margin-top: 26px; }
.foot { text-align: center; margin-top: 8px; font-size: 6.5pt; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 4px; }

@media print {
    @page { size: A4 portrait; margin: 8mm; }
    .no-print { display: none !important; }
    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
}
</style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 6px; vertical-align: -2px;">
        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h6a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5H5z"/>
    </svg>
    Imprimir / Guardar PDF
</button>

<div class="wrap">
    <!-- Encabezado Corporativo -->
    <div class="header">
        <div class="header-info">
            <div class="empresa">Novitecnologia Cia. Ltda. — Mesa de Ayuda & Soporte SGN</div>
            <div><strong>GYE:</strong> 04-6031337 / 0960500158 &nbsp;&nbsp; <strong>UIO:</strong> 02-6001635 / 0960500156</div>
            <div>soporte@novitec.com.ec &nbsp;·&nbsp; www.novitec.com.ec</div>
        </div>
        <img src="{{ asset('Novitecpdf.png') }}" alt="SGN - Novitec">
    </div>

    <!-- Barra de Título del Ticket -->
    <div class="orden-header {{ $ticket->tipo_ticket === 'sistemas' ? 'sistemas' : '' }}">
        <div class="nro">
            Nro. de Ticket: {{ $ticket->codigo_ticket }}
            <span style="font-size: 7.5pt; font-weight: 500; margin-left: 8px; opacity: 0.9;">
                [{{ $ticket->tipo_ticket === 'sistemas' ? 'SISTEMAS TI (QUITO)' : 'SOPORTE TÉCNICO' }}]
            </span>
        </div>
        <div class="meta">
            Fecha de apertura: {{ $ticket->fecha_apertura ? $ticket->fecha_apertura->format('d/m/Y H:i') : $ticket->created_at->format('d/m/Y H:i') }}<br>
            Estado: <strong>{{ strtoupper($estadoTexto) }}</strong>
        </div>
    </div>

    <!-- Sección 1: Datos del Solicitante / Tienda -->
    <div class="sec-titulo {{ $ticket->tipo_ticket === 'sistemas' ? 'sistemas' : '' }}">1. Datos del Solicitante & Ubicación Origen</div>
    <table class="datos">
        <tr>
            <td width="30%"><span class="lbl">Solicitante</span>{{ $solicitante ? ($solicitante->nombre_tecnico ?: $solicitante->usuario) : ($ticket->solicitante_nombre ?: 'Solicitante') }}</td>
            <td width="22%"><span class="lbl">C.I. / Usuario</span>{{ $solicitante?->usuario ?: '—' }}</td>
            <td width="28%"><span class="lbl">Tienda / Ubicación Origen</span>{{ $ticket->tienda_nombre ?: ($sucursalCliente->nombre ?? 'Tienda Externa') }}</td>
            <td width="20%"><span class="lbl">Empresa Origen</span><strong>{{ $ticket->empresa_origen }}</strong></td>
        </tr>
        <tr>
            <td><span class="lbl">Correo Institucional</span>{{ $solicitante?->correo_tec ?: 'Sin correo registrado' }}</td>
            <td><span class="lbl">Teléfono / WhatsApp</span>{{ $ticket->contacto_telefono ?: ($solicitante?->telefono ?: 'No especificado') }}</td>
            <td><span class="lbl">Departamento / Área</span>{{ $depto ?: 'Operaciones / Tiendas' }}</td>
            <td>
                <span class="lbl">AnyDesk ID</span>
                <strong style="color: #b91c1c; font-family: monospace;">{{ $anydesk ?: 'No registrado' }}</strong>
            </td>
        </tr>
        @if($iconoMba || $userMba)
            <tr>
                <td colspan="2">
                    <span class="lbl">Ícono / URDP Acceso MBA3</span>
                    <strong style="font-family: monospace;">{{ $iconoMba ?: 'No especificado' }}</strong>
                </td>
                <td colspan="2">
                    <span class="lbl">Usuario MBA3 / Vendedor</span>
                    <strong style="font-family: monospace;">{{ $userMba ?: 'No especificado' }}</strong>
                </td>
            </tr>
        @endif
    </table>

    <!-- Sección 2: Técnico Responsable / Soporte Asignado -->
    <div class="sec-titulo {{ $ticket->tipo_ticket === 'sistemas' ? 'sistemas' : '' }}">2. Mesa de Ayuda & Técnico Responsable</div>
    <table class="datos">
        <tr>
            <td width="35%">
                <span class="lbl">Técnico Resolutor Asignado</span>
                <strong>{{ $asignadoA ? ($asignadoA->nombre_tecnico ?: $asignadoA->usuario) : 'Sin asignar (Central Quito)' }}</strong>
            </td>
            <td width="25%"><span class="lbl">Correo del Técnico</span>{{ $asignadoA?->correo_tec ?: 'soporte@novitec.com.ec' }}</td>
            <td width="20%"><span class="lbl">Fecha de Asignación</span>{{ $ticket->fecha_asignacion ? $ticket->fecha_asignacion->format('d/m/Y H:i') : '—' }}</td>
            <td width="20%"><span class="lbl">Primera Respuesta</span>{{ $ticket->fecha_primera_respuesta ? $ticket->fecha_primera_respuesta->format('d/m/Y H:i') : '—' }}</td>
        </tr>
    </table>

    <!-- Sección 3: Ficha del Requerimiento -->
    <div class="sec-titulo {{ $ticket->tipo_ticket === 'sistemas' ? 'sistemas' : '' }}">3. Requerimiento Técnico & Detalle del Problema</div>
    <table class="datos">
        <tr>
            <td width="25%"><span class="lbl">Tipo de Ticket</span>{{ $ticket->tipo_ticket === 'sistemas' ? 'Sistemas TI' : 'Soporte Técnico' }}</td>
            <td width="30%"><span class="lbl">Categoría</span><strong>{{ $ticket->categoria }}</strong></td>
            <td width="20%">
                <span class="lbl">Prioridad</span>
                @php
                    $prioColors = [
                        'urgente' => ['#fee2e2', '#991b1b'],
                        'alta' => ['#fef3c7', '#92400e'],
                        'media' => ['#e0f2fe', '#0369a1'],
                        'baja' => ['#f1f5f9', '#475569'],
                    ];
                    $pCol = $prioColors[$ticket->prioridad] ?? ['#f1f5f9', '#475569'];
                @endphp
                <span class="badge" style="background: {{ $pCol[0] }}; color: {{ $pCol[1] }};">
                    {{ strtoupper($ticket->prioridad) }}
                </span>
            </td>
            <td width="25%"><span class="lbl">Plazo Máximo SLA</span>{{ $ticket->estado === 'en_mba' ? '48 Horas (MBA)' : 'Estándar 24 Horas' }}</td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="lbl">Asunto / Título del Requerimiento</span>
                <strong style="font-size: 8pt;">{{ $ticket->titulo }}</strong>
            </td>
        </tr>
        @if($tieneFichaEspecifica)
            <tr>
                <td colspan="4" style="background: #faf5ff; border: 1.5px solid #c084fc;">
                    <span class="lbl" style="color: #6b21a8; font-weight: 800;">Ficha de Datos del Colaborador / Requerimiento</span>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 2px; font-size: 7pt;">
                        <tr>
                            <td width="35%" style="border:none; padding:1px 0;"><span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Cargo en Sucursal:</span> <strong>{{ $cargoEsp ?: '—' }}</strong></td>
                            <td width="40%" style="border:none; padding:1px 0;"><span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Nombres Completos:</span> <strong>{{ $nombresEsp ?: '—' }}</strong></td>
                            <td width="25%" style="border:none; padding:1px 0;"><span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Cédula:</span> <strong style="font-family:monospace;">{{ $cedulaEsp ?: '—' }}</strong></td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;"><span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Tel. Corporativo:</span> {{ $telefonoEsp ?: ($ticket->contacto_telefono ?: '—') }}</td>
                            <td style="border:none; padding:1px 0;"><span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Correo Tienda:</span> {{ $correoEsp ?: '—' }}</td>
                            <td style="border:none; padding:1px 0;"><span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Sucursal:</span> <strong>{{ $sucursalEsp ?: ($ticket->tienda_nombre ?: '—') }}</strong></td>
                        </tr>
                        @if($grupoMilEsp || $accesoParecidoEsp)
                            <tr>
                                @if($grupoMilEsp)
                                    <td colspan="3" style="border:none; padding:1px 0; border-top: 1px dashed #cbd5e1; margin-top:2px;">
                                        <span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Grupo de Milenium:</span>
                                        <strong>{{ $grupoMilEsp }}</strong>
                                    </td>
                                @endif
                                @if($accesoParecidoEsp)
                                    <td colspan="3" style="border:none; padding:1px 0; border-top: 1px dashed #cbd5e1; margin-top:2px;">
                                        <span style="color:#64748b; font-size:5.5pt; font-weight:700; text-transform:uppercase; display:block;">Acceso Parecido a:</span>
                                        <strong>{{ $accesoParecidoEsp }}</strong>
                                    </td>
                                @endif
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="4">
                <span class="lbl">Detalle Completo del Requerimiento / Solicitud</span>
                <div style="white-space: pre-line; line-height: 1.35; padding: 2px 0;">{{ $ticket->descripcion }}</div>
            </td>
        </tr>
        @if($ticket->adjuntos->isNotEmpty())
            <tr>
                <td colspan="4">
                    <span class="lbl">Evidencias y Archivos Adjuntos Iniciales ({{ $ticket->adjuntos->count() }})</span>
                    <div style="margin-top: 2px; font-size: 6.8pt;">
                        @foreach($ticket->adjuntos as $adj)
                            <span style="display: inline-block; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 1px 6px; border-radius: 3px; margin-right: 4px; margin-bottom: 2px;">
                                📄 {{ $adj->nombre_archivo }} ({{ $adj->tamano_legible }})
                            </span>
                        @endforeach
                    </div>
                </td>
            </tr>
        @endif
    </table>

    <!-- Sección 4: Atención, Escalado & Solución Técnica -->
    @if($ticket->estado === 'en_mba' || $ticket->numero_ticket_mba)
        <div class="sec-titulo mba">4. Escalado a Soporte MBA (SLA 48 Horas)</div>
        <div class="alerta-box mba">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td width="35%"><strong>N° Ticket / Caso MBA:</strong> <span style="font-family: monospace; font-size: 8pt; font-weight: 700;">#{{ $ticket->numero_ticket_mba ?: 'Pendiente' }}</span></td>
                    <td width="35%"><strong>Fecha Escalado:</strong> {{ $ticket->fecha_escalado_mba ? $ticket->fecha_escalado_mba->format('d/m/Y H:i') : '—' }}</td>
                    <td width="30%"><strong>Plazo Máximo:</strong> 48 Horas</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="sec-titulo resolucion">4. Resolución & Solución Técnica Aplicada</div>
    <table class="datos">
        <tr>
            <td width="30%">
                <span class="lbl">Estado de Resolución</span>
                <strong style="color: {{ in_array($ticket->estado, ['resuelto', 'cerrado']) ? '#166534' : '#b45309' }};">
                    {{ strtoupper($estadoTexto) }}
                </strong>
            </td>
            <td width="35%"><span class="lbl">Fecha y Hora de Resolución</span>{{ $ticket->fecha_resolucion ? $ticket->fecha_resolucion->format('d/m/Y H:i:s') : ($ticket->estado === 'resuelto' ? 'Completado' : 'Pendiente') }}</td>
            <td width="35%"><span class="lbl">Resuelto Por</span>{{ $asignadoA ? ($asignadoA->nombre_tecnico ?: $asignadoA->usuario) : 'Mesa de Ayuda' }}</td>
        </tr>
        <tr>
            <td colspan="3">
                <span class="lbl">Comentario / Solución Técnica Registrada</span>
                <div style="white-space: pre-line; line-height: 1.4; padding: 2px 0; font-weight: 600; color: #1e293b;">
                    {{ $ticket->solucion_texto ?: ($ticket->solucion ?: 'Atención técnica en proceso.') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Sección 5: Calificación & Conformidad de la Tienda -->
    @if($ticket->calificacion)
        <div class="sec-titulo alerta">5. Calificación & Satisfacción del Solicitante</div>
        <table class="datos">
            <tr>
                <td width="30%">
                    <span class="lbl">Calificación Otorgada</span>
                    <strong style="color: #b45309; font-size: 8pt;">
                        @for($i = 1; $i <= 5; $i++)
                            {{ $i <= $ticket->calificacion ? '★' : '☆' }}
                        @endfor
                        ({{ $ticket->calificacion }} / 5)
                    </strong>
                </td>
                <td width="35%"><span class="lbl">Fecha de Cierre y Calificación</span>{{ $ticket->fecha_cierre ? $ticket->fecha_cierre->format('d/m/Y H:i') : '—' }}</td>
                <td width="35%">
                    <span class="lbl">Comentario / Reseña</span>
                    <em>"{{ $ticket->comentario_calificacion ?: 'Sin comentario adicional' }}"</em>
                </td>
            </tr>
        </table>
    @endif

    <!-- Sección 6: Historial de Mensajes & Seguimiento Técnico -->
    @if($mensajes->isNotEmpty())
        <div class="sec-titulo">6. Bitácora de Mensajes & Historial de Atención</div>
        <table class="bitacora-tbl">
            <thead>
                <tr>
                    <th style="width: 18%;">Fecha / Hora</th>
                    <th style="width: 25%;">Autor / Usuario</th>
                    <th style="width: 57%;">Detalle del Mensaje / Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mensajes as $msg)
                    @php
                        $esNota = (bool) $msg->es_nota_interna;
                        $esTecnico = ($msg->usuario_id !== $ticket->solicitante_id);
                    @endphp
                    <tr class="{{ $esNota ? 'nota-interna' : ($esTecnico ? 'soporte' : '') }}">
                        <td>{{ $msg->created_at ? $msg->created_at->format('d/m/Y H:i') : '—' }}</td>
                        <td>
                            <strong>{{ $msg->usuario ? ($msg->usuario->nombre_tecnico ?: $msg->usuario->usuario) : 'Sistema' }}</strong>
                            @if($esNota)
                                <span style="font-size: 5.5pt; color: #b45309; font-weight: 700;">[NOTA INTERNA]</span>
                            @endif
                        </td>
                        <td>
                            {{ $msg->mensaje }}
                            @if($msg->cambio_estado)
                                <span style="font-size: 5.8pt; color: #1d4ed8; font-weight: 600;">(Estado: {{ $msg->cambio_estado }})</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Sección 7: Firmas de Conformidad -->
    <div class="firmas">
        <div class="firma-box">
            <div class="firma-linea">
                <strong>{{ $solicitante ? ($solicitante->nombre_tecnico ?: $solicitante->usuario) : 'Solicitante' }}</strong><br>
                <span>Firma Solicitante / Tienda ({{ $ticket->empresa_origen }})</span><br>
                <span style="font-size: 6pt; color: #64748b;">C.I.: {{ $solicitante?->usuario ?: '________________' }}</span>
            </div>
        </div>
        <div class="firma-box">
            <div class="firma-linea">
                <strong>{{ $asignadoA ? ($asignadoA->nombre_tecnico ?: $asignadoA->usuario) : 'Mesa de Ayuda' }}</strong><br>
                <span>Firma Técnico / Sistemas TI</span><br>
                <span style="font-size: 6pt; color: #64748b;">Novitecnología Cia. Ltda.</span>
            </div>
        </div>
    </div>

    <!-- Pie de Página -->
    <div class="foot">
        Documento generado automáticamente por el Sistema de Gestión Novitec (SGN) el {{ now()->format('d/m/Y H:i:s') }} — Página 1 de 1
    </div>
</div>

</body>
</html>
