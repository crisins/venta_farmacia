<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Envio extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_id',
        'empresa_log_id',
        'estado_envio',
        'fecha_envio',
        'fecha_entrega',
    ];
    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];
    protected $table = 'envios';
    protected $primaryKey = 'id';    

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
    public function empresaLogistica()
    {
        return $this->belongsTo(EmpresaLogistica::class, 'empresa_log_id');
    }
}
