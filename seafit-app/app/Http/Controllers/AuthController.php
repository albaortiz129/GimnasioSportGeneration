<?php

/**
 * Controlador de autenticación: gestiona inicio y cierre de sesión.
 */
namespace App\Http\Controllers;

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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        // Normaliza el email para evitar fallos por mayúsculas/minúsculas.
        $credentials['email'] = strtolower(trim((string) $credentials['email']));

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
                        ->with('warning', 'Debes cambiar tu contraseña temporal.');
                }

                return redirect()->intended('/perfil');
            }
        } catch (QueryException $exception) {
            // Si no hay conexión con MySQL, evita error 500 en pantalla.
            report($exception);

            return back()->withErrors([
                'email' => 'No hay conexión con la base de datos local. Inicia MySQL y vuelve a intentarlo.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'El correo electrónico o la contraseña no coinciden.',
        ])->onlyInput('email');
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
