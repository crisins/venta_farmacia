<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'tipo' => 'required|in:administrador,usuario',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:255'
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tipo' => $validated['tipo'],
            'telefono' => $validated['telefono'],
            'direccion' => $validated['direccion']
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    }

    public function show($id)
    {
        $usuario = Usuario::find($id);
        
        if ($usuario) {
            return response()->json([
                'success' => true,
                'data' => $usuario
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuarios,email,'.$usuario->id,
            'password' => 'sometimes|string|min:6',
            'tipo' => 'sometimes|in:administrador,usuario',
            'telefono' => 'sometimes|string|max:20',
            'direccion' => 'sometimes|string|max:255'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario
        ]);
    }

    public function index()
    {
        try {
            $usuarios = Usuario::select([
                'id',
                'nombre', 
                'email',
                'tipo',
                'telefono',
                'direccion',
                'created_at'
            ])->get();

            return response()->json([
                'success' => true,
                'count' => $usuarios->count(),
                'data' => $usuarios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}