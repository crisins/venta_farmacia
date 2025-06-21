<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Inventario;

class VentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
            'productos' => 'required|array|min:1', // **Validación clave: debe haber al menos un producto**
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $producto_id = $this->input("productos.{$index}.producto_id");

                    if ($producto_id) {
                        $inventario = Inventario::where('producto_id', $producto_id)->first();

                        if (!$inventario) {
                            $fail("No se encontró inventario para el producto ID $producto_id.");
                            return;
                        }

                        if ($inventario->stock_actual < $value) {
                            $fail("Stock insuficiente para el producto ID $producto_id. Stock actual: {$inventario->stock_actual}, solicitado: {$value}.");
                        }
                    }
                }
            ],
        ];
    }

    /**
     * Get the custom validation messages for attributes.
     */
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'usuario_id.required' => 'El usuario es obligatorio.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
            'fecha.required' => 'La fecha de la venta es obligatoria.',
            'fecha.date' => 'La fecha de la venta debe ser una fecha válida.',
            'productos.required' => 'La venta debe contener productos.',
            'productos.array' => 'Los productos deben ser enviados como un arreglo.',
            'productos.min' => 'La venta debe contener al menos un producto.',
            'productos.*.producto_id.required' => 'El ID del producto es obligatorio para cada item.',
            'productos.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria para cada producto.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad de cada producto debe ser al menos 1.',
        ];
    }
}