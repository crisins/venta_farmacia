<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'requiere_receta', // Asegúrate de que esté aquí
        'estado',          // Asegúrate de que esté aquí
        'fecha_alta',
        // 'stock', // Si este campo no existe en tu migración de 'productos', ELIMÍNALO de aquí también.
                  // El stock se maneja en el modelo Inventario.
    ];

    // Opcional: define una relación con Inventario si la necesitas
    public function inventario()
    {
        return $this->hasOne(Inventario::class);
    }
}
