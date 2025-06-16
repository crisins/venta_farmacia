<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleEgreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'egreso_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function egreso()
    {
        return $this->belongsTo(Egreso::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
