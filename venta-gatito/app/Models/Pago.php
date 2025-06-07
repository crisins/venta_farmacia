<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pago extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_id',
        'metodo_pago',
        'estado',
        'fecha_pago',
    ];
    protected $casts = [
        'fecha_pago' => 'datetime',
    ];
    protected $table = 'pagos';
    protected $primaryKey = 'id';    

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
