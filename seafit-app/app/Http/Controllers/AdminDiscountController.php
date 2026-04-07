<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDiscountController extends Controller
{
    public function index()
    {
        $codes = DiscountCode::orderByDesc('id')->paginate(20);
        return view('admin.descuentos.index', compact('codes'));
    }

    public function create()
    {
        return view('admin.descuentos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DiscountCode::create($data + [
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.discounts.index')->with('success', 'Codigo creado.');
    }

    public function edit(DiscountCode $discountCode)
    {
        return view('admin.descuentos.edit', compact('discountCode'));
    }

    public function update(Request $request, DiscountCode $discountCode)
    {
        $data = $this->validateData($request, $discountCode->id);
        $discountCode->update($data);

        return redirect()->route('admin.discounts.index')->with('success', 'Codigo actualizado.');
    }

    public function destroy(DiscountCode $discountCode)
    {
        if ($discountCode->redemptions()->exists()) {
            return back()->with('error', 'No se puede borrar un codigo ya usado. Desactivalo.');
        }

        $discountCode->delete();

        return back()->with('success', 'Codigo eliminado.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9_-]{4,30}$/',
                Rule::unique('discount_codes', 'code')->ignore($ignoreId),
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

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');
        $data['one_use_per_user'] = $request->boolean('one_use_per_user');

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            abort(422, 'Si el tipo es porcentaje, el valor maximo es 100.');
        }

        return $data;
    }
}
