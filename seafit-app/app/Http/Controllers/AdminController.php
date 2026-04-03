<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function index() {
        $usuarios = User::all();
        return view('admin.dashboard', compact('usuarios'));
    }

    // Mostrar formulario de edición (opcional, si quieres una página aparte)
    public function edit($id) {
        $user = User::findOrFail($id);
        return view('admin.edit_user', compact('user'));
    }

    // Actualizar datos
    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->update($request->all());
        return redirect()->route('admin.dashboard')->with('success', 'Usuario actualizado');
    }

    // Eliminar usuario
    public function destroy($id) {
        $user = User::findOrFail($id);
        
        // Seguridad: No dejar que el admin se borre a sí mismo
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Usuario eliminado correctamente.');
    }
}
