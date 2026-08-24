@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1300px;">
    <!-- Barra Flotante de Llamada Activa WebRTC -->
    <div id="call-active-bar" class="card border-0 shadow-lg rounded-4 p-3 bg-dark text-white mb-4 animate__animated animate__fadeInDown" style="display: none !important;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-telephone-inbound-fill fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6 mb-0 d-flex align-items-center gap-2">
                        <span id="call-partner-name">Soporte Técnico</span>
                        <span class="badge bg-danger rounded-pill px-2 py-0" style="font-size: 0.65rem;">EN VIVO</span>
                    </div>
                    <div class="text-white-50 small d-flex align-items-center gap-2">
                        <i class="bi bi-stopwatch"></i> <span id="call-timer-display" class="font-monospace fw-bold text-white">00:00</span>
                        <span class="text-success small fw-semibold ms-2"><i class="bi bi-shield-check me-1"></i>Audio HD Seguro</span>
                    </div>
                </div>
            </div>

            <!-- Controles de Llamada -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" id="btn-call-mute" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2" onclick="toggleMuteCall()">
                    <i class="bi bi-mic-fill" id="ico-call-mute"></i> <span id="txt-call-mute">Silenciar</span>
                </button>
                <button type="button" id="btn-call-screen" class="btn btn-outline-info rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2" onclick="toggleScreenShare()">
                    <i class="bi bi-display"></i> <span id="txt-call-screen">Compartir Pantalla</span>
                </button>
                <button type="button" class="btn btn-danger rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow" onclick="colgarLlamada()">
                    <i class="bi bi-telephone-x-fill"></i> Colgar
                </button>
            </div>
        </div>

        <!-- Visor de Pantalla Compartida Remota -->
        <div id="remote-screen-wrapper" class="mt-3 pt-3 border-top border-secondary" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-semibold text-white-50"><i class="bi bi-display me-1"></i> Transmisión de Pantalla en Vivo</span>
                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 small" onclick="toggleFullScreenVideo()">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Pantalla Completa
                </button>
            </div>
            <video id="remote-video" autoplay playsinline class="w-100 rounded-3 border border-secondary shadow" style="max-height: 480px; background: #000;"></video>
        </div>
        <audio id="remote-audio" autoplay></audio>
    </div>

    <!-- Migas de pan y encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('mistickets.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
                <i class="bi bi-arrow-left"></i> Volver a Mis Solicitudes
            </a>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h2 class="h3 fw-bold text-dark mb-0 font-monospace">{{ $ticket->codigo_ticket }}</h2>
                
                @if($ticket->tipo_ticket === 'sistemas')
                    <span class="badge bg-purple text-white px-3 py-1 rounded-pill fw-bold" style="background: #7c3aed; font-size: 0.8rem;">
                        <i class="bi bi-cpu-fill me-1"></i> Sistemas TI (Quito)
                    </span>
                @else
                    <span class="badge bg-primary px-3 py-1 rounded-pill fw-bold" style="font-size: 0.8rem;">
                        <i class="bi bi-tools me-1"></i> Soporte Técnico
                    </span>
                @endif

                @if($ticket->estado === 'abierto')
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-semibold">Abierto</span>
                @elseif($ticket->estado === 'en_atencion')
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill fw-semibold">En Atención</span>
                @elseif($ticket->estado === 'en_espera')
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1 rounded-pill fw-semibold">En Espera</span>
                @elseif($ticket->estado === 'resuelto')
                    <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold">✓ Resuelto</span>
                @elseif($ticket->estado === 'cerrado')
                    <span class="badge bg-dark text-white px-3 py-1 rounded-pill fw-semibold">Cerrado</span>
                @elseif($ticket->estado === 'cancelado')
                    <span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-semibold">Cancelado</span>
                @endif
            </div>
        </div>

        <!-- Acciones rápidas (Calificar si está resuelto) -->
        <div class="d-flex gap-2">
            @if($ticket->estado === 'resuelto' && !$ticket->calificacion)
                <button type="button" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="abrirModalCalificar()">
                    <i class="bi bi-star-fill text-warning"></i> Calificar Atención
                </button>
            @endif
        </div>
    </div>

    <!-- Alerta de Resolución si está resuelto o cerrado -->
    @if(in_array($ticket->estado, ['resuelto', 'cerrado']))
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #f0fdf4; border: 1.5px solid #86efac !important;">
            <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bi bi-patch-check-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-bold text-success mb-0">¡Ticket Resuelto por Soporte!</h5>
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 small">✓ Finalizado</span>
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-calendar-check me-1"></i>Fecha de resolución: <strong>{{ $ticket->fecha_resolucion ? $ticket->fecha_resolucion->format('d/m/Y H:i:s') : ($ticket->updated_at ? $ticket->updated_at->format('d/m/Y H:i:s') : 'Hoy') }}</strong>
                            @if($ticket->asignadoA)
                                &nbsp;·&nbsp; <i class="bi bi-person-badge me-1"></i>Técnico: <strong>{{ $ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario }}</strong>
                            @endif
                        </div>
                        <div class="bg-white p-3 rounded-3 border border-success border-opacity-25 text-dark small shadow-sm" style="white-space: pre-line; line-height: 1.6;">
                            <strong class="text-success d-block mb-1"><i class="bi bi-check2-circle me-1"></i>Solución / Comentario Técnico:</strong>
                            {{ $ticket->solucion_texto ?: ($ticket->solucion ?: 'El equipo técnico de Quito ha concluido la atención de tu requerimiento satisfactoriamente.') }}
                        </div>
                    </div>
                </div>

                @if(!$ticket->calificacion)
                    <button type="button" class="btn btn-success fw-bold rounded-3 px-3 py-2 text-nowrap shadow-sm" onclick="abrirModalCalificar()">
                        <i class="bi bi-star-fill text-warning me-1"></i> Calificar Atención
                    </button>
                @else
                    <div class="bg-white p-3 rounded-3 border text-center flex-shrink-0 shadow-sm" style="min-width: 160px;">
                        <div class="small text-muted fw-semibold mb-1">Tu Calificación</div>
                        <div class="text-warning fs-5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $ticket->calificacion ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Columna Izquierda: Detalle Inicial + Chat en Tiempo Real -->
        <div class="col-12 col-lg-8">
            <!-- Tarjeta con Descripción Inicial -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-muted small fw-semibold">Descripción del Requerimiento</span>
                    <span class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $ticket->fecha_apertura ? $ticket->fecha_apertura->format('d/m/Y H:i:s') : $ticket->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <h4 class="fw-bold text-dark mb-3">{{ $ticket->titulo }}</h4>
                <div class="text-dark mb-3" style="white-space: pre-line; line-height: 1.6;">{{ $ticket->descripcion }}</div>

                <!-- Adjuntos iniciales del ticket -->
                @php
                    $adjuntosIniciales = $ticket->adjuntos->whereNull('mensaje_id');
                @endphp
                @if($adjuntosIniciales->isNotEmpty())
                    <div class="border-top pt-3 mt-3">
                        <div class="fw-semibold text-dark small mb-2"><i class="bi bi-paperclip me-1"></i> Evidencias adjuntas iniciales ({{ $adjuntosIniciales->count() }}):</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($adjuntosIniciales as $adj)
                                @php
                                    $esImg = str_starts_with($adj->tipo_mime ?? '', 'image/');
                                @endphp
                                <a href="{{ asset('storage/' . $adj->ruta_archivo) }}" target="_blank" class="btn btn-sm btn-light border rounded-3 d-inline-flex align-items-center gap-2 p-2 text-decoration-none">
                                    <i class="bi {{ $esImg ? 'bi-file-earmark-image text-primary' : 'bi-file-earmark-pdf text-danger' }} fs-5"></i>
                                    <div class="text-start">
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 180px;">{{ $adj->nombre_original }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ number_format(($adj->tamano_bytes ?? 0) / 1024, 1) }} KB</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- CHAT EN TIEMPO REAL -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <!-- Cabecera del Chat en Vivo -->
                <div class="p-3 px-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-dots-fill text-primary fs-5"></i>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Chat en Vivo con Soporte Quito</h6>
                            <span class="text-muted" style="font-size: 0.75rem;">Canal directo en tiempo real</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status"></span>
                            En Vivo
                        </span>
                    </div>
                </div>

                <!-- Contenedor del Historial de Mensajes -->
                <div id="chat-stream" class="p-4 d-flex flex-column gap-3" style="max-height: 480px; min-height: 260px; overflow-y: auto; background: #fdfdfd;">
                    @php $ultimoId = 0; @endphp
                    @forelse($ticket->mensajes as $msg)
                        @if($msg->es_nota_interna)
                            @continue
                        @endif
                        @php
                            if ($msg->id > $ultimoId) $ultimoId = $msg->id;
                            $esMio = (int) $msg->usuario_id === (int) $usuario->id;
                            $esSolicitante = (int) $msg->usuario_id === (int) $ticket->solicitante_id;
                        @endphp

                        <div class="d-flex gap-3 {{ $esMio ? 'flex-row-reverse' : '' }}" id="msg-{{ $msg->id }}">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0 shadow-sm" 
                                 style="width: 36px; height: 36px; font-size: 0.8rem; background: {{ $esMio ? '#2563eb' : '#059669' }};">
                                {{ strtoupper(substr($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : 'S', 0, 1)) }}
                            </div>
                            <div class="card border-0 p-3 rounded-4 shadow-sm" style="max-width: 80%; background: {{ $esMio ? '#eff6ff' : '#ffffff' }}; border: 1px solid {{ $esMio ? '#bfdbfe' : '#e2e8f0' }} !important;">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                    <span class="fw-bold small {{ $esMio ? 'text-primary' : 'text-success' }}">
                                        {{ $esMio ? 'Tú (' . ($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : '') . ')' : ($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : 'Soporte SGN') }}
                                        @if(!$esMio) <span class="badge bg-success bg-opacity-10 text-success ms-1">Soporte Técnico</span> @endif
                                    </span>
                                    <span class="text-muted" style="font-size: 0.72rem;">{{ $msg->created_at ? $msg->created_at->format('H:i') : '' }}</span>
                                </div>
                                <div class="text-dark small" style="white-space: pre-line; line-height: 1.5;">{{ $msg->mensaje }}</div>

                                <!-- Adjuntos del mensaje -->
                                @if($msg->adjuntos && $msg->adjuntos->isNotEmpty())
                                    <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2">
                                        @foreach($msg->adjuntos as $adj)
                                            @php $esImg = str_starts_with($adj->tipo_mime ?? '', 'image/'); @endphp
                                            @if($esImg)
                                                <a href="{{ asset('storage/' . $adj->ruta_archivo) }}" target="_blank" class="d-block mt-1">
                                                    <img src="{{ asset('storage/' . $adj->ruta_archivo) }}" class="rounded-3 border shadow-sm" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $adj->ruta_archivo) }}" target="_blank" class="btn btn-sm btn-white bg-white border rounded-2 p-1 px-2 d-inline-flex align-items-center gap-1 text-decoration-none">
                                                    <i class="bi bi-paperclip text-muted"></i>
                                                    <span class="small fw-semibold text-dark text-truncate" style="max-width: 160px;">{{ $adj->nombre_original }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div id="chat-empty-msg" class="text-center text-muted py-5">
                            <i class="bi bi-chat-heart fs-1 d-block mb-2 opacity-50 text-primary"></i>
                            El chat está abierto. Escribe cualquier mensaje o duda para comunicarte con el equipo de soporte.
                        </div>
                    @endforelse
                </div>

                <!-- Barra de Entrada de Mensaje -->
                @if($ticket->estado !== 'cerrado' && $ticket->estado !== 'cancelado')
                    <div class="p-3 bg-light border-top">
                        <!-- Vista previa de archivos adjuntos / imagen pegada -->
                        <div id="preview-adjuntos" class="d-flex flex-wrap gap-2 mb-2" style="display: none !important;"></div>

                        <form id="form-chat-enviar" onsubmit="enviarMensajeChat(event)">
                            @csrf
                            <div class="input-group bg-white rounded-4 border p-1 shadow-sm">
                                <label class="btn btn-link text-muted p-2 px-3 m-0" title="Adjuntar archivo o imagen" style="cursor: pointer;">
                                    <i class="bi bi-paperclip fs-5"></i>
                                    <input type="file" id="chat-file-input" multiple class="d-none" accept="image/*,.pdf,.doc,.docx,.txt,.zip" onchange="onArchivosSeleccionados(this)">
                                </label>
                                <textarea id="chat-input-texto" rows="1" class="form-control border-0 shadow-none px-2 py-2" placeholder="Escribe un mensaje aquí... (Ctrl+V para pegar captura)" style="resize: none; font-size: 0.92rem;" onkeydown="onKeyDownChat(event)"></textarea>
                                <button type="submit" id="btn-chat-submit" class="btn btn-primary rounded-4 px-4 m-1 fw-bold shadow-sm d-flex align-items-center gap-1">
                                    <i class="bi bi-send-fill"></i> <span class="d-none d-sm-inline">Enviar</span>
                                </button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-2 text-muted" style="font-size: 0.72rem;">
                            <span><i class="bi bi-info-circle me-1"></i>Pulsa <strong>Enter</strong> para enviar, <strong>Shift+Enter</strong> para salto de línea.</span>
                            <span><i class="bi bi-clipboard me-1"></i>Puedes pegar capturas con <strong>Ctrl+V</strong></span>
                        </div>
                    </div>
                @else
                    <div class="p-3 text-center bg-light text-muted small border-top">
                        <i class="bi bi-lock-fill me-1"></i> Este ticket está {{ $ticket->estado }}. El chat ha sido archivado.
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Resumen de Ficha Técnica -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold text-dark mb-3 text-uppercase small"><i class="bi bi-info-circle me-1 text-primary"></i> Ficha del Ticket</h6>
                
                <div class="d-flex flex-column gap-3 small">
                    <div>
                        <div class="text-muted">Código:</div>
                        <div class="fw-bold fs-6 text-dark font-monospace">{{ $ticket->codigo_ticket }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Tipo de Ticket:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->tipo_ticket === 'sistemas' ? '⚡ Sistemas TI (Quito)' : '🛠️ Soporte Técnico' }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Categoría:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->categoria }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Prioridad:</div>
                        <div class="fw-semibold">
                            @if($ticket->prioridad === 'urgente')
                                <span class="text-danger fw-bold"><i class="bi bi-fire me-1"></i>Urgente</span>
                            @elseif($ticket->prioridad === 'alta')
                                <span class="text-warning fw-bold">Alta</span>
                            @elseif($ticket->prioridad === 'media')
                                <span class="text-info fw-bold">Media</span>
                            @else
                                <span class="text-muted">Baja</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-muted">Tienda / Ubicación:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->tienda_nombre ?: ($ticket->sucursalCliente->nombre ?? 'Tienda Externa') }} ({{ $ticket->empresa_origen }})</div>
                    </div>
                    <div>
                        <div class="text-muted">Técnico / Asignado:</div>
                        <div class="fw-semibold text-dark" id="ficha-asignado-nombre">
                            {{ $ticket->asignadoA ? ($ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario) : 'Por asignar (Mesa de Ayuda Quito)' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-muted">Fecha de Apertura:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->fecha_apertura ? $ticket->fecha_apertura->format('d/m/Y H:i:s') : '-' }}</div>
                    </div>
                    @if($ticket->fecha_resolucion)
                        <div>
                            <div class="text-muted">Fecha de Resolución:</div>
                            <div class="fw-semibold text-success">{{ $ticket->fecha_resolucion->format('d/m/Y H:i:s') }}</div>
                        </div>
                    @endif
                    @if($ticket->calificacion)
                        <div>
                            <div class="text-muted">Calificación de Atención:</div>
                            <div class="text-warning fs-5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $ticket->calificacion ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                                <span class="text-muted small ms-1">({{ $ticket->calificacion }}/5)</span>
                            </div>
                            @if($ticket->comentario_calificacion)
                                <div class="text-muted small fst-italic mt-1">"{{ $ticket->comentario_calificacion }}"</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let ultimoMensajeId = {{ $ultimoId }};
let archivosAdjuntosList = [];
const ticketId = {{ $ticket->id }};
const syncUrl = "{{ route('tickets.chat.sync', $ticket->id) }}";
const enviarUrl = "{{ route('tickets.chat.enviar', $ticket->id) }}";
let ringtoneAudio = null;

// Audio Ringtone Synthesizer
function startRingtone() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc1 = ctx.createOscillator();
        const osc2 = ctx.createOscillator();
        const gain = ctx.createGain();

        osc1.type = 'sine';
        osc2.type = 'sine';
        osc1.frequency.setValueAtTime(440, ctx.currentTime);
        osc2.frequency.setValueAtTime(480, ctx.currentTime);

        gain.gain.setValueAtTime(0.1, ctx.currentTime);
        osc1.connect(gain);
        osc2.connect(gain);
        gain.connect(ctx.destination);

        osc1.start();
        osc2.start();

        ringtoneAudio = { ctx, osc1, osc2, gain };
    } catch(e) {}
}

