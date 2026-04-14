<?php

/**
 * Controlador de autenticación: gestiona inicio y cierre de sesión de usuarios.
 */
namespace App\Http\Controllers;

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

        if (Auth::attempt($credentials)) {
            // Seguridad de sesion tras login correcto.
            $request->session()->regenerate();

            $user = Auth::user();

            // Admin entra al panel de gestion.
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

        return back()->withErrors([
            'email' => 'El correo electronico o la contraseña no coinciden.',
        ])->onlyInput('email');
    }


    /**
     * Cierra sesión y limpia estado de seguridad.
     */
    public function logout(Request $request)
    {
        // Cerrar sesión del usuario autenticado.
        Auth::logout();

        // Borra la sesión anterior y crea un código nuevo de seguridad.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirección al login.
        return redirect('/login');
    }
}

