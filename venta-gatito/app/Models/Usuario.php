<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'tipo',
        'telefono',
        'direccion'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}