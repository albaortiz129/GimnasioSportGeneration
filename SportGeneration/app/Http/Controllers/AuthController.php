<?php

/**
 * Controlador de autenticación: gestiona inicio y cierre de sesión.
 */
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Valida credenciales e inicia sesión.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'min:6'],
        ]);

        $login = strtolower(trim((string) $data['email']));
        $email = $login === 'admin'
            ? $this->resolveAdminEmail()
            : $login;

        if (!$email) {
            return back()->withErrors([
                'email' => 'No hay ningún administrador configurado.',
            ])->onlyInput('email');
        }

        $credentials = [
            'email' => $email,
            'password' => $data['password'],
        ];

        try {
            if (Auth::attempt($credentials)) {
                // Seguridad de sesión tras login correcto.
                $request->session()->regenerate();

                $user = Auth::user();

                // Admin entra al panel de gestión.
                if ($user->is_admin) {
                    return redirect()->route('admin.dashboard');
                }

                // Si tiene clave temporal, se fuerza cambio.
                if ($user->must_change_password) {
                    return redirect()->route('password.force.form')
                        ->with('warning', 'Debes cambiar tu contraseña.');
                }

                return redirect()->intended('/perfil');
            }
        } catch (QueryException $exception) {
            // Si no hay conexión con MySQL.
            report($exception);

            return back()->withErrors([
                'email' => 'No hay conexión con la base de datos, inténtalo más tarde.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'El correo electrónico o la contraseña no coinciden.',
        ])->onlyInput('email');
    }

    /**
     * Permite entrar como admin escribiendo "admin" en el campo de usuario.
     */
    private function resolveAdminEmail(): ?string
    {
        $configuredEmail = strtolower(trim((string) config('services.sport_generation.admin_email', '')));

        if ($configuredEmail !== '') {
            return $configuredEmail;
        }

        return User::where('is_admin', true)->orderBy('id')->value('email');
    }

    /**
     * Cierra sesión y limpia estado de seguridad.
     */
    public function logout(Request $request)
    {
        // Cerrar sesión del usuario autenticado.
        Auth::logout();

        // Borra la sesión anterior y crea un token nuevo de seguridad.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirección al login.
        return redirect('/login');
    }
}
