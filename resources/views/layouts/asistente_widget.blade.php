{{-- Estilos y markup del Asistente Flotante de IA --}}
<div id="ai-chat-widget" style="position: fixed; bottom: 25px; right: 25px; z-index: 99999; font-family: system-ui, -apple-system, sans-serif;">
    
    {{-- Botón Flotante Redondo --}}
    <button id="ai-chat-btn" style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6d28d9); border: none; color: white; font-size: 24px; cursor: pointer; box-shadow: 0 4px 20px rgba(109, 40, 217, 0.4); display: flex; align-items: center; justify-content: center; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <i class="bi bi-chat-left-dots-fill" id="ai-chat-icon" style="transition: transform 0.3s;"></i>
    </button>

    {{-- Ventana de Chat (Glassmorphism Premium) --}}
    <div id="ai-chat-window" style="display: none; position: absolute; bottom: 70px; right: 0; width: 370px; height: 500px; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); flex-direction: column; overflow: hidden; transition: all 0.3s ease; transform: scale(0.9); transform-origin: bottom right; opacity: 0;">
        
        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); padding: 15px 18px; color: white; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-cpu" style="font-size: 16px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; letter-spacing: 0.3px;">Asistente SGN-IA</h4>
                    <span style="font-size: 10.5px; opacity: 0.9; display: flex; align-items: center; gap: 4px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span> Activo ahora
                    </span>
                </div>
            </div>
            <button id="ai-chat-close" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="bi bi-x-lg"></i></button>
        </div>

        {{-- Contenedor de Mensajes --}}
        <div id="ai-chat-messages" style="flex: 1; padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; scroll-behavior: smooth;">
            {{-- Mensaje de Bienvenida --}}
            <div class="ai-msg-assistant">
                ¡Hola! Soy tu asistente de Inteligencia Artificial para el sistema. ¿En qué te puedo ayudar hoy?
            </div>
            
            {{-- Sugerencias Rápidas --}}
            <div id="ai-chat-quick-replies" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; flex-shrink: 0;">
                <button class="ai-qr-btn" onclick="_aiEnviarPregunta('¿Qué órdenes tengo pendientes?')">📋 Pendientes</button>
                <button class="ai-qr-btn" onclick="_aiEnviarPregunta('¿Cómo van las solicitudes de Notas de Crédito?')">💵 Notas de Crédito</button>
                <button class="ai-qr-btn" onclick="_aiEnviarPregunta('¿Qué informes técnicos se han redactado hoy?')">📝 Informes de hoy</button>
            </div>
        </div>

        {{-- Formulario de Entrada --}}
        <div style="padding: 12px; border-top: 1px solid rgba(0,0,0,0.06); background: rgba(255,255,255,0.9); display: flex; gap: 8px; align-items: center; flex-shrink: 0;">
            <input type="text" id="ai-chat-input" placeholder="Pregúntame algo (Ej: UIO-000001)..." style="flex: 1; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 20px; font-size: 12.5px; outline: none; transition: border-color 0.2s; box-sizing: border-box;" onkeydown="if(event.key==='Enter') _aiEnviarMensajeInput()">
            <button id="ai-chat-send" onclick="_aiEnviarMensajeInput()" style="width: 36px; height: 36px; border-radius: 50%; background: #8b5cf6; border: none; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; flex-shrink: 0;">
                <i class="bi bi-send-fill" style="font-size: 13px;"></i>
            </button>
        </div>
    </div>
</div>

