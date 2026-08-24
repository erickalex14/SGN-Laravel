@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1400px;">
    <!-- Barra Flotante de Llamada Activa WebRTC -->
    <div id="call-active-bar" class="card border-0 shadow-lg rounded-4 p-3 bg-dark text-white mb-4 animate__animated animate__fadeInDown" style="display: none !important;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-headset fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6 mb-0 d-flex align-items-center gap-2">
                        <span>Llamada con: {{ $ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario }}</span>
                        <span class="badge bg-danger rounded-pill px-2 py-0" style="font-size: 0.65rem;">EN VIVO</span>
                    </div>
                    <div class="text-white-50 small d-flex align-items-center gap-2">
                        <i class="bi bi-stopwatch"></i> <span id="call-timer-display" class="font-monospace fw-bold text-white">00:00</span>
                        <span class="text-success small fw-semibold ms-2"><i class="bi bi-shield-check me-1"></i>Voz HD & WebRTC Seguro</span>
                    </div>
                </div>
            </div>

            <!-- Controles de Llamada -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" id="btn-call-mute" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2" onclick="toggleMuteCall()">
                    <i class="bi bi-mic-fill" id="ico-call-mute"></i> <span id="txt-call-mute">Silenciar</span>
                </button>
                <button type="button" id="btn-call-screen" class="btn btn-outline-info rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2" onclick="toggleScreenShare()">
                    <i class="bi bi-display"></i> <span id="txt-call-screen">Compartir Mi Pantalla</span>
                </button>
                <button type="button" class="btn btn-danger rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow" onclick="colgarLlamada()">
                    <i class="bi bi-telephone-x-fill"></i> Finalizar Llamada
                </button>
            </div>
        </div>

        <!-- Visor de Pantalla Compartida Remota (De la Tienda) -->
        <div id="remote-screen-wrapper" class="mt-3 pt-3 border-top border-secondary" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-semibold text-warning"><i class="bi bi-display me-1"></i> Pantalla en Vivo de la Tienda</span>
                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 small" onclick="toggleFullScreenVideo()">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Pantalla Completa
                </button>
            </div>
            <video id="remote-video" autoplay playsinline class="w-100 rounded-3 border border-secondary shadow" style="max-height: 540px; background: #000;"></video>
        </div>
        <audio id="remote-audio" autoplay></audio>
    </div>

    <!-- Encabezado con acciones -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-4 rounded-4 shadow-sm border">
        <div>
            <a href="{{ route('tickets.gestion') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
                <i class="bi bi-arrow-left"></i> Volver a la Mesa de Ayuda
            </a>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h2 class="h3 fw-bold text-dark mb-0 font-monospace">{{ $ticket->codigo_ticket }}</h2>
                
                @if($ticket->tipo_ticket === 'sistemas')
                    <span class="badge bg-purple text-white px-3 py-1 rounded-pill fw-bold" style="background: #7c3aed; font-size: 0.85rem;">
                        <i class="bi bi-cpu-fill me-1"></i> Sistemas TI (Quito)
                    </span>
                @else
                    <span class="badge bg-primary px-3 py-1 rounded-pill fw-bold" style="font-size: 0.85rem;">
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

        <!-- Botones de Acción de Mesa de Ayuda -->
        <div class="d-flex flex-wrap gap-2">
            <!-- Botón Iniciar Llamada de Soporte en Vivo -->
            @if(!in_array($ticket->estado, ['resuelto', 'cerrado', 'cancelado']))
                <button type="button" class="btn btn-success text-white rounded-3 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="iniciarLlamadaWebRTC()">
                    <i class="bi bi-telephone-fill"></i> Iniciar Llamada / Pantalla
                </button>
            @endif

            <!-- Asignar Técnico -->
            <button type="button" class="btn btn-outline-primary rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="modalAsignarTicket()">
                <i class="bi bi-person-check-fill"></i>
                {{ $ticket->asignadoA ? 'Reasignar' : 'Asignarme / Asignar' }}
            </button>

            <!-- Cambiar Estado -->
            @if($ticket->estado === 'abierto')
                <button type="button" class="btn btn-warning text-dark rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="cambiarEstadoDirecto('en_atencion')">
                    <i class="bi bi-play-circle-fill"></i> Iniciar Atención
                </button>
            @endif

            @if(in_array($ticket->estado, ['abierto', 'en_atencion']))
                <button type="button" class="btn btn-outline-secondary rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="modalCambiarEstado('en_espera')">
                    <i class="bi bi-pause-circle"></i> Poner En Espera
                </button>
            @elseif($ticket->estado === 'en_espera')
                <button type="button" class="btn btn-warning text-dark rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="cambiarEstadoDirecto('en_atencion')">
                    <i class="bi bi-play-circle-fill"></i> Reanudar Atención
                </button>
            @endif

            @if(!in_array($ticket->estado, ['resuelto', 'cerrado', 'cancelado']))
                <button type="button" class="btn btn-dark text-white rounded-3 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="modalResolverTicket()">
                    <i class="bi bi-check-circle-fill text-success"></i> Resolver Ticket
                </button>
            @endif
        </div>
    </div>

    <!-- Alerta y Detalle de Resolución Técnica -->
    @if(in_array($ticket->estado, ['resuelto', 'cerrado']))
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #f0fdf4; border: 1.5px solid #86efac !important;">
            <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bi bi-patch-check-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-bold text-success mb-0">Ticket {{ $ticket->estado === 'cerrado' ? 'Cerrado' : 'Resuelto con Éxito' }}</h5>
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 small">✓ Solucionado</span>
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-calendar-check me-1"></i>Fecha de resolución: <strong>{{ $ticket->fecha_resolucion ? $ticket->fecha_resolucion->format('d/m/Y H:i:s') : ($ticket->updated_at ? $ticket->updated_at->format('d/m/Y H:i:s') : 'Hoy') }}</strong>
                            @if($ticket->asignadoA)
                                &nbsp;·&nbsp; <i class="bi bi-person-badge me-1"></i>Atendido por: <strong>{{ $ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario }}</strong>
                            @endif
                        </div>
                        <div class="bg-white p-3 rounded-3 border border-success border-opacity-25 text-dark small shadow-sm" style="white-space: pre-line; line-height: 1.6;">
                            <strong class="text-success d-block mb-1"><i class="bi bi-chat-quote-fill me-1"></i>Comentario / Solución Técnica Registrada:</strong>
                            {{ $ticket->solucion_texto ?: ($ticket->solucion ?: 'Atención técnica completada satisfactoriamente.') }}
                        </div>
                    </div>
                </div>

                @if($ticket->calificacion)
                    <div class="bg-white p-3 rounded-3 border text-center flex-shrink-0 shadow-sm" style="min-width: 170px;">
                        <div class="small text-muted fw-semibold mb-1">Calificación de Tienda</div>
                        <div class="text-warning fs-5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $ticket->calificacion ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                        @if($ticket->comentario_calificacion)
                            <div class="text-muted small mt-1 fst-italic">"{{ $ticket->comentario_calificacion }}"</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Columna Izquierda: Detalle Inicial + Chat en Tiempo Real y Notas Internas -->
        <div class="col-12 col-lg-8">
            <!-- Tarjeta con Descripción Inicial -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-muted small fw-semibold">Requerimiento Original</span>
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

            <!-- CHAT EN TIEMPO REAL & NOTAS INTERNAS -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <!-- Cabecera del Chat en Vivo -->
                <div class="p-3 px-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-headset text-primary fs-5"></i>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Canal de Chat & Notas Internas</h6>
                            <span class="text-muted" style="font-size: 0.75rem;">Comunicación en tiempo real con {{ $ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1" onclick="iniciarLlamadaWebRTC()">
                            <i class="bi bi-telephone-fill"></i> Llamar Tienda
                        </button>
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
                        @php
                            if ($msg->id > $ultimoId) $ultimoId = $msg->id;
                            $esNota = (bool) $msg->es_nota_interna;
                            $esMio = (int) $msg->usuario_id === (int) $usuario->id;
                            $esSolicitante = (int) $msg->usuario_id === (int) $ticket->solicitante_id;
                        @endphp

                        <div class="d-flex gap-3 {{ $esMio && !$esNota ? 'flex-row-reverse' : '' }}" id="msg-{{ $msg->id }}">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0 shadow-sm" 
                                 style="width: 36px; height: 36px; font-size: 0.8rem; background: {{ $esNota ? '#d97706' : ($esMio ? '#2563eb' : ($esSolicitante ? '#059669' : '#475569')) }};">
                                @if($esNota)
                                    <i class="bi bi-lock-fill"></i>
                                @else
                                    {{ strtoupper(substr($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : 'S', 0, 1)) }}
                                @endif
                            </div>
                            <div class="card border-0 p-3 rounded-4 shadow-sm" style="max-width: 85%; background: {{ $esNota ? '#fffbeb' : ($esMio ? '#eff6ff' : '#ffffff') }}; border: 1.5px solid {{ $esNota ? '#fde68a' : ($esMio ? '#bfdbfe' : '#e2e8f0') }} !important;">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                    <div>
                                        <span class="fw-bold small {{ $esNota ? 'text-warning text-dark' : ($esMio ? 'text-primary' : ($esSolicitante ? 'text-success' : 'text-dark')) }}">
                                            {{ $esMio ? 'Tú (' . ($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : '') . ')' : ($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : 'Soporte SGN') }}
                                        </span>
                                        @if($esNota)
                                            <span class="badge bg-warning text-dark ms-1"><i class="bi bi-shield-lock me-1"></i>Nota Interna (Privada)</span>
                                        @elseif($esSolicitante)
                                            <span class="badge bg-success bg-opacity-10 text-success ms-1">Tienda / Solicitante</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary ms-1">Técnico</span>
                                        @endif
                                    </div>
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
                        <div id="chat-empty-msg" class="text-center text-muted py-4">
                            <i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-50 text-primary"></i>
                            Inicia la conversación con el solicitante o deja una nota interna para tu equipo.
                        </div>
                    @endforelse
                </div>

                <!-- Barra de Entrada de Mensaje -->
                @if($ticket->estado !== 'cerrado' && $ticket->estado !== 'cancelado')
                    <div class="p-3 bg-light border-top">
                        <!-- Toggle Público vs Nota Interna -->
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" class="btn btn-sm btn-primary active rounded-pill px-3 fw-semibold" id="btn-modo-publico" onclick="setModoRespuesta(false)">
                                <i class="bi bi-chat-left-dots me-1"></i> Respuesta a Tienda
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-semibold" id="btn-modo-nota" onclick="setModoRespuesta(true)">
                                <i class="bi bi-shield-lock me-1"></i> Nota Interna Privada (Solo Técnicos)
                            </button>
                        </div>

                        <!-- Vista previa de archivos adjuntos / imagen pegada -->
                        <div id="preview-adjuntos" class="d-flex flex-wrap gap-2 mb-2" style="display: none !important;"></div>

                        <form id="form-chat-enviar" onsubmit="enviarMensajeChat(event)">
                            @csrf
                            <input type="hidden" id="chat-es-nota-interna" value="0">
                            <div class="input-group bg-white rounded-4 border p-1 shadow-sm" id="chat-input-wrapper">
                                <label class="btn btn-link text-muted p-2 px-3 m-0" title="Adjuntar archivo o imagen" style="cursor: pointer;">
                                    <i class="bi bi-paperclip fs-5"></i>
                                    <input type="file" id="chat-file-input" multiple class="d-none" accept="image/*,.pdf,.doc,.docx,.txt,.zip" onchange="onArchivosSeleccionados(this)">
                                </label>
                                <textarea id="chat-input-texto" rows="1" class="form-control border-0 shadow-none px-2 py-2" placeholder="Escribe un mensaje para la tienda... (Ctrl+V para pegar captura)" style="resize: none; font-size: 0.92rem;" onkeydown="onKeyDownChat(event)"></textarea>
                                <button type="submit" id="btn-chat-submit" class="btn btn-primary rounded-4 px-4 m-1 fw-bold shadow-sm d-flex align-items-center gap-1">
                                    <i class="bi bi-send-fill"></i> <span class="d-none d-sm-inline">Enviar</span>
                                </button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-2 text-muted" style="font-size: 0.72rem;">
                            <span><i class="bi bi-info-circle me-1"></i>Pulsa <strong>Enter</strong> para enviar, <strong>Shift+Enter</strong> para salto de línea.</span>
                            <span><i class="bi bi-clipboard me-1"></i>Capturas con <strong>Ctrl+V</strong></span>
                        </div>
                    </div>
                @else
                    <div class="p-3 text-center bg-light text-muted small border-top">
                        <i class="bi bi-lock-fill me-1"></i> Este ticket está {{ $ticket->estado }}. El chat ha sido archivado.
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Ficha Técnica y Solicitante -->
        <div class="col-12 col-lg-4">
            <!-- Ficha del Solicitante -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold text-dark mb-3 text-uppercase small"><i class="bi bi-person-badge me-1 text-primary"></i> Solicitante & Tienda</h6>
                <div class="d-flex flex-column gap-2 small">
                    <div>
                        <div class="text-muted">Nombre del Solicitante:</div>
                        <div class="fw-bold text-dark fs-6">{{ $ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Tienda / Ubicación Origen:</div>
                        <div class="fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $ticket->tienda_nombre ?: ($ticket->sucursalCliente->nombre ?? 'Tienda Externa') }}</div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary mt-1">{{ $ticket->empresa_origen }}</span>
                    </div>
                    <div>
                        <div class="text-muted">Correo Institucional:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->solicitante->correo_tec ?: 'Sin correo registrado' }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Teléfono de Contacto:</div>
                        <div class="fw-semibold text-dark">
                            {{ $ticket->contacto_telefono ?: 'No especificado' }}
                            @if($ticket->contacto_telefono)
                                <a href="https://wa.me/593{{ ltrim($ticket->contacto_telefono, '0') }}" target="_blank" class="btn btn-sm btn-success py-0 px-2 rounded-pill ms-2 text-white text-decoration-none">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                    @if($ticket->solicitante->anydesk_id)
                        <div class="p-2 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25 mt-1">
                            <div class="text-danger small fw-bold"><i class="bi bi-display me-1"></i>AnyDesk ID:</div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="fw-bold text-dark fs-6 font-monospace" id="span-anydesk">{{ $ticket->solicitante->anydesk_id }}</span>
                                <button type="button" class="btn btn-sm btn-white bg-white border py-0 px-2 small rounded-2" onclick="navigator.clipboard.writeText('{{ $ticket->solicitante->anydesk_id }}'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'AnyDesk copiado', showConfirmButton: false, timer: 1500});">
                                    <i class="bi bi-clipboard me-1"></i>Copiar
                                </button>
                            </div>
                        </div>
                    @endif
                    @if($ticket->solicitante->usuario_mba || $ticket->solicitante->codigo_usuario)
                        <div class="p-2 bg-light rounded-3 border mt-1">
                            <div class="text-muted small"><strong>MBA3:</strong> {{ $ticket->solicitante->usuario_mba ?: '-' }} &nbsp;|&nbsp; <strong>Cód. Vendedor:</strong> {{ $ticket->solicitante->codigo_usuario ?: '-' }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ficha Técnica del Ticket -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold text-dark mb-3 text-uppercase small"><i class="bi bi-gear-fill me-1 text-primary"></i> Datos del Ticket</h6>
                <div class="d-flex flex-column gap-3 small">
                    <div>
                        <div class="text-muted">Técnico Asignado:</div>
                        <div class="fw-bold text-dark">
                            @if($ticket->asignadoA)
                                <span class="badge bg-primary text-white p-2 rounded-3 fs-6">{{ $ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario }}</span>
                            @else
                                <span class="badge bg-warning text-dark p-2 rounded-3">⚠️ Sin Asignar (Quito)</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-muted">Categoría:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->categoria }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Prioridad:</div>
                        <div class="fw-semibold">
                            @if($ticket->prioridad === 'urgente')
                                <span class="badge bg-danger text-white">Urgente</span>
                            @elseif($ticket->prioridad === 'alta')
                                <span class="badge bg-warning text-dark">Alta</span>
                            @elseif($ticket->prioridad === 'media')
                                <span class="badge bg-info text-white">Media</span>
                            @else
                                <span class="badge bg-light text-dark border">Baja</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-muted">Fecha de Apertura:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->fecha_apertura ? $ticket->fecha_apertura->format('d/m/Y H:i:s') : '-' }}</div>
                    </div>
                    @if($ticket->fecha_primera_respuesta)
                        <div>
                            <div class="text-muted">Primera Respuesta:</div>
                            <div class="fw-semibold text-dark">{{ $ticket->fecha_primera_respuesta->format('d/m/Y H:i:s') }}</div>
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

// WebRTC Engine Variables (Iniciador - Técnico)
let peerConnection = null;
let localStream = null;
let screenStream = null;
let videoSender = null;
let isCallActive = false;
let callTimerInterval = null;
let callSeconds = 0;
let callPollerInterval = null;
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

function setModoRespuesta(esNota) {
    const btnPub = document.getElementById('btn-modo-publico');
    const btnNota = document.getElementById('btn-modo-nota');
    const inputMsg = document.getElementById('chat-input-texto');
    const btnSubmit = document.getElementById('btn-chat-submit');
    const hidNota = document.getElementById('chat-es-nota-interna');

    hidNota.value = esNota ? '1' : '0';

    if (esNota) {
        btnNota.className = 'btn btn-sm btn-warning fw-bold text-dark rounded-pill px-3 shadow-sm';
        btnPub.className = 'btn btn-sm btn-light border text-muted rounded-pill px-3';
        inputMsg.placeholder = '🔒 Escribe una nota interna (solo visible para técnicos)...';
        inputMsg.style.borderColor = '#f59e0b';
        btnSubmit.className = 'btn btn-warning fw-bold text-dark rounded-pill px-4 d-flex align-items-center gap-2';
        btnSubmit.innerHTML = '<i class="bi bi-shield-lock-fill"></i> Guardar Nota Interna';
    } else {
        btnPub.className = 'btn btn-sm btn-primary fw-bold rounded-pill px-3 shadow-sm';
        btnNota.className = 'btn btn-sm btn-light border text-muted rounded-pill px-3';
        inputMsg.placeholder = 'Escribe un mensaje para la tienda / solicitante... (Ctrl+V para pegar captura)';
        inputMsg.style.borderColor = '#cbd5e1';
        btnSubmit.className = 'btn btn-primary fw-bold rounded-pill px-4 d-flex align-items-center gap-2';
        btnSubmit.innerHTML = '<i class="bi bi-send-fill"></i> Enviar a la Tienda';
    }
}

function renderMessage(msg) {
    const stream = document.getElementById('chat-stream');
    const div = document.createElement('div');
    const esNota = Boolean(msg.es_nota_interna);
    const esMio = Boolean(msg.es_propio);
    const esSolicitante = Boolean(msg.es_solicitante);

    div.className = `d-flex gap-3 ${esMio && !esNota ? 'flex-row-reverse' : ''}`;
    div.id = `msg-${msg.id}`;

    let avatarBg = '#475569';
    if (esNota) avatarBg = '#d97706';
    else if (esMio) avatarBg = '#2563eb';
    else if (esSolicitante) avatarBg = '#059669';

    let cardBg = '#ffffff';
    let cardBorder = '#e2e8f0';
    if (esNota) {
        cardBg = '#fffbeb';
        cardBorder = '#fde68a';
    } else if (esMio) {
        cardBg = '#eff6ff';
        cardBorder = '#bfdbfe';
    }

    let badgeHtml = '';
    if (esNota) badgeHtml = '<span class="badge bg-warning text-dark ms-1"><i class="bi bi-shield-lock me-1"></i>Nota Interna</span>';
    else if (esSolicitante) badgeHtml = '<span class="badge bg-success bg-opacity-10 text-success ms-1">Tienda / Solicitante</span>';
    else badgeHtml = '<span class="badge bg-primary bg-opacity-10 text-primary ms-1">Técnico</span>';

    let adjuntosHtml = '';
    if (msg.adjuntos && msg.adjuntos.length > 0) {
        adjuntosHtml = `<div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2">` +
            msg.adjuntos.map(a => `
                <a href="${a.url}" target="_blank" class="btn btn-sm btn-light border rounded-3 d-inline-flex align-items-center gap-2 p-1 px-2 text-decoration-none">
                    <i class="bi ${a.es_imagen ? 'bi-image text-primary' : 'bi-file-earmark text-secondary'}"></i>
                    <span class="small text-truncate" style="max-width: 140px;">${a.nombre}</span>
                </a>
            `).join('') + `</div>`;
    }

    const inicial = (msg.autor_nombre || 'S').charAt(0).toUpperCase();

    div.innerHTML = `
        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0 shadow-sm" 
             style="width: 36px; height: 36px; font-size: 0.8rem; background: ${avatarBg};">
            ${esNota ? '<i class="bi bi-lock-fill"></i>' : inicial}
        </div>
        <div class="card border-0 p-3 rounded-4 shadow-sm" style="max-width: 85%; background: ${cardBg}; border: 1.5px solid ${cardBorder} !important;">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                <div>
                    <span class="fw-bold small ${esNota ? 'text-warning text-dark' : (esMio ? 'text-primary' : (esSolicitante ? 'text-success' : 'text-dark'))}">
                        ${esMio ? 'Tú (' + msg.autor_nombre + ')' : msg.autor_nombre}
                    </span>
                    ${badgeHtml}
                </div>
                <span class="text-muted" style="font-size: 0.72rem;">${msg.creado_hace}</span>
            </div>
            <div class="text-dark small" style="white-space: pre-line; line-height: 1.5;">${escapeHtml(msg.mensaje)}</div>
            ${adjuntosHtml}
        </div>
    `;

    stream.appendChild(div);
}

function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text || '';
    return d.innerHTML;
}

setInterval(syncChatMessages, 2500);

async function syncChatMessages() {
    try {
        const url = `${syncUrl}?ultimo_id=${ultimoMensajeId}`;
        const res = await fetch(url);
        const data = await res.json();
        if (data.ok && data.mensajes && data.mensajes.length > 0) {
            let hasIncoming = false;
            data.mensajes.forEach(m => {
                if (m.id > ultimoMensajeId) {
                    ultimoMensajeId = m.id;
                    if (!document.getElementById(`msg-${m.id}`)) {
                        renderMessage(m);
                        if (!m.es_propio && !m.es_nota_interna) hasIncoming = true;
                    }
                }
            });
            scrollToBottom();
            if (hasIncoming) {
                playChatChime();
            }
        }
    } catch (e) {
        console.warn('Sync tick error:', e);
    }
}

async function iniciarLlamadaWebRTC() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
    } catch(err) {
        Swal.fire({
            title: 'Permiso de Micrófono',
            text: 'Debes permitir el uso del micrófono para realizar la llamada.',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    processedIceCandidates.clear();

    Swal.fire({
        title: 'Llamando a la tienda...',
        html: `
            <div class="py-3">
                <div class="spinner-grow text-success mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <p class="text-muted small mb-0">Haciendo sonar la llamada en la pantalla de la tienda...</p>
            </div>
        `,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false,
    }).then((res) => {
        if (!res.isConfirmed && !isCallActive) colgarLlamada();
    });

    try {
        peerConnection = new RTCPeerConnection(rtcConfig);

        localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
        });

        const videoTransceiver = peerConnection.addTransceiver('video', { direction: 'sendrecv' });
        videoSender = videoTransceiver.sender;

        peerConnection.ontrack = (event) => {
            if (event.track.kind === 'video') {
                const remoteVid = document.getElementById('remote-video');
                const screenWrap = document.getElementById('remote-screen-wrapper');
                if (remoteVid) {
                    remoteVid.srcObject = event.streams[0] || new MediaStream([event.track]);
                    remoteVid.play().catch(e => console.warn('Video play error:', e));
                }
                if (screenWrap) screenWrap.style.display = 'block';
                event.track.onended = () => { if (screenWrap) screenWrap.style.display = 'none'; };
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
                    body: JSON.stringify({ ice: event.candidate })
                }).catch(() => {});
            }
        };

        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);

        const resIni = await fetch("{{ route('tickets.llamada.iniciar', $ticket->id) }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ offer: JSON.stringify(offer) })
        });
        const dataIni = await resIni.json();
        
        if (!dataIni.ok) {
            Swal.fire('Error al Iniciar Llamada', dataIni.error || 'No se pudo conectar', 'error');
            cerrarLlamadaLocal();
            return;
        }

        if (callPollerInterval) clearInterval(callPollerInterval);
        callPollerInterval = setInterval(syncSignalingTecnico, 1000);

    } catch(err) {
        Swal.fire('Error al Conectar', err.message, 'error');
        cerrarLlamadaLocal();
    }
}

