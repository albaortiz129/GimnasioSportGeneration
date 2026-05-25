<?php

/**
 * Controlador de seguridad de contraseñas.
 * Gestiona la recuperación de contraseña por email y cambios de contraseña desde perfil.
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
     * Muestra el formulario para solicitar la recuperación de la contraseña por email.
     */
    public function showRequestForm()
    {
        return view('user.forgot-password');
    }

    /**
     * Genera el token y envía el enlace de recuperación.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = strtolower(trim((string) $request->email)); // Obtiene el correo del usuario.
        $user = User::where('email', $email)->first(); // Busca el usuario por correo.

        if (!$user) { // Si el usuario no existe.
            return back()->with('status', 'Si el correo existe, te enviaremos un enlace de recuperación.');
        }

        $token = Str::random(64); // Genera un token de 64 caracteres.
        // Guarda el token en la tabla password_reset_tokens para que solo pueda restablecer contraseña una vez.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email], // Actualiza o inserta el token.
            [
                'token' => Hash::make($token), // Guarda hash del token.
                'created_at' => Carbon::now(), // Guarda la fecha actual.
            ]
        );

        try {
            Mail::send('emails.password-reset', ['token' => $token], function ($message) use ($email) {
                $message->to($email); // Envía el correo al usuario.
                $message->subject('Recuperar contraseña de Sport Generation'); // Asunto del correo.
            });
        } catch (\Throwable $e) { // Captura si hay error.
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
     * Guarda la nueva contraseña si el email y el token son válidos.
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

        $email = strtolower(trim((string) $request->email)); // Obtiene el correo del usuario.

        $resetRecord = DB::table('password_reset_tokens') // Verifica si el usuario tiene un proceso de cambio de contraseña pendiente.
            ->where('email', $email)
            ->first();

        if (!$resetRecord) { // Si el usuario no tiene un proceso de cambio de contraseña pendiente devuelve un error
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido o ha caducado.']);
        }

        $expiresAt = Carbon::parse($resetRecord->created_at) // Calcula la fecha de expiración.
            ->addMinutes((int) config('auth.passwords.users.expire', 60));

        if (now()->greaterThan($expiresAt)) { // Si la fecha actual es mayor a la fecha de expiración elimina el registro de restablecimiento
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return back()->withErrors(['email' => 'El enlace de recuperación ha caducado. Solicita uno nuevo.']);
        }

        // En el formato actual, el token siempre se guarda con hash.
        $storedToken = (string) $resetRecord->token;
        $tokenValido = Hash::check((string) $request->token, $storedToken);

        if (!$tokenValido) { // Si el token no es válido, devuelve un error
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido o ha caducado.']);
        }

        $user = User::where('email', $email)->first(); // Busca el usuario por correo.

        if (!$user) { // Si el usuario no existe,
            return back()->withErrors(['email' => 'No existe una cuenta asociada a ese correo.']); // Devuelve un error.
        }

        $user->password = Hash::make($request->password); // Cambia la contraseña.
        $user->must_change_password = false; // Establece que el usuario no debe cambiar la contraseña.
        $user->save(); // Guarda el usuario.

        DB::table('password_reset_tokens')->where('email', $email)->delete(); // Elimina el registro de restablecimiento.

        return redirect()->route('login')->with('success', 'Tu contraseña ha sido cambiada con éxito.'); // Redirige al login con un mensaje de éxito.
    }

    /**
     * Cambia contraseña desde el perfil del usuario.
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
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La nueva contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $user = Auth::user(); // Obtiene el usuario logueado.

        if (!Hash::check($request->password_actual, $user->password)) { // Verifica si la contraseña actual es correcta.
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']); // Devuelve un error.
        }

        $user->password = Hash::make($request->password); // Cambia la contraseña.
        $user->save(); // Guarda el usuario.

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }

    /**
     * Formulario obligatorio en el primer inicio de sesión para cambiar contraseña temporal.
     */
    public function showInitialChangeForm()
    {
        return view('user.force-change-password');
    }

    /**
     * Guarda la nueva contraseña en el primer inicio de sesión.
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
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $user = Auth::user(); // Obtiene el usuario logueado.

        $user->password = Hash::make($request->password); // Cambia la contraseña.
        $user->must_change_password = false;
        $user->save(); // Guarda el usuario.

        return redirect()->route('perfil')->with('success', 'Contraseña actualizada.');
    }
}
