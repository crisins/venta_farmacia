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
    'requiere_receta',
    'estado',
    'fecha_alta',
    'stock',
];

}
