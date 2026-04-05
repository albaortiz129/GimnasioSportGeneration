<?php

/**
 * Controlador del panel de administracion: lista, edita y elimina usuarios.
 */
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Lista completa de usuarios para gestion administrativa.
     */
    public function index()
    {
        $usuarios = User::orderBy('id')->get();

        return view('admin.dashboard', compact('usuarios'));
    }

    /**
     * Formulario de edicion de un usuario.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.edit_user', compact('user'));
    }

    /**
     * Actualiza campos basicos del usuario desde admin.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Usuario actualizado.');
    }

    /**
     * Elimina un usuario (excepto el administrador autenticado).
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Seguridad: evitar que el admin se borre a si mismo.
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Usuario eliminado correctamente.');
    }
}

