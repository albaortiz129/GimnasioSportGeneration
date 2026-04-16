{{-- Vista para guardar una nueva tarjeta con Stripe. --}}
@extends('layouts.app')

@section('contenido')
    <div class="max-w-md mx-auto my-10 p-8 bg-white rounded-3xl shadow-lg border">
        <h2 class="text-2xl font-bold mb-6 text-[#0A1931]">Añadir nueva tarjeta</h2>

        @if(!$stripeKey)
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                Falta STRIPE_KEY en .env
            </div>
        @endif

        <div id="card-element" class="p-4 border rounded-xl bg-gray-50 mb-3"></div>
        <div id="card-errors" class="text-red-600 text-sm mb-3"></div>

        <button id="card-button" data-secret="{{ $intent->client_secret }}"
            class="w-full bg-[#0A1931] text-white py-3 rounded-xl font-bold hover:bg-[#1A3878] transition-colors"
            @disabled(!$stripeKey)>
            Guardar tarjeta
        </button>
    </div>

    @if($stripeKey)
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            // Inicializa Stripe y monta el campo de tarjeta.
            const stripe = Stripe(@json($stripeKey));
            const elements = stripe.elements();
            const cardElement = elements.create('card');
            cardElement.mount('#card-element');

            const cardButton = document.getElementById('card-button');
            const errorsBox = document.getElementById('card-errors');

            cardButton.addEventListener('click', async () => {
                // Evita doble envio y limpia errores previos.
                cardButton.disabled = true;
                errorsBox.textContent = '';

                const clientSecret = cardButton.dataset.secret;

                const { setupIntent, error } = await stripe.confirmCardSetup(clientSecret, {
                    payment_method: { card: cardElement }
                });

                if (error) {
                    errorsBox.textContent = error.message;
                    cardButton.disabled = false;
                    return;
                }

                // Envía el payment_method al backend para guardarlo.
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('pago.guardar') }}";

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";

                const pm = document.createElement('input');
                pm.type = 'hidden';
                pm.name = 'payment_method';
                pm.value = setupIntent.payment_method;

                form.appendChild(csrf);
                form.appendChild(pm);
                document.body.appendChild(form);
                form.submit();
            });
        </script>
    @endif
@endsection
