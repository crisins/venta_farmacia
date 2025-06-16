<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'stock_actual',
    ];

    // Aquí puedes agregar relaciones si las necesitas
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
