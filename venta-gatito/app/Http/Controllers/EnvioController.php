<?php
namespace App\Http\Controllers;

use App\Models\Envio;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    public function index()
    {
        return response()->json(Envio::with(['pedido', 'empresaLogistica'])->get());
    }

    public function show($id)
    {
        $envio = Envio::with(['pedido', 'empresaLogistica'])->find($id);
        if ($envio) {
            return response()->json($envio);
        } else {
            return response()->json(['error' => 'Envío no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'empresa_log_id' => 'required|exists:empresas_logisticas,id',
            'estado_envio' => 'required|in:pendiente,en camino,entregado',
            'fecha_envio' => 'required|date',
            'fecha_entrega' => 'nullable|date',
        ]);

        $envio = Envio::create([
            'pedido_id' => $request->pedido_id,
            'empresa_log_id' => $request->empresa_log_id,
            'estado_envio' => $request->estado_envio,
            'fecha_envio' => $request->fecha_envio,
            'fecha_entrega' => $request->fecha_entrega,
        ]);

        return response()->json($envio, 201);
    }

    public function update(Request $request, $id)
    {
        $envio = Envio::find($id);

        if ($envio) {
            $envio->update([
                'pedido_id' => $request->pedido_id,
                'empresa_log_id' => $request->empresa_log_id,
                'estado_envio' => $request->estado_envio,
                'fecha_envio' => $request->fecha_envio,
                'fecha_entrega' => $request->fecha_entrega,
            ]);

            return response()->json($envio);
        } else {
            return response()->json(['error' => 'Envío no encontrado'], 404);
        }
    }

    public function destroy($id)
    {
        $envio = Envio::find($id);

        if ($envio) {
            $envio->delete();
            return response()->json(['message' => 'Envío eliminado']);
        } else {
            return response()->json(['error' => 'Envío no encontrado'], 404);
        }
    }
}
