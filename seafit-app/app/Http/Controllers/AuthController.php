<?php

/**
 * Controlador de autenticacion: gestiona inicio y cierre de sesion.
 */
namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Valida credenciales e inicia sesion.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        try {
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
        } catch (QueryException $exception) {
            // Si no hay conexion con MySQL, evita error 500 en pantalla.
            report($exception);

            return back()->withErrors([
                'email' => 'No hay conexion con la base de datos local. Inicia MySQL y vuelve a intentarlo.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'El correo electronico o la contraseña no coinciden.',
        ])->onlyInput('email');
    }

    /**
     * Cierra sesion y limpia estado de seguridad.
     */
    public function logout(Request $request)
    {
        // Cerrar sesion del usuario autenticado.
        Auth::logout();

        // Borra la sesion anterior y crea un token nuevo de seguridad.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redireccion al login.
        return redirect('/login');
    }
}
