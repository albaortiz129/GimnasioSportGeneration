<?php

/**
 * Controlador de seguridad: recuperacion y cambio de contrasena.
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
     * Muestra el formulario para pedir recuperacion por email.
     */
    public function mostrarFormularioEmail()
    {
        return view('usuario.recuperar-password');
    }

    /**
     * Crea un token y envia el enlace de recuperacion.
     */
    public function enviarEnlace(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No encontramos ningún socio con este correo electrónico.']);
        }

        $token = Str::random(64);

        // Si ya existia un token para ese email, se reemplaza.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now(),
            ]
        );

        Mail::send('emails.recuperar-password', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Recuperar contraseña - SeaFit');
        });

        return back()->with('status', '¡Listo! Revisa tu bandeja de entrada, te hemos enviado el enlace de recuperación.');
    }

    /**
     * Muestra el formulario para poner una nueva contrasena.
     */
    public function mostrarFormularioReset($token)
    {
        return view('usuario.reset-password', ['token' => $token]);
    }

    /**
     * Guarda la nueva contrasena si email y token son correctos.
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
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido o ha caducado.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No existe una cuenta asociada a ese correo.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Se borra el token para que no se pueda reutilizar.
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', '¡Tu contraseña ha sido cambiada con éxito! Ya puedes entrar.');
    }

    /**
     * Cambio de contrasena desde el perfil.
     */
    public function cambiarPasswordPerfil(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', '¡Contraseña actualizada correctamente!');
    }
}
