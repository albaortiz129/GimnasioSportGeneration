<?php

/**
 * Controlador para administrar los descuentos.
 * Permite crear, editar y eliminar códigos de descuento.
 */

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDiscountController extends Controller
{
    /**
     * Muestra una lista de todos los códigos de descuento.
     * Se utiliza compact para convertir la variable en un array asociativo compatible con el motor de plantillas Blade.
     */
    public function index()
    {
        $codes = DiscountCode::orderByDesc('id')->paginate(20); // Ordena los códigos de descuento por ID de forma descendente y los muestra en grupos de 20.
        return view('admin.discounts.index', compact('codes'));
    }

    /**
     * Muestra el formulario para crear un nuevo código de descuento.
     */
    public function create()
    {
        return view('admin.discounts.create');
    }

    /**
     * Guarda el nuevo código de descuento.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request); // Valida los datos del formulario.

        DiscountCode::create($data + [
            'created_by' => auth()->id(), // Asigna el ID del usuario que creó el código.
        ]);

        return redirect()->route('admin.discounts.index')->with('success', 'Código creado.');
    }

    /**
     * Muestra el formulario para editar un código de descuento existente.
     * 
     */
    public function edit(DiscountCode $discountCode)
    {
        return view('admin.discounts.edit', compact('discountCode'));
    }

    /**
     * Actualiza un código de descuento.
     */
    public function update(Request $request, DiscountCode $discountCode)
    {
        $data = $this->validateData($request, $discountCode->id);
        $discountCode->update($data);

        return redirect()->route('admin.discounts.index')->with('success', 'Código actualizado.');
    }

    /**
     * Elimina un código sin usos.
     */
    public function destroy(DiscountCode $discountCode)
    {
        if ($discountCode->redemptions()->exists()) { // Comprueba si el código de descuento ya tiene usos.
            return back()->with('error', 'No se puede borrar un código ya usado. Desactívalo.');
        }

        $discountCode->delete();

        return back()->with('success', 'Código eliminado.');
    }

    /**
     * Reglas de validación para crear y editar.
     */
    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9_-]{4,30}$/',
                Rule::unique('discount_codes', 'code')->ignore($ignoreId), // Verifica que el código sea único
            ],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'max_uses' => 'nullable|integer|min:1',
            'one_use_per_user' => 'nullable|boolean',
            'stripe_coupon_id' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['code'] = strtoupper(trim($data['code'])); // Convierte el código a mayúsculas y elimina los espacios en blanco.
        $data['is_active'] = $request->boolean('is_active'); // Convierte el estado a booleano.
        $data['one_use_per_user'] = $request->boolean('one_use_per_user'); // Convierte el número máximo de usos por usuario a booleano.

        // Porcentaje máximo permitido en descuentos de tipo porcentaje.
        if ($data['type'] === 'percent' && (float) $data['value'] > 100) { // Si el tipo es porcentaje, el valor máximo es 100.
            abort(422, 'El valor máximo es 100 al ser un número de tipo porcentaje.');
        }

        return $data; // Devuelve los datos validados.
    }
}
