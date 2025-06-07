<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Mostrar todos los pagos con los detalles del pedido.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtenemos todos los pagos con la relación del pedido
        $pagos = Pago::with('pedido')->get();
        return response()->json($pagos);
    }

    /**
     * Mostrar un pago por ID con los detalles del pedido.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Obtenemos el pago con la relación del pedido
        $pago = Pago::with('pedido')->find($id);
        
        if ($pago) {
            return response()->json($pago);
        } else {
            return response()->json(['error' => 'Pago no encontrado'], 404);
        }
    }

    /**
     * Almacenar un nuevo pago.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validación de los datos recibidos
        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'metodo_pago' => 'required|in:WebPay,PayPal',
            'estado' => 'required|in:pendiente,completado,fallido',
            'fecha_pago' => 'required|date',
        ]);
        
        // Crear el pago con los datos validados
        $pago = Pago::create($validated);

        return response()->json($pago, 201);
    }

    /**
     * Actualizar un pago existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validación de los datos recibidos
        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'metodo_pago' => 'required|in:WebPay,PayPal',
            'estado' => 'required|in:pendiente,completado,fallido',
            'fecha_pago' => 'required|date',
        ]);
        
        // Buscar el pago por ID
        $pago = Pago::find($id);

        if ($pago) {
            // Actualizamos los datos del pago
            $pago->update($validated);
            return response()->json($pago);
        } else {
            return response()->json(['error' => 'Pago no encontrado'], 404);
        }
    }

}

    

    