function stopRingtone() {
    if (ringtoneAudio) {
        try {
            ringtoneAudio.osc1.stop();
            ringtoneAudio.osc2.stop();
            ringtoneAudio.ctx.close();
        } catch(e) {}
        ringtoneAudio = null;
    }
}

// Audio Chime Synthesizer
function playChatChime() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
        gain.gain.setValueAtTime(0.12, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.35);
    } catch(e) {}
}

function scrollToBottom() {
    const stream = document.getElementById('chat-stream');
    if (stream) {
        stream.scrollTop = stream.scrollHeight;
    }
}

function renderMessage(m) {
    const emptyMsg = document.getElementById('chat-empty-msg');
    if (emptyMsg) emptyMsg.remove();

    if (document.getElementById(`msg-${m.id}`)) return;

    const stream = document.getElementById('chat-stream');
    const isMine = m.es_propio;
    const initial = (m.autor_nombre || 'S').charAt(0).toUpperCase();

    let adjuntosHtml = '';
    if (m.adjuntos && m.adjuntos.length > 0) {
        adjuntosHtml = `<div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2">`;
        m.adjuntos.forEach(a => {
            if (a.es_imagen) {
                adjuntosHtml += `<a href="${a.url}" target="_blank" class="d-block mt-1"><img src="${a.url}" class="rounded-3 border shadow-sm" style="max-height: 140px; max-width: 100%; object-fit: contain;"></a>`;
            } else {
                adjuntosHtml += `<a href="${a.url}" target="_blank" class="btn btn-sm btn-white bg-white border rounded-2 p-1 px-2 d-inline-flex align-items-center gap-1 text-decoration-none"><i class="bi bi-paperclip text-muted"></i><span class="small fw-semibold text-dark text-truncate" style="max-width: 160px;">${a.nombre_original}</span></a>`;
            }
        });
        adjuntosHtml += `</div>`;
    }

    const html = `
        <div class="d-flex gap-3 ${isMine ? 'flex-row-reverse' : ''} animate__animated animate__fadeInUp" id="msg-${m.id}" style="animation-duration: 0.25s;">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0 shadow-sm" 
                 style="width: 36px; height: 36px; font-size: 0.8rem; background: ${isMine ? '#2563eb' : '#059669'};">
                ${initial}
            </div>
            <div class="card border-0 p-3 rounded-4 shadow-sm" style="max-width: 80%; background: ${isMine ? '#eff6ff' : '#ffffff'}; border: 1px solid ${isMine ? '#bfdbfe' : '#e2e8f0'} !important;">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                    <span class="fw-bold small ${isMine ? 'text-primary' : 'text-success'}">
                        ${isMine ? 'Tú (' + m.autor_nombre + ')' : m.autor_nombre}
                        ${!isMine ? '<span class="badge bg-success bg-opacity-10 text-success ms-1">Soporte Técnico</span>' : ''}
                    </span>
                    <span class="text-muted" style="font-size: 0.72rem;">${m.hora || ''}</span>
                </div>
                <div class="text-dark small" style="white-space: pre-line; line-height: 1.5;">${m.mensaje || ''}</div>
                ${adjuntosHtml}
            </div>
        </div>
    `;

    stream.insertAdjacentHTML('beforeend', html);
}