{{-- Estilos Auxiliares CSS --}}
<style>
.ai-qr-btn {
    background: white; border: 1.5px solid rgba(139, 92, 246, 0.3); color: #6d28d9;
    padding: 6px 12px; border-radius: 15px; font-size: 11px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.ai-qr-btn:hover { background: #f5f3ff; border-color: #8b5cf6; }
.ai-msg-tech { align-self: flex-end; background: #8b5cf6; color: white; padding: 10px 14px; border-radius: 14px 14px 2px 14px; max-width: 85%; font-size: 12.5px; line-height: 1.45; box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2); word-break: break-word; }
.ai-msg-assistant { align-self: flex-start; background: #fff; border: 1px solid rgba(0,0,0,0.05); color: #1e293b; padding: 10px 14px; border-radius: 14px 14px 14px 2px; max-width: 85%; font-size: 12.5px; line-height: 1.45; box-shadow: 0 2px 5px rgba(0,0,0,0.03); word-break: break-word; }
.ai-typing-dots { display: flex; gap: 4px; padding: 10px 14px; align-self: flex-start; background: #fff; border: 1px solid rgba(0,0,0,0.05); border-radius: 12px; }
.ai-typing-dots span { width: 6px; height: 6px; background: #8b5cf6; border-radius: 50%; animation: aiPulse 1.2s infinite; }
.ai-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.ai-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes aiPulse { 0%, 100% { transform: scale(0.6); opacity: 0.4; } 50% { transform: scale(1.1); opacity: 1; } }
</style>

{{-- JS del Widget --}}
<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('ai-chat-btn');
        var win = document.getElementById('ai-chat-window');
        var close = document.getElementById('ai-chat-close');
        var input = document.getElementById('ai-chat-input');
        var CSRF = '{{ csrf_token() }}';

        if (!btn || !win) return;

        btn.addEventListener('click', function () {
            if (win.style.display === 'none' || win.style.display === '') {
                win.style.display = 'flex';
                setTimeout(function () {
                    win.style.opacity = '1';
                    win.style.transform = 'scale(1)';
                }, 10);
                document.getElementById('ai-chat-icon').className = 'bi bi-x-lg';
                input.focus();
            } else {
                _aiCerrarChat();
            }
        });

        if (close) {
            close.addEventListener('click', _aiCerrarChat);
        }

        function _aiCerrarChat() {
            win.style.opacity = '0';
            win.style.transform = 'scale(0.9)';
            document.getElementById('ai-chat-icon').className = 'bi bi-chat-left-dots-fill';
            setTimeout(function () {
                win.style.display = 'none';
            }, 300);
        }

        window._aiEnviarPregunta = function (txt) {
            var msgs = document.getElementById('ai-chat-messages');
            
            // 1. Mostrar mensaje del usuario
            var userDiv = document.createElement('div');
            userDiv.className = 'ai-msg-tech';
            userDiv.textContent = txt;
            msgs.appendChild(userDiv);
            
            // Ocultar quick-replies provisionalmente
            var qreplies = document.getElementById('ai-chat-quick-replies');
            if (qreplies) qreplies.style.display = 'none';

            // 2. Mostrar indicador de escribiendo
            var typingDiv = document.createElement('div');
            typingDiv.className = 'ai-typing-dots';
            typingDiv.id = 'ai-typing-indicator';
            typingDiv.innerHTML = '<span></span><span></span><span></span>';
            msgs.appendChild(typingDiv);
            msgs.scrollTop = msgs.scrollHeight;

            // 3. Petición AJAX
            fetch('{{ url("/operaciones/asistente-ia/preguntar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ consulta: txt })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var ind = document.getElementById('ai-typing-indicator');
                if (ind) ind.remove();

                var assistantDiv = document.createElement('div');
                assistantDiv.className = 'ai-msg-assistant';
                
                if (data.ok) {
                    // Formatear Markdown simple a saltos de línea, negritas y listas
                    var formatted = data.respuesta
                        .replace(/\n/g, '<br>')
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/- (.*?)(<br>|$)/g, '• $1$2');
                    assistantDiv.innerHTML = formatted;
                } else {
                    assistantDiv.innerHTML = '<span style="color:#ef4444;"><i class="bi bi-exclamation-triangle-fill"></i> ' + (data.error || 'Ocurrió un error.') + '</span>';
                }
                
                msgs.appendChild(assistantDiv);
                
                // Volver a mostrar quick-replies al final
                if (qreplies) {
                    msgs.appendChild(qreplies);
                    qreplies.style.display = 'flex';
                }

                msgs.scrollTop = msgs.scrollHeight;
            })
            .catch(function () {
                var ind = document.getElementById('ai-typing-indicator');
                if (ind) ind.remove();

                var errDiv = document.createElement('div');
                errDiv.className = 'ai-msg-assistant';
                errDiv.innerHTML = '<span style="color:#ef4444;"><i class="bi bi-exclamation-triangle-fill"></i> Error al conectar con el servidor.</span>';
                msgs.appendChild(errDiv);
                if (qreplies) msgs.appendChild(qreplies);
                msgs.scrollTop = msgs.scrollHeight;
            });
        };

        window._aiEnviarMensajeInput = function () {
            var val = input.value.trim();
            if (val.length === 0) return;
            input.value = '';
            _aiEnviarPregunta(val);
        };
    });
}());
</script>
