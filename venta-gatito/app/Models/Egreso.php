<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Egreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'usuario_id',
        'fecha',
        'tipo', // <--- ¡Añadir esta línea!
        'total',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleEgreso::class);
    }
}
