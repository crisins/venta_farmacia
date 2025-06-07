<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AtencionCliente extends Model
{
    use HasFactory;
    protected $fillable = [
        'usuario_id',
        'tipo',
        'detalle',
        'estado',
        'fecha',
    ];
    protected $casts = [
        'fecha' => 'datetime',
    ];
    protected $table = 'atencion_cliente';
    protected $primaryKey = 'id';   
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
