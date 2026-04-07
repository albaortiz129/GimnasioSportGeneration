@extends('moldes.inicio')

@section('titulo', 'Descuentos - Admin')

@section('contenido')
    <div class="max-w-6xl mx-auto px-4 py-8">
        @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div> @endif
        @if(session('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div> @endif

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black">Codigos de descuento</h1>
            <a href="{{ route('admin.discounts.create') }}" class="bg-[#0A1931] text-white px-4 py-2 rounded">Nuevo
                codigo</a>
        </div>

        <div class="bg-white border rounded-xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Codigo</th>
                        <th class="p-3 text-left">Tipo</th>
                        <th class="p-3 text-left">Valor</th>
                        <th class="p-3 text-left">Activo</th>
                        <th class="p-3 text-left">Usos</th>
                        <th class="p-3 text-left">Stripe Coupon</th>
                        <th class="p-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($codes as $c)
                        <tr class="border-t">
                            <td class="p-3 font-bold">{{ $c->code }}</td>
                            <td class="p-3">{{ $c->type === 'percent' ? 'Porcentaje' : 'Fijo' }}</td>
                            <td class="p-3">{{ $c->value }}</td>
                            <td class="p-3">{{ $c->is_active ? 'Si' : 'No' }}</td>
                            <td class="p-3">{{ $c->used_count }} / {{ $c->max_uses ?? '∞' }}</td>
                            <td class="p-3">{{ $c->stripe_coupon_id ?: '-' }}</td>
                            <td class="p-3 flex gap-2">
                                <a href="{{ route('admin.discounts.edit', $c) }}"
                                    class="bg-blue-600 text-white px-3 py-1 rounded">Editar</a>
                                <form method="POST" action="{{ route('admin.discounts.destroy', $c) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 text-white px-3 py-1 rounded">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-gray-500">No hay codigos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $codes->links() }}</div>
    </div>
@endsection