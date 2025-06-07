<?php

namespace App\Http\Controllers;

use App\Models\AtencionCliente;
use Illuminate\Http\Request;

class AtencionClienteController extends Controller
{
    public function index()
    {
        return response()->json(AtencionCliente::with('usuario')->get());
    }

    public function show($id)
    {
        $atencion = AtencionCliente::with('usuario')->find($id);
        if ($atencion) {
            return response()->json($atencion);
        } else {
            return response()->json(['error' => 'Atención al cliente no encontrada'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'tipo' => 'required|in:reclamo,consulta',
            'detalle' => 'required|string',
            'estado' => 'required|in:abierto,resuelto,cerrado',
            'fecha' => 'required|date',
        ]);

        $atencion = AtencionCliente::create([
            'usuario_id' => $request->usuario_id,
            'tipo' => $request->tipo,
            'detalle' => $request->detalle,
            'estado' => $request->estado,
            'fecha' => $request->fecha,
        ]);

        return response()->json($atencion, 201);
    }

    public function update(Request $request, $id)
    {
        $atencion = AtencionCliente::find($id);

        if ($atencion) {
            $atencion->update([
                'usuario_id' => $request->usuario_id,
                'tipo' => $request->tipo,
                'detalle' => $request->detalle,
                'estado' => $request->estado,
                'fecha' => $request->fecha,
            ]);

            return response()->json($atencion);
        } else {
            return response()->json(['error' => 'Atención al cliente no encontrada'], 404);
        }
    }

    public function destroy($id)
    {
        $atencion = AtencionCliente::find($id);

        if ($atencion) {
            $atencion->delete();
            return response()->json(['message' => 'Atención al cliente eliminada']);
        } else {
            return response()->json(['error' => 'Atención al cliente no encontrada'], 404);
        }
    }
}
