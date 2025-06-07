<?php
namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'telefono' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'direccion' => 'required|string|max:255',
            'tipo' => 'required|in:cliente,administrador',
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => bcrypt($request->password),
            'direccion' => $request->direccion,
            'tipo' => $request->tipo,
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    }
    public function show($id)
    {
        // Buscar el usuario por su ID
        $usuario = Usuario::find($id);
        
        if ($usuario) {
            return response()->json($usuario);
        } else {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    }
    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        if ($usuario) {
            $usuario->update([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'tipo' => $request->tipo,
            ]);

            return response()->json($usuario);
        } else {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    }
    


    public function index()
    {
        $usuarios = Usuario::all();
        return response()->json($usuarios);
    }
    protected $hidden = [
        'password',
    ];
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'direccion',
        'tipo',
    ];
    
    
}