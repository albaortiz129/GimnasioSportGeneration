<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class PasswordController extends Controller
{
    public function mostrarFormularioEmail()
    {
        return view('usuario.recuperar-password'); 
    }

    public function enviarEnlace(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No encontramos ningún socio con este correo electrónico.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token, 
                'created_at' => Carbon::now()
            ]
        );

        Mail::send('emails.recuperar-password', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Recuperar contraseña - SeaFit');
        });

        // Cambiado a 'status' para que coincida con @if (session('status')) en tu Blade
        return back()->with('status', '¡Listo! Revisa tu bandeja de entrada, te hemos enviado el enlace de recuperación.');
    }

    public function mostrarFormularioReset($token)
    {
        return view('usuario.reset-password', ['token' => $token]);
    }

    public function actualizarPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required'
        ]);

        $resetRecord = DB::table('password_reset_tokens')
                            ->where('email', $request->email)
                            ->where('token', $request->token)
                            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido o ha caducado.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Redirigimos al login con mensaje de éxito
        return redirect()->route('login')->with('success', '¡Tu contraseña ha sido cambiada con éxito! Ya puedes entrar.');
    }

    public function cambiarPasswordPerfil(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.'
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