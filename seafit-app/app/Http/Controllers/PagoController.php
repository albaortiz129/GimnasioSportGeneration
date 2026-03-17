<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoController extends Controller
{
    // 1. Mostrar la vista principal de pagos
    public function index()
    {
        $user = Auth::user();
        return view('usuario.pago', compact('user'));
    }

    // 2. Cancelar el plan actual
    public function cancelarPlan()
    {
        $user = Auth::user();
        $user->tarifa = 'cancelada'; 
        $user->save();

        return back()->with('info', 'Tu suscripción ha sido cancelada. Ya no recibirás más cobros.');
    }

    // 3. Establecer tarjeta principal (ESTA ES LA QUE TE FALTABA)
    public function establecerPrincipal()
    {
        // Simulamos la acción
        return back()->with('success', 'PayPal se ha establecido como tu método de pago principal.');
    }

    // 4. Eliminar tarjeta (ESTA TAMBIÉN TE FALTABA)
    public function eliminarMetodo()
    {
        // Simulamos el borrado
        return back()->with('success', 'El método de pago ha sido eliminado correctamente de tu cuenta.');
    }

    // 5. Descargar Factura (Simulada por ahora para evitar errores si no has instalado el generador PDF)
    public function descargarFactura($id)
    {
        $user = Auth::user();
        return response()->streamDownload(function () use ($id, $user) {
            echo "Factura SeaFit #$id \nSocio: " . $user->nombre . "\nTotal: 29,99€";
        }, "factura.txt");
    }

    // 6. Página para añadir nuevo método
    public function nuevoMetodo()
    {
        return "Módulo de pasarela de pago (Stripe/PayPal) en construcción.";
    }
}