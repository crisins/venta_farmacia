<?php

namespace App\Services;

use App\Models\MetodoPago;

class MetodoPagoService
{
    public function listarTodos()
    {
        return MetodoPago::all();
    }

    public function obtenerPorId($id)
    {
        return MetodoPago::findOrFail($id);
    }

    public function crear(array $data)
    {
        return MetodoPago::create([
            'nombre' => $data['nombre'],
        ]);
    }

    public function actualizar($id, array $data)
    {
        $metodo = MetodoPago::findOrFail($id);
        $metodo->update($data);
        return $metodo;
    }

    public function eliminar($id)
    {
        $metodo = MetodoPago::findOrFail($id);
        $metodo->delete();
        return $metodo;
    }
}
