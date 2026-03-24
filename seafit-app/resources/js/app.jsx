import './bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import FormularioRegistro from './componentes/formularioRegistro'; 

// 1. Importamos las herramientas de Stripe
import { loadStripe } from '@stripe/stripe-js';
import { Elements } from '@stripe/react-stripe-js';

// 2. Cargamos tu clave pública (Copia la que empieza por pk_test de tu .env)
const stripePromise = loadStripe('pk_test_51TEX1vLV86wly52B4QWT61O9A8VQauOMPGGP8wwsNucKZoT2hFYJG4vOWwHNHziOcgEWEBEnVWaNN5tVKLWw212W000QGBAbAp');

const rootElement = document.getElementById('react-root');

if (rootElement) {
    const root = ReactDOM.createRoot(rootElement);
    // 3. Envolvemos el formulario con el Provider de Elements
    root.render(
        <Elements stripe={stripePromise}>
            <FormularioRegistro />
        </Elements>
    );
}