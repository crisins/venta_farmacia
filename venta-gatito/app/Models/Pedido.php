<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use HasFactory;
    protected $fillable = [
        'usuario_id',
        'fecha_pedido',
        'estado',
        'total',
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
    

}