// Sincronización en Tiempo Real de Chat & Llamadas
async function syncChatLoop() {
    try {
        const res = await fetch(`${syncUrl}?last_id=${ultimoMensajeId}`);
        const data = await res.json();
        if (data.ok && data.mensajes && data.mensajes.length > 0) {
            let hasIncoming = false;
            data.mensajes.forEach(m => {
                if (m.id > ultimoMensajeId) {
                    ultimoMensajeId = m.id;
                    if (!document.getElementById(`msg-${m.id}`)) {
                        renderMessage(m);
                        if (!m.es_propio) hasIncoming = true;
                    }
                }
            });
            scrollToBottom();
            if (hasIncoming) {
                playChatChime();
            }
        }

        // Consultar estado de llamada entrante
        checkLlamadaEstado();
    } catch (e) {
        console.warn('Sync tick error:', e);
    }
}

// WebRTC Engine Variables (Receptor - Tienda)
let peerConnection = null;
let localStream = null;
let screenStream = null;
let videoSender = null;
let isCallActive = false;
let callTimerInterval = null;
let callSeconds = 0;
let currentLlamadaId = null;
let processedIceCandidates = new Set();

const rtcConfig = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' },
        { urls: 'stun:stun3.l.google.com:19302' },
        { urls: 'stun:global.stun.twilio.com:3478' }
    ]
};

