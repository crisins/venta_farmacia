<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpresaLogistica extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'email',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    protected $table = 'empresas_logisticas'; 
    protected $primaryKey = 'id';
}

