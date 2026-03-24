@extends('moldes.inicio')

@section('contenido')
<div class="max-w-md mx-auto my-10 p-8 bg-white rounded-3xl shadow-lg border">
    <h2 class="text-2xl font-bold mb-6 text-[#0A1931]">Añadir Nueva Tarjeta</h2>

    <div id="card-element" class="p-4 border rounded-xl bg-gray-50 mb-4">
        </div>

    <button id="card-button" data-secret="{{ $intent->client_secret }}" 
        class="w-full bg-[#0A1931] text-white py-3 rounded-xl font-bold hover:bg-[#1A3878] transition-colors">
        Guardar Tarjeta Segura
    </button>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ env("STRIPE_KEY") }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const cardButton = document.getElementById('card-button');
    const clientSecret = cardButton.dataset.secret;

    cardButton.addEventListener('click', async (e) => {
        cardButton.disabled = true;
        const { setupIntent, error } = await stripe.confirmCardSetup(
            clientSecret, { payment_method: { card: cardElement } }
        );

        if (error) {
            alert('Error: ' + error.message);
            cardButton.disabled = false;
        } else {
            // Enviamos el ID a nuestro controlador para guardarlo en la BD
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('pago.guardar') }}";
            form.innerHTML = `@csrf <input type="hidden" name="payment_method" value="${setupIntent.payment_method}">`;
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endsection