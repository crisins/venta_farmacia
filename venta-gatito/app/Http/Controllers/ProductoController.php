<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    // Obtener todos los productos
    public function index()
    {
        $productos = Producto::all()->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => $producto->precio,
                'stock' => $producto->stock,
                'requiere_receta' => $producto->requiere_receta,
                'estado' => $producto->estado,
                'fecha_alta' => $producto->fecha_alta,
            ];
        });
        return response()->json($productos);
    }

    // Obtener un producto por ID
    public function show($id)
    {
        $producto = Producto::find($id);

        if ($producto) {
            $data = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => $producto->precio,
                'stock' => $producto->stock,
                'requiere_receta' => $producto->requiere_receta,
                'estado' => $producto->estado,
                'fecha_alta' => $producto->fecha_alta,
            ];
            return response()->json($data);
        } else {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
    }

    // Crear un nuevo producto
    public function store(Request $request)
    {
        $producto = Producto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'stock' => $request->stock,
            'requiere_receta' => $request->requiere_receta,
            'estado' => $request->estado,
            'fecha_alta' => $request->fecha_alta,
        ]);

        return response()->json($producto, 201);
    }

    // Actualizar un producto
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);

        if ($producto) {
            $producto->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'stock' => $request->stock,
                'requiere_receta' => $request->requiere_receta,
                'estado' => $request->estado,
                'fecha_alta' => $request->fecha_alta,
            ]);

            return response()->json($producto);
        } else {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
    }

    // Eliminar un producto
    public function destroy($id)
    {
        $producto = Producto::find($id);

        if ($producto) {
            $producto->delete();
            return response()->json(['message' => 'Producto eliminado con éxito']);
        } else {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
    }
}
