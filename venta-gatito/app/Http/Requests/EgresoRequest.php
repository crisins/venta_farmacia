<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Inventario;
use App\Models\Producto;

class EgresoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asume que la autorización se maneja en Middleware o directamente en el controlador
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
                    // Opcional: Validar si el producto está 'activo' para egresos
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
                        $inventario = Inventario::where('producto_id', $producto_id)->first();
                        if (!$inventario || $inventario->stock_actual < $value) {
                            $fail('Stock insuficiente para egreso de tipo salida del producto ID ' . $producto_id . '. Stock disponible: ' . ($inventario ? $inventario->stock_actual : 0));
                        }
                    }
                }
            ],
            'detalles.*.costo_unitario' => 'required|numeric|min:0',
        ];

        // Reglas adicionales si estás actualizando un egreso y quieres validar ciertos campos
        // if ($this->isMethod('PUT')) {
        //     // 'proveedor_id' => 'sometimes|exists:proveedores,id',
        //     // 'usuario_id' => 'sometimes|exists:usuarios,id',
        //     // 'fecha' => 'sometimes|date',
        //     // 'tipo' => 'sometimes|in:entrada,salida,ajuste',
        //     // ... otras reglas para update
        // }

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