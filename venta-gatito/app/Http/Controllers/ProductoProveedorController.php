<?php

namespace App\Http\Controllers;

use App\Models\ProductoProveedor;
use Illuminate\Http\Request;

class ProductoProveedorController extends Controller
{
    public function index()
    {
        $data = ProductoProveedor::with(['producto', 'proveedor'])->get();
        return response()->json($data);
    }

    public function show($id)
    {
        $data = ProductoProveedor::with(['producto', 'proveedor'])->find($id);
        if ($data) {
            return response()->json($data);
        } else {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $data = ProductoProveedor::create($request->all());
        return response()->json($data, 201);
    }

    public function update(Request $request, $id)
    {
        $registro = ProductoProveedor::find($id);
        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        $registro->update($request->all());
        return response()->json($registro);
    }

    public function destroy($id)
    {
        $registro = ProductoProveedor::find($id);
        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        $registro->delete();
        return response()->json(['mensaje' => 'Registro eliminado']);
    }
}
