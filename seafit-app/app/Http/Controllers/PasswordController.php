<?php

/**
 * Controlador de seguridad de contraseñas.
 * Gestiona recuperacion por email y cambios desde perfil.
 */
namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * Muestra el formulario para solicitar recuperacion por email.
     */
    public function showRequestForm()
    {
        return view('user.forgot-password');
    }

    /**
     * Genera token y envia enlace de recuperacion.
     */
    public function sendResetLink(Request $request)
    {
        // Solo se necesita el email para generar enlace.
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No encontramos ningun socio con este correo electronico.']);
        }

        $token = Str::random(64);

        // Si ya habia token, se reemplaza por uno nuevo.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now(),
            ]
        );

        try {
            // Envio simple con plantilla Blade.
            Mail::send('emails.password-reset', ['token' => $token], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Recuperar contraseña - SeaFit');
            });
        } catch (\Throwable $e) {
            // Evita error 500 si SMTP falla en produccion y deja rastro en logs.
            Log::error('Error al enviar correo de recuperacion.', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'No se pudo enviar el correo ahora mismo. Intentalo de nuevo en unos minutos.',
            ]);
        }

        return back()->with('status', 'Listo. Revisa tu bandeja de entrada, te hemos enviado el enlace de recuperacion.');
    }

    /**
     * Muestra el formulario para establecer nueva contraseña.
     */
    public function showResetForm($token)
    {
        return view('user.reset-password', ['token' => $token]);
    }

    /**
     * Guarda la nueva contraseña si email y token son validos.
     */
    public function updatePassword(Request $request)
    {
        // Token + email + password confirmada.
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
        ]);

        // Comprueba que el token pertenece a ese email.
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'El enlace de recuperacion no es valido o ha caducado.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No existe una cuenta asociada a ese correo.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // El token se elimina para impedir reutilizacion.
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Tu contraseña ha sido cambiada con exito. Ya puedes entrar.');
    }

    /**
     * Cambia contraseña desde el perfil del usuario logueado.
     */
    public function changeProfilePassword(Request $request)
    {
        // Cambio de password desde cuenta logueada.
        $request->validate([
            'password_actual' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'La confirmacion de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'contraseña actualizada correctamente.');
    }

    /**
     * Formulario obligatorio de primer inicio para cambiar contraseña temporal.
     */
    public function showInitialChangeForm()
    {
        return view('user.force-change-password');
    }

    /**
     * Guarda nueva contraseña en el primer inicio y desactiva el bloqueo.
     */
    public function changeInitialPassword(Request $request)
    {
        // Primer acceso con clave temporal.
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'La confirmacion no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('perfil')->with('success', 'contraseña actualizada.');
    }
}
