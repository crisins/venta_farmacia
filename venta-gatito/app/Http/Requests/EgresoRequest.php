<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Producto;

class EgresoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $rules = [
            'proveedor_id' => 'required|exists:proveedores,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
            'tipo' => 'required|in:entrada,salida,ajuste', // Tipos de egreso permitidos
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => [
                'required',
                'exists:productos,id',
                function ($attribute, $value, $fail) {
                    // Validar si el producto existe y está activo si es necesario
                    $producto = Producto::find($value);
                    if (!$producto) {
                        $fail('El producto ID ' . $value . ' no existe.');
                    }
                    // Opcional: - Validar si el producto está activo
                    // if ($producto && $producto->estado === 'inactivo') {
                    //     $fail('El producto ' . $producto->nombre . ' está inactivo y no puede ser usado en un egreso.');
                    // }
                }
            ],
            'detalles.*.cantidad' => [
                'required',
                'integer',
                'min:1', // Cantidad debe ser al menos 1
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $producto_id = $this->input("detalles.{$index}.producto_id");
                    $tipoEgreso = $this->input('tipo');

                    if ($producto_id && $tipoEgreso === 'salida') {
                        $producto = Producto::find($producto_id);
                        if (!$producto || $producto->stock < $value) {
                            $fail('Stock insuficiente para egreso de tipo salida del producto ID ' . $producto_id . '. Stock disponible: ' . ($producto ? $producto->stock : 0));
                        }
                    }
                }
            ],
            'detalles.*.costo_unitario' => 'required|numeric|min:0',
        ];
        return $rules;
    }

    public function messages(): array
    {
        return [
            'detalles.required' => 'Debe haber al menos un detalle de egreso.',
            'detalles.min' => 'Debe haber al menos un detalle de egreso.',
            'detalles.*.producto_id.required' => 'El ID del producto es obligatorio en cada detalle.',
            'detalles.*.producto_id.exists' => 'El producto seleccionado en un detalle no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada detalle.',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'detalles.*.costo_unitario.required' => 'El costo unitario es obligatorio en cada detalle.',
            'detalles.*.costo_unitario.numeric' => 'El costo unitario debe ser un número.',
            'detalles.*.costo_unitario.min' => 'El costo unitario no puede ser negativo.',
            'tipo.in' => 'El tipo de egreso debe ser "entrada", "salida" o "ajuste".',
        ];
    }
}