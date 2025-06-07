<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Validation\ValidationException;

class ProductoService
{
    /**
     * Registrar un nuevo producto.
     */
    public function registrarProducto(array $data)
    {
        $producto = Producto::create($data);
        return $producto;
    }

    /**
     * Actualizar un producto existente.
     */
    public function actualizarProducto(int $id, array $data)
    {
        $producto = Producto::findOrFail($id);
        $producto->update($data);
        return $producto;
    }

    /**
     * Eliminar un producto.
     */
    public function eliminarProducto(int $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();
    }

    /**
     * Listar todos los productos.
     */
    public function listarProductos()
    {
        return Producto::with('proveedor')->get();
    }

    /**
     * Mostrar un producto específico.
     */
    public function mostrarProducto(int $id)
    {
        return Producto::with('proveedor')->findOrFail($id);
    }
}