<?php

/**
 * Controlador de seguridad de contraseñas.
 * Gestiona recuperación por email y cambios desde perfil.
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
     * Muestra el formulario para solicitar recuperación por email.
     */
    public function showRequestForm()
    {
        return view('user.forgot-password');
    }

    /**
     * Genera token y envía enlace de recuperación.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = strtolower(trim((string) $request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Respuesta neutra para no filtrar si el email existe.
            return back()->with('status', 'Si el correo existe, te enviaremos un enlace de recuperación.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                // Se guarda hash del token, nunca token en claro.
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        try {
            Mail::send('emails.password-reset', ['token' => $token], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Recuperar contraseña - SeaFit');
            });
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de recuperación.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'No se pudo enviar el correo ahora mismo. Inténtalo de nuevo en unos minutos.',
            ]);
        }

        return back()->with('status', 'Si el correo existe, te enviaremos un enlace de recuperación.');
    }

    /**
     * Muestra el formulario para establecer nueva contraseña.
     */
    public function showResetForm($token)
    {
        return view('user.reset-password', ['token' => $token]);
    }

    /**
     * Guarda la nueva contraseña si email y token son válidos.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            'token' => 'required',
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $email = strtolower(trim((string) $request->email));

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido o ha caducado.']);
        }

        $expiresAt = Carbon::parse($resetRecord->created_at)
            ->addMinutes((int) config('auth.passwords.users.expire', 60));

        if (now()->greaterThan($expiresAt)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return back()->withErrors(['email' => 'El enlace de recuperación ha caducado. Solicita uno nuevo.']);
        }

        // Compatibilidad: tokens antiguos en claro + nuevos hasheados.
        $storedToken = (string) $resetRecord->token;
        $isHashedToken = Str::startsWith($storedToken, ['$2y$', '$2a$', '$2b$', '$argon2i$', '$argon2id$']);
        $tokenValido = $isHashedToken
            ? Hash::check((string) $request->token, $storedToken)
            : hash_equals($storedToken, (string) $request->token);

        if (!$tokenValido) {
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido o ha caducado.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No existe una cuenta asociada a ese correo.']);
        }

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')->with('success', 'Tu contraseña ha sido cambiada con éxito. Ya puedes entrar.');
    }

    /**
     * Cambia contraseña desde el perfil del usuario logueado.
     */
    public function changeProfilePassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
        ], [
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La nueva contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Contraseña actualizada correctamente.');
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
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
        ], [
            'password.confirmed' => 'La confirmación no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('perfil')->with('success', 'Contraseña actualizada.');
    }
}
