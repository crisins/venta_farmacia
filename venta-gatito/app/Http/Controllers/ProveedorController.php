<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    public function index()
    {
        // Devuelve todos los proveedores
        return response()->json(Proveedor::all());
    }

    public function show($id)
    {
        // Buscar el proveedor por su ID
        $proveedor = Proveedor::find($id);

        if ($proveedor) {
            return response()->json($proveedor);
        } else {
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        // Validar los datos entrantes
        $request->validate([
            'nombre' => 'required|string|max:100',
            'contacto' => 'required|string|max:100',
            'telefono' => 'required|string|max:20',
            'email' => 'required|string|email|max:100',
            'direccion' => 'required|string|max:255',
        ]);

        // Crear el proveedor
        $proveedor = Proveedor::create([
            'nombre' => $request->nombre,
            'contacto' => $request->contacto,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'direccion' => $request->direccion,
        ]);

        // Responder con el proveedor creado
        return response()->json($proveedor, 201);
    }

    public function update(Request $request, $id)
    {
        // Buscar el proveedor por su ID
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }

        // Validar los datos entrantes
        $request->validate([
            'nombre' => 'string|max:100',
            'contacto' => 'string|max:100',
            'telefono' => 'string|max:20',
            'email' => 'string|email|max:100',
            'direccion' => 'string|max:255',
        ]);

        // Actualizar los campos del proveedor
        $proveedor->update($request->only(['nombre', 'contacto', 'telefono', 'email', 'direccion']));

        // Responder con el proveedor actualizado
        return response()->json($proveedor);
    }

    public function destroy($id)
    {
        // Buscar el proveedor por su ID
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }

        // Eliminar el proveedor
        $proveedor->delete();

        // Responder con mensaje de éxito
        return response()->json(['message' => 'Proveedor eliminado con éxito']);
    }
}
