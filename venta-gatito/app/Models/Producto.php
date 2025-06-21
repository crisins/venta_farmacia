<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'requiere_receta',
        'estado',
        'fecha_alta',
        'stock', // <--- ¡Añade esta línea!
    ];

    // Eliminada relación con Inventario, ya no es necesaria
}
