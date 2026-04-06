<?php

/**
 * Controlador de autenticacion: gestiona inicio y cierre de sesion de usuarios.
 */
namespace App\Http\Controllers;

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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->must_change_password) {
                return redirect()->route('password.force.form')
                    ->with('warning', 'Debes cambiar tu contrasena temporal.');
            }

            return redirect()->intended('/perfil');
        }

        return back()->withErrors([
            'email' => 'El correo electronico o la contrasena no coinciden.',
        ])->onlyInput('email');
    }


    /**
     * Cierra sesion y limpia estado de seguridad.
     */
    public function logout(Request $request)
    {
        // Cerrar sesion del usuario autenticado.
        Auth::logout();

        // Borra la sesion anterior y crea un codigo nuevo de seguridad.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redireccion al login.
        return redirect('/login');
    }
}