async function syncSignalingTecnico() {
    try {
        const stRes = await fetch("{{ route('tickets.llamada.estado', $ticket->id) }}");
        const stData = await stRes.json();
        if (!stData.ok || !stData.hay_llamada) return;

        if (stData.estado === 'en_curso' && stData.answer) {
            if (peerConnection && !peerConnection.currentRemoteDescription) {
                try {
                    const ansObj = typeof stData.answer === 'string' ? JSON.parse(stData.answer) : stData.answer;
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(ansObj));
                    Swal.close();
                    if (!isCallActive) iniciarLlamadaUI();
                } catch(e) {
                    console.error('Error setting remote description:', e);
                }
            }
        } else if (stData.estado === 'rechazada') {
            clearInterval(callPollerInterval);
            Swal.fire('Llamada Rechazada', 'La tienda rechazó la llamada.', 'info');
            cerrarLlamadaLocal();
            return;
        } else if (stData.estado === 'finalizada' && isCallActive) {
            clearInterval(callPollerInterval);
            Swal.fire({ title: 'Llamada Finalizada', icon: 'info', timer: 2000, showConfirmButton: false });
            cerrarLlamadaLocal();
            return;
        }

        if (peerConnection && peerConnection.remoteDescription && stData.ice_peer && Array.isArray(stData.ice_peer)) {
            for (const cand of stData.ice_peer) {
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
    } catch(e) {
        console.warn('Signaling error:', e);
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
                if (txt) txt.textContent = 'Compartir Mi Pantalla';
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
        if (txt) txt.textContent = 'Compartir Mi Pantalla';
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
    if (callPollerInterval) clearInterval(callPollerInterval);
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

async function enviarMensajeChat(e) {
    e.preventDefault();
    const txtInput = document.getElementById('chat-input-texto');
    const texto = txtInput.value.trim();
    const btnSubmit = document.getElementById('btn-chat-submit');
    const esNota = document.getElementById('chat-es-nota-interna').value === "1";

    if (!texto && archivosAdjuntosList.length === 0) return;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    const formData = new FormData();
    formData.append('mensaje', texto);
    formData.append('es_nota_interna', esNota ? '1' : '0');
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
        setModoRespuesta(esNota);
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

// Modales de Acción
function modalAsignarTicket() {
    const asignadoActualId = "{{ $ticket->asignado_a_id ?? '' }}";
    
    Swal.fire({
        title: 'Asignar Técnico Responsable',
        html: `
            <div style="text-align: left; font-size: 13px;">
                <div class="mb-2 position-relative">
                    <label class="fw-bold mb-1 d-block text-dark small">
                        <i class="bi bi-search me-1 text-primary"></i>Buscar técnico por nombre:
                    </label>
                    <input type="text" id="swal-search-tecnico" class="form-control form-control-sm" 
                           placeholder="Escribe el nombre (ej. Omar, Pucha, Morales...)" 
                           style="border-radius: 8px; font-size: 13px; padding: 7px 12px; border: 1.5px solid #cbd5e1;"
                           autocomplete="off">
                </div>

                <div class="small text-muted mb-2 d-flex justify-content-between">
                    <span>Selecciona el técnico responsable:</span>
                    <span id="swal-tecnico-count" class="badge bg-light text-dark border"></span>
                </div>

                <div id="swal-tecnicos-list" style="max-height: 280px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px; background: #fafafa;" class="d-flex flex-column gap-1">
                    <!-- Opción Sin Asignar -->
                    <label class="swal-tec-item d-flex align-items-center justify-content-between p-2 rounded-3 border bg-white" 
                           style="cursor: pointer; transition: all 0.15s ease; border-color: #e2e8f0 !important;"
                           data-nombre="sin asignar desasignar ninguno" data-id="">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                ⚠️
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 12.5px;">-- Sin Asignar --</div>
                                <div class="text-muted" style="font-size: 11px;">Quitar técnico asignado</div>
                            </div>
                        </div>
                        <input type="radio" name="swal_selected_tecnico" value="" {{ !$ticket->asignado_a_id ? 'checked' : '' }} style="accent-color: #2563eb; width: 16px; height: 16px;">
                    </label>

                    @foreach($tecnicosQuito as $t)
                        @php
                            $isSelected = ((int)$ticket->asignado_a_id === (int)$t->id);
                            $rolStr = strtolower($t->rol->rol ?? 'tecnico');
                            $nombreCompleto = $t->nombre_tecnico ?: $t->usuario;
                        @endphp
                        <label class="swal-tec-item d-flex align-items-center justify-content-between p-2 rounded-3 border bg-white {{ $isSelected ? 'border-primary bg-primary bg-opacity-10' : '' }}" 
                               style="cursor: pointer; transition: all 0.15s ease; {{ $isSelected ? 'border-color: #2563eb !important; background: #eff6ff !important;' : 'border-color: #e2e8f0 !important;' }}"
                               data-nombre="{{ strtolower($nombreCompleto) }} {{ strtolower($t->usuario) }} {{ $rolStr }}" 
                               data-id="{{ $t->id }}">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0" 
                                     style="width: 32px; height: 32px; font-size: 0.75rem; background: {{ str_contains($rolStr, 'master') ? '#2563eb' : '#059669' }};">
                                    {{ strtoupper(substr($nombreCompleto, 0, 1)) }}
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold text-dark swal-tec-name" style="font-size: 12.5px;">{{ $nombreCompleto }}</div>
                                    <div class="d-flex align-items-center gap-1 mt-0.5">
                                        <span class="badge {{ str_contains($rolStr, 'master') ? 'bg-primary' : 'bg-success' }} text-white" style="font-size: 9.5px; padding: 2px 6px;">
                                            {{ $t->rol->rol ?? 'Técnico' }}
                                        </span>
                                        @if($isSelected)
                                            <span class="badge bg-warning text-dark" style="font-size: 9.5px; padding: 2px 5px;">Asignado actual</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <input type="radio" name="swal_selected_tecnico" value="{{ $t->id }}" {{ $isSelected ? 'checked' : '' }} style="accent-color: #2563eb; width: 16px; height: 16px;">
                        </label>
                    @endforeach
                </div>
                <div id="swal-tecnicos-empty" style="display: none;" class="p-3 text-center text-muted small border rounded-3 bg-light mt-1">
                    <i class="bi bi-person-x fs-5 d-block mb-1 text-secondary opacity-50"></i>
                    No se encontraron técnicos con ese nombre.
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar Asignación',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        didOpen: () => {
            const searchInput = document.getElementById('swal-search-tecnico');
            const items = document.querySelectorAll('.swal-tec-item');
            const emptyEl = document.getElementById('swal-tecnicos-empty');
            const countBadge = document.getElementById('swal-tecnico-count');

            function updateCount() {
                let visible = 0;
                items.forEach(it => {
                    if (it.style.display !== 'none') visible++;
                });
                if (countBadge) countBadge.textContent = `${visible} disponibles`;
                if (emptyEl) emptyEl.style.display = visible === 0 ? 'block' : 'none';
            }

            updateCount();

            if (searchInput) {
                searchInput.focus();
                searchInput.addEventListener('input', function() {
                    const q = this.value.toLowerCase().trim();
                    items.forEach(it => {
                        const hay = it.getAttribute('data-nombre') || '';
                        if (!q || hay.includes(q)) {
                            it.style.display = 'flex';
                        } else {
                            it.style.display = 'none';
                        }
                    });
                    updateCount();
                });
            }

            // Click directo en cualquier tarjeta selecciona su radio
            items.forEach(it => {
                it.addEventListener('click', function(e) {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        items.forEach(other => {
                            other.style.borderColor = '#e2e8f0';
                            other.style.background = '#ffffff';
                        });
                        this.style.borderColor = '#2563eb';
                        this.style.background = '#eff6ff';
                    }
                });
            });
        },
        preConfirm: () => {
            const checked = document.querySelector('input[name="swal_selected_tecnico"]:checked');
            return checked ? checked.value : '';
        }
    }).then((r) => {
        if (r.isConfirmed) {
            Swal.showLoading();
            fetch("{{ route('tickets.asignar', $ticket->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tecnico_id: r.value })
            })
            .then(res => res.json())
            .then(res => {
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Asignación Actualizada',
                        text: res.mensaje,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            });
        }
    });
}

function modalCambiarEstado(nuevoEstado) {
    Swal.fire({
        title: 'Poner Ticket En Espera',
        html: `
            <div style="text-align: left;">
                <label class="fw-bold mb-1">Indica el motivo por el cual queda en espera:</label>
                <textarea id="swal-motivo-espera" class="swal2-textarea" placeholder="Ej: Esperando confirmación de tienda / Esperando repuesto..." style="width: 100%; margin: 0; height: 80px;"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Pausar Ticket',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d97706',
        preConfirm: () => {
            const motivo = document.getElementById('swal-motivo-espera').value.trim();
            if (!motivo) {
                Swal.showValidationMessage('Debe indicar un motivo.');
                return false;
            }
            return motivo;
        }
    }).then((r) => {
        if (r.isConfirmed) {
            cambiarEstadoDirecto(nuevoEstado, r.value);
        }
    });
}

function modalResolverTicket() {
    Swal.fire({
        title: 'Resolver Ticket',
        html: `
            <div style="text-align: left;">
                <p class="small text-muted mb-2">Al resolver el ticket, el solicitante podrá calificar la atención y confirmar el cierre.</p>
                <label class="fw-bold mb-1">Descripción de la Solución Técnica Aplicada <span class="text-danger">*</span>:</label>
                <textarea id="swal-solucion" class="swal2-textarea" placeholder="Describe detalladamente qué se realizó para resolver el problema..." style="width: 100%; margin: 0; height: 100px;"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Marcar como Resuelto',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
        preConfirm: () => {
            const solucion = document.getElementById('swal-solucion').value.trim();
            if (!solucion) {
                Swal.showValidationMessage('La descripción de la solución es obligatoria.');
                return false;
            }
            return solucion;
        }
    }).then((r) => {
        if (r.isConfirmed) {
            cambiarEstadoDirecto('resuelto', null, r.value);
        }
    });
}

function cambiarEstadoDirecto(estado, motivo = null, solucion = null) {
    Swal.showLoading();
    fetch("{{ route('tickets.cambiar_estado', $ticket->id) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ estado, motivo, solucion })
    })
    .then(r => r.json())
    .then(res => {
        if (res.ok) {
            Swal.fire('Actualizado', res.mensaje, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', res.error, 'error');
        }
    })
    .catch(err => Swal.fire('Error', 'No se pudo actualizar el estado', 'error'));
}

document.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
    setInterval(syncChatLoop, 1800);
});
</script>
@endsection
