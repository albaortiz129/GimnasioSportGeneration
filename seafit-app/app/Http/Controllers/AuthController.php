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
        // Validacion de entrada.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        // Intenta iniciar sesion con email y contrasena.
        if (Auth::attempt($credentials)) {
            // regenerar sesion despues de autenticar.
            $request->session()->regenerate();
            return redirect()->intended('/perfil');
        }

        // Error si el correo no coincide.
        return back()->withErrors([
            'email' => 'El correo electrónico o la contrasena no coinciden.',
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

