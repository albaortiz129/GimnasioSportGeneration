{{-- Chat lateral basico de IA para soporte rapido del usuario. --}}
@if(config('services.ai_chat.enabled'))
    <div id="ai-chat-wrapper" class="fixed right-5 bottom-5 z-[140]">
        {{-- Boton flotante para abrir/cerrar el chat. --}}
        <button id="ai-chat-toggle"
            class="bg-[#0A1931] text-white rounded-full px-4 py-3 font-bold shadow-lg hover:bg-[#1A3878] transition-colors">
            Chat IA
        </button>

        {{-- Panel lateral del chat. --}}
        <div id="ai-chat-panel"
            class="hidden mt-3 w-[340px] max-w-[92vw] bg-white border border-gray-200 rounded-2xl shadow-2xl">
            <div class="p-4 border-b">
                <h3 class="font-black text-[#0A1931]">Asistente SeaFit</h3>
                <p class="text-xs text-gray-500">Respuestas basicas sobre la web y servicios.</p>
            </div>

            <div id="ai-chat-messages" class="h-[320px] overflow-y-auto p-3 space-y-2 bg-[#f8fafc]">
                <div class="text-sm bg-white border rounded-xl p-2">
                    Hola, soy el asistente de SeaFit. Preguntame algo.
                </div>
            </div>

            <form id="ai-chat-form" class="p-3 border-t flex gap-2">
                <input id="ai-chat-input" type="text" maxlength="500" required placeholder="Escribe tu pregunta..."
                    class="flex-1 border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1A3878]/30">
                <button type="submit"
                    class="bg-[#0A1931] text-white px-3 py-2 rounded-xl font-bold text-sm hover:bg-[#1A3878]">
                    Enviar
                </button>
            </form>
        </div>
    </div>

    <script>
        (() => {
            // Referencias de la interfaz del chat.
            const toggleBtn = document.getElementById('ai-chat-toggle');
            const panel = document.getElementById('ai-chat-panel');
            const form = document.getElementById('ai-chat-form');
            const input = document.getElementById('ai-chat-input');
            const messages = document.getElementById('ai-chat-messages');

            if (!toggleBtn || !panel || !form || !input || !messages) return;

            const url = @json(route('ia.chat'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // Abre o cierra el panel.
            toggleBtn.addEventListener('click', () => {
                panel.classList.toggle('hidden');
                if (!panel.classList.contains('hidden')) input.focus();
            });

            // Pinta un mensaje en el chat.
            function addMessage(text, from = 'bot') {
                const bubble = document.createElement('div');
                bubble.className = from === 'user'
                    ? 'text-sm bg-[#0A1931] text-white rounded-xl p-2 ml-8'
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
                    const reply = data?.reply || 'No tengo esa informacion ahora. Puedes contactar en soporte.seafit@gmail.com.';
                    addMessage(reply, 'bot');
                } catch (error) {
                    addMessage('No tengo esa informacion ahora. Puedes contactar en soporte.seafit@gmail.com.', 'bot');
                } finally {
                    input.disabled = false;
                    input.focus();
                }
            });
        })();
    </script>
@endif
