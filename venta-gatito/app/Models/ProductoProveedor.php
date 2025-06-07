<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoProveedor extends Model
{
    use HasFactory;
    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'precio_compra',
        'stock_disponible',
        'tiempo_entrega_dias',
    ];
    protected $table = 'productos_proveedores';   
    
    public function producto()
{
    return $this->belongsTo(Producto::class);
}

public function proveedor()
{
    return $this->belongsTo(Proveedor::class);
}

}
