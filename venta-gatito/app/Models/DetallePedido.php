<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class DetallePedido extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unit',
    ];
    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id';    

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    

}
