@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4" style="max-width: 1400px;">
    <!-- Barra Flotante de Llamada Activa WebRTC -->
    <div id="call-active-bar" class="card border-0 shadow-lg rounded-4 p-3 bg-dark text-white mb-4 animate__animated animate__fadeInDown" style="display: none !important;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-headset fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6 mb-0 d-flex align-items-center gap-2">
                        <span>Llamada con: {{ $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : ($ticket->solicitante_nombre ?: 'Solicitante') }}</span>
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
                @elseif($ticket->estado === 'en_atencion' || $ticket->estado === 'en_proceso')
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill fw-semibold">En Atención</span>
                @elseif($ticket->estado === 'en_espera')
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1 rounded-pill fw-semibold">En Espera</span>
                @elseif($ticket->estado === 'en_mba')
                    <span class="badge text-white px-3 py-1 rounded-pill fw-bold shadow-sm" style="background: #9333ea;">
                        <i class="bi bi-clock-history me-1"></i> En Manos de MBA (Máx. 48h)
                    </span>
                    @if($ticket->numero_ticket_mba)
                        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-semibold font-monospace" style="font-size: 0.8rem;">
                            Ticket MBA: #{{ $ticket->numero_ticket_mba }}
                        </span>
                    @endif
                @elseif($ticket->estado === 'resuelto')
                    <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Resuelto</span>
                @elseif($ticket->estado === 'cerrado')
                    <span class="badge bg-dark text-white px-3 py-1 rounded-pill fw-semibold">Cerrado</span>
                @elseif($ticket->estado === 'cancelado')
                    <span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-semibold">Cancelado</span>
                @endif
            </div>
        </div>

        <!-- Botones de Acción de Mesa de Ayuda -->
        <div class="d-flex flex-wrap gap-2">
            <!-- Botón Imprimir PDF Ticket (Estilo OT) -->
            <a href="{{ route('tickets.imprimir', $ticket->id) }}" target="_blank" class="btn btn-outline-danger rounded-3 fw-bold d-flex align-items-center gap-2 shadow-sm" title="Imprimir PDF oficial del Ticket">
                <i class="bi bi-printer-fill"></i> Imprimir PDF (OT)
            </a>

            <!-- Botón Iniciar Llamada de Soporte en Vivo -->
            @if(!in_array($ticket->estado, ['resuelto', 'cerrado', 'cancelado']))
                <button type="button" class="btn btn-success text-white rounded-3 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="iniciarLlamadaWebRTC()">
                    <i class="bi bi-telephone-fill"></i> Iniciar Llamada / Pantalla
                </button>
            @endif

            <!-- Asignar Técnico -->
            @if(!in_array($ticket->estado, ['resuelto', 'cerrado', 'cancelado']))
                <button type="button" class="btn btn-outline-primary rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="modalAsignarTicket()">
                    <i class="bi bi-person-check-fill"></i>
                    {{ $ticket->asignadoA ? 'Reasignar' : 'Asignarme / Asignar' }}
                </button>
            @endif

            <!-- Botones de Gestión de Estado -->
            @if($ticket->estado === 'abierto')
                <button type="button" class="btn btn-warning text-dark rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="cambiarEstadoDirecto('en_proceso')">
                    <i class="bi bi-play-circle-fill"></i> Iniciar Atención
                </button>
            @endif

            @if(in_array($ticket->estado, ['abierto', 'en_atencion', 'en_proceso']))
                <button type="button" class="btn btn-outline-secondary rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="modalCambiarEstado('en_espera')">
                    <i class="bi bi-pause-circle"></i> Poner En Espera
                </button>
            @elseif($ticket->estado === 'en_espera')
                <button type="button" class="btn btn-warning text-dark rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="cambiarEstadoDirecto('en_proceso')">
                    <i class="bi bi-play-circle-fill"></i> Reanudar Atención
                </button>
            @endif

            <!-- Botón Escalar a MBA (Disponible para tickets activos) -->
            @if($ticket->estado === 'en_mba')
                <button type="button" class="btn text-white rounded-3 fw-semibold d-flex align-items-center gap-2 shadow-sm" style="background: #9333ea; border-color: #9333ea;" onclick="modalEscalarMba()">
                    <i class="bi bi-pencil-square"></i> N° Ticket MBA (#{{ $ticket->numero_ticket_mba ?: 'Ingresar' }})
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-3 fw-semibold d-flex align-items-center gap-2" onclick="cambiarEstadoDirecto('en_proceso', 'Retomado a atención interna de soporte')">
                    <i class="bi bi-arrow-return-left"></i> Retomar a Soporte Interno
                </button>
            @elseif(!in_array($ticket->estado, ['resuelto', 'cerrado', 'cancelado']))
                <button type="button" class="btn rounded-3 fw-semibold d-flex align-items-center gap-2" style="border: 1.5px solid #9333ea; color: #9333ea; background: #faf5ff;" onclick="modalEscalarMba()">
                    <i class="bi bi-send-exclamation-fill"></i> Escalar a MBA (Máx 48h)
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
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 small"><i class="bi bi-check-circle-fill me-1"></i> Solucionado</span>
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

                        @php
                            $msgResolucion = $ticket->mensajes->where('cambio_estado', 'resuelto')->last();
                            $adjuntosResolucion = $msgResolucion ? $msgResolucion->adjuntos : collect();
                        @endphp
                        @if($adjuntosResolucion->isNotEmpty())
                            <div class="mt-2 pt-2 border-top border-success border-opacity-25">
                                <strong class="text-success d-block mb-1.5 small"><i class="bi bi-camera-fill me-1"></i>Foto / Evidencia de la Solución:</strong>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($adjuntosResolucion as $adj)
                                        @if($adj->es_imagen)
                                            <a href="{{ $adj->url }}" target="_blank" class="d-inline-block border rounded-3 overflow-hidden shadow-sm hover-scale" style="width: 100px; height: 80px;" title="Ver imagen completa">
                                                <img src="{{ $adj->url }}" alt="Evidencia" style="width: 100%; height: 100%; object-fit: cover;">
                                            </a>
                                        @else
                                            <a href="{{ $adj->url }}" target="_blank" class="btn btn-sm btn-white bg-white border rounded-3 d-inline-flex align-items-center gap-1.5 p-2 small shadow-sm">
                                                <i class="bi bi-file-earmark-arrow-down-fill text-success"></i> {{ $adj->nombre_archivo }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
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

    <!-- Ficha Rápida Móvil para Técnico (Visible en pantallas < 992px) -->
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3 d-block d-lg-none border-start border-4 border-primary">
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary font-monospace fw-bold">{{ $ticket->codigo_ticket }}</span>
                <span class="badge bg-purple text-white ms-1" style="background: #7c3aed; font-size: 10.5px;">{{ $ticket->categoria }}</span>
            </div>
            <div class="d-flex gap-1">
                @if($ticket->contacto_telefono || $ticket->solicitante?->telefono)
                    @php $telNum = $ticket->contacto_telefono ?: $ticket->solicitante?->telefono; @endphp
                    <a href="https://wa.me/593{{ ltrim($telNum, '0') }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                @endif
            </div>
        </div>
        <div class="row g-2 small">
            <div class="col-12">
                <span class="text-muted" style="font-size: 11px;">Solicitante:</span>
                <strong class="text-dark d-block fs-6">{{ $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : ($ticket->solicitante_nombre ?: 'Solicitante') }}</strong>
            </div>
            <div class="col-6">
                <span class="text-muted" style="font-size: 11px;">Tienda:</span>
                <strong class="text-dark d-block text-truncate">{{ $ticket->tienda_nombre ?: ($ticket->sucursalCliente->nombre ?? 'Tienda') }}</strong>
            </div>
            <div class="col-6">
                <span class="text-muted" style="font-size: 11px;">AnyDesk ID:</span>
                <strong class="text-danger font-monospace d-block">{{ $ticket->solicitante?->anydesk_id ?: 'No reg.' }}</strong>
            </div>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <!-- Columna Izquierda: Detalle Inicial + Chat en Tiempo Real y Notas Internas -->
        <div class="col-12 col-lg-8">
            @php
                $esCasoMba = ($ticket->categoria === 'Casos MBA3' || str_contains($ticket->descripcion, 'CASO MBA3') || str_contains($ticket->categoria, 'MBA'));
            @endphp

            @if($ticket->estado === 'en_mba')
                <!-- Alerta Destacada de Caso Escalado a MBA (48h) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-dark" style="background: #faf5ff; border: 2px solid #a855f7 !important;">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center flex-shrink-0 text-white shadow-sm" style="background: #9333ea; width: 48px; height: 48px;">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <h5 class="fw-bold mb-0" style="color: #7e22ce;">Caso Escalado a Soporte MBA (SLA 48 Horas)</h5>
                                    <span class="badge text-white rounded-pill px-2.5 py-1" style="background: #9333ea;"><i class="bi bi-clock-history me-1"></i> En Manos de MBA</span>
                                </div>
                                <p class="text-muted small mb-2">Este requerimiento está siendo tratado por el equipo de ingeniería de MBA3. Plazo máximo de resolución: 48 horas.</p>
                                <div class="d-flex flex-wrap gap-3 small">
                                    <div>
                                        <span class="text-muted">N° Ticket / Caso MBA:</span>
                                        <b class="text-dark font-monospace fs-6">#{{ $ticket->numero_ticket_mba ?: 'Pendiente' }}</b>
                                    </div>
                                    @if($ticket->fecha_escalado_mba)
                                        <div>
                                            <span class="text-muted">Escalado el:</span>
                                            <b class="text-dark">{{ $ticket->fecha_escalado_mba->format('d/m/Y H:i') }}</b>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2 flex-shrink-0">
                            <button type="button" class="btn text-white fw-bold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: #9333ea; border-color: #9333ea;" onclick="modalEscalarMba()">
                                <i class="bi bi-pencil-square"></i> Actualizar Ticket MBA
                            </button>
                            <button type="button" class="btn btn-success text-white fw-bold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="modalResolverTicket('mba')">
                                <i class="bi bi-check2-all"></i> MBA ya Resolvió (Cerrar)
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($esCasoMba)
                <!-- Tarjeta Destacada de Caso MBA3 con Descarga Word (.docx) -->
                <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 text-dark" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1.5px solid #c4b5fd !important;">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="background: #7c3aed; color: #ffffff; width: 46px; height: 46px;">
                                <i class="bi bi-file-earmark-word-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    Plantilla Oficial de Caso MBA3
                                    <span class="badge" style="background: #7c3aed; color: #fff; font-size: 0.7rem;">Word .docx</span>
                                </h6>
                                <p class="text-muted small mb-0 mt-0.5">Se compiló el reporte oficial con los datos del ícono, ruta de acceso, antecedentes y solución requerida.</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('tickets.word_mba', $ticket->id) }}" class="btn btn-purple fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2 text-white" style="background: #7c3aed; border-color: #7c3aed;">
                                <i class="bi bi-file-earmark-arrow-down-fill"></i> Descargar Word (.docx)
                            </a>
                        </div>
                    </div>
                </div>
            @endif

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
                        <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-paperclip text-primary fs-6"></i> Evidencias y Archivos Adjuntos Iniciales ({{ $adjuntosIniciales->count() }}):
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($adjuntosIniciales as $adj)
                                @if($adj->es_imagen)
                                    <div class="border rounded-3 p-2 bg-light d-flex align-items-center gap-2 shadow-sm">
                                        <a href="{{ $adj->url }}" target="_blank" class="d-inline-block rounded-2 overflow-hidden border flex-shrink-0" style="width: 55px; height: 50px;">
                                            <img src="{{ $adj->url }}" alt="{{ $adj->nombre_archivo }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        </a>
                                        <div class="overflow-hidden" style="max-width: 200px;">
                                            <a href="{{ $adj->url }}" target="_blank" class="fw-bold text-dark text-truncate d-block small text-decoration-none" title="{{ $adj->nombre_archivo }}">
                                                {{ $adj->nombre_archivo }}
                                            </a>
                                            <span class="text-muted" style="font-size: 11px;">{{ $adj->tamano_legible }} · Imagen</span>
                                        </div>
                                        <a href="{{ $adj->url }}" target="_blank" download class="btn btn-sm btn-white bg-white border rounded-2 p-1 px-2 text-primary ms-1" title="Descargar">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                @else
                                    <div class="border rounded-3 p-2 bg-light d-flex align-items-center gap-2 shadow-sm">
                                        <div class="rounded-2 bg-white border p-2 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                            <i class="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                                        </div>
                                        <div class="overflow-hidden" style="max-width: 200px;">
                                            <a href="{{ $adj->url }}" target="_blank" class="fw-bold text-dark text-truncate d-block small text-decoration-none" title="{{ $adj->nombre_archivo }}">
                                                {{ $adj->nombre_archivo }}
                                            </a>
                                            <span class="text-muted" style="font-size: 11px;">{{ $adj->tamano_legible }}</span>
                                        </div>
                                        <a href="{{ $adj->url }}" target="_blank" download class="btn btn-sm btn-white bg-white border rounded-2 p-1 px-2 text-primary ms-1" title="Descargar">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- CHAT EN TIEMPO REAL & NOTAS INTERNAS (EXPERIENCIA MÓVIL MODERNA) -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <!-- Cabecera del Chat en Vivo -->
                <div class="p-3 px-3 px-md-4 border-bottom bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            <i class="bi bi-headset fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-6">Chat & Soporte con la Tienda</h6>
                            <span class="text-muted small" style="font-size: 0.72rem;">{{ $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : ($ticket->solicitante_nombre ?: 'Solicitante') }} · {{ $ticket->tienda_nombre ?: 'Tienda' }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 shadow-sm" onclick="iniciarLlamadaWebRTC()" style="font-size: 11.5px;">
                            <i class="bi bi-telephone-fill"></i> <span class="d-none d-sm-inline">Llamar Tienda</span>
                        </button>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 7px; height: 7px;" role="status"></span>
                            En Vivo
                        </span>
                    </div>
                </div>

                <!-- Contenedor del Historial de Mensajes -->
                <div id="chat-stream" class="p-3 p-md-4 d-flex flex-column gap-2.5" 
                     style="max-height: 480px; min-height: 300px; overflow-y: auto; background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 0.75px, transparent 0.75px); background-size: 16px 16px; -webkit-overflow-scrolling: touch;">
                    @php $ultimoId = 0; @endphp
                    @forelse($ticket->mensajes as $msg)
                        @php
                            if ($msg->id > $ultimoId) $ultimoId = $msg->id;
                            $esNota = (bool) $msg->es_nota_interna;
                            $esMio = (int) $msg->usuario_id === (int) $usuario->id;
                            $esSolicitante = (int) $msg->usuario_id === (int) $ticket->solicitante_id;
                        @endphp

                        <div class="d-flex gap-2 {{ $esMio && !$esNota ? 'justify-content-end' : 'justify-content-start' }}" id="msg-{{ $msg->id }}">
                            @if(!$esMio || $esNota)
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0 shadow-sm mt-1" 
                                     style="width: 32px; height: 32px; font-size: 0.75rem; background: {{ $esNota ? '#d97706' : ($esSolicitante ? '#059669' : '#7c3aed') }};">
                                    @if($esNota)
                                        <i class="bi bi-lock-fill"></i>
                                    @else
                                        {{ strtoupper(substr($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : 'S', 0, 1)) }}
                                    @endif
                                </div>
                            @endif

                            <div class="p-2.5 px-3 rounded-4 shadow-sm" 
                                 style="max-width: 86%; background: {{ $esNota ? '#fffbeb' : ($esMio ? '#dbeafe' : '#ffffff') }}; border: 1px solid {{ $esNota ? '#fde68a' : ($esMio ? '#bfdbfe' : '#e2e8f0') }}; border-radius: {{ ($esMio && !$esNota) ? '18px 18px 4px 18px' : '18px 18px 18px 4px' }} !important;">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                    <div>
                                        <span class="fw-bold" style="font-size: 0.78rem; color: {{ $esNota ? '#b45309' : ($esMio ? '#1d4ed8' : ($esSolicitante ? '#059669' : '#7c3aed')) }};">
                                            {{ $esMio ? 'Tú (' . ($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : '') . ')' : ($msg->autor ? ($msg->autor->nombre_tecnico ?: $msg->autor->usuario) : 'Soporte SGN') }}
                                        </span>
                                        @if($esNota)
                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;"><i class="bi bi-shield-lock-fill me-1"></i>Nota Interna</span>
                                        @elseif($esSolicitante)
                                            <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size: 9px;">Tienda</span>
                                        @else
                                            <span class="badge bg-purple bg-opacity-10 text-purple ms-1" style="font-size: 9px; color: #7c3aed; background: #f3e8ff;">Sistemas</span>
                                        @endif
                                    </div>
                                    <span class="text-muted" style="font-size: 0.68rem;">{{ $msg->created_at ? $msg->created_at->format('H:i') : '' }}</span>
                                </div>
                                <div class="text-dark" style="white-space: pre-line; line-height: 1.45; font-size: 0.88rem; word-break: break-word;">{{ $msg->mensaje }}</div>

                                <!-- Adjuntos del mensaje -->
                                @if($msg->adjuntos && $msg->adjuntos->isNotEmpty())
                                    <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2">
                                        @foreach($msg->adjuntos as $adj)
                                            @php $esImg = str_starts_with($adj->tipo_mime ?? '', 'image/'); @endphp
                                            @if($esImg)
                                                <a href="{{ asset('storage/' . $adj->ruta_archivo) }}" target="_blank" class="d-block mt-1">
                                                    <img src="{{ asset('storage/' . $adj->ruta_archivo) }}" class="rounded-3 border shadow-sm" style="max-height: 130px; max-width: 100%; object-fit: contain;">
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $adj->ruta_archivo) }}" target="_blank" class="btn btn-sm btn-white bg-white border rounded-2 p-1 px-2 d-inline-flex align-items-center gap-1 text-decoration-none">
                                                    <i class="bi bi-paperclip text-muted"></i>
                                                    <span class="small fw-semibold text-dark text-truncate" style="max-width: 140px;">{{ $adj->nombre_original }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if($esMio && !$esNota)
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0 shadow-sm mt-1" 
                                     style="width: 32px; height: 32px; font-size: 0.75rem; background: #2563eb;">
                                    {{ strtoupper(substr($usuario->nombre_tecnico ?: $usuario->usuario, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div id="chat-empty-msg" class="text-center text-muted py-4">
                            <i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-50 text-primary"></i>
                            Inicia la conversación con la tienda o registra una nota interna técnica.
                        </div>
                    @endforelse
                </div>

                <!-- Barra Móvil Moderna de Entrada de Mensaje (Pill WhatsApp style con selector de nota) -->
                @if($ticket->estado !== 'cerrado' && $ticket->estado !== 'cancelado')
                    <div class="p-2 p-md-3 bg-white border-top">
                        <!-- Toggle Público vs Nota Interna -->
                        <div class="d-flex gap-1.5 mb-2 overflow-auto" style="white-space: nowrap;">
                            <button type="button" class="btn btn-sm btn-primary active rounded-pill px-3 py-1 fw-bold" id="btn-modo-publico" onclick="setModoRespuesta(false)" style="font-size: 11.5px;">
                                <i class="bi bi-chat-left-dots me-1"></i> Respuesta a Tienda
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1 fw-semibold" id="btn-modo-nota" onclick="setModoRespuesta(true)" style="font-size: 11.5px;">
                                <i class="bi bi-shield-lock me-1"></i> Nota Interna Privada
                            </button>
                        </div>

                        <!-- Vista previa de archivos adjuntos / imagen pegada -->
                        <div id="preview-adjuntos" class="d-flex flex-wrap gap-2 mb-2 px-1" style="display: none !important;"></div>

                        <form id="form-chat-enviar" onsubmit="enviarMensajeChat(event)">
                            @csrf
                            <input type="hidden" id="chat-es-nota-interna" value="0">
                            <div class="d-flex align-items-center gap-1.5 p-1 bg-light rounded-pill border shadow-sm" id="chat-input-wrapper">
                                <label class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 text-muted m-0" 
                                       title="Adjuntar archivo o imagen" style="width: 38px; height: 38px; cursor: pointer;">
                                    <i class="bi bi-paperclip fs-5"></i>
                                    <input type="file" id="chat-file-input" multiple class="d-none" accept="image/*,.pdf,.doc,.docx,.txt,.zip" onchange="onArchivosSeleccionados(this)">
                                </label>
                                
                                <textarea id="chat-input-texto" rows="1" class="form-control border-0 bg-transparent shadow-none px-2 py-1.5" 
                                          placeholder="Escribe un mensaje para la tienda... (Ctrl+V para pegar captura)" 
                                          style="resize: none; font-size: 0.9rem; max-height: 100px;" 
                                          onkeydown="onKeyDownChat(event)"></textarea>

                                <button type="submit" id="btn-chat-submit" 
                                        class="btn btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" 
                                        style="width: 38px; height: 38px;">
                                    <i class="bi bi-send-fill" style="font-size: 0.95rem; margin-left: 2px;"></i>
                                </button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mt-1 px-2 text-muted" style="font-size: 0.68rem;">
                            <span><i class="bi bi-info-circle me-1"></i>Pulsa <strong>Enter</strong> para enviar</span>
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
                @php
                    $desc = $ticket->descripcion ?? '';
                    
                    // AnyDesk: profile first, then regex search in ticket description
                    $anydesk = $ticket->solicitante?->anydesk_id;
                    if (empty($anydesk) && preg_match('/(?:anydesk|any|ad)[:\s]*([0-9\s]{9,11})/i', $desc, $mAny)) {
                        $anydesk = trim($mAny[1]);
                    }
                    
                    // Ícono / URDP: profile codigo_usuario or regex search
                    $iconoMba = $ticket->solicitante?->codigo_usuario;
                    if (empty($iconoMba) && preg_match('/(?:urdp|icono|rdp|acceso)[:\s\-]*([a-zA-Z0-9\-\_]+)/i', $desc, $mIco)) {
                        $iconoMba = trim($mIco[1]);
                    }
                    
                    // Usuario MBA: profile usuario_mba or regex search
                    $userMba = $ticket->solicitante?->usuario_mba;
                    if (empty($userMba) && preg_match('/(?:usuario\s*mba|user\s*mba|vendedor)[:\s\-]*([a-zA-Z0-9\-\_\s]+?)(?:\n|\r|,|\.|$)/i', $desc, $mMba)) {
                        $userMba = trim($mMba[1]);
                    }
                    
                    $depto = $ticket->solicitante?->departamento;
                @endphp

                <h6 class="fw-bold text-dark mb-3 text-uppercase small"><i class="bi bi-person-badge me-1 text-primary"></i> Solicitante & Tienda</h6>
                <div class="d-flex flex-column gap-2 small">
                    <div>
                        <div class="text-muted">Nombre del Solicitante:</div>
                        <div class="fw-bold text-dark fs-6">{{ $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : ($ticket->solicitante_nombre ?: 'Solicitante') }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Tienda / Ubicación Origen:</div>
                        <div class="fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $ticket->tienda_nombre ?: ($ticket->sucursalCliente->nombre ?? 'Tienda Externa') }}</div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary mt-1">{{ $ticket->empresa_origen }}</span>
                    </div>
                    @if($depto)
                        <div>
                            <div class="text-muted">Departamento / Área:</div>
                            <div class="fw-semibold text-dark">{{ $depto }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-muted">Correo Institucional:</div>
                        <div class="fw-semibold text-dark">{{ $ticket->solicitante?->correo_tec ?: 'Sin correo registrado' }}</div>
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

                    <!-- AnyDesk ID -->
                    <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-danger small fw-bold"><i class="bi bi-display me-1"></i>AnyDesk ID:</span>
                            @if($anydesk)
                                <button type="button" class="btn btn-xs btn-white bg-white border py-0 px-2 small rounded-2" onclick="navigator.clipboard.writeText('{{ $anydesk }}'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'AnyDesk copiado', showConfirmButton: false, timer: 1500});">
                                    <i class="bi bi-clipboard me-1"></i>Copiar
                                </button>
                            @endif
                        </div>
                        <div class="fw-bold text-dark fs-6 font-monospace" id="span-anydesk">
                            {{ $anydesk ?: 'No registrado' }}
                        </div>
                    </div>

                    <!-- Datos MBA3 & URDP -->
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 mt-2">
                        <div class="text-primary small fw-bold mb-2"><i class="bi bi-hdd-network me-1"></i>Datos MBA3 / Accesos:</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-muted" style="font-size: 0.75rem;">ÍCONO / URDP:</div>
                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">
                                    {{ $iconoMba ?: 'No especificado' }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted" style="font-size: 0.75rem;">USUARIO MBA3:</div>
                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">
                                    {{ $userMba ?: 'No especificado' }}
                                </div>
                            </div>
                        </div>
                    </div>
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
                                <span class="badge bg-warning text-dark p-2 rounded-3">Sin Asignar (Quito)</span>
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

            <!-- Ficha de Archivos y Evidencias Adjuntas -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                @if($esCasoMba)
                    <div class="mb-3 p-2.5 rounded-3 border d-flex align-items-center justify-content-between" style="background: #f5f3ff; border-color: #ddd6fe !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-word-fill fs-4" style="color: #7c3aed;"></i>
                            <div>
                                <div class="fw-bold text-dark small">Formato Word MBA3</div>
                                <div class="text-muted" style="font-size: 10.5px;">Documento oficial generado</div>
                            </div>
                        </div>
                        <a href="{{ route('tickets.word_mba', $ticket->id) }}" class="btn btn-sm btn-purple text-white px-2.5 py-1 rounded-2 shadow-sm fw-semibold" style="background: #7c3aed; font-size: 11.5px;">
                            <i class="bi bi-download"></i> Descargar
                        </a>
                    </div>
                @endif

                @if($ticket->adjuntos->isNotEmpty())
                    <div class="d-flex flex-column gap-2">
                        @foreach($ticket->adjuntos as $adj)
                            @if($adj->es_imagen)
                                <div class="border rounded-3 p-2 bg-light d-flex align-items-center gap-2">
                                    <a href="{{ $adj->url }}" target="_blank" class="d-inline-block rounded-2 overflow-hidden border flex-shrink-0" style="width: 48px; height: 44px;">
                                        <img src="{{ $adj->url }}" alt="{{ $adj->nombre_archivo }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </a>
                                    <div class="overflow-hidden flex-grow-1">
                                        <a href="{{ $adj->url }}" target="_blank" class="fw-bold text-dark text-truncate d-block small text-decoration-none" title="{{ $adj->nombre_archivo }}">
                                            {{ $adj->nombre_archivo }}
                                        </a>
                                        <span class="text-muted" style="font-size: 10.5px;">{{ $adj->tamano_legible }} · {{ $adj->mensaje_id ? 'En chat' : 'Inicial' }}</span>
                                    </div>
                                    <a href="{{ $adj->url }}" target="_blank" download class="btn btn-sm btn-white bg-white border rounded-2 p-1 px-2 text-primary" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            @else
                                <div class="border rounded-3 p-2 bg-light d-flex align-items-center gap-2">
                                    <div class="rounded-2 bg-white border p-2 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden flex-grow-1">
                                        <a href="{{ $adj->url }}" target="_blank" class="fw-bold text-dark text-truncate d-block small text-decoration-none" title="{{ $adj->nombre_archivo }}">
                                            {{ $adj->nombre_archivo }}
                                        </a>
                                        <span class="text-muted" style="font-size: 10.5px;">{{ $adj->tamano_legible }} · {{ $adj->mensaje_id ? 'En chat' : 'Inicial' }}</span>
                                    </div>
                                    <a href="{{ $adj->url }}" target="_blank" download class="btn btn-sm btn-white bg-white border rounded-2 p-1 px-2 text-primary" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="p-3 text-center border rounded-3 bg-light text-muted small">
                        <i class="bi bi-folder-x fs-4 d-block mb-1 text-secondary opacity-50"></i>
                        No se adjuntaron archivos o capturas en este ticket.
                    </div>
                @endif
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
        inputMsg.placeholder = 'Escribe una nota interna (solo visible para técnicos)...';
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

function modalAsignarTicket() {
    const asignadoActualId = "{{ $ticket->asignado_a_id ?? '' }}";
    const esSistemas = {{ $ticket->tipo_ticket === 'sistemas' ? 'true' : 'false' }};
    
    Swal.fire({
        title: esSistemas ? 'Asignar Técnico de Sistemas' : 'Asignar Técnico Responsable',
        html: `
            <div style="text-align: left; font-size: 13px;">
                ${esSistemas ? `
                    <div class="p-2 mb-2 rounded-3 border d-flex align-items-center gap-2" style="background: #f5f3ff; border-color: #ddd6fe !important; color: #6b21a8; font-size: 11.5px;">
                        <i class="bi bi-shield-check fs-5 flex-shrink-0" style="color: #7c3aed;"></i>
                        <div><strong>Área Sistemas TI:</strong> Asignación exclusiva para el equipo técnico de Sistemas.</div>
                    </div>
                ` : ''}
                <div class="mb-2 position-relative">
                    <label class="fw-bold mb-1 d-block text-dark small">
                        <i class="bi bi-search me-1 text-primary"></i>Buscar técnico por nombre:
                    </label>
                    <input type="text" id="swal-search-tecnico" class="form-control form-control-sm" 
                           placeholder="${esSistemas ? 'Escribe el nombre (ej. Erick, Carlos, Omar, Josué...)' : 'Escribe el nombre...'}" 
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

function modalEscalarMba() {
    const currentMbaNum = @json($ticket->numero_ticket_mba ?? '');
    Swal.fire({
        title: 'Escalar a Soporte MBA (Máx. 48h)',
        html: `
            <div style="text-align: left;">
                <div class="alert p-2.5 mb-3 rounded-3 small border" style="background: #f5f3ff; border-color: #ddd6fe !important; color: #6b21a8;">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Utiliza este estado si el caso es complejo y requiere atención directa del soporte de MBA. El plazo máximo de resolución es de <b>48 horas</b>.
                </div>
                <div class="mb-3">
                    <label class="fw-bold mb-1 small text-dark">Número de Ticket / Caso Asignado por MBA <span class="text-danger">*</span>:</label>
                    <input type="text" id="swal-ticket-mba" class="form-control form-control-sm font-monospace fw-bold" placeholder="Ej: MBA-89421 / CASO-4509" value="${currentMbaNum}" required>
                    <div class="form-text text-muted" style="font-size: 11px;">Ingresa el código o número de caso provisto por el soporte de MBA.</div>
                </div>
                <div class="mb-2">
                    <label class="fw-bold mb-1 small text-dark">Observaciones / Motivo de Escalamiento (Opcional):</label>
                    <textarea id="swal-motivo-mba" class="form-control form-control-sm" rows="2" placeholder="Indica qué módulo o ajuste se solicitó a MBA..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-clock-history me-1"></i> Poner en Manos de MBA (48h)',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#9333ea',
        cancelButtonColor: '#64748b',
        preConfirm: () => {
            const ticketMba = (document.getElementById('swal-ticket-mba')?.value || '').trim();
            const motivo = (document.getElementById('swal-motivo-mba')?.value || '').trim();
            if (!ticketMba) {
                Swal.showValidationMessage('Debe ingresar el número de ticket / caso de MBA.');
                return false;
            }
            return { ticketMba, motivo };
        }
    }).then((r) => {
        if (r.isConfirmed) {
            cambiarEstadoDirecto('en_mba', r.value.motivo, null, null, r.value.ticketMba);
        }
    });
}

function modalResolverTicket(tipo = null) {
    const asignadoAIdCheck = {{ $ticket->asignado_a_id ? $ticket->asignado_a_id : 'null' }};
    if (!asignadoAIdCheck) {
        Swal.fire({
            icon: 'warning',
            title: 'Técnico Requerido',
            text: 'No se puede resolver el ticket porque no tiene un técnico asignado. Por favor asigna un técnico responsable antes de resolverlo.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#2563eb'
        });
        return;
    }
    const isMbaActive = {{ $ticket->estado === 'en_mba' ? 'true' : 'false' }};
    const currentMbaNum = @json($ticket->numero_ticket_mba ?? '');
    const isCasoMba = {{ ($esCasoMba || !empty($ticket->numero_ticket_mba)) ? 'true' : 'false' }};

    Swal.fire({
        title: isMbaActive || tipo === 'mba' ? 'Cerrar / Resolver Caso MBA' : 'Resolver Ticket',
        html: `
            <div style="text-align: left;">
                <p class="small text-muted mb-2">Al marcar como resuelto, el solicitante podrá calificar la atención recibida.</p>

                ${isCasoMba ? `
                <div class="p-2.5 mb-3 bg-light rounded-3 border">
                    <label class="fw-bold mb-1 small text-dark d-block">Tipo de Resolución Aplicada <span class="text-danger">*</span>:</label>
                    <div class="d-flex flex-wrap gap-3">
                        <label class="form-check-label d-flex align-items-center gap-1.5 small cursor-pointer">
                            <input type="radio" name="tipo_resolucion" value="soporte" ${isMbaActive ? '' : 'checked'} class="form-check-input mt-0" onchange="toggleMbaNumInput(false)">
                            <b>Resuelto por Soporte Interno</b>
                        </label>
                        <label class="form-check-label d-flex align-items-center gap-1.5 small cursor-pointer">
                            <input type="radio" name="tipo_resolucion" value="mba" ${isMbaActive ? 'checked' : ''} class="form-check-input mt-0" onchange="toggleMbaNumInput(true)">
                            <b style="color: #9333ea;">Resuelto por Soporte MBA (Externo)</b>
                        </label>
                    </div>
                    <div id="contenedor-res-mba-num" class="mt-2 ${isMbaActive ? '' : 'd-none'}">
                        <label class="small fw-bold text-dark">N° Ticket / Caso MBA Aplicado:</label>
                        <input type="text" id="swal-resolucion-mba-num" class="form-control form-control-sm font-monospace" placeholder="Ej: MBA-89421" value="${currentMbaNum}">
                    </div>
                </div>
                ` : ''}

                <label class="fw-bold mb-1 small text-dark">Descripción de la Solución Aplicada <span class="text-danger">*</span>:</label>
                <textarea id="swal-solucion" class="swal2-textarea" placeholder="Describe detalladamente la solución técnica o el informe de MBA..." style="width: 100%; margin: 0; height: 95px; font-size: 13px;"></textarea>

                <div class="mt-3 p-3 bg-light rounded-3 border" id="swal-paste-dropzone" style="border: 2px dashed #cbd5e1 !important; transition: all 0.2s;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="fw-bold d-flex align-items-center gap-1.5 small text-dark mb-0">
                            <i class="bi bi-camera-fill text-primary"></i> Foto o Captura de Evidencia (Opcional):
                        </label>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-0.5" style="font-size: 11px;">
                            <i class="bi bi-clipboard-check me-1"></i> Ctrl + V disponible
                        </span>
                    </div>

                    <!-- Vista previa de captura pegada o seleccionada -->
                    <div id="swal-preview-container" class="d-none mb-2 p-2 bg-white rounded-3 border d-flex align-items-center justify-content-between gap-2 shadow-sm">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <img id="swal-preview-img" src="" alt="Captura" style="width: 46px; height: 46px; object-fit: cover; border-radius: 6px;" class="border flex-shrink-0">
                            <div class="text-truncate">
                                <div class="fw-bold small text-dark text-truncate" id="swal-preview-nombre">captura.png</div>
                                <div class="text-success small" style="font-size: 11px;"><i class="bi bi-check-circle-fill me-1"></i>Captura lista para adjuntar</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 flex-shrink-0" onclick="removerEvidenciaPaste()" title="Eliminar captura">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <div id="swal-upload-input-group">
                        <input type="file" id="swal-evidencia" class="form-control form-control-sm bg-white" accept="image/*,.pdf,.png,.jpg,.jpeg">
                        <div class="form-text text-muted mt-1" style="font-size: 11px;">
                            Presiona <b>Ctrl + V</b> en cualquier lugar de esta ventana para pegar una captura del portapapeles.
                        </div>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Marcar como Resuelto',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        didOpen: () => {
            window.toggleMbaNumInput = function(show) {
                const el = document.getElementById('contenedor-res-mba-num');
                if (el) el.className = show ? 'mt-2' : 'mt-2 d-none';
            };

            let evidenciaFile = null;

            function mostrarPreview(file) {
                evidenciaFile = file;
                const container = document.getElementById('swal-preview-container');
                const img = document.getElementById('swal-preview-img');
                const nombre = document.getElementById('swal-preview-nombre');
                if (container && img && nombre) {
                    nombre.innerText = file.name || 'captura_solucion.png';
                    if (file.type && file.type.startsWith('image/')) {
                        img.src = URL.createObjectURL(file);
                        img.className = 'border flex-shrink-0';
                    } else {
                        img.src = '';
                        img.className = 'd-none';
                    }
                    container.classList.remove('d-none');
                }
            }

            window.removerEvidenciaPaste = function() {
                evidenciaFile = null;
                const fileInput = document.getElementById('swal-evidencia');
                if (fileInput) fileInput.value = '';
                const container = document.getElementById('swal-preview-container');
                if (container) container.classList.add('d-none');
                const img = document.getElementById('swal-preview-img');
                if (img) img.src = '';
            };

            const fileInput = document.getElementById('swal-evidencia');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        mostrarPreview(e.target.files[0]);
                    }
                });
            }

            const pasteHandler = function(e) {
                if (!e.clipboardData || !e.clipboardData.items) return;
                const items = e.clipboardData.items;
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        const blob = items[i].getAsFile();
                        if (blob) {
                            const ext = blob.type ? (blob.type.split('/')[1] || 'png') : 'png';
                            const file = new File([blob], `captura_solucion_${Date.now()}.${ext}`, { type: blob.type || 'image/png' });
                            mostrarPreview(file);
                            
                            const dropzone = document.getElementById('swal-paste-dropzone');
                            if (dropzone) {
                                dropzone.style.borderColor = '#10b981';
                                dropzone.style.background = '#f0fdf4';
                                setTimeout(() => {
                                    dropzone.style.borderColor = '#cbd5e1';
                                    dropzone.style.background = '#f8fafc';
                                }, 1200);
                            }
                            break;
                        }
                    }
                }
            };

            window.addEventListener('paste', pasteHandler);

            window.__cleanupSwalPaste = function() {
                window.removeEventListener('paste', pasteHandler);
            };

            window.__getEvidenciaFile = function() {
                return evidenciaFile || (fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null);
            };
        },
        willClose: () => {
            if (window.__cleanupSwalPaste) {
                window.__cleanupSwalPaste();
            }
        },
        preConfirm: () => {
            const solucion = document.getElementById('swal-solucion').value.trim();
            if (!solucion) {
                Swal.showValidationMessage('La descripción de la solución es obligatoria.');
                return false;
            }
            const mbaNum = document.getElementById('swal-resolucion-mba-num')?.value.trim() || null;
            const file = window.__getEvidenciaFile ? window.__getEvidenciaFile() : null;
            return { solucion, file, mbaNum };
        }
    }).then((r) => {
        if (r.isConfirmed) {
            cambiarEstadoDirecto('resuelto', null, r.value.solucion, r.value.file, r.value.mbaNum);
        }
    });
}

function cambiarEstadoDirecto(estado, motivo = null, solucion = null, file = null, numeroTicketMba = null) {
    Swal.showLoading();

    const fd = new FormData();
    fd.append('estado', estado);
    if (motivo) fd.append('motivo', motivo);
    if (solucion) fd.append('solucion', solucion);
    if (file) fd.append('evidencia', file);
    if (numeroTicketMba) fd.append('numero_ticket_mba', numeroTicketMba);

    fetch("{{ route('tickets.cambiar_estado', $ticket->id) }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: '¡Estado Actualizado!',
                text: res.mensaje,
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
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
