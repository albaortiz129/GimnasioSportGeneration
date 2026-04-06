// Punto de entrada JS: monta el formulario de registro en React.
import './bootstrap';
import ReactDOM from 'react-dom/client';
import { loadStripe } from '@stripe/stripe-js';
import { Elements } from '@stripe/react-stripe-js';
import FormularioRegistro from './componentes/formularioRegistro';

// Clave publica de Stripe usada por el formulario de registro.
const stripePromise = loadStripe('pk_test_51TEX1vLV86wly52B4QWT61O9A8VQauOMPGGP8wwsNucKZoT2hFYJG4vOWwHNHziOcgEWEBEnVWaNN5tVKLWw212W000QGBAbAp');

const rootElement = document.getElementById('react-root');

if (rootElement) {
    const root = ReactDOM.createRoot(rootElement);

    // El formulario se renderiza dentro del provider de Stripe.
    root.render(
        <Elements stripe={stripePromise}>
            <FormularioRegistro />
        </Elements>
    );
}
