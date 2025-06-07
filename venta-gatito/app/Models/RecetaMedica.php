<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecetaMedica extends Model
{
    use HasFactory;

    protected $table = 'recetas_medicas';

    protected $fillable = [
        'usuario_id',
        'archivo_url',
        'fecha_subida',
        'estado_validacion',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];    
    public function usuario()
{
    return $this->belongsTo(Usuario::class);
}

}
