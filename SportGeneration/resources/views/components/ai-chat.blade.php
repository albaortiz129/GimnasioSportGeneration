{{-- Chat de IA para soporte rÃ¡pido del usuario. --}}
@if(config('services.ai_chat.enabled'))
    <div id="ai-chat-wrapper" class="fixed right-3 sm:right-5 bottom-5 z-[140] max-w-[calc(100vw-1rem)]">
        {{-- BotÃ³n para abrir/cerrar el chat. --}}
        <button id="ai-chat-toggle"
            class="bg-[#265E1F] text-white rounded-full px-4 py-3 font-bold shadow-lg hover:bg-[#265E1F] transition-colors">
            Chat
        </button>

        {{-- Panel del chat. --}}
        <div id="ai-chat-panel"
            class="hidden mt-3 w-[340px] max-w-[calc(100vw-1rem)] bg-white border border-gray-200 rounded-2xl shadow-2xl">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="font-black text-[#265E1F]">Asistente Sport Generation</h3>
                <button id="ai-chat-close" type="button"
                    class="text-gray-500 hover:text-[#265E1F] font-bold text-lg leading-none" aria-label="Cerrar chat">
                    &times;
                </button>
            </div>

            <div id="ai-chat-messages" class="h-[320px] overflow-y-auto p-3 space-y-2 bg-[#EAF7DB]">
                <div class="text-sm bg-white border rounded-xl p-2">
                    Hola, soy el asistente de Sport Generation. Â¿En quÃ© puedo ayudarte?
                </div>
            </div>

            <form id="ai-chat-form" class="p-3 border-t flex gap-2">
                <input id="ai-chat-input" type="text" maxlength="500" required placeholder="Escribe tu pregunta..."
                    class="flex-1 border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#265E1F]/30">
                <button type="submit"
                    class="bg-[#265E1F] text-white px-3 py-2 rounded-xl font-bold text-sm hover:bg-[#265E1F]">
                    Enviar
                </button>
            </form>
        </div>
    </div>

    <script>
        (() => {
            // Referencias de la interfaz del chat.
            const toggleBtn = document.getElementById('ai-chat-toggle');
            const closeBtn = document.getElementById('ai-chat-close');
            const panel = document.getElementById('ai-chat-panel');
            const form = document.getElementById('ai-chat-form');
            const input = document.getElementById('ai-chat-input');
            const messages = document.getElementById('ai-chat-messages');

            if (!toggleBtn || !panel || !form || !input || !messages) return;

            const url = @json(route('ia.chat'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // Muestra el panel y oculta el botÃ³n flotante.
            function openChat() {
                panel.classList.remove('hidden');
                toggleBtn.classList.add('hidden');
                input.focus();
            }

            // Oculta el panel y vuelve a mostrar el botÃ³n flotante.
            function closeChat() {
                panel.classList.add('hidden');
                toggleBtn.classList.remove('hidden');
            }

            // Abre el panel desde el botÃ³n flotante.
            toggleBtn.addEventListener('click', openChat);

            // Cierra el panel desde el botÃ³n de cierre.
            if (closeBtn) {
                closeBtn.addEventListener('click', closeChat);
            }

            // FunciÃ³n para enviar el mensaje en el chat.
            function addMessage(text, from = 'bot') {
                const bubble = document.createElement('div');
                bubble.className = from === 'user'
                    ? 'text-sm bg-[#265E1F] text-white rounded-xl p-2 ml-8'
                    : 'text-sm bg-white border rounded-xl p-2 mr-8';
                bubble.textContent = text;
                messages.appendChild(bubble);
                messages.scrollTop = messages.scrollHeight;
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const text = (input.value || '').trim();
                if (!text) return;

                addMessage(text, 'user');
                input.value = '';
                input.disabled = true;

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ message: text }),
                    });

                    const data = await res.json();
                    const reply = data?.reply || 'No tengo esa informaciÃ³n ahora. Puedes contactar en soporte.seafit@gmail.com.';
                    addMessage(reply, 'bot');
                } catch (error) {
                    addMessage('No tengo esa informaciÃ³n ahora. Puedes contactar en soporte.seafit@gmail.com.', 'bot');
                } finally {
                    input.disabled = false;
                    input.focus();
                }
            });
        })();
    </script>
@endif

