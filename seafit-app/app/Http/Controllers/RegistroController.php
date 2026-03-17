<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    public function registrar(Request $request)
    {
        try {
            // 1. Validar los datos recibidos
            $validator = Validator::make($request->all(), [
                'nombre'           => 'required|string|max:255',
                'apellidos'        => 'required|string|max:255',
                'dni'              => 'required|string|unique:users,dni',
                'fecha_nacimiento' => 'required|date',
                'telefono'         => 'required|string|max:20',
                'email'            => 'required|email|unique:users,email',
                'password'         => 'required|string|min:6',
                'domicilio'        => 'required|string|max:255',
                'tarifa'           => 'required|string',
                'metodo_pago'      => 'required|string',
                'cupon'            => 'nullable|string', // AÑADIDO: Validamos que el cupón sea opcional
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Errores de validación',
                    'detalles' => $validator->errors()
                ], 422);
            }

            // --- LÓGICA DE DESCUENTOS (AÑADIDO) ---
            $tarifaSeleccionada = $request->tarifa; // Ej: 'mensual', 'anual'
            $cuponIntroducido = $request->cupon;
            $mensajeDescuento = "";

            // Definimos nuestros cupones "mágicos"
            $cuponesDisponibles = [
                'SEAFIT20' => 0.20,   // 20% de descuento
                'BIENVENIDA' => 5.00  // 5€ de descuento fijo
            ];

            if ($cuponIntroducido && isset($cuponesDisponibles[$cuponIntroducido])) {
                $mensajeDescuento = "¡Cupón aplicado con éxito!";
                // Aquí podrías calcular el precio final si tuvieras una tabla de precios
            }
            // ---------------------------------------

            // 2. Crear el usuario
            $user = new User();
            $user->nombre = $request->nombre;
            $user->apellidos = $request->apellidos;
            $user->dni = $request->dni;
            $user->fecha_nacimiento = $request->fecha_nacimiento;
            $user->telefono = $request->telefono;
            $user->email = $request->email;
            $user->domicilio = $request->domicilio;
            $user->tarifa = $request->tarifa;
            $user->metodo_pago = $request->metodo_pago;
            
            // Ciframos la contraseña
            $user->password = Hash::make($request->password);
            
            $user->save();

            return response()->json([
                'mensaje' => '¡Socio registrado con éxito en SeaFit!',
                'descuento' => $mensajeDescuento, // Informamos a React si hubo descuento
                'usuario_id' => $user->id,
                'email' => $user->email
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en Registro SeaFit: ' . $e->getMessage());
            return response()->json([
                'error' => 'No se pudo completar el registro',
                'debug' => $e->getMessage()
            ], 500);
        }
    }
}