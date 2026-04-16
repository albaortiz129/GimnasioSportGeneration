{{-- Vista de administracion de clases: crear, editar y gestionar apuntados. --}}
@extends('layouts.app')

@section('titulo', 'Clases - Admin')

@section('contenido')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-black">Calendario de clases</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-[#1A3878] font-bold">Volver al panel</a>
        </div>

        {{-- Formulario para crear una nueva clase. --}}
        <form action="{{ route('admin.classes.store') }}" method="POST"
            class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-2xl border mb-8">
            @csrf
            <input name="nombre" placeholder="Nombre" class="border rounded p-2" required>
            <input name="instructor" placeholder="Instructor" class="border rounded p-2" required>
            <input name="sala" placeholder="Sala" class="border rounded p-2" required>
            <input type="time" name="hora_inicio" class="border rounded p-2" required>

            <select name="dia_semana" class="border rounded p-2" required>
                <option>Lunes</option>
                <option>Martes</option>
                <option>Miercoles</option>
                <option>Jueves</option>
                <option>Viernes</option>
                <option>Sabado</option>
                <option>Domingo</option>
            </select>

            <input type="number" name="capacidad_max" min="0" placeholder="Plazas disponibles" class="border rounded p-2"
                required>
            <input name="imagen" placeholder="Ruta imagen (opcional)" class="border rounded p-2">
            <button class="bg-[#0A1931] text-white rounded p-2 font-bold">Crear clase</button>

            <textarea name="descripcion" placeholder="Descripcion" class="border rounded p-2 md:col-span-4"></textarea>
        </form>

        {{-- Filtro rapido por dia de la semana. --}}
        <div class="flex gap-2 overflow-x-auto mb-6">
            <a href="{{ route('admin.classes.index') }}"
                class="px-4 py-2 rounded-full text-sm font-bold {{ empty($dia) ? 'bg-[#1A3878] text-white' : 'bg-gray-100 text-gray-600' }}">
                Todos
            </a>
            @foreach($diasSemana as $d)
                <a href="{{ route('admin.classes.index', ['dia' => $d]) }}"
                    class="px-4 py-2 rounded-full text-sm font-bold {{ ($dia ?? null) === $d ? 'bg-[#1A3878] text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ $d }}
                </a>
            @endforeach
        </div>

        {{-- Listado de clases y gestion de inscritos. --}}
        <div class="space-y-6">
            @foreach($clases as $clase)
                @php
                    $usuariosDisponibles = $usuarios
                        ->reject(fn($u) => $clase->users->contains($u->id))
                        ->values();
                @endphp

                <div class="bg-white border rounded-2xl p-4">
                    {{-- Formulario de edicion rapida de la clase actual. --}}
                    <form action="{{ route('admin.classes.update', $clase) }}" method="POST"
                        class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-4">
                        @csrf
                        @method('PUT')
                        <input name="nombre" value="{{ $clase->nombre }}" class="border rounded p-2" required>
                        <input name="instructor" value="{{ $clase->instructor }}" class="border rounded p-2" required>
                        <input name="sala" value="{{ $clase->sala }}" class="border rounded p-2" required>
                        <input type="time" name="hora_inicio" value="{{ substr($clase->hora_inicio, 0, 5) }}"
                            class="border rounded p-2" required>

                        {{-- Se normaliza visualmente el dia para soportar datos antiguos. --}}
                        <select name="dia_semana" class="border rounded p-2" required>
                            @php $diaClase = $clase->dia_semana; @endphp
                            @foreach(['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'] as $diaItem)
                                <option value="{{ $diaItem }}" @selected($diaClase === $diaItem)>{{ $diaItem }}</option>
                            @endforeach
                        </select>

                        <input type="number" name="capacidad_max" min="0" value="{{ $clase->capacidad_max }}"
                            class="border rounded p-2" required>
                        <input name="imagen" value="{{ $clase->imagen }}" class="border rounded p-2">
                        <button class="bg-blue-600 text-white rounded p-2 font-bold">Guardar clase</button>

                        <textarea name="descripcion"
                            class="border rounded p-2 md:col-span-4">{{ $clase->descripcion }}</textarea>
                    </form>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            {{-- Lista de personas ya inscritas en esta clase. --}}
                            <h3 class="font-bold mb-2">Apuntados ({{ $clase->users->count() }})</h3>
                            @forelse($clase->users as $u)
                                <div class="flex items-center justify-between text-sm border rounded p-2 mb-2">
                                    <span>{{ $u->nombre }} {{ $u->apellidos }}</span>
                                    <form action="{{ route('admin.classes.usuarios.destroy', [$clase, $u]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 font-bold">Quitar</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Sin inscritos.</p>
                            @endforelse
                        </div>

                        <div>
                            {{-- Alta manual de alumno en la clase con buscador dentro del propio bloque. --}}
                            <h3 class="font-bold mb-2">Anadir usuario</h3>
                            <form action="{{ route('admin.classes.usuarios.store', $clase) }}" method="POST" class="space-y-2">
                                @csrf

                                <input
                                    type="text"
                                    class="class-user-filter border rounded p-2 w-full"
                                    data-target="user-select-{{ $clase->id }}"
                                    placeholder="Buscar alumno para esta clase...">

                                <div class="flex gap-2">
                                    <select
                                        id="user-select-{{ $clase->id }}"
                                        name="user_id"
                                        class="border rounded p-2 w-full"
                                        @disabled($usuariosDisponibles->isEmpty())
                                        required>
                                        @if($usuariosDisponibles->isEmpty())
                                            <option value="">No hay alumnos disponibles</option>
                                        @else
                                            @foreach($usuariosDisponibles as $u)
                                                <option
                                                    value="{{ $u->id }}"
                                                    data-search="{{ strtolower($u->nombre . ' ' . $u->apellidos . ' ' . $u->dni) }}">
                                                    {{ $u->nombre }} {{ $u->apellidos }} ({{ $u->dni }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>

                                    <button class="bg-[#0A1931] text-white px-4 rounded" @disabled($usuariosDisponibles->isEmpty())>
                                        Anadir
                                    </button>
                                </div>
                            </form>

                            {{-- Borrado completo de la clase. --}}
                            <form action="{{ route('admin.classes.destroy', $clase) }}" method="POST" class="mt-4"
                                onsubmit="return confirm('Eliminar clase?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-700 font-bold">Eliminar clase</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Cada filtro solo afecta al desplegable de su propia clase.
            document.querySelectorAll('.class-user-filter').forEach(function (input) {
                const selectId = input.getAttribute('data-target');
                const select = document.getElementById(selectId);
                if (!select) return;

                const opcionesOriginales = Array.from(select.options)
                    .filter(function (option) {
                        return option.value !== '';
                    })
                    .map(function (option) {
                        return {
                            value: option.value,
                            text: option.text,
                            search: (option.dataset.search || option.text || '').toLowerCase(),
                        };
                    });

                if (opcionesOriginales.length === 0) {
                    input.disabled = true;
                    return;
                }

                const renderOpciones = function () {
                    const texto = (input.value || '').trim().toLowerCase();
                    const filtradas = opcionesOriginales.filter(function (opcion) {
                        return texto === '' || opcion.search.includes(texto);
                    });

                    select.innerHTML = '';

                    if (filtradas.length === 0) {
                        const vacia = document.createElement('option');
                        vacia.value = '';
                        vacia.textContent = 'Sin resultados';
                        vacia.disabled = true;
                        vacia.selected = true;
                        select.appendChild(vacia);
                        return;
                    }

                    filtradas.forEach(function (opcion) {
                        const item = document.createElement('option');
                        item.value = opcion.value;
                        item.textContent = opcion.text;
                        item.dataset.search = opcion.search;
                        select.appendChild(item);
                    });
                };

                input.addEventListener('input', renderOpciones);
            });
        });
    </script>
@endsection
