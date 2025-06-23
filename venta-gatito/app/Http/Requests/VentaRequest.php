<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Producto;

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
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
            'productos' => 'required|array|min:1', // **Validación clave: debe haber al menos un producto**
            'productos.*.producto_id' => [
                'required',
                'exists:productos,id',
                // Validación para productos que requieren receta
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1]; // Obtiene el índice del producto en el array
                    $producto = Producto::find($value); // Busca el producto por su ID

                    // Si el producto existe y requiere receta
                    if ($producto && $producto->requiere_receta) {
                        // Verifica si el campo 'con_receta' está presente y es true para este producto
                        $conReceta = $this->input("productos.{$index}.con_receta");

                        // Si el producto requiere receta y 'con_receta' no es true
                        if (!$conReceta) {
                            $fail("El producto '{$producto->nombre}' (ID: {$producto->id}) requiere receta médica y debe incluir '\"con_receta\": true' en su detalle.");
                        }
                    }
                },
            ],
            'productos.*.cantidad' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $producto_id = $this->input("productos.{$index}.producto_id");

                    if ($producto_id) {
                        $producto = Producto::find($producto_id);

                        if (!$producto) {
                            $fail("No se encontró producto con ID $producto_id.");
                            return;
                        }

                        if ($producto->stock < $value) {
                            $fail("Stock insuficiente para el producto ID $producto_id. Stock actual: {$producto->stock}, solicitado: {$value}.");
                        }
                    }
                }
            ],
            'productos.*.con_receta' => 'sometimes|boolean', // Nuevo campo opcional para indicar si se adjunta receta
        ];
    }

    /**
     * Get the custom validation messages for attributes.
     */
    public function messages(): array
    {
        return [
            'usuario_id.required' => 'El usuario comprador es obligatorio.',
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
            'productos.*.con_receta.boolean' => 'El campo "con_receta" debe ser verdadero o falso.',
        ];
    }
}