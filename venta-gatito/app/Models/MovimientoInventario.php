<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'descripcion',
        'fecha',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
