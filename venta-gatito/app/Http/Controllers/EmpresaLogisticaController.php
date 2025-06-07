<?php
namespace App\Http\Controllers;

use App\Models\EmpresaLogistica;
use Illuminate\Http\Request;

class EmpresaLogisticaController extends Controller
{
    public function index()
    {
        return response()->json(EmpresaLogistica::all());
    }

    public function show($id)
    {
        $empresa = EmpresaLogistica::find($id);
        if ($empresa) {
            return response()->json($empresa);
        } else {
            return response()->json(['error' => 'Empresa Logística no encontrada'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'contacto' => 'required|string|max:100',
            'telefono' => 'required|string|max:20',
            'email' => 'required|string|email|max:100',
        ]);

        $empresa = EmpresaLogistica::create([
            'nombre' => $request->nombre,
            'contacto' => $request->contacto,
            'telefono' => $request->telefono,
            'email' => $request->email,
        ]);

        return response()->json($empresa, 201);
    }

    public function update(Request $request, $id)
    {
        $empresa = EmpresaLogistica::find($id);

        if ($empresa) {
            $empresa->update([
                'nombre' => $request->nombre,
                'contacto' => $request->contacto,
                'telefono' => $request->telefono,
                'email' => $request->email,
            ]);

            return response()->json($empresa);
        } else {
            return response()->json(['error' => 'Empresa Logística no encontrada'], 404);
        }
    }

    public function destroy($id)
    {
        $empresa = EmpresaLogistica::find($id);

        if ($empresa) {
            $empresa->delete();
            return response()->json(['message' => 'Empresa Logística eliminada']);
        } else {
            return response()->json(['error' => 'Empresa Logística no encontrada'], 404);
        }
    }
}
