<?php

namespace App\Services;

use App\Models\Proveedor;
use Illuminate\Validation\ValidationException;

class ProveedorService
{
    /**
     * Registrar un nuevo proveedor.
     */
    public function registrarProveedor(array $data)
    {
        $proveedor = Proveedor::create($data);
        return $proveedor;
    }

    /**
     * Actualizar un proveedor existente.
     */
    public function actualizarProveedor(int $id, array $data)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($data);
        return $proveedor;
    }

    /**
     * Eliminar un proveedor.
     */
    public function eliminarProveedor(int $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();
    }

    /**
     * Listar todos los proveedores.
     */
    public function listarProveedores()
    {
        return Proveedor::all();
    }

    /**
     * Mostrar un proveedor específico.
     */
    public function mostrarProveedor(int $id)
    {
        return Proveedor::findOrFail($id);
    }
}