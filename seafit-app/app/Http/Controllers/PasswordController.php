<?php

/**
 * Controlador de seguridad de contrasenas.
 * Gestiona recuperacion por email y cambios desde perfil.
 */
namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * Muestra el formulario para solicitar recuperacion por email.
     */
    public function mostrarFormularioEmail()
    {
        return view('usuario.recuperar-password');
    }

    /**
     * Genera token y envia enlace de recuperacion.
     */
    public function enviarEnlace(Request $request)
    {
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

        Mail::send('emails.recuperar-password', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Recuperar contrasena - SeaFit');
        });

        return back()->with('status', 'Listo. Revisa tu bandeja de entrada, te hemos enviado el enlace de recuperacion.');
    }

    /**
     * Muestra el formulario para establecer nueva contrasena.
     */
    public function mostrarFormularioReset($token)
    {
        return view('usuario.reset-password', ['token' => $token]);
    }

    /**
     * Guarda la nueva contrasena si email y token son validos.
     */
    public function actualizarPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
        ]);

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

        return redirect()->route('login')->with('success', 'Tu contrasena ha sido cambiada con exito. Ya puedes entrar.');
    }

    /**
     * Cambia contrasena desde el perfil del usuario logueado.
     */
    public function cambiarPasswordPerfil(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'La confirmacion de la nueva contrasena no coincide.',
            'password.min' => 'La nueva contrasena debe tener al menos 8 caracteres.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contrasena actual no es correcta.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Contrasena actualizada correctamente.');
    }

    /**
     * Formulario obligatorio de primer inicio para cambiar contrasena temporal.
     */
    public function mostrarFormularioCambioInicial()
    {
        return view('usuario.forzar-cambiar-password');
    }

    /**
     * Guarda nueva contrasena en el primer inicio y desactiva el bloqueo.
     */
    public function cambiarPasswordInicial(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'La confirmacion no coincide.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
        ]);

        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('perfil')->with('success', 'Contrasena actualizada.');
    }
}