// WebRTC Engine para el Solicitante (Receptor)
async function checkLlamadaEstado() {
    try {
        const res = await fetch("{{ route('tickets.llamada.estado', $ticket->id) }}");
        const data = await res.json();
        if (!data.ok || !data.hay_llamada) return;

        if (data.estado === 'timbrando' && !data.es_iniciador && !isCallActive) {
            currentLlamadaId = data.llamada_id;
            mostrarModalLlamadaEntrante(data.iniciador_nombre, data.offer);
        } else if (data.estado === 'finalizada' && isCallActive) {
            Swal.fire({
                title: 'Llamada Finalizada',
                text: 'El soporte técnico ha finalizado la llamada.',
                icon: 'info',
                timer: 3000,
                showConfirmButton: false
            });
            cerrarLlamadaLocal();
        }

        // Agregar candidatos ICE del iniciador (técnico) durante la llamada
        if (isCallActive && peerConnection && peerConnection.remoteDescription && data.ice_peer && Array.isArray(data.ice_peer)) {
            for (const cand of data.ice_peer) {
                if (!cand) continue;
                const candKey = typeof cand === 'string' ? cand : (cand.candidate || JSON.stringify(cand));
                if (!processedIceCandidates.has(candKey)) {
                    processedIceCandidates.add(candKey);
                    try {
                        const candObj = typeof cand === 'string' ? JSON.parse(cand) : cand;
                        await peerConnection.addIceCandidate(new RTCIceCandidate(candObj));
                    } catch(err) {
                        console.warn('Error adding ICE candidate:', err);
                    }
                }
            }
        }
    } catch(e) {}
}

let incomingCallSwal = null;
function mostrarModalLlamadaEntrante(nombreIniciador, offerSdp) {
    if (incomingCallSwal) return;
    startRingtone();

    incomingCallSwal = Swal.fire({
        title: '📞 ¡Llamada de Soporte Entrante!',
        html: `
            <div class="py-2">
                <div class="spinner-grow text-success mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5 class="fw-bold text-dark mb-1">${nombreIniciador}</h5>
                <p class="text-muted small mb-0">El equipo técnico de Quito te está llamando para resolver este requerimiento en tiempo real.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '✅ Contestar Llamada',
        cancelButtonText: '❌ Rechazar',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#dc2626',
        allowOutsideClick: false,
    }).then(async (result) => {
        stopRingtone();
        incomingCallSwal = null;

        if (result.isConfirmed) {
            await contestarLlamadaWebRTC(offerSdp);
        } else {
            fetch("{{ route('tickets.llamada.rechazar', $ticket->id) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
        }
    });
}

async function contestarLlamadaWebRTC(offerSdp) {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
    } catch (err) {
        let msg = 'Debes dar clic en "Permitir" cuando el navegador te solicite permiso de micrófono para contestar la llamada.';
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            msg = 'El navegador bloqueó el micrófono porque la web está en HTTP. Ingresa usando HTTPS: https://novitec.com.ec/sgn/ o activa el permiso en la configuración del sitio.';
        }
        Swal.fire({
            title: 'Permiso de Micrófono',
            text: msg,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    processedIceCandidates.clear();

    try {
        peerConnection = new RTCPeerConnection(rtcConfig);
        localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
        });

        // Pre-configurar transceiver de video
        const videoTransceiver = peerConnection.addTransceiver('video', { direction: 'sendrecv' });
        videoSender = videoTransceiver.sender;

        peerConnection.ontrack = (event) => {
            console.log('[WebRTC Tienda] Track recibido:', event.track.kind, event.streams);
            if (event.track.kind === 'video') {
                const remoteVid = document.getElementById('remote-video');
                const screenWrap = document.getElementById('remote-screen-wrapper');
                if (remoteVid) {
                    remoteVid.srcObject = event.streams[0] || new MediaStream([event.track]);
                    remoteVid.play().catch(e => console.warn('Video play error:', e));
                }
                if (screenWrap) screenWrap.style.display = 'block';

                event.track.onended = () => {
                    if (screenWrap) screenWrap.style.display = 'none';
                };
            } else if (event.track.kind === 'audio') {
                const remoteAud = document.getElementById('remote-audio');
                if (remoteAud) {
                    remoteAud.srcObject = event.streams[0] || new MediaStream([event.track]);
                    remoteAud.muted = false;
                    remoteAud.play().catch(e => console.warn('Audio play error:', e));
                }
            }
        };

        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                fetch("{{ route('tickets.llamada.ice', $ticket->id) }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ ice: event.candidate.toJSON ? event.candidate.toJSON() : event.candidate })
                }).catch(() => {});
            }
        };

        try {
            const offObj = typeof offerSdp === 'string' ? JSON.parse(offerSdp) : offerSdp;
            await peerConnection.setRemoteDescription(new RTCSessionDescription(offObj));
        } catch(e) {
            console.error('Error setting remote description for offer:', e);
        }
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);

        await fetch("{{ route('tickets.llamada.contestar', $ticket->id) }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ answer: JSON.stringify(answer) })
        });

        iniciarLlamadaUI();
    } catch (err) {
        Swal.fire('Error al Conectar', err.message, 'error');
        cerrarLlamadaLocal();
    }
}

function iniciarLlamadaUI() {
    isCallActive = true;
    const callBar = document.getElementById('call-active-bar');
    if (callBar) callBar.style.setProperty('display', 'block', 'important');

    callSeconds = 0;
    callTimerInterval = setInterval(() => {
        callSeconds++;
        const m = String(Math.floor(callSeconds / 60)).padStart(2, '0');
        const s = String(callSeconds % 60).padStart(2, '0');
        const timerDisp = document.getElementById('call-timer-display');
        if (timerDisp) timerDisp.textContent = `${m}:${s}`;
    }, 1000);
}

function toggleMuteCall() {
    if (!localStream) return;
    const audioTrack = localStream.getAudioTracks()[0];
    if (audioTrack) {
        audioTrack.enabled = !audioTrack.enabled;
        const btnMute = document.getElementById('btn-call-mute');
        const ico = document.getElementById('ico-call-mute');
        const txt = document.getElementById('txt-call-mute');

        if (audioTrack.enabled) {
            btnMute.className = 'btn btn-outline-light rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2';
            ico.className = 'bi bi-mic-fill';
            txt.textContent = 'Silenciar';
        } else {
            btnMute.className = 'btn btn-warning rounded-pill px-3 py-2 fw-bold text-dark d-flex align-items-center gap-2';
            ico.className = 'bi bi-mic-mute-fill';
            txt.textContent = 'Silenciado';
        }
    }
}

async function toggleScreenShare() {
    if (!peerConnection) return;

    if (!screenStream) {
        try {
            screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: false });
            const screenTrack = screenStream.getVideoTracks()[0];

            if (videoSender) {
                await videoSender.replaceTrack(screenTrack);
            } else {
                videoSender = peerConnection.addTrack(screenTrack, screenStream);
            }

            screenTrack.onended = async () => {
                if (videoSender) await videoSender.replaceTrack(null);
                screenStream = null;
                const txt = document.getElementById('txt-call-screen');
                if (txt) txt.textContent = 'Compartir Pantalla';
            };

            const txt = document.getElementById('txt-call-screen');
            if (txt) txt.textContent = 'Dejar de Compartir';
        } catch(err) {
            console.warn('Screen share canceled:', err);
        }
    } else {
        screenStream.getTracks().forEach(t => t.stop());
        if (videoSender) await videoSender.replaceTrack(null);
        screenStream = null;
        const txt = document.getElementById('txt-call-screen');
        if (txt) txt.textContent = 'Compartir Pantalla';
    }
}

function toggleFullScreenVideo() {
    const vid = document.getElementById('remote-video');
    if (vid.requestFullscreen) vid.requestFullscreen();
}

async function colgarLlamada() {
    fetch("{{ route('tickets.llamada.finalizar', $ticket->id) }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });
    cerrarLlamadaLocal();
}

function cerrarLlamadaLocal() {
    isCallActive = false;
    stopRingtone();
    if (callTimerInterval) clearInterval(callTimerInterval);

    if (localStream) {
        localStream.getTracks().forEach(t => t.stop());
        localStream = null;
    }
    if (screenStream) {
        screenStream.getTracks().forEach(t => t.stop());
        screenStream = null;
    }
    if (videoSender) {
        videoSender = null;
    }
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
    }

    const callBar = document.getElementById('call-active-bar');
    if (callBar) callBar.style.setProperty('display', 'none', 'important');
    const screenWrap = document.getElementById('remote-screen-wrapper');
    if (screenWrap) screenWrap.style.display = 'none';
}

// Enviar Mensaje
async function enviarMensajeChat(e) {
    e.preventDefault();
    const txtInput = document.getElementById('chat-input-texto');
    const texto = txtInput.value.trim();
    const btnSubmit = document.getElementById('btn-chat-submit');

    if (!texto && archivosAdjuntosList.length === 0) return;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    const formData = new FormData();
    formData.append('mensaje', texto);
    formData.append('_token', '{{ csrf_token() }}');

    archivosAdjuntosList.forEach(file => {
        formData.append('archivos[]', file);
    });

    try {
        const res = await fetch(enviarUrl, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.ok && data.mensaje) {
            txtInput.value = '';
            txtInput.style.height = 'auto';
            limpiarAdjuntosPreview();
            if (data.mensaje.id > ultimoMensajeId) {
                ultimoMensajeId = data.mensaje.id;
            }
            renderMessage(data.mensaje);
            scrollToBottom();
        } else {
            Swal.fire('Error', data.error || 'No se pudo enviar el mensaje', 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'Problema de conexión al enviar', 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="bi bi-send-fill"></i> <span class="d-none d-sm-inline">Enviar</span>';
        txtInput.focus();
    }
}

function onKeyDownChat(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('form-chat-enviar').dispatchEvent(new Event('submit'));
    }
}

// Soporte de Captura pegada (Ctrl + V)
window.addEventListener('paste', e => {
    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    for (let item of items) {
        if (item.type.indexOf('image') !== -1) {
            const blob = item.getAsFile();
            archivosAdjuntosList.push(blob);
            actualizarPreviewAdjuntos();
        }
    }
});

function onArchivosSeleccionados(input) {
    if (input.files) {
        for (let file of input.files) {
            archivosAdjuntosList.push(file);
        }
        actualizarPreviewAdjuntos();
    }
}

function actualizarPreviewAdjuntos() {
    const prev = document.getElementById('preview-adjuntos');
    if (!prev) return;
    if (archivosAdjuntosList.length === 0) {
        prev.style.setProperty('display', 'none', 'important');
        prev.innerHTML = '';
        return;
    }

    prev.style.setProperty('display', 'flex', 'important');
    prev.innerHTML = '';

    archivosAdjuntosList.forEach((f, idx) => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-white text-dark border p-2 rounded-3 d-inline-flex align-items-center gap-2 shadow-sm';
        badge.innerHTML = `
            <i class="bi bi-image text-primary"></i>
            <span class="small fw-semibold text-truncate" style="max-width: 140px;">${f.name || 'Captura de pantalla'}</span>
            <button type="button" class="btn-close btn-close-xs" style="font-size: 0.6rem;" onclick="eliminarAdjunto(${idx})"></button>
        `;
        prev.appendChild(badge);
    });
}

function eliminarAdjunto(idx) {
    archivosAdjuntosList.splice(idx, 1);
    actualizarPreviewAdjuntos();
}

function limpiarAdjuntosPreview() {
    archivosAdjuntosList = [];
    actualizarPreviewAdjuntos();
    const fileInput = document.getElementById('chat-file-input');
    if (fileInput) fileInput.value = '';
}

function abrirModalCalificar() {
    Swal.fire({
        title: 'Calificar Atención del Ticket',
        html: `
            <div style="text-align: left; font-size: 0.9rem;">
                <p class="text-muted mb-3">Tu opinión nos ayuda a mejorar el servicio de soporte y sistemas.</p>
                <div class="mb-3">
                    <label class="fw-bold mb-1">¿Cómo calificarías la atención recibida?</label>
                    <select id="swal-calificacion" class="swal2-input" style="width: 100%; margin: 0;">
                        <option value="5">⭐⭐⭐⭐⭐ Excelente (5 estrellas)</option>
                        <option value="4">⭐⭐⭐⭐ Muy Bueno (4 estrellas)</option>
                        <option value="3">⭐⭐⭐ Regular / Aceptable (3 estrellas)</option>
                        <option value="2">⭐⭐ Insatisfecho (2 estrellas)</option>
                        <option value="1">⭐ Muy Malo (1 estrella)</option>
                    </select>
                </div>
                <div>
                    <label class="fw-bold mb-1">Comentarios adicionales (Opcional):</label>
                    <textarea id="swal-comentario" class="swal2-textarea" placeholder="Escribe tu opinión o sugerencia..." style="width: 100%; margin: 0; height: 80px;"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar Calificación y Cerrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
        preConfirm: () => {
            const calificacion = document.getElementById('swal-calificacion').value;
            const comentario = document.getElementById('swal-comentario').value;
            return { calificacion, comentario };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.showLoading();
            fetch("{{ route('mistickets.calificar', $ticket->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(result.value)
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    Swal.fire('¡Gracias!', res.mensaje, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'No se pudo registrar la calificación', 'error'));
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
    setInterval(syncChatLoop, 1800);
});
</script>
@endsection
